<?php

class Database {

public $host_name2, $dbname, $username, $password, $conn;

function __construct() {
    $this->host_name2 = "localhost";
    $this->dbname = "dorders_db";
    $this->username = "dorders_admin";
    $this->password = ";M#+^p-qaci!";

    try {

        $this->conn = new PDO("mysql:host=$this->host_name2;dbname=$this->dbname", $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        echo 'Error: Database connection failed.';
    }
}

function getDatabase()
{
    return $this->conn;
}

// Identifiers (table/column names) can't be bound as parameters, so they are
// restricted to a safe allowlist pattern instead of being escaped/interpolated.
private function assertSafeIdentifier($name) {
    if (!is_string($name) || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new InvalidArgumentException('Unsafe SQL identifier: ' . var_export($name, true));
    }
    return $name;
}

// $sql must be a static/trusted string. Bind any dynamic values via $params
// (positional "?" or named ":name" placeholders), never via string concatenation.
function customSelect($sql, $params = []) {
    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return $rows;
    } catch (PDOException $e) {
        error_log('Database::customSelect failed: ' . $e->getMessage());
        return [];
    }
}

// $cond, if used, must be a static/trusted WHERE-clause fragment (e.g. a
// hardcoded string in the calling code) with any dynamic values passed
// through $condParams as "?" placeholders inside $cond — never interpolate
// request-derived data directly into $cond.
function select($tbl, $cond = '', $condParams = []) {
    $tbl = $this->assertSafeIdentifier($tbl);
    $sql = "SELECT * FROM `$tbl`";
    if ($cond != '') {
        $sql .= " WHERE $cond";
    }

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($condParams);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database::select failed: ' . $e->getMessage());
        return [];
    }
}

function num_rows($rows){
     $n = count($rows);
     return $n;
}

function delete($tbl, $cond = '', $condParams = []) {
    $tbl = $this->assertSafeIdentifier($tbl);
    $sql = "DELETE FROM `$tbl`";
    if ($cond != '') {
        $sql .= " WHERE $cond";
    }

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($condParams);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database::delete failed: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

function insert($tbl, $arr) {
    $tbl = $this->assertSafeIdentifier($tbl);
    $cols = array_keys($arr);
    foreach ($cols as $col) {
        $this->assertSafeIdentifier($col);
    }
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO `$tbl` (`" . implode('`, `', $cols) . "`) VALUES ($placeholders)";

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_values($arr));
        return $this->conn->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database::insert failed: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}

// $cond, if used, must be a static/trusted WHERE-clause fragment; pass any
// dynamic values for it via $condParams appended after the SET values.
function update($tbl, $arr, $cond, $condParams = []) {
    $tbl = $this->assertSafeIdentifier($tbl);
    $fld = [];
    $values = [];
    foreach ($arr as $k => $v) {
        $this->assertSafeIdentifier($k);
        $fld[] = "`$k` = ?";
        $values[] = $v;
    }
    $sql = "UPDATE `$tbl` SET " . implode(', ', $fld);
    if ($cond != '') {
        $sql .= " WHERE " . $cond;
        $values = array_merge($values, $condParams);
    }

    try {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database::update failed: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}
}

?>
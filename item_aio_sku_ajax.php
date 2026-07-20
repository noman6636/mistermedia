<?php
require_once "inc/config.php";
require_once "inc/functions.php";

header('Content-Type: application/json; charset=UTF-8');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(array('status' => 'error', 'items' => array()));
    exit();
}

if(!in_array(31, $permissions_allow)){
    echo json_encode(array('status' => 'error', 'items' => array()));
    exit();
}

$q = isset($_POST['q']) ? trim((string)$_POST['q']) : '';
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 200;

if($limit <= 0 || $limit > 500){
    $limit = 200;
}

$q_escaped = $conn->real_escape_string($q);
$where = "deleted = 0 AND item_type = 1 AND sku != ''";
if($q_escaped !== ''){
    $where .= " AND sku LIKE '%{$q_escaped}%'";
}

$sql = "SELECT sku, image FROM app_items WHERE {$where} ORDER BY sku ASC LIMIT {$limit}";
$res = $conn->query($sql);

$items = array();
if($res){
    while($row = $res->fetch_assoc()){
        $items[] = array(
            'sku' => (string)$row['sku'],
            'image' => ($row['image'] !== '' ? (string)$row['image'] : '54818317.png')
        );
    }
}

echo json_encode(array('status' => 'success', 'items' => $items));

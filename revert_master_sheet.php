<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "inc/config.php";
// Validate ID
if (!isset($_POST['id'])) {
    echo "ID missing";
    exit;
}

$id = intval($_POST['id']);

if ($id <= 0) {
    echo "Invalid ID";
    exit;
}

// Run query
$query = "UPDATE app_master_sheet 
          SET deleted_master_sheet = 0 
          WHERE id = $id";

if ($conn->query($query)) {
    echo "success";
} else {
    echo "SQL Error: " . $conn->error;
}

<?php
require_once "config.php";
require_once "functions.php";

// Check session
if (!isset($_SESSION['admin_id'])) {
    exit();
}

echo flash_msg();
?>
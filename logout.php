<?php 
require_once "inc/config.php";
require_once "inc/functions.php";


$_SESSION['admin_id']='';
addSystemLog($conn, 'LOGOUT', 'User Logged out from System', '');
session_destroy();

header("Location: login.php");
?>

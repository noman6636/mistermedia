<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

$logoutAdminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
if ($logoutAdminId > 0) {
    addSystemLog($conn, 'LOGOUT', 'User Logged out from System', '', $logoutAdminId);
}

$_SESSION['admin_id'] = '';
unset($_SESSION['auth_fingerprint'], $_SESSION['admin_auth_version'], $_SESSION['auth_started_at'], $_SESSION['last_activity']);
session_destroy();

header("Location: login.php");
    exit();
?>

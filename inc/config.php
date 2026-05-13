<?php
// Start output buffering at the very beginning
ob_start();
session_start();
// Configure error reporting
ini_set('display_errors', '0');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);
// Configure custom session save path if needed
$sessionPath = '/home/misterme/public_html/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}

// Set session options
ini_set('session.save_path', $sessionPath);
ini_set('session.gc_probability', 1);


// Set timezone
date_default_timezone_set('Europe/London');

// Database configuration
$servername = "localhost";
$username   = "mistgzny_mistermedia";
$database   = "mistgzny_mistermediasolutions";
$password   = "7W2bi,bD6wF~";

// Establish connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<h1>Database Error: " . htmlspecialchars($conn->connect_error) . "</h1>");
}

// $conn->set_charset("utf8");
$conn->set_charset("utf8");
$conn->query("SET SESSION time_zone = '-4:00'");


// Load admin data if logged in
$admin = null;
$permissions_allow = [];

if (isset($_SESSION['admin_id'])) {
    $adminId = intval($_SESSION['admin_id']); // Sanitize ID
    $adminResult = $conn->query("SELECT * FROM app_admins WHERE id = $adminId");

    if ($adminResult && $adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();

        if (!empty($admin['role_id'])) {
            $roleId = intval($admin['role_id']);
            $roleResult = $conn->query("SELECT * FROM app_roles WHERE id = $roleId");

            if ($roleResult && $roleResult->num_rows > 0) {
                $role = $roleResult->fetch_assoc();
                $permissions_allow = explode(',', $role['permissions']);
            }
        }
    }
}

// Load application settings
$settings = [];
$app_settings = $conn->query("SELECT * FROM app_settings");

if ($app_settings) {
    while ($row = $app_settings->fetch_assoc()) {
        $settings[$row['name']] = $row['value'];
    }
}

// Prepare arrays from settings
$pageArray     = isset($settings['page_settings']) ? explode(",", $settings['page_settings']) : [];
$csvArray      = isset($settings['csv_settings']) ? explode(",", $settings['csv_settings']) : [];
$columnsArray  = isset($settings['accounts_columns_settings']) ? explode(",", $settings['accounts_columns_settings']) : [];
?>

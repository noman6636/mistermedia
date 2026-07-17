<?php
// Start output buffering at the very beginning
ob_start();

function getClientIpAddress() {
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function getIpNetworkHint($ipAddress) {
    if ($ipAddress === '') {
        return '';
    }

    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ipAddress);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.' . $parts[2];
        }
    }

    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ipAddress);
        return implode(':', array_slice($parts, 0, 4));
    }

    return $ipAddress;
}

function buildSessionFingerprint() {
    $ip = getClientIpAddress();
    $ipHint = getIpNetworkHint($ip);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return hash('sha256', $ipHint . '|' . $userAgent);
}

function isSameOriginRequest() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return false;
    }

    $originHost = '';
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) ?: '';
    } elseif (!empty($_SERVER['HTTP_REFERER'])) {
        $originHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) ?: '';
    }

    if ($originHost === '') {
        return false;
    }

    return strcasecmp($host, $originHost) === 0;
}

function enforceAuthenticatedSessionIntegrity() {
    if (!isset($_SESSION['admin_id'])) {
        return;
    }

    $currentFingerprint = buildSessionFingerprint();
    if (!isset($_SESSION['auth_fingerprint'])) {
        $_SESSION['auth_fingerprint'] = $currentFingerprint;
        return;
    }

    if (!hash_equals($_SESSION['auth_fingerprint'], $currentFingerprint)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

// Enforce safer PHP session behavior before starting the session.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

enforceAuthenticatedSessionIntegrity();

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
$username   = "mistgzny_mymisterofmediaorder";
$database   = "mistgzny_mistermediasolutions";
$password   = "_l_e4F2DN#6WG?gf";

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

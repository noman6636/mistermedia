<?php
require_once "config.php";
header('Content-Type: application/json');

// Check session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get dashboard data
$dashboardData = $conn->query("SELECT value FROM app_settings WHERE name = 'dashboard_data'")->fetch_assoc()['value'];
$dashboardData = json_decode($dashboardData, true);

// Get accounts count
$accountsCount = $conn->query("SELECT COUNT(*) as count FROM app_accounts WHERE deleted = '0'")->fetch_assoc()['count'];

$conn->close();

echo json_encode([
    'newOrders' => $dashboardData['newOrders'] ?? 0,
    'shippedOrders' => $dashboardData['shippedOrders'] ?? 0,
    'archivedOrders' => $dashboardData['archivedOrders'] ?? 0,
    'accountsCount' => $accountsCount
]);
?>
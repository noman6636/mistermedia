<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
        exit();
}

$accountIdParam = isset($_GET['account_id']) ? (string)$_GET['account_id'] : '';
$frmTs = isset($_GET['frmdate']) ? strtotime((string)$_GET['frmdate']) : false;
$toTs = isset($_GET['todate']) ? strtotime((string)$_GET['todate']) : false;

if ($accountIdParam === '' || $frmTs === false || $toTs === false) {
    header("location: index.php");
    exit();
}

$frmDate = date('Y-m-d', $frmTs);
$toDate = date('Y-m-d', $toTs);

if($accountIdParam==='all'){
    $account = "ALL";
    $header_row = array("Sn", "Account", "Orders", "Amount");
    $csvName = $account.'_'.$frmDate.'_'.$toDate.'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    
    fputcsv($output,$header_row);
    
    $sn=0;
    $total_orders = 0;
    $total_amount = 0;
    $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
    while ($accountRow = $accounts->fetch_assoc()) {
        $sn++;
        $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) >= '$frmDate' && DATE(CreatedTime) <= '$toDate' && AccountID = '{$accountRow['id']}' && IsArchived = '0'")->num_rows;
        $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.AccountID = '{$accountRow['id']}'")->fetch_assoc()['amount'];
        $total_orders+=$ordersCount;
        $total_amount+=$amountPaid;
        $dataValus=array($sn, $accountRow['account_name'], $ordersCount, round($amountPaid, 2));
        fputcsv($output,$dataValus);
    }
    
    $footer_row = array("", "Total:", $total_orders, round($total_amount,2));
    fputcsv($output,$footer_row);
    fclose($output);
    exit();
}else{
    $accountId = (int)$accountIdParam;
    if ($accountId <= 0) {
        header("location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM app_accounts WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $accountRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$accountRow) {
        header("location: index.php");
        exit();
    }

    $account = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$accountRow['account_name']);
    $header_row = array("Sn", "Date", "Orders", "Amount");
    $csvName = $account.'_'.$frmDate.'_'.$toDate.'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    
    fputcsv($output,$header_row);
    
    $account = $accountRow;
    $sn=0;
    $total_orders = 0;
    $total_amount = 0;
    while (strtotime($frmDate) <= strtotime($toDate)) {
        $sn++;
        $date = $frmDate;
        $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && AccountID = '$accountId' && IsArchived = '0'")->num_rows;
        $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0' && b.AccountID = '$accountId'")->fetch_assoc()['amount'];
        $total_orders+=$ordersCount;
        $total_amount+=$amountPaid;
        $dataValus=array($sn, $date, $ordersCount, round($amountPaid, 2));
        fputcsv($output,$dataValus);
        $frmDate = date ("Y-m-d", strtotime("+1 day", strtotime($frmDate)));
    }
    
    $footer_row = array("", "Total:", $total_orders, round($total_amount,2));
    fputcsv($output,$footer_row);
    fclose($output);
    exit();
    
}
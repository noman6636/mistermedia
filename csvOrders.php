<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if($_GET['account_id']=='all'){
    $account = "ALL";
    $header_row = array("Sn", "Account", "Orders", "Amount");
    $csvName = $account.'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    
    fputcsv($output,$header_row);
    
    $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    $toDate = date('Y-m-d', strtotime($_GET['todate']));
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
    $account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc()['account_name'];
    $header_row = array("Sn", "Date", "Orders", "Amount");
    $csvName = $account.'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    
    fputcsv($output,$header_row);
    
    $account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc();
    $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    $toDate = date('Y-m-d', strtotime($_GET['todate']));
    $sn=0;
    $total_orders = 0;
    $total_amount = 0;
    while (strtotime($frmDate) <= strtotime($toDate)) {
        $sn++;
        $date = $frmDate;
        $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && AccountID = '{$_GET['account_id']}' && IsArchived = '0'")->num_rows;
        $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0' && b.AccountID = '{$_GET['account_id']}'")->fetch_assoc()['amount'];
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
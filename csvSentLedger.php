<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
        exit();
}

    $accountId = (int)$_GET['account_id'];
    $header_row = array("Sn", "Date", "Received From", "Type", "Amount");
    $csvName = $accountId.'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    fputcsv($output,$header_row);

    $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    $toDate = date('Y-m-d', strtotime($_GET['todate']));
    $stmt = $conn->prepare("SELECT * FROM app_payments where sent_to = ? && datetime >= ? && datetime <= ?");
    $stmt->bind_param('iss', $accountId, $frmDate, $toDate);
    $stmt->execute();
    $payments = $stmt->get_result();
                    $total = 0;
    $sn=0;
    $accountStmt = $conn->prepare("SELECT * FROM app_accounts where id = ?");
    while($row = $payments->fetch_assoc()){
        $sn++;
        $total += $row['amount'];
        $accountStmt->bind_param('i', $row['account_id']);
        $accountStmt->execute();
        $received_account = $accountStmt->get_result()->fetch_assoc();
        if($row['type']==1){ $tp = 'Payment'; }else{ $tp = 'Profit';}
        $dataValus=array($sn, date('Y-m-d', strtotime($row['datetime'])), $received_account['account_name'], $tp, $row['amount']);
        fputcsv($output,$dataValus);
    
    }
    $dataValus=array($sn, "Total", "", "", $total);
    fputcsv($output,$dataValus);
	               
    fclose($output);
    exit();
?>
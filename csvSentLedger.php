<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

    $header_row = array("Sn", "Date", "Received From", "Type", "Amount");
    $csvName = $_GET['account_id'].'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    fputcsv($output,$header_row);
    
    $payments = $conn->query("SELECT * FROM app_payments where sent_to = '{$_GET['account_id']}' && datetime >= '{$_GET['frmdate']}' && datetime <= '{$_GET['todate']}'");
                    $total = 0;
    $sn=0;
    while($row = $payments->fetch_assoc()){
        $sn++;
        $total += $row['amount'];
        $received_account = $conn->query("SELECT * FROM app_accounts where id = '{$row['account_id']}'")->fetch_assoc();
        if($row['type']==1){ $tp = 'Payment'; }else{ $tp = 'Profit';}
        $dataValus=array($sn, date('Y-m-d', strtotime($row['datetime'])), $received_account['account_name'], $tp, $row['amount']);
        fputcsv($output,$dataValus);
    
    }
    $dataValus=array($sn, "Total", "", "", $total);
    fputcsv($output,$dataValus);
	               
    fclose($output);
    exit();
?>
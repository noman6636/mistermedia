<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

    $account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc();
    $header_row = array("Sn", "Date", "Narration", "Sent To", "Debit", "Credit", "Balance");
    $csvName = $account['account_name'].'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$csvName.'";');
    $output = fopen('php://output', 'w');
    fputcsv($output,$header_row);
    
    if($_GET['type']==1){
        $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    $toDate = date('Y-m-d', strtotime($_GET['todate']));
    $previousOrderAmount = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) < '$frmDate' && b.IsArchived = '0' && b.AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
    $previousShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE DATE(CreatedTime) < '$frmDate' &&  IsArchived = '0' && AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
    $previousPaymentAmount =  $conn->query("SELECT SUM(amount) as amount from app_payments where DATE(datetime) < '$frmDate' && account_id = '{$_GET['account_id']}'  and status = 100 and type = 1")->fetch_assoc()['amount']+0;
    $openBalance = ($previousOrderAmount+$previousShippingCost)-$previousPaymentAmount;
    $openbalacne_row = array("1", "Opening Balance", "", "", "", "", round($openBalance, 2));
    fputcsv($output,$openbalacne_row);
    
    $ledgerarray = array();
	                while (strtotime($frmDate) <= strtotime($toDate)) {
	                    
                        $date = $frmDate;
                         
                        $OrderAmount = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date' && b.IsArchived = '0' && b.AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
                        $ShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE DATE(CreatedTime) = '$date' &&  IsArchived = '0' && AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
                        $ledgerRow = array();
                        $ledgerRow['date'] = $date;
                        $ledgerRow['narration'] = '';
                        $ledgerRow['sent_to'] = '';
                        $ledgerRow['order_amount'] = ($OrderAmount+$ShippingCost);
                        $ledgerRow['payment_amount'] = 0;
                        $ledgerarray[] = $ledgerRow;
                        
                        $frmDate = date("Y-m-d", strtotime("+1 day", strtotime($frmDate)));
                    
	                }
	                $payments =  $conn->query("SELECT * from app_payments where DATE(datetime) >= '{$_GET['frmdate']}' && DATE(datetime) <= '{$_GET['todate']}' && account_id = '{$_GET['account_id']}' and status = 100 and type = '1'");
	                while($payment = $payments->fetch_assoc()){
	                    $ledgerRow = array();
                        $ledgerRow['date'] = date('Y-m-d', strtotime($payment['datetime']));
                        $ledgerRow['narration'] = $payment['description'];
                        $ledgerRow['sent_to'] = $payment['sent_to'];
                        $ledgerRow['order_amount'] = 0;
                        $ledgerRow['payment_amount'] = $payment['amount'];
                        $ledgerarray[] = $ledgerRow;
	                }
                    
                    
                    array_multisort(array_map('strtotime',array_column($ledgerarray,'date')), 
                    SORT_ASC, 
                    $ledgerarray);
                    
                    $sn=1;
                    foreach($ledgerarray as $ledger){
                        $sn++;
                        $openBalance = $openBalance+$ledger['order_amount']-$ledger['payment_amount'];
                        if($ledger['order_amount']!=0){ $oamount =  number_format($ledger['order_amount'], 2);}else{ $oamount = ""; }
                        if($ledger['payment_amount']!=0){$pamount = number_format($ledger['payment_amount'],2);}else{ $pamount = ""; }
                        
                        if($ledger['order_amount']!=0 || $ledger['payment_amount'] != 0){
                            $dataValus=array($sn, $ledger['date'], $ledger['narration'], $ledger['sent_to'], $oamount, $pamount, $openBalance);
                            fputcsv($output,$dataValus);
                        }
                    
                    }
                    fclose($output);
                    exit();
    }else{
        $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    $toDate = date('Y-m-d', strtotime($_GET['todate']));
    
    $previousPaymentAmount =  $conn->query("SELECT SUM(amount) as amount from app_payments where DATE(datetime) < '$frmDate' && account_id = '{$_GET['account_id']}'  and status = 100 and type = 2")->fetch_assoc()['amount']+0;
    $openBalance = $previousPaymentAmount;
    $openbalacne_row = array("1", "Opening Balance", "", "", "", "", round($openBalance, 2));
    fputcsv($output,$openbalacne_row);
    
    $ledgerarray = array();
	                
	                $payments =  $conn->query("SELECT * from app_payments where DATE(datetime) >= '{$_GET['frmdate']}' && DATE(datetime) <= '{$_GET['todate']}' && account_id = '{$_GET['account_id']}' and status = 100 and type = '2'");
	                while($payment = $payments->fetch_assoc()){
	                    $ledgerRow = array();
                        $ledgerRow['date'] = date('Y-m-d', strtotime($payment['datetime']));
                        $ledgerRow['narration'] = $payment['description'];
                        $ledgerRow['sent_to'] = $payment['sent_to'];
                        $ledgerRow['order_amount'] = 0;
                        $ledgerRow['payment_amount'] = $payment['amount'];
                        $ledgerarray[] = $ledgerRow;
	                }
                    
                    
                    array_multisort(array_map('strtotime',array_column($ledgerarray,'date')), 
                    SORT_ASC, 
                    $ledgerarray);
                    
                    $sn=1;
                    foreach($ledgerarray as $ledger){
                        $sn++;
                        $openBalance = $openBalance+$ledger['payment_amount'];
                        if($ledger['order_amount']!=0){ $oamount =  number_format($ledger['order_amount'], 2);}else{ $oamount = ""; }
                        if($ledger['payment_amount']!=0){$pamount = number_format($ledger['payment_amount'],2);}else{ $pamount = ""; }
                        
                        if($ledger['order_amount']!=0 || $ledger['payment_amount'] != 0){
                            $dataValus=array($sn, $ledger['date'], $ledger['narration'], $ledger['sent_to'], $oamount, $pamount, $openBalance);
                            fputcsv($output,$dataValus);
                        }
                    
                    }
                    fclose($output);
                    exit();
    }
    
?>
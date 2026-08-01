<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
        exit();
}

$accountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
$ledgerType = isset($_GET['type']) ? (int)$_GET['type'] : 1;
$frmTs = isset($_GET['frmdate']) ? strtotime((string)$_GET['frmdate']) : false;
$toTs = isset($_GET['todate']) ? strtotime((string)$_GET['todate']) : false;

if ($accountId <= 0 || !in_array($ledgerType, array(1, 2), true) || $frmTs === false || $toTs === false) {
    header("location: index.php");
    exit();
}

$frmDateInput = date('Y-m-d', $frmTs);
$toDateInput = date('Y-m-d', $toTs);
$titleCode = $accountId.'-'.date('mdY', $frmTs).date('mdY', $toTs);

$stmt = $conn->prepare("SELECT * FROM app_accounts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $accountId);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    header("location: index.php");
    exit();
}

if($ledgerType==2){
?>
<!DOCTYPE html>
<html>
<head>
	<title>Ledger # <?=htmlspecialchars($titleCode, ENT_QUOTES, 'UTF-8');?></title>


	<style>
		body {
            background-color: #ffffff;
        }
		 * { margin: 0; padding: 0; font-family: serif; }
		 body { font-size:12px; }
		 p { margin: 0; /* line-height: 17px; */ }
		table { width: 100%;border-collapse:collapse;top: 1.2in; }
		th { border: 1px solid black; padding: 5px; font-size:9px;color:#fff; background:#000;transition: none !important; -webkit-print-color-adjust: exact; text-align:center; }
		
		td { text-align: left; vertical-align: center; border: 1px solid black;border-bottom: 1px solid;padding:5px;}
		 
         .for {top: 0.5in;left: .0in;width: 3.9in;margin-bottom: 10px;}
         .for p{padding-top:0 !important;padding-bottom:0 !important;font-size:10px; border: 1px solid;}
         .totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
        @media print {
		th { border: 1px solid black; padding: 5px; font-size:9px;color:#fff; background:#000;transition: none !important; -webkit-print-color-adjust: exact; text-align:center; }
		.totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
		}


       
	</style>

</head>
<body>
    <span class="for" style="float:left;">
           <h3 class="text-center shadowhead txtbold">Ladger : <?=htmlspecialchars((string)$account['account_name'], ENT_QUOTES, 'UTF-8').' ('.date('Y/m/d', $frmTs).'-'.date('Y/m/d', $toTs).')';?></h3>
         <p>
               <span><b>Phone: </b> <?=htmlspecialchars((string)$account['phone'], ENT_QUOTES, 'UTF-8');?></span><br>
               <span><b>Email: </b> <?=htmlspecialchars((string)$account['email'], ENT_QUOTES, 'UTF-8');?></span><br>
               <span><b>Address: </b> <?=htmlspecialchars((string)$account['address'], ENT_QUOTES, 'UTF-8');?></span><br>
             <span><b>Type: </b> Profit</span><br>
             
        </p>
    </span>
	<br>
	<br>
	<table class="voucher-table sortable">
	    <thead>
	        <th style="width: 10%;">Sn.</th>
	        <th style="width: 13%;">Date</th>
	        <th style="width: 22%;">Narration</th>
            <th style="width: 15%;">Sent To</th>
	        <th style="width: 10%;">Debit</th>
	        <th style="width: 10%;">Credit</th>
	        <th style="width: 10%;">Balance</th>
	    </thead>
	    <tbody>
	        <?php 
            $frmDate = $frmDateInput;
            $toDate = $toDateInput;
	        
            $previousPaymentAmount =  $conn->query("SELECT SUM(amount) as amount from app_payments where DATE(datetime) < '$frmDate' && account_id = '$accountId'  and status = 100 and type = '2'")->fetch_assoc()['amount']+0;
	        $openBalance = $previousPaymentAmount;
	        ?>
	        <tr>
                    <td>1</td>
                    <td>Opening Balance</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:center"></td>
                    <td style="text-align:center"></td>
                    <td style="text-align:right"><?=round($openBalance, 2);?></td>
            </tr>
	        <?php   
	                
	                $ledgerarray = array();
	                $order_total = 0;
	                $payment_total = 0;
	                
                    $payments =  $conn->query("SELECT * from app_payments where DATE(datetime) >= '$frmDateInput' && DATE(datetime) <= '$toDateInput' && account_id = '$accountId' and status = 100 and type = '2'");
	                while($payment = $payments->fetch_assoc()){
	                    $ledgerRow = array();
                        $ledgerRow['date'] = date('Y-m-d', strtotime($payment['datetime']));
                        $ledgerRow['narration'] = $payment['description'];
                        $ledgerRow['sent_to'] = $payment['sent_to'];
                        
                        
                        $ledgerRow['order_amount'] = 0;
                        $ledgerRow['payment_amount'] = $payment['amount'];
                        $ledgerarray[] = $ledgerRow;
	                }
                    
                    
                    $dateSortKeys = array_map('strtotime', array_column($ledgerarray, 'date'));
                    array_multisort($dateSortKeys, SORT_ASC, $ledgerarray);
                    
                    $sn=1;
                    foreach($ledgerarray as $ledger){
                    $sn++;
                    $openBalance = $openBalance+$ledger['payment_amount'];
                    $order_total += $ledger['order_amount'];
                    $payment_total += $ledger['payment_amount'];
                    
                    ?>
                    
                <tr style="<?php if($ledger['order_amount']==0 && $ledger['payment_amount'] == 0){ echo 'display:none;';} ?>">
                    <td><?=$sn;?></td>
                    <td><?=$ledger['date'];?></td>
                    <td><?=$ledger['narration'];?></td>
                    <td><?=$ledger['sent_to'];?></td>
                    <td style="text-align:center"><?php if($ledger['order_amount']!=0){echo number_format($ledger['order_amount'], 2);}?></td>
                    <td style="text-align:center"><?php if($ledger['payment_amount']!=0){echo number_format($ledger['payment_amount'],2);}?></td>
                    <td style="text-align:right"><?=round($openBalance, 2);?></td>
                </tr>
                   
                
                <?php  } ?>
	    
        	    <tr>
                    <td></td>
                    <td colspan="3">Total</td>
                    <td style="text-align:center"><?php echo $order_total; ?></td>
                    <td style="text-align:center"><?php echo $payment_total; ?></td>
                    <td style="text-align:right"></td>
                </tr>
	        
	    </tbody>
	</table>



<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">

// 		window.print();
		
</script>
</body>
</html>
<?php }else{ ?>
<html>
<head>
    <title>Ledger # <?=htmlspecialchars($titleCode, ENT_QUOTES, 'UTF-8');?></title>


	<style>
		body {
            background-color: #ffffff;
        }
		 * { margin: 0; padding: 0; font-family: serif; }
		 body { font-size:12px; }
		 p { margin: 0; /* line-height: 17px; */ }
		table { width: 100%;border-collapse:collapse;top: 1.2in; }
		th { border: 1px solid black; padding: 5px; font-size:9px;color:#fff; background:#000;transition: none !important; -webkit-print-color-adjust: exact; text-align:center; }
		
		td { text-align: left; vertical-align: center; border: 1px solid black;border-bottom: 1px solid;padding:5px;}
		 
         .for {top: 0.5in;left: .0in;width: 3.9in;margin-bottom: 10px;}
         .for p{padding-top:0 !important;padding-bottom:0 !important;font-size:10px; border: 1px solid;}
         .totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
        @media print {
		th { border: 1px solid black; padding: 5px; font-size:9px;color:#fff; background:#000;transition: none !important; -webkit-print-color-adjust: exact; text-align:center; }
		.totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
		}


       
	</style>

</head>
<body>
    <span class="for" style="float:left;">
           <h3 class="text-center shadowhead txtbold">Ladger : <?=htmlspecialchars((string)$account['account_name'], ENT_QUOTES, 'UTF-8').' ('.date('Y/m/d', $frmTs).'-'.date('Y/m/d', $toTs).')';?></h3>
         <p>
               <span><b>Phone: </b> <?=htmlspecialchars((string)$account['phone'], ENT_QUOTES, 'UTF-8');?></span><br>
               <span><b>Email: </b> <?=htmlspecialchars((string)$account['email'], ENT_QUOTES, 'UTF-8');?></span><br>
               <span><b>Address: </b> <?=htmlspecialchars((string)$account['address'], ENT_QUOTES, 'UTF-8');?></span><br>
             <span><b>Type: </b> Payments</span><br>
             
        </p>
    </span>
	<br>
	<br>
	<table class="voucher-table sortable">
	    <thead>
	        <th style="width: 10%;">Sn.</th>
	        <th style="width: 13%;">Date</th>
	        <th style="width: 22%;">Narration</th>
            <th style="width: 15%;">Sent To</th>
	        <th style="width: 10%;">Debit</th>
	        <th style="width: 10%;">Credit</th>
	        <th style="width: 10%;">Balance</th>
	    </thead>
	    <tbody>
	        <?php 
            $frmDate = $frmDateInput;
            $toDate = $toDateInput;
	        $previousOrderAmount = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) < '$frmDate' && b.IsArchived = '0' && b.AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
	        $previousShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE DATE(CreatedTime) < '$frmDate' &&  IsArchived = '0' && AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
            $previousPaymentAmount =  $conn->query("SELECT SUM(amount) as amount from app_payments where DATE(datetime) < '$frmDate' && account_id = '$accountId'  and status = 100 and type = '1'")->fetch_assoc()['amount']+0;
	        $openBalance = ($previousOrderAmount+$previousShippingCost)-$previousPaymentAmount;
	        ?>
	        <tr>
                    <td>1</td>
                    <td>Opening Balance</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:center"></td>
                    <td style="text-align:center"></td>
                    <td style="text-align:right"><?=round($openBalance, 2);?></td>
            </tr>
	        <?php   
	                
	                $ledgerarray = array();
	                $order_total = 0;
	                $payment_total = 0;
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
                    $payments =  $conn->query("SELECT * from app_payments where DATE(datetime) >= '$frmDateInput' && DATE(datetime) <= '$toDateInput' && account_id = '$accountId' and status = 100 and type = '1'");
	                while($payment = $payments->fetch_assoc()){
	                    $ledgerRow = array();
                        $ledgerRow['date'] = date('Y-m-d', strtotime($payment['datetime']));
                        $ledgerRow['narration'] = $payment['description'];
                        $ledgerRow['sent_to'] = $payment['sent_to'];
                        
                        
                        $ledgerRow['order_amount'] = 0;
                        $ledgerRow['payment_amount'] = $payment['amount'];
                        $ledgerarray[] = $ledgerRow;
	                }
                    
                    
                    $dateSortKeys = array_map('strtotime', array_column($ledgerarray, 'date'));
                    array_multisort($dateSortKeys, SORT_ASC, $ledgerarray);
                    
                    $sn=1;
                    foreach($ledgerarray as $ledger){
                    $sn++;
                    $openBalance = $openBalance+$ledger['order_amount']-$ledger['payment_amount'];
                    $order_total += $ledger['order_amount'];
                    $payment_total += $ledger['payment_amount'];
                    
                    ?>
                    
                <tr style="<?php if($ledger['order_amount']==0 && $ledger['payment_amount'] == 0){ echo 'display:none;';} ?>">
                    <td><?=$sn;?></td>
                    <td><?=$ledger['date'];?></td>
                    <td><?=$ledger['narration'];?></td>
                    <td><?=$ledger['sent_to'];?></td>
                    <td style="text-align:center"><?php if($ledger['order_amount']!=0){echo number_format($ledger['order_amount'], 2);}?></td>
                    <td style="text-align:center"><?php if($ledger['payment_amount']!=0){echo number_format($ledger['payment_amount'],2);}?></td>
                    <td style="text-align:right"><?=round($openBalance, 2);?></td>
                </tr>
                   
                
                <?php  } ?>
	    
        	    <tr>
                    <td></td>
                    <td colspan="3">Total</td>
                    <td style="text-align:center"><?php echo $order_total; ?></td>
                    <td style="text-align:center"><?php echo $payment_total; ?></td>
                    <td style="text-align:right"></td>
                </tr>
	        
	    </tbody>
	</table>



<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">

// 		window.print();
		
</script>
</body>
</html>
<?php } ?>
<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


?>
<!DOCTYPE html>
<html>
<head>
	<title> <?=$_GET['account_id'].'-'.date('mdY', strtotime($_GET['frmdate'])).date('mdY', strtotime($_GET['todate']));?></title>


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
        <h3 class="text-center shadowhead txtbold">Ladger : <?=$_GET['account_id'].' ('.date('Y/m/d', strtotime($_GET['frmdate'])).'-'.date('Y/m/d', strtotime($_GET['todate'])).')';?></h3>
        
    </span>
	<br>
	<br>
	<table class="voucher-table sortable">
	    <thead>
	        <th style="width: 10%;">Sn.</th>
	        <th style="width: 15%;">Date</th>
	        <th style="width: 35%;">Received From</th>
            <th style="width: 20%;">Type</th>
	        <th style="width: 20%;">Amount</th>
	    </thead>
	    <tbody>
	       
	        <?php   
	                
	                $payments = $conn->query("SELECT * FROM app_payments where sent_to = '{$_GET['account_id']}' && datetime >= '{$_GET['frmdate']}' && datetime <= '{$_GET['todate']}'");
                    $total = 0;
                    $sn=0;
                    while($row = $payments->fetch_assoc()){
                    $sn++;
                    $received_account = $conn->query("SELECT * FROM app_accounts where id = '{$row['account_id']}'")->fetch_assoc();
                    $total += $row['amount'];
                    
                    ?>
                    
                <tr style="">
                    <td><?=$sn;?></td>
                    <td><?=date('Y-m-d', strtotime($row['datetime']));?></td>
                    <td><?=$received_account['account_name'];?></td>
                    <td><? if($row['type']==1){ echo 'Payment'; }else{ echo 'Profit';} ?></td>
                    <td style="text-align:right"><?=$row['amount'];?></td>
                </tr>
                   
                
                <?php  } ?>
	    
	            <tr style="">
                    <td><?=$sn;?></td>
                    <td colspan="3">Total</td>
                    <td style="text-align:right"><?=$total;?></td>
                </tr>
	        
	    </tbody>
	</table>



<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">

// 		window.print();
		
</script>
</body>
</html>
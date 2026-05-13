<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("location: login.php");
    exit();
}

$account = "Unknown Account"; // Default value

if (isset($_GET['account_id'])) {
    $accountId = $_GET['account_id'];

    if ($accountId === 'all') {
        $account = "ALL";
    } elseif ($accountId === 'all1') {
        $account = "ALL EBAY ACCOUNTS";
    } elseif ($accountId === 'all2') {
        $account = "ALL STATIC ACCOUNTS";
    } elseif ($accountId === 'all4') {
        $account = "ALL AMAZON ACCOUNTS";
    } else {
        // Sanitize the input
        $accountId = intval($accountId); // assuming numeric IDs
        $result = $conn->query("SELECT account_name FROM app_accounts WHERE id = $accountId");

        if ($result && $row = $result->fetch_assoc()) {
            $account = $row['account_name'];
        } else {
            $account = "Invalid Account";
        }
    }
} else {
    $account = "No Account Selected";
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Orders # <?=$account.'-'.date('mdY', strtotime($_GET['frmdate'])).date('mdY', strtotime($_GET['todate']));?></title>


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
         table tfoot{display:table-row-group;}
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
        <h3 class="text-center shadowhead txtbold">Account: <?=$account;?></h3>
        <h3 class="text-center shadowhead txtbold">From Date: <?=date('Y-m-d', strtotime($_GET['frmdate']));?></h3>
        <h3 class="text-center shadowhead txtbold">To Date: <?=date('Y-m-d', strtotime($_GET['todate']));?></h3>
    </span>
	<br>
	<br>
	<?php if($_GET['account_id']=='all' || $_GET['account_id']=='all1' || $_GET['account_id']=='all2' || $_GET['account_id']=='all4'){
	if($_GET['type']=='1'){ ?>
	<table class="voucher-table sortable">
	    <thead>
	        <th style="width: 10%;">Sn.</th>
	        <th style="width: 60%;">Account</th>
	        <th style="width: 15%;">Orders</th>
	        <th style="width: 15%;">Amount</th>
	    </thead>
	    <tbody>
	        <?php   
	                $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
	                $toDate = date('Y-m-d', strtotime($_GET['todate']));
	                $sn=0;
	                $total_orders = 0;
	                $total_amount = 0;
	                if($_GET['account_id']=='all'){
                        $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                    }if($_GET['account_id']=='all1'){
                       $accounts = $conn->query("select * from app_accounts where account_type = 1 and deleted = 0 order by account_name asc");
                    }if($_GET['account_id']=='all2'){
                        $accounts = $conn->query("select * from app_accounts where (account_type = 2 || account_type = 3) and deleted = 0 order by account_name asc");
                    }if($_GET['account_id']=='all4'){
                        $accounts = $conn->query("select * from app_accounts where account_type = 4 and deleted = 0 order by account_name asc");
                    }
	                
	                while ($accountRow = $accounts->fetch_assoc()) {
	                    $sn++;
                    $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) >= '$frmDate' && DATE(CreatedTime) <= '$toDate' && AccountID = '{$accountRow['id']}' && IsArchived = '0'")->num_rows;
                    // $totalSaleForItems = $conn->query("select a.SKU, b.id, SUM(c.price) as amount from app_orders as a, app_items as b, app_sellprices_amount as c where a.AccountID = '{$accountRow['id']}' && b.sku = a.SKU && c.item_id = b.id && c.name_id = '{$accountRow['price_tag']}' && DATE(a.CreatedTime) >= '$frmDate' && DATE(a.CreatedTime) <= '$toDate' && a.IsArchived = '0'")->fetch_assoc()['amount']+0;
                    // $totalSaleForPackage = $conn->query("select a.sku, SUM(b.price) as amount FROM app_orders as a, app_packages as b WHERE b.sku = a.SKU && a.AccountID = '{$accountRow['id']}' && DATE(a.CreatedTime) >= '$frmDate' && DATE(a.CreatedTime) <= '$toDate' && a.IsArchived = '0'")->fetch_assoc()['amount']+0;
                    $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.AccountID = '{$accountRow['id']}'")->fetch_assoc()['amount'];
                    $total_orders+=$ordersCount;
                     $total_amount+=$amountPaid;
                    ?>
                    
                <tr>
                    <td><?=$sn;?></td>
                    <td><?=$accountRow['account_name'];?></td>
                    <td style="text-align:center"><?=$ordersCount;?></td>
                    <td style="text-align:right"><?=round($amountPaid, 2);?></td>
                </tr>
                   
                
                <?php  }
                
                
                ?>
	    
	        
	    </tbody>
	    <tfoot>
	        <?php echo '<tr>
	            <td colspan="2" class="totalAmount">Total : </td>
	            <td class="totalAmount">'.$total_orders.'</td>
	            <td class="totalAmount">'.round($total_amount,2).'</td>
	            </tr>'; ?>
	    </tfoot>
	</table>
	<?php }else{ ?>
	    <table class="voucher-table sortable">
    	    <thead>
    	        <th style="width: 10%;">Sn.</th>
    	        <th style="width: 60%;">Date</th>
    	        <th style="width: 15%;">Orders</th>
    	        <th style="width: 15%;">Amount</th>
    	    </thead>
    	    <tbody>
    	        <?php   
    	                $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
    	                $toDate = date('Y-m-d', strtotime($_GET['todate']));
    	                $sn=0;
    	                $total_orders = 0;
    	                $total_amount = 0;
    	                while (strtotime($frmDate) <= strtotime($toDate)) {
    	                    $sn++;
                        $date = $frmDate;
                        
                        if($_GET['account_id']=='all'){
                            $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && IsArchived = '0'")->num_rows;
                            $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0'")->fetch_assoc()['amount'];
                        }if($_GET['account_id']=='all1'){
                           $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && IsArchived = '0' && OrderType = '1'")->num_rows;
                           $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0' && b.OrderType = '1'")->fetch_assoc()['amount'];
                        }if($_GET['account_id']=='all2'){
                            $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && IsArchived = '0' && (OrderType = '2' || OrderType = '3')")->num_rows;
                           $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0' && (b.OrderType = '2' || b.OrderType = '3')")->fetch_assoc()['amount'];
                        }if($_GET['account_id']=='all4'){
                            $ordersCount = $conn->query("SELECT * FROM `app_orders` where DATE(CreatedTime) = '$date' && IsArchived = '0' && OrderType = '4'")->num_rows;
                           $amountPaid = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date'  && b.IsArchived = '0' && b.OrderType = '4'")->fetch_assoc()['amount'];
                        }
                        
                        $total_orders+=$ordersCount;
                        $total_amount+=$amountPaid;
                        ?>
                        
                    <tr>
                        <td><?=$sn;?></td>
                        <td><?=$date;?></td>
                        <td style="text-align:center"><?=$ordersCount;?></td>
                        <td style="text-align:right"><?=round($amountPaid, 2);?></td>
                    </tr>
                       
                    
                    <?php $frmDate = date ("Y-m-d", strtotime("+1 day", strtotime($frmDate))); } ?>
                    
                    
    	    
    	        
    	    </tbody>
    	    <tfoot>
    	        <?php echo '<tr>
    	            <td colspan="2" class="totalAmount">Total : </td>
    	            <td class="totalAmount">'.$total_orders.'</td>
    	            <td class="totalAmount">'.round($total_amount,2).'</td>
    	            </tr>'; ?>
    	    </tfoot>
    	</table>
	<?php }}else{ ?>
	<table class="voucher-table sortable">
	    <thead>
	        <th style="width: 10%;">Sn.</th>
	        <th style="width: 60%;">Date</th>
	        <th style="width: 15%;">Orders</th>
	        <th style="width: 15%;">Amount</th>
	    </thead>
	    <tbody>
	        <?php   
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
                    ?>
                    
                <tr>
                    <td><?=$sn;?></td>
                    <td><?=$date;?></td>
                    <td style="text-align:center"><?=$ordersCount;?></td>
                    <td style="text-align:right"><?=round($amountPaid, 2);?></td>
                </tr>
                   
                
                <?php $frmDate = date ("Y-m-d", strtotime("+1 day", strtotime($frmDate))); } ?>
                
                
	    
	        
	    </tbody>
	    <tfoot>
	        <?php echo '<tr>
	            <td colspan="2" class="totalAmount">Total : </td>
	            <td class="totalAmount">'.$total_orders.'</td>
	            <td class="totalAmount">'.round($total_amount,2).'</td>
	            </tr>'; ?>
	    </tfoot>
	</table>
    <?php } ?>

<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">

// 		window.print();
		
</script>
</body>
</html>
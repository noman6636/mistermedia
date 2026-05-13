<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

$account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc();
$header_row = array("Sn", "Date", "OrderID", "SellRecordNo", "SKU", "Qty", "Total");
$csvName = $account['account_name'].'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$csvName.'";');
$output = fopen('php://output', 'w');

fputcsv($output,$header_row);
 $sn=0;
	        $total = 0;
	        $totalqty = 0;
	        $totalShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE  IsArchived = '0' && AccountID = '{$_GET['account_id']}' && DATE(CreatedTime) >= '{$_GET['frmdate']}' && DATE(CreatedTime) <= '{$_GET['todate']}'")->fetch_assoc()['amount'];
	        $orders = $conn->query("select * from app_order_items a, app_orders b where b.AccountID = '{$_GET['account_id']}' and DATE(b.CreatedTime) >= '{$_GET['frmdate']}' and DATE(b.CreatedTime) <= '{$_GET['todate']}' && b.IsArchived = '0' && b.OrderID = a.OrderID order by b.CreatedTime asc");
	        while($order = $orders->fetch_assoc()){
	            $totalqty += $order['QuantityPurchased'];
	            $total +=($order['QuantityPurchased']*$order['Price']);
	            $sn++;
	            
    	        $dataValus=array($sn, date('Y-m-d', strtotime($order['CreatedTime'])), $order['OrderID'], $order['SellingManagerSalesRecordNumber'], $order['SKU'], $order['QuantityPurchased'], ($order['QuantityPurchased']*$order['Price']));
                fputcsv($output,$dataValus);
	        }

	        if($orders->num_rows > 0){
	            $footer_row = array("", "", "", "", "Total:", $totalqty, $total);
	            fputcsv($output,$footer_row);
	            $footer_row = array("", "", "", "", "Shipping Cost:", "", $totalShippingCost);
	            fputcsv($output,$footer_row);
	            $footer_row = array("", "", "", "", "Net Total:", "", round($total+$totalShippingCost, 2));
                fputcsv($output,$footer_row);
	        }
	        fclose($output);
            exit();
	        
	        
?>
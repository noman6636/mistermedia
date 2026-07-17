<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

$accountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
$frmTs = isset($_GET['frmdate']) ? strtotime((string)$_GET['frmdate']) : false;
$toTs = isset($_GET['todate']) ? strtotime((string)$_GET['todate']) : false;

if ($accountId <= 0 || $frmTs === false || $toTs === false) {
	header("location: index.php");
	exit();
}

$frmDate = date('Y-m-d', $frmTs);
$toDate = date('Y-m-d', $toTs);

$stmt = $conn->prepare("SELECT * FROM app_accounts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $accountId);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
	header("location: index.php");
	exit();
}

$safeAccountName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$account['account_name']);
$header_row = array("Sn", "Date", "OrderID", "SellRecordNo", "SKU", "Qty", "Total");
$csvName = $safeAccountName.'_'.$frmDate.'_'.$toDate.'.csv';
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$csvName.'";');
$output = fopen('php://output', 'w');

fputcsv($output,$header_row);
 $sn=0;
	        $total = 0;
	        $totalqty = 0;
	        $stmt = $conn->prepare("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE IsArchived = '0' AND AccountID = ? AND DATE(CreatedTime) >= ? AND DATE(CreatedTime) <= ?");
	        $stmt->bind_param("iss", $accountId, $frmDate, $toDate);
	        $stmt->execute();
	        $totalShippingCost = $stmt->get_result()->fetch_assoc()['amount'];
	        $stmt->close();

	        $stmt = $conn->prepare("SELECT * FROM app_order_items a, app_orders b WHERE b.AccountID = ? AND DATE(b.CreatedTime) >= ? AND DATE(b.CreatedTime) <= ? AND b.IsArchived = '0' AND b.OrderID = a.OrderID ORDER BY b.CreatedTime ASC");
	        $stmt->bind_param("iss", $accountId, $frmDate, $toDate);
	        $stmt->execute();
	        $orders = $stmt->get_result();
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
	        $stmt->close();
	        fclose($output);
            exit();
	        
	        
?>
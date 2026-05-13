<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if($_GET['type']==1){ 
    
    if($_GET['item_id']=='all'){
        $item = "ALL";
        $header_row = array("Sn", "Item", "Reference", "Orders");
        $csvName = $item.'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$csvName.'";');
        $output = fopen('php://output', 'w');
        
        fputcsv($output,$header_row);
        
        $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
        $toDate = date('Y-m-d', strtotime($_GET['todate']));
        $sn=0;
        $total_orders = 0;
        $total_amount = 0;
        $items = $conn->query("select * from app_items where deleted = 0 order by sku asc");
        while ($itemRow = $items->fetch_assoc()) {
            $sn++;
            $whereAccount = '';
            if($_GET['account_id'] != 'all'){
                $whereAccount = "&& AccountID = '{$_GET['account_id']}'";
            }
            $total_sale_from_order_item = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$itemRow['sku']}' && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.OrderID = a.OrderID $whereAccount ")->fetch_assoc()['qty']+0;
            $package_items_sku = $conn->query("SELECT IFNULL(SUM(a.qty), 0) as qty, b.sku FROM `app_packages_items` as a, app_packages as b WHERE a.item_id = '{$itemRow['id']}' && b.id = a.package_id GROUP by a.package_id");
            $total_sale_from_order_package = 0;
            while($package_item_sku = $package_items_sku->fetch_assoc()){
                $tqtypis = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$package_item_sku['sku']}' && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.OrderID = a.OrderID $whereAccount")->fetch_assoc()['qty']+0;
                $total_sale_from_order_package += $package_item_sku['qty']*$tqtypis;
            }
            $sale = $total_sale_from_order_item+$total_sale_from_order_package;
            $total_orders+=$sale;
            
            $dataValus=array($sn, $itemRow['sku'], $itemRow['reference'], $sale);
            fputcsv($output,$dataValus);
                        
        }
        
        $footer_row = array("", "", "Total:", $total_orders);
        fputcsv($output,$footer_row);
        fclose($output);
        exit();
    	      
    }else{
        // Handle multiple item IDs
        $item_ids = explode(',', $_GET['item_id']);
        $valid_item_ids = array();
        
        // Validate and sanitize item IDs
        foreach($item_ids as $id) {
            $id = trim($id);
            if(is_numeric($id)) {
                $valid_item_ids[] = intval($id);
            }
        }
        
        if(empty($valid_item_ids)) {
            die("No valid item IDs provided");
        }
        
        // Get account info if specified
        $account_name = "ALL";
        if($_GET['account_id'] != 'all') {
            $account_id = intval($_GET['account_id']);
            $account = $conn->query("SELECT account_name FROM app_accounts WHERE id = $account_id")->fetch_assoc();
            if($account) {
                $account_name = $account['account_name'];
            }
        }
        
        // Prepare CSV
        $header_row = array("Sn", "Item", "Reference", "Orders");
        $csvName = 'MultipleItems_'.$account_name.'_'.date('Y-m-d', strtotime($_GET['frmdate'])).'_'.date('Y-m-d', strtotime($_GET['todate'])).'.csv';
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$csvName.'";');
        $output = fopen('php://output', 'w');
        fputcsv($output, $header_row);
        
        // Date range
        $frmDate = date('Y-m-d', strtotime($_GET['frmdate']));
        $toDate = date('Y-m-d', strtotime($_GET['todate']));
        $sn = 0;
        $total_orders = 0;
        
        // Account filter
        $whereAccount = '';
        if($_GET['account_id'] != 'all') {
            $whereAccount = "AND b.AccountID = '".intval($_GET['account_id'])."'";
        }
        
        // Process each item
        foreach($valid_item_ids as $item_id) {
            $sn++;
            $item = $conn->query("SELECT * FROM app_items WHERE id = $item_id")->fetch_assoc();
            if(!$item) continue;
            
            // Direct sales
            $total_sale_from_order_item = (int) $conn->query("
                SELECT IFNULL(SUM(a.QuantityPurchased), 0) as qty 
                FROM app_order_items a, app_orders b 
                WHERE a.SKU = '{$item['sku']}' 
                AND DATE(b.CreatedTime) >= '$frmDate' 
                AND DATE(b.CreatedTime) <= '$toDate' 
                AND b.IsArchived = '0' 
                AND b.OrderID = a.OrderID 
                $whereAccount
            ")->fetch_assoc()['qty'];
            
            // Package sales
            $total_sale_from_order_package = 0;
            $package_items = $conn->query("
                SELECT a.qty, b.sku 
                FROM app_packages_items a, app_packages b 
                WHERE a.item_id = $item_id 
                AND b.id = a.package_id
            ");
            
            while($package_item = $package_items->fetch_assoc()) {
                $pkg_qty = (int) $conn->query("
                    SELECT IFNULL(SUM(a.QuantityPurchased), 0) as qty 
                    FROM app_order_items a, app_orders b 
                    WHERE a.SKU = '{$package_item['sku']}' 
                    AND DATE(b.CreatedTime) >= '$frmDate' 
                    AND DATE(b.CreatedTime) <= '$toDate' 
                    AND b.IsArchived = '0' 
                    AND b.OrderID = a.OrderID 
                    $whereAccount
                ")->fetch_assoc()['qty'];
                
                $total_sale_from_order_package += $package_item['qty'] * $pkg_qty;
            }
            
            $sale = $total_sale_from_order_item + $total_sale_from_order_package;
            $total_orders += $sale;
            
            fputcsv($output, array(
                $sn,
                $item['sku'],
                $item['reference'],
                $sale
            ));
        }
        
        // Add footer with total
        fputcsv($output, array("", "", "Total:", $total_orders));
        fclose($output);
        exit();
    }

}elseif($_GET['type']==2){ 
    
    if($_GET['account_id']=='all'){
        $account = "ALL";
        $header_row = array("Sn", "Account", "Orders");
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
            if($_GET['item_id']=='all'){
                $ordersCount = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased), 0) count FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.AccountID = '{$accountRow['id']}'")->fetch_assoc()['count'];
            }else{
                $item = $conn->query("SELECT * FROM app_items where id = '{$_GET['item_id']}'")->fetch_assoc();
                $total_sale_from_order_item = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$item['sku']}' && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.AccountID = '{$accountRow['id']}'")->fetch_assoc()['qty']+0;
                $package_items_sku = $conn->query("SELECT IFNULL(SUM(a.qty), 0) as qty, b.sku FROM `app_packages_items` as a, app_packages as b WHERE a.item_id = '{$_GET['item_id']}' && b.id = a.package_id GROUP by a.package_id");
                $total_sale_from_order_package = 0;
                while($package_item_sku = $package_items_sku->fetch_assoc()){
                    $tqtypis = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$package_item_sku['sku']}' && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.AccountID = '{$accountRow['id']}'")->fetch_assoc()['qty']+0;
                    $total_sale_from_order_package += $package_item_sku['qty']*$tqtypis;
                }
                $ordersCount = $total_sale_from_order_item+$total_sale_from_order_package;
            }
             $total_orders+=$ordersCount;
            $dataValus=array($sn, $accountRow['account_name'], $ordersCount);
            fputcsv($output,$dataValus);
        }
        $footer_row = array("", "Total:", $total_orders);
        fputcsv($output,$footer_row);
        fclose($output);
        exit();
    }else{
        $account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc()['account_name'];
        $header_row = array("Sn", "Date", "Orders");
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
                        
                        if($_GET['item_id']=='all'){
	                        $ordersCount = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased), 0) count FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date' && b.IsArchived = '0' && b.AccountID = '{$_GET['account_id']}'")->fetch_assoc()['count'];
	                    }else{
	                        $item = $conn->query("SELECT * FROM app_items where id = '{$_GET['item_id']}'")->fetch_assoc();
	                        $total_sale_from_order_item = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$item['sku']}' && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date' && b.AccountID = '{$_GET['account_id']}'")->fetch_assoc()['qty']+0;
                            $package_items_sku = $conn->query("SELECT IFNULL(SUM(a.qty), 0) as qty, b.sku FROM `app_packages_items` as a, app_packages as b WHERE a.item_id = '{$_GET['item_id']}' && b.id = a.package_id GROUP by a.package_id");
                            $total_sale_from_order_package = 0;
                            while($package_item_sku = $package_items_sku->fetch_assoc()){
                                $tqtypis = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '{$package_item_sku['sku']}' && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) = '$date' && b.AccountID = '{$_GET['account_id']}'")->fetch_assoc()['qty']+0;
                                $total_sale_from_order_package += $package_item_sku['qty']*$tqtypis;
                            }
	                        $ordersCount = $total_sale_from_order_item+$total_sale_from_order_package;
	                    }
	                    $total_orders+=$ordersCount;
	                    $dataValus=array($sn, $date, $ordersCount);
                        fputcsv($output,$dataValus);
                        $frmDate = date ("Y-m-d", strtotime("+1 day", strtotime($frmDate)));
	                }
	                $footer_row = array("", "Total:", $total_orders);
                    fputcsv($output,$footer_row);
                    fclose($output);
                    exit();
        
                
    }
} ?>
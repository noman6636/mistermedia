<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
    exit;
}

// Validate required POST parameters
if(!isset($_POST['type']) || !in_array($_POST['type'], [1, 2])) {
    die("Invalid report type");
}

if(!isset($_POST['frmdate']) || !isset($_POST['todate'])) {
    die("Date range not specified");
}

// Validate dates
$frmDate = date('Y-m-d', strtotime($_POST['frmdate']));
$toDate = date('Y-m-d', strtotime($_POST['todate']));
if(!$frmDate || !$toDate) {
    die("Invalid date format");
}

// Sanitize item_ids and account_ids
$item_ids = isset($_POST['item_id']) && !empty($_POST['item_id']) ? $_POST['item_id'] : '0';
$account_ids = isset($_POST['account_id']) && !empty($_POST['account_id']) ? $_POST['account_id'] : '0';

// Convert to safe SQL values
$item_ids = implode(',', array_map('intval', explode(',', $item_ids)));
$account_ids = implode(',', array_map('intval', explode(',', $account_ids)));

?>
<!DOCTYPE html>
<?php if($_POST['type']==1){ ?>
<html>
<head>
    <title>Orders # <?='Items-'.date('mdY', strtotime($frmDate)).date('mdY', strtotime($toDate));?></title>
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
        <h3 class="text-center shadowhead txtbold">From Date: <?=htmlspecialchars($frmDate);?></h3>
        <h3 class="text-center shadowhead txtbold">To Date: <?=htmlspecialchars($toDate);?></h3>
    </span>
    <br>
    <br>
    <table class="voucher-table sortable">
        <thead>
            <th style="width: 10%;">Sn.</th>
            <th style="width: 40%;">Item</th>
            <th style="width: 20%;">Reference</th>
            <th style="width: 15%;">Orders</th>
        </thead>
        <tbody>
            <?php   
                    $sn=0;
                    $total_orders = 0;
                    $items = $conn->query("select * from app_items where id IN ($item_ids) && deleted = 0 order by sku asc");
                    if($items) {
                        while ($itemRow = $items->fetch_assoc()) {
                            $sn++;
                            $whereAccount = $account_ids != '0' ? "&& AccountID IN ($account_ids)" : "";
                            
                            $total_sale_from_order_item = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '".$conn->real_escape_string($itemRow['sku'])."' && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.OrderID = a.OrderID $whereAccount ")->fetch_assoc()['qty']+0;
                            $package_items_sku = $conn->query("SELECT IFNULL(SUM(a.qty), 0) as qty, b.sku FROM `app_packages_items` as a, app_packages as b WHERE a.item_id = '".$conn->real_escape_string($itemRow['id'])."' && b.id = a.package_id GROUP by a.package_id");
                            $total_sale_from_order_package = 0;
                            if($package_items_sku) {
                                while($package_item_sku = $package_items_sku->fetch_assoc()){
                                    $tqtypis = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '".$conn->real_escape_string($package_item_sku['sku'])."' && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.IsArchived = '0' && b.OrderID = a.OrderID $whereAccount")->fetch_assoc()['qty']+0;
                                    $total_sale_from_order_package += $package_item_sku['qty']*$tqtypis;
                                }
                            }
                            $sale = $total_sale_from_order_item+$total_sale_from_order_package;
                            $total_orders+=$sale;
                            ?>
                            
                        <tr>
                            <td><?=$sn;?></td>
                            <td><?=htmlspecialchars($itemRow['sku']);?></td>
                            <td><?=htmlspecialchars($itemRow['reference']);?></td>
                            <td style="text-align:center"><?=$sale;?></td>
                        </tr>
                        <?php  
                        }
                    }
                    ?>
        </tbody>
        <tfoot>
            <?php
             echo '<tr>
                <td colspan="3" class="totalAmount">Total : </td>
                <td class="totalAmount">'.$total_orders.'</td>
                </tr>';
                ?>
        </tfoot>
    </table>
    
<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">
//      window.print();
</script>
</body>
</html>
<?php }elseif($_POST['type']==2){ ?>
<html>
<head>
    <title>Orders # <?='Accounts-'.date('mdY', strtotime($frmDate)).date('mdY', strtotime($toDate));?></title>
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
        <h3 class="text-center shadowhead txtbold">From Date: <?=htmlspecialchars($frmDate);?></h3>
        <h3 class="text-center shadowhead txtbold">To Date: <?=htmlspecialchars($toDate);?></h3>
    </span>
    <br>
    <br>
    <table class="voucher-table sortable">
        <thead>
            <th style="width: 10%;">Sn.</th>
            <th style="width: 60%;">Item</th>
            <th style="width: 15%;">Orders</th>
        </thead>
        <tbody>
            <?php   
                    $sn=0;
                    $total_orders = 0;
                    $accounts = $conn->query("select * from app_accounts where id IN ($account_ids) && deleted = 0 order by account_name asc");
                    if($accounts) {
                        while ($accountRow = $accounts->fetch_assoc()) {
                            $sn++;
                            $total_sale_from_order_item = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU IN (select sku from app_items WHERE id IN ($item_ids)) && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.AccountID = '".$conn->real_escape_string($accountRow['id'])."'")->fetch_assoc()['qty']+0;
                            $package_items_sku = $conn->query("SELECT IFNULL(SUM(a.qty), 0) as qty, b.sku FROM `app_packages_items` as a, app_packages as b WHERE a.item_id IN ($item_ids) && b.id = a.package_id GROUP by a.package_id");
                            $total_sale_from_order_package = 0;
                            if($package_items_sku) {
                                while($package_item_sku = $package_items_sku->fetch_assoc()){
                                    $tqtypis = (int) $conn->query("Select SUM(a.QuantityPurchased) as qty from app_order_items a, app_orders b where a.SKU = '".$conn->real_escape_string($package_item_sku['sku'])."' && b.IsArchived = '0' && b.OrderID = a.OrderID && DATE(b.CreatedTime) >= '$frmDate' && DATE(b.CreatedTime) <= '$toDate' && b.AccountID = '".$conn->real_escape_string($accountRow['id'])."'")->fetch_assoc()['qty']+0;
                                    $total_sale_from_order_package += $package_item_sku['qty']*$tqtypis;
                                }
                            }
                            $ordersCount = $total_sale_from_order_item+$total_sale_from_order_package;
                            $total_orders+=$ordersCount;
                            ?>
                            
                        <tr>
                            <td><?=$sn;?></td>
                            <td><?=htmlspecialchars($accountRow['account_name']);?></td>
                            <td style="text-align:center"><?=$ordersCount;?></td>
                        </tr>
                        <?php  
                        }
                    }
                    ?>
        </tbody>
        <tfoot>
            <?php
             echo '<tr>
                <td colspan="2" class="totalAmount">Total : </td>
                <td class="totalAmount">'.$total_orders.'</td>
                </tr>';
                ?>
        </tfoot>
    </table>
<script src="app-assets/js/scripts/sorttable.js"></script>
<script type="text/javascript">
//      window.print();
</script>
</body>
</html>
<?php } ?>
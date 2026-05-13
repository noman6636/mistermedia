<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(8, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['labeltype'])){
    $goBackUrl = $_SERVER['REQUEST_URI'];
    $labeltype = $_POST['labeltype'];
    if($labeltype == 1){
        $labelImg = 'DC_UNG.png';
    }elseif($labeltype == 2){
        $labelImg = 'DC_UNH.png';
    }
    $aorder = $_POST['aorder'] ?? [];
    if(count($aorder) < 1){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast on order to print.</div></div>';
        header("location: $goBackUrl");
        exit();
    }
    
    if($labeltype == 100){
        $orderIds = array();
        foreach($aorder as $order){
            $orderId = strtok($order, '/');
            $conn->query("update app_orders set IsArchived = '1' where ID = '$orderId'");
            array_push($orderIds, $orderId);
        }
        
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'ORDER ARCHIVED', "Total $totalOrders has been archived", $orderIds);
        
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders has been archived</div></div>';
        header("location: $goBackUrl");
        exit();
    }
    
    if($labeltype == 3){
        $ids = '';
        $orderIds = array();
        foreach($aorder as $order){
            $orderId = strtok($order, '/');
            if($orderId) {
                $ids .= $orderId.',';
                array_push($orderIds, $orderId);
            }
        }
        $ids = rtrim($ids, ',');
        $whereQuery = $ids ? "WHERE ID IN ($ids)" : "WHERE 1=0";
        $data=array();
        $headKeys=array();
        $keysOnly = $settings['csv_settings'] ?? '';
        $keysOnlyDb = $keysOnly;
        $keysOnlyDb = str_replace(array('FullName,', 'AddressLine1,', 'AddressLine2,', 'City,', 'Country,', 'PhoneNo,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb = str_replace(array('ItemID,', 'SKU,', 'ItemTitle,', 'ConditionDisplayName,', 'QuantityPurchased,', 'SKUQTY,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb .= ", ShippingAddress";
        
        $query = "select OrderID, $keysOnlyDb from app_orders $whereQuery ORDER BY ID DESC";
        $query = $conn->query($query);
        while($row = $query->fetch_assoc()){
            $shipa = json_decode($row['ShippingAddress'] ?? '{}', true) ?: [];
                
            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '".($row['OrderID'] ?? '')."'");
            $showSKUQTY = '';
            $showItemID = '';
            $showSKU = '';
            $showItemTitle = '';
            $showConditionDisplayName = '';
            $showQuantityPurchased = '';
            
            while($item = $itemsList->fetch_assoc()){
                $showSKUQTY .= ($item['QuantityPurchased'] ?? '')." x ".($item['SKU'] ?? '')." = ";
                $showItemID .= ($item['ItemID'] ?? '')." = ";
                $showSKU .= ($item['SKU'] ?? '')." = ";
                $showItemTitle .= ($item['ItemTitle'] ?? '')." = ";
                $showConditionDisplayName .= ($item['ConditionDisplayName'] ?? '')." = ";
                $showQuantityPurchased .= ($item['QuantityPurchased'] ?? '')." = ";
            }
            if (strpos($keysOnly, 'SKUQTY') !== false) {
               $row['SKU_QTY'] = rtrim($showSKUQTY, ' = ');
            }
            
            if (strpos($keysOnly, 'ItemID') !== false) {
               $row['ItemID'] = rtrim($showItemID, ' = ');
            }
            
            if (strpos($keysOnly, 'SKU') !== false) {
               $row['SKU'] = rtrim($showSKU, ' = ');
            }
            
            if (strpos($keysOnly, 'ItemTitle') !== false) {
               $row['ItemTitle'] = rtrim($showItemTitle, ' = ');
            }
            
            if (strpos($keysOnly, 'ConditionDisplayName') !== false) {
               $row['ConditionDisplayName'] = rtrim($showConditionDisplayName, ' = ');
            }
            
            if (strpos($keysOnly, 'QuantityPurchased') !== false) {
               $row['QuantityPurchased'] = rtrim($showQuantityPurchased, ' = ');
            }
                    
            unset($row['ShippingAddress']);
            foreach($shipa as $key => $value){
                if($key == 'Name' && (strpos($keysOnly, 'FullName') !== false)){
                    $row['Name'] = (!is_array($value) ? $value : '');
                }
                
                if($key == 'Street1' && (strpos($keysOnly, 'AddressLine1') !== false)){
                    $row['Street1'] = (!is_array($value) ? $value : '');
                }
                
                if($key == 'Street2' && (strpos($keysOnly, 'AddressLine2') !== false)){
                    $row['Street2'] = (!is_array($value) ? $value : '');
                }
                
                if($key == 'CityName' && (strpos($keysOnly, 'City') !== false)){
                    $row['CityName'] = (!is_array($value) ? $value : '');
                }
                
                if($key == 'CountryName' && (strpos($keysOnly, 'Country') !== false)){
                    $row['CountryName'] = (!is_array($value) ? $value : '');
                }
                if($key == 'Phone' && (strpos($keysOnly, 'PhoneNo') !== false)){
                   $row['Phone'] = (!is_array($value) ? $value : '');
                }
            }
        
            $account = $conn->query("select * from app_accounts where id = '".($row['AccountID'] ?? '')."'")->fetch_assoc();
            $row['AccountID'] = $account['account_name'] ?? '';
            
            $data[]=$row;
        }
        
        $dateNow = date('Y-m-d H:i:s');
        if($ids) {
            $conn->query("update app_orders set IsInReship = '2', ShippedTime = '$dateNow' $whereQuery");
        }
        
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'ORDER RESHIPPED', "Total $totalOrders has been reshipped with csv format", $orderIds);
    
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="NewOrders.csv";');
        $output = fopen('php://output', 'w');
    
        $keysPut = 0;
        foreach($data as $product) {
            if($keysPut == 0){
               fputcsv($output, array_keys($product));  
               $keysPut = 1;
            }
            fputcsv($output, $product);  
        }
        fclose($output);
        exit();
    }
    
    $totalSelected = count($aorder);
    $n = 0;
    $printHTML = '<style type="text/css">
   table { page-break-inside:auto }
   tr    { page-break-inside:avoid; page-break-after:auto }
</style>
    <table width="100%">';
    
    $orderIds = array();
    
   foreach($aorder as $order){
       $orderId = strtok($order, '/');
       if(!$orderId) continue;
       
       array_push($orderIds, $orderId);
       $order = $conn->query("select * from app_orders where ID = '$orderId'")->fetch_assoc();
       $shipa = json_decode($order['ShippingAddress'] ?? '{}', true) ?: [];
       $dateNow = date('Y-m-d H:i:s');
       $conn->query("update app_orders set IsInReship = '2', ShippedTime = '$dateNow' where ID = '$orderId'");
       
       $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '".($order['OrderID'] ?? '')."'");
        $showSku = '';
        
        while($item = $itemsList->fetch_assoc()){
            $showSku .= ($item['QuantityPurchased'] ?? '').' x '.($item['SKU'] ?? '').'<br>';
        }
       if($n%2 == 0) {$printHTML .= '<tr>';}
       $printHTML .= '<td style="width:50%;padding: 20px;">
            <div style="width:100%;display: flex;">
                <div style="width:60%;font-size: 16px;font-family: sans-serif;">';
                   if(!empty($shipa['Name']) && !is_array($shipa['Name'])){ $printHTML .= htmlspecialchars($shipa['Name']) .'<br>'; }
                   if(!empty($shipa['Street1']) && !is_array($shipa['Street1'])){ $printHTML .= htmlspecialchars($shipa['Street1']) .'<br>'; }
                   if(!empty($shipa['Street2']) && !is_array($shipa['Street2'])){ $printHTML .= htmlspecialchars($shipa['Street2']) .'<br>'; }
                   if(!empty($shipa['CityName']) && !is_array($shipa['CityName'])){ $printHTML .= htmlspecialchars($shipa['CityName']) .'<br>'; }
                   if(!empty($shipa['StateOrProvince']) && !is_array($shipa['StateOrProvince'])){ $printHTML .= htmlspecialchars($shipa['StateOrProvince']) .'<br>'; }
                   if(!empty($shipa['PostalCode']) && !is_array($shipa['PostalCode'])){ $printHTML .= htmlspecialchars($shipa['PostalCode']) .'<br>'; }
                   if(!empty($shipa['CountryName']) && !is_array($shipa['CountryName'])){ $printHTML .= htmlspecialchars($shipa['CountryName']) .'<br>'; }
                   
               $printHTML .= '</div>
                <div style="width:40%;text-align: right;font-size: 16px;font-family: sans-serif;">
                    <img src="assets/'.htmlspecialchars($labelImg).'" style="width:60%"/><br>
                    '.htmlspecialchars($order['OrderID'] ?? '').'<br>
                    '.$showSku.'
                </div>
            </div>
        </td>';
       if($n%2 == 1) {$printHTML .= '</tr>';}
       $n++;
   }
   
   if($n%2 == 1) {$printHTML .= '</tr>';}
   
   $printHTML .= '</table>';
   
    $totalOrders = count($orderIds);
    $orderIds = implode(', ', $orderIds);
    addSystemLog($conn, 'ORDER RESHIPPED', "Total $totalOrders has been reshipped with DC UNG/UNH format", $orderIds);
   
   echo $printHTML;
   echo '<script type="text/javascript">
window.print();
window.onfocus=function(){ window.location.href="'.htmlspecialchars($goBackUrl).'";}
</script>';
    exit();
}

?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>Re-Ship Orders || D-Orders</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/extensions/toastr.min.css">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/semi-dark-layout.css">

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/charts/chart-apex.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/extensions/ext-component-toastr.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
    /* ================================
   EXTRA SMALL DEVICES (PHONES <576px)
================================ */
@media (max-width: 575.98px) {

    /* Layout Fix */
    .content-wrapper {
        padding: 8px !important;
    }

    /* Cards */
    .card {
        margin-bottom: 10px;
    }

    .card-header h4 {
        font-size: 14px;
    }

    /* Buttons */
    .btn {
        width: 100%;
        margin-bottom: 6px;
        font-size: 12px;
        padding: 6px;
    }

    .btn-group {
        width: 100%;
    }

    .btn-group .btn {
        width: 100%;
    }

    /* Form Inputs */
    input, select, textarea {
        font-size: 12px !important;
    }

    /* Table Handling */
    .table {
        display: block;
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
    }

    .table th, 
    .table td {
        font-size: 10px;
        padding: 6px;
    }

    /* Hide less important columns */
    .table th:nth-child(3),
    .table td:nth-child(3),
    .table th:nth-child(7),
    .table td:nth-child(7) {
        display: none;
    }

    /* Dropdown Fix */
    .dropdown-menu {
        width: 100%;
    }

    /* Modal */
    .modal-dialog {
        width: 95%;
        margin: auto;
    }
}


/* ================================
   SMALL DEVICES (PHONES ≥576px)
================================ */
@media (min-width: 576px) and (max-width: 767.98px) {

    .content-wrapper {
        padding: 10px !important;
    }

    .card-header h4 {
        font-size: 15px;
    }

    .btn {
        font-size: 13px;
        padding: 6px 10px;
    }

    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .table th, 
    .table td {
        font-size: 11px;
        padding: 8px;
    }

    /* Hide optional columns */
    .table th:nth-child(6),
    .table td:nth-child(6) {
        display: none;
    }
}


/* ================================
   MEDIUM DEVICES (TABLETS ≥768px)
================================ */
@media (min-width: 768px) and (max-width: 991.98px) {

    .content-wrapper {
        padding: 12px;
    }

    .card-header h4 {
        font-size: 16px;
    }

    .btn {
        font-size: 13px;
    }

    .table th, 
    .table td {
        font-size: 12px;
        padding: 8px;
    }

    /* Slight scroll if needed */
    .table {
        display: block;
        overflow-x: auto;
    }
}


/* ================================
   LARGE DEVICES (SMALL LAPTOPS ≥992px)
================================ */
@media (min-width: 992px) and (max-width: 1199.98px) {

    .table th, 
    .table td {
        font-size: 12px;
        padding: 10px;
    }
}


/* ================================
   EXTRA LARGE DEVICES ≥1200px
================================ */
@media (min-width: 1200px) {

    .table th, 
    .table td {
        font-size: 13px;
        padding: 10px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
<style>
    .table th, .table td {
            padding: 0.72rem 10px;
            font-size: 10px;
            vertical-align: middle;
        }
        
        .table thead .sorting_asc {
  background-image: url(assets/sort-ascending.png);
  background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}
.table thead .sorting_desc {
  background-image: url(assets/sort-descending.png);
  background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}
.table .sorting{
  background-image: url(assets/sort-descending.png);
 background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}

.no-sort{
         background-image: url(assad) !important
}
</style>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Orders
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">

   <?php if(isset($_GET['history'])){ ?>
                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       <div class="col-12">
                           <?php echo flash_msg(); ?>
                            <form action="" method="GET" style="margin-bottom: 10px;">
                                <div class="row">
                                   
                                    <div class="col-sm-4 col-12" >
                                        <input type="datetime-local" class="form-control" name="frmDate" value="<?php echo htmlspecialchars($_GET['frmDate'] ?? ''); ?>" required/>
                                    </div>
                                    <div class="col-sm-4 col-12" >
                                        <input type="datetime-local" class="form-control" name="toDate" value="<?php echo htmlspecialchars($_GET['toDate'] ?? ''); ?>" required/>
                                    </div>
                                    <div class="col-sm-4 col-12">
                                        <input name="filter" value="1" type="hidden">
                                        <input name="history" value="1" type="hidden">
                                        <button type="submit" class="btn btn-primary" style="margin-right:10px" >Seach Orders</button>
                                    </div>
                                    
                                </div>
                            </form>
                           
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Reship Orders History</h4>
                                </div>
                                <script>
                                    function submitPrint(val){
                                        document.getElementById("labeltype").value=val;
                                        var form = document.getElementById("allordersdata");
                                        form.submit();
                                    }
                                </script>
                                <div class="card-datatable">
                                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <?php if(in_array('CreatedTime', $pageArray ?? [])){ ?><th style="width:4%;">Date/Time</th><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray ?? [])){ ?><th style="width:4%;">Order #</th><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray ?? [])){ ?><th style="width:11%;">Buyer Userid</th><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray ?? [])){ ?><th style="width:30%;">Item Name</th><?php } ?>
                                                <?php if(in_array('SKU', $pageArray ?? [])){ ?><th style="width:8%;">SKU</th><?php } ?>
                                                <?php if(in_array('Total', $pageArray ?? [])){ ?><th style="width:6%;">P</th><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray ?? [])){ ?><th style="width:6%;">SP</th><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray ?? [])){ ?><th style="width:6%;">PC/ZP</th><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray ?? [])){ ?><th style="width:10%;">SM</th><?php } ?>
                                                <?php if(in_array('Country', $pageArray ?? [])){ ?><th style="width:6%;">CT</th><?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        if(isset($_GET['frmDate']) && isset($_GET['toDate'])){
                                            $frmDate = date('Y-m-d H:i:s', strtotime($_GET['frmDate']));
                                            $toDate = date('Y-m-d H:i:s', strtotime($_GET['toDate']));
                                            $query = "select * from app_orders WHERE IsInReship='2' && CreatedTime >= '$frmDate' && CreatedTime <= '$toDate' && IsArchived = '0' ORDER BY ID DESC";
                                        }else{
                                            $query = "select * from app_orders WHERE IsInReship='2' && ID < 1 && IsArchived = '0' ORDER BY ID DESC";
                                        }
                                            
                                        $today = date('Y-m-d');
                                        $orders = $conn->query($query);
                                        $sn = 0;
                                        while($order = $orders->fetch_assoc()){
                                            $account = $conn->query("select * from app_accounts where id = '".($order['AccountID'] ?? '')."'")->fetch_assoc();
                                            $shipa = json_decode($order['ShippingAddress'] ?? '{}', true) ?: [];
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '".($order['OrderID'] ?? '')."'");
                                            $sku = '';
                                            $showItem = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= htmlspecialchars($item['ItemTitle'] ?? '').' x '.htmlspecialchars($item['QuantityPurchased'] ?? '').'<br>';
                                                $sku .= htmlspecialchars($item['SKU'] ?? '').'<br>';
                                            }
                                            $sn++; ?>
                                    	    <tr>
                                    	        <?php if(in_array('CreatedTime', $pageArray ?? [])){ ?><td><?php echo date('d/M H:i', strtotime($order['CreatedTime'] ?? '')); ?></td><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['OrderID'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['BuyerUserID'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray ?? [])){ ?>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if(!empty($order['BuyerCheckoutMessage'])){
                                                    echo '<br><span style="color:red">Buyer Note: '.htmlspecialchars($order['BuyerCheckoutMessage']).'</span>';
                                                }?>
                                                </td>
                                                <?php } ?>
                                                <?php if(in_array('SKU', $pageArray ?? [])){ ?><td><?php echo $sku; ?></td><?php } ?>
                                                <?php if(in_array('Total', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['Total'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['ShippingServiceCost'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($shipa['PostalCode'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray ?? [])){ ?><td><?php echo substr(htmlspecialchars($order['ShippingService'] ?? ''), 0, 16); ?></td><?php } ?>
                                                <?php if(in_array('Country', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($shipa['Country'] ?? ''); ?></td><?php } ?>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
   <?php }else{ ?>            
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Unshipped Orders</h4>
                                    
                                    <div>
                                        <button type="button" onclick="window.location.href='reship_orders.php?history=1'" class="btn btn-primary" style="float:right;margin-right:10px">Reship History</button>
                                        <div class="btn-group" style="float:right;margin-right:10px">
                                        <button  class="btn btn-primary dropdown-toggle waves-effect waves-float waves-light" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Print Label
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(1)">DC UNG</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(2)">DC UNH</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(3)">CSV</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(100)">Archive</a>
                                        </div>
                                    </div>
                                    </div>
                                    
                                </div>
                                <script>
    function submitPrint(val){
        document.getElementById("labeltype").value=val;
        var form = document.getElementById("allordersdata");
        form.submit();
    }
</script>
                                <div class="card-datatable">
                                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <?php if(in_array('CreatedTime', $pageArray ?? [])){ ?><th style="width:4%;">Date/Time</th><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray ?? [])){ ?><th style="width:4%;">Order #</th><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray ?? [])){ ?><th style="width:11%;">Buyer Userid</th><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray ?? [])){ ?><th style="width:30%;">Item Name</th><?php } ?>
                                                <?php if(in_array('SKU', $pageArray ?? [])){ ?><th style="width:8%;">SKU</th><?php } ?>
                                                <?php if(in_array('Total', $pageArray ?? [])){ ?><th style="width:6%;">P</th><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray ?? [])){ ?><th style="width:6%;">SP</th><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray ?? [])){ ?><th style="width:6%;">PC/ZP</th><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray ?? [])){ ?><th style="width:10%;">SM</th><?php } ?>
                                                <?php if(in_array('Country', $pageArray ?? [])){ ?><th style="width:6%;">CT</th><?php } ?>
                                                <th><input type="checkbox" id="selectall"/></th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $query = "select * from app_orders WHERE IsInReship='1' && IsArchived = '0' ORDER BY ID DESC";
                                        
                                        $today = date('Y-m-d');
                                        $orders = $conn->query($query);
                                        $sn = 0;
                                        while($order = $orders->fetch_assoc()){
                                            $account = $conn->query("select * from app_accounts where id = '".($order['AccountID'] ?? '')."'")->fetch_assoc();
                                            $shipa = json_decode($order['ShippingAddress'] ?? '{}', true) ?: [];
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '".($order['OrderID'] ?? '')."'");
                                            $sku = '';
                                            $showItem = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= htmlspecialchars($item['ItemTitle'] ?? '').' x '.htmlspecialchars($item['QuantityPurchased'] ?? '').'<br>';
                                                $sku .= htmlspecialchars($item['SKU'] ?? '').'<br>';
                                            }
                                            $sn++; ?>
                                    	    <tr>
                                    	        <?php if(in_array('CreatedTime', $pageArray ?? [])){ ?><td><?php echo date('d/M H:i', strtotime($order['CreatedTime'] ?? '')); ?></td><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['OrderID'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['BuyerUserID'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray ?? [])){ ?>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if(!empty($order['BuyerCheckoutMessage'])){
                                                    echo '<br><span style="color:red">Buyer Note: '.htmlspecialchars($order['BuyerCheckoutMessage']).'</span>';
                                                }?>
                                                </td>
                                                <?php } ?>
                                                <?php if(in_array('SKU', $pageArray ?? [])){ ?><td><?php echo $sku; ?></td><?php } ?>
                                                <?php if(in_array('Total', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['Total'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($order['ShippingServiceCost'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($shipa['PostalCode'] ?? ''); ?></td><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray ?? [])){ ?><td><?php echo substr(htmlspecialchars($order['ShippingService'] ?? ''), 0, 16); ?></td><?php } ?>
                                                <?php if(in_array('Country', $pageArray ?? [])){ ?><td><?php echo htmlspecialchars($shipa['Country'] ?? ''); ?></td><?php } ?>
                                                <td><input type="checkbox" class="case" name="aorder[]" value="<?php echo htmlspecialchars($order['ID'] ?? ''); ?>"/></td>
                                                <td>
                                                    <button type="button" onclick="editOrder(<?php echo htmlspecialchars($order['ID'] ?? ''); ?>)" class="btn btn-icon btn-icon rounded-circle btn-flat-success waves-effect">
                                                            Edit
                                                    </button></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
    <?php } ?>
            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div style="display:none;">
                       <button type="button" id="openmodelbtn" class="btn btn-outline-primary waves-effect" data-toggle="modal" data-target="#editModel">
                           Click
                        </button>
                   </div>
<div class="modal fade text-left" id="editModel" tabindex="-1" aria-labelledby="myModalLabel33" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Edit Address</h4>
                <button type="button" id="closemodelbtn" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="" onsubmit="return false;" method="post" id="editOrderForm">
                <input type="hidden" name="editId" value="" id="editId"/>
                <div class="modal-body" id="editInputs">
                    
                   
                </div>
                <div class="modal-footer" id="saveBtnDiv">
                    <button type="button" onclick="saveOrder()" class="btn btn-primary waves-effect waves-float waves-light" data-dismiss="modal">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

     <!-- BEGIN: Vendor JS-->
     <script src="app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!-- BEGIN: Page Vendor JS-->
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <script src="app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    
    <script src="app-assets/vendors/js/editors/quill/katex.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/highlight.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/quill.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>


    <!-- BEGIN: Page JS-->
    <script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });
        
        $("#selectall").click(function () {
                var checkAll = $("#selectall").prop('checked');
                    if (checkAll) {
                        $(".case").prop("checked", true);
                    } else {
                        $(".case").prop("checked", false);
                    }
            });

            $(".case").click(function(){
                if($(".case").length == $(".case:checked").length) {
                    $("#selectall").prop("checked", true);
                } else {
                    $("#selectall").prop("checked", false);
                }

            });
            
        function editOrder(id) {
            $("#openmodelbtn").click();
            $("#editInputs").html('<center><img src="app-assets/ajax_loading.gif" /></center>');
            $.ajax({
                    url : "inc/ajax.php",
                    method : "POST",
                    data : {editAddress: id},
                    async : true,
                    dataType : 'html',
                    success: function(data){
                        // console.log(data);
                       $("#editId").val(id);
                       $("#editInputs").html(data);
                        
                    }
                });
        }
        
        function saveOrder(){
            var data = $("#editOrderForm").serialize();
            $.ajax({
                    url : "inc/ajax.php?postEditAddress=1",
                    method : "POST",
                    data : data,
                    async : true,
                    success: function(data){
                        console.log(data);
                    }
            });
        }
        
    function printDiv(divName) {
         var printContents = document.getElementById(divName).innerHTML;
         var originalContents = document.body.innerHTML;
         document.body.innerHTML = printContents;
        //  window.print();
        //  document.body.innerHTML = originalContents;
    }
    
    function exportReportToExcel(divName) {
      let table = document.getElementById("ptbl"); // you can use document.getElementById('tableId') as well by providing id to the table tag
      TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
        name: `export.xlsx`, // fileName you could use any name
        sheet: {
          name: 'Sheet 1' // sheetName
        }
      });
    }
    
     $(".dt-row-grouping-t").DataTable({
            "bPaginate": false, //hide pagination
            "bInfo": false, // hide showing entries
        });
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
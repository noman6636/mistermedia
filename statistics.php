<?php
require_once "inc/config.php";
require_once "inc/functions.php";
$LIMIT = 10;
$time_start = microtime(true);

// Check if admin_id is set in session
if (empty($_SESSION['admin_id'])) {
    header("location: login.php");
    exit();
}

// Check if permissions_allow exists and has the required values
if ((empty($permissions_allow) || (!in_array(27, $permissions_allow) && !in_array(30, $permissions_allow)))) {
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if (isset($_GET['items_csv'])) {
    $data = array();
    $items = $conn->query("SELECT * FROM app_items WHERE deleted = 0 ORDER BY sku ASC");
    
    if ($items) {
        while ($item = $items->fetch_assoc()) {
            $itemData = array();
            $itemData['SKU'] = $item['sku'] ?? '';
            
            // Fetch package size name
            $packageSizeName = '';
            if (!empty($item['packing_size_id'])) {
                $sizeQuery = $conn->query("SELECT name FROM app_packing_sizes WHERE id = {$item['packing_size_id']}");
               
                if ($sizeQuery && $sizeQuery->num_rows > 0) {
                    $sizeRow = $sizeQuery->fetch_assoc();
                    $packageSizeName = $sizeRow['name'] ?? '';
                    
                }
            }
            $item_id = $item['id'];
            $DPrice = $conn->query("
                        SELECT *
                        FROM app_sellprices_amount
                        WHERE item_id = '$item_id' AND name_id = 1
                        LIMIT 1
                    ")->fetch_assoc();
              
                     
            $newcostPrice = $conn->query("
                        SELECT *
                        FROM app_sellprices_amount
                        WHERE item_id = '$item_id' AND name_id = 8
                        LIMIT 1
                    ")->fetch_assoc();
            $vatPrice = $conn->query("
                        SELECT *
                        FROM app_sellprices_amount
                        WHERE item_id = '$item_id' AND name_id = 2
                        LIMIT 1
                    ")->fetch_assoc();
                    
            $itemData['Package_Size'] = $packageSizeName;
            
            if (isset($item['item_type']) && $item['item_type'] == 1) {
                $dataStat = json_decode($item['statistics'] ?? '{}', true) ?? array();
                
                $itemData['Remaining_Stock'] = $dataStat['remain_stock'] ?? 0;
                $itemData['Upcoming_Stock'] = $dataStat['upcoming_stock'] ?? 0;
                $itemData['Cost_Price_(£)'] = $dataStat['cost_price'] ?? 0;
                $itemData['Cost_Price_($)'] = $newcostPrice['price'] ?? 0;
                $itemData['Default_Price'] = $DPrice['price'] ?? 0;
                $itemData['Vat_Price'] = $vatPrice['price'] ?? 0;
                
                $itemData['Available_Stock_Amount'] = ($itemData['Remaining_Stock'] ?? 0) * ($dataStat['cost_price'] ?? 0);
                $itemData['Upcoming_Stock_Amount'] = ($itemData['Upcoming_Stock'] ?? 0) * ($dataStat['cost_price'] ?? 0);
            } else {
                $itemData['Remaining_Stock'] = 0;
                $itemData['Upcoming_Stock'] = 0;
                $itemData['Cost_Price'] = 0;
                $itemData['Available_Stock_Amount'] = 0;
                $itemData['Upcoming_Stock_Amount'] = 0;
                $itemData['Default_Price'] = 0;
                // $itemData['New_Cost_Price'] =  0;
                $itemData['Vat_Price'] =  0;
            }
            
            $data[] = $itemData;
        }
    }

    $dateNow = date('Y-m-d H:i:s');
    addSystemLog($conn, 'CSV DOWNLOAD', "User has been downloaded csv of (ItemsList-$dateNow)", "");

    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="ItemsList-' . $dateNow . '.csv";');
    $output = fopen('php://output', 'w');

    $keysPut = 0;
    foreach ($data as $product) {
        if ($keysPut == 0) {
            fputcsv($output, array_keys($product));
            $keysPut = 1;
        }
        fputcsv($output, $product);
    }
    fclose($output);
    exit();
}

if (isset($_GET['delete'])) {
    $deleteId = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
    if ($deleteId) {
        $conn->query("UPDATE app_items SET deleted = 1 WHERE id = '{$deleteId}'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been deleted</div></div>';
        header("location: statistics.php?items=1");
        exit();
    }
}

if (isset($_GET['recover'])) {
    $recoverId = filter_var($_GET['recover'], FILTER_SANITIZE_NUMBER_INT);
    if ($recoverId) {
        $conn->query("UPDATE app_items SET deleted = 0 WHERE id = '{$recoverId}'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been recovered</div></div>';
        header("location: statistics.php?items=1&deleted=1");
        exit();
    }
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
    <title>Statistics || IConnect</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <style>
        /* ================= MOBILE ================= */
@media (max-width: 768px) {

    /* Navbar */
    .header-navbar {
        padding: 8px 10px;
    }

    .user-nav {
        display: none !important;
    }

    .avatar img {
        width: 35px;
        height: 35px;
    }

    /* Sidebar */
    .main-menu {
        transform: translateX(-100%);
        position: fixed;
        z-index: 9999;
        width: 260px;
        transition: 0.3s;
    }

    body.menu-expanded .main-menu {
        transform: translateX(0);
    }

    /* Content full width */
    .app-content {
        margin-left: 0 !important;
        padding: 10px;
    }

    /* Cards (top stats) */
    .card-shadow {
        height: auto !important;
        padding: 15px !important;
    }

    .card-shadow h2 {
        font-size: 18px !important;
    }

    .card-shadow img {
        width: 40px !important;
        height: 40px !important;
    }

    /* Reorder / Lowstock / Outofstock cards */
    .col-md-4 {
        width: 100%;
        max-width: 100%;
        flex: 100%;
    }

    .card-body {
        height: auto !important;
    }

    /* Tables scroll */
    .card-datatable {
        overflow-x: auto;
    }

    table {
        min-width: 700px;
    }

    /* Buttons group */
    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .btn {
        margin-bottom: 5px;
        width: 100%;
    }
}
@media (max-width: 768px) {
    .row > div {
        margin-bottom: 15px;
    }
}
    </style>

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">

                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><h4 class="text-shadow">Dashboard</h4></a>
                                    </li>
                                    <li class="breadcrumb-item active"><h4 class="text-shadow-none">Statistics</h4></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <?php if (isset($_GET['items'])) { ?>

                    <section id="page-account-settings" style="margin-top:20px">

                        <?php
                        $reorders = array();
                        $lowstock = array();
                        $outofstock = array();
                        $itemsList = array();
                        
                        $query = isset($_GET['deleted']) ? 
                            "SELECT * FROM app_items WHERE deleted = 1 ORDER BY sku ASC" : 
                            "SELECT * FROM app_items WHERE deleted = 0 ORDER BY sku ASC";
                            
                        $items = $conn->query($query);
                        
                        $sn = 0;
                        $today = date('Y-m-d');
                        $total_qty = 0;
                        $total_amount = 0;
                        $total_available_stock_amount = 0;
                        $total_upcoming_stock_amount = 0;
                        
                        
                        if ($items) {
                            while ($item = $items->fetch_assoc()) {
                                $itemsArray = array();
                                $remain_stock = 0;
                                $upcoming_stock = 0;
                                $cost_price = 0;
                                $item_id = $item['id'];
                                
                                $VatPrice = $conn->query("
                                    SELECT *
                                    FROM app_sellprices_amount
                                    WHERE item_id = '$item_id' AND name_id = 2
                                    LIMIT 1
                                ")->fetch_assoc();
                          
                                 
                                $FbaPrice = $conn->query("
                                    SELECT *
                                    FROM app_sellprices_amount
                                    WHERE item_id = '$item_id' AND name_id = 5
                                    LIMIT 1
                                ")->fetch_assoc();
                                
                                if (isset($item['item_type']) && $item['item_type'] == 1) {
                                    $dataStat = json_decode($item['statistics'] ?? '{}', true) ?? array();
                                    $remain_stock = $dataStat['remain_stock'] ?? 0;
                                    $upcoming_stock = $dataStat['upcoming_stock'] ?? 0;
                                    $cost_price = $dataStat['cost_price'] ?? 0;
                                }
                                
                                $total_available_stock_amount += ($remain_stock * $cost_price);
                                $total_upcoming_stock_amount += ($upcoming_stock * $cost_price);
                                $sn++;
                                
                                $itemsArray['id'] = $item['id'] ?? 0;
                                $itemsArray['item'] = $item['sku'] ?? '';
                                $itemsArray['qty'] = $remain_stock;
                                $itemsArray['sku'] = $item['sku'] ?? '';
                                $itemsArray['name'] = $item['name'] ?? '';
                                $itemsArray['price'] = $item['price'] ?? 0;
                                $itemsArray['vat_price'] = $VatPrice['price'] ?? 0;
                                $itemsArray['fba_price'] = $FbaPrice['price'] ?? 0;
                                $itemsArray['image'] = $item['image'] ?? 'https://cdn-icons-png.flaticon.com/128/1829/1829586.png';
                                $itemsArray['deleted'] = $item['deleted'] ?? 0;
                                $itemsArray['inhide_outofstock'] = ($remain_stock < 0) ? 0 : ($item['inhide_outofstock'] ?? 0);
                                $itemsArray['inhide_lowstock'] = $item['inhide_lowstock'] ?? 0;
                                $itemsArray['inhide_reorder'] = $item['inhide_reorder'] ?? 0;
                                $itemsArray['upcoming'] = $upcoming_stock;
                                $itemsArray['reference'] = $item['reference'];
                                
                                
                                $itemsList[] = $itemsArray;
                           
                                
                                $order_threshold = $item['order_threshold'] ?? 0;
                                $stock_threshold = $item['stock_threshold'] ?? 0;
                                
                                if (($remain_stock + $upcoming_stock) <= $order_threshold) {
                                    $reorders[] = $itemsArray;
                                }
                                if ($remain_stock <= $stock_threshold && $remain_stock > 1) {
                                    $lowstock[] = $itemsArray;
                                }
                                if ($remain_stock < 1) {
                                    $outofstock[] = $itemsArray;
                                }
                            }
                        }
                        ?>
                        <div class="row mb-3">

                            <div class="col-sm-6 col-12">
                                <div class="card-shadow card-shadow-hover" style="width:100%; height:200px;background: linear-gradient(to right, #A2C2E8, #F4B6C2);border-radius: 15px;padding: 27px 40px; ">
                                    <h2 style="text-align: center;color: white;font-size: 70px;margin-bottom:15px"><img src="https://cdn-icons-png.flaticon.com/128/5166/5166961.png" loading="lazy" alt="Ready stock " title="Ready stock " width="64" height="64"></h2>
                                    <h2 style="text-align:center; color:white;font-size: 16px;">Available stock</h2>
                                    <h2 style="text-align:center; color:white;font-size: 16px; font-weight: bold;" id="avaiable_amount"><?= round($total_available_stock_amount); ?></h2>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="card-shadow card-shadow-hover" style="width:100%; height:200px;background: linear-gradient(to right, #FFB3AB, #FFDDC1);border-radius: 15px;padding: 27px 40px;">
                                    <h2 style="text-align: center;color: white;font-size: 70px;margin-bottom:15px"><img src="https://cdn-icons-png.flaticon.com/128/5166/5166936.png" loading="lazy" alt="New product " title="New product " width="64" height="64"></h2>
                                    <h2 style="text-align:center; color:white;font-size: 16px;">Upcoming Stock</h2>
                                    <h2 style="text-align:center; color:white;font-size: 16px; font-weight: bold;" id="upcomming_amount"><?= round($total_upcoming_stock_amount); ?></h2>
                                </div>
                            </div>

                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <!-- right content section -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Re-Orders</h4>
                                        <button type="submit" class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" data_type="1" id="btnReOrder" onclick="showHideReOrder()">Hidden Items</button></td>
                                    </div>
                                    <div class="card-body" style="padding: 0px;height: 250px;overflow-y: auto;">
                                        <table class="dt-row-grouping-st table">
                                            <thead>
                                                <tr>
                                                    <th style="width:70%">Item</th>
                                                    <th style="width:30%">Qty</th>
                                                    <?php if (in_array(27, $permissions_allow)) { ?><th></th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody id="reorder_showen">
                                                <?php foreach ($reorders as $order) {
                                                    if ($order['inhide_reorder'] == 0) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_reorder(this, <?= $order['id']; ?>, 1)"><i data-feather='eye-off'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                            <tbody style="display:none" id="reorder_hidden">
                                                <?php foreach ($reorders as $order) {
                                                    if ($order['inhide_reorder'] == 1) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_reorder(this, <?= $order['id']; ?>, 0)"><i data-feather='eye'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Low Stock</h4>
                                        <button type="submit" class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" data_type="1" id="btnLowStock" onclick="showHideLowStock()">Hidden Items</button></td>

                                    </div>
                                    <div class="card-body" style="padding: 0px;height: 250px;overflow-y: auto;">
                                        <table class="dt-row-grouping-st table">
                                            <thead>
                                                <tr>
                                                    <th style="width:70%">Item</th>
                                                    <th style="width:30%">Qty</th>
                                                    <?php if (in_array(27, $permissions_allow)) { ?><th></th><?php } ?>
                                                </tr>
                                            </thead>

                                            <tbody id="lowstock_showen">
                                                <?php foreach ($lowstock as $order) {
                                                    if ($order['inhide_lowstock'] == 0) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_lowstock(this, <?= $order['id']; ?>, 1)"><i data-feather='eye-off'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                            <tbody style="display:none" id="lowstock_hidden">
                                                <?php foreach ($lowstock as $order) {
                                                    if ($order['inhide_lowstock'] == 1) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_lowstock(this, <?= $order['id']; ?>, 0)"><i data-feather='eye'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Out Of Stock</h4>
                                        <button type="submit" class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" data_type="1" id="btnOutofStock" onclick="showHideOutofStock()">Hidden Items</button></td>
                                    </div>
                                    <div class="card-body" style="padding: 0px;height: 250px;overflow-y: auto;">
                                        <table class="dt-row-grouping-st table">
                                            <thead>
                                                <tr>
                                                    <th style="width:70%">Item</th>
                                                    <th style="width:30%">Qty</th>
                                                    <?php if (in_array(27, $permissions_allow)) { ?><th></th> <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody id="outofstock_showen">
                                                <?php foreach ($outofstock as $order) {
                                                    if ($order['inhide_outofstock'] == 0) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_outofstock(this, <?= $order['id']; ?>, 1)"><i data-feather='eye-off'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                            <tbody style="display:none" id="outofstock_hidden">
                                                <?php foreach ($outofstock as $order) {
                                                    if ($order['inhide_outofstock'] == 1) { ?>
                                                        <tr>
                                                            <td><?= $order['item']; ?></td>
                                                            <td><?= $order['qty']; ?></td>
                                                            <?php if (in_array(27, $permissions_allow)) { ?><td><button type="submit" class="btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light" onclick="inhide_outofstock(this, <?= $order['id']; ?>, 0)"><i data-feather='eye'></i></button></td><?php } ?>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!--/ right content section -->
                        </div>
                    </section>



                    <section id="row-grouping-datatable" style="margin-top:20px">
                        <div class="row" style="margin-top:10px;">

                            <div class="col-12">
                                <form action="" id="allordersdata" method="POST">
                                    <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                                    <div class="card">
                                        <div class="card-header border-bottom">
                                            <h4 class="card-title">List of Items</h4>
                                            <!--<?php if (isset($_GET['deleted'])) { ?>-->
                                            <!--    <button type="button" class="btn rounded-pill btn-success waves-effect waves-light" style="margin-left: auto;margin-right: 10px;" data_type="1" onclick="window.location.href='statistics.php?items=1'">All SKUs</button></td>-->
                                            <!--<?php } else { ?>-->
                                            <!--    <button type="button" class="btn rounded-pill btn-danger waves-effect waves-light" style="margin-left: auto;margin-right: 10px;" data_type="1" onclick="window.location.href='statistics.php?items=1&deleted=1'">Deleted SKUs</button></td>-->
                                            <!--<?php } ?>-->

                                            <!--<button type="button" class="btn rounded-pill btn-warning waves-effect waves-light" data_type="1" onclick="window.location.href='statistics.php?items_csv=1'">Download Csv</button></td>-->
                                            
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-warning waves-effect waves-light" data_type="1" onclick="window.location.href='statistics.php?items_csv=1'">Download Csv</button>
                                                <?php if (isset($_GET['deleted'])) { ?>
                                                <button type="button" class="btn btn-success waves-effect waves-light" data_type="1" onclick="window.location.href='statistics.php?items=1'">All SKUs</button>
                                                <?php } else { ?>
                                                <button type="button" class="btn btn-danger waves-effect waves-light" data_type="1" onclick="window.location.href='statistics.php?items=1&deleted=1'">Deleted SKUs</button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        
                                        <div class="card-datatable pr-2 pl-2 table-responsive">
                                            <table id="loi" class="dt-row-grouping-t table">
                                                <thead>
                                                    <tr>
                                                        <th>Sn</th>
                                                        <th>Image</th>
                                                        <th>SKU</th>
                                                        <th>Reference</th>
                                                        <th>FBA Price</th>
                                                        <th>VAT</th>
                                                        <th>Price</th>
                                                        <th>Total Stock</th>
                                                        <th>OTW</th>
                                                        <?php if (in_array(27, $permissions_allow)) { ?><th width="10%">Action</th><?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
<?php
$sn = 0;
$total_qty = 0;
$total_amount = 0;
foreach ($itemsList as $item) {
    $sn++;
    $total_qty += $item['qty'];
    $total_amount += ($item['qty'] * $item['price']);
?>
    <tr>
        <td><?= $sn; ?></td>
        <td><img src="<?= $item['image'] != '' ? 'items_image/' . $item['image'] : 'https://cdn-icons-png.flaticon.com/128/1829/1829586.png'; ?>" style="width:50px;"></td>
        <td><?= $item['sku']; ?></td>
        <td><?=$item['reference']?></td>
        <td><?= $item['fba_price']; ?></td>
        <td><?= $item['vat_price']; ?></td>
        <td><?= $item['price']; ?></td>
        <td><?= $item['qty']; ?></td>
        <td><span style="color:green"><a href="manage_purchase.php?item_id=<?= $item['id']; ?>">(<?= $item['upcoming']; ?>)</a></span></td>

        <?php if (in_array(27, $permissions_allow)) { ?>
        <td style="display: flex;">
            <a type="button" href="add_item.php?edit=<?= $item['id']; ?>" class="btn btn-primary btn-sm" style="margin-right: 5px;">
                <img src="https://cdn-icons-png.flaticon.com/128/1827/1827933.png" loading="lazy" alt="Edit " title="Edit " width="20" height="20">
            </a>

            <?php if ($item['deleted'] == 1) { ?>
                <a type="button" href="?recover=<?= $item['id']; ?>" class="btn btn-sm">
                    <img src="https://cdn-icons-png.flaticon.com/128/13386/13386428.png" loading="lazy" alt="Arrows " title="Arrows " width="20" height="20">
                </a>
            <?php } else { ?>
                <a type="button" 
                   href="?delete=<?= $item['id']; ?>" 
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Are you sure you want to delete this item?');">
                    <img src="https://cdn-icons-png.flaticon.com/128/3405/3405244.png" loading="lazy" alt="Delete " title="Delete " width="21" height="21">
                </a>
            <?php } ?>
        </td>
        <?php } ?>
    </tr>

<?php } ?>


                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" style="font-size:20px; color:red">Total :</td>
                                                        <td style="font-size:20px; color:red"><?= $total_qty; ?></td>
                                                        <td style="font-size:20px; color:red"><?= $total_amount; ?></td>
                                                        <td style="display:none"></td>
                                                        <td style="display:none"></td>
                                                        <td style="display:none"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>

                <?php } else if (isset($_GET['payables'])) { ?>
                    <section id="row-grouping-datatable" style="margin-top:20px">

                        <div class="row">

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Accounts Payable</h4>
                                        <h4 style="float:right;background: #1192d2;color: white;padding: 10px;" id="total_amount">Total Amount : 0</h4>
                                    </div>
                                    <div class="card-datatable">
                                        <table class="dt-row-grouping-t table">
                                            <thead>
                                                <tr>
                                                    <th style="width:15%">Sn</th>
                                                    <th>Account Username</th>
                                                    <th>Amount Due</th>
                                                    <th>Last Paid</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                                                $sn = 0;
                                                $total_amount = 0;
                                                while ($account = $accounts->fetch_assoc()) {
                                                    $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}' &&  type = '1' && status != 100")->fetch_assoc()['amount'] + 0;
                                                    $balance = $totalPayments + $account['balance'];
                                                    $last_paid = $conn->query("SELECT * FROM `app_payments` where account_id = '{$account['id']}' ORDER BY datetime DESC");
                                                    if ($last_paid->num_rows > 0) {
                                                        $last_paid = $last_paid->fetch_assoc();
                                                        $last_paid_date = date('Y-m-d', strtotime($last_paid['datetime']));
                                                        $now = time(); // or your date as well
                                                        $ls_date = strtotime($last_paid_date);
                                                        $datediff = $now - $ls_date;
                                                        $daysDiff = round($datediff / (60 * 60 * 24));
                                                    } else {
                                                        $last_paid_date = 'Never';
                                                        $daysDiff = 0;
                                                    }




                                                    if (abs($balance) >= $account['amount_threshold'] || $daysDiff >= $account['days_threshold']) {
                                                        $sn++;
                                                        $total_amount += $balance;
                                                ?>

                                                        <tr>
                                                            <td><?php echo $sn; ?></td>
                                                            <td><?php echo $account['account_name']; ?> <?php if ($account['account_username'] != '') {
                                                                                                            echo '(' . $account['account_username'] . ')';
                                                                                                        } ?></td>
                                                            <td><?php if ($balance > 0) {
                                                                    echo '<span style="color:green">' . number_format($balance, 2) . '</span>';
                                                                } else {
                                                                    echo '<span style="color:red">' . number_format(abs($balance), 2) . '</span>';
                                                                } ?></td>
                                                            <td><?= $last_paid_date; ?></td>

                                                        </tr>
                                                <?php }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                <?php } ?>

                <?php
                $time_end = microtime(true);

                //dividing with 60 will give the execution time in minutes otherwise seconds
                $execution_time = ($time_end - $time_start);

                //execution time of the script
                echo '<b>Total Execution Time:</b> ' . $execution_time . ' Seconds';
                ?>
            </div>
        </div>
    </div>
    <div id="printableArea" style="display:none">


    </div>
    <!-- END: Content-->

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
<script src="app-assets/vendors/js/vendors.min.js"></script>

<!-- Other vendor scripts -->
<script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
<script src="app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <!-- BEGIN: Page JS-->
    <script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>

    <?php if (isset($_GET['payables'])) { ?>

        <script>
            $(window).on('load', function() {
                $("#total_amount").html("Total Amount : <?php echo number_format(abs($total_amount), 2); ?>");
            });
        </script>

    <?php } ?>

    <script>
    $(document).ready(function() {
        $('#loi').DataTable({lengthMenu: [
            [10, 25, 50, 100, -1],
            ['10', '25', '50', '100', 'All']
        ],pageLength: 10});
//     // Setup column search filters (optional)
//     $('#loi thead th').each(function () {
//         var title = $(this).text();
//         if (title !== "Action") { // Skip action column
//             $(this).html(title + '<br><input type="text" placeholder="Search ' + title + '" style="width: 100%;" />');
//         }
//     });

//     var table = $('#loi').DataTable({
//         dom: 'Bfrtip',
//         buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
//         responsive: true,

//         // ✅ Add "Show N rows" dropdown
//         lengthMenu: [
//             [10, 25, 50, 100, -1],
//             ['10', '25', '50', '100', 'All']
//         ],
//         pageLength: 10, // Default number of rows

//         initComplete: function () {
//             console.log('DataTable initialized');
//         }
//     });

//     // Column-specific search
//     table.columns().every(function () {
//         var that = this;
//         $('input', this.header()).on('keyup change clear', function () {
//             if (that.search() !== this.value) {
//                 that.search(this.value).draw();
//             }
//         });
//     });
});

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
        
        function inhide_outofstock(e, id, val) {
            if (val == 1) {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_outofstock(this, " + id + ", 0)");
                $("#outofstock_hidden").append(tr);

            } else {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye-off'].toSvg())
                    .attr("onclick", "inhide_outofstock(this, " + id + ", 1)");
                $("#outofstock_showen").append(tr);

            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                url: "inc/ajax.php",
                method: "POST",
                data: {
                    inhide_outofstock: id,
                    value: val
                },
                async: true,
                success: function(data) {
                    console.log(data);

                }
            });
        }

        function inhide_lowstock(e, id, val) {
            if (val == 1) {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_lowstock(this, " + id + ", 0)");
                $("#lowstock_hidden").append(tr);

            } else {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye-off'].toSvg())
                    .attr("onclick", "inhide_lowstock(this, " + id + ", 1)");
                $("#lowstock_showen").append(tr);

            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                url: "inc/ajax.php",
                method: "POST",
                data: {
                    inhide_lowstock: id,
                    value: val
                },
                async: true,
                success: function(data) {
                    console.log(data);

                }
            });
        }

        function inhide_reorder(e, id, val) {
            if (val == 1) {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_reorder(this, " + id + ", 0)");
                $("#reorder_hidden").append(tr);

            } else {
                var tr = $(e).closest("tr").remove().clone();
                tr.find("button")
                    .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye-off'].toSvg())
                    .attr("onclick", "inhide_reorder(this, " + id + ", 1)");
                $("#reorder_showen").append(tr);

            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                url: "inc/ajax.php",
                method: "POST",
                data: {
                    inhide_reorder: id,
                    value: val
                },
                async: true,
                success: function(data) {
                    console.log(data);

                }
            });
        }

        function showHideOutofStock() {
            var btnOutofStock_type = $("#btnOutofStock").attr("data_type");
            if (btnOutofStock_type == 1) {
                $("#btnOutofStock").html("Back");
                $("#btnOutofStock").attr("data_type", "0");
                $("#outofstock_showen").hide();
                $("#outofstock_hidden").show();
            } else {
                $("#btnOutofStock").html("Hidden Items");
                $("#btnOutofStock").attr("data_type", "1");
                $("#outofstock_hidden").hide();
                $("#outofstock_showen").show();


            }
        }

        function showHideLowStock() {
            var btnLowStock_type = $("#btnLowStock").attr("data_type");
            if (btnLowStock_type == 1) {
                $("#btnLowStock").html("Back");
                $("#btnLowStock").attr("data_type", "0");
                $("#lowstock_showen").hide();
                $("#lowstock_hidden").show();
            } else {
                $("#btnLowStock").html("Hidden Items");
                $("#btnLowStock").attr("data_type", "1");
                $("#lowstock_hidden").hide();
                $("#lowstock_showen").show();


            }
        }

        function showHideReOrder() {
            var btnReOrder_type = $("#btnReOrder").attr("data_type");
            if (btnReOrder_type == 1) {
                $("#btnReOrder").html("Back");
                $("#btnReOrder").attr("data_type", "0");
                $("#reorder_showen").hide();
                $("#reorder_hidden").show();
            } else {
                $("#btnReOrder").html("Hidden Items");
                $("#btnReOrder").attr("data_type", "1");
                $("#reorder_hidden").hide();
                $("#reorder_showen").show();
            }
        }
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
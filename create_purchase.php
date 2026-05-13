<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Check permissions
if(empty($permissions_allow) || !in_array(22, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("Location: index.php");
    exit();
}

if(isset($_POST['create_purchase'])){
    // echo '$_POST: <pre>' .print_r($_POST,true). '</pre>'; die;
    // Validate and sanitize input
    $supplier = isset($_POST['supplier']) ? $conn->real_escape_string(trim($_POST['supplier'])) : '';
    $invoice_no = isset($_POST['invoice_no']) ? $conn->real_escape_string(trim($_POST['invoice_no'])) : '';
    $date = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : date('Y-m-d');
    $total_amount = isset($_POST['fld_grand_total_amount']) ? floatval($_POST['fld_grand_total_amount']) : 0;
    $created_date = date('Y-m-d H:i:s');
    
    // Validate required fields
    // if(empty($supplier) || empty($invoice_no) || $total_amount <= 0){
    //     $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Required fields are missing or invalid.</div></div>';
    //     header("Location: manage_purchase.php");
    //     exit();
    // }

    // Initialize arrays with empty values if not set
    $items = isset($_POST['item']) ? $_POST['item'] : [];
    $qtys = isset($_POST['qty']) ? $_POST['qty'] : [];
    $prices = isset($_POST['price']) ? $_POST['price'] : [];
    $totals = isset($_POST['total']) ? $_POST['total'] : [];
    $statuss = isset($_POST['status']) ? $_POST['status'] : [];

    // Check if items array is empty
    if(empty($items)){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">No items selected for purchase.</div></div>';
        header("Location: manage_purchase.php");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        $pquery = "INSERT INTO app_purchase SET supplier_id = '$supplier', invoice_no='$invoice_no', date = '$date', total_amount = '$total_amount', created_date = '$created_date'";
        
        if($conn->query($pquery)){
            $purchase_id = $conn->insert_id;
            $success = true;
            
            for ($i = 0, $n = count($items); $i < $n; $i++) {
                if(!isset($qtys[$i]) || !isset($prices[$i]) || !isset($totals[$i]) || !isset($statuss[$i])){
                    continue;
                }
                
                $qty = floatval($qtys[$i]);
                if($qty > 0){
                    $status = intval($statuss[$i]);
                    $item_id = intval($items[$i]);
                    $price = floatval($prices[$i]);
                    $total = floatval($totals[$i]);
                    
                    $detail_query = "INSERT INTO app_purchase_detail SET purchase_id = '$purchase_id', item_id = '$item_id', qty = '$qty', price = '$price', total = '$total', status = '$status'";
                    
                    if($conn->query($detail_query)){
                        $pdid = $conn->insert_id;
                        
                        if($status == 1){
                            $stock_query = "INSERT INTO app_stocks SET pid = '$purchase_id', pdid = '$pdid', item_id = '$item_id', description='Stock added from Purchase with invoice no $invoice_no', qty = '$qty', datetime = '$created_date'";
                            if(!$conn->query($stock_query)){
                                $success = false;
                                break;
                            }
                        }
                    } else {
                        $success = false;
                        break;
                    }
                }
            }
            
            if($success){
                $conn->commit();
                addSystemLog($conn, 'PURCHASE CREATED', "New purchase with id ($purchase_id) has been created", "");
                $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Purchase added successfully.</div></div>';
            } else {
                $conn->rollback();
                $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There was a problem adding purchase items.</div></div>';
            }
        } else {
            $conn->rollback();
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding purchase.</div></div>';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Error: '.$e->getMessage().'</div></div>';
    }
    
    header("Location: manage_purchase.php");
    exit();
}

if(isset($_POST['create_purchase_from_order'])){
    // Validate order_id
    if(!isset($_GET['from_order'])){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Order reference missing.</div></div>';
        header("Location: manage_purchase.php");
        exit();
    }
    
    $order_id = intval($_GET['from_order']);
    $supplier = isset($_POST['supplier']) ? $conn->real_escape_string($_POST['supplier']) : '';
    $invoice_no = isset($_POST['invoice_no']) ? $conn->real_escape_string($_POST['invoice_no']) : '';
    $date = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : date('Y-m-d');
    $total_amount = isset($_POST['fld_grand_total_amount']) ? floatval($_POST['fld_grand_total_amount']) : 0;
    $created_date = date('Y-m-d H:i:s');
    
    // Initialize arrays with empty values if not set
    $items = isset($_POST['item']) ? $_POST['item'] : [];
    $qtys = isset($_POST['qty']) ? $_POST['qty'] : [];
    $orignal_qty = isset($_POST['orignal_qty']) ? $_POST['orignal_qty'] : [];
    $prices = isset($_POST['price']) ? $_POST['price'] : [];
    $totals = isset($_POST['total']) ? $_POST['total'] : [];
    
    // Validate required fields
    if(empty($supplier) || empty($invoice_no) || $total_amount <= 0 || empty($items)){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Required fields are missing or invalid.</div></div>';
        header("Location: manage_purchase.php");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        $pquery = "INSERT INTO app_purchase SET supplier_id = '$supplier', invoice_no='$invoice_no', date = '$date', total_amount = '$total_amount', created_date = '$created_date'";
        $total_qty = 0;
        $tota_gprice = 0;
        
        if($conn->query($pquery)){
            $purchase_id = $conn->insert_id;
            $success = true;
            
            for ($i = 0, $n = count($items); $i < $n; $i++) {
                if(!isset($qtys[$i]) || !isset($orignal_qty[$i]) || !isset($prices[$i]) || !isset($totals[$i])){
                    continue;
                }
                
                $qty = floatval($qtys[$i]);
                if($qty > 0){
                    $item_id = intval($items[$i]);
                    $price = floatval($prices[$i]);
                    $total = floatval($totals[$i]);
                    
                    $detail_query = "INSERT INTO app_purchase_detail SET purchase_id = '$purchase_id', item_id = '$item_id', qty = '$qty', price = '$price', total = '$total', status = '0'";
                    
                    if($conn->query($detail_query)){
                        $pdid = $conn->insert_id;
                        
                        $remain_qty = floatval($orignal_qty[$i]) - $qty;
                        
                        if($remain_qty > 0){
                            $total_qty += $remain_qty;
                            $tota_price = $remain_qty * $price;
                            $tota_gprice += $tota_price;
                            
                            $update_query = "UPDATE app_purchase_orders_detail SET qty = '$remain_qty', price = '$price', total='$tota_price' WHERE purchase_id = '$order_id' AND item_id = '$item_id'";
                            if(!$conn->query($update_query)){
                                $success = false;
                                break;
                            }
                        } else {
                            $delete_query = "DELETE FROM app_purchase_orders_detail WHERE purchase_id = '$order_id' AND item_id = '$item_id'";
                            if(!$conn->query($delete_query)){
                                $success = false;
                                break;
                            }
                        }
                    } else {
                        $success = false;
                        break;
                    }
                }
            }
            
            if($success){
                if($total_qty < 1){
                    $delete_order_query = "DELETE FROM app_purchase_orders WHERE id = '$order_id'";
                    if(!$conn->query($delete_order_query)){
                        $success = false;
                    }
                } else {
                    $update_order_query = "UPDATE app_purchase_orders SET total_amount = '$tota_gprice' WHERE id = '$order_id'";
                    if(!$conn->query($update_order_query)){
                        $success = false;
                    }
                }
                
                if($success){
                    $conn->commit();
                    addSystemLog($conn, 'PURCHASE CREATED', "New purchase from purchase order ($order_id) has been created with id ($purchase_id)", "");
                    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Purchase added from order successfully.</div></div>';
                } else {
                    $conn->rollback();
                    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There was a problem updating the order.</div></div>';
                }
            } else {
                $conn->rollback();
                $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There was a problem adding purchase items.</div></div>';
            }
        } else {
            $conn->rollback();
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding purchase.</div></div>';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Error: '.$e->getMessage().'</div></div>';
    }
    
    header("Location: manage_purchase.php");
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
    <title>Create Purchase || D-Orders</title>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
/* =========================
   GLOBAL MOBILE FIX
========================= */
@media (max-width: 768px) {

    .content-wrapper {
        padding: 8px !important;
    }

    .card {
        border-radius: 10px;
    }

    .card-body {
        padding: 12px !important;
    }

    .btn {
        width: 100%;
        margin-bottom: 8px;
    }

    .form-control {
        font-size: 13px;
        height: 38px;
    }
}


/* =========================
   STACK FORM FIELDS
========================= */
@media (max-width: 768px) {

    .row > div {
        width: 100% !important;
        max-width: 100% !important;
        flex: 100% !important;
    }
}


/* =========================
   TABLE → MOBILE CARD STYLE
========================= */
@media (max-width: 768px) {

    .table {
        display: block;
    }

    .table thead {
        display: none;
    }

    .table tbody {
        display: block;
    }

    .table tbody tr {
        display: block;
        background: #f9f9f9;
        margin-bottom: 12px;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #eee;
    }

    .table tbody td {
        display: block;
        width: 100%;
        padding: 6px 0;
        border: none;
    }

    /* Make inputs full width */
    .table input,
    .table select {
        width: 100% !important;
        font-size: 13px;
        margin-bottom: 6px;
    }

    /* Row number */
    .table tbody td:first-child {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }
}


/* =========================
   FIX SELECT2 MOBILE
========================= */
@media (max-width: 768px) {

    .select2-container {
        width: 100% !important;
    }

    .select2-selection {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }
}


/* =========================
   ACTION BUTTONS
========================= */
@media (max-width: 768px) {

    .btn-danger {
        width: 100%;
        margin-top: 5px;
    }

    #add_invoice_item {
        width: 45px;
        height: 35px;
        padding: 0;
        font-size: 18px;
    }
}


/* =========================
   TOTAL SECTION FIX
========================= */
@media (max-width: 768px) {

    tfoot tr {
        display: block;
        text-align: center;
    }

    #fld_grand_total_amount {
        width: 100%;
        margin-top: 10px;
    }
}


/* =========================
   SMALL PHONES
========================= */
@media (max-width: 480px) {

    .card-body {
        padding: 10px !important;
    }

    .form-control {
        font-size: 12px;
    }

    .table tbody tr {
        padding: 10px;
    }

    .btn {
        font-size: 13px;
    }
}
@media (max-width: 768px) {
    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static" data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
    <style>
.select2-selection__arrow{
    display:none;
}
.table th, .table td {
            padding: 0.72rem 10px;
            font-size: 11px;
            vertical-align: middle;
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
                                    <li class="breadcrumb-item active">Create Purchase
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <style>
            input[type='checkbox']{
                    margin-top: 4px;
            }
            </style>
            <div class="content-body">
                <!-- account setting page -->
                <section id="page-account-settings">
                    <div class="row">
                       

                        <!-- right content section -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                          
                                            <?php if(isset($_GET['from_order'])){ 
                                            $purchase = $conn->query("SELECT * FROM app_purchase_orders where id = '{$_GET['from_order']}'");
                                            if($purchase->num_rows > 0){
                                                $purchase = $purchase->fetch_assoc();
                                                $supplier = $conn->query("select * from app_suppliers where id = '{$purchase['supplier_id']}'")->fetch_assoc();
                                            }else{
                                                header("location: manage_purchase_orders.php");
                                                exit();
                                            }
                                            ?>
                                                <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="supplier">Supplier</label>
                                                            <input type="hidden" class="form-control" name="supplier" value="<?php echo $purchase['supplier_id']; ?>" required/>
                                                            <input type="text" class="form-control" id="supplier" value="<?php echo $supplier['name']; ?>" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="date">Purchase Date</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="invoice_no">Invoice #</label>
                                                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="<?php echo $purchase['order_no']; ?>" required/>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class="col-12">
                                                        <div class="card-datatable">
                                                           <table class="dt-row-grouping-t table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">Sn</th>
                                                                        <th style="width:45%">Item</th>
                                                                        <th style="width:15%">Qty</th>
                                                                        <th style="width:15%">Price</th>
                                                                        <th style="width:15%">Total</th>
                                                                        <th style="width:5%"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <?php
                                                                    $sn=0;
                                                                    $purchase_details = $conn->query("select * from app_purchase_orders_detail where purchase_id = '{$_GET['from_order']}'");
                                                                    while($row = $purchase_details->fetch_assoc()){
                                                                        $item = $conn->query("Select * from app_items where id = '{$row['item_id']}'")->fetch_assoc();
                                                                    $sn++;?>
                                                                    <tr>
                                                                        <td><?=$sn;?></td>
                                                                        <td>
                                                                            <?=$item['sku'];?>
                                                                            <input type="hidden" name="item[]" value="<?=$row['item_id'];?>" /> 
                                                                        </td>
                                                                        <td>
                                                                            <input type="hidden" name="orignal_qty[]" value="<?=$row['qty'];?>" />
                                                                            <input type="number" class="form-control" id="qty_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="qty[]" value="<?=$row['qty'];?>" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".0001" class="form-control" id="price_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="price[]" value="<?=$row['price'];?>" required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" step=".0001" class="form-control total_price text-right" id="total_<?=$sn;?>" name="total[]" value="<?=$row['total'];?>" readonly required/>
                                                                        </td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="4"><b>Total($):</b></td>
                                                                    <td class="text-right">
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="<?=$purchase['total_amount'];?>" readonly="readonly">
                                                                    </td>
                                                                    
                                                                </tr>
                                                            </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="create_purchase_from_order" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1" onclick="this.form.submit(); this.disabled=true; this.innerText='Submitting…'; ">Create Purchase</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                            
                                            <?php }else{ ?>
                                                <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="supplier">Supplier</label>
                                                            <select class="form-control select2" id="supplier" name="supplier" required>
                                                                <option value="">Select Supplier</option>
                                                                <?php $suppliers = $conn->query("select * from app_suppliers order by name asc");
                                                                    while($supplier = $suppliers->fetch_assoc()){
                                                                        echo '<option value="'.$supplier['id'].'">'.$supplier['name'].'</option>';
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="date">Purchase Date</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="invoice_no">Invoice #</label>
                                                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="" required/>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class="col-12">
                                                        <div class="card-datatable">
                                                           <table class="dt-row-grouping-t table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">Sn</th>
                                                                        <th style="width:45%">Item</th>
                                                                        <th style="width:15%">Qty</th>
                                                                        <th style="width:15%">Price</th>
                                                                        <th style="width:15%">Total</th>
                                                                        <th style="width:5%">Status</th>
                                                                        <th style="width:5%"></th>
                                                                        
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                            <select class="form-control select2" id="item_1" name="item[]" required>
                                                                                <option value="">Select Item</option>
                                                                                <?php $items = $conn->query("select * from app_items order by sku asc");
                                                                                    while($item = $items->fetch_assoc()){
                                                                                        echo '<option value="'.$item['id'].'">'.$item['sku'].'</option>';
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control" id="qty_1" onkeyup="calculate_store(1)" name="qty[]" value="0" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".0001" class="form-control" id="price_1" onkeyup="calculate_store(1)" name="price[]" value="0.0000" required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" step=".0001" class="form-control total_price text-right" id="total_1" name="total[]" value="0.0000" readonly required/>
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-control select2" id="status_1" name="status[]" required>
                                                                                <option value="0">Ordered</option>
                                                                                <option value="1">Received</option>
                                                                                
                                                                                
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button>
                                                                        </td>
                                                                        
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="4"><b>Total($):</b></td>
                                                                    
                                                                    <td class="text-right">
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="0.0000" readonly="readonly">
                                                                    </td>
                                                                    <td class="text-right" colspan=""></td>
                                                                    <td> 
                                                                    <button type="button" id="add_invoice_item" class="btn btn-success" name="add-purchase-item" onclick="addPurchaseOrderRow()" >+</button>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="create_purchase" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1" onclick="this.form.submit(); this.disabled=true; this.innerText='Submitting…'; ">Create Purchase</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                            
                                            <?php } ?>
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ right content section -->
                    </div>
                </section>
                <!-- / account setting page -->

            </div>
        </div>
    </div>
    
    	<div id="productSelect" style="display:none;">
			<?php $items = $conn->query("select * from app_items order by sku asc");
                while($item = $items->fetch_assoc()){
                    echo '<option value="'.$item['id'].'">'.$item['sku'].'</option>';
                }
            ?>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script src="app-assets/vendors/js/editors/quill/katex.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/highlight.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/quill.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->

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
        function createPop(url, name)
        {    
                
            newwindow=window.open(url,name,'width=760,height=540,toolbar=0,menubar=0,location=0');  
            if (window.focus) {newwindow.focus()}
        }
        
        $(document).ready(function() {
            $('.select2').select2();
        });
        
    count = 2;
        
    function addPurchaseOrderRow(){
	
		var products=$("#productSelect").html();
       
        var newdiv = document.createElement('tr');
        
        newdiv.innerHTML ='<td>'+count+'</td><td><select class="form-control select2" id="item_'+count+'" name="item[]" required><option value="">Select Item</option>'+products+'</select></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" value="0" required/></td><td><input type="number" step=".0001" class="form-control" onkeyup="calculate_store('+count+')" id="price_'+count+'" name="price[]" value="0.0000" required/></td><td class="text-right"><input type="text" step=".0001" class="form-control total_price text-right" id="total_'+count+'" name="total[]" value="0.0000" readonly required/></td><td><select class="form-control select2" id="status_'+count+'" name="status[]" required><option value="0">Ordered</option><option value="1">Received</option></select></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
        document.getElementById("addPurchaseItem").appendChild(newdiv);
        $('#item_'+count).select2();
        $('#status_'+count).select2();
        count++;
        
    }
	function deleteRow(e) {
        var t = $("#addPurchaseItem > tr").length;
        if (1 == t) alert("There only one row you can't delete.");
        else {
            var a = e.parentNode.parentNode;
            a.parentNode.removeChild(a)
        }
        calculateSum()
    }
    
    //Calculate Sum
    "use strict";
function calculateSum() {
      var t = 0;

         
            //Total Price
    $(".total_price").each(function () {
        isNaN(this.value) || 0 == this.value.length || (t += parseFloat(this.value))
    }),   
    e = t.toFixed(4);

    var test = +e;
    $("#fld_grand_total_amount").val(test.toFixed(4));


    var gt = $("#fld_grand_total_amount").val();
    var grnt_totals = gt;
    $("#fld_grand_total_amount").val(grnt_totals);

    
}
 //Calculate store product
 "use strict";
function calculate_store(sl) {
    var gr_tot = 0;
    var qty = $("#qty_" + sl).val();
    var price = $("#price_" + sl).val();

    // Ensure price is a valid number, even if it is 0 or empty
    price = price === "" || isNaN(price) ? 0 : parseFloat(price);

    var total_price = qty * price;
    $("#total_" + sl).val(total_price.toFixed(4));

    // Total Price
    $(".total_price").each(function() {
        // Only sum valid numbers
        if (!isNaN(this.value) && this.value !== "") {
            gr_tot += parseFloat(this.value);
        }
    });

    // Calculate grand total
    var grandtotal = gr_tot;
    $("#fld_grand_total_amount").val(grandtotal.toFixed(4));
}

    //     "use strict";
    // function calculate_store(sl) {
       
    //     var gr_tot = 0;
    //     var qty    = $("#qty_"+sl).val();
    //     var price = $("#price_"+sl).val();

    //     var total_price     = qty * price;
    //     $("#total_"+sl).val(total_price.toFixed(4));

    //     //Total Price
    //     $(".total_price").each(function() {
    //         isNaN(this.value) || 0 == this.value.length || (gr_tot += parseFloat(this.value))
    //     });

    //     //$("#Total").val(gr_tot.toFixed(2,2));
    //     var grandtotal = gr_tot;
    //     $("#fld_grand_total_amount").val(grandtotal.toFixed(4));
    // }
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
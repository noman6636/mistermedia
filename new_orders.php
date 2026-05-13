<?php 
require_once "inc/config.php";
require_once "inc/functions.php";


if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(5, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['savesettings'])){
    // print_r($_POST);
    $pageSettings =  implode(",", array_keys($_POST['page']));
    $csvSettings =  implode(",", array_keys($_POST['csv']));
    $conn->query("update app_settings set value = '$pageSettings' where name = 'page_settings'");
    $conn->query("update app_settings set value = '$csvSettings' where name = 'csv_settings'");
    
    addSystemLog($conn, 'SETTING UPDATED', "New Orders page settings has been updated", "");
    header("location: new_orders.php");
    exit;
}

include("inc/orderActions.php");

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
    <title>New Orders || D-Orders</title>
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
    <!-- END: Custom CSS-->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
<style>
    /* =========================
   TABLE RESPONSIVENESS
========================= */

/* Wrap table for horizontal scroll */
.card-datatable {
    overflow-x: auto;
}

/* Prevent table breaking layout */
.table {
    min-width: 900px;
}


/* =========================
   TABLET (≤ 991px)
========================= */
@media (max-width: 991px) {

    .table th, 
    .table td {
        font-size: 11px;
        padding: 0.5rem 6px;
        white-space: nowrap;
    }

    .btn {
        font-size: 12px;
        padding: 6px 10px;
    }

    .content-header h4 {
        font-size: 16px;
    }

    /* Top buttons spacing */
    .row > .col-12 > button,
    .row > .col-12 > .btn-group {
        margin-bottom: 5px;
    }
}


/* =========================
   MOBILE (≤ 767px)
========================= */
@media (max-width: 767px) {

    /* Stack top action buttons */
    .row > .col-12 > button,
    .row > .col-12 > .btn-group {
        float: none !important;
        display: block;
        width: 100%;
        margin-bottom: 8px;
    }

    /* Filters form */
    form select,
    form input[type="checkbox"] {
        display: block;
        width: 100%;
        margin-bottom: 8px;
    }

    /* Reduce table font */
    .table th, 
    .table td {
        font-size: 10px;
        padding: 0.4rem 5px;
    }

    /* Modal adjustments */
    .modal-dialog {
        margin: 10px;
    }

    .modal-content {
        font-size: 12px;
    }

    /* Breadcrumb */
    .breadcrumb {
        font-size: 12px;
    }

    /* Fix checkbox alignment */
    input[type="checkbox"] {
        transform: scale(1.1);
    }
}


/* =========================
   SMALL MOBILE (≤ 480px)
========================= */
@media (max-width: 480px) {

    .table th, 
    .table td {
        font-size: 9px;
    }

    .btn {
        font-size: 11px;
        padding: 5px 8px;
    }

    .card-title {
        font-size: 14px;
    }

    #ocount {
        font-size: 14px;
        font-weight: bold;
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
                                    <li class="breadcrumb-item active">New Orders
                                    </li>
                                </ol>
                            </div>
                        
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">
                <div class="row" style="margin-bottom:10px">
                    <div class="col-12">
                        
                        <button type="button" class="btn btn-primary" style="float:right">Select All <input type="checkbox" id="select_all"/></button>
                        <div class="btn-group" style="float:right;margin-right:10px">
                            <button  class="btn btn-primary dropdown-toggle waves-effect waves-float waves-light" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Print Label
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(1)">DC UNG</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(2)">DC UNH</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(3)">CSV</a>
                            </div>
                        </div>
                       <button type="button" onclick="confirmArchive(100)" style="float:right;margin-right:10px" class="btn btn-outline-primary waves-effect">
    <i data-feather='archive'></i>
    <span>Archive</span>
</button>

<script>
function confirmArchive(id) {
    if (confirm("Are you sure you want to archive this item?")) {
        submitPrint(id);  // Your original function
    }
}
</script>
                         <button type="button" data-toggle="modal" data-target="#pageSettings" style="float:right;margin-right:10px" class="btn btn-primary">
                            <i data-feather='settings'></i>
                            <span>Settings</span>
                        </button>
                    </div>
                    <div class="col-12">
                        <br>
                        <form action="" method="get">
                            <!--<input type="checkbox" name="hidePrinted" value="1" onclick="this.form.submit();" <?php //if(isset($_GET['hidePrinted'])){echo'checked';} ?>> Hide Printed Orders-->
                            <input type="checkbox" name="removeHeaders" value="1" onclick="this.form.submit();" <?php if(isset($_GET['removeHeaders'])){echo'checked';} ?>> Remove Headers
                            <input type="checkbox" name="viewNotes" value="1" onclick="this.form.submit();" <?php if(isset($_GET['viewNotes'])){echo'checked';} ?>> View Notes Orders
                            <input type="checkbox" name="viewSp" value="1" onclick="this.form.submit();" <?php if(isset($_GET['viewSp'])){echo'checked';} ?>> View SP Orders
                            <input type="checkbox" name="viewPc" value="1" onclick="this.form.submit();" <?php if(isset($_GET['viewPc'])){echo'checked';} ?>> View Same PC Orders
                            
                            <select id="sizeId" name="sizeId" onchange="this.form.submit();">
                                <option value="">Select Packing Size</option>
                                <?php 
                                $sizes = $conn->query("SELECT * FROM app_packing_sizes ORDER BY name");
                                while($size = $sizes->fetch_assoc()){ ?>
                                    <option value="<?=$size['id']; ?>" <?php if(isset($_GET['sizeId']) && $_GET['sizeId'] == $size['id']){echo'selected';} ?>><?=$size['name']; ?></option>
                                <?php } ?>
                            </select>
                            
                            <select id="charCount" name="charCount" onchange="this.form.submit();">
                                <option value="">Select Character Count</option>
                                
                                <option value="1" <?php if(isset($_GET['charCount']) && $_GET['charCount'] == 1){echo'selected';} ?>>Less then 28</option>
                                <option value="2" <?php if(isset($_GET['charCount']) && $_GET['charCount'] == 2){echo'selected';} ?>>Greater then and equal 28</option>
                                
                            </select>
                                                                
                        </form>
                        <p>Selected Orders : <span id="ocount">0</span></p>
                    </div>
                </div>
                <div class="modal fade text-left" id="pageSettings" tabindex="-1" aria-labelledby="myModalLabel33" style="display: none;" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel33">Page Settings</h4>
                                <button type="button" id="closemodelbtn" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form action="" method="post">
                                <div class="modal-body">
                                    
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="page-settings-tab-click" data-toggle="tab" href="#page-settings-tab" role="tab" aria-selected="true">Page Settings</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="csv-settings-tab-click" data-toggle="tab" href="#csv-settings-tab" role="tab" aria-selected="false">CSV Settings</a>
                                        </li>
                                    </ul>
                                    <style>
                                        .float-right{
                                            float:right;
                                        }
                                    </style>
                                    
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="page-settings-tab" role="tabpanel">
                                            <table class="table table-sm table-bordered">
                                                
                                                <tbody>
                                                    <tr>
                                                        <td><label>Date/Time :</label> <input name="page[CreatedTime]" <?php if(in_array('CreatedTime', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Country Code :</label> <input name="page[Country]" <?php if(in_array('Country', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Price Paid :</label> <input name="page[Total]" <?php if(in_array('Total', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td><label>Order No :</label> <input name="page[OrderID]" <?php if(in_array('OrderID', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>SKU :</label> <input name="page[SKU]" <?php if(in_array('SKU', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Postcode :</label> <input name="page[PostalCode]" <?php if(in_array('PostalCode', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>Buyer UserId :</label> <input name="page[BuyerUserID]" <?php if(in_array('BuyerUserID', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Print Status :</label> <input name="page[IsPrinted]" <?php if(in_array('IsPrinted', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Shipping Paid :</label> <input name="page[ShippingServiceCost]" <?php if(in_array('ShippingServiceCost', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>Item Title & Qty :</label> <input name="page[ItemTitle]" <?php if(in_array('ItemTitle', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Shipping Method :</label> <input name="page[ShippingService]" <?php if(in_array('ShippingService', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>SellRecordNo :</label> <input name="page[SellingManagerSalesRecordNumber]" <?php if(in_array('SellingManagerSalesRecordNumber', $pageArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="csv-settings-tab"  role="tabpanel">
                                            <table class="table table-sm table-bordered">
                                                
                                                <tbody>
                                                    <tr>
                                                        <td><label>AccountID :</label> <input name="csv[AccountID]" <?php if(in_array('AccountID', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>OrderID :</label> <input name="csv[OrderID]" type="checkbox" class="float-right" value="1" checked disabled></td>
                                                        <td><label>OrderStatus :</label> <input name="csv[OrderStatus]" <?php if(in_array('OrderStatus', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <td><label>AdjustmentAmount :</label> <input name="csv[AdjustmentAmount]" <?php if(in_array('AdjustmentAmount', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>AmountPaid :</label> <input name="csv[AmountPaid]" <?php if(in_array('AmountPaid', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>AmountSaved :</label> <input name="csv[AmountSaved]" <?php if(in_array('AmountSaved', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>PaymentMethod :</label> <input name="csv[PaymentMethod]" <?php if(in_array('PaymentMethod', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>PaymentStatus :</label> <input name="csv[PaymentStatus]" <?php if(in_array('PaymentStatus', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>CreatedTime :</label> <input name="csv[CreatedTime]" <?php if(in_array('CreatedTime', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>Subtotal :</label> <input name="csv[Subtotal]" <?php if(in_array('Subtotal', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Total :</label> <input name="csv[Total]" <?php if(in_array('Total', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>SellRecordNo :</label> <input name="csv[SellingManagerSalesRecordNumber]" <?php if(in_array('SellingManagerSalesRecordNumber', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>SKU_QTY :</label> <input name="csv[SKUQTY]" <?php if(in_array('SKUQTY', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>PostCode :</label> <input name="csv[PostCode]" <?php if(in_array('PostCode', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>ShippingService :</label> <input name="csv[ShippingService]" <?php if(in_array('ShippingService', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>ShippingCost :</label> <input name="csv[ShippingServiceCost]" <?php if(in_array('ShippingServiceCost', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>STN :</label> <input name="csv[ShipmentTrackingNumber]" <?php if(in_array('ShipmentTrackingNumber', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>ItemID :</label> <input name="csv[ItemID]" <?php if(in_array('ItemID', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>SKU :</label> <input name="csv[SKU]" <?php if(in_array('SKU', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>ItemTitle :</label> <input name="csv[ItemTitle]" <?php if(in_array('ItemTitle', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>Condition :</label> <input name="csv[ConditionDisplayName]" <?php if(in_array('ConditionDisplayName', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>Quantity :</label> <input name="csv[QuantityPurchased]" <?php if(in_array('QuantityPurchased', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>BuyerUserID :</label> <input name="csv[BuyerUserID]" <?php if(in_array('BuyerUserID', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>BuyerMessage :</label> <input name="csv[BuyerCheckoutMessage]" <?php if(in_array('BuyerCheckoutMessage', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label>PaidTime :</label> <input name="csv[PaidTime]" <?php if(in_array('PaidTime', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>ShippedTime :</label> <input name="csv[ShippedTime]" <?php if(in_array('ShippedTime', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>FullName :</label> <input name="csv[FullName]" <?php if(in_array('FullName', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        
                                                        
                                                    </tr>
                                                    <tr>
                                                        
                                                        <td><label>AddressLine1 :</label> <input name="csv[AddressLine1]" <?php if(in_array('AddressLine1', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>AddressLine2 :</label> <input name="csv[AddressLine2]" <?php if(in_array('AddressLine2', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>City :</label> <input name="csv[City]" <?php if(in_array('City', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        
                                                        
                                                    </tr>
                                                     <tr>
                                                        
                                                        
                                                        <td><label>Country :</label> <input name="csv[Country]" <?php if(in_array('Country', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>PhoneNo :</label> <input name="csv[PhoneNo]" <?php if(in_array('PhoneNo', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <td><label>BuyerEmail :</label> <input name="csv[BuyerEmail]" <?php if(in_array('BuyerEmail', $csvArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="modal-footer" id="">
                                    <input type="hidden" name="savesettings" value="1" />
                                    <button type="submit" class="btn btn-primary waves-effect waves-float waves-light">Save Changes</button>
                                </div>
                            </form>
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
                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <?php echo flash_msg(); ?>
                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                    <?php 
                    if(!isset($_GET['removeHeaders'])){
                    $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                    while($account = $accounts->fetch_assoc()){
                     $today = date('Y-m-d');
                    $where = "where AccountID = '{$account['id']}' && IsPrinted = '0' && IsArchived = '0'";
                    if(isset($_GET['hidePrinted'])  || isset($_GET['viewNotes']) || isset($_GET['viewSp']) || isset($_GET['sizeId'])){
                    //   $where = "where AccountID = '{$account['id']}' && IsArchived = '0'";
                    //   if(isset($_GET['hidePrinted'])){
                        //   $where .= " && IsPrinted = '0'";
                    //   }
                       if(isset($_GET['viewNotes'])){
                           $where .= " && BuyerCheckoutMessage != ''";
                       }
                       if(isset($_GET['viewSp'])){
                           $where .= " && ShippingServiceCost <> '0'";
                       }
                       
                       if(isset($_GET['sizeId']) && !empty($_GET['sizeId'])){
                           $where .= " && OrderID IN (SELECT OrderID FROM app_order_items WHERE (SKU IN (SELECT SKU FROM app_items WHERE packing_size_id = '{$_GET['sizeId']}') || SKU IN (SELECT sku FROM app_packages WHERE packing_size_id = '{$_GET['sizeId']}')))";
                       }
                            $query = "select * from app_orders $where ORDER BY PostCode";
                        
                        
                    }else{
                        //$query = "select * from app_orders where AccountID = '{$account['id']}' && (DATE(CreatedTime) = '$today' || IsPrinted = '0') && IsArchived = '0' ORDER BY PostCode";
                        $query = "select * from app_orders $where  ORDER BY PostCode";
                    }
                    
                    $orders = $conn->query($query);
                    $sn = 0; 
                    
                    if($orders->num_rows > 0){?>
                    <div class="row">
                       <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title"><?php echo $account['account_name']; ?></h4>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <?php if(in_array('CreatedTime', $pageArray)){ ?><th style="width:4%;">Date/Time</th><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray)){ ?><th style="width:4%;">Order #</th><?php } ?>
                                                <?php if(in_array('SellingManagerSalesRecordNumber', $pageArray)){ ?><th style="width:4%;">SellRecord #</th><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray)){ ?><th style="width:11%;">Buyer Userid</th><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray)){ ?><th style="width:30%;">Item Name</th><?php } ?>
                                                <?php if(in_array('SKU', $pageArray)){ ?><th style="width:8%;">SKU</th><?php } ?>
                                                <?php if(in_array('Total', $pageArray)){ ?><th style="width:6%;">P</th><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray)){ ?><th style="width:6%;">SP</th><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray)){ ?><th style="width:6%;">PC/ZP</th><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray)){ ?><th style="width:10%;">SM</th><?php } ?>
                                                <?php if(in_array('Country', $pageArray)){ ?><th style="width:6%;">CT</th><?php } ?>
                                                <th style="width:4%;" class="no-sort"><center><img src="assets/printer_icon.png" style="width: 18px;"></center></th>
                                                <th style="width:5%;" class="no-sort"><input type="checkbox" id="market_<?php echo $account['id']; ?>"/></th>
                                            </tr>
                                        </thead>
                                        <tbody id="market_<?php echo $account['id']; ?>">
                                        <?php 
                                       
                                        while($order = $orders->fetch_assoc()){
                                            $shipa = json_decode($order['ShippingAddress'], true);
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$order['OrderID']}'");
                                            $sku = '';
                                            $showItem = '';
                                            $skuCount = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= $item['ItemTitle'].' x '.$item['QuantityPurchased'].'<br>';
                                                $sku .= $item['SKU'].'<br>';
                                                $skuCount  .= $item['QuantityPurchased']." x ".$item['SKU']." = ";
                                            }
                                            $skuCount = rtrim($skuCount, ' = ');
                                            $showRow = true;
                                            if(!empty($_GET['charCount'])){
                                               
                                                if($_GET['charCount'] == 1){
                                                    
                                                    if(strlen($skuCount) >= 28){
                                                        $showRow = false;
                                                    }
                                                }else{
                                                    
                                                    if(strlen($skuCount) < 28){
                                                        
                                                        $showRow = false;
                                                    }
                                                }
                                            }
                                             $count = $conn->query("SELECT * from app_orders $where && PostCode = '{$order['PostCode']}'")->num_rows;
                                                if($count < 2 && isset($_GET['viewPc']) && !empty($_GET['viewPc'])){
                                                    $showRow = false;
                                                }
                                            if($showRow){
                                               
                                            
                                            $sn++; ?>
                                    	    <tr style="<?php if($count > 1){ echo 'background: beige;'; } ?>">
                                    	        
                                                <?php if(in_array('CreatedTime', $pageArray)){ ?><td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray)){ ?><td><?php echo $order['OrderID']; ?></td><?php } ?>
                                                <?php if(in_array('SellingManagerSalesRecordNumber', $pageArray)){ ?><td><?php echo $order['SellingManagerSalesRecordNumber']; ?></td><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray)){ ?><td><?php echo $order['BuyerUserID']; ?></td><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray)){ ?>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if($order['BuyerCheckoutMessage'] !=''){
                                                    echo '<br><span style="color:red">Buyer Note: '.$order['BuyerCheckoutMessage'].'</span>';
                                                }?>
                                                </td>
                                                <?php } ?>
                                                <?php if(in_array('SKU', $pageArray)){ ?><td><?php echo $sku; ?></td><?php } ?>
                                                <?php if(in_array('Total', $pageArray)){ ?><td><?php echo $order['Total']; ?></td><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray)){ ?><td><?php echo $order['ShippingServiceCost']; ?></td><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray)){ ?><td><?php echo $shipa['PostalCode']; ?></td><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray)){ ?><td><?php echo substr($order['ShippingService'], 0, 16); ?></td><?php } ?>
                                                <?php if(in_array('Country', $pageArray)){ ?><td><?php echo $shipa['Country']; ?></td><?php } ?>
                                                <td>
                                                    <center>
                                                        <button type="button" onclick="editOrder(<?php echo $order['ID']; ?>)" class="btn btn-icon btn-icon rounded-circle btn-flat-success waves-effect">
                                                            <i data-feather='edit'></i>
                                                        </button>
                                                        
                                                        <?php if(in_array('IsPrinted', $pageArray)){
                                                        if($order['IsPrinted'] == '1'){ ?>
                                                        <img class="dontprint" src="assets/tick_mark.gif" style="width: 15px !important;">
                                                        <?php }else{ ?>
                                                        <img class="dontprint" src="assets/cross_mark.png" style="width: 15px !important;">
                                                        <?php }} ?>
                                                        
                                                    </center>
                                                </td>
                                                <td><input type="checkbox" name="aorder[]" class="selectedId" value="<?php echo $order['ID']; ?>/<?php echo $account['id']; ?>/ebay"></td>
                                            </tr>
                                        <?php }} ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }}}else{ ?>
                    <div class="row">
                       <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">New Orders</h4>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <?php if(in_array('CreatedTime', $pageArray)){ ?><th style="width:4%;">Date/Time</th><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray)){ ?><th style="width:4%;">Order #</th><?php } ?>
                                                <?php if(in_array('SellingManagerSalesRecordNumber', $pageArray)){ ?><th style="width:4%;">SellRecord #</th><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray)){ ?><th style="width:11%;">Buyer Userid</th><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray)){ ?><th style="width:30%;">Item Name</th><?php } ?>
                                                <?php if(in_array('SKU', $pageArray)){ ?><th style="width:8%;">SKU</th><?php } ?>
                                                <?php if(in_array('Total', $pageArray)){ ?><th style="width:6%;">P</th><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray)){ ?><th style="width:6%;">SP</th><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray)){ ?><th style="width:6%;">PC/ZP</th><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray)){ ?><th style="width:10%;">SM</th><?php } ?>
                                                <?php if(in_array('Country', $pageArray)){ ?><th style="width:6%;">CT</th><?php } ?>
                                                <th style="width:4%;" class="no-sort"><center><img src="assets/printer_icon.png" style="width: 18px;"></center></th>
                                                <th style="width:5%;" class="no-sort"><input type="checkbox" id="market_<?php echo $account['id']; ?>"/></th>
                                            </tr>
                                        </thead>
                                        <tbody id="market_1">
                                        <?php 
                                        $today = date('Y-m-d');
                                        $where = "where IsPrinted = '0' && IsArchived = '0'";
                                        if(isset($_GET['hidePrinted'])  || isset($_GET['viewNotes']) || isset($_GET['viewSp']) || isset($_GET['sizeId'])){
                                          
                                           if(isset($_GET['viewNotes'])){
                                               $where .= " && BuyerCheckoutMessage != ''";
                                           }
                                           if(isset($_GET['viewSp'])){
                                               $where .= " && ShippingServiceCost <> '0'";
                                           }
                                           if(isset($_GET['sizeId']) && !empty($_GET['sizeId'])){
                                               $where .= " && OrderID IN (SELECT OrderID FROM app_order_items WHERE (SKU IN (SELECT SKU FROM app_items WHERE packing_size_id = '{$_GET['sizeId']}') || SKU IN (SELECT sku FROM app_packages WHERE packing_size_id = '{$_GET['sizeId']}')))";
                                           }
                                                $query = "select * from app_orders $where  ORDER BY PostCode";
                                        }else{
                                            // $query = "select * from app_orders where (DATE(CreatedTime) = '$today' || IsPrinted = '0') && IsArchived = '0' ORDER BY PostCode";
                                            $query = "select * from app_orders $where ORDER BY PostCode";
                                        }
                                        $orders = $conn->query($query);
                                        $sn = 0;
                                        while($order = $orders->fetch_assoc()){
                                            $shipa = json_decode($order['ShippingAddress'], true);
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$order['OrderID']}'");
                                            $sku = '';
                                            $showItem = '';
                                             $skuCount = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= $item['ItemTitle'].' x '.$item['QuantityPurchased'].'<br>';
                                                $sku .= $item['SKU'].'<br>';
                                                $skuCount  .= $item['QuantityPurchased']." x ".$item['SKU']." = ";
                                            }
                                            $skuCount = rtrim($skuCount, ' = ');
                                            $showRow = true;
                                            if(!empty($_GET['charCount'])){
                                               
                                                if($_GET['charCount'] == 1){
                                                    
                                                    if(strlen($skuCount) >= 28){
                                                        $showRow = false;
                                                    }
                                                }else{
                                                    
                                                    if(strlen($skuCount) < 28){
                                                        
                                                        $showRow = false;
                                                    }
                                                }
                                            }
                                            $count = $conn->query("SELECT * from app_orders $where && PostCode = '{$order['PostCode']}'")->num_rows;
                                            if($count < 2 && isset($_GET['viewPc']) && !empty($_GET['viewPc'])){
                                                    $showRow = false;
                                                }
                                            if($showRow){
                                                
                                            $sn++; ?>
                                            
                                    	    <tr style="<?php if($count > 1){ echo 'background: beige;'; } ?>">
                                    	        
                                                <?php if(in_array('CreatedTime', $pageArray)){ ?><td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td><?php } ?>
                                                <?php if(in_array('OrderID', $pageArray)){ ?><td><?php echo $order['OrderID']; ?></td><?php } ?>
                                                <?php if(in_array('SellingManagerSalesRecordNumber', $pageArray)){ ?><td><?php echo $order['SellingManagerSalesRecordNumber']; ?></td><?php } ?>
                                                <?php if(in_array('BuyerUserID', $pageArray)){ ?><td><?php echo $order['BuyerUserID']; ?></td><?php } ?>
                                                <?php if(in_array('ItemTitle', $pageArray)){ ?>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if($order['BuyerCheckoutMessage'] !=''){
                                                    echo '<br><span style="color:red">Buyer Note: '.$order['BuyerCheckoutMessage'].'</span>';
                                                }?>
                                                </td>
                                                <?php } ?>
                                                <?php if(in_array('SKU', $pageArray)){ ?><td><?php echo $sku; ?></td><?php } ?>
                                                <?php if(in_array('Total', $pageArray)){ ?><td><?php echo $order['Total']; ?></td><?php } ?>
                                                <?php if(in_array('ShippingServiceCost', $pageArray)){ ?><td><?php echo $order['ShippingServiceCost']; ?></td><?php } ?>
                                                <?php if(in_array('PostalCode', $pageArray)){ ?><td><?php echo $shipa['PostalCode']; ?></td><?php } ?>
                                                <?php if(in_array('ShippingService', $pageArray)){ ?><td><?php echo substr($order['ShippingService'], 0, 16); ?></td><?php } ?>
                                                <?php if(in_array('Country', $pageArray)){ ?><td><?php echo $shipa['Country']; ?></td><?php } ?>
                                                <td>
                                                    <center>
                                                        <button type="button" onclick="editOrder(<?php echo $order['ID']; ?>)" class="btn btn-icon btn-icon rounded-circle btn-flat-success waves-effect">
                                                            <i data-feather='edit'></i>
                                                        </button>
                                                        <?php if(in_array('IsPrinted', $pageArray)){
                                                        if($order['IsPrinted'] == '1'){ ?>
                                                        <img class="dontprint" src="assets/tick_mark.gif" style="width: 15px !important;">
                                                        <?php }else{ ?>
                                                        <img class="dontprint" src="assets/cross_mark.png" style="width: 15px !important;">
                                                        <?php }} ?>
                                                    </center>
                                                </td>
                                                <td><input type="checkbox" name="aorder[]" class="selectedId" value="<?php echo $order['ID']; ?>/<?php echo $account['id']; ?>/ebay"></td>
                                            </tr>
                                        <?php }} ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    </form>
                </section>
                <!--/ Row grouping -->
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
            </div>
        </div>
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
                        console.log(data);
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
        
        //  $("#selectall").click(function () {
        //         var checkAll = $("#selectall").prop('checked');
        //             if (checkAll) {
        //                 $(".case").prop("checked", true);
        //             } else {
        //                 $(".case").prop("checked", false);
        //             }
        //     });
            
            
        //     function selectGroup(val){
        //         var checkAllA = $("#accountselect"+val).prop('checked');
        //         console.log("value of checkbox is: ", val);
        //             if (checkAllA) {
        //                 $(".case"+val).prop("checked", true);
        //             } else {
        //                 $(".case"+val).prop("checked", false);
        //             }
        //     }

        //     $(".case").click(function(){
        //         if($(".case").length == $(".case:checked").length) {
        //             $("#selectall").prop("checked", true);
        //         } else {
        //             $("#selectall").prop("checked", false);
        //         }

        //     });
        
        $(function() {
    $('#select_all').change(function(){
        console.log("Hi");
    	
        var checkboxes = $('#allordersdata').find(':checkbox').not(":disabled");
     
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });
});
//SELECT ALL FUNCITON BY MARKET
$(function() {
    $('input[id^="market_"]').change(function(){
        
    	var inputId = $(this).attr("id");
    	console.log("HI IS"+ inputId);
        var checkboxes = $('#'+inputId+' td').find(':checkbox').not(":disabled");
        
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true).closest('td').parent().addClass('highlight_row');
        } else {
          checkboxes.prop('checked', false).closest('td').parent().removeClass('highlight_row');
        }
    });
});
//CHECKBOX COUNT
$(function() {
    $('input[type="checkbox"]').change(function(){
		var numberNotChecked = $('input[name="aorder[]"]').filter(':checked').length;
		$("#ocount").html(numberNotChecked);
    });
});
        
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
    </script>
     <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
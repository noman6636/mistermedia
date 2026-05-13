<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(7, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

// if(isset($_POST['mark_unship'])){
//      $aorder = $_POST['aorder'];
//     if(count($aorder) < 1){
//         $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast on order to change status.</div></div>';
//         header("location: advance_search.php");
//         exit();
//     }
//     $date = date('Y-m-d H:i:s', strtotime($_POST['date']));
    
//     foreach($aorder as $orderid){
//         $order = $conn->query("select * from app_orders where ID = '$orderid'")->fetch_assoc();
//         $order_items = $conn->query("select * from app_order_items where OrderID = '$orderid'");
        
//         $new_order_id = strtotime(date('Y-m-d H:i:s')).'-'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
//         $pquery = "INSERT INTO app_orders SET AccountID = '221', OrderID = '$new_order_id', OrderStatus = 'Completed', PaymentMethod = 'D-Orders', PaymentStatus = 'Complete', CreatedTime = '$date', Subtotal = '{$order['Subtotal']}', Total = '{$order['Total']}', ShippingAddress = '{$order['ShippingAddress']}', PostCode = '{$order['PostCode']}', Reference = '{$order['Reference']}', BuyerCheckoutMessage = 'Reshipped Order # $orderid <br> {$order['BuyerCheckoutMessage']}', OrderType = '2', IsPrinted = '1', IsDispatched = '1', IsRespond = '1', IsInReship = '1'";
        
//          if($conn->query($pquery)){
        
//                 while ($itemso = $order_items->fetch_assoc()) {
                   
//         			    $conn->query("INSERT INTO app_order_items SET OrderID = '$new_order_id', SKU = '{$itemso['SKU']}', ItemTitle = '{$itemso['ItemTitle']}', QuantityPurchased = '{$itemso['QuantityPurchased']}', Price = '{$itemso['Price']}', OrderType = '2'");
                    
//                 }
        
//          }
        
//     }
//      $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Orders marked as unshipped.</div></div>';
//     header("location: advance_search.php");
//     exit();
// }

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
    <title>Orders || D-Orders</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->
<style>
    /* ================= MOBILE RESPONSIVE (ADVANCE SEARCH PAGE) ================= */

/* Small devices (phones) */
@media (max-width: 576px) {

    /* Force full width layout */
    .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Card spacing */
    .card-body, .card-header {
        padding: 10px;
    }

    /* Form controls */
    .form-control {
        font-size: 14px;
        padding: 6px 8px;
        margin-bottom: 8px;
    }

    /* Select2 fix */
    .select2-container {
        width: 100% !important;
    }

    /* Buttons full width */
    .btn {
        width: 100%;
        margin-bottom: 6px;
        font-size: 13px;
        padding: 6px;
    }

    /* Button group */
    .btn-group {
        width: 100%;
        display: block;
    }

    .btn-group .btn {
        width: 100%;
    }

    /* Dropdown */
    .dropdown-menu {
        width: 100%;
    }

    /* Table scroll */
    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 900px;
    }

    /* Table text */
    .table th,
    .table td {
        font-size: 11px;
        padding: 6px;
        white-space: nowrap;
    }

    /* Prevent long text overflow */
    .table td {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Modal */
    .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }
}


/* Medium devices (tablets) */
@media (min-width: 577px) and (max-width: 768px) {

    .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card-body {
        padding: 12px;
    }

    .form-control {
        font-size: 14px;
    }

    .btn {
        font-size: 13px;
    }

    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 850px;
    }

    .table th,
    .table td {
        font-size: 11px;
        white-space: nowrap;
    }
}


/* Large tablets / small laptops */
@media (min-width: 769px) and (max-width: 1024px) {

    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 800px;
    }

    .table th,
    .table td {
        font-size: 12px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
    <style>
.select2-selection__arrow{
    display:none;
}
        .table th, .table td {
            padding: 0.72rem 10px;
            font-size: 10px;
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
                                    <li class="breadcrumb-item active">Orders
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">


                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       <div class="col-md-12">
                           <?php echo flash_msg(); ?>
                       </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Keyword Search</h4>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                           
                                            
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="account_id">Account</label>
                                                            
                                                            <select name="account_id" class="form-control select2" id="account_id" required>
                                                                <option value="all">All Accounts</option>
                                                                <?php $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                                                                while($account=$accounts->fetch_assoc()){ ?>
                                                                    <option value="<?=$account['id'];?>" <?php if(isset($_POST['account_id']) && $_POST['account_id'] == $account['id']){ echo 'selected'; } ?>><?=$account['account_name'];?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                   
                                                    <div class="col-6">
                                                        <label for="search_col">Feilds</label>
                                                        <select name="key" class="form-control" id="search_col" required>
                                                                <option value="all">Search in all</option>
                                                                <option value="OrderID" <?php if(isset($_POST['key']) && $_POST['key'] == 'OrderID'){ echo 'selected'; } ?>>Order No.</option>
                                            					<option value="ItemID" <?php if(isset($_POST['key']) && $_POST['key'] == 'ItemID'){ echo 'selected'; } ?>>Item Id / ASIN</option>
                                            					<option value="BuyerUserID" <?php if(isset($_POST['key']) && $_POST['key'] == 'BuyerUserID'){ echo 'selected'; } ?>>Buyer User ID</option>
                                            					<option value="ItemTitle" <?php if(isset($_POST['key']) && $_POST['key'] == 'ItemTitle'){ echo 'selected'; } ?>>Product Title</option>
                                            					<option value="ShippingAddress"<?php if(isset($_POST['key']) && $_POST['key'] == 'ShippingAddress'){ echo 'selected'; } ?>>Shipping Address</option>
                                            					<option value="ShippingAddress">City Name</option>
                                            					<option value="ShippingAddress">Postcode / Zipcode</option>
                                            					<option value="ShippingAddress">County / State</option>
                                            					<option value="ShipmentTrackingNumber" <?php if(isset($_POST['key']) && $_POST['key'] == 'ShipmentTrackingNumber'){ echo 'selected'; } ?>>Tracking</option>
                                            					<option value="SKU" <?php if(isset($_POST['key']) && $_POST['key'] == 'SKU'){ echo 'selected'; } ?>>SKU</option>
                                            					<option value="Missing_SKU" <?php if(isset($_POST['key']) && $_POST['key'] == 'Missing_SKU'){ echo 'selected'; } ?>>Missing SKU</option>
                                            					
                                                               
                                                            </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="keywords">Keywords</label>
                                                            <input type="text" class="form-control" id="keywords" name="value" value="<?php if(isset($_POST['value'])){echo $_POST['value']; } ?>"  placeholder=""/>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="search_keyword" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Search</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                            
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Bulk Search</h4>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                           
                                            
                                            <form class="" action="" method="post"  enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="account_id">Orders(every order id on new line)</label>
                                                            
                                                            <textarea class="form-control" name="orderids" rows="4"><?php if(isset($_POST['orderids']))echo $_POST['orderids']; ?></textarea>
                                                        </div>
                                                    </div>
                                                   
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="search_bulk" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Search</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                            
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($_POST['search_keyword']) || isset($_POST['search_bulk'])) { ?>
                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                    
                    <div class="row">
                       <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Search Orders</h4>
                                    <div class="btn-group" style="margin-right:10px">
                                        <button  class="btn btn-primary dropdown-toggle waves-effect waves-float waves-light" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(1)">Print DC UNG</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(2)">Print DC UNH</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(3)">Generate CSV</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(100)">Archive</a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="submitPrint(200)">Re-ship</a>
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
                                   <table class="table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width:4%;">SN</th>
                                                <th style="width:4%;">Date/Time</th>
                                                <th style="width:4%;">Order #</th>
                                                <th style="width:8%;">Seller</th>
                                                <th style="width:11%;">Buyer Userid</th>
                                                <th style="width:30%;">Item Name</th>
                                                <th style="width:8%;">SKU</th>
                                                <th style="width:6%;">P</th>
                                                <th style="width:6%;">SP</th>
                                                <th style="width:6%;">PC/ZP</th>
                                                <th style="width:10%;">SM</th>
                                                <th style="width:6%;">CT</th>
                                                <th style="width:5%;"><input type="checkbox" id="market_1"/></th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="market_1">
                                        <?php 
                                        if(isset($_POST['search_keyword'])){
                                            $account_id = $_POST['account_id'];
                                            $key = $_POST['key'];
                                            $value= trim($_POST['value']);
                                            if($account_id == 'all'){
                                                if($key == 'all'){
                                                    $query = "SELECT * FROM app_orders WHERE (CONCAT(OrderID, BuyerUserID, ShippingAddress, ShipmentTrackingNumber) LIKE '%$value%' || OrderID IN (SELECT OrderID FROM app_order_items WHERE CONCAT(ItemID, SKU, ItemTitle) LIKE '%$value%')) && IsArchived = '0'";
                                                }else{
                                                    
                                                    if($key == 'ItemID' || $key == 'ItemTitle' || $key == 'SKU'){
                                                        $query = "SELECT * FROM app_orders WHERE OrderID IN (SELECT OrderID FROM app_order_items WHERE $key LIKE '%$value%') && IsArchived = '0'";
                                                    }elseif($key == 'Missing_SKU'){
                                                        $query = "SELECT * FROM app_orders WHERE OrderID IN (SELECT OrderID FROM app_order_items WHERE SKU = '') && IsArchived = '0'";
                                                    }else{
                                                        $query = "SELECT * FROM app_orders WHERE $key LIKE '%$value%' && IsArchived = '0'";
                                                    }
                                                    
                                                }
                                            }else{
                                                if($key == 'all'){
                                                    $query = "SELECT * FROM app_orders WHERE (CONCAT(OrderID, BuyerUserID, ShippingAddress, ShipmentTrackingNumber) LIKE '%$value%' || OrderID IN (SELECT OrderID FROM app_order_items WHERE CONCAT(ItemID, SKU, ItemTitle) LIKE '%$value%')) && IsArchived = '0' && AccountID = '$account_id'";
                                                }else{
                                                    if($key == 'ItemID' || $key == 'ItemTitle' || $key == 'SKU'){
                                                        $query = "SELECT * FROM app_orders WHERE OrderID IN (SELECT OrderID FROM app_order_items WHERE $key LIKE '%$value%') && IsArchived = '0' && AccountID = '$account_id'";
                                                    }elseif($key == 'Missing_SKU'){
                                                        $query = "SELECT * FROM app_orders WHERE OrderID IN (SELECT OrderID FROM app_order_items WHERE SKU = '') && IsArchived = '0'  && AccountID = '$account_id'";
                                                    }else{
                                                        $query = "SELECT * FROM app_orders WHERE $key LIKE '%$value%' AND AccountID = '$account_id' && IsArchived = '0'";
                                                    }
                                                }
                                            }
                                            
                                        }elseif(isset($_POST['search_bulk'])){
                                            $textarea_array = array_map('trim',explode("\n", $_POST['orderids'])); // to remove extra spaces from each value of array
                                            $query = "SELECT * FROM app_orders WHERE OrderID IN ('".implode('\',\'',$textarea_array)."')";   
                                        }
                                        $orders = $conn->query($query);
                                        $sn = 0;
                                        while($order = $orders->fetch_assoc()){
                                            $shipa = json_decode($order['ShippingAddress'], true);
                                            $account = $conn->query("SELECT * FROM app_accounts where id = '{$order['AccountID']}'")->fetch_assoc();
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$order['OrderID']}'");
                                            $sku = '';
                                            $showItem = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= $item['ItemTitle'].' x '.$item['QuantityPurchased'].'<br>';
                                                $sku .= $item['SKU'].'<br>';
                                            }
                                            $sn++; ?>
                                    	    <tr>
                                    	        <td><?php echo $sn ?></td>
                                                <td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td>
                                                <td><?php echo $order['OrderID'] ?? 'N/A'; ?></td>
                                                <td><?php echo $account['account_name'] ?? 'N/A'; ?></td>
                                                <td><?php echo $order['BuyerUserID'] ?? 'N/A'; ?></td>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if($order['BuyerCheckoutMessage'] !=''){
                                                    echo '<br><span style="color:red">Buyer Note: '.$order['BuyerCheckoutMessage'].'</span>';
                                                }?>
                                                </td>
                                                <td><?php echo $sku; ?></td>
                                                <td><?php echo $order['Total']; ?></td>
                                                <td><?php echo $order['ShippingServiceCost']; ?></td>
                                                <td><?php echo $shipa['PostalCode']; ?></td>
                                                <td><?php echo isset($order['ShippingService']) ? substr($order['ShippingService'], 0, 16) : 'N/A'; ?></td>
                                                <td><?php echo $shipa['Country']; ?></td>
                                                
                                                <td><input type="checkbox" name="aorder[]" class="selectedId" value="<?php echo $order['ID']; ?>"></td>
                                                <td>
                                                    <button type="button" onclick="editOrder(<?php echo $order['ID']; ?>)" class="btn btn-icon btn-icon rounded-circle btn-flat-success waves-effect">
                                                            Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    </form>
                    <?php } ?>
                </section>
                <!--/ Row grouping -->
               

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        $(document).ready(function() {
            $('.select2').select2();
        });
        
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
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(2, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_GET['deactive'])){
$deactive = $_GET['deactive'];
$conn->query("update app_accounts set active = 0 where id = '$deactive'");
addSystemLog($conn, 'ACCOUNT DEACTIVE', "Account id $deactive has been deactivated", $deactive);
$_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account deactivated Successfully.</div></div>';
header("location: accounts.php");
exit();
}

if(isset($_GET['restore'])){
$restore = $_GET['restore'];
$conn->query("update app_accounts set deleted = 0 where id = '$restore'");
addSystemLog($conn, 'ACCOUNT RESTORE', "Account id $restore has been restored", $restore);
$_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Restored Successfully.</div></div>';
header("location: accounts.php?deleted=1");
exit();
}

if(isset($_GET['delete'])){
$delete_id = $_GET['delete'];
$conn->query("update app_accounts set active = 0, deleted = '1' where id = '$delete_id'");
addSystemLog($conn, 'ACCOUNT DELETE', "Account id $delete_id has been deleted", $delete_id);
$_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account deleted Successfully.</div></div>';
header("location: accounts.php");
exit();
}

if(isset($_GET['active'])){
$active = $_GET['active'];
$conn->query("update app_accounts set active = 1 where id = '$active'");
addSystemLog($conn, 'ACCOUNT ACTIVE', "Account id $active has been activated", $active);
$_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account activated Successfully.</div></div>';
header("location: accounts.php");
exit();
}

if(isset($_POST['edit'])){
    $editId = $_POST['edit'];
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $reference = $conn->real_escape_string($_POST['reference']);
    $days_threshold = $conn->real_escape_string($_POST['days_threshold']);
    $amount_threshold = $conn->real_escape_string($_POST['amount_threshold']);
    $price_tag = $conn->real_escape_string($_POST['price_tag']);
    
    $check_name = $conn->query("Select * from app_accounts where account_name = '$name' && id <> $editId")->num_rows;
    
    if($check_name > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account Name already exists.</div></div>';
        header("location: accounts.php");
        exit();
    }
    
    $conn->query("update app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', reference = '$reference', days_threshold = '$days_threshold', amount_threshold = '$amount_threshold', price_tag='$price_tag' where id = '$editId'");
    addSystemLog($conn, 'ACCOUNT UPDATE', "Account id $editId has been updated data", $editId);
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account name has been changed successfully..</div></div>';
    header("location: accounts.php");
    exit();
    
}

if(isset($_POST['payout_to_payment'])){
    $account_id = $_POST['accountId'];
    $amount = $_POST['amount'];
    
    $type = $_POST['type'];
    
    if($amount > 0){
        
        if($type=='auto'){
            $description = 'Amount Transferred to Payments';
            $date = date('Y-m-d H:i:s');
            $conn->query("insert into app_auto_payouts set account_id = '$account_id', amount = '-$amount', datetime = '$date', description = '$description'");
            $description = 'Amount Transferred from Auto Payouts';
            $sent_to = '';
            $status = 100;
            $type = 1;
            $conn->query("insert into app_payments set account_id = '$account_id', amount = '$amount', datetime = '$date', description = '$description', sent_to = '$sent_to', type='$type', status = '$status'");
            addSystemLog($conn, 'AUTO PAYOUT TO PAYMENT', "Amount $amount for account id $account_id has been transferred from auto payouts to payments", $account_id);
        }else{
            $description = 'Amount Transferred to Payments';
            $date = date('Y-m-d H:i:s');
            $conn->query("insert into app_payouts set account_id = '$account_id', amount = '-$amount', datetime = '$date', description = '$description'");
            $description = 'Amount Transferred from Payouts';
            $sent_to = '';
            $status = 100;
            $type = 1;
            $conn->query("insert into app_payments set account_id = '$account_id', amount = '$amount', datetime = '$date', description = '$description', sent_to = '$sent_to', type='$type', status = '$status'");
            addSystemLog($conn, 'PAYOUT TO PAYMENT', "Amount $amount for account id $account_id has been transferred from payout to payments", $account_id);
        }
        
    }
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payment Transferred Successfully.</div></div>';
    header("location: accounts.php");
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
    <title>Accounts || D-Orders</title>
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->
<style>
    /* =========================
   TABLE FIX (IMPORTANT)
========================= */

/* Enable horizontal scroll */
.card-datatable {
    overflow-x: auto;
}

/* Prevent table collapse */
.table {
    min-width: 1000px;
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

    /* Header right button fix */
    .content-header .col-6 {
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
    }

    .content-header .col-6:last-child {
        text-align: left !important;
        margin-top: 8px;
    }

    /* Total amount box */
    #total_amount {
        float: none !important;
        display: inline-block;
        font-size: 14px;
        padding: 8px;
        margin-top: 10px;
    }

    .btn {
        font-size: 12px;
        padding: 6px 10px;
        margin-bottom: 5px;
    }
}


/* =========================
   MOBILE (≤ 767px)
========================= */
@media (max-width: 767px) {

    /* Stack header properly */
    .breadcrumb-wrapper {
        margin-bottom: 10px;
    }

    /* Show deleted button full width */
    .content-header a.btn {
        width: 100%;
        display: block;
        float: none !important;
    }

    /* Table compact */
    .table th, 
    .table td {
        font-size: 10px;
        padding: 0.4rem 5px;
    }

    /* Action buttons stacked */
    td .btn {
        display: block;
        width: 100%;
        margin-bottom: 5px;
    }

    /* Fix images inside table */
    td img {
        max-width: 40px;
        height: auto;
    }

    /* Modal fix */
    .modal-dialog {
        margin: 10px;
    }

    .modal-content {
        font-size: 12px;
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

    #total_amount {
        font-size: 13px;
        padding: 6px;
    }

    .card-title {
        font-size: 14px;
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
                <div class="content-header-left col-md-12 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-6">
                            
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Accounts
                                    </li>
                                    
                                </ol>
                                
                            </div>
                        </div>
                        <div class="col-6">
                            <?php if(!isset($_GET['deleted'])){ ?>
                                <a type="button" href="?deleted=1" style="float:right;" class="btn btn-warning btn-xs btn-sm waves-effect waves-float waves-light">Show Deleted Accounts</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">


                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <?php if(isset($_GET['deleted'])) { ?>
                                <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Deleted Accounts</h4>
                                    
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Name</th>
                                                <th width="24%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $accounts = $conn->query("select * from app_accounts where deleted = 1 order by account_name asc");
                                        $sn = 0;
                                        $total_amount = 0;
                                        while($account = $accounts->fetch_assoc()){
                                            
                                          
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td><?php echo $account['account_name']; ?> <?php if($account['account_username']!=''){ echo '('.$account['account_username'].')';} ?></td>
                                                
                                                <td>
                                                    
                                                    <a type="button" href="?restore=<?php echo $account['id']; ?>" class="btn btn-primary btn-xs btn-sm waves-effect waves-float waves-light">Restore Account</a>
                                                </td>
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php }else{ ?>
                                <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Accounts</h4>
                                    <div>
                                        <h4 style="float:right;background: #1192d2;color: white;padding: 10px;"  id="total_amount">Total Amount : 0</h4>
                                        <!--<a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="add_account.php" ><i data-feather='plus'></i></a>-->
                                
                                    </div>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Acc/Type</th>
                                                <th>Name</th>
                                                <th>Reference</th>
                                                <!--<th>Token Expire</th>-->
                                                <th>Awaiting Shipment</th>
                                                <th>Balance</th>
                                                <th>Payouts</th>
                                                <th>AutoPayouts</th>
                                                <th width="24%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                                        $sn = 0;
                                        $total_amount = 0;
                                        while($account = $accounts->fetch_assoc()){
                                            
                                            // $totalSale = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && b.IsArchived = '0' && b.AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
                                            // $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}' and status = 100 and type = 1")->fetch_assoc()['amount']+0;
                                            // $balance = $totalPayments-$totalSale;
                                            $totalPayouts = $conn->query("select SUM(amount) as amount from app_payouts where account_id = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            $totalPayoutsAuto = $conn->query("select SUM(amount) as amount from app_auto_payouts where account_id = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            $balance = $account['balance'];
                                            $total_amount += $balance;
                                            
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td>
                                                    <?php if($account['account_type']==1){ ?>
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/EBay_logo.svg/2560px-EBay_logo.svg.png" style="width: 50px;" />
                                                    <?php }else if($account['account_type']==2 || $account['account_type']==3){ ?>
                                                        <img src="https://d-orders.co.uk/assets/d-orders_logo.png" style="width: 50px;" />
                                                    <?php }else{ ?>
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Amazon_logo.svg/2560px-Amazon_logo.svg.png" style="width: 50px;" />
                                                        
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo $account['account_name']; ?> <?php if($account['account_username']!=''){ echo '('.$account['account_username'].')';} ?></td>
                                                <td><?php echo $account['reference']; ?></td>
                                                <td><?php echo $account['awaiting_shipments']; //$conn->query("select * from app_orders where AccountID = '{$account['id']}' && IsArchived = '0' && IsPrinted = '0'")->num_rows; ?></td>
                                                <td><?php if($balance > 0){ echo '<span style="color:green">'.number_format($balance, 2).'</span>';}else{echo '<span style="color:red">'.number_format(abs($balance), 2).'</span>';} ?></td>
                                                <td><?php if($totalPayouts > 0){ echo '<span style="color:green">'.number_format($totalPayouts, 2).'</span>';}else{echo '<span style="color:red">'.number_format(abs($totalPayouts), 2).'</span>';} ?></td>
                                                <td><?php if($totalPayoutsAuto > 0){ echo '<span style="color:green">'.number_format($totalPayoutsAuto, 2).'</span>';}else{echo '<span style="color:red">'.number_format(abs($totalPayoutsAuto), 2).'</span>';} ?></td>
                                                
                                                <td>
                                                    
                                                    <button type="button" onclick="transferPayout(<?php echo $account['id']; ?>, 'manual', 1);" class="btn btn-primary btn-xs btn-sm waves-effect waves-float waves-light">Transfer Payout</button>
                                                    <?php if($account['active']==1){ ?>
                                                        <a type="button" href="?deactive=<?php echo $account['id']; ?>" class="btn btn-warning btn-xs btn-sm waves-effect waves-float waves-light">Deactive</a>
                                                    <?php }else{ ?>
                                                        <a type="button" href="?active=<?php echo $account['id']; ?>" class="btn btn-success btn-xs btn-sm waves-effect waves-float waves-light">Active</a>
                                                    <?php } ?>
                                                    <a type="button" href="?delete=<?php echo $account['id']; ?>" class="btn btn-danger btn-xs btn-sm waves-effect waves-float waves-light">Delete</a>
                                                </td>
                                            </tr>
                                            <? /*<div class="modal fade text-left" id="editForm<?php echo $sn; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel33">Edit Details</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="" method="post">
                                                            <div class="modal-body">
                                                                <label>Account Name: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Account Name" name="account_name" value="<?php echo $account['account_name']; ?>" class="form-control"  required/>
                                                                </div>

                                                            </div>
                                                             <div class="modal-body">
                                                                <label>Phone: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Phone" name="phone" value="<?php echo $account['phone']; ?>" class="form-control" />
                                                                </div>

                                                            </div>
                                                             <div class="modal-body">
                                                                <label>Email: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Email" name="email" value="<?php echo $account['email']; ?>" class="form-control" />
                                                                </div>

                                                            </div>
                                                             <div class="modal-body">
                                                                <label>Address: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Address" name="address" value="<?php echo $account['address']; ?>" class="form-control" />
                                                                </div>

                                                            </div>
                                                            <div class="modal-body">
                                                                <label>Reference: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Reference" name="reference" value="<?php echo $account['reference']; ?>" class="form-control" />
                                                                </div>

                                                            </div>
                                                            <div class="modal-body">
                                                                <label>Threshold (Days): </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Threshold (Days)" name="days_threshold" value="<?php echo $account['days_threshold']; ?>" class="form-control" required/>
                                                                </div>

                                                            </div>
                                                            <div class="modal-body">
                                                                <label>Threshold (Amount): </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Threshold (Amount)" name="amount_threshold" value="<?php echo $account['amount_threshold']; ?>" class="form-control" required/>
                                                                </div>

                                                            </div>
                                                            <div class="modal-body">
                                                                <label>Price Tag: </label>
                                                                <div class="form-group">
                                                                    <select name="price_tag" class="form-control">
                                                                        <?php 
                                                                    $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                                    while($price = $prices->fetch_assoc()){ ?>
                                                                        <option value="<?=$price['id'];?>" <?php if($price['id']==$account['price_tag']){echo'selected';} ?>><?=$price['name'];?></option>
                                                                    <?php } ?>
                                                                    </select>
                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <input name="edit" value="<?php echo $account['id']; ?>" type="hidden" />
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div> */?>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </section>
                <!--/ Row grouping -->
                    <div style="display:none;">
                       <button type="button" id="openmodelbtn" class="btn btn-outline-primary waves-effect" data-toggle="modal" data-target="#transferPayoutModel">
                           Click
                        </button>
                   </div>
                    <div class="modal fade text-left" id="transferPayoutModel" tabindex="-1" aria-labelledby="myModalLabel33" style="display: none;" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel33">Transfer Payout</h4>
                                    <button type="button" id="closemodelbtn" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <form action=""method="post" id="transferPayoutForm">
                                    <input type="hidden" name="accountId" value="" id="accountId"/>
                                    <div class="modal-body" id="transferPayoutInputs">
                                        
                                       
                                    </div>
                                    <div class="modal-footer" id="saveBtnDiv">
                                        <input type="hidden" name="payout_to_payment" value="1" />
                                        <button type="submit" class="btn btn-primary waves-effect waves-float waves-light">Submit</button>
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

    <!-- BEGIN: Page JS-->
    <script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>

    <script>
        $(window).on('load', function() {
            $("#total_amount").html("Total Amount : <?php echo number_format(abs($total_amount), 2); ?>");
        });
    </script>
    
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $(".dt-row-grouping-t").DataTable({
            "bPaginate": false, //hide pagination
            "bInfo": false, // hide showing entries
        });
        
        function transferPayout(id, type, click) {
            if(click==1){
                $("#openmodelbtn").click();
            }
            console.log(id, type, click);
            $("#transferPayoutInputs").html('<center><img src="app-assets/ajax_loading.gif" /></center>');
            $.ajax({
                    url : "inc/ajax.php",
                    method : "POST",
                    data : {transferPayout: id, payoutType: type},
                    async : true,
                    dataType : 'html',
                    success: function(data){
                        console.log(data);
                       $("#accountId").val(id);
                       $("#transferPayoutInputs").html(data);
                        
                    }
                });
        }
    </script>
         <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
    exit();
}

// Check permissions
if(!in_array(35, $permissions_allow ?? [])){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

// Handle settings save
if(isset($_POST['savesettings'])){
    $columnsSettings = isset($_POST['settings']) ? implode(",", array_keys($_POST['settings'])) : '';
    $accounts_columns = isset($_POST['accounts_columns']) ? $conn->real_escape_string($_POST['accounts_columns']) : '';
    
    $conn->query("UPDATE app_settings SET value = '$columnsSettings' WHERE name = 'accounts_columns_settings'");
    $conn->query("UPDATE app_settings SET value = '$accounts_columns' WHERE name = 'accounts_columns'");
    addSystemLog($conn, 'SETTING UPDATED', "Account Details page settings has been updated", "");
    
    header("location: accounts_details.php");
    exit();
}

// Handle account hide/show
if(isset($_GET['hide_account'])){
    $account_id = intval($_GET['hide_account']);
    $conn->query("UPDATE app_accounts SET hidden = '1' WHERE id = '$account_id'");
    addSystemLog($conn, 'ACCOUNT HIDE', "Account id $account_id has been set to hidden", $account_id);
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Hide Successfully.</div></div>';
    header("location: accounts_details.php");
    exit();
}

if(isset($_GET['show_account'])){
    $account_id = intval($_GET['show_account']);
    $conn->query("UPDATE app_accounts SET hidden = '0' WHERE id = '$account_id'");
    addSystemLog($conn, 'ACCOUNT SHOW', "Account id $account_id has been set to show", $account_id);
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Restored Successfully.</div></div>';
    header("location: accounts_details.php?hidden=1");
    exit();
}

// Get settings
$settings_result = $conn->query("SELECT * FROM app_settings");
$settings = [];
while($row = $settings_result->fetch_assoc()) {
    $settings[$row['name']] = $row['value'];
}

$columnsArray = [];
if(!empty($settings['accounts_columns_settings'])) {
    $columnsArray = explode(",", $settings['accounts_columns_settings']);
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
    <link rel="stylesheet" href="https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
   /* =========================================
   BASE (Desktop First Adjustments)
========================================= */

.container,
.content-wrapper {
    padding-left: 15px;
    padding-right: 15px;
}

.table {
    width: 100%;
    word-break: break-word;
}

.btn {
    white-space: nowrap;
}

/* =========================================
   LARGE DEVICES (≤1200px)
========================================= */
@media (max-width: 1200px) {

    .content-wrapper {
        padding: 10px;
    }

    .card-body {
        padding: 12px;
    }

    h4 {
        font-size: 18px;
    }
}

/* =========================================
   TABLET DEVICES (≤992px)
========================================= */
@media (max-width: 992px) {

    .col-md-6,
    .col-md-4,
    .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .breadcrumb {
        flex-wrap: wrap;
    }

    .breadcrumb-item {
        font-size: 13px;
    }

    .btn {
        font-size: 13px;
        padding: 6px 10px;
    }

    .card {
        margin-bottom: 15px;
    }
}

/* =========================================
   SMALL TABLETS / LARGE PHONES (≤768px)
========================================= */
@media (max-width: 768px) {

    body {
        font-size: 13px;
    }

    h4 {
        font-size: 16px;
    }

    .content-wrapper {
        padding: 8px;
    }

    .card-body {
        padding: 10px;
    }

    /* TABLE FIX */
    .table {
        font-size: 12px;
    }

    .table th,
    .table td {
        padding: 6px;
    }

    /* BUTTONS STACK */
    .btn {
        width: 100%;
        margin-bottom: 5px;
    }

    /* FLEX FIX */
    .d-flex {
        flex-wrap: wrap !important;
    }

    /* MODAL */
    .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }

    /* HEADER BUTTONS */
    .card-header div {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
}

/* =========================================
   MOBILE DEVICES (≤576px)
========================================= */
@media (max-width: 576px) {

    body {
        font-size: 12px;
    }

    h4 {
        font-size: 14px;
    }

    .breadcrumb {
        font-size: 11px;
    }

    .card-header {
        padding: 10px;
    }

    .card-body {
        padding: 8px;
    }

    /* TABLE SCROLL */
    .table-responsive {
        overflow-x: auto;
    }

    /* TABLE COMPACT */
    .table th,
    .table td {
        font-size: 11px;
        padding: 5px;
    }

    /* BUTTON FIX */
    .btn {
        font-size: 12px;
        padding: 5px 8px;
    }

    /* IMAGES */
    img {
        max-width: 100%;
        height: auto;
    }
}

/* =========================================
   EXTRA SMALL DEVICES (≤400px)
========================================= */
@media (max-width: 400px) {

    h4 {
        font-size: 13px;
    }

    .btn {
        font-size: 11px;
        padding: 4px 6px;
    }

    .table th,
    .table td {
        font-size: 10px;
    }

    .card-body {
        padding: 6px;
    }
}
@media (max-width: 768px) {

    table th {
        white-space: nowrap !important;
        writing-mode: horizontal-tb !important;
        transform: none !important;
    }

    table td {
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
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
                            
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">

                <div class="modal fade text-left" id="columnsSettings" tabindex="-1" aria-labelledby="myModalLabel33" style="display: none;" aria-hidden="true">
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
                                            <a class="nav-link" id="columns-settings-tab-click" data-toggle="tab" href="#columns-settings-tab" role="tab" aria-selected="false">Edit Columns</a>
                                        </li>
                                    </ul>
                                    <style>
                                        .float-right{
                                            float:right;
                                        }
                                        .bootstrap-tagsinput{
                                            width:100%;
                                        }
                                        .bootstrap-tagsinput .tag {
                                            margin-right: 2px;
                                            color: white;
                                            background: #2196f3;
                                            border-radius: 5px;
                                            padding: 2px;
                                        }
                                    </style>
                                    
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="page-settings-tab" role="tabpanel">
                                            <table class="table table-sm table-bordered">
                                                
                                                <tbody>
                                                    <tr>
                                                        <?php 
                                                        $i=0;
                                                        
                                                        if(!empty($settings['accounts_columns'])){
                                                        $accounts_columns = $settings['accounts_columns'].',Days Threshold,Amount Threshold';
                                                        $accounts_columns = explode(",", $accounts_columns);
                                                        foreach($accounts_columns as $name){
                                                            if(empty($name)) continue;
                                                            $i++; ?>
                                                        <td><label><?=htmlspecialchars($name);?> :</label> <input name="settings[<?=htmlspecialchars($name);?>]" <?php if(in_array($name, $columnsArray)){echo 'checked="checked"';} ?> type="checkbox" class="float-right" value="1"></td>
                                                        <?php if($i % 3 == 0) { ?>
                                                        </tr>
                                                        <tr>
                                                        <?php }}} ?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="columns-settings-tab"  role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <input type="text" id="tags" name="accounts_columns" data-role="tagsinput" class="form-control" style="width: 100%;"  placeholder="e.g city,country,area" value="<?=htmlspecialchars($settings['accounts_columns'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
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
                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            
                                <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Accounts</h4>
                                    <div>
                                        <?php if(isset($_GET['hidden'])){ ?>
                                        <a href="?" style="float:right;margin-right:10px" class="btn btn-success">
                                            <i data-feather='arrow-left'></i>
                                            <span>Go Back</span>
                                        </a>
                                        <?php }else{ ?>
                                        <a href="?hidden=1" style="float:right;margin-right:10px" class="btn btn-danger">
                                            <i data-feather='user'></i>
                                            <span>Hidden Accounts</span>
                                        </a>
                                        <?php } ?>
                                        
                                        <button type="button" data-toggle="modal" data-target="#columnsSettings" style="float:right;margin-right:10px" class="btn btn-primary">
                                            <i data-feather='settings'></i>
                                            <span>Settings</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-datatable">
                                    <?php if(isset($_GET['hidden'])){ ?>
                                    <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Name</th>
                                                <?php
                                                    if(!empty($settings['accounts_columns'])){
                                                        $accounts_columns = explode(",", $settings['accounts_columns']);
                                                        foreach($accounts_columns as $name){
                                                            if(empty($name)) continue;
                                                            if(in_array($name, $columnsArray)){ echo '<th>'.htmlspecialchars($name).'</th>'; }
                                                        }
                                                    }
                                                ?>
                                                
                                                <?php if(in_array("Days Threshold", $columnsArray)){ echo '<th>Days Threshold</th>'; } ?>
                                                <?php if(in_array("Amount Threshold", $columnsArray)){ echo '<th>Amount Threshold</th>'; } ?>
                                                <th width="24%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $accounts = $conn->query("SELECT * FROM app_accounts WHERE hidden = '1' ORDER BY account_name ASC");
                                        $sn = 0;
                                        $total_amount = 0;
                                        while($account = $accounts->fetch_assoc()){
                                            $other = json_decode($account['other'] ?? '{}', true);
                                           
                                            
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                                                <?php
                                                if (!empty($settings['accounts_columns'])) {
                                                    $accounts_columns = explode(",", $settings['accounts_columns']);
                                                    foreach ($accounts_columns as $name) {
                                                        if (empty($name)) continue;
                                                        if (in_array($name, $columnsArray)) { 
                                                            echo '<td>'; 
                                                            if (is_array($other) && array_key_exists($name, $other)) {
                                                                echo htmlspecialchars($other[$name]);
                                                            }
                                                            echo '</td>'; 
                                                        }
                                                    }
                                                }
                                                ?>

                                                <?php if(in_array("Days Threshold", $columnsArray)){ echo '<td>'.htmlspecialchars($account['days_threshold']).'</td>'; } ?>
                                                <?php if(in_array("Amount Threshold", $columnsArray)){ echo '<td>'.htmlspecialchars($account['amount_threshold']).'</td>'; } ?>
                                                <td>
                                                    <a type="button" href="?show_account=<?php echo $account['id']; ?>" class="btn btn-success btn-xs btn-sm waves-effect waves-float waves-light">Show Account</a>
                                                </td>
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php }else{ ?>
                                    <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Name</th>
                                                <?php
                                                    if(!empty($settings['accounts_columns'])){
                                                        $accounts_columns = explode(",", $settings['accounts_columns']);
                                                        foreach($accounts_columns as $name){
                                                            if(empty($name)) continue;
                                                            if(in_array($name, $columnsArray)){ echo '<th>'.htmlspecialchars($name).'</th>'; }
                                                        }
                                                    }
                                                ?>
                                                <?php if(in_array("Days Threshold", $columnsArray)){ echo '<th>Days Threshold</th>'; } ?>
                                                <?php if(in_array("Amount Threshold", $columnsArray)){ echo '<th>Amount Threshold</th>'; } ?>
                                                <th width="24%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $accounts = $conn->query("SELECT * FROM app_accounts WHERE hidden = '0' ORDER BY account_name ASC");
                                        $sn = 0;
                                        $total_amount = 0;
                                        while($account = $accounts->fetch_assoc()){
                                            $other = json_decode($account['other'] ?? '{}', true);
                                           
                                            
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                                                <?php
                                                    if(!empty($settings['accounts_columns'])){
                                                    $accounts_columns = explode(",", $settings['accounts_columns']);
                                                    foreach($accounts_columns as $name){
                                                        if(empty($name)) continue;
                                                        if(in_array($name, $columnsArray)){ 
                                                            echo '<td>'; 
                                                            // Ensure $other is always an array
                                                            $other = is_array($other) ? $other : [];
                                                            if(array_key_exists($name, $other) && !empty($other[$name])){ 
                                                                echo htmlspecialchars($other[$name]); 
                                                            }
                                                            echo '</td>'; 
                                                        }
                                                    }
                                                }
                                                ?>
                                                <?php if(in_array("Days Threshold", $columnsArray)){ echo '<td>'.htmlspecialchars($account['days_threshold']).'</td>'; } ?>
                                                <?php if(in_array("Amount Threshold", $columnsArray)){ echo '<td>'.htmlspecialchars($account['amount_threshold']).'</td>'; } ?>
                                                <td>
                                                    <a type="button" href="edit_account.php?editid=<?php echo $account['id']; ?>" class="btn btn-primary btn-xs btn-sm waves-effect waves-float waves-light">View & Edit</a>
                                                    <a type="button" href="?hide_account=<?php echo $account['id']; ?>" class="btn btn-danger btn-xs btn-sm waves-effect waves-float waves-light">Hide Account</a>
                                                </td>
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Row grouping -->


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
    <script src="https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
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
    </script>
         <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
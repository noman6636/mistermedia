<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(1, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['add_account'])){
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $auth_token = $conn->real_escape_string($_POST['auth_token']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $days_threshold = $conn->real_escape_string($_POST['days_threshold']);
    $amount_threshold = $conn->real_escape_string($_POST['amount_threshold']);
    $price_tag = $conn->real_escape_string($_POST['price_tag']);
    $token_expire = date('Y-m-d H:i:s', strtotime($conn->real_escape_string($_POST['token_expire'])));

    $check_name = $conn->query("select * from app_accounts where account_name = '$account_name'");
    if($check_name->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account name already found in database.</div></div>';
        header("location: add_account.php");
        exit();
    }
    $now = date('Y-m-d H:i:s');
    $account_username = $_SESSION['account_username'] ?? null;

    if (!empty($account_username)) {
        $check_uname = $conn->query("select * from app_accounts where account_username = '$account_username'");
        if($check_uname->num_rows > 0){
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account Username already found in database.</div></div>';
            // header("location: add_account.php");
            exit();
        }
    }
    
    // echo "insert into app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', account_username = '$account_username', auth_token = '$auth_token', token_expire='$token_expire', created_date = '$now'";
    // exit();
    $conn->query("insert into app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', days_threshold = '$days_threshold', amount_threshold = '$amount_threshold', price_tag='$price_tag', account_username = '$account_username', auth_token = '$auth_token', token_expire='$token_expire', created_date = '$now'");
    unset($_SESSION['ebaySid']);
    unset($_SESSION['account_username']);
    
    addSystemLog($conn, 'ACCOUNT ADDED', "New account ($account_name) has been added", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Created Successfully.</div></div>';
    header("location: add_account.php");
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
    <title>Add Account || D-Orders</title>
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
   BASE IMPROVEMENTS
========================= */

/* Center card on large screens */
.content-body .col-md-6 {
    margin: 0 auto;
}


/* =========================
   TABLET (≤ 991px)
========================= */
@media (max-width: 991px) {

    /* Full width instead of half */
    .content-body .col-md-6 {
        max-width: 100%;
        flex: 0 0 100%;
    }

    .card {
        margin-bottom: 15px;
    }

    .card-body {
        padding: 15px;
    }

    .btn {
        font-size: 13px;
        padding: 8px 12px;
    }

    .form-group label {
        font-size: 13px;
    }
}


/* =========================
   MOBILE (≤ 767px)
========================= */
@media (max-width: 767px) {

    /* Full width layout */
    .content-body .col-md-6 {
        padding: 0 10px;
    }

    /* Stack buttons full width */
    .form-group button {
        width: 100%;
        display: block;
        margin-right: 0 !important;
    }

    /* Better spacing */
    .form-group {
        margin-bottom: 15px;
    }

    .card-body {
        padding: 12px;
    }

    /* Breadcrumb resize */
    .breadcrumb {
        font-size: 12px;
    }

    /* Header spacing fix */
    .content-header {
        margin-bottom: 10px;
    }
}


/* =========================
   SMALL MOBILE (≤ 480px)
========================= */
@media (max-width: 480px) {

    .btn {
        font-size: 12px;
        padding: 7px 10px;
    }

    .form-group label {
        font-size: 12px;
    }

    .card-title {
        font-size: 15px;
    }

    .card-body {
        padding: 10px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static" data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>

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
                                    <li class="breadcrumb-item active">Add Account
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">
                <!-- account setting page -->
                <section id="page-account-settings">
                    <div class="row">
                       

                        <!-- right content section -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                                <div class="row">
                                                
                                                    
                                                     <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Connect Ebay</label><br> 
                                                            <button type="button" class="btn btn-primary mr-1 mt-1" onclick="createPop('connect_ebay.php', 'Ebay Login')">Sign in With Ebay</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Connect Amazon</label><br>
                                                            <button type="button" class="btn btn-primary mr-1 mt-1" onclick="createPop('connect_amazon.php', 'Amazon Login')">Sign in With Amazon</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Create Account</label><br>
                                                            <button type="button" class="btn btn-primary mr-1 mt-1" onclick="window.location.href='add_static_account.php'">Direct Account</button>
                                                        </div>
                                                    </div>
                                                   
                                                   
                                                    
                                                    
                                                </div>
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
    </script>
     <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if($admin['role_id'] != 1){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['add_user'])){
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = md5($_POST['password']);
    $role_id = $conn->real_escape_string($_POST['role_id']);
    
    
    $check_username = $conn->query("select * from app_admins where username = '$username'");
    if($check_username->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Username Already exists in database.</div></div>';
        header("location: add_user.php");
        exit();
    }
    
    $check_email = $conn->query("select * from app_admins where email = '$email'");
    if($check_email->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Email Already exists in database.</div></div>';
        header("location: add_user.php");
        exit();
    }
    
    $conn->query("insert into app_admins set username = '$username', email = '$email', role_id = '$role_id', password = '$password'");
    addSystemLog($conn, 'USER ADDED', "New User ($username) has been added", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">User added successfully.</div></div>';
    header("location: add_user.php");
    exit();
}

if(isset($_POST['edit_user'])){
    $editId = $_POST['edit_user'];
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role_id = $conn->real_escape_string($_POST['role_id']);
    
    $check_username = $conn->query("select * from app_admins where username = '$username' && id <> $editId");
    if($check_username->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Username Already exists in database.</div></div>';
        header("location: add_user.php?edit=".$editId);
        exit();
    }
    
    $check_email = $conn->query("select * from app_admins where email = '$email'  && id <> $editId");
    if($check_email->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Email Already exists in database.</div></div>';
        header("location: add_user.php?edit=".$editId);
        exit();
    }
    
    $conn->query("update app_admins set username = '$username', email = '$email', role_id = '$role_id' where id = '$editId'");
    if(!empty($_POST['password'])){
        $password = md5($_POST['password']);
        $conn->query("update app_admins set password = '$password' where id = '$editId'");
    }
    
    addSystemLog($conn, 'USER UPDATED', "User ($username) with id ($editId) has been updated", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">User has been updated.</div></div>';
    header("location: users.php");
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
    <title>Add Users || D-Orders</title>
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
    /* ===== MOBILE RESPONSIVE ===== */

/* Tablets */
@media (max-width: 991px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card {
        margin-bottom: 15px;
    }
}

/* Mobile devices */
@media (max-width: 768px) {

    /* Layout spacing */
    .content-wrapper {
        padding: 10px;
    }

    .card-body {
        padding: 15px 10px;
    }

    /* Form inputs */
    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    /* Labels */
    label {
        font-size: 13px;
        margin-bottom: 4px;
    }

    /* Buttons */
    .btn {
        width: 100%;
        margin-top: 10px;
        font-size: 14px;
        padding: 10px;
    }

    /* Breadcrumb */
    .breadcrumb {
        font-size: 12px;
        flex-wrap: wrap;
    }

    /* Card width fix */
    .col-md-6 {
        padding: 0;
    }

}

/* Small phones */
@media (max-width: 480px) {

    .card {
        border-radius: 10px;
    }

    .card-body {
        padding: 12px 8px;
    }

    .form-control {
        font-size: 13px;
    }

    .btn {
        font-size: 13px;
        padding: 9px;
    }

    .breadcrumb {
        font-size: 11px;
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
                                    <li class="breadcrumb-item active">Add User
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
                        <div class="col-12 col-md-6 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            <?php if(isset($_GET['edit'])){
                                            $user = $conn->query("select * from app_admins where id = '{$_GET['edit']}'");
                                            if($user->num_rows < 1) {
                                                header("location: users.php");
                                                exit();
                                            }
                                            $user = $user->fetch_assoc();
                                           
                                            ?>
                                            
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Username</label>
                                                            <input type="text" class="form-control" id="username" name="username" value="<?=$user['username'];?>" placeholder="Username" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?=$user['email'];?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="password">Password (Leave blank if don't want to change)</label>
                                                            <input type="text" class="form-control" id="password" name="password" placeholder="Password"/>
                                                        </div>
                                                    </div>
                                                     <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="role">Role</label>
                                                            
                                                            <select name="role_id" class="form-control" id="role" required>
                                                                <option value="">Select Role</option>
                                                                <?php $roles = $conn->query("select * from app_roles order by id asc");
                                                                while($role=$roles->fetch_assoc()){ ?>
                                                                    <option value="<?=$role['id'];?>" <?php if($user['role_id'] == $role['id']){ echo 'selected';} ?>><?=$role['name'];?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="edit_user" value="<?=$user['id'];?>" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                                
                                            <?php }else{ ?>
                                            
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Username</label>
                                                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="text" class="form-control" id="email" name="email" placeholder="Email" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="password">Password</label>
                                                            <input type="text" class="form-control" id="password" name="password" placeholder="Password" required/>
                                                        </div>
                                                    </div>
                                                     <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="role">Role</label>
                                                            
                                                            <select name="role_id" class="form-control" id="role" required>
                                                                <option value="">Select Role</option>
                                                                <?php $roles = $conn->query("select * from app_roles order by id asc");
                                                                while($role=$roles->fetch_assoc()){ ?>
                                                                    <option value="<?=$role['id'];?>"><?=$role['name'];?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="add_user" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
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
<?php

require_once "inc/config.php";
require_once "inc/functions.php";

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']); // Note: Still insecure for production

    $stmt = $conn->prepare("SELECT * FROM app_admins WHERE (username = ? OR email = ?) AND password = ? LIMIT 1");
    $stmt->bind_param("sss", $username, $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Incorrect username or password.</div></div>';
        header("Location: login.php");
        exit();
    }

    $adminDtl = $result->fetch_assoc();
    
    $_SESSION['admin_id'] = $adminDtl['id'];

    addSystemLog($conn, 'LOGIN', 'User Logged in to System', '');
   
    // Redirect to ref_url if set
    // if (!empty($_POST['ref_url'])) {
    //     header("Location: " . $_POST['ref_url']);
    //     exit();
    // }

    header("Location: index.php");
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
    <title>Login || D-Orders</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/vendors.min.css">
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
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/forms/form-validation.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/page-auth.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="horizontal-layout horizontal-menu blank-page navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="blank-page">
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <div class="auth-wrapper auth-v1 px-2">
                    <div class="auth-inner py-2">
                        <!-- Login v1 -->
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="javascript:void(0);" class="brand-logo">
                                    <img src="assets/d-orders_logo.png" style="width: 190px;" />
                                </a>
                                <?php echo flash_msg(); ?>

                                <h4 class="card-title mb-1">Welcome to D-Orders</h4>
                                <p class="card-text mb-2">Please sign-in to your account to access software</p>

                                <form class="auth-login-form mt-2" action="" method="POST">
                                    <div class="form-group">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" tabindex="1" autofocus />
                                    </div>

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                            <label for="login-password">Password</label>
                                            
                                        </div>
                                        <div class="input-group input-group-merge form-password-toggle">
                                            <input type="password" class="form-control form-control-merge" id="login-password" name="password" tabindex="2" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                            <div class="input-group-append">
                                                <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ref_url" value="<?php if(isset($_SERVER['HTTP_REFERER'])){ echo $_SERVER['HTTP_REFERER']; } ?>" />
                                    <button class="btn btn-primary btn-block" tabindex="4">Sign in</button>
                                </form>
                                <p class="text-center mt-2">
                                  <span>&copy; Developed by <a href="" target="_blank">Waseem abbas</a></span>
                                </p>
                               
                            </div>
                        </div>
                        <!-- /Login v1 -->
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src="app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="app-assets/js/scripts/pages/page-auth-login.js"></script>
    <!-- END: Page JS-->

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
    </script>
</body>
<!-- END: Body-->

</html>
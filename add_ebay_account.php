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
    $reference = $conn->real_escape_string($_POST['reference']);
    $days_threshold = $conn->real_escape_string($_POST['days_threshold']);
    $amount_threshold = $conn->real_escape_string($_POST['amount_threshold']);
    $price_tag = $conn->real_escape_string($_POST['price_tag']);
    $auto_payouts = $conn->real_escape_string($_POST['auto_payouts']);
    $token_expire = date('Y-m-d H:i:s', strtotime($conn->real_escape_string($_POST['token_expire'])));

    $other = array();
    
    
    foreach($_POST['other_details_name'] as $k=> $name){
        $value = $_POST['other_details_value'][$k];
        if(trim($name) != '' && trim($value) != ''){
            $otherData['name'] = $name;
            $otherData['value'] = $value;
            $other[] = $otherData;
        }
    }
   
    if(count($other) == 0){
        $other = '';
    }else{
        $other = $conn->real_escape_string(json_encode($other));
    }
    
    
    $check_name = $conn->query("select * from app_accounts where account_name = '$account_name'");
    if($check_name->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account name already found in database.</div></div>';
        header("location: add_account.php");
        exit();
    }
    $now = date('Y-m-d H:i:s');
    $account_username = $_SESSION['account_username'];
    $account_username = $_SESSION['account_username'] ?? null;

    if (!empty($account_username)) {
        $check_uname = $conn->query("select * from app_accounts where account_username = '$account_username'");
        if($check_uname->num_rows > 0){
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account Username already found in database.</div></div>';
            // header("location: add_account.php");
            exit();
        }
    }
    // echo "insert into app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', reference = '$reference', other = '$other', days_threshold = '$days_threshold', amount_threshold = '$amount_threshold', price_tag='$price_tag', account_username = '$account_username', auth_token = '$auth_token', token_expire='$token_expire', created_date = '$now', account_type = '1'";
    // exit();
    $conn->query("insert into app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', reference = '$reference', other = '$other', days_threshold = '$days_threshold', amount_threshold = '$amount_threshold', price_tag='$price_tag', auto_payout = '$auto_payouts', account_username = '$account_username', auth_token = '$auth_token', token_expire='$token_expire', created_date = '$now', account_type = '1'");
    unset($_SESSION['ebaySid']);
    unset($_SESSION['account_username']);
    
    addSystemLog($conn, 'ACCOUNT ADDED', "New Ebay account ($account_name) has been added", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Created Successfully.</div></div>';
    header("location: add_account.php");
    exit();
}

if (empty($_SESSION['account_username']) && !empty($_GET['username'])) {
    $_SESSION['account_username'] = $_GET['username'];
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
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    
                                                    
                                                    <?php if(isset($_SESSION['ebaySid'])){
                                                        require_once('inc/Keys.php');
                                                        require_once('inc/eBaySession.php');
                                                        $sid = $_SESSION['ebaySid'];
                                                        $siteID = 0;
                                                        $verb = 'FetchToken';
                                                        $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
                                                        $requestXmlBody .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                                                        $requestXmlBody .= '<SessionID>'.$sid.'</SessionID>';
                                                        $requestXmlBody .= '</FetchTokenRequest>';

                                                        $session = new eBaySession('', $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
                                                        
                                                        $responseXml = $session->sendHttpRequest($requestXmlBody);
                                                        if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
                                                            die('<P>Error sending request');
                                                        }
                                                        $res = XML2Array($responseXml);
                                                        if($res['Ack'] == 'Failure'){
                                                            unset($_SESSION['ebaySid']);
                                                            header("location: add_account.php");
                                                            exit();
                                                        }
                                                        ?>
                                                        <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Account Name</label>
                                                            <input type="text" class="form-control" id="name" name="account_name" placeholder="Account Name" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Account Username</label>
                                                            <input type="text" class="form-control" id="name" name="account_username" placeholder="" value="<?php echo $_SESSION['account_username'] ?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="phone">Phone</label>
                                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="" value="" />
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="email" class="form-control" id="email" name="email" placeholder="" value=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="address">Address</label>
                                                            <input type="text" class="form-control" id="address" name="address" placeholder="" value=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="reference">Reference</label>
                                                            <input type="text" class="form-control" id="reference" name="reference" placeholder="" value=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="days_threshold">Threshold (Days)</label>
                                                            <input type="number" class="form-control" id="days_threshold" name="days_threshold" placeholder="" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="amount_threshold">Threshold (Amount)</label>
                                                            <input type="number" class="form-control" step=".01" id="amount_threshold" name="amount_threshold" placeholder="" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="amount_threshold">Price Tag</label>
                                                            <select name="price_tag" class="form-control">
                                                                <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ ?>
                                                                <option value="<?=$price['id'];?>"><?=$price['name'];?></option>
                                                            <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="auto_payouts">Auto Payouts</label>
                                                            <select name="auto_payouts" class="form-control">
                                                               <option value="1" selected>Enable</option>
                                                               <option value="0">Disable</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Account Username</label>
                                                            <input type="text" class="form-control" id="name" name="account_username" placeholder="" value="<?php echo $_SESSION['account_username']; ?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Auth Token</label>
                                                            <textarea class="form-control" name="auth_token" style="width:100%" rows="8" readonly><?php echo $res['eBayAuthToken']; ?></textarea>
                                                            <input type="hidden" name="token_expire" value="<?php echo $res['HardExpirationTime']; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <input type="hidden" name="add_account" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                        <button type="button" class="btn btn-primary mr-1 mt-1" onclick="createPop('connect_ebay.php?revoke=1', 'Ebay Login')" >Revoke Token</button>
                                                    </div>
                                                    <?php }else{ header("location: add_account.php"); } ?>
                                                   
                                                   
                                                    
                                                    
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
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            
                                                <div class="row">
                                                
                                                    
                                                    
                                                    <div class="col-12">
                                                        <h1 style="font-size: 17px;">Other Details</h1>
                                                        <div class="row" id="other_inputs">
                                                            <?php 
                                                            if($settings['accounts_columns'] != ''){
                                                            $accounts_columns = explode(",", $settings['accounts_columns']);
                                                            foreach($accounts_columns as $name){ ?>
                                                                <div class="col-4">
                                                                    <div class="form-group">
                                                                        <input type="text" class="form-control" name="other_details_name[<?=$name;?>]" placeholder="Input Name" value="<?=$name;?>" readonly/>
                                                                    </div>
                                                                </div>
                                                                 <div class="col-8">
                                                                    <div class="form-group">
                                                                        <input type="text" class="form-control" name="other_details_value[<?=$name;?>]" placeholder="Input Value"/>
                                                                    </div>
                                                                </div>
                                                              <?php }} ?>
                                                        </div>
                                                    </div>
                                                  
                                                   
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                            <script>
                                function addInputBox(){
                            	    count = 1;
                                    var htmlInput = '<div class="col-4"><div class="form-group"><input type="text" class="form-control" name="other_details_name['+count+']" placeholder="Input Name"/></div></div><div class="col-8"><div class="form-group"><input type="text" class="form-control" name="other_details_value['+count+']" placeholder="Input Value"/></div></div>';
                                    // document.getElementById("other_inputs").appendChild(htmlInput);
                                    $("#other_inputs").append(htmlInput);
                                    count++;
                                    
                                }
                            </script>
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
</body>
<!-- END: Body-->

</html>
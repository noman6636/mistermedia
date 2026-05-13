<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(2, $permissions_allow) && !in_array(35, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_GET['editid'])){
    $editid = $_GET['editid'];
    
    $account = $conn->query("SELECT * FROM app_accounts WHERE id = '$editid'");
    if($account->num_rows > 0){
        $account = $account->fetch_assoc();
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
        header("location: index.php");
        exit();
    }
}else{
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['edit_account'])){
    
    $editid = $account['id'];
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $reference = $conn->real_escape_string($_POST['reference']);
    $days_threshold = $conn->real_escape_string($_POST['days_threshold']);
    $amount_threshold = $conn->real_escape_string($_POST['amount_threshold']);
    $price_tag = $conn->real_escape_string($_POST['price_tag']);
    $auto_payouts = $conn->real_escape_string($_POST['auto_payouts']);
    $now = date('Y-m-d H:i:s');
    
    $other = array();
    
     
    if(isset($_POST['other_details_value'])){
        if($settings['accounts_columns'] != ''){
        $accounts_columns = explode(",", $settings['accounts_columns']);
        foreach($accounts_columns as $name){
            $other[$name] = $_POST['other_details_value'][$name];
        }}
    }
    
    
   
    $other = $conn->real_escape_string(json_encode($other));
    
    $check_name = $conn->query("select * from app_accounts where account_name = '$account_name' && id <> '$editid'");
    if($check_name->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account name already found in database.</div></div>';
        header("location: edit_account.php?editid=".$editid);
        exit();
    }
    
    $conn->query("update app_accounts set account_name = '$account_name', phone = '$phone', email = '$email', address = '$address', reference = '$reference', other = '$other', days_threshold = '$days_threshold', amount_threshold = '$amount_threshold', price_tag='$price_tag', auto_payout = '$auto_payouts' where id = '$editid'");
    addSystemLog($conn, 'ACCOUNT UPDATED', "Account ($account_name) has been updated", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Account Updated Successfully.</div></div>';
    header("location: accounts_details.php");
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
    <title>Edit Account || D-Orders</title>
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
                                    <li class="breadcrumb-item active">Edit Account
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
                    <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                    <div class="row">
                       
                        <div class="col-md-12">
                            <?php echo flash_msg(); ?>
                        </div>
                        <!-- right content section -->
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                      
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            
                                                <div class="row">
                                                
                                                    
                                                    
                                                    
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Account Name</label>
                                                            <input type="text" class="form-control" id="name" name="account_name" value="<?=$account['account_name']; ?>" placeholder="Account Name" required/>
                                                        </div>
                                                    </div>
                                                   
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="phone">Phone</label>
                                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="" value="<?=$account['phone']; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="email" class="form-control" id="email" name="email" placeholder="" value="<?=$account['email']; ?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="address">Address</label>
                                                            <input type="text" class="form-control" id="address" name="address" placeholder="" value="<?=$account['address']; ?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="reference">Reference</label>
                                                            <input type="text" class="form-control" id="reference" name="reference" placeholder="" value="<?=$account['reference']; ?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="days_threshold">Threshold (Days)</label>
                                                            <input type="number" class="form-control" id="days_threshold" name="days_threshold" placeholder="" value="<?=$account['days_threshold']; ?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="amount_threshold">Threshold (Amount)</label>
                                                            <input type="number" class="form-control" step=".01" id="amount_threshold" name="amount_threshold" placeholder="" value="<?=$account['amount_threshold']; ?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="amount_threshold">Price Tag</label>
                                                            <select name="price_tag" class="form-control">
                                                                <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ ?>
                                                                <option value="<?=$price['id'];?>" <?php if($account['price_tag'] == $price['id']){ echo 'selected';} ?>><?=$price['name'];?></option>
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
                                                        <input type="hidden" name="edit_account" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                        <a href="connect_ebay.php?renewToken=<?php echo $account['id']; ?>" class="btn btn-warning mr-1 mt-1">Renew Token</a>
                                                    </div>
                                                   
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            
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
                                                        $other = json_decode($account['other'], true);
                                                        if($settings['accounts_columns'] != ''){
                                                        $accounts_columns = explode(",", $settings['accounts_columns']);
                                                        foreach($accounts_columns as $name){
                                                        
                                                        ?>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" name="other_details_name[<?=$name;?>]" placeholder="Input Name" value="<?=$name;?>" readonly/>
                                                                </div>
                                                            </div>
                                                             <div class="col-8">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" name="other_details_value[<?=$name;?>]" placeholder="Input Value" value="<?php if(array_key_exists($name, $other)){ echo $other[$name]; } ?>"/>
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
                        </div>
                        </div>
                       
                        <!--/ right content section -->
                    </div>
                     </form>
                </section>
                <!-- / account setting page -->
<script>
    function addInputBox(){
	    count = <?=$i;?>;
        var htmlInput = '<div class="col-4"><div class="form-group"><input type="text" class="form-control" name="other_details_name['+count+']" placeholder="Input Name"/></div></div><div class="col-8"><div class="form-group"><input type="text" class="form-control" name="other_details_value['+count+']" placeholder="Input Value"/></div></div>';
        // document.getElementById("other_inputs").appendChild(htmlInput);
        $("#other_inputs").append(htmlInput);
        count++;
        
    }
</script>
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
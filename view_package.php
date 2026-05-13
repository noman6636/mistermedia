<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(isset($_GET['id'])){
    $pid = $_GET['id'];
    $package = $conn->query("SELECT * FROM app_packages where id = '$pid'");
    if($package->num_rows > 0){
        $package = $package->fetch_assoc();
        $orders = $conn->query("SELECT * FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && b.IsArchived = 0 && a.SKU = '{$package['sku']}'")->num_rows;
    }else{
        header("location: manage_packages.php");
        exit();
    }
}else{
    header("location: manage_packages.php");
    exit();
}


if(isset($_POST['update_package'])){
    $editId = $_GET['id'];
    $sku = $conn->real_escape_string(trim($_POST['sku']));
    $old_sku = $conn->real_escape_string(trim($_POST['old_sku']));
    $name = $conn->real_escape_string(trim($_POST['name']));
    $packing_size_id = $conn->real_escape_string($_POST['packing_size_id']);
    
    $check_sku = $conn->query("Select * from app_items where sku = '$sku'")->num_rows;
    $check_sku_package = $conn->query("Select * from app_packages where sku = '$sku'  && id <> $editId")->num_rows;
    
    if($check_sku > 0 || $check_sku_package > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">SKU already exists.</div></div>';
        header("location: view_package.php?id=".$editId);
        exit();
    }
    
    $price = $_POST['price'];
    $name_id = $_POST['name_id'];
    $st = $price[0];
   
    $conn->query("UPDATE app_packages SET sku = '$sku', name = '$name', price = '$st', packing_size_id = '$packing_size_id' WHERE id = '$editId'");
    
    
    if($sku != $old_sku){
        $conn->query("update app_order_items set SKU = '$sku' where SKU = '$old_sku'");
    }
    for ($i = 0, $n = count($price); $i < $n; $i++) {
        if(!empty($price[$i])){
            $check_price_tag = $conn->query("select * from app_sellprices_amount where name_id = '{$name_id[$i]}' && item_id = '$editId' && type = '2'");
            if($check_price_tag->num_rows > 0){
                $conn->query("update app_sellprices_amount set price = '{$price[$i]}' where name_id = '{$name_id[$i]}' && item_id = '$editId'  && type = '2'");
               
            }else{
                $conn->query("insert into app_sellprices_amount set item_id = '$editId', name_id = '{$name_id[$i]}', price = '{$price[$i]}', type = '2'");
            }
        }
        
    }
    
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Package updated successfully.</div></div>';
    
   
    
    header("location: manage_packages.php");
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
    <title>View Package || D-Orders</title>
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
    <!-- END: Custom CSS-->

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
                                    <li class="breadcrumb-item active">View Package
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
                                          
                                          
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                  <div class="col-6">
                                                    <div class="row">
                                                        <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="sku">SKU</label>
                                                            <input type="text" class="form-control" id="sku" name="sku" value="<?=$package['sku'];?>" required/>
                                                            <input type="text" class="form-control" id="old_sku" name="old_sku" value="<?=$package['sku'];?>" required/>
                                                        </div>
                                                        </div>
                                                        <div class="col-8">
                                                            <div class="form-group">
                                                                <label for="name">Name</label>
                                                                <input type="text" class="form-control" id="name" name="name" value="<?=$package['name'];?>"  required/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                    
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="order_threshold">Packing Size</label>
                                                            <select class="form-control" id="packing_size_id" name="packing_size_id" required>
                                                                <option value="">Select Size</option>
                                                                <?php 
                                                                $sizes = $conn->query("SELECT * FROM app_packing_sizes ORDER BY name");
                                                                while($size = $sizes->fetch_assoc()){ ?>
                                                                    <option value="<?=$size['id']; ?>" <?php if($package['packing_size_id']==$size['id']){ echo 'selected'; } ?>><?=$size['name']; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="row">
                                                            <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ 
                                                            $cprice = $conn->query("Select * from app_sellprices_amount where item_id = '{$package['id']}' && name_id = '{$price['id']}' && type='2'")->fetch_assoc()['price']+0;
                                                            ?>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="price"><?php echo $price['name']; ?></label>
                                                                    <input type="text" class="form-control" id="price" name="price[]" placeholder="0.00" value="<?=$cprice;?>" <?php if($price['id']==12){ echo 'readonly'; } ?> required/>
                                                                    <input type="hidden" name="name_id[]" value="<?php echo $price['id']; ?>" />
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                </div>
                                                    <br>
                                                    <div class="col-12">
                                                        <div class="card-datatable">
                                                           <table class="dt-row-grouping-t table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">Sn</th>
                                                                        <th style="width:80%">Item</th>
                                                                        <th style="width:15%">Qty</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <?php
                                                                    $sn=0;
                                                                    $package_items = $conn->query("select * from app_packages_items where package_id = '$pid'");
                                                                    while($row = $package_items->fetch_assoc()){
                                                                        $item = $conn->query("Select * from app_items where id = '{$row['item_id']}'")->fetch_assoc();
                                                                    $sn++;?>
                                                                    <tr>
                                                                        <td><?=$sn;?></td>
                                                                        <td>(<?=$item['sku'];?>) <?=$item['name'];?></td>
                                                                        <td><?=$row['qty'];?></td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                                
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <input type="hidden" name="update_package" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Update Package</button>
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
                    echo '<option value="'.$item['id'].'">('.$item['sku'].') '.$item['name'].'</option>';
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
        
   
    </script>
</body>
<!-- END: Body-->

</html>
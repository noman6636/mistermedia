<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(18, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['create_package'])){
    $sku = $conn->real_escape_string(trim($_POST['sku']));
    $name = $conn->real_escape_string(trim($_POST['name']));
    $packing_size_id = $conn->real_escape_string($_POST['packing_size_id']);
    $created_date = date('Y-m-d H:i:s');
    
    
    $check_sku = $conn->query("select * from app_items where sku = '$sku'");
    $check_sku_package = $conn->query("Select * from app_packages where sku = '$sku'");
    if($check_sku_package->num_rows > 0){
        $check_sku_package=$check_sku_package->fetch_assoc();
        if($check_sku_package['deleted']==1){
            $conn->query("update app_packages set deleted = '0' where id = '{$check_sku_package['id']}'");
            addSystemLog($conn, 'SKU ENABLED', "SKU ($sku) has been enabled from add package page", "");
            $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">SKU Already exists in deleted list and Enabled now.</div></div>';
        }else{
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">SKU Already exists in database.</div></div>';
        }
        
        
        header("location: add_item.php");
        exit();
    }elseif($check_sku->num_rows > 0){
        $item = $check_sku->fetch_assoc();
        $conn->query("DELETE FROM app_packages_items WHERE item_id = '{$item['id']}'");
        $conn->query("DELETE FROM app_stocks WHERE item_id = '{$item['id']}'");
        $conn->query("DELETE FROM app_sellprices_amount WHERE item_id = '{$item['id']}' && type = '1'");
        $conn->query("DELETE FROM app_stocks WHERE item_id = '{$item['id']}'");
        $conn->query("DELETE FROM app_purchase_detail WHERE item_id = '{$item['id']}'");
        $conn->query("DELETE FROM app_purchase_orders_detail WHERE item_id = '{$item['id']}'");
        $conn->query("DELETE FROM app_items WHERE id = '{$item['id']}'");
    }
    
    
    $price = $_POST['price'];
    $name_id = $_POST['name_id'];
    
    $items = $_POST['item'];
    $qtys = $_POST['qty'];
    $st = $price[0];
    
    $pquery = "INSERT INTO app_packages set sku = '$sku', name = '$name', price = '$st', packing_size_id = '$packing_size_id'";
    if($conn->query($pquery)){
        $package_id = $conn->insert_id;
        for ($i = 0, $n = count($items); $i < $n; $i++) {
			$conn->query("insert into app_packages_items set package_id = '$package_id', item_id = '{$items[$i]}', qty = '{$qtys[$i]}'");
        }
        
        for ($i = 0, $n = count($price); $i < $n; $i++) {
           
                $conn->query("insert into app_sellprices_amount set item_id = '$package_id', name_id = '{$name_id[$i]}', price = '{$price[$i]}', type='2'");
           
            
        }
        addSystemLog($conn, 'PACKAGE ADDED', "New Package with SKU ($sku) has been added", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Package added successfully.</div></div>';
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding Package.</div></div>';
    }
    
   
    
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
    <title>Create Package || D-Orders</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
    /* =========================
   GLOBAL RESPONSIVE FIX
========================= */
@media (max-width: 991px) {

    /* Stack main columns */
    .col-md-12,
    .col-md-6,
    .col-6,
    .col-4,
    .col-8 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    /* Fix nested rows spacing */
    .row {
        margin-left: 0;
        margin-right: 0;
    }

    .row > div {
        padding-left: 5px;
        padding-right: 5px;
    }

    /* Buttons full width */
    .btn {
        width: 100%;
    }

    /* Card spacing */
    .card {
        margin-bottom: 15px;
    }
}

/* =========================
   TABLE (ITEM LIST) FIX
========================= */
@media (max-width: 768px) {

    /* IMPORTANT: scroll instead of breaking */
    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 600px;
    }

    table th, 
    table td {
        white-space: nowrap;
        font-size: 13px;
    }

    /* Fix select2 width */
    .select2-container {
        width: 100% !important;
    }

    /* Fix input spacing */
    .form-control {
        margin-bottom: 10px;
    }
}

/* =========================
   SMALL PHONES
========================= */
@media (max-width: 480px) {

    body {
        font-size: 13px;
    }

    .card-body {
        padding: 10px;
    }

    /* Labels tighter */
    label {
        font-size: 13px;
    }

    /* Inputs smaller */
    .form-control {
        font-size: 13px;
        padding: 7px;
    }

    /* Table compact */
    table th, table td {
        font-size: 12px;
        padding: 6px;
    }

    /* Buttons spacing */
    .btn {
        margin-top: 10px;
    }
}

/* =========================
   PREVENT OVERFLOW BUG
========================= */
body {
    overflow-x: hidden;
}
</style>
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
                                    <li class="breadcrumb-item active">Create Package
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
                                                            <input type="text" class="form-control" id="sku" name="sku" value="" required/>
                                                        </div>
                                                        </div>
                                                        <div class="col-8">
                                                            <div class="form-group">
                                                                <label for="name">Name</label>
                                                                <input type="text" class="form-control" id="name" name="name"  required/>
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
                                                                    <option value="<?=$size['id']; ?>"><?=$size['name']; ?></option>
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
                                                            
                                                            ?>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="price"><?php echo $price['name']; ?></label>
                                                                    <input type="text" class="form-control" id="price" name="price[]" placeholder="0.00" <?php if($price['id']==12){ echo 'readonly'; } ?> value="0"  />
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
                                                                        <th style="width:83%">Item</th>
                                                                        <th style="width:7%">Qty</th>
                                                                        <th style="width:5%"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                            <select class="form-control select2" id="item_1" name="item[]" required>
                                                                                <option value="">Select Item</option>
                                                                                <?php $items = $conn->query("select * from app_items order by sku asc");
                                                                                    while($item = $items->fetch_assoc()){
                                                                                        echo '<option value="'.$item['id'].'">'.$item['sku'].'</option>';
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control" id="qty_1" onkeyup="calculate_store(1)" name="qty[]" value="0" required/>
                                                                        </td>
                                                                        
                                                                        <td>
                                                                            <button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="3"></td>
                                                                   
                                                                    <td> 
                                                                    <button type="button" id="add_invoice_item" class="btn btn-success" name="add-purchase-item" onclick="addPurchaseOrderRow()" >+</button>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="create_package" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Create Package</button>
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
                    echo '<option value="'.$item['id'].'">'.$item['sku'].'</option>';
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
        
    count = 2;
        
    function addPurchaseOrderRow(){
	
		var products=$("#productSelect").html();
       
        var newdiv = document.createElement('tr');
        
        newdiv.innerHTML ='<td>'+count+'</td><td><select class="form-control select2" id="item_'+count+'" name="item[]" required><option value="">Select Item</option>'+products+'</select></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" value="0" required/></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
        document.getElementById("addPurchaseItem").appendChild(newdiv);
        $('#item_'+count).select2();
        count++;
        
    }
	function deleteRow(e) {
        var t = $("#addPurchaseItem > tr").length;
        if (1 == t) alert("There only one row you can't delete.");
        else {
            var a = e.parentNode.parentNode;
            a.parentNode.removeChild(a)
        }
       
    }
    
    //Calculate Sum
   
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
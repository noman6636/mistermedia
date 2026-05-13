<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(isset($_GET['id'])){
    $pid = $_GET['id'];
    $purchase = $conn->query("SELECT * FROM app_purchase_orders where id = '$pid'");
    if($purchase->num_rows > 0){
        $purchase = $purchase->fetch_assoc();
        $supplier = $conn->query("select * from app_suppliers where id = '{$purchase['supplier_id']}'")->fetch_assoc();
    }else{
        header("location: manage_purchase_orders.php");
        exit();
    }
}else{
    header("location: manage_purchase_orders.php");
    exit();
}



if(!in_array(24, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['upadte_purchase'])){
    $edit_id = $pid;
    $supplier = $conn->real_escape_string($_POST['supplier']);
    $order_no = $conn->real_escape_string($_POST['order_no']);
    $total_amount = $_POST['fld_grand_total_amount'];
    $expected_delivery = $_POST['expected_delivery'];
    
    $items = $_POST['item'];
    $qtys = $_POST['qty'];
    $prices = $_POST['price'];
    $totals = $_POST['total'];
    
    $pquery = "update app_purchase_orders set order_no = '$order_no', supplier_id = '$supplier', total_amount = '$total_amount', expected_delivery='$expected_delivery' where id = '$edit_id'";
    if($conn->query($pquery)){
        $conn->query("DELETE FROM app_purchase_orders_detail where purchase_id = '$edit_id'");
        for ($i = 0, $n = count($items); $i < $n; $i++) {
			$conn->query("insert into app_purchase_orders_detail set purchase_id = '$edit_id', item_id = '{$items[$i]}', qty = '{$qtys[$i]}', price = '{$prices[$i]}', total = '{$totals[$i]}'");
        }
        addSystemLog($conn, 'PURCHASE ORDER UPDATED', "Purchase Order with number ($order_no) has been updated", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Purchase Order updated successfully.</div></div>';
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding purchase Order.</div></div>';
    }
    
   
    
    header("location: manage_purchase_orders.php");
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
    <title>Edit Purchase Order || D-Orders</title>
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
                                    <li class="breadcrumb-item active">Edit Purchase Order
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
                                                        <div class="form-group">
                                                            <label for="supplier">Supplier</label>
                                                            <select class="form-control select2" id="supplier" name="supplier" required>
                                                                <option value="">Select Supplier</option>
                                                                <?php $suppliers = $conn->query("select * from app_suppliers order by name asc");
                                                                    while($supplier = $suppliers->fetch_assoc()){ ?>
                                                                        <option value="<?=$supplier['id'];?>" <?php if($purchase['supplier_id']==$supplier['id']){ echo 'selected' ;} ?>><?=$supplier['name'];?></option>
                                                                    <?php } ?>
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="expected_delivery">Expected Delivery</label>
                                                            <input type="date" class="form-control" id="expected_delivery" name="expected_delivery" value="<?php echo $purchase['expected_delivery']; ?>" required/>
                                                        </div>
                                                    </div>
                                                     <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="invoice_no">Order #</label>
                                                            <input type="text" class="form-control" id="order_no" name="order_no" value="<?php echo $purchase['order_no']; ?>" required/>
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class="col-12">
                                                        <div class="card-datatable">
                                                           <table class="dt-row-grouping-t table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">Sn</th>
                                                                        <th style="width:45%">Item</th>
                                                                        <th style="width:15%">Qty</th>
                                                                        <th style="width:15%">Price</th>
                                                                        <th style="width:15%">Total</th>
                                                                        <th style="width:5%"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <?php
                                                                    $sn=0;
                                                                    $purchase_details = $conn->query("select * from app_purchase_orders_detail where purchase_id = '$pid'");
                                                                    $total =0;
                                                                    while($row = $purchase_details->fetch_assoc()){
                                                                        $total += $row['total'];
                                                                    $sn++;?>
                                                                    <tr>
                                                                        <td><?=$sn;?></td>
                                                                        <td>
                                                                            <select class="form-control select2" id="item_<?=$sn;?>" name="item[]" required>
                                                                                <option value="">Select Item</option>
                                                                                <?php $items = $conn->query("select * from app_items order by sku asc");
                                                                                    while($item = $items->fetch_assoc()){ ?>
                                                                                        <option value="<?=$item['id'];?>" <?php if($row['item_id']==$item['id']){ echo 'selected' ;} ?>><?=$item['sku'];?></option>';
                                                                                   <?php } ?>
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control" id="qty_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="qty[]" value="<?=$row['qty'];?>" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".0001" class="form-control" id="price_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="price[]" value="<?=$row['price'];?>" required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" step=".0001" class="form-control total_price text-right" id="total_<?=$sn;?>" name="total[]" value="<?=$row['total'];?>" readonly required/>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button>
                                                                        </td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="4"><b>Total($):</b></td>
                                                                    <td class="text-right">
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="<?=$total;?>" readonly="readonly">
                                                                    </td>
                                                                    <td> 
                                                                    <button type="button" id="add_invoice_item" class="btn btn-success" name="add-purchase-item" onclick="addPurchaseOrderRow()" >+</button>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="upadte_purchase" value="<?php echo $pid;?>" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Update Order</button>
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
    count = <?php echo $sn+1;?>;
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
    
            // $( ".select2" ).each(function() {
            //   $(this).select2();
            // });
        });
        
    
        
    function addPurchaseOrderRow(){
	
		var products=$("#productSelect").html();
       
        var newdiv = document.createElement('tr');
        
        newdiv.innerHTML ='<td>'+count+'</td><td><select class="form-control select2" id="item_'+count+'" name="item[]" required><option value="">Select Item</option>'+products+'</select></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" value="0" required/></td><td><input type="number" step=".0001" class="form-control" onkeyup="calculate_store('+count+')" id="price_'+count+'" name="price[]" value="0.0000" required/></td><td class="text-right"><input type="text" step=".0001" class="form-control total_price text-right" id="total_'+count+'" name="total[]" value="0.0000" readonly required/></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
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
        calculateSum()
    }
    
    //Calculate Sum
    "use strict";
function calculateSum() {
      var t = 0;

         
            //Total Price
    $(".total_price").each(function () {
        isNaN(this.value) || 0 == this.value.length || (t += parseFloat(this.value))
    }),   
    e = t.toFixed(4);

    var test = +e;
    $("#fld_grand_total_amount").val(test.toFixed(4));


    var gt = $("#fld_grand_total_amount").val();
    var grnt_totals = gt;
    $("#fld_grand_total_amount").val(grnt_totals);

    
}
 //Calculate store product
        "use strict";
    function calculate_store(sl) {
       
        var gr_tot = 0;
        var qty    = $("#qty_"+sl).val();
        var price = $("#price_"+sl).val();

        var total_price     = qty * price;
        $("#total_"+sl).val(total_price.toFixed(4));

        //Total Price
        $(".total_price").each(function() {
            isNaN(this.value) || 0 == this.value.length || (gr_tot += parseFloat(this.value))
        });

        //$("#Total").val(gr_tot.toFixed(2,2));
        var grandtotal = gr_tot;
        $("#fld_grand_total_amount").val(grandtotal.toFixed(4));
    }
    </script>
</body>
<!-- END: Body-->

</html>
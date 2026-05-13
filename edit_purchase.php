<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(22, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['update_purchase'])){
    $purchase_id = $conn->real_escape_string(trim($_POST['update_purchase']));
    $supplier = $conn->real_escape_string(trim($_POST['supplier']));
    $invoice_no = $conn->real_escape_string(trim($_POST['invoice_no']));
    $date = date('Y-m-d', strtotime($_POST['date']));
    $total_amount = $_POST['fld_grand_total_amount'];
    $created_date = date('Y-m-d H:i:s');
    $items = $_POST['item'];
    $qtys = $_POST['qty'];
    $prices = $_POST['price'];
    $totals = $_POST['total'];
    $statuss = $_POST['status'];
    
    $pquery = "update app_purchase set supplier_id = '$supplier', invoice_no='$invoice_no', date = '$date', total_amount = '$total_amount' where id = '$purchase_id'";
    if($conn->query($pquery)){
        $conn->query("DELETE FROM app_purchase_detail WHERE purchase_id = '$purchase_id'");
        $conn->query("DELETE FROM app_stocks WHERE pid = '$purchase_id'");
        for ($i = 0, $n = count($items); $i < $n; $i++) {
            if($qtys[$i] > 0){
            $status = $statuss[$i];
			$conn->query("insert into app_purchase_detail set purchase_id = '$purchase_id', item_id = '{$items[$i]}', qty = '{$qtys[$i]}', price = '{$prices[$i]}', total = '{$totals[$i]}', status = '$status'");
			$pdid = $conn->insert_id;
    			if($status == 1){
    			    $conn->query("insert into app_stocks set pid = '$purchase_id', pdid = '$pdid', item_id = '{$items[$i]}', description='Stock added from Purchase with invoice no $invoice_no', qty = '{$qtys[$i]}', datetime = '$created_date'");
    			}
			
            }
        }
        addSystemLog($conn, 'PURCHASE UPDATED', "Purchase with id ($purchase_id) has been updated", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Purchase updated successfully.</div></div>';
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem updateing purchase.</div></div>';
    }
    
   
    
    header("location: manage_purchase.php");
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
    <title>Edit Purchase || D-Orders</title>
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
        
select[readonly].select2-hidden-accessible + .select2-container {
  pointer-events: none;
  touch-action: none;
}

select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
  background: #eee;
  box-shadow: none;
}

select[readonly].select2-hidden-accessible + .select2-container .select2-selection__arrow,
select[readonly].select2-hidden-accessible + .select2-container .select2-selection__clear {
  display: none;
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
                                    <li class="breadcrumb-item active">Edit Purchase
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
                                          
                                            <?php if(isset($_GET['edit'])){
                                             $purchase = $conn->query("SELECT * FROM app_purchase where id = '{$_GET['edit']}'");
                                            if($purchase->num_rows > 0){
                                                $purchase = $purchase->fetch_assoc();
                                                $supplier = $conn->query("select * from app_suppliers where id = '{$purchase['supplier_id']}'")->fetch_assoc();
                                            }else{
                                                header("location: manage_purchase_orders.php");
                                                exit();
                                            }?>
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="supplier">Supplier</label>
                                                            <input type="hidden" class="form-control" name="supplier" value="<?php echo $purchase['supplier_id']; ?>" required/>
                                                            <input type="text" class="form-control" id="supplier" value="<?php echo $supplier['name']; ?>" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="date">Purchase Date</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="invoice_no">Invoice #</label>
                                                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="<?php echo $purchase['invoice_no']; ?>" required/>
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
                                                                        <th style="width:5%">Status</th>
                                                                        <th style="width:5%"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <input type="hidden" name="alreadyRecevied[]" value="<?php echo $row['status']; ?>" />
                                                                    <?php
                                                                    $sn=0;
                                                                    $purchase_details = $conn->query("select * from app_purchase_detail where purchase_id = '{$_GET['edit']}'");
                                                                    while($row = $purchase_details->fetch_assoc()){
                                                                        $item = $conn->query("Select * from app_items where id = '{$row['item_id']}'")->fetch_assoc();
                                                                    $sn++;?>
                                                                    <tr>
                                                                        <td><?=$sn;?></td>
                                                                        <td>
                                                                            <select class="form-control select2" id="item_<?=$sn;?>" name="item[]" <?php if($row['status']==1){ echo 'readonly'; } ?> required>
                                                                                <option value="">Select Item</option>
                                                                                <?php $items = $conn->query("select * from app_items order by sku asc");
                                                                                    while($item = $items->fetch_assoc()){
                                                                                        
                                                                                        if($row['item_id'] == $item['id']){ 
                                                                                            echo '<option value="'.$item['id'].'" selected>'.$item['sku'].'</option>';
                                                                                        }else{
                                                                                            echo '<option value="'.$item['id'].'">'.$item['sku'].'</option>';
                                                                                        }
                                                                                        
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control" id="qty_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="qty[]"  value="<?=$row['qty'];?>" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".0001" class="form-control" id="price_<?=$sn;?>" onkeyup="calculate_store(<?=$sn;?>)" name="price[]" value="<?=$row['price'];?>" required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" step=".0001" class="form-control total_price text-right" id="total_<?=$sn;?>" name="total[]" value="<?=$row['total'];?>" readonly required/>
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-control select2" id="status_<?=$sn;?>" name="status[]" <?php if($row['status']==1){ echo 'readonly'; } ?> required>
                                                                                <option value="0" <?php if($row['status']==0){ echo 'selected'; } ?>>Ordered</option>
                                                                                <option value="1" <?php if($row['status']==1){ echo 'selected'; } ?>>Received</option>
                                                                                
                                                                                
                                                                            </select>
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
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="<?=$purchase['total_amount'];?>" readonly="readonly">
                                                                    </td>
                                                                    <td class="text-right" colspan=""></td>
                                                                    <td> 
                                                                    <button type="button" id="add_invoice_item" class="btn btn-success" name="add-purchase-item" onclick="addPurchaseOrderRow()" >+</button>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="update_purchase" value="<?=$purchase['id'];?>" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Update Purchase</button>
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
        
    count = <?php echo $sn+1; ?>
        
    function addPurchaseOrderRow(){
	
		var products=$("#productSelect").html();
       
        var newdiv = document.createElement('tr');
        
        newdiv.innerHTML ='<input type="hidden" name="alreadyRecevied[]" value="0" /><td>'+count+'</td><td><select class="form-control select2" id="item_'+count+'" name="item[]" required><option value="">Select Item</option>'+products+'</select></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" value="0" required/></td><td><input type="number" step=".0001" class="form-control" onkeyup="calculate_store('+count+')" id="price_'+count+'" name="price[]" value="0.0000" required/></td><td class="text-right"><input type="text" step=".0001" class="form-control total_price text-right" id="total_'+count+'" name="total[]" value="0.0000" readonly required/></td><td><select class="form-control select2" id="status_'+count+'" name="status[]" required><option value="0">Ordered</option><option value="1">Received</option></select></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
        document.getElementById("addPurchaseItem").appendChild(newdiv);
        $('#item_'+count).select2();
        $('#status_'+count).select2();
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
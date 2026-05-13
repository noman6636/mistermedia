<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(28, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

// if(isset($_POST['create_purchase'])){
//     $supplier = $conn->real_escape_string(trim($_POST['supplier']));
//     $invoice_no = $conn->real_escape_string(trim($_POST['invoice_no']));
//     $date = date('Y-m-d', strtotime($_POST['date']));
//     $total_amount = $_POST['fld_grand_total_amount'];
//     $created_date = date('Y-m-d H:i:s');
//     $items = $_POST['item'];
//     $qtys = $_POST['qty'];
//     $prices = $_POST['price'];
//     $totals = $_POST['total'];
    
//     $pquery = "INSERT INTO app_purchase set supplier_id = '$supplier', invoice_no='$invoice_no', date = '$date', total_amount = '$total_amount', created_date = '$created_date'";
//     if($conn->query($pquery)){
//         $purchase_id = $conn->insert_id;
//         for ($i = 0, $n = count($items); $i < $n; $i++) {
//             if($qtys[$i] > 0){
// 			$conn->query("insert into app_purchase_detail set purchase_id = '$purchase_id', item_id = '{$items[$i]}', qty = '{$qtys[$i]}', price = '{$prices[$i]}', total = '{$totals[$i]}'");
// 			$pdid = $conn->insert_id;
			
// 			$conn->query("insert into app_stocks set pid = '$purchase_id', pdid = '$pdid', item_id = '{$items[$i]}', description='Stock added from Purchase with invoice no $invoice_no', qty = '{$qtys[$i]}', datetime = '$created_date'");
//             }
//         }
        
//         $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Purchase added successfully.</div></div>';
//     }else{
//         $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding purchase.</div></div>';
//     }
    
   
    
//     header("location: manage_purchase.php");
//     exit();
// }

if(isset($_POST['create_sale'])){
    $account = $conn->real_escape_string(trim($_POST['account']));
    $reference = $conn->real_escape_string(trim($_POST['reference']));
    $date = date('Y-m-d H:i:s', strtotime($_POST['date']));
    $total_amount = $_POST['fld_grand_total_amount'];
    $order_id = strtotime(date('Y-m-d H:i:s')).'-'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    $shipping_name = $conn->real_escape_string(trim($_POST['shipping_name']));
    $shipping_street1 = $conn->real_escape_string(trim($_POST['shipping_street1']));
    $shipping_street2 = $conn->real_escape_string(trim($_POST['shipping_street2']));
    $shipping_cityname = $conn->real_escape_string(trim($_POST['shipping_cityname']));
    $shipping_state = $conn->real_escape_string(trim($_POST['shipping_state']));
    $shipping_country = $conn->real_escape_string(trim($_POST['shipping_country']));
    $shipping_postalcode = $conn->real_escape_string(trim($_POST['shipping_postalcode']));
    $shipping_phone = $conn->real_escape_string(trim($_POST['shipping_phone']));
    
    $shippingAddress = array();
    $shippingAddress['Name'] = $shipping_name;
    $shippingAddress['Street1'] = $shipping_street1;
    $shippingAddress['Street2'] = $shipping_street2;
    $shippingAddress['CityName'] = $shipping_cityname;
    $shippingAddress['StateOrProvince'] = $shipping_state;
    $shippingAddress['Country'] = $shipping_country;
    $shippingAddress['CountryName'] = $shipping_country;
    $shippingAddress['Phone'] = $shipping_postalcode;
    $shippingAddress['PostalCode'] = $shipping_phone;
    
    $shippingAddress = $conn->real_escape_string(json_encode($shippingAddress));
    
    $items = $_POST['item'];
    $qtys = $_POST['qty'];
    $prices = $_POST['price'];
    $totals = $_POST['total'];
    
    $pquery = "INSERT INTO app_orders SET AccountID = '$account', OrderID = '$order_id', OrderStatus = 'Completed', PaymentMethod = 'D-Orders', PaymentStatus = 'Complete', CreatedTime = '$date', Subtotal = '$total_amount', Total = '$total_amount', ShippingAddress = '$shippingAddress', PostCode = '$shipping_postalcode', Reference = '$reference', OrderType = '2', IsPrinted = '1', IsDispatched = '1', IsRespond = '1'";
    if($conn->query($pquery)){
        
        for ($i = 0, $n = count($items); $i < $n; $i++) {
            if($qtys[$i] > 0){
			    $title = $conn->real_escape_string(getTitleFromSKU($conn, $items[$i]));
			    $conn->query("INSERT INTO app_order_items SET OrderID = '$order_id', SKU = '{$items[$i]}', ItemTitle = '$title', QuantityPurchased = '{$qtys[$i]}', Price = '{$prices[$i]}', OrderType = '2'");
            }
        }
        addSystemLog($conn, 'SALE ORDER CREATED', "New sale order with no ($order_id) has been created.", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Sale added successfully.</div></div>';
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem adding sale.</div></div>';
    }
    
   
    
    header("location: create_sale_order.php");
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
    <title>Create Purchase || D-Orders</title>
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
        /* ================= MOBILE ONLY FIX ================= */
@media (max-width: 768px) {

    /* Fix grid spacing */
    .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Reduce padding */
    .card-body {
        padding: 10px;
    }

    /* Inputs full width */
    .form-control {
        font-size: 14px;
        padding: 6px 8px;
    }

    /* Select2 fix */
    .select2-container {
        width: 100% !important;
    }

    /* Table responsive */
    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 700px; /* prevent breaking structure */
    }

    /* Smaller table text */
    .table th, 
    .table td {
        font-size: 12px;
        padding: 6px;
        white-space: nowrap;
    }

    /* Buttons smaller */
    .btn {
        padding: 5px 8px;
        font-size: 12px;
    }

    /* Add button spacing */
    #add_invoice_item {
        width: 100%;
        margin-top: 5px;
    }

    /* Heading spacing */
    p {
        margin-bottom: 5px;
        font-size: 14px;
    }

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
                                    <li class="breadcrumb-item active">Create Sale
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
                                                            <label for="supplier">Account</label>
                                                            <select class="form-control select2" id="account" name="account" onchange="changeaccount(this.value)" required>
                                                                <option value="">Select Account</option>
                                                                <?php $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                                                                    while($account = $accounts->fetch_assoc()){ ?>
                                                                    <option value="<?=$account['id'];?>" <?php if(isset($_GET['s']) && $_GET['s']==$account['id']) {echo 'selected';} ?>><?=$account['account_name'];?></option>
                                                                    <?php } ?>
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="date">Sale Date</label>
                                                            <input type="datetime-local" class="form-control" id="date" name="date" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="reference">Reference</label>
                                                            <input type="text" class="form-control" id="reference" name="reference" value=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <p>Shipping Details</p>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_name">Name</label>
                                                            <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_street1">Street 1</label>
                                                            <input type="text" class="form-control" id="shipping_street1" name="shipping_street1" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_street2">Street 2</label>
                                                            <input type="text" class="form-control" id="shipping_street2" name="shipping_street2" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_cityname">City Name</label>
                                                            <input type="text" class="form-control" id="shipping_cityname" name="shipping_cityname" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_state">State</label>
                                                            <input type="text" class="form-control" id="shipping_state" name="shipping_state" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_country">Country Name</label>
                                                            <input type="text" class="form-control" id="shipping_country" name="shipping_country" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_postalcode">Postal Code</label>
                                                            <input type="text" class="form-control" id="shipping_postalcode" name="shipping_postalcode" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="shipping_phone">Phone No</label>
                                                            <input type="text" class="form-control" id="shipping_phone" name="shipping_phone" value="" required/>
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
                                                                    <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                            <select class="form-control select2 sku_select" onchange="getPrice(1)" id="item_1" name="item[]" required>
                                                                                <option value="" sku="">Select Item</option>
                                                                                <?php $items = $conn->query("SELECT * FROM (select sku from app_items where deleted = 0 UNION SELECT sku from app_packages where deleted = 0) A order by sku ASC");
                                                                                    while($item = $items->fetch_assoc()){
                                                                                        echo '<option value="'.$item['sku'].'">'.$item['sku'].'</option>';
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control qty" id="qty_1" onkeyup="calculate_store(1)" name="qty[]" value="0" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".0001" class="form-control price" id="price_1" onkeyup="calculate_store(1)" name="price[]" value="0.0000" readonly required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" step=".0001" class="form-control total_price text-right" id="total_1" name="total[]" value="0.0000" readonly required/>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="4"><b>Total($):</b></td>
                                                                    <td class="text-right">
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="0.0000" readonly="readonly">
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
                                                        <input type="hidden" name="create_sale" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Create Sale</button>
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
			<?php $items = $conn->query("SELECT * FROM (select sku from app_items where deleted = 0 UNION SELECT sku from app_packages where deleted = 0) A order by sku ASC");
                while($item = $items->fetch_assoc()){
                    echo '<option value="'.$item['sku'].'">'.$item['sku'].'</option>';
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
        
        function changeaccount(val){
            window.location.href = 'create_sale_order.php?s='+val;
        }
        
        function getPrice(sl) {
            var sid = <?php if(isset($_GET['s']) && !empty($_GET['s'])) {echo $_GET['s'];}else{echo '0';} ?>;
            var item_id = $("#item_"+sl).val();
            var data = {sid: sid, item_id: item_id};
            console.log(data);
            $.ajax({
                    url : "inc/ajax.php?getPrice=1",
                    method : "POST",
                    data : data,
                    async : true,
                    success: function(data){
                        console.log(data);
                        $("#price_"+sl).val(data);
                        calculate_store(sl);
                    }
                });
        }
        
    count = 2;
        
    function addPurchaseOrderRow(){
	
		var products=$("#productSelect").html();
       
        var newdiv = document.createElement('tr');
        
        newdiv.innerHTML ='<td>'+count+'</td><td><select class="form-control select2 sku_select" id="item_'+count+'" onchange="getPrice('+count+')" name="item[]" required><option value="">Select Item</option>'+products+'</select></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" value="0" required/></td><td><input type="number" step=".0001" class="form-control" onkeyup="calculate_store('+count+')" id="price_'+count+'" name="price[]" value="0.0000" readonly required/></td><td class="text-right"><input type="text" step=".0001" class="form-control total_price text-right" id="total_'+count+'" name="total[]" value="0.0000" readonly required/></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
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
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
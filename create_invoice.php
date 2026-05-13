<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(33, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['create_invoice'])){
    $company_id = $conn->real_escape_string(trim($_POST['company_id']));
    $date = date('Y-m-d', strtotime($_POST['date']));
    $total_amount = $_POST['fld_grand_total_amount'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $now = date('Y-m-d H:i:s');

    
    $title = $_POST['title'];
    $qtys = $_POST['qty'];
    $prices = $_POST['price'];
    $totals = $_POST['total'];
   
    $pquery = "INSERT INTO app_invoices SET company_id = '$company_id', date = '$date', name = '$name', email = '$email', address = '$address', phone = '$phone', total_amount = '$total_amount', datetime='$now'";
    if($conn->query($pquery)){
        $invoice_id = $conn->insert_id;
        for ($i = 0, $n = count($title); $i < $n; $i++) {
            if($qtys[$i] > 0){
			    $conn->query("INSERT INTO app_invoices_details SET invoice_id = '$invoice_id', title = '{$title[$i]}', qty = '{$qtys[$i]}', price = '{$prices[$i]}', amount = '{$totals[$i]}'");
            }
        }
        addSystemLog($conn, 'INVOICE CREATED', "New Invoice ($invoice_id) has been created", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Invoice Created Successfully.</div></div>';
    }else{
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">There is some problem creating invoice.</div></div>';
    }
    
   
    
    header("location: create_invoice.php");
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
    <title>Create Invoice || D-Orders</title>
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
    /* ================= MOBILE RESPONSIVE FIX ================= */

/* Tablet */
@media (max-width: 991px) {

    .col-3 {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .card {
        padding: 10px;
    }
}

/* Mobile */
@media (max-width: 767px) {

    /* Full width fields */
    .col-3, .col-6, .col-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .form-control {
        font-size: 14px;
        padding: 8px;
    }

    /* Fix Select2 */
    .select2-container {
        width: 100% !important;
    }

    /* TABLE → CARD VIEW */
    table {
        width: 100%;
    }

    table thead {
        display: none;
    }

    table tbody tr {
        display: block;
        background: #fff;
        margin-bottom: 12px;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    table tbody td {
        display: flex;
        flex-direction: column;
        margin-bottom: 8px;
        border: none;
        padding: 0;
    }

    table tbody td input {
        width: 100%;
        margin-top: 5px;
    }

    /* Buttons */
    .btn {
        width: 100%;
        margin-top: 5px;
    }

    /* Total section */
    tfoot tr {
        display: block;
    }

    tfoot td {
        display: block;
        width: 100%;
        text-align: left !important;
    }

    #fld_grand_total_amount {
        width: 100%;
    }

    /* Add button */
    #add_invoice_item {
        width: 100%;
        margin-top: 10px;
    }
}

/* Extra Small Devices */
@media (max-width: 480px) {

    .card {
        padding: 8px;
    }

    .form-control {
        font-size: 13px;
        padding: 6px;
    }

    table tbody tr {
        padding: 10px;
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
                                    <li class="breadcrumb-item active">Create Invocie
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
                                                
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="supplier">Select Company</label>
                                                            <select class="form-control select2"  name="company_id"  required>
                                                                <option value="">Select Company</option>
                                                                <option value="1">Oxijan Ltd</option>
                                                                <option value="2">Smart Future Kings</option>
                                                                <option value="3">Pak Tools</option>
                                                               
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="date">Invoice Date</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        
                                                    </div>
                                                    <div class="col-12">
                                                        <p>Invoice Details</p>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="name">Name</label>
                                                            <input type="text" class="form-control" id="name" name="name" value="" required/>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="address">Address</label>
                                                            <input type="text" class="form-control" id="address" name="address" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="text" class="form-control" id="email" name="email" value="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="phone">Phone No</label>
                                                            <input type="text" class="form-control" id="phone" name="phone" value="" required/>
                                                        </div>
                                                    </div>
                                                    
                                                    <br>
                                                    <div class="col-12">
                                                        <div class="card-datatable">
                                                           <table class="dt-row-grouping-t table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:45%">Title</th>
                                                                        <th style="width:15%">Qty</th>
                                                                        <th style="width:15%">Price</th>
                                                                        <th style="width:15%">Total</th>
                                                                        <th style="width:5%"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="addPurchaseItem">
                                                                    <tr>
                                                                        
                                                                        <td>
                                                                            <input type="text" class="form-control" id="title_1" name="title[]" placeholder="Title..." required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" class="form-control qty" id="qty_1" onkeyup="calculate_store(1)" name="qty[]" placeholder="Qty" required/>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step=".01" class="form-control price text-right" id="price_1" onkeyup="calculate_store(1)" name="price[]" placeholder="Price"  required/>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <input type="text" style="text-align:right" step=".0001" class="form-control total_price text-right" id="total_1" name="total[]" value="0.00" readonly required/>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td class="text-right" colspan="3"><b>Total($):</b></td>
                                                                    <td class="text-right">
                                                                        <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="0.00" readonly="readonly">
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
                                                        <input type="hidden" name="create_invoice" value="1" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Create Invoice</button>
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
            window.location.href = 'create_sale_order?s='+val;
        }
        
        function getPrice(sl) {
            var sid = <?php if(isset($_GET['s']) && !empty($_GET['s'])) {echo $_GET['s'];}else{echo '0';} ?>;
            var item_id = $("#item_"+sl).val();
            var data = {sid: sid, item_id: item_id};
            console.log(data);
            $.ajax({
                    url : "inc/ajax?getPrice=1",
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
        
        newdiv.innerHTML ='<td><input type="text" class="form-control" id="title_'+count+'" name="title[]" placeholder="Title..." required/></td><td><input type="number" class="form-control" onkeyup="calculate_store('+count+')" id="qty_'+count+'" name="qty[]" placeholder="Qty" required/></td><td><input type="number" step=".01" class="form-control text-right" onkeyup="calculate_store('+count+')" id="price_'+count+'" name="price[]" placeholder="Price" required/></td><td class="text-right"><input type="text" step=".01" class="form-control total_price text-right" id="total_'+count+'" name="total[]" value="0.00" readonly required/></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)" >x</button></td>';
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
    e = t.toFixed(2);

    var test = +e;
    $("#fld_grand_total_amount").val(test.toFixed(2));


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
        $("#total_"+sl).val(total_price.toFixed(2));

        //Total Price
        $(".total_price").each(function() {
            isNaN(this.value) || 0 == this.value.length || (gr_tot += parseFloat(this.value))
        });

        //$("#Total").val(gr_tot.toFixed(2,2));
        var grandtotal = gr_tot;
        $("#fld_grand_total_amount").val(grandtotal.toFixed(2));
    }
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
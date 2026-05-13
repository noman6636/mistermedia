<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if(!in_array(3, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
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
    <title>Generate Invoice || D-Orders</title>
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

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    /* ===== GLOBAL TABLE FIX ===== */
.table {
    width: 100%;
    white-space: nowrap;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Prevent header breaking */
.table th {
    white-space: nowrap;
}

/* Fix vertical text issue */
table th, table td {
    word-break: normal !important;
    overflow-wrap: normal !important;
}
</style>
</head>
<!-- END: Head-->
<style>
    .select2-selection__arrow{
        display:none;
    }
</style>
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
                                    <li class="breadcrumb-item active">Generate Invoice
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
                                           
                                            
                                            <form class="" action="" method="post" onsubmit="return false;" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="account_id">Account</label>
                                                            
                                                            <select name="account_id" class="form-control select2" id="account_id" required>
                                                                <?php $accounts = $conn->query("select * from app_accounts order by account_name asc");
                                                                while($account=$accounts->fetch_assoc()){ ?>
                                                                    <option value="<?=$account['id'];?>"><?=$account['account_name'];?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                   
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="date">From Date</label>
                                                            <input type="date" class="form-control" id="frmdate" name="frmdate" value="<?=date('Y-m-d');?>" placeholder="" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="date">To Date</label>
                                                            <input type="date" class="form-control" id="todate" name="todate" value="<?=date('Y-m-d');?>" placeholder="" required/>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateInvoice(1);">Generate View</button>
                                                        <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateInvoice(2);">Generate Csv</button>
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
                
               <!-- <section id="row-grouping-datatable">
                    <div class="row">
                       <form action="" id="" method="POST">
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Generate Invoice</h4>
                                    <div class="form-group">
                                                            <label for="account_id">Account</label>
                                                            
                                                            <select name="account_id" class="form-control" id="account_id" required>
                                                                <?php $accounts = $conn->query("select * from app_accounts order by account_name asc");
                                                                while($account=$accounts->fetch_assoc()){ ?>
                                                                    <option value="<?=$account['id'];?>"><?=$account['account_name'];?> - <?=$account['account_username'];?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                </div>
                                
                                <div class="card-datatable">
                                    
                                   <table class="table">
                                        <thead>
                                            <tr>
                                                <th style="width:10%;">Date/Time</th>
                                                <th style="width:30%;">SKU</th>
                                                <th style="width:10%;">QTY</th>
                                                <th style="width:25%;">Price</th>
                                                <th style="width:25%;">Total</th>
                                                <th style="width:5%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoiceGTable">
                                       
                                    	    <tr>
                                    	        <td><input type="date" name="date[]" class="form-control" value="<?php echo date('Y-m-d'); ?>" /></td>
                                    	        <td><input type="text" name="sku[]" id="sku_1" class="form-control" value="" /></td>
                                    	        <td><input type="text" name="qty[]" id="qty_1" class="form-control" value="" /></td>
                                    	        <td><input type="text" name="price[]" id="price_1" class="form-control" /></td>
                                    	        <td><input type="text" name="total[]" id="total_1" class="text-right form-control" value="0.00" readonly /></td>
                                    	        <td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)"><i data-feather="x-circle"></i></button></td>
                                                
                                                
                                            </tr>
                                        
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-right" colspan="4"><b>Total:</b></td>
                                                <td class="text-right">
                                                    <input type="text" id="fld_grand_total_amount" class="text-right form-control" name="fld_grand_total_amount" value="0.00" readonly="readonly">
                                                </td>
                                                <td> 
                                                <button type="button" id="add_invoice_item" class="btn btn-info" name="add-invoice-item" onclick="addPurchaseOrderField1('invoiceGTable')"><i data-feather="plus-circle"></i></button>
                                               </td>
                                            </tr>
                                                                    </tfoot>
                                    </table>
                                    
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </section> -->
                <!-- / account setting page -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

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
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>

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
        $(document).ready(function() {
            $('.select2').select2();
        });
        
        function generateInvoice(type){
            var account_id, date;
            account_id = $("#account_id").val();
            frmdate = $("#frmdate").val();
            todate = $("#todate").val();
            if(type==1){
                var newwindow=window.open("printInvoice.php?account_id="+account_id+"&frmdate="+frmdate+'&todate='+todate,"Generate Invoice",'width=793.7, height=1122.52,toolbar=0,menubar=0,location=0');  
                if (window.focus) {newwindow.focus()}
            }else{
                window.location.href="csvInvoice.php?account_id="+account_id+"&frmdate="+frmdate+'&todate='+todate;
            }
            
        }
        var count = 2;
        function addPurchaseOrderField1(divName){
		
            var newdiv = document.createElement('tr');
            var tabin="sku_"+count;
            newdiv = document.createElement("tr");
            var dd = "<?php echo date('Y-m-d'); ?>";



            newdiv.innerHTML ='<td><input type="date" name="date[]" class="form-control" value="'+dd+'" /></td><td><input type="text" name="sku[]" id="sku_'+count+'" class="form-control" value="" /></td><td><input type="text" name="qty[]" id="qty_'+count+'" class="form-control" value="" /></td><td><input type="text" name="price[]" id="price_'+count+'" class="form-control" /></td><td><input type="text" name="total[]" id="total_'+count+'" class="text-right form-control" value="0.00" readonly /></td><td><button class="btn btn-danger text-right red" type="button" value="Delete" onclick="deleteRow(this)"><i data-feather="x-circle"></i></button></td>'; 
            document.getElementById(divName).appendChild(newdiv);
            document.getElementById(tabin).focus();
            //document.getElementById("add_invoice_item").setAttribute("tabindex", tab5);
            //document.getElementById("add_purchase").setAttribute("tabindex", tab6);
			//document.getElementById("add_purchase_another").setAttribute("tabindex", tab7);
           
            count++;
        
    }
	function deleteRow(e) {
        var t = $("#invoiceGTable > tr").length;
        if (1 == t) alert("There only one row you can't delete.");
        else {
            var a = e.parentNode.parentNode;
            a.parentNode.removeChild(a)
        }
        // calculateSum()
    }
    </script>
         <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
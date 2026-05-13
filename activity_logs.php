<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

if($_SESSION['admin_id'] != 1){
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
    <title>Activity Logs || D-Orders</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
    /* =========================
   MOBILE RESPONSIVE FIXES
   ========================= */

/* Tablets and below */
@media (max-width: 992px) {

    .content-header-left {
        text-align: center;
    }

    .breadcrumb-wrapper {
        justify-content: center;
    }

    form .row > div {
        margin-bottom: 10px;
    }

    .card-title {
        font-size: 16px;
    }

    .table th, 
    .table td {
        font-size: 11px;
        padding: 8px 6px;
        white-space: nowrap;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Mobile phones */
@media (max-width: 768px) {

    /* Make filters stack vertically */
    form .row {
        flex-direction: column;
    }

    form .col-sm-2,
    form .col-sm-5 {
        width: 100%;
        max-width: 100%;
    }

    form button {
        width: 100%;
        margin-top: 10px;
    }

    /* Table becomes scrollable */
    .card-datatable {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table.dt-row-grouping {
        min-width: 900px; /* forces horizontal scroll instead of breaking */
    }

    /* Reduce font size more */
    .table th, 
    .table td {
        font-size: 10px;
        padding: 6px 5px;
    }

    /* Hide less important columns (optional) */
    .table th:nth-child(7),
    .table td:nth-child(7),
    .table th:nth-child(8),
    .table td:nth-child(8) {
        display: none;
    }
}

/* Very small devices */
@media (max-width: 480px) {

    .card-title {
        font-size: 14px;
        text-align: center;
    }

    .breadcrumb {
        font-size: 12px;
    }

    .table th, 
    .table td {
        font-size: 9px;
    }

    .btn {
        font-size: 12px;
        padding: 8px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
    <style>
        .table th, .table td {
            padding: 0.72rem 10px;
            font-size: 10px;
            vertical-align: middle;
        }
        
        .table thead .sorting_asc {
  background-image: url(assets/sort-ascending.png);
  background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}
.table thead .sorting_desc {
  background-image: url(assets/sort-descending.png);
  background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}
.table .sorting{
  background-image: url(assets/sort-descending.png);
 background-repeat: no-repeat;
    background-position: right;
    background-size: 12px;
}

.no-sort{
         background-image: url(assad) !important
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
                                    <li class="breadcrumb-item active">Activity Logs
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">


                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                           <?php echo flash_msg(); ?>
                            <form action="" method="GET" style="margin-bottom: 10px;">
                                <div class="row">
                                    <div class="col-sm-2 col-12" >
                                        <input type="date" class="form-control" name="frmDate" value="<?php if(isset($_GET['frmDate'])){ echo $_GET['frmDate']; } ?>"  required/>
                                    </div>
                                    <div class="col-sm-2 col-12" >
                                        <input type="date" class="form-control" name="toDate" value="<?php if(isset($_GET['toDate'])){ echo $_GET['toDate']; } ?>" required/>
                                    </div>
                                    <div class="col-sm-5 col-12">
                                        <input name="filter" value="1" type="hidden">
                                        <button type="submit" class="btn btn-primary" style="margin-right:10px" >Submit</button>
                                        <!--<a href="?get_all=1" class="btn btn-primary">All Orders</a> -->
                                        
                                        
                                        
                                    </div>
                                    
                                </div>
                            </form>
                           
                        </div>
                    </div>
                    
                  
                    
                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                    <?php 
                    if(isset($_GET['frmDate']) && isset($_GET['toDate'])){
                        $frmDate = date('Y-m-d 00:00:00',strtotime($_GET['frmDate']));
                        
                        $toDate = date('Y-m-d 11:59:59',strtotime($_GET['toDate']));
                        
                        
                        $query = "select * from app_systemlogs where datetime >= '$frmDate' && datetime <= '$toDate' ORDER BY id DESC";         
                        
                        $sn = 0;
                        $logs = $conn->query($query);?>
                    <div class="row">
                       <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Activity Logs (<?=$frmDate;?> to <?=$toDate;?>) </h4>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                               <th>Date/Time</th>
                                               <th>User</th>
                                               <th>Action</th>
                                               <th>Description</th>
                                               <th>Ip Address</th>
                                               <th>Location</th>
                                               <th>Device</th>
                                            </tr>
                                        </thead>
                                        <tbody id="market_1">
                                        <?php 
                                       
                                       while($log = $logs->fetch_assoc()){
                                            $user = $conn->query("SELECT * FROM app_admins WHERE id = '{$log['admin_id']}'")->fetch_assoc();
                                            $sn++;
                                            ?>
                                            
                                    	    <tr>
                                    	        <td><?=$sn;?></td>
                                    	        <td><?php echo date('d/M h:i a', strtotime($log['datetime'])); ?></td>
                                    	        <td><?php echo $user['username']; ?></td>
                                    	        <td><?php echo $log['action']; ?></td>
                                    	        <td><?php echo $log['description']; ?></td>
                                    	        <td><?php echo $log['ip_address']; ?></td>
                                    	        <td><?php echo $log['address']; ?></td>
                                    	        <td><?php echo $log['device']; ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php }else{ ?>
                            <div class="row">
                       <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Last 24 Hours Activity Logs</h4>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping table" style="width:100%">
                                        <thead>
                                            <tr>
                                               <th>Sn</th>
                                               <th>Date/Time</th>
                                               <th>User</th>
                                               <th>Action</th>
                                               <th>Description</th>
                                               <th>Ip Address</th>
                                               <th>Location</th>
                                               <th>Device</th>
                                            </tr>
                                        </thead>
                                        <tbody id="market_1">
                                        <?php 
                                        $frmDate = date('Y-m-d', time()-3600*24);
                                       
                                        $query = "select * from app_systemlogs where datetime >= '$frmDate' ORDER BY id DESC";
                                        $logs = $conn->query($query);
                                        $sn = 0;
                                        while($log = $logs->fetch_assoc()){
                                            $user = $conn->query("SELECT * FROM app_admins WHERE id = '{$log['admin_id']}'")->fetch_assoc();
                                            $sn++;
                                             ?>
                                            
                                    	    <tr>
                                    	        <td><?=$sn;?></td>
                                    	        <td><?php echo date('d/M h:i a', strtotime($log['datetime'])); ?></td>
                                    	        <td><?php echo $user['username']; ?></td>
                                    	        <td><?php echo $log['action']; ?></td>
                                    	        <td><?php echo $log['description']; ?></td>
                                    	        <td><?php echo $log['ip_address']; ?></td>
                                    	        <td><?php echo $log['address']; ?></td>
                                    	        <td><?php echo $log['device']; ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    </form>
                </section>
                <!--/ Row grouping -->
                
               

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
        
        function editOrder(id) {
            $("#openmodelbtn").click();
            $("#editInputs").html('<center><img src="app-assets/ajax_loading.gif" /></center>');
            $.ajax({
                    url : "inc/ajax",
                    method : "POST",
                    data : {editAddress: id},
                    async : true,
                    dataType : 'html',
                    success: function(data){
                        console.log(data);
                       $("#editId").val(id);
                       $("#editInputs").html(data);
                        
                    }
                });
        }
        
        function saveOrder(){
            var data = $("#editOrderForm").serialize();
            $.ajax({
                    url : "inc/ajax?postEditAddress=1",
                    method : "POST",
                    data : data,
                    async : true,
                    success: function(data){
                        console.log(data);
                    }
            });
        }
        
        $(function() {
    $('#select_all').change(function(){
        console.log("Hi");
    	
        var checkboxes = $('#allordersdata').find(':checkbox').not(":disabled");
     
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });
});
//SELECT ALL FUNCITON BY MARKET
$(function() {
    $('input[id^="market_"]').change(function(){
        
    	var inputId = $(this).attr("id");
    	console.log("HI IS"+ inputId);
        var checkboxes = $('#'+inputId+' td').find(':checkbox').not(":disabled");
        
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true).closest('td').parent().addClass('highlight_row');
        } else {
          checkboxes.prop('checked', false).closest('td').parent().removeClass('highlight_row');
        }
    });
});
//CHECKBOX COUNT
$(function() {
    $('input[type="checkbox"]').change(function(){
		var numberNotChecked = $('input[name="aorder[]"]').filter(':checked').length;
		$("#ocount").html(numberNotChecked);
    });
});
        
        
    function printDiv(divName) {
         var printContents = document.getElementById(divName).innerHTML;
         var originalContents = document.body.innerHTML;
         document.body.innerHTML = printContents;
        //  window.print();
        //  document.body.innerHTML = originalContents;
    }
    
    function exportReportToExcel(divName) {
      let table = document.getElementById("ptbl"); // you can use document.getElementById('tableId') as well by providing id to the table tag
      TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
        name: `export.xlsx`, // fileName you could use any name
        sheet: {
          name: 'Sheet 1' // sheetName
        }
      });
    }
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
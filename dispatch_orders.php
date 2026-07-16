<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if(!isset($_SESSION['admin_id'])){
    $conn->close();
    header("location: login.php");
    exit;
}

if(!in_array(10, $permissions_allow)){
    $conn->close();
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['trackingTable'])){
    $order_id = $_POST['order_id'];
    $tracking_id = $_POST['tracking_id'];
    
    foreach($order_id as $k=>$orderid){
        $tr_id = $tracking_id[$k];
       $orderid =  preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $orderid);
        $orderid = removeEmoji($conn->real_escape_string($orderid));
            $conn->query("UPDATE app_orders SET ShipmentTrackingNumber = '$tr_id', isTrackingUpload = '0' WHERE OrderID = '$orderid'");
    }
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Tracking has been uploaded in system and will be posted soon on eBay.</div></div>';
    header("location: dispatch_orders.php");
    exit();
}

if(isset($_POST['csvUpload'])){
    $total_orders = 0;
    $total_order_not_exists = 0;
    $file = $_FILES["trackingCSVFile"];
        
        // Check the file extension
        $allowedExtensions = ['csv'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">File type not allowed to upload.</div></div>';
            header("location: dispatch_orders.php");
            exit();
            
        }
            $tempFilePath = $file['tmp_name'];
            
        
        if (($handle = fopen($tempFilePath, "r")) !== FALSE) {
          
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if(!empty($data[0])){
                    $orderNo = $data[0];
                    $order = $conn->query("SELECT * FROM app_orders WHERE OrderID ='$orderNo' AND ShipmentTrackingNumber IS NULL")->num_rows;
                    // echo '$order: <pre>' .print_r($order,true). '</pre>'; 
                    if($order == 0){
                        $total_order_not_exists += 1;
                    }
                    
                    $total_orders += 1;
                }
                
            }
            
            fclose($handle);
        } else {
             $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Unable to open csv file.</div></div>';
            header("location: dispatch_orders.php");
            exit();
        }
}

if(isset($_POST['labeltype'])){
     $case = isset($_POST['case']) ? $_POST['case'] : [];
    if(count($case) < 1){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast on order to Dispach.</div></div>';
        header("location: dispatch_orders.php");
        exit();
    }
    
    if($_POST['labeltype'] == 100){
        $orderIds = array();
        foreach($case as $order){
            $orderId = strtok($order, '/');
            $conn->query("update app_orders set IsDispatched = '1' where ID = '$orderId'");
            array_push($orderIds, $orderId);
        }
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'DISPATCHED ORDERS', "Total $totalOrders has been dispached", $orderIds);
        $conn->close();
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders has been Dispached</div></div>';
        header("location: dispatch_orders.php");
        exit();
    }
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
    <title>Disptach Orders || BeatOrders</title>
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
    /* ===============================
   BASE TABLE FIX
=================================*/
.table th, 
.table td {
    padding: 0.72rem 10px;
    font-size: 12px;
    vertical-align: middle;
    white-space: nowrap;
}

/* Scroll fix */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.card-datatable {
    overflow-x: auto;
}

/* ===============================
   MOBILE (≤ 767px)
=================================*/
@media (max-width: 767px) {

    /* Header layout fix */
    .card-header {
        display: flex;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .card-header h4 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    /* Buttons full width */
    .card-header button {
        width: 100%;
        margin: 5px 0 !important;
    }

    /* Table */
    .table th, 
    .table td {
        font-size: 11px;
        padding: 6px;
    }

    /* Dropdown / select */
    select {
        width: 100%;
    }

    /* CSV section spacing */
    #csvClickButton {
        width: 100%;
    }
}

/* ===============================
   EXTRA SMALL (≤ 575px)
=================================*/
@media (max-width: 575px) {

    .table th, 
    .table td {
        font-size: 10px;
        padding: 5px;
    }

    .card-title {
        font-size: 14px;
    }

    input[type="checkbox"] {
        transform: scale(0.85);
    }
}

/* ===============================
   TABLETS (768px–991px)
=================================*/
@media (min-width: 768px) and (max-width: 991px) {

    .table th, 
    .table td {
        font-size: 12px;
        padding: 8px;
    }

    .card-header {
        flex-wrap: wrap;
    }

    .card-header button {
        margin-top: 5px;
    }
}

/* ===============================
   LARGE TABLE FIX
=================================*/
.dt-row-grouping {
    display: block;
    width: 100%;
    overflow-x: auto;
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
                                    <li class="breadcrumb-item active">Orders
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
                            
                            
                            <?php if(isset($_POST['csvUpload'])){ 
                           
                            echo '<div class="alert alert-success" role="alert"><div class="alert-body">Total '.$total_orders.' orders found in CSV and total '.$total_order_not_exists.' tracking will be uploaded.</div></div>';
                            
                            ?>
                            
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <button type="button" onclick="submitPrint(100)" style="float:right;margin-right:10px;margin-left: auto;" class="btn btn-outline-primary waves-effect">
                                        <i data-feather='archive'></i>
                                        <span>Submit Data</span>
                                    </button>
                                    
                                </div>
                                <script>
                                    function submitPrint(val){
                                        var form = document.getElementById("allordersdata");
                                        form.submit();
                                    }
                                   
                                </script>
                                <div class="card-datatable">
                                    <form action="" id="allordersdata" method="POST">
                                     <input id="trackingTable" value="0" type="hidden" name="trackingTable"; />
                                   <table class="dt-row-grouping table">
                                        <thead>
                                            <tr>
                                                
                                                <th style="width:5%;">Order Number</th>
                                                <th style="width:20%;">Tracking ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        if (($handle = fopen($tempFilePath, "r")) !== FALSE) {
          
                                            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                                if(!empty($data[0])){
                                                    
                                                
                                                
                                            
                                        ?>
                                    	    <tr>
                                    	        <td><input type="hidden" name="order_id[]" value="<?=$data[0];?>" /><?=$data[0];?></td>
                                    	        <td><input type="hidden" name="tracking_id[]" value="<?=$data[1];?>" /><?=$data[1];?></td>
                                               
                                            </tr>
                                        <?php }}
                                            
                                            fclose($handle);
                                        }  ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                            
                            <?php }else{ ?>
                            
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Ready for dispatch <br><span style="font-size: 12px;" id="ocount">Selected Orders : 0</span></h4>
                                    <button type="button" onclick="submitPrint(100)" style="float:right;margin-right:10px;margin-left: auto;" class="btn btn-outline-primary waves-effect">
                                        <i data-feather='archive'></i>
                                        <span>Dispatch</span>
                                    </button>
                                    
                                      <button type="button" id="csvClickButton" onclick="document.getElementById('trackingCSVFile').click();" style="float:right;margin-right:10px;" class="btn btn-outline-primary waves-effect">
                                        <i data-feather='upload'></i>
                                        <span>Upload CSV</span>
                                    </button>
                                    
                                    <form action="" id="trackingCSVUpload" method="post" style="display:none" enctype="multipart/form-data">
                                        <input id="csvUpload" value="0" type="hidden" name="csvUpload"; />
                                        <input type="file" name="trackingCSVFile" id="trackingCSVFile" onchange="this.form.submit();document.querySelector('#csvClickButton').disabled = true;" accept=".csv">
                                    </form>
                                </div>
                                <script>
                                    function submitPrint(val){
                                        document.getElementById("labeltype").value=val;
                                        var form = document.getElementById("allordersdata");
                                        form.submit();
                                    }
                                   
                                </script>
                                <div class="card-datatable">
                                    <form action="" id="allordersdata" method="POST">
                                      <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                                   <table class="dt-row-grouping table">
                                        <thead>
                                            <tr>
                                                
                                                <th style="width:5%;">Date/Time</th>
                                                <th style="width:20%;">Account</th>
                                                <th style="width:5%;">Order #</th>
                                                <th style="width:40%;">Item Name</th>
                                                <th style="width:9%;">SKU</th>
                                                <th style="width:9%;">P</th>
                                                <th style="width:9%;">
                                                    <select name="sm">
                                                        <option>
                                                            Royal Mail
                                                        </option>
                                                        <option>
                                                            DHL
                                                        </option>
                                                    </select>
                                                </th>
                                            
                                                <th style="width:5%;"><input type="checkbox" id="selectall"/></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $query = "select * from app_orders WHERE IsDispatched='0' && IsPrinted = '1' ORDER BY ID DESC";
                                       
                                        $today = date('Y-m-d');
                                        // echo $query;
                                        $orders = $conn->query($query);
                                        $sn = 0;
                                        while($order = $orders->fetch_assoc()){
                                            $account = $conn->query("select * from app_accounts where id = '{$order['AccountID']}'")->fetch_assoc();
                                            $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$order['OrderID']}'");
                                            $sku = '';
                                            $showItem = '';
                                            while($item = $itemsList->fetch_assoc()){
                                                $showItem .= $item['ItemTitle'].' x '.$item['QuantityPurchased'].'<br>';
                                                $sku .= $item['SKU'].'<br>';
                                            }
                                            $sn++; ?>
                                    	    <tr>
                                    	        <td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td>
                                                <td><?php echo $account['account_name']; ?></td>
                                                <td><?php echo $order['OrderID']; ?></td>
                                                 
                                                <td><?php echo $showItem; ?></td>
                                                <td><?php echo $sku; ?></td>
                                                <td><?php echo $order['Total']; ?></td>
                                                <td><input type="checkbox" class="case" name="case[]" value="<?php echo $order['ID']; ?>"/</td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                            
                            <?php } ?>
                        </div>
                    </div>
                </section>
               

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <? $conn->close(); ?>
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
        
        $("#selectall").click(function () {
                var checkAll = $("#selectall").prop('checked');
                    if (checkAll) {
                        $(".case").prop("checked", true);
                    } else {
                        $(".case").prop("checked", false);
                    }
            });

            $(".case").click(function(){
                if($(".case").length == $(".case:checked").length) {
                    $("#selectall").prop("checked", true);
                } else {
                    $("#selectall").prop("checked", false);
                }

            });
            
            $(function() {
    $('input[type="checkbox"]').change(function(){
		var numberNotChecked = $('input[name="case[]"]').filter(':checked').length;
		$("#ocount").html("Selected Orders : "+numberNotChecked);
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
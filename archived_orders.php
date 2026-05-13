<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("location: login.php");
    exit();
}

if (!in_array(9, $permissions_allow)) {
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if (isset($_POST['labeltype'])) {
    $case = isset($_POST['case']) ? (array)$_POST['case'] : [];

    if (count($case) < 1) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select at least one order to print.</div></div>';
        header("location: archived_orders.php");
        exit();
    }

    $labelType = intval($_POST['labeltype']);
    
    if ($labelType === 100) {
        $orderIds = [];

        foreach ($case as $order) {
            $orderId = intval(strtok($order, '/'));

            // Only update if ID is valid
            if ($orderId > 0) {
                $stmt = $conn->prepare("UPDATE app_orders SET IsArchived = '0' WHERE ID = ?");
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $stmt->close();

                $orderIds[] = $orderId;
            }
        }

        if (count($orderIds) > 0) {
            $totalOrders = count($orderIds);
            $orderIdsStr = implode(', ', $orderIds);
            addSystemLog($conn, 'UNDO ARCHIVED', "Total $totalOrders orders have been unarchived", $orderIdsStr);
            $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders have been unarchived.</div></div>';
        } else {
            $_SESSION['flash'] = '<div class="alert alert-warning" role="alert"><div class="alert-body">No valid orders selected.</div></div>';
        }

        header("location: archived_orders.php");
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
    <title>Orders || D-Orders</title>
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
   GLOBAL RESPONSIVE FIXES
=================================*/

/* Make tables scrollable */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

/* Wrap DataTable */
.dataTables_wrapper {
    width: 100%;
    overflow-x: auto;
}

/* Prevent breaking layout */
table.dataTable {
    width: 100% !important;
}

/* ===============================
   MOBILE (<= 767px)
=================================*/
@media (max-width: 767px) {

    /* Header & buttons */
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .card-header h4 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .card-header button {
        width: 100%;
        margin-bottom: 8px;
    }

    /* Table adjustments */
    .table th,
    .table td {
        font-size: 11px;
        padding: 6px;
        white-space: nowrap; /* prevent breaking */
    }

    /* DataTables controls */
    .dataTables_length,
    .dataTables_filter {
        width: 100%;
        text-align: left;
    }

    .dataTables_filter input {
        width: 100%;
        margin-top: 5px;
    }

    /* Pagination */
    .dataTables_paginate {
        text-align: center !important;
        margin-top: 10px;
    }
}

/* ===============================
   SMALL DEVICES (<= 575px)
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

    /* Checkbox center */
    input[type="checkbox"] {
        transform: scale(0.9);
    }
}

/* ===============================
   TABLETS (768px - 991px)
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
   LARGE TABLE FIX (IMPORTANT)
=================================*/
.tt4 {
    display: block;
    width: 100%;
    overflow-x: auto;
}

/* Prevent column squeeze */
.tt4 th, 
.tt4 td {
    white-space: nowrap;
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
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
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Archived Orders</h4>
                                    <button type="button" onclick="submitPrint(100)" style="float:right;margin-right:10px" class="btn btn-outline-primary waves-effect">
                                        <i data-feather='archive'></i>
                                        <span>Undo Archive</span>
                                    </button>
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
                        <input id="labeltype" value="0" type="hidden" name="labeltype" />
                                   <table class="dt-row-grouping table tt4">
                                        <thead>
                                            <tr>
                                                <th>SN</th>
                                                <th>AccountID</th>
                                                <th>OrderID</th>
                                                <th>Created Time</th>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th><input type="checkbox" id="selectall"/></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $query = "select * from app_orders WHERE IsArchived='1' ORDER BY ID DESC";
                                       
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
                                    	        <td><?php echo $sn; ?></td>
                                                <td><?php echo $account['account_name']; ?></td>
                                                <td><?php echo $order['OrderID']; ?></td>
                                                 <td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td>
                                                <td>
                                                <?php echo $showItem; ?>
                                                <?php if($order['BuyerCheckoutMessage'] !=''){
                                                    echo '<br><span style="color:red">Buyer Note: '.$order['BuyerCheckoutMessage'].'</span>';
                                                }?>
                                                </td>
                                                <td><?php echo $order['QuantityPurchased'] ?? 'N/A'; ?></td>
                                                <td><?php echo $order['Total'] ?? 'N/A'; ?></td>
                                                <td><input type="checkbox" class="case" name="case[]" value="<?php echo $order['ID']; ?>"/></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
               

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
    <!--<script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>-->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
    $(document).ready(function() {
        $('.tt4').DataTable({
            "lengthMenu": [[10, 30, 50, -1], [10, 30, 50, "All"]],
            "pageLength": 10
        });
    });
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
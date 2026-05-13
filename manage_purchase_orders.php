<?php 
exit;
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(25, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['deleteEntries'])){
    $case = $_POST['case'];
        if(count($case) < 1){
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast row to delete.</div></div>';
            header("location: manage_purchase_orders.php");
            exit();
        }
        foreach($case as $delId){
           $conn->query("DELETE FROM app_purchase_orders where id = '$delId'");
           $conn->query("DELETE FROM app_purchase_orders_detail where purchase_id = '$delId'");
        }
        
        $totalOrders = count($case);
        $orderIds = implode(', ', $case);
        
        addSystemLog($conn, 'PURCHASE ORDERS DELETE', "Total $totalOrders purchase orders has been deleted", $orderIds);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected rows has been deleted</div></div>';
        header("location: manage_purchase_orders.php");
        exit();
    
}

if(isset($_GET['create_purchase'])){
    $order_id = $_GET['create_purchase'];
    $purchase_o = $conn->query("SELECT * FROM app_purchase_orders where id = '$order_id'");
    if($purchase_o->num_rows > 0){
        $purchase_o = $purchase_o->fetch_assoc();
        $date = date('Y-m-d');
        $created_date = date('Y-m-d H:i:s');
        $pquery = "INSERT INTO app_purchase set supplier_id = '{$purchase_o['supplier_id']}', date = '$date', total_amount = '{$purchase_o['total_amount']}', created_date = '$created_date'";
        $conn->query($pquery);
        $purchase_id = $conn->insert_id;
        $get_order_detail = $conn->query("select * from app_purchase_orders_detail where purchase_id = '$order_id'");
        while($row=$get_order_detail->fetch_assoc()){
			$conn->query("insert into app_purchase_detail set purchase_id = '$purchase_id', item_id = '{$row['item_id']}', qty = '{$row['qty']}', price = '{$row['price']}', total = '{$row['total']}'");
			$pdid = $conn->insert_id;
			$conn->query("insert into app_stocks set pid = '$purchase_id', pdid = '$pdid', item_id = '{$row['item_id']}', qty = '{$row['qty']}', datetime = '$created_date'");
        }
        $conn->query("DELETE FROM app_purchase_orders WHERE id = '$order_id'");
        $conn->query("DELETE FROM app_purchase_orders_detail WHERE purchase_id = '$order_id'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Order has been recevied and purchase created.</div></div>';
        header("location: manage_purchase_orders.php");
        exit();
    }else{
        header("location: manage_purchase_orders.php");
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
    <title>Manage Purchase Orders || D-Orders</title>
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
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
<style>
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
                                    <li class="breadcrumb-item active">Manage Purchase Orders
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
                            <form action="" id="allordersdata" method="POST">
                            <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Purchases Orders</h4>
                                    <div>
                                        <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="create_purchase.php" ><i data-feather='plus'></i></a>
                                        <button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light"><i data-feather='trash-2'></i></button>
                                    </div>
                                    
                                </div>
                                <div class="card-datatable">
                                    
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th style="width:15%"><input type="checkbox" id="selectall"/> Order id</th>
                                                <th>Order #</th>
                                                <th>Supplier</th>
                                                <th>Expected Delivery</th>
                                                <th>Amount</th>
                                                <th style="width:22%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        
                                        if(isset($_GET['item_id'])){
                                            $purchases = $conn->query("select * from app_purchase_orders where id IN (SELECT purchase_id FROM app_purchase_orders_detail where item_id = '{$_GET['item_id']}') order by id desc");
                                        }else{
                                            $purchases = $conn->query("select * from app_purchase_orders order by id desc");
                                        }
                                        
                                        $sn = 0;
                                        
                                        while($purchase = $purchases->fetch_assoc()){
                                            // $totalSale = $conn->query("select SUM(Total) as amount from app_orders where AccountID = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            // $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            // $balance = $totalPayments-$totalSale;
                                            $supplier = $conn->query("select * from app_suppliers where id = '{$purchase['supplier_id']}'")->fetch_assoc();
                                            $sn++; ?>
                                    	    <tr>
                                                <td><input type="checkbox" class="case" name="case[]" value="<?php echo $purchase['id']; ?>"/> <?php echo $purchase['id']; ?></td>
                                                <td><?php echo $purchase['order_no']; ?></td>
                                                <td><?php echo $supplier['name']; ?><br><?php echo $supplier['phone']; ?></td>
                                                <td><?php echo $purchase['expected_delivery']; ?></td>
                                                <td>$<?php echo $purchase['total_amount']; ?></td>
                                               
                                                <td>
                                                    <a type="button" href="create_purchase.php?from_order=<?php echo $purchase['id']; ?>" class="btn btn-success btn-sm">Received</a>
                                                    <a type="button" href="edit_purchase_order.php?id=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                                    <a type="button" href="view_purchase_order.php?id=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                                </td>
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                   
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
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
       
    $(".dt-row-grouping-t").DataTable();
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

  
    </script>
</body>
<!-- END: Body-->

</html>
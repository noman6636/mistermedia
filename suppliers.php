<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(21, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_GET['delete'])){
$delId = $_GET['delete'];
$conn->query("DELETE FROM app_suppliers where id = '$delId'");
addSystemLog($conn, 'SUPPLIER DELETE', "Supplier with id ($delId) has been deleted", "");
$_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Supplier Deleted Successfully.</div></div>';
header("location: suppliers.php");
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
    <title>Suppliers || D-Orders</title>
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
    /* =====================================
   DEFAULT IMPROVEMENTS (ALL SCREENS)
===================================== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* Buttons spacing */
.card-header .btn {
    margin-top: 5px;
}


/* =====================================
   EXTRA SMALL DEVICES (phones <576px)
===================================== */
@media (max-width: 575.98px) {

    .content-wrapper {
        padding: 10px;
    }

    .card {
        padding: 10px;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .card-title {
        font-size: 16px;
        margin-bottom: 10px;
    }

    /* Table -> Card Style */
    .table thead {
        display: none;
    }

    .table tbody tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
    }

    .table tbody td {
        display: block;
        width: 100%;
        text-align: left;
        font-size: 13px;
        padding: 6px 10px;
        border: none;
        position: relative;
    }

    .table tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        display: block;
        margin-bottom: 3px;
        color: #555;
    }

    /* Buttons full width */
    .table td .btn {
        width: 100%;
        margin-bottom: 5px;
        font-size: 13px;
        padding: 6px;
    }
}


/* =====================================
   SMALL DEVICES (phones landscape 576–767px)
===================================== */
@media (min-width: 576px) and (max-width: 767.98px) {

    .content-wrapper {
        padding: 12px;
    }

    .card-title {
        font-size: 17px;
    }

    .table th, .table td {
        font-size: 12px;
        padding: 8px;
    }

    .table td .btn {
        font-size: 12px;
        padding: 5px 8px;
    }
}


/* =====================================
   MEDIUM DEVICES (tablets 768–991px)
===================================== */
@media (min-width: 768px) and (max-width: 991.98px) {

    .content-wrapper {
        padding: 15px;
    }

    .table th, .table td {
        font-size: 13px;
    }

    .card-title {
        font-size: 18px;
    }
}


/* =====================================
   LARGE DEVICES (992px+)
===================================== */
@media (min-width: 992px) {

    .content-wrapper {
        padding: 20px;
    }

    .table th, .table td {
        font-size: 14px;
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
                                    <li class="breadcrumb-item active">Suppliers
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
                                    <h4 class="card-title">List of Suppliers</h4>
                                    <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="add_supplier.php" ><i data-feather='plus'></i></a>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>PostCode</th>
                                                <th>Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $suppliers = $conn->query("select * from app_suppliers order by name asc");
                                        $sn = 0;
                                        
                                        while($supplier = $suppliers->fetch_assoc()){
                                            // $totalSale = $conn->query("select SUM(Total) as amount from app_orders where AccountID = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            // $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                            // $balance = $totalPayments-$totalSale;
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td data-label="Name"><?php echo $supplier['name']; ?></td>
                                                <td data-label="Contact"><?php echo $supplier['mobile']; ?><br><?php echo $supplier['phone']; ?></td>
                                                <td data-label="Email"><?php echo $supplier['email']; ?></td>
                                                <td data-label="PostCode"><?php echo $supplier['postcode']; ?></td>
                                                <td data-label="Address"><?php echo $supplier['address']; ?>,<br><?php echo $supplier['city']; ?>, <?php echo $supplier['country']; ?></td>
                                                <td>
                                                    <a type="button" href="add_supplier.php?edit=<?php echo $supplier['id']; ?>" class="btn btn-primary">Edit</a>
                                                    <a type="button" href="?delete=<?php echo $supplier['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this account. All Purchased and Stocks will also deleted from that supplier.');">Delete</a>
                                                </td>
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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

  
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
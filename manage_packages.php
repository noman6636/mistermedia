<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check permissions safely
if (!isset($permissions_allow) || !in_array(19, $permissions_allow)) {
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("Location: index.php");
    exit();
}

// Handle delete request
if (isset($_POST['deleteEntries'])) {
    // Safely get case array with null coalescing
    $case = $_POST['case'] ?? [];
    
    // Validate selection
    if (empty($case)) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select at least one row to delete.</div></div>';
        header("Location: manage_packages.php");
        exit();
    }

    // Prepare statement for secure update
    $stmt = $conn->prepare("UPDATE app_packages SET deleted = '1' WHERE id = ?");
    $updatedIds = [];
    $successCount = 0;

    foreach ($case as $delId) {
        // Validate and sanitize ID
        if (!is_numeric($delId)) {
            continue;
        }
        
        $delId = (int)$delId;
        
        try {
            $stmt->bind_param("i", $delId);
            if ($stmt->execute()) {
                $updatedIds[] = $delId;
                $successCount++;
            }
        } catch (Exception $e) {
            // Log error if needed
            error_log("Error marking package ID $delId as deleted: " . $e->getMessage());
            continue;
        }
    }

    $stmt->close();

    // Only log if updates were successful
    if ($successCount > 0) {
        $packageIds = implode(', ', $updatedIds);
        addSystemLog($conn, 'PACKAGE DELETE', "Total $successCount packages have been marked as deleted", $packageIds);
        $message = "Selected packages have been marked as deleted";
        $alertClass = "alert-success";
    } else {
        $message = "No packages were updated";
        $alertClass = "alert-danger";
    }

    $_SESSION['flash'] = '<div class="alert '.$alertClass.'" role="alert"><div class="alert-body">'.$message.'</div></div>';
    header("Location: manage_packages.php");
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
    <title>Manage Packages || D-Orders</title>
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
   GLOBAL RESPONSIVE FIX
========================= */
@media (max-width: 991px) {

    /* Better spacing */
    .content-wrapper .col-12 {
        padding: 0 10px;
    }

    /* Card spacing */
    .card {
        margin-bottom: 15px;
    }

    /* Header buttons stack */
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .card-header div {
        width: 100%;
        display: flex;
        gap: 10px;
    }

    .card-header .btn {
        flex: 1;
        width: 100%;
    }
}

/* =========================
   TABLE RESPONSIVE FIX
========================= */
@media (max-width: 768px) {

    /* Scroll instead of breaking */
    .card-datatable {
        overflow-x: auto;
    }

    .card-datatable table {
        min-width: 650px; /* keeps layout safe */
    }

    table th,
    table td {
        white-space: nowrap;
        font-size: 13px;
        padding: 8px;
    }

    /* Fix action button */
    table .btn {
        padding: 5px 8px;
        font-size: 12px;
    }
}

/* =========================
   SMALL PHONES
========================= */
@media (max-width: 480px) {

    body {
        font-size: 13px;
    }

    .card-body {
        padding: 10px;
    }

    .card-header h4 {
        font-size: 16px;
    }

    table th,
    table td {
        font-size: 12px;
        padding: 6px;
    }

    /* Buttons spacing */
    .btn {
        margin-top: 5px;
    }
}

/* =========================
   PREVENT OVERFLOW
========================= */
body {
    overflow-x: hidden;
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
                                    <li class="breadcrumb-item active">Manage Purchase
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
                                    <h4 class="card-title">List of Packages</h4>
                                    <div>
                                        <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="create_package.php" ><i data-feather='plus'></i></a>
                                        <button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light"><i data-feather='trash-2'></i></button>
                                    </div>
                                    
                                </div>
                                <div class="card-datatable table-responsive">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th style="width:15%"><input type="checkbox" id="selectall"/> Package id</th>
                                                <th>SKU</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th style="width:12%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $packages = $conn->query("select * from app_packages where deleted = '0' order by sku asc");
                                        $sn = 0;
                                        
                                        while($package = $packages->fetch_assoc()){
                                            
                                            $sn++; ?>
                                    	    <tr>
                                                <td><input type="checkbox" class="case" name="case[]" value="<?php echo $package['id']; ?>"/> <?php echo $package['id']; ?></td>
                                                <td><?php echo $package['sku']; ?></td>
                                                <td><?php echo $package['name']; ?></td>
                                                <td>£<?php echo $package['price']; ?></td>
                                               
                                                <td>
                                                    <a type="button" href="view_package.php?id=<?php echo $package['id']; ?>" class="btn btn-primary">View</a>
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
       
   $('.dt-row-grouping-t').DataTable({
        responsive: true,
        scrollX: true,
        autoWidth: false,
        lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
        pageLength: 10
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
  
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
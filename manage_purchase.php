<?php
require_once "inc/config.php";
require_once "inc/functions.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if permissions array exists and user has required permission
if (!isset($permissions_allow) || !in_array(23, $permissions_allow)) {
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("Location: index.php");
    exit();
}

// Handle delete request
if (isset($_POST['deleteEntries'])) {
    // Initialize case array safely
    $case = $_POST['case'] ?? [];
    
    // Validate selection
    if (empty($case) || count($case) < 1) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select at least one row to delete.</div></div>';
        header("Location: manage_purchase.php");
        exit();
    }

    // Prepare statements for better security
    $delPurchase = $conn->prepare("DELETE FROM app_purchase WHERE id = ?");
    $delPurchaseDetail = $conn->prepare("DELETE FROM app_purchase_detail WHERE purchase_id = ?");
    $delStocks = $conn->prepare("DELETE FROM app_stocks WHERE pid = ?");
    
    $deletedIds = [];
    $successCount = 0;

    foreach ($case as $delId) {
        // Validate ID (should be numeric)
        if (!is_numeric($delId)) {
            continue;
        }

        // Sanitize ID
        $delId = (int)$delId;
        
        try {
            // Delete from purchase table
            $delPurchase->bind_param("i", $delId);
            $delPurchase->execute();
            
            // Delete from purchase detail table
            $delPurchaseDetail->bind_param("i", $delId);
            $delPurchaseDetail->execute();
            
            // Delete from stocks table
            $delStocks->bind_param("i", $delId);
            $delStocks->execute();
            
            $deletedIds[] = $delId;
            $successCount++;
        } catch (Exception $e) {
            // Log error if needed
            error_log("Error deleting purchase ID $delId: " . $e->getMessage());
            continue;
        }
    }

    // Close prepared statements
    $delPurchase->close();
    $delPurchaseDetail->close();
    $delStocks->close();

    // Log action if any deletions were successful
    if ($successCount > 0) {
        $purchaseIds = implode(', ', $deletedIds);
        addSystemLog($conn, 'PURCHASE DELETE', "Total $successCount purchases have been deleted", $purchaseIds);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected rows have been deleted</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">No rows were deleted</div></div>';
    }

    header("Location: manage_purchase.php");
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
    <title>Manage Purchase || D-Orders</title>
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
   GLOBAL MOBILE FIX
========================= */
@media (max-width: 768px) {

    .content-wrapper {
        padding: 8px !important;
    }

    .card-body {
        padding: 10px !important;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }

    .card-header h4 {
        font-size: 16px;
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
   TABLE → MOBILE CARD STYLE
========================= */
@media (max-width: 768px) {

    .table {
        display: block;
    }

    .table thead {
        display: none;
    }

    .table tbody {
        display: block;
    }

    .table tbody tr {
        display: block;
        background: #f9f9f9;
        margin-bottom: 12px;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #eee;
    }

    .table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border: none;
        font-size: 13px;
    }

    /* First column (checkbox + ID) */
    .table tbody td:first-child {
        font-weight: bold;
        font-size: 14px;
        display: flex;
        gap: 8px;
    }

    /* Action buttons */
    .table tbody td:last-child {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
        margin-top: 8px;
    }

    .table tbody td:last-child a {
        flex: 1;
        text-align: center;
    }
}


/* =========================
   BUTTON FIXES
========================= */
@media (max-width: 768px) {

    .btn {
        font-size: 13px;
        padding: 6px;
    }

    .btn img {
        width: 16px;
        height: 16px;
    }
}


/* =========================
   CHECKBOX FIX
========================= */
@media (max-width: 768px) {

    input[type="checkbox"] {
        transform: scale(1.2);
    }
}


/* =========================
   DATATABLE SCROLL FIX (IMPORTANT)
========================= */
@media (max-width: 768px) {

    .card-datatable {
        overflow-x: auto;
    }
}


/* =========================
   SMALL DEVICES
========================= */
@media (max-width: 480px) {

    .table tbody td {
        font-size: 12px;
    }

    .card-header h4 {
        font-size: 14px;
    }

    .btn {
        font-size: 12px;
    }
}
@media (max-width: 768px) {
    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
table.dataTable {
    width: 100% !important;
}
@media (max-width: 767px) {

    table.dataTable thead {
        display: none;
    }

    table.dataTable tbody tr {
        display: block;
        background: #fff;
        margin-bottom: 12px;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    table.dataTable tbody td {
        display: flex;
        justify-content: space-between;
        padding: 6px 10px;
        font-size: 13px;
        border: none;
    }

    table.dataTable tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #666;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
    <style>
        .table th,
        .table td {
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
                                        <h4 class="card-title">List of Purchases</h4>
                                        <div>
                                            <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="create_purchase.php"><i data-feather='plus'></i></a>
                                            <button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light"><i data-feather='trash-2'></i></button>
                                        </div>

                                    </div>
                                    <div class="card-datatable pl-2 pr-2">
                                        <table class="dt-row-grouping-t table">
                                            <thead>
                                                <tr>
                                                    <th style="width:15%"><input type="checkbox" id="selectall" /> Purchase id</th>
                                                    <th>Invoice #</th>
                                                    <th>Supplier</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th style="width:15%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($_GET['item_id'])) {
                                                    $purchases = $conn->query("select * from app_purchase where id IN (SELECT purchase_id FROM app_purchase_detail where item_id = '{$_GET['item_id']}' && status = 0) order by id desc");
                                                } else {
                                                    $purchases = $conn->query("select * from app_purchase order by id desc");
                                                }

                                                $sn = 0;

                                                while ($purchase = $purchases->fetch_assoc()) {
                                                    // $totalSale = $conn->query("select SUM(Total) as amount from app_orders where AccountID = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                                    // $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}'")->fetch_assoc()['amount']+0;
                                                    // $balance = $totalPayments-$totalSale;
                                                    $supplier = $conn->query("select * from app_suppliers where id = '{$purchase['supplier_id']}'")->fetch_assoc();
                                                    $sn++; ?>
                                                    <tr>
                                                        <td data-label="Purchase ID"><input type="checkbox" class="case" name="case[]" value="<?php echo $purchase['id'] ?? 'N/A'; ?>" /> <?php echo $purchase['id'] ?? 'N/A'; ?></td>
                                                        <td data-label="Invoice #"><?php echo $purchase['invoice_no'] ?? 'N/A'; ?></td>
                                                        <td data-label="Supplier"><?php echo $supplier['name'] ?? 'N/A'; ?><br><?php echo $supplier['phone'] ?? 'N/A'; ?></td>
                                                        <td data-label="Date"><?php echo $purchase['date'] ?? 'N/A'; ?></td>
                                                        <td data-label="Amount">$<?php echo $purchase['total_amount'] ?? 'N/A'; ?></td>
                                                        

                                                        <td>
                                                            <a type="button" href="edit_purchase.php?edit=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-xs btn-sm waves-effect waves-float waves-light">
                                                                <img src="https://cdn-icons-png.flaticon.com/128/1827/1827933.png" loading="lazy" alt="Edit " title="Edit " width="20" height="20">
                                                            </a>
                                                            <a type="button" href="view_purchase.php?id=<?php echo $purchase['id']; ?>" class="btn btn-success btn-xs btn-sm waves-effect waves-float waves-light">
                                                                <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" loading="lazy" alt="View " title="View " width="20" height="20">
                                                            </a>
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

       $(".dt-row-grouping-t").DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
            ]
        });

        $("#selectall").click(function() {
            var checkAll = $("#selectall").prop('checked');
            if (checkAll) {
                $(".case").prop("checked", true);
            } else {
                $(".case").prop("checked", false);
            }
        });

        $(".case").click(function() {
            if ($(".case").length == $(".case:checked").length) {
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
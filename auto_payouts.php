<?php
require_once "inc/config.php";
require_once "inc/functions.php";

// Auth checks
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!in_array(34, $permissions_allow)) {
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("Location: index.php");
    exit();
}

// Handle delete — use prepared statement to prevent SQL injection
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM app_auto_payouts WHERE id = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();

    addSystemLog($conn, 'AUTO PAYOUT DELETED', "Auto Payout with id ($delId) has been deleted.", $delId);
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payout Deleted Successfully.</div></div>';
    header("Location: auto_payouts.php");
    exit();
}

// Fetch all payouts with their account names in one JOIN query (avoids N+1 queries)
$payouts = [];
$result = $conn->query("
    SELECT p.*, a.account_name
    FROM app_auto_payouts p
    LEFT JOIN app_accounts a ON a.id = p.account_id
    ORDER BY p.id DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payouts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Auto Payouts | D-Orders</title>

    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" href="app-assets/vendors/css/extensions/toastr.min.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" href="app-assets/css/colors.css">
    <link rel="stylesheet" href="app-assets/css/components.css">
    <link rel="stylesheet" href="app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" href="app-assets/css/themes/semi-dark-layout.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" href="app-assets/css/plugins/extensions/ext-component-toastr.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">

    <style>
        /* ── Responsive table wrapper ── */
        .card-datatable {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── Table base ── */
        table.dataTable {
            width: 100% !important;
            min-width: 500px;
        }

        /* ── Row status colors ── */
        tr.status-pending { background: #fbe3e3 !important; }
        tr.status-processing { background: beige !important; }

        /* ── Amount negative ── */
        .amount-negative {
            color: red;
            font-weight: 700;
        }

        /* ── Action badge ── */
        .no-action-badge {
            color: #6c757d;
            font-size: 0.8rem;
        }

        /* ── Mobile: stack cards ── */
        @media (max-width: 767.98px) {
            .content-header-left h2 { font-size: 1.1rem; }

            /* Hide less-critical columns on small phones */
            table.dataTable thead th:nth-child(3),
            table.dataTable tbody td:nth-child(3) {
                display: none;
            }

            .btn-xs { padding: 4px 8px; font-size: 0.75rem; }

            .card-header { flex-wrap: wrap; gap: .5rem; }

            /* DataTables controls */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                text-align: left;
                margin-bottom: 8px;
            }
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none;
                text-align: left;
            }
        }

        @media (max-width: 575.98px) {
            /* Hide Date column too on very small screens */
            table.dataTable thead th:nth-child(5),
            table.dataTable tbody td:nth-child(5) {
                display: none;
            }
            .breadcrumb { font-size: 0.8rem; }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            table.dataTable { font-size: 0.9rem; }
        }
    </style>
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static"
      data-open="hover" data-menu="horizontal-menu" data-col="">

    <?php include("header.php"); ?>

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>

        <div class="content-wrapper">

            <!-- Breadcrumb -->
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Auto Payouts</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="content-body">
                <section id="page-account-settings">
                    <div class="row">
                        <div class="col-12">

                            <?php echo flash_msg(); ?>

                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title mb-0">List of Payouts</h4>
                                </div>

                                <div class="card-datatable">
                                    <table class="dt-payouts table table-hover" role="grid">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Account</th>
                                                <th>Narration</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($payouts)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">No payouts found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($payouts as $sn => $payout):
                                                $rowClass = '';
                                                if ($payout['status'] == 1) $rowClass = 'status-pending';
                                                elseif ($payout['status'] == 2) $rowClass = 'status-processing';

                                                $accountName = htmlspecialchars($payout['account_name'] ?? 'N/A');
                                                $description = htmlspecialchars($payout['description'] ?? '');
                                                $amount      = $payout['amount'] ?? 0;
                                                $date        = date('Y-m-d', strtotime($payout['datetime']));
                                                $isNegative  = (float)$amount < 0;
                                            ?>
                                            <tr class="<?= $rowClass ?>">
                                                <td><?= $sn + 1 ?></td>
                                                <td><?= $accountName ?></td>
                                                <td><?= $description ?></td>
                                                <td class="<?= $isNegative ? 'amount-negative' : '' ?>">
                                                    <?= htmlspecialchars((string)$amount) ?>
                                                </td>
                                                <td><?= $date ?></td>
                                                <td>
                                                    <?php if ($isNegative): ?>
                                                        <a href="?delete=<?= (int)$payout['id'] ?>"
                                                           class="btn btn-danger btn-xs btn-sm waves-effect waves-light"
                                                           onclick="return confirm('Are you sure you want to delete this payout?');">
                                                            Delete
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="no-action-badge">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div><!-- /card-datatable -->
                            </div><!-- /card -->

                        </div>
                    </div>
                </section>
            </div><!-- /content-body -->
        </div>
    </div>
    <!-- END: Content -->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- Vendor JS (single bundle — load once) -->
    <script src="app-assets/vendors/js/vendors.min.js"></script>

    <!-- DataTables -->
    <script src="app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>

    <!-- Theme JS -->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>

    <script>
        $(document).ready(function () {
            // Init feather icons
            if (typeof feather !== 'undefined') {
                feather.replace({ width: 14, height: 14 });
            }

            // Init DataTable — responsive, no extra buttons needed for this page
            $('.dt-payouts').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 5 } // Action column not sortable
                ],
                language: {
                    search: "Search payouts:"
                }
            });
        });
    </script>
         <script src="footer.js"></script>
</body>
</html>
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

// ── ADD PAYOUT ────────────────────────────────────────────────────────────────
if (isset($_POST['add_payout'])) {
    $account_id  = (int) $_POST['account_id'];
    $amount      = (float) $_POST['amount'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $date        = date('Y-m-d H:i:s', strtotime($_POST['date']));

    if ($account_id > 0 && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO app_payouts (account_id, amount, datetime, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $account_id, $amount, $date, $description);
        $stmt->execute();
        $stmt->close();
        addSystemLog($conn, 'PAYOUT ADDED', "Payout received in account id ($account_id) with amount ($amount)", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payment added successfully.</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Invalid account or amount.</div></div>';
    }
    header("Location: receive_payout.php");
    exit();
}

// ── EDIT PAYOUT ───────────────────────────────────────────────────────────────
if (isset($_POST['edit_payout'])) {
    $id          = (int) $_POST['edit_payout'];
    $account_id  = (int) $_POST['account_id'];
    $amount      = (float) $_POST['amount'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $date        = date('Y-m-d H:i:s', strtotime($_POST['date']));

    if ($id > 0 && $account_id > 0 && $amount > 0) {
        $stmt = $conn->prepare("UPDATE app_payouts SET account_id=?, amount=?, datetime=?, description=? WHERE id=?");
        $stmt->bind_param("idssi", $account_id, $amount, $date, $description, $id);
        $stmt->execute();
        $stmt->close();
        addSystemLog($conn, 'PAYOUT UPDATED', "Payout updated in account id ($account_id) with amount ($amount)", $id);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payout updated successfully.</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Invalid data provided.</div></div>';
    }
    header("Location: receive_payout.php");
    exit();
}

// ── DELETE PAYOUT ─────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    if ($delId > 0) {
        $stmt = $conn->prepare("DELETE FROM app_payouts WHERE id = ?");
        $stmt->bind_param("i", $delId);
        $stmt->execute();
        $stmt->close();
        addSystemLog($conn, 'PAYOUT DELETED', "Payout with id ($delId) has been deleted.", $delId);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payout deleted successfully.</div></div>';
    }
    header("Location: receive_payout.php");
    exit();
}

// ── FETCH EDIT DATA ───────────────────────────────────────────────────────────
$payout    = null;
$edit_mode = false;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt    = $conn->prepare("SELECT * FROM app_payouts WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $payout = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($payout) $edit_mode = true;
}

// ── FETCH ACCOUNTS (once) ─────────────────────────────────────────────────────
$accounts_result = $conn->query("SELECT id, account_name FROM app_accounts ORDER BY account_name ASC");
$accounts = [];
while ($row = $accounts_result->fetch_assoc()) $accounts[] = $row;

// ── FETCH PAYOUTS (once, with JOIN) ──────────────────────────────────────────
$payouts_result = $conn->query("
    SELECT p.*, a.account_name
    FROM app_payouts p
    LEFT JOIN app_accounts a ON a.id = p.account_id
    ORDER BY p.id DESC
");
$payouts = [];
while ($row = $payouts_result->fetch_assoc()) $payouts[] = $row;
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Receive Payouts | D-Orders</title>

    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
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

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">

    <style>
        /* ── Select2 arrow hide ── */
        .select2-selection__arrow { display: none; }

        /* ── Form card ── */
        .payout-form-card { margin-bottom: 1.5rem; }

        /* ── Table action buttons spacing on small screens ── */
        .action-btns a { margin-bottom: 4px; }

        /* ── Mobile: stack table horizontally scrollable ── */
        .card-datatable { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* ────────────────────────────────────────────
           MEDIA QUERIES
        ──────────────────────────────────────────── */

        /* Extra small – phones < 576px */
        @media (max-width: 575.98px) {
            .content-wrapper { padding: 0.75rem !important; }

            /* Form columns: full width on mobile */
            .payout-form-card .col-7,
            .payout-form-card .col-5,
            .payout-form-card .col-4,
            .payout-form-card .col-2 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            /* Submit button row */
            .payout-form-card .col-2 {
                padding-top: 0 !important;
                margin-bottom: 0.5rem;
            }

            /* Card padding */
            .card-body { padding: 1rem !important; }

            /* Breadcrumb font size */
            .breadcrumb { font-size: 0.8rem; }

            /* Table font size */
            .card-datatable table { font-size: 0.8rem; }

            /* Action buttons: stack vertically */
            .action-btns { display: flex; flex-direction: column; gap: 4px; }
            .action-btns a { width: 100%; text-align: center; }

            /* Card title */
            .card-title { font-size: 1rem; }
        }

        /* Small – phones 576px–767px */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .payout-form-card .col-7,
            .payout-form-card .col-5 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .payout-form-card .col-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .payout-form-card .col-2 {
                flex: 0 0 100%;
                max-width: 100%;
                padding-top: 0 !important;
            }
            .action-btns { display: flex; gap: 4px; flex-wrap: wrap; }
        }

        /* Medium – tablets 768px–991px */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .payout-form-card .col-7 { flex: 0 0 58.333%; max-width: 58.333%; }
            .payout-form-card .col-5 { flex: 0 0 41.667%; max-width: 41.667%; }
            .payout-form-card .col-4 { flex: 0 0 40%; max-width: 40%; }
            .payout-form-card .col-2 { flex: 0 0 20%; max-width: 20%; }
            .card-datatable table { font-size: 0.85rem; }
        }

        /* Large – small desktops 992px–1199px */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .payout-form-card .col-4 { flex: 0 0 33.333%; max-width: 33.333%; }
        }

        /* Submit button full-width on very small */
        @media (max-width: 767.98px) {
            .payout-form-card button[type="submit"] { width: 100%; }
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                margin: -10px !important;
        }
    </style>
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static"
      data-open="hover" data-menu="horizontal-menu" data-col="">

    <?php include("header.php"); ?>

    <!-- BEGIN: Content -->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            <!-- Breadcrumb -->
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Receive Payouts</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="page-account-settings">

                    <!-- ── FORM CARD ── -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card payout-form-card">
                                <div class="card-body">
                                    <?php echo flash_msg(); ?>

                                    <form action="" method="post" autocomplete="on">
                                        <div class="row">

                                            <!-- Account -->
                                            <div class="col-7">
                                                <div class="form-group">
                                                    <label for="account_id">Account</label>
                                                    <select name="account_id" class="form-control select2" id="account_id" required>
                                                        <?php foreach ($accounts as $account): ?>
                                                            <option value="<?= (int)$account['id'] ?>"
                                                                <?= ($edit_mode && $account['id'] == $payout['account_id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($account['account_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Date -->
                                            <div class="col-5">
                                                <div class="form-group">
                                                    <label for="date">Date</label>
                                                    <input type="date" class="form-control" id="date" name="date"
                                                           value="<?= $edit_mode ? date('Y-m-d', strtotime($payout['datetime'])) : date('Y-m-d') ?>"
                                                           required>
                                                </div>
                                            </div>

                                            <!-- Amount -->
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="amount">Amount</label>
                                                    <input type="number" class="form-control" id="amount" name="amount"
                                                           value="<?= $edit_mode ? htmlspecialchars($payout['amount']) : '' ?>"
                                                           placeholder="0.00" step="0.01" min="0.01" required>
                                                </div>
                                            </div>

                                            <!-- Narration -->
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="narration">Narration</label>
                                                    <input type="text" class="form-control" id="narration" name="description"
                                                           value="<?= $edit_mode ? htmlspecialchars($payout['description']) : '' ?>"
                                                           placeholder="Narration" required>
                                                </div>
                                            </div>

                                            <!-- Submit -->
                                            <div class="col-2" style="display:flex;align-items:center;padding-top:9px;">
                                                <?php if ($edit_mode): ?>
                                                    <input type="hidden" name="edit_payout" value="<?= (int)$payout['id'] ?>">
                                                <?php else: ?>
                                                    <input type="hidden" name="add_payout" value="1">
                                                <?php endif; ?>
                                                <button type="submit" class="btn btn-primary mr-1"
                                                        onclick="this.disabled=true; this.innerText='Submitting…'; this.form.submit();">
                                                    <?= $edit_mode ? 'Update' : 'Submit' ?>
                                                </button>
                                                <?php if ($edit_mode): ?>
                                                    <a href="receive_payout.php" class="btn btn-secondary">Cancel</a>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── LIST TABLE (hidden in edit mode) ── -->
                    <?php if (!$edit_mode): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Payouts</h4>
                                </div>
                                <div class="card-datatable">
                                    <table class="payout-datatable table" style="width:100%">
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
                                        <?php $sn = 0; foreach ($payouts as $p): $sn++; ?>
                                            <tr>
                                                <td><?= $sn ?></td>
                                                <td><?= htmlspecialchars($p['account_name'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($p['description']) ?></td>
                                                <td><?= number_format((float)$p['amount'], 2) ?></td>
                                                <td><?= date('Y-m-d', strtotime($p['datetime'])) ?></td>
                                                <td class="action-btns">
                                                    <a href="?edit=<?= (int)$p['id'] ?>"
                                                       class="btn btn-primary btn-sm waves-effect waves-float waves-light">Edit</a>
                                                    <a href="?delete=<?= (int)$p['id'] ?>"
                                                       class="btn btn-danger btn-sm waves-effect waves-float waves-light"
                                                       onclick="return confirm('Are you sure you want to delete this payout?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </section>
            </div>
        </div>
    </div>
    <!-- END: Content -->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- Vendor JS (single jQuery load) -->
    <script src="app-assets/vendors/js/vendors.min.js"></script>
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Theme JS -->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>

    <script>
        $(function () {
            // Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace({ width: 14, height: 14 });
            }

            // Select2 init
            $('.select2').select2();

            // DataTable init (only when table exists)
            if ($('.payout-datatable').length) {
                $('.payout-datatable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: 5 } // disable sort on Action column
                    ]
                });
            }
        });
    </script>
         <script src="footer.js"></script>
</body>
</html>
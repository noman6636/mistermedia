<?php
require_once "inc/config.php";
require_once "inc/functions.php";

// ── Auth checks ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
if (!in_array(4, $permissions_allow)) {
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("Location: index.php");
    exit();
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function validStatus(int $s): bool { return in_array($s, [1, 2, 100]); }
function validType(int $t): bool   { return in_array($t, [1, 2]); }

// ── ADD PAYMENT ───────────────────────────────────────────────────────────────
if (isset($_POST['add_payment'])) {
    $account_id  = (int)   $_POST['account_id'];
    $amount      = (float) $_POST['amount'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $sent_to     = $conn->real_escape_string(trim($_POST['sent_to']));
    $status      = (int)   $_POST['status'];
    $type        = (int)   $_POST['type'];
    $date        = date('Y-m-d H:i:s', strtotime($_POST['date']));

    if ($account_id > 0 && $amount > 0 && validStatus($status) && validType($type)) {
        $stmt = $conn->prepare("INSERT INTO app_payments (account_id, amount, datetime, description, sent_to, type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idsssii", $account_id, $amount, $date, $description, $sent_to, $type, $status);
        $stmt->execute();
        $stmt->close();
        addSystemLog($conn, 'PAYMENT ADDED', "Payment received in account id ($account_id) with amount ($amount)", "");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payment added successfully.</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Invalid data provided.</div></div>';
    }
    header("Location: receive_payment.php");
    exit();
}

// ── EDIT PAYMENT ──────────────────────────────────────────────────────────────
if (isset($_POST['edit_payment'])) {
    $id          = (int)   $_POST['edit_payment'];
    $account_id  = (int)   $_POST['account_id'];
    $amount      = (float) $_POST['amount'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $sent_to     = $conn->real_escape_string(trim($_POST['sent_to']));
    $status      = (int)   $_POST['status'];
    $type        = (int)   $_POST['type'];
    $date        = date('Y-m-d H:i:s', strtotime($_POST['date']));

    if ($id > 0 && $account_id > 0 && $amount > 0 && validStatus($status) && validType($type)) {
        $stmt = $conn->prepare("UPDATE app_payments SET account_id=?, amount=?, datetime=?, description=?, sent_to=?, type=?, status=? WHERE id=?");
        $stmt->bind_param("idsssiis", $account_id, $amount, $date, $description, $sent_to, $type, $status, $id);
        // Fix bind: id is int
        $stmt->close();
        // Redo with correct types
        $stmt = $conn->prepare("UPDATE app_payments SET account_id=?, amount=?, datetime=?, description=?, sent_to=?, type=?, status=? WHERE id=?");
        $stmt->bind_param("idsssiii", $account_id, $amount, $date, $description, $sent_to, $type, $status, $id);
        $stmt->execute();
        $stmt->close();
        addSystemLog($conn, 'PAYMENT UPDATED', "Payment updated in account id ($account_id) with amount ($amount)", $id);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payment updated successfully.</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Invalid data provided.</div></div>';
    }
    header("Location: receive_payment.php");
    exit();
}

// ── DELETE PAYMENT ────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    if ($delId > 0) {
        $stmt = $conn->prepare("SELECT type FROM app_payments WHERE id = ?");
        $stmt->bind_param("i", $delId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && (int)$row['type'] === 2) {
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Entry cannot be deleted. Contact Backend Engineer.</div></div>';
        } else {
            $stmt = $conn->prepare("DELETE FROM app_payments WHERE id = ?");
            $stmt->bind_param("i", $delId);
            $stmt->execute();
            $stmt->close();
            addSystemLog($conn, 'PAYMENT DELETED', "Payment with id ($delId) has been deleted.", $delId);
            $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Payment deleted successfully.</div></div>';
        }
    }
    header("Location: receive_payment.php");
    exit();
}

// ── FETCH EDIT ROW ────────────────────────────────────────────────────────────
$payment   = null;
$edit_mode = false;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt    = $conn->prepare("SELECT * FROM app_payments WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($payment) $edit_mode = true;
}

// ── FETCH ACCOUNTS (once) ─────────────────────────────────────────────────────
$accounts = [];
$res = $conn->query("SELECT id, account_name FROM app_accounts ORDER BY account_name ASC");
while ($row = $res->fetch_assoc()) $accounts[] = $row;

// ── FETCH SENT_TO SUGGESTIONS (once) ─────────────────────────────────────────
$sent_to_list = [];
$res2 = $conn->query("SELECT DISTINCT sent_to FROM app_payments WHERE sent_to != '' ORDER BY sent_to ASC");
while ($row = $res2->fetch_assoc()) $sent_to_list[] = $row['sent_to'];

// ── STATUS FILTER & FETCH PAYMENTS (single JOIN query) ────────────────────────
$filter_status = isset($_GET['status']) ? (string)$_GET['status'] : 'all';
$allowed_statuses = ['1' => 1, '2' => 2, '100' => 100];

if (isset($allowed_statuses[$filter_status])) {
    $fs   = (int) $filter_status;
    $stmt = $conn->prepare("SELECT p.*, a.account_name FROM app_payments p LEFT JOIN app_accounts a ON a.id = p.account_id WHERE p.status = ? ORDER BY p.id DESC");
    $stmt->bind_param("i", $fs);
    $stmt->execute();
    $payments_result = $stmt->get_result();
    $stmt->close();
} else {
    $payments_result = $conn->query("SELECT p.*, a.account_name FROM app_payments p LEFT JOIN app_accounts a ON a.id = p.account_id ORDER BY p.id DESC");
}
$payments = [];
while ($row = $payments_result->fetch_assoc()) $payments[] = $row;

// ── Status label helper ───────────────────────────────────────────────────────
function statusLabel(int $s): string {
    return match($s) { 1 => 'Requested', 2 => 'Transferred', 100 => 'Completed', default => '—' };
}
function typeLabel(int $t): string {
    return match($t) { 1 => 'Payment', 2 => 'Profit', default => '—' };
}
function rowBg(int $s): string {
    return match($s) { 1 => 'background:#fbe3e3;', 2 => 'background:beige;', default => '' };
}
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Receive Payments | D-Orders</title>

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

    <!-- DataTables + Buttons (one CDN source, consistent version) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.1/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap4.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ── Utilities ── */
        .select2-selection__arrow { display: none; }
        .wrap-text { word-break: break-word; max-width: 180px; }
        .card-datatable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .action-btns a { margin-bottom: 3px; }

        /* ── Card header with filter ── */
        .card-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .card-header .status-filter { display: flex; align-items: center; gap: 8px; }
        .card-header .status-filter label { margin: 0; font-weight: 500; white-space: nowrap; }
        .card-header .status-filter select { min-width: 140px; }

        /* ────────────────────────────────────────────
           MEDIA QUERIES
        ──────────────────────────────────────────── */

        /* XS – phones < 576px */
        @media (max-width: 575.98px) {
            body { font-size: 13px; }
            .content-wrapper { padding: 0.6rem !important; }
            .card-body { padding: 0.9rem !important; }
            .breadcrumb { font-size: 0.78rem; }

            /* Form: all columns full-width */
            .payment-form .col-4,
            .payment-form .col-3,
            .payment-form .col-2 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            .payment-form .col-2 {
                padding-top: 0 !important;
                align-items: flex-start !important;
            }
            .payment-form button[type="submit"],
            .payment-form a.btn-secondary { width: 100%; margin-bottom: 4px; }

            /* Table */
            .card-datatable table { min-width: 650px; font-size: 0.78rem; }

            /* Action buttons */
            .action-btns { display: flex; flex-direction: column; gap: 3px; }
            .action-btns a { width: 100%; text-align: center; }

            /* Header filter */
            .card-header { flex-direction: column; align-items: stretch; }
            .card-header .status-filter { flex-direction: column; align-items: stretch; }
            .card-header .status-filter select { width: 100%; }

            /* DT buttons */
            .dt-buttons { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
            .dt-button { font-size: 11px !important; padding: 4px 8px !important; }
        }

        /* SM – phones 576px–767px */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .payment-form .col-4 { flex: 0 0 50%; max-width: 50%; }
            .payment-form .col-3 { flex: 0 0 50%; max-width: 50%; }
            .payment-form .col-2 {
                flex: 0 0 100%; max-width: 100%;
                padding-top: 0 !important;
            }
            .payment-form button[type="submit"] { width: 48%; }
            .action-btns { display: flex; flex-wrap: wrap; gap: 4px; }
            .card-datatable table { min-width: 600px; font-size: 0.82rem; }
        }

        /* MD – tablets 768px–991px */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .payment-form .col-4 { flex: 0 0 33.333%; max-width: 33.333%; }
            .payment-form .col-3 { flex: 0 0 33.333%; max-width: 33.333%; }
            .payment-form .col-2 { flex: 0 0 20%; max-width: 20%; }
            .card-datatable table { font-size: 0.85rem; }
        }

        /* LG – 992px–1199px */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .payment-form .col-3 { flex: 0 0 25%; max-width: 25%; }
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
                                    <li class="breadcrumb-item active">Receive Payments</li>
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
                            <div class="card">
                                <div class="card-body">
                                    <?php echo flash_msg(); ?>

                                    <form action="" method="post" autocomplete="on" class="payment-form">
                                        <div class="row">

                                            <!-- Account -->
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="account_id">Account</label>
                                                    <select name="account_id" class="form-control select2" id="account_id" required>
                                                        <?php foreach ($accounts as $acc): ?>
                                                            <option value="<?= (int)$acc['id'] ?>"
                                                                <?= ($edit_mode && $acc['id'] == $payment['account_id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($acc['account_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Date -->
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="date">Date</label>
                                                    <input type="date" class="form-control" id="date" name="date"
                                                           value="<?= $edit_mode ? date('Y-m-d', strtotime($payment['datetime'])) : date('Y-m-d') ?>"
                                                           required>
                                                </div>
                                            </div>

                                            <!-- Amount -->
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="amount">Amount</label>
                                                    <input type="number" class="form-control" id="amount" name="amount"
                                                           value="<?= $edit_mode ? htmlspecialchars($payment['amount']) : '' ?>"
                                                           placeholder="0.00" step="0.01" min="0.01" required>
                                                </div>
                                            </div>

                                            <!-- Narration -->
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="narration">Narration</label>
                                                    <input type="text" class="form-control" id="narration" name="description"
                                                           value="<?= $edit_mode ? htmlspecialchars($payment['description']) : '' ?>"
                                                           placeholder="Narration" required>
                                                </div>
                                            </div>

                                            <!-- Sent To -->
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="sent_to">Sent To</label>
                                                    <input type="text" class="form-control" list="sent_to_list" id="sent_to" name="sent_to"
                                                           value="<?= $edit_mode ? htmlspecialchars($payment['sent_to']) : '' ?>"
                                                           placeholder="Sent To" required>
                                                    <datalist id="sent_to_list">
                                                        <?php foreach ($sent_to_list as $st): ?>
                                                            <option value="<?= htmlspecialchars($st) ?>">
                                                        <?php endforeach; ?>
                                                    </datalist>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select name="status" class="form-control select2" id="status" required>
                                                        <option value="1"   <?= ($edit_mode && $payment['status'] == 1)   ? 'selected' : '' ?>>Requested</option>
                                                        <option value="2"   <?= ($edit_mode && $payment['status'] == 2)   ? 'selected' : '' ?>>Transferred</option>
                                                        <option value="100" <?= ($edit_mode && $payment['status'] == 100) ? 'selected' : '' ?>>Completed</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Type -->
                                            <div class="col-2">
                                                <div class="form-group">
                                                    <label for="type">Type</label>
                                                    <select name="type" class="form-control select2" id="type" required>
                                                        <option value="1" <?= ($edit_mode && $payment['type'] == 1) ? 'selected' : '' ?>>Payment</option>
                                                        <option value="2" <?= ($edit_mode && $payment['type'] == 2) ? 'selected' : '' ?>>Profit</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Submit -->
                                            <div class="col-2" style="display:flex;align-items:center;padding-top:9px;gap:6px;flex-wrap:wrap;">
                                                <?php if ($edit_mode): ?>
                                                    <input type="hidden" name="edit_payment" value="<?= (int)$payment['id'] ?>">
                                                <?php else: ?>
                                                    <input type="hidden" name="add_payment" value="1">
                                                <?php endif; ?>
                                                <button type="submit" class="btn btn-primary"
                                                        onclick="this.disabled=true; this.innerText='Submitting…'; this.form.submit();">
                                                    <?= $edit_mode ? 'Update' : 'Submit' ?>
                                                </button>
                                                <?php if ($edit_mode): ?>
                                                    <a href="receive_payment.php" class="btn btn-secondary">Cancel</a>
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
                                    <h4 class="card-title mb-0">List of Payments</h4>
                                    <div class="status-filter">
                                        <label for="statusFilter">Status</label>
                                        <select id="statusFilter" class="form-control"
                                                onchange="window.location.href='receive_payment.php?status='+this.value">
                                            <option value="all"<?= $filter_status === 'all' ? ' selected' : '' ?>>All</option>
                                            <option value="1"  <?= $filter_status === '1'   ? ' selected' : '' ?>>Requested</option>
                                            <option value="2"  <?= $filter_status === '2'   ? ' selected' : '' ?>>Transferred</option>
                                            <option value="100"<?= $filter_status === '100' ? ' selected' : '' ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-datatable">
                                    <table class="payments-table table" id="paymentsTable" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Account</th>
                                                <th>Narration</th>
                                                <th>Sent To</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $sn = 0; foreach ($payments as $p): $sn++; ?>
                                            <tr style="<?= rowBg((int)$p['status']) ?>">
                                                <td><?= $sn ?></td>
                                                <td class="wrap-text"><?= htmlspecialchars($p['account_name'] ?? '—') ?></td>
                                                <td class="wrap-text"><?= htmlspecialchars($p['description']) ?></td>
                                                <td><?= htmlspecialchars($p['sent_to']) ?></td>
                                                <td><?= number_format((float)$p['amount'], 2) ?></td>
                                                <td><?= typeLabel((int)$p['type']) ?></td>
                                                <td><?= statusLabel((int)$p['status']) ?></td>
                                                <td><?= date('Y-m-d', strtotime($p['datetime'])) ?></td>
                                                <td class="action-btns">
                                                    <a href="?edit=<?= (int)$p['id'] ?>"
                                                       class="btn btn-primary btn-sm waves-effect waves-float waves-light">Edit</a>
                                                    <a href="?delete=<?= (int)$p['id'] ?>"
                                                       class="btn btn-danger btn-sm waves-effect waves-float waves-light"
                                                       onclick="return confirm('Are you sure you want to delete this payment?');">Delete</a>
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

    <!-- ── Vendor JS (single jQuery, no duplicates) ── -->
    <script src="app-assets/vendors/js/vendors.min.js"></script>
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>

    <!-- DataTables + Buttons (one CDN source) -->
    <script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Theme JS -->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>

    <!-- Footer JS -->
    <script src="footer.js"></script>

    <script>
        $(function () {
            // Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace({ width: 14, height: 14 });
            }

            // Select2
            $('.select2').select2();

            // DataTable (only when table exists)
            if ($('#paymentsTable').length) {
                $('#paymentsTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    dom: 'Bfrtip',
                    buttons: [
                        { extend: 'copy',  className: 'btn btn-sm btn-secondary' },
                        { extend: 'csv',   className: 'btn btn-sm btn-secondary' },
                        { extend: 'excel', className: 'btn btn-sm btn-secondary' },
                        { extend: 'print', className: 'btn btn-sm btn-secondary' }
                    ],
                    order: [[0, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: 8 } // Action column
                    ]
                });
            }
        });
    </script>
</body>
</html>
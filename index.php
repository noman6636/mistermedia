<?php

require_once "inc/config.php";
require_once "inc/functions.php";

if (!isset($_SESSION['admin_id'])) {
    $conn->close();
    header("location: login.php");
    exit();
}

// Preload data
// First, get the raw data from database
$result = $conn->query("SELECT value FROM app_settings WHERE name = 'dashboard_data'");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Decode the JSON string into an array
    $dashboardData = json_decode($row['value'], true);

    // Access the values safely with null coalescing
    $newOrders = $dashboardData['newOrders'] ?? 0;
    $shippedOrders = $dashboardData['shippedOrders'] ?? 0;
    $archivedOrders = $dashboardData['archivedOrders'] ?? 0;
} else {
    // Fallback if no row is found
    $newOrders = 0;
    $shippedOrders = 0;
    $archivedOrders = 0;
}

 $now = date('Y-m-d');
$accountsCount = $conn->query("SELECT COUNT(*) FROM app_accounts WHERE deleted = '0'")->fetch_row()[0];
$expiredAccounts = $conn->query("SELECT * FROM app_accounts where deleted = 0 && active = 1 && auth_token != '' && (DATE(token_expire) < '$now' or IsTokenInvalid = '1')");
// $expiredAccounts = $conn->query("SELECT * FROM app_accounts WHERE deleted = 0 AND active = 1 AND auth_token != '' AND IsTokenInvalid = '1'");
//   echo '$expiredAccounts: <pre>' .print_r($expiredAccounts,true). '</pre>'; die;
// $newOrdersDirectCount = $conn->query("SELECT COUNT(*) FROM app_orders WHERE IsPrinted = '0' && IsArchived = '0'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="D-Orders admin dashboard">
    <title>Dashboard || D-Orders</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" as="style">
    <link rel="preload" href="app-assets/vendors/css/vendors.min.css" as="style">
    <link rel="preload" href="app-assets/css/bootstrap.css" as="style">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600">
    <link rel="stylesheet" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" href="app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" href="app-assets/vendors/css/extensions/toastr.min.css">
    <link rel="stylesheet" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" href="app-assets/css/colors.css">
    <link rel="stylesheet" href="app-assets/css/components.css">
    <link rel="stylesheet" href="app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" href="app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" href="app-assets/css/themes/semi-dark-layout.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" href="app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" href="app-assets/css/plugins/charts/chart-apex.css">
    <link rel="stylesheet" href="app-assets/css/plugins/extensions/ext-component-toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?=time()?>">
    <link rel="stylesheet" href="app-assets/css/jquery.multiselect.css">
    
    <style>
    /* ===============================
   GLOBAL RESPONSIVE IMPROVEMENTS
================================= */

/* Small devices (phones ≤576px) */
@media (max-width: 576px) {

    .dashboard-card {
        height: auto;
        padding: 20px;
        margin-bottom: 15px;
    }

    .dashboard-card h2 {
        font-size: 14px;
    }

    .dashboard-card h2.value {
        font-size: 20px;
    }

    .dashboard-card .icon img {
        width: 48px;
        height: 48px;
    }

    /* Forms spacing */
    .form-group {
        margin-bottom: 10px;
    }

    /* Buttons full width */
    .btn {
        width: 100%;
    }

    /* Fix row spacing */
    .row > [class*="col-"] {
        margin-bottom: 15px;
    }

    /* Card padding */
    .card-body {
        padding: 15px;
    }

    /* Select2 fix */
    .select2-container {
        width: 100% !important;
    }
}


/* Medium devices (tablets 577px - 991px) */
@media (min-width: 577px) and (max-width: 991px) {

    .dashboard-card {
        height: auto;
        padding: 20px;
    }

    .dashboard-card h2 {
        font-size: 15px;
    }

    .dashboard-card h2.value {
        font-size: 22px;
    }

    .dashboard-card .icon img {
        width: 56px;
        height: 56px;
    }

    /* Adjust grid spacing */
    .row > [class*="col-"] {
        margin-bottom: 20px;
    }

    .btn {
        width: auto;
        display: block;
        margin: 5px auto;
    }
}


/* Large devices (992px - 1200px) */
@media (min-width: 992px) and (max-width: 1200px) {

    .dashboard-card {
        height: 180px;
        padding: 25px;
    }

    .dashboard-card h2 {
        font-size: 15px;
    }

    .dashboard-card h2.value {
        font-size: 22px;
    }
}


/* Extra large devices (≥1201px) */
@media (min-width: 1201px) {

    .dashboard-card {
        height: 200px;
    }
}


/* ===============================
   MULTISELECT & SELECT2 FIXES
================================= */

@media (max-width: 768px) {

    .ms-options-wrap,
    .ms-options-wrap > button {
        width: 100% !important;
    }

    .ms-options-wrap > .ms-options {
        max-height: 250px;
        overflow-y: auto;
    }
}


/* ===============================
   TABLE / OVERFLOW FIX
================================= */

@media (max-width: 768px) {

    .table-responsive {
        overflow-x: auto;
    }
}


/* ===============================
   HEADER / NAV FIX (if breaking)
================================= */

@media (max-width: 768px) {

    .header-navbar {
        flex-wrap: wrap;
    }
}
/* ===============================
   FORCE SIDEBAR RESPONSIVE FIX
================================= */

@media (max-width: 768px) {

    /* Hide sidebar by default */
    .main-menu,
    .horizontal-menu-wrapper {
        transform: translateX(-100%) !important;
        position: fixed !important;
        width: 260px !important;
        z-index: 9999;
    }

    /* Show when open (if your theme toggles class) */
    .main-menu.menu-open {
        transform: translateX(0) !important;
    }

    /* Fix content full width */
    .app-content {
        margin-left: 0 !important;
        width: 100% !important;
    }

    .content-wrapper {
        padding: 10px !important;
    }
}
        .select2-selection__arrow { display: none; }
        .ms-options-wrap>.ms-options>ul>li.optgroup .label:hover {
            cursor: pointer;
            text-decoration: underline;
        }
      
        .dashboard-card {
            width: 100%; 
            height: 200px;
            border-radius: 15px;
            padding: 27px 40px; 
            cursor: pointer;
        }
        .dashboard-card h2 {
            text-align: center;
            color: white;
            font-size: 16px;
        }
        .dashboard-card h2.value {
            font-weight: bold;
            font-size: 24px;
            margin-top: 10px;
        }
        .dashboard-card .icon {
            text-align: center;
            margin-bottom: 15px;
        }
        .dashboard-card .icon img {
            width: 64px;
            height: 64px;
        }
    </style>
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><h4 class="text-shadow">Dashboard</h4></a></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!in_array(26, $permissions_allow)): ?>
                <div class="content-body">
                    <section id="row-grouping-datatable">
                        <?php echo flash_msg(); ?>
                        <div class="alert alert-danger" role="alert">
                            <div class="alert-body">Access denied to this page.</div>
                        </div>
                    </section>
                </div>
            <?php else: ?>
                <div class="content-body">
                    <section id="row-grouping-datatable">
                        <?php echo flash_msg(); ?>
                        <?php while ($expire = $expiredAccounts->fetch_assoc()):
                            // echo '$expire: <pre>' .print_r($expire,true). '</pre>'; 
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <div class="alert-body">
                                    Token for ebay account username <b><?= htmlspecialchars($expire['account_username']) ?></b> has been expired. 
                                    <a href="connect_ebay.php?renewToken=<?= $expire['id'] ?>">Click to Renew</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="row">
                            <!-- Dashboard Cards -->
                            <div class="col-sm-3 col-12">
                                <div class="dashboard-card" style="background: linear-gradient(to right, #D8B7DD, #A2DFF7);" onclick="window.location.href='new_orders.php'">
                                    <div class="icon">
                                        <img src="https://cdn-icons-png.flaticon.com/128/891/891407.png" loading="lazy" alt="Cart" width="64" height="64">
                                    </div>
                                    <h2>New Orders</h2>
                                    <h2 class="value"><?= $newOrders ?></h2>
                                </div>
                            </div>
                            
                            <div class="col-sm-3 col-12">
                                <div class="dashboard-card" style="background: linear-gradient(to right, #FFB3AB, #FFDDC1);" onclick="window.location.href='all_orders.php'">
                                    <div class="icon">
                                        <img src="https://cdn-icons-png.flaticon.com/128/10130/10130795.png" loading="lazy" alt="Tracking" width="64" height="64">
                                    </div>
                                    <h2>Shipped Orders</h2>
                                    <h2 class="value"><?= $shippedOrders ?></h2>
                                </div>
                            </div>
                            
                            <div class="col-sm-3 col-12">
                                <div class="dashboard-card" style="background: linear-gradient(to right, #A2C2E8, #F4B6C2);" onclick="window.location.href='archived_orders.php'">
                                    <div class="icon">
                                        <img src="https://cdn-icons-png.flaticon.com/128/1481/1481625.png" loading="lazy" alt="Clipboard" width="64" height="64">
                                    </div>
                                    <h2>Archived Orders</h2>
                                    <h2 class="value"><?= $archivedOrders ?></h2>
                                </div>
                            </div>
                            
                            <div class="col-sm-3 col-12">
                                <div class="dashboard-card" style="background: linear-gradient(to right, #A8E6CF, #FFD3B6);" onclick="window.location.href='accounts.php'">
                                    <div class="icon">
                                        <img src="https://cdn-icons-png.flaticon.com/128/681/681392.png" loading="lazy" alt="Group" width="64" height="64">
                                    </div>
                                    <h2>Total Accounts</h2>
                                    <h2 class="value"><?= $accountsCount ?></h2>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="page-account-settings" style="margin-top:20px">
                        <div class="row">
                            <!-- Accounts Orders List -->
                            <div class="col-md-6">
                                <div class="card custom-card card-hover">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Accounts Orders List</h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="ordersForm" onsubmit="return false;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="account_id_o">Account</label>
                                                        <select name="account_id" class="form-control select2" id="account_id_o" required>
                                                            <option value="all">All Accounts</option>
                                                            <option value="all1">All Ebay Accounts</option>
                                                            <option value="all4">All Amazon Accounts</option>
                                                            <option value="all2">All Static Accounts</option>
                                                            <?php 
                                                            $accounts = $conn->query("SELECT id, account_name FROM app_accounts WHERE deleted = 0 ORDER BY account_name ASC");
                                                            while ($account = $accounts->fetch_assoc()): ?>
                                                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="frmdate_o">From Date</label>
                                                        <input type="date" class="form-control" id="frmdate_o" name="frmdate" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="todate_o">To Date</label>
                                                        <input type="date" class="form-control" id="todate_o" name="todate" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="type_o">Select Type</label>
                                                        <select name="type_o" class="form-control" id="type_o" required>
                                                            <option value="1">Account Wise</option>
                                                            <option value="2">Date Wise</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 text-center mt-2">
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateOrders(1);">Generate View</button>
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateOrders(2);">Generate Csv</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Account Ledger -->
                            <div class="col-md-6">
                                <div class="card custom-card card-hover">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Account Ledger</h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="ledgerForm" onsubmit="return false;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="account_id_l">Account</label>
                                                        <select name="account_id" class="form-control select2" id="account_id_l" required>
                                                            <?php 
                                                            $accounts = $conn->query("SELECT id, account_name FROM app_accounts WHERE deleted = 0 ORDER BY account_name ASC");
                                                            while ($account = $accounts->fetch_assoc()): ?>
                                                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="frmdate_l">From Date</label>
                                                        <input type="date" class="form-control" id="frmdate_l" name="frmdate_l" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="todate_l">To Date</label>
                                                        <input type="date" class="form-control" id="todate_l" name="todate_l" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="type_l">Select Type</label>
                                                        <select name="type_l" class="form-control" id="type_l" required>
                                                            <option value="1">Payments</option>
                                                            <option value="2">Profit</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 text-center mt-2">
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateLedger(1);">Generate View</button>
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateLedger(2);">Generate Csv</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Items Orders List -->
                            <div class="col-md-6">
                                <div class="card custom-card card-hover">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Items Orders List</h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="itemOrdersForm" onsubmit="return false;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="item_id_o">Item</label>
                                                        <select name="item_id" class="form-control" multiple id="item_id_o">
                                                            <?php 
                                                            $items = $conn->query("SELECT id, sku FROM app_items WHERE deleted = 0 ORDER BY sku ASC");
                                                            while ($item = $items->fetch_assoc()): ?>
                                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['sku']) ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="account_id_io">Account</label>
                                                        <select name="account_id[]" multiple id="account_id_io">
                                                            <optgroup label="Ebay Accounts">
                                                                <?php 
                                                                $accounts = $conn->query("SELECT id, account_name FROM app_accounts WHERE account_type = 1 AND deleted = 0 ORDER BY account_name ASC");
                                                                while ($account = $accounts->fetch_assoc()): ?>
                                                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                                                                <?php endwhile; ?>
                                                            </optgroup>
                                                            <optgroup label="Amazon Accounts">
                                                                <?php 
                                                                $accounts = $conn->query("SELECT id, account_name FROM app_accounts WHERE account_type = 4 AND deleted = 0 ORDER BY account_name ASC");
                                                                while ($account = $accounts->fetch_assoc()): ?>
                                                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                                                                <?php endwhile; ?>
                                                            </optgroup>
                                                            <optgroup label="Static Accounts">
                                                                <?php 
                                                                $accounts = $conn->query("SELECT id, account_name FROM app_accounts WHERE account_type IN (2,3) AND deleted = 0 ORDER BY account_name ASC");
                                                                while ($account = $accounts->fetch_assoc()): ?>
                                                                    <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                                                                <?php endwhile; ?>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="frmdate_io">From Date</label>
                                                        <input type="date" class="form-control" id="frmdate_io" name="frmdate" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="todate_io">To Date</label>
                                                        <input type="date" class="form-control" id="todate_io" name="todate" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label for="type_io">Select Type</label>
                                                        <select name="type_io" class="form-control" id="type_io" required>
                                                            <option value="1">Item Wise</option>
                                                            <option value="2">Account Wise</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 text-center mt-2">
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateItemOrders(1);">Generate View</button>
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateItemOrders(2);">Generate Csv</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sent To Ledger -->
                            <div class="col-md-6">
                                <div class="card custom-card card-hover">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Sent To Ledger</h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="sentLedgerForm" onsubmit="return false;">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="account_id_sl">Account</label>
                                                        <select name="account_id_sl" class="form-control select2" id="account_id_sl" required>
                                                            <?php 
                                                            $accounts = $conn->query("SELECT sent_to FROM app_payments GROUP BY sent_to ORDER BY sent_to ASC");
                                                            while ($account = $accounts->fetch_assoc()): ?>
                                                                <option value="<?= htmlspecialchars($account['sent_to']) ?>"><?= htmlspecialchars($account['sent_to']) ?></option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="frmdate_sl">From Date</label>
                                                        <input type="date" class="form-control" id="frmdate_sl" name="frmdate_sl" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="todate_sl">To Date</label>
                                                        <input type="date" class="form-control" id="todate_sl" name="todate_sl" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-12 text-center mt-2">
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateSentLedger(1);">Generate View</button>
                                                    <button type="button" class="btn btn-primary mr-1 mt-1" onclick="generateSentLedger(2);">Generate Csv</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="printableArea" style="display:none"></div>
    
    <?php $conn->close(); ?>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="app-assets/vendors/js/vendors.min.js"></script>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>
    <script src="app-assets/js/jquery.multiselect.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize components
            $('.select2').select2();
            
            $('#account_id_io').multiselect({
                columns: 1,
                placeholder: 'Select Accounts',
                search: true,
                selectAll: true
            });
            
            $('#item_id_o').multiselect({
                columns: 1,
                placeholder: 'Select Items',
                search: true,
                selectAll: true
            });
            
            // Check all functionality
            $("#selectall").click(function() {
                $(".case").prop("checked", this.checked);
            });
            
            $(".case").click(function() {
                $("#selectall").prop("checked", $(".case").length === $(".case:checked").length);
            });
        });
        
        function generateSentLedger(type) {
            const account_id = $("#account_id_sl").val();
            const frmdate = $("#frmdate_sl").val();
            const todate = $("#todate_sl").val();
            
            if (type === 1) {
                window.open(`printSentLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}`, 
                           "Generate Sent Ledger", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
            } else {
                window.location.href = `csvSentLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}`;
            }
        }
        
        function generateLedger(typeview) {
            const account_id = $("#account_id_l").val();
            const frmdate = $("#frmdate_l").val();
            const todate = $("#todate_l").val();
            const type = $("#type_l").val();
            
            if (typeview === 1) {
                window.open(`printLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`, 
                           "Generate Ledger", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
            } else {
                window.location.href = `csvLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`;
            }
        }
        
        function generateOrders(typeview) {
            const account_id = $("#account_id_o").val();
            const frmdate = $("#frmdate_o").val();
            const todate = $("#todate_o").val();
            const type = $("#type_o").val();
            
            if (typeview === 1) {
                window.open(`printOrders.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`, 
                           "Order List", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
            } else {
                window.location.href = `csvOrders.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`;
            }
        }
        
        function generateItemOrders(typeview) {
            const item_id = $("#item_id_o").val();
            const account_id = $("#account_id_io").val();
            const frmdate = $("#frmdate_io").val();
            const todate = $("#todate_io").val();
            const type = $("#type_io").val();
            
            if (typeview === 1) {
                const param = {
                    'item_id': item_id,
                    'frmdate': frmdate,
                    'todate': todate,
                    'account_id': account_id,
                    'type': type
                };
                OpenWindowWithPost('printItemLedger.php', "width=1024,height=720,toolbar=0,menubar=0,location=0", "Items Order List", param);
            } else {
                window.location.href = `csvItemLedger.php?item_id=${item_id}&frmdate=${frmdate}&todate=${todate}&account_id=${account_id}&type=${type}`;
            }
        }
        
        function print() {
            const values = $('input[name="case[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            if (values.length === 0) {
                alert("Please select atleast one order.");
                return;
            }
            
            $.ajax({
                url: "getPrintData.php?value=" + values,
                type: "POST",
                data: { values: values },
                success: function(response) {
                    $("#printableArea").html(response);
                    const table = document.getElementById("ptbl");
                    TableToExcel.convert(table, {
                        name: `export.xlsx`,
                        sheet: { name: 'Sheet 1' }
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus, errorThrown);
                    new Noty({
                        text: "Some error while registration.",
                        timeout: 15000,
                        layout: 'bottomRight',
                        theme: "metroui",
                        type: 'warning',
                        killer: true
                    }).show();
                }
            });
        }
        
        function OpenWindowWithPost(url, windowoption, name, params) {
            const form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", url);
            form.setAttribute("target", name);
            
            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }
            }
            
            document.body.appendChild(form);
            window.open("post.htm", name, windowoption);
            form.submit();
            document.body.removeChild(form);
        }
     
    </script>
      <script src="footer.js"></script>
</body>
</html>
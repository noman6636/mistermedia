<style>
    .main-menu.menu-light .navigation li a {
        color: #000000;
    }

    table th,
    .table td {
        padding: 0.72rem 10px !important;
        font-size: 10px;
        vertical-align: middle !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {

        padding: 7px 4px;
        border-right: 0px;

    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove>span {

        display: none;

    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover,
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
        background-color: transparent;
        color: transparent;
    }

    .ms-options ul {
        list-style-type: disclosure-closed;
    }

    .ms-options-wrap>.ms-options>.ms-selectall.global {
        margin: 8px 12px;
        text-transform: capitalize;
        color: black;
        font-weight: 700;
    }
    /* =========================================
   HEADER RESPONSIVE FIX
========================================= */

@media (max-width: 768px) {

    /* Navbar container spacing */
    .navbar-container {
        padding: 10px !important;
        flex-wrap: nowrap;
    }

    /* Username hide on mobile */
    .user-nav {
        display: none !important;
    }

    /* Avatar resize */
    .avatar img {
        width: 32px !important;
        height: 32px !important;
    }

    /* Fix dropdown position */
    .dropdown-menu {
        right: 10px !important;
        left: auto !important;
    }

    /* Hamburger always visible */
    .menu-toggle {
        display: block !important;
    }
}


/* =========================================
   SIDEBAR RESPONSIVE FIX (MAIN ISSUE)
========================================= */

@media (max-width: 768px) {

    .main-menu {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 260px !important;
        height: 100%;
        transform: translateX(-100%);
        transition: all 0.3s ease;
        z-index: 1050;
    }

    /* When menu is open */
    .main-menu.menu-open {
        transform: translateX(0) !important;
    }

    /* Overlay effect */
    body.menu-open::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }

    /* Fix content full width */
    .app-content {
        margin-left: 0 !important;
        width: 100% !important;
    }

    /* Fix header width */
    .header-navbar {
        width: 100% !important;
    }
}


/* =========================================
   TABLE + UI FIXES
========================================= */

@media (max-width: 768px) {

    table th,
    .table td {
        font-size: 11px !important;
        padding: 8px !important;
    }

    /* Prevent overflow */
    .table-responsive {
        overflow-x: auto;
    }

    /* Select2 full width */
    .select2-container {
        width: 100% !important;
    }

    /* Multiselect fix */
    .ms-options-wrap,
    .ms-options-wrap > button {
        width: 100% !important;
    }
}


/* =========================================
   GLOBAL FIX (VERY IMPORTANT)
========================================= */

html, body {
    overflow-x: hidden;
}
@media (max-width: 768px) {

    .main-menu {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 260px !important;
        height: 100%;
        background: #fff;
        transform: translateX(-100%);
        transition: all 0.3s ease-in-out;
        z-index: 1050;
    }

    /* When open */
    .main-menu.menu-open {
        transform: translateX(0);
    }

    /* Overlay */
    body.menu-open::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }

    /* Fix content */
    .app-content {
        margin-left: 0 !important;
    }
}
.menu-toggle i {
    width: 24px !important;
    height: 24px !important;
    display: inline-block !important;
    color: #000 !important;
}
</style>
<?php $filename = basename($_SERVER['PHP_SELF']);
?>
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light">
    <div class="card-shadow br-15 navbar-container d-flex content">
        <div class="bookmark-wrapper d-flex align-items-center">
            <ul class="nav navbar-nav">
                <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="fas fa-bars"></i></a></li>
            </ul>
        </div>

        <ul class="nav navbar-nav align-items-center ml-auto">
            <li class="nav-item dropdown dropdown-user"><a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="user-nav d-sm-flex d-none"><span class="user-name font-weight-bolder"><?php echo $admin['username']; ?></span><span class="user-status">Admin</span></div><span class="avatar"><img class="round" src="app-assets/images/portrait/small/avatar-s-11.jpg" alt="Admin" height="40" width="40"><span class="avatar-status-online"></span></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                    <a class="dropdown-item" href="profile.php"><i class="mr-50" data-feather="user"></i> Profile</a>
                    <?php if ($admin['role_id'] == 1) { ?>
                        <a class="dropdown-item" href="activity_logs.php"><i class="mr-50" data-feather="user"></i> System Logs</a>
                    <?php } ?>
                    <a class="dropdown-item" href="logout.php"><i class="mr-50" data-feather="log-out"></i> Logout</a>

                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- END: Header-->

<style>
    .nav-colored {
        color: #ffffff !important;
        background: #002b49 !important;
        border-radius: 6px;
    }

    .main-margin-nav {
        margin: 5px 0px;
    }
</style>
<!-- BEGIN: Main Menu-->
<div class="card-shadow br-15 main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/d-orders_logo.png" style="width: 190px;"/>
                </a>
            </li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content" style="margin-top:20px">
       
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="index.php"><i data-feather="home"></i><span class="menu-title text-truncate" data-i18n="Dashboard">Dashboard</span></a>
            </li>
            <?php
            // print_r($permissions_allow); 
            ?>
            <?php if (in_array(27, $permissions_allow) || in_array(30, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="statistics.php?items=1"><i data-feather="home"></i><span class="menu-title text-truncate" data-i18n="Dashboard">Statistics</span></a>
                </li>

            <?php } ?>
            <?php if (in_array(1, $permissions_allow) || in_array(2, $permissions_allow) || in_array(3, $permissions_allow) || in_array(4, $permissions_allow) || in_array(34, $permissions_allow) || in_array(35, $permissions_allow) || in_array(37, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='users'></i><span class="menu-title text-truncate" data-i18n="Users">Accounts</span></a>
                    <ul class="menu-content">

                        <?php if (in_array(1, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="add_account.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add Account">Add Account</span></a></li>
                        <?php } ?>
                        <?php if (in_array(2, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="accounts.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Accounts">Manage Accounts</span></a></li>
                        <?php } ?>
                        <?php if (in_array(35, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="accounts_details.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Accounts Details">Accounts Details</span></a></li>
                        <?php } ?>
                        <?php if (in_array(2, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="statistics.php?payables=1"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Payable Accounts">Payable Accounts</span></a></li>
                        <?php } ?>
                        <?php if (in_array(3, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="generate_invoice.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Generate Invoice">Generate Invoice</span></a></li>
                        <?php } ?>
                        <?php if (in_array(4, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="receive_payment.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Receive Payment">Receive Payment</span></a></li>
                        <?php } ?>
                        <?php if (in_array(34, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="receive_payout.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Receive Payouts">Receive Payout</span></a></li>
                        <?php } ?>
                        <?php if (in_array(34, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="auto_payouts.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Auto Payouts">Auto Payouts</span></a></li>
                        <?php } ?>
                        <?php if (in_array(37, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="manage_messages.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Messages">Manage Messages</span></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php if (in_array(5, $permissions_allow) || in_array(6, $permissions_allow) || in_array(7, $permissions_allow) || in_array(8, $permissions_allow) || in_array(9, $permissions_allow) || in_array(10, $permissions_allow) || in_array(11, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='list'></i><span class="menu-title text-truncate" data-i18n="Users">Orders</span></a>
                    <ul class="menu-content">
                        <?php if (in_array(28, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="create_sale_order.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="New Orders">Create Order</span></a></li>
                        <?php } ?>
                        <?php if (in_array(5, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="new_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="New Orders">New Orders</span></a></li>
                        <?php } ?>
                        <?php if (in_array(6, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="all_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="All Orders">All Orders</span></a></li>
                        <?php } ?>
                        <?php if (in_array(7, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="advance_search.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Advance Search">Advance Search</span></a></li>
                        <?php } ?>
                        <?php if (in_array(8, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="reship_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Reship Orders">Reship Orders</span></a></li>
                        <?php } ?>
                        <?php if (in_array(9, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="archived_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Archived Orders">Archived Orders</span></a></li>
                        <?php } ?>
                        <?php if (in_array(10, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="dispatch_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Dispatch Orders">Dispatch Orders</span></a></li>
                        <?php } ?>
                        <?php if (in_array(11, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="generate_csv.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Generate CSV">Generate CSV</span></a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>

            <?php if (in_array(14, $permissions_allow) || in_array(15, $permissions_allow) || in_array(16, $permissions_allow) || in_array(17, $permissions_allow) || in_array(18, $permissions_allow) || in_array(19, $permissions_allow)  || in_array(36, $permissions_allow)   || in_array(31, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='box'></i><span class="menu-title text-truncate" data-i18n="tems & Stock">Items & Stock</span></a>
                    <ul class="menu-content">
                        <?php if (in_array(14, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="add_item.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add Item">Add Item</span></a></li>
                        <?php } ?>
                        <?php if (in_array(15, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="item_list.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Item List">Item List</span></a></li>
                        <?php } ?>
                        <?php if (in_array(31, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="item_aio.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="AIO Items">AIO Items</span></a></li>
                        <?php } ?>
                        <?php if (in_array(16, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="add_stock.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add Stock">Add Stock</span></a></li>
                        <?php } ?>
                        <?php if (in_array(17, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="stock_list.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Stock List">Stock List</span></a></li>
                        <?php } ?>
                        <?php if (in_array(18, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="create_package.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Create Package">Create Package</span></a></li>
                        <?php } ?>
                        <?php if (in_array(19, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="manage_packages.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Packages">Manage Packages</span></a></li>
                        <?php } ?>
                        <?php if (in_array(36, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="exchange_rate.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Exchange Rate">Exchange Rate</span></a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>
            <?php if (in_array(20, $permissions_allow) || in_array(21, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='truck'></i><span class="menu-title text-truncate" data-i18n="Suppliers">Suppliers</span></a>
                    <ul class="menu-content">
                        <?php if (in_array(20, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="add_supplier.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add Supplier">Add Supplier</span></a></li>
                        <?php } ?>
                        <?php if (in_array(21, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="suppliers.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Suppliers List">Suppliers List</span></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (in_array(22, $permissions_allow) || in_array(23, $permissions_allow) || in_array(24, $permissions_allow) || in_array(25, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='shopping-cart'></i><span class="menu-title text-truncate" data-i18n="Purchase">Purchase & Orders</span></a>
                    <ul class="menu-content">
                        <?php if (in_array(22, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="create_purchase.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Create Purchase">Create Purchase</span></a></li>
                        <?php } ?>
                        <?php if (in_array(23, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="manage_purchase.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Purchase">Manage Purchase</span></a></li>
                        <?php } ?>
                        <?php if (in_array(24, $permissions_allow)) { ?>
                            <!--<li><a class="d-flex align-items-center" href="create_purchase_order.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Create Order">Create Order</span></a></li>-->
                        <?php } ?>
                        <?php if (in_array(25, $permissions_allow)) { ?>
                            <!--<li><a class="d-flex align-items-center" href="manage_purchase_orders.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Orders">Manage Orders</span></a></li>-->
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (in_array(32, $permissions_allow) || in_array(33, $permissions_allow)) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='file-text'></i><span class="menu-title text-truncate" data-i18n="Invoices">Invoices</span></a>
                    <ul class="menu-content">
                        <?php if (in_array(33, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="create_invoice.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Create Invoice">Create Invoice</span></a></li>
                        <?php } ?>
                        <?php if (in_array(32, $permissions_allow)) { ?>
                            <li><a class="d-flex align-items-center" href="manage_invoices.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Manage Invoices">Manage Invoices</span></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if ($admin['role_id'] == 1) { ?>
                <li class="nav-item main-margin-nav"><a class="d-flex align-items-center nav-colored" href="#"><i data-feather='user'></i><span class="menu-title text-truncate" data-i18n="User Management">User Management</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="add_user.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add User">Add User</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="users.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="User List">User List</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="add_role.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Add Role">Add Role</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="roles.php"><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="Role List">Role List</span></a>
                        </li>
                    </ul>
                </li>
            <?php } ?>
            
                <?php if (in_array(38, $permissions_allow)) { ?>
                 <li><a class="d-flex align-items-center nav-colored" href="master_sheet.php"><i data-feather='user'></i><span class="menu-title text-truncate" data-i18n="User Management">Master sheet</span></a>
                 </li>
                 <?php } ?>

        <p class="text-center mt-2">
            <span>&copy; Developed by <a href="/" target="_blank">waseem abbass</a></span>
        </p>
    </div>
</div>
<?php
$files = array('statistics.php', 'accounts.php', 'receive_payment.php', 'item_list.php', 'stock_list.php', 'manage_packages.php', 'suppliers.php', 'manage_purchase.php', 'users.php', 'roles.php', 'generate_csv.php');

if (in_array($filename, $files)) { ?>
    <style>
        table th,
        .table td {

            font-size: 14px !important;
            font-weight: 500;
            /*color: black;*/
        }
    </style>
<?php } ?>
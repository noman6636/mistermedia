<?php
require_once "inc/config.php";
require_once "inc/functions.php";
$LIMIT = 10;
$time_start = microtime(true);

// Check if admin_id is set in session
if (empty($_SESSION['admin_id'])) {
    header("location: login.php");
    exit();
}

// Check if permissions_allow exists and has the required values
if(!in_array(38, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if (isset($_GET['delete'])) {
    $deleteId = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
    if ($deleteId) {
        $conn->query("UPDATE app_items SET deleted = 1 WHERE id = '{$deleteId}'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been deleted</div></div>';
        header("location: statistics.php?items=1");
        exit();
    }
}

if (isset($_GET['recover'])) {
    $recoverId = filter_var($_GET['recover'], FILTER_SANITIZE_NUMBER_INT);
    if ($recoverId) {
        $conn->query("UPDATE app_items SET deleted = 0 WHERE id = '{$recoverId}'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been recovered</div></div>';
        header("location: statistics.php?items=1&deleted=1");
        exit();
    }
}

if (isset($_POST['update_master_sheet'])) {
    $item_id = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);
    $team_name = $conn->real_escape_string($_POST['team_name']);
    $days_sale = filter_var($_POST['days_sale_10'], FILTER_SANITIZE_NUMBER_INT);
    $telly_date = $conn->real_escape_string($_POST['telly_date']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    $color     = $conn->real_escape_string($_POST['color'] ?? '');
    $color_label = $conn->real_escape_string(trim($_POST['color_label'] ?? ''));

    $check_query = "SELECT id FROM app_master_sheet WHERE item_id = '$item_id' LIMIT 1";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        $update_query = "UPDATE app_master_sheet SET 
            team_name = '$team_name',
            days_sale_10 = '$days_sale',
            telly_date = '$telly_date',
            remarks = '$remarks',
            color = '$color',
            color_label = '$color_label'
            WHERE item_id = '$item_id'";
        $conn->query($update_query);
    } else {
        $insert_query = "INSERT INTO app_master_sheet (item_id, team_name, days_sale_10, telly_date, remarks, color, color_label) 
                        VALUES ('$item_id', '$team_name', '$days_sale', '$telly_date', '$remarks', '$color', '$color_label')";
        $conn->query($insert_query);
    }
    
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Master sheet entry has been updated successfully</div></div>';
    header("location: master_sheet.php");
    exit();
}

if (isset($_GET['delete_entry'])) {
    $entry_id = filter_var($_GET['delete_entry'], FILTER_SANITIZE_NUMBER_INT);
    if ($entry_id) {
        $conn->query("UPDATE app_master_sheet SET deleted_master_sheet = 1 WHERE id = '{$entry_id}'");
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been deleted</div></div>';
        header("location: master_sheet.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Master Sheet || IConnect</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">

    <style>
        #master_sheet_id { font-size: 16px; }
        #master_sheet_id thead th { font-size: 17px; font-weight: 600; }
        #master_sheet_id tbody td { font-size: 16px; }
        #master_sheet_id tfoot td { font-size: 18px; font-weight: bold; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { font-size: 15px; }
        .dataTables_wrapper select,
        .dataTables_wrapper input[type="search"] { font-size: 15px; padding: 6px; }

        /* Color filter dropdown styles */
        #colorFilterBox { min-width: 240px; max-height: 320px; overflow-y: auto; }
        #colorFilterBox .color-option { 
            display: flex; 
            align-items: center; 
            padding: 8px 12px; 
            cursor: pointer; 
            border-radius: 4px;
            margin: 2px 0;
        }
        #colorFilterBox .color-option:hover { background: #f5f5f5; }
        #colorFilterBox .color-option input[type="checkbox"] { 
            margin-right: 10px; 
            cursor: pointer;
            width: 16px;
            height: 16px;
        }
        #colorFilterBox .color-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            flex-shrink: 0;
            border: 1px solid rgba(0,0,0,0.2);
        }
        #colorFilterBox .color-label-text {
            font-size: 14px;
            font-weight: 500;
        }
        #colorFilterBox .color-sub-label {
            font-size: 11px;
            color: #888;
            margin-left: 4px;
        }
        .filter-divider { border-top: 1px solid #eee; margin: 4px 0; }
        .filter-clear-btn {
            width: 100%;
            text-align: center;
            padding: 6px;
            font-size: 13px;
            color: #dc3545;
            cursor: pointer;
            background: none;
            border: none;
        }
        .filter-clear-btn:hover { text-decoration: underline; }
    </style>
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>

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
                                    <li class="breadcrumb-item active"><h4 class="text-shadow-none">Master Sheet</h4></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <?php 
                if (isset($_SESSION['flash'])) {
                    echo $_SESSION['flash'];
                    unset($_SESSION['flash']);
                }
                
                if (isset($_GET['edit'])) {
                    $edit_id = filter_var($_GET['edit'], FILTER_SANITIZE_NUMBER_INT);
                    $edit_item_query = "SELECT ai.*, ms.id as master_sheet_id, ms.team_name, ms.days_sale_10, ms.telly_date, ms.remarks, ms.color, ms.color_label
                                       FROM app_items ai 
                                       LEFT JOIN app_master_sheet ms ON ai.id = ms.item_id 
                                       WHERE ai.id = '$edit_id' 
                                       LIMIT 1";
                    $edit_item_result = $conn->query($edit_item_query);
                    
                    if ($edit_item_result && $edit_item_result->num_rows > 0) {
                        $edit_item = $edit_item_result->fetch_assoc();
                        $remain_stock = 0;
                        $cost_price = 0;
                        if (isset($edit_item['item_type']) && $edit_item['item_type'] == 1) {
                            $dataStat = json_decode($edit_item['statistics'] ?? '{}', true) ?? array();
                            $remain_stock = $dataStat['remain_stock'] ?? 0;
                            $cost_price = $dataStat['cost_price'] ?? 0;
                        }
                ?>
                        <section id="edit-item-section" style="margin-top:20px; margin-bottom:30px;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header border-bottom">
                                            <h4 class="card-title">Edit Master Sheet Entry: <?= htmlspecialchars($edit_item['sku']); ?></h4>
                                            <a href="master_sheet.php" class="btn btn-secondary btn-sm">Back to List</a>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="POST">
                                                <input type="hidden" name="item_id" value="<?= $edit_item['id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>SKU</label>
                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($edit_item['sku']); ?>" readonly style="background-color:#e9ecef;cursor:not-allowed;">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Available Stock</label>
                                                            <input type="text" class="form-control" value="<?= $remain_stock; ?>" readonly style="background-color:#e9ecef;cursor:not-allowed;">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Cost</label>
                                                            <input type="text" class="form-control" value="<?= $cost_price; ?>" readonly style="background-color:#e9ecef;cursor:not-allowed;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Team Name</label>
                                                            <input type="text" class="form-control" name="team_name" value="<?= htmlspecialchars($edit_item['team_name'] ?? ''); ?>" placeholder="Enter team name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>10 Days Sale</label>
                                                            <input type="number" class="form-control" name="days_sale_10" value="<?= htmlspecialchars($edit_item['days_sale_10'] ?? ''); ?>" min="0" placeholder="Enter 10 days sale">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Tally Date</label>
                                                            <?php 
                                                            $telly_date_value = '';
                                                            if (!empty($edit_item['telly_date']) && $edit_item['telly_date'] != '0000-00-00' && $edit_item['telly_date'] != '0000-00-00 00:00:00') {
                                                                $telly_date_value = date('Y-m-d', strtotime($edit_item['telly_date']));
                                                            }
                                                            ?>
                                                            <input type="date" class="form-control" name="telly_date" value="<?= $telly_date_value; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Color</label>
                                                            <select class="form-control select-color" name="color">
                                                                <option value="">Select Color</option>
                                                                <option value="Red"    data-color="#dc3545" <?= ($edit_item['color'] == 'Red')    ? 'selected' : ''; ?>>Red</option>
                                                                <option value="Blue"   data-color="#0d6efd" <?= ($edit_item['color'] == 'Blue')   ? 'selected' : ''; ?>>Blue</option>
                                                                <option value="Green"  data-color="#198754" <?= ($edit_item['color'] == 'Green')  ? 'selected' : ''; ?>>Green</option>
                                                                <option value="Yellow" data-color="#ffc107" <?= ($edit_item['color'] == 'Yellow') ? 'selected' : ''; ?>>Yellow</option>
                                                                <option value="Orange" data-color="#fd7e14" <?= ($edit_item['color'] == 'Orange') ? 'selected' : ''; ?>>Orange</option>
                                                                <option value="Pink"   data-color="#d63384" <?= ($edit_item['color'] == 'Pink')   ? 'selected' : ''; ?>>Pink</option>
                                                                <option value="Black"  data-color="#000000" <?= ($edit_item['color'] == 'Black')  ? 'selected' : ''; ?>>Black</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Custom Label</label>
                                                            <input type="text" class="form-control" name="color_label" value="<?= htmlspecialchars($edit_item['color_label'] ?? ''); ?>" placeholder="e.g. Good, Urgent, Priority">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Remarks</label>
                                                            <textarea class="form-control" name="remarks" rows="3" placeholder="Enter remarks"><?= htmlspecialchars($edit_item['remarks'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button type="submit" name="update_master_sheet" class="btn btn-primary">
                                                            <i class="feather icon-save"></i> Update Master Sheet Entry
                                                        </button>
                                                        <a href="master_sheet.php" class="btn btn-secondary">
                                                            <i class="feather icon-x"></i> Cancel
                                                        </a>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                <?php 
                    } else {
                        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Item not found</div></div>';
                        header("location: master_sheet.php");
                        exit();
                    }
                }
                ?>

                <section id="row-grouping-datatable" style="margin-top:20px">
                    <div class="row" style="margin-top:10px;">
                        <div class="col-12">
                            <form action="" id="allordersdata" method="POST">
                                <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title">Inventory Stock Report</h4>
                                    </div>

                                    <?php
                                    $reorders = array();
                                    $lowstock = array();
                                    $outofstock = array();
                                    $itemsList = array();

                                    $color_filter = $_GET['filter_color'] ?? '';
                                    $color_condition = '';
                                    if (!empty($color_filter)) {
                                        $color_filter = $conn->real_escape_string($color_filter);
                                        $color_condition = " AND ms.color = '$color_filter' ";
                                    }

                                    $query = "
                                        SELECT 
                                            ai.*,
                                            ms.id AS master_sheet_id,
                                            ms.team_name,
                                            ms.days_sale_10,
                                            ms.telly_date,
                                            ms.remarks,
                                            ms.color,
                                            ms.color_label,
                                            IFNULL(ms.deleted_master_sheet, 0) AS deleted_master_sheet,
                                            IFNULL(od.last_10_days_orders, 0) AS last_10_days_orders
                                        FROM app_items ai
                                        LEFT JOIN app_master_sheet ms ON ai.id = ms.item_id
                                        LEFT JOIN (
                                            SELECT sku, SUM(qty) AS last_10_days_orders
                                            FROM (
                                                SELECT oi.sku, SUM(oi.QuantityPurchased) AS qty
                                                FROM app_order_items oi
                                                JOIN app_orders o ON o.OrderID = oi.OrderID
                                                WHERE o.CreatedTime >= DATE_SUB(CURDATE(), INTERVAL 9 DAY)
                                                  AND o.CreatedTime < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                                  AND o.IsArchived = 0
                                                GROUP BY oi.sku
                                                UNION ALL
                                                SELECT ai.sku, SUM(oi.QuantityPurchased * pi.qty) AS qty
                                                FROM app_order_items oi
                                                JOIN app_orders o ON o.OrderID = oi.OrderID
                                                JOIN app_packages p ON p.sku = oi.SKU
                                                JOIN app_packages_items pi ON pi.package_id = p.id
                                                JOIN app_items ai ON ai.id = pi.item_id
                                                WHERE o.CreatedTime >= DATE_SUB(CURDATE(), INTERVAL 9 DAY)
                                                  AND o.CreatedTime < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                                  AND o.IsArchived = 0
                                                GROUP BY ai.sku
                                            ) t
                                            GROUP BY sku
                                        ) od ON od.sku = ai.sku
                                        WHERE ai.deleted = " . (isset($_GET['deleted']) ? 1 : 0) . "
                                        $color_condition
                                        ORDER BY ai.sku ASC
                                    ";

                                    $items = $conn->query($query);
                                    $sn = 0;
                                    $today = date('Y-m-d');
                                    $total_qty = 0;

                                    // PHP color map
                                    $colorMap = [
                                        'Red'    => '#dc3545',
                                        'Blue'   => '#0d6efd',
                                        'Green'  => '#198754',
                                        'Yellow' => '#ffc107',
                                        'Orange' => '#fd7e14',
                                        'Pink'   => '#d63384',
                                        'Black'  => '#000000',
                                    ];

                                    // =====================================================
                                    // KEY FIX: Build color options for JS filter FROM PHP
                                    // We collect unique color+label combos here in PHP
                                    // then pass them to JS as JSON — no DOM scanning needed
                                    // =====================================================
                                    $jsColorOptions = []; // for filter dropdown

                                    if ($items) {
                                        while ($item = $items->fetch_assoc()) {
                                            $itemsArray = array();
                                            $remain_stock = 0;
                                            $upcoming_stock = 0;
                                            $cost_price = 0;

                                            if (isset($item['item_type']) && $item['item_type'] == 1) {
                                                $dataStat = json_decode($item['statistics'] ?? '{}', true) ?? array();
                                                $remain_stock = $dataStat['remain_stock'] ?? 0;
                                                $upcoming_stock = $dataStat['upcoming_stock'] ?? 0;
                                                $cost_price = $dataStat['cost_price'] ?? 0;
                                            }

                                            $sn++;
                                            $itemsArray['id']                  = $item['id'] ?? 0;
                                            $itemsArray['master_sheet_id']     = $item['master_sheet_id'] ?? null;
                                            $itemsArray['sku']                 = $item['sku'] ?? '';
                                            $itemsArray['name']                = $item['name'] ?? '';
                                            $itemsArray['price']               = $cost_price ?? 0;
                                            $itemsArray['deleted']             = $item['deleted'] ?? 0;
                                            $itemsArray['remain_stock']        = $remain_stock;
                                            $itemsArray['reference']           = $item['reference'];
                                            $itemsArray['team_name']           = $item['team_name'] ?? '';
                                            $itemsArray['days_sale_10']        = $item['last_10_days_orders'] ?? '';
                                            $itemsArray['telly_date']          = $item['telly_date'] ?? '';
                                            $itemsArray['remarks']             = $item['remarks'] ?? '';
                                            $itemsArray['color']               = $item['color'] ?? '';
                                            $itemsArray['color_label']         = $item['color_label'] ?? '';
                                            $itemsArray['deleted_master_sheet']= $item['deleted_master_sheet'] ?? 0;

                                            // Collect unique color options for JS filter
                                            $cName = $item['color'] ?? '';
                                            if (!empty($cName) && !isset($jsColorOptions[$cName])) {
                                                $cLabel = !empty($item['color_label']) ? $item['color_label'] : '';
                                                $jsColorOptions[$cName] = [
                                                    'color' => $cName,
                                                    'label' => $cLabel,
                                                    'hex'   => $colorMap[$cName] ?? '#999999'
                                                ];
                                            } elseif (!empty($cName) && !empty($item['color_label']) && empty($jsColorOptions[$cName]['label'])) {
                                                // Update label if we found one later
                                                $jsColorOptions[$cName]['label'] = $item['color_label'];
                                            }

                                            $itemsList[] = $itemsArray;
                                        }
                                    }
                                    ?>

                                    <div class="card-datatable pr-2 pl-2">
                                        <table id="master_sheet_id" class="dt-row-grouping-t table">
                                            <thead>
                                                <tr>
                                                    <th>S#</th>
                                                    <th>SKU</th>
                                                    <th>Available Stock</th>
                                                    <th>Cost</th>
                                                    <th>Total Cost</th>
                                                    <th>Team Name</th>
                                                    <th>10 Days Sale</th>
                                                    <th>Tally Date</th>
                                                    <th>Remarks</th>
                                                    <th>Color</th><!-- col index 9 - hidden, raw color name for filtering -->
                                                    <th>Deleted</th><!-- col index 10 - hidden -->
                                                    <?php if (in_array(38, $permissions_allow)) { ?><th width="10%">Action</th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $sn = 0;
                                            $total_qty = 0;
                                            $total_amount = 0;
                                            foreach ($itemsList as $item) {
                                                $sn++;
                                                $total_qty    += $item['remain_stock'];
                                                $total_amount += ($item['remain_stock'] * $item['price']);

                                                $colorName  = $item['color'] ?? '';
                                                $colorLabel = !empty($item['color_label']) ? $item['color_label'] : $colorName;
                                                $colorCode  = $colorMap[$colorName] ?? '';

                                                $bg_color   = $colorCode;
                                                $text_color = '';
                                                if (!empty($bg_color)) {
                                                    $text_color = in_array($colorName, ['Yellow','Orange','Pink']) ? '#000' : '#fff';
                                                }
                                            ?>
                                                <tr style="<?= !empty($bg_color) ? 'background-color:' . $bg_color . ';color:' . $text_color . ';' : ''; ?>">
                                                    <td><?= $sn; ?></td>
                                                    <td><?= htmlspecialchars($item['sku']); ?></td>
                                                    <td><?= $item['remain_stock']; ?></td>
                                                    <td><?= $item['price']; ?></td>
                                                    <td><?= $item['price'] * $item['remain_stock']; ?></td>
                                                    <td><?= htmlspecialchars($item['team_name']); ?></td>
                                                    <td><?= $item['days_sale_10']; ?></td>
                                                    <td><?= (!empty($item['telly_date']) && $item['telly_date'] !== '0000-00-00' && $item['telly_date'] !== '0000-00-00 00:00:00') ? date('d M Y', strtotime($item['telly_date'])) : ''; ?></td>
                                                    <td><?= htmlspecialchars($item['remarks']); ?></td>

                                                    <!-- col 9: raw color name (hidden) — used by JS filter -->
                                                    <td><?= htmlspecialchars($colorName); ?></td>

                                                    <!-- col 10: deleted status (hidden) -->
                                                    <td><?= htmlspecialchars($item['deleted_master_sheet']); ?></td>

                                                    <?php if (in_array(27, $permissions_allow)) { ?>
                                                    <td style="display:flex;">
                                                        <a href="master_sheet.php?edit=<?= $item['id']; ?>" class="btn btn-primary btn-sm" style="margin-right:5px;" title="Edit Entry">
                                                            <img src="https://cdn-icons-png.flaticon.com/128/1827/1827933.png" loading="lazy" alt="Edit" width="20" height="20">
                                                        </a>
                                                        <?php } ?>
                                                        <?php if ($item['deleted_master_sheet'] == 1) { ?>
                                                            <button class="btn btn-success btn-sm revert-btn" data-id="<?= $item['master_sheet_id']; ?>" title="Revert">⟲</button>
                                                        <?php } else { ?>
                                                            <a href="?delete_entry=<?= $item['master_sheet_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this master sheet entry?');" title="Delete Entry">
                                                                <img src="https://cdn-icons-png.flaticon.com/128/3405/3405244.png" loading="lazy" alt="Delete" width="21" height="21">
                                                            </a>
                                                        <?php } ?>
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

            </div>
        </div>
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <script src="app-assets/vendors/js/vendors.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <?php
    // =====================================================
    // OUTPUT PHP COLOR DATA AS JSON FOR JAVASCRIPT
    // This is the KEY FIX - PHP sends data to JS directly
    // No DOM scanning, no template literal issues
    // =====================================================
    $jsColorOptionsJson = json_encode(array_values($jsColorOptions));
    ?>
    <script>
    // Color options built from PHP — guaranteed correct data
    var PHP_COLOR_OPTIONS = <?php echo $jsColorOptionsJson; ?>;

    $(document).ready(function() {

        var showDeletedOnly = false;

        // ✅ Initialize DataTable
        var table = $('#master_sheet_id').DataTable({
            pageLength: 20,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            order: [[0, "asc"]],
            dom: '<"row mb-2"<"col-md-4"B><"col-md-4 text-center custom-controls"><"col-md-4"f>>' +
                 '<"row"<"col-md-6"l><"col-md-6 text-right">>' +
                 'rtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Download CSV',
                    className: 'btn btn-success btn-sm',
                    filename: function() {
                        var now  = new Date();
                        var date = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
                        var time = String(now.getHours()).padStart(2,'0') + '-' + String(now.getMinutes()).padStart(2,'0') + '-' + String(now.getSeconds()).padStart(2,'0');
                        return 'mastersheet_' + date + '_' + time;
                    },
                    exportOptions: { columns: ':visible:not(:last-child)' }
                }
            ],
            columnDefs: [
                { targets: 9,  visible: false }, // raw color name — used for filter logic
                { targets: 10, visible: false }  // deleted status
            ]
        });

        // ✅ Build color filter dropdown from PHP_COLOR_OPTIONS (pure string concat, no backticks)
        function buildColorFilterDropdown() {
            var html = '';

            if (PHP_COLOR_OPTIONS.length === 0) {
                html += '<div style="padding:10px;color:#888;font-size:13px;">No color data</div>';
            } else {
                for (var i = 0; i < PHP_COLOR_OPTIONS.length; i++) {
                    var opt   = PHP_COLOR_OPTIONS[i];
                    var label = opt.label ? opt.label : opt.color;
                    var subLabel = opt.label ? ' (' + opt.color + ')' : '';

                    html += '<label class="color-option">';
                    html +=   '<input type="checkbox" value="' + opt.color + '">';
                    html +=   '<span class="color-dot" style="background:' + opt.hex + ';"></span>';
                    html +=   '<span class="color-label-text">' + label + '</span>';
                    html +=   '<span class="color-sub-label">' + subLabel + '</span>';
                    html += '</label>';
                }
            }

            html += '<div class="filter-divider"></div>';
            html += '<button class="filter-clear-btn" id="clearColorFilter">Clear Filter</button>';

            return html;
        }

        // ✅ Insert controls - pure string concat
        var controlsHtml = '';
        controlsHtml += '<div class="d-flex justify-content-center align-items-center">';
        controlsHtml +=   '<a href="javascript:void(0)" id="toggleDeleted" class="btn btn-danger btn-sm mr-2">Show Deleted SKUs</a>';
        controlsHtml +=   '<div class="dropdown">';
        controlsHtml +=     '<button class="btn btn-outline-primary dropdown-toggle btn-sm" id="colorFilterBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Filter by Color</button>';
        controlsHtml +=     '<div class="dropdown-menu p-2" id="colorFilterBox">';
        controlsHtml +=       buildColorFilterDropdown();
        controlsHtml +=     '</div>';
        controlsHtml +=   '</div>';
        controlsHtml += '</div>';

        $('.custom-controls').html(controlsHtml);

        // ✅ Custom DataTable search filter
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var selectedColors = [];
            $('#colorFilterBox input[type="checkbox"]:checked').each(function() {
                selectedColors.push($(this).val());
            });

            var rowColor      = (data[9]  || '').toString().trim();
            var deletedStatus = (data[10] || '').toString().trim();

            // Handle deleted toggle
            if (showDeletedOnly) {
                if (deletedStatus !== '1') return false;
            } else {
                if (deletedStatus === '1') return false;
            }

            // Handle color filter
            if (selectedColors.length === 0) return true;
            return selectedColors.indexOf(rowColor) !== -1;
        });

        // ✅ Toggle deleted rows
        $(document).on('click', '#toggleDeleted', function(e) {
            e.preventDefault();
            showDeletedOnly = !showDeletedOnly;
            $(this).text(showDeletedOnly ? 'Show All SKUs' : 'Show Deleted SKUs')
                   .toggleClass('btn-danger btn-success');
            table.draw();
        });

        // ✅ Checkbox change — redraw + update button text
        $(document).on('change', '#colorFilterBox input[type="checkbox"]', function() {
            var count = $('#colorFilterBox input[type="checkbox"]:checked').length;
            $('#colorFilterBtn').text(count > 0 ? 'Color Filter (' + count + ')' : 'Filter by Color');
            table.draw();
        });

        // ✅ Clear filter button
        $(document).on('click', '#clearColorFilter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#colorFilterBox input[type="checkbox"]').prop('checked', false);
            $('#colorFilterBtn').text('Filter by Color');
            table.draw();
        });

        // ✅ Prevent dropdown closing when clicking inside it
        $(document).on('click', '#colorFilterBox', function(e) {
            e.stopPropagation();
        });

        setTimeout(function() { table.draw(); }, 100);

        // ✅ Revert button
        $(document).on('click', '.revert-btn', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (!confirm('Revert this entry?')) return;
            $.ajax({
                url: 'revert_master_sheet.php',
                type: 'POST',
                data: { id: id },
                success: function() { location.reload(); },
                error: function(xhr) { console.log(xhr.responseText); }
            });
        });

        // ✅ Select2 for color dropdown in edit form
        function formatColorSelect(option) {
            if (!option.id) return option.text;
            var color = $(option.element).data('color');
            return $('<span><span style="display:inline-block;width:14px;height:14px;border-radius:50%;margin-right:8px;background-color:' + color + ';"></span>' + option.text + '</span>');
        }
        $('.select-color').select2({
            theme: 'bootstrap-5',
            templateResult: formatColorSelect,
            templateSelection: formatColorSelect,
            escapeMarkup: function(m) { return m; }
        });

    });

    $(window).on('load', function() {
        if (typeof feather !== 'undefined') {
            feather.replace({ width: 14, height: 14 });
        }
    });

    $(document).ready(function() {
        $("#selectall").click(function() {
            $(".case").prop("checked", $("#selectall").prop('checked'));
        });
        $(".case").click(function() {
            $("#selectall").prop("checked", $(".case").length === $(".case:checked").length);
        });
    });
    </script>
      <script src="footer.js"></script>
</body>
</html>
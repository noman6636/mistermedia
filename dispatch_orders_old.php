<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
require_once "inc/Keys.php";
require_once "inc/eBaySession.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manual label dispatching
if (isset($_POST['labeltype'])) {
    $case = $_POST['case'] ?? [];

    if (count($case) < 1) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select at least one order to Dispatch.</div></div>';
        header("location: dispatch_orders.php");
        exit();
    }

    if ($_POST['labeltype'] == 100) {
        $orderIds = [];

        foreach ($case as $order) {
            $orderId = strtok($order, '/');
            if (!empty($orderId)) {
                $stmt = $conn->prepare("UPDATE app_orders SET IsDispatched = '1' WHERE ID = ?");
                $stmt->bind_param("s", $orderId);
                $stmt->execute();
                $stmt->close();
                $orderIds[] = $orderId;
            }
        }

        if (!empty($orderIds)) {
            addSystemLog($conn, 'DISPATCHED ORDERS', "Total " . count($orderIds) . " orders dispatched", implode(', ', $orderIds));
            $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders have been dispatched.</div></div>';
        }

        header("location: dispatch_orders.php");
        exit();
    }
}

// CSV Preview
$csv_preview_data = [];

if (isset($_POST['upload_csv']) && isset($_FILES['csv_file'])) {
    $csvFile = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $orderNumber = trim($data[0] ?? '');
            $trackingId = trim($data[1] ?? '');

            if ($orderNumber && $trackingId) {
                $csv_preview_data[] = [
                    'OrderID' => $orderNumber,
                    'TrackingNumber' => $trackingId
                ];
            }
        }
        fclose($handle);
    }
}

// Dispatch from CSV
if (isset($_POST['dispatch_csv'])) {
    $orders = $_POST['csv_data'] ?? [];
    $dispatched = [];

    foreach ($orders as $entry) {
        $orderNumber = trim($entry['OrderID'] ?? '');
        $trackingId = trim($entry['TrackingNumber'] ?? '');

        if (!$orderNumber || !$trackingId) {
            continue;
        }

        // Fetch order details with account ID
        $stmt = $conn->prepare("SELECT * FROM app_orders WHERE OrderID = ?");
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderRow = $result->fetch_assoc();
        $stmt->close();

        if (!$orderRow || empty($orderRow['ebay_account_id'])) {
            continue;
        }

        // Get account token
        $stmt = $conn->prepare("SELECT * FROM app_accounts WHERE id = ? AND active = '1' AND auth_token != '' AND account_type = '1'");
        $stmt->bind_param("i", $orderRow['ebay_account_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $accountRow = $result->fetch_assoc();
        $stmt->close();

        if (!$accountRow) {
            continue;
        }

        $userToken = $accountRow['auth_token'];
        $shippedTime = gmdate("Y-m-d\TH:i:s.000\Z");

        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
<SetShipmentTrackingInfoRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . htmlspecialchars($userToken) . '</eBayAuthToken>
  </RequesterCredentials>
  <IsPaid>true</IsPaid>
  <IsShipped>true</IsShipped>
  <OrderID>' . htmlspecialchars($orderNumber) . '</OrderID>
  <Shipment>
    <ShipmentTrackingDetails>
      <ShipmentTrackingNumber>' . htmlspecialchars($trackingId) . '</ShipmentTrackingNumber>
      <ShippingCarrierUsed>Royal Mail</ShippingCarrierUsed>
    </ShipmentTrackingDetails>
    <ShippedTime>' . $shippedTime . '</ShippedTime>
  </Shipment>
</SetShipmentTrackingInfoRequest>';

        $verb = 'SetShipmentTrackingInfo';
        $siteID = 3;

        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        $response = simplexml_load_string($responseXml);

        if ($response && $response->Ack == 'Success') {
            addSystemLog($conn, 'EBAY TRACKING', "Tracking updated for $orderNumber", "$trackingId via Royal Mail");

            $stmt = $conn->prepare("UPDATE app_orders SET IsDispatched = '1', TrackingNumber = ? WHERE OrderID = ?");
            $stmt->bind_param("ss", $trackingId, $orderNumber);
            $stmt->execute();
            $stmt->close();

            $dispatched[] = $orderNumber;
        } else {
            $error = isset($response->Errors) ? json_encode($response->Errors) : 'Unknown';
            addSystemLog($conn, 'EBAY TRACKING ERROR', "Failed for $orderNumber", $error);
        }
    }

    if (!empty($dispatched)) {
        addSystemLog($conn, 'DISPATCHED CSV PREVIEW', "Dispatched via CSV preview", implode(", ", $dispatched));
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Successfully dispatched ' . count($dispatched) . ' orders.</div></div>';
    } else {
        $_SESSION['flash'] = '<div class="alert alert-warning" role="alert"><div class="alert-body">No matching orders were updated.</div></div>';
    }

    header("Location: dispatch_orders.php");
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
    <title>Dispatch Orders || D-Orders</title>
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
            font-size: 10px;
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
                                    <li class="breadcrumb-item active">Orders
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
                                <div class="card-datatable">
                                    <?php if (!empty($csv_preview_data)) : ?>
                                    
                                        <div class="card mt-2">
                                            <div class="card-header">
                                                <h4 class="card-title">CSV Preview</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    Total rows in CSV: <strong><?= count($csv_preview_data) ?></strong>
                                                </div>
                                                <form method="post">
                                                    <button type="submit" class="btn btn-success">Submit and Dispatch Orders</button>
                                                    <input type="hidden" name="dispatch_csv" value="1">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Order ID</th>
                                                                <th>Tracking Number</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($csv_preview_data as $entry): ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php echo htmlspecialchars($entry['OrderID']); ?>
                                                                        <input type="hidden" name="csv_data[][OrderID]" value="<?php echo htmlspecialchars($entry['OrderID']); ?>">
                                                                    </td>
                                                                    <td>
                                                                        <?php echo htmlspecialchars($entry['TrackingNumber']); ?>
                                                                        <input type="hidden" name="csv_data[][TrackingNumber]" value="<?php echo htmlspecialchars($entry['TrackingNumber']); ?>">
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </form>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="card-header border-bottom">
                                            <h4 class="card-title">Ready for dispatch <br><span style="font-size: 12px;" id="ocount">Selected Orders : 0</span></h4>
                                            <button type="button" onclick="submitPrint(100)" style="float:right;margin-right:10px" class="btn btn-outline-primary waves-effect">
                                                <i data-feather='archive'></i>
                                                <span>Dispatch</span>
                                            </button>
                                            <form action="" method="post" enctype="multipart/form-data" style="display:inline;" id="csv-upload-form">
                                                <input type="file" name="csv_file" id="csv_file" accept=".csv" style="display:none;" required>
                                                
                                                <button type="button" class="btn btn-outline-primary waves-effect" onclick="triggerCSVUpload()">
                                                    <i data-feather='upload'></i> <span>Upload CSV</span>
                                                </button>
                                            
                                                <!-- Hidden submit button to trigger real upload once file is chosen -->
                                                <button type="submit" name="upload_csv" id="csv_submit_btn" style="display: none;"></button>
                                            </form>
                                            
                                            <script>
                                                function triggerCSVUpload() {
                                                    document.getElementById('csv_file').click();
                                                }
                                            
                                                document.getElementById('csv_file').addEventListener('change', function () {
                                                    if (this.files.length > 0) {
                                                        document.getElementById('csv_submit_btn').click();
                                                    }
                                                });
                                            </script>
        
        
        
                                        </div>
                                        <script>
                                            function submitPrint(val){
                                                document.getElementById("labeltype").value=val;
                                                var form = document.getElementById("allordersdata");
                                                form.submit();
                                            }
                                        </script>
                                        <form action="" id="allordersdata" method="POST">
                                        <input id="labeltype" value="0" type="hidden" name="labeltype" />
                                        <table class="dt-row-grouping table">
                                            <thead>
                                                <tr>
                                                    <th style="width:5%;">Date/Time</th>
                                                    <th style="width:20%;">Account</th>
                                                    <th style="width:5%;">Order #</th>
                                                    <th style="width:40%;">Item Name</th>
                                                    <th style="width:9%;">SKU</th>
                                                    <th style="width:9%;">P</th>
                                                    <th style="width:9%;">
                                                        <select name="sm">
                                                            <option>Royal Mail</option>
                                                            <option>DHL</option>
                                                        </select>
                                                    </th>
                                                    <th style="width:5%;"><input type="checkbox" id="selectall"/></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $query = "SELECT * FROM app_orders WHERE IsDispatched='0' AND IsPrinted = '1' ORDER BY ID DESC";
                                                $orders = $conn->query($query);
                                                
                                                if($orders && $orders->num_rows > 0){
                                                    $sn = 0;
                                                    while($order = $orders->fetch_assoc()){
                                                        $account = [];
                                                        $accountQuery = $conn->query("SELECT * FROM app_accounts WHERE id = '".$conn->real_escape_string($order['AccountID'])."'");
                                                        if($accountQuery){
                                                            $account = $accountQuery->fetch_assoc();
                                                        }
                                                        
                                                        $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '".$conn->real_escape_string($order['OrderID'])."'");
                                                        $sku = '';
                                                        $showItem = '';
                                                        
                                                        if($itemsList){
                                                            while($item = $itemsList->fetch_assoc()){
                                                                $showItem .= htmlspecialchars($item['ItemTitle']).' x '.htmlspecialchars($item['QuantityPurchased']).'<br>';
                                                                $sku .= htmlspecialchars($item['SKU']).'<br>';
                                                            }
                                                        }
                                                        $sn++; 
                                                ?>
                                                        <tr>
                                                            <td><?php echo date('d/M H:i', strtotime($order['CreatedTime'])); ?></td>
                                                            <td><?php echo isset($account['account_name']) ? htmlspecialchars($account['account_name']) : 'N/A'; ?></td>
                                                            <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                                                            <td><?php echo $showItem; ?></td>
                                                            <td><?php echo $sku; ?></td>
                                                            <td><?php echo htmlspecialchars($order['Total']); ?></td>
                                                            <td><input type="checkbox" class="case" name="case[]" value="<?php echo htmlspecialchars($order['ID']); ?>"/></td>
                                                        </tr>
                                                <?php 
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="8" class="text-center">No orders ready for dispatch</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php endif; ?>

                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
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
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>

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
        
        $("#selectall").click(function () {
            var checkAll = $("#selectall").prop('checked');
            $(".case").prop("checked", checkAll);
            updateSelectedCount();
        });

        $(".case").click(function(){
            $("#selectall").prop("checked", $(".case").length == $(".case:checked").length);
            updateSelectedCount();
        });
        
        function updateSelectedCount() {
            var numberChecked = $('input[name="case[]"]:checked').length;
            $("#ocount").text("Selected Orders : " + numberChecked);
        }
        
        // Initialize count on page load
        $(document).ready(function() {
            updateSelectedCount();
        });
        
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
        
        function exportReportToExcel(divName) {
            let table = document.getElementById("ptbl");
            TableToExcel.convert(table, {
                name: `export.xlsx`,
                sheet: {
                    name: 'Sheet 1'
                }
            });
        }
    </script>
</body>
<!-- END: Body-->

</html>
<?php
require_once "inc/config.php";
require_once "inc/functions.php";
require_once('inc/Keys.php');
require_once('inc/eBaySession.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
logMessage("CRON started", 'start');
   function getImportOrdersEbay($conn, $accountRow, $siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $page)
    {

        $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        // $requestXmlBody .= '<OrderIDArray><OrderID>21-07244-05089</OrderID><OrderID>25-07255-74186</OrderID></OrderIDArray>';
        $requestXmlBody .= "<CreateTimeFrom>$CreateTimeFrom</CreateTimeFrom><CreateTimeTo>$CreateTimeTo</CreateTimeTo>";
        $requestXmlBody .= "<Pagination><PageNumber>$page</PageNumber></Pagination>";
        $requestXmlBody .= '<OrderRole>Seller</OrderRole><OrderStatus>Completed</OrderStatus>';
        $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
        $requestXmlBody .= '</GetOrdersRequest>';

        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);

        //send the request and get response
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        if (stristr($responseXml, 'HTTP 404') || $responseXml == '') {
            echo '<p>Error sending request</p><br>' . $accountRow['account_username'];
        } else {
            $res = XML2Array($responseXml);

            if ($res['Ack'] !== 'Success' && $res['Ack'] !== 'Warning') {
                $error = isset($res['Errors']['LongMessage']) ? $res['Errors']['LongMessage'] : 'Unknown error';
                logMessage("eBay API Failure: $error", 'error');
            }

            if ($res['Ack'] == 'Success' || $res['Ack'] == 'Warning') {
                $dd = date('Y-m-d H:i:s');
                $conn->query("update app_accounts set last_get = '$dd', IsTokenInvalid = '0' WHERE id = '{$accountRow['id']}'");

                if ($res['PaginationResult']['TotalNumberOfEntries'] == 1) {
                    $res['OrderArray']['Order'] = array($res['OrderArray']['Order']);
                }

                if ($res['PaginationResult']['TotalNumberOfEntries'] > 0) {
                    // echo json_encode($res['OrderArray']['Order']);
                    foreach ($res['OrderArray']['Order'] as $order) {
                        $OrderID = $order['OrderID'];
                        $check_order = $conn->query("select * from app_orders where OrderID = '$OrderID'");
                        // echo $OrderID.'<br>';
                        if ($check_order->num_rows == 0) {
                            // echo $OrderID.'<br>';
                            $CreatedTime = date('Y-m-d H:i:s', strtotime($order['CreatedTime']));
                            $PaidTime = date('Y-m-d H:i:s', strtotime($order['PaidTime']));
                            if ($order['OrderStatus'] == 'Completed') {
                                $ShippingAddress = $conn->real_escape_string(json_encode($order['ShippingAddress']));
                                $query = "insert into app_orders set ";
                                $query .= "AccountID='$AccountID', ";
                                $query .= "OrderID='$OrderID', ";
                                $query .= "OrderStatus='{$order['OrderStatus']}', ";
                                $query .= "AdjustmentAmount='{$order['AdjustmentAmount']}', ";
                                $query .= "AmountPaid='{$order['AmountPaid']}', ";
                                $query .= "PaymentMethod='{$order['CheckoutStatus']['PaymentMethod']}', ";
                                $query .= "PaymentStatus='{$order['CheckoutStatus']['Status']}', ";
                                $query .= "CreatedTime='$CreatedTime', ";
                                $query .= "Subtotal='{$order['Subtotal']}', ";
                                $query .= "Total='{$order['Total']}', ";
                                $query .= "PostCode='{$order['ShippingAddress']['PostalCode']}', ";

                                $query .= "SellingManagerSalesRecordNumber='{$order['ShippingDetails']['SellingManagerSalesRecordNumber']}', ";
                                $query .= "ShippingAddress='$ShippingAddress', ";
                                $query .= "ShippingService='{$order['ShippingServiceSelected']['ShippingService']}', ";
                                $query .= "ShippingServiceCost='{$order['ShippingServiceSelected']['ShippingServiceCost']}', ";


                                $Condition = $order['TransactionArray']['Transaction']['Item']['ConditionDisplayName'] ?? '';
                                $Condition = $conn->real_escape_string($Condition);
                                $query .= "BuyerUserID='{$order['BuyerUserID']}', ";

                                if (array_key_exists("BuyerCheckoutMessage", $order)) {
                                    $check_out_msg  = removeEmoji($conn->real_escape_string($order['BuyerCheckoutMessage']));
                                    $query .= "BuyerCheckoutMessage='$check_out_msg', ";
                                }

                                if (array_key_exists("ShippedTime", $order)) {
                                    $query .= "ShipmentTrackingNumber='{$order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber']}', ";
                                    $ShippedTime = date('Y-m-d H:i:s', strtotime($order['ShippedTime']));
                                    $query .= "ShippedTime='$ShippedTime', ";
                                    $query .= "IsPrinted='0', ";
                                    $query .= "OrderType='1', ";
                                    $query .= "IsDispatched='0', ";
                                    $query .= "IsRespond='1', ";
                                }

                                $allowInsert = false;
                                if (array_key_exists("Item", $order['TransactionArray']['Transaction'])) {

                                    if (array_key_exists("Variation", $order['TransactionArray']['Transaction'])) {
                                        $ItemTitle = removeEmoji($conn->real_escape_string($order['TransactionArray']['Transaction']['Item']['Title']));
                                        if (!IsSKUBanned($order['TransactionArray']['Transaction']['Variation']['SKU'])) {
                                            $allowInsert = true;
                                            check_add_item($conn, $order['TransactionArray']['Transaction']['Variation']['SKU'], $ItemTitle, $order['AmountPaid']);
                                            $UnitPrice = getPriceFromSKU($conn, $order['TransactionArray']['Transaction']['Variation']['SKU'], $accountRow['price_tag']);
                                            $conn->query("INSERT INTO app_order_items SET OrderID = '$OrderID', ItemID = '{$order['TransactionArray']['Transaction']['Item']['ItemID']}', SKU = '{$order['TransactionArray']['Transaction']['Variation']['SKU']}', ItemTitle = '$ItemTitle', ConditionDisplayName = '$Condition', QuantityPurchased = '{$order['TransactionArray']['Transaction']['QuantityPurchased']}', Price = '$UnitPrice', OrderType='1'");
                                        }
                                    } else {
                                        $ItemTitle = removeEmoji($conn->real_escape_string($order['TransactionArray']['Transaction']['Item']['Title']));

                                        if (!IsSKUBanned($order['TransactionArray']['Transaction']['Item']['SKU'])) {
                                            $allowInsert = true;
                                            check_add_item($conn, $order['TransactionArray']['Transaction']['Item']['SKU'], $ItemTitle, $order['AmountPaid']);
                                            $UnitPrice = getPriceFromSKU($conn, $order['TransactionArray']['Transaction']['Item']['SKU'], $accountRow['price_tag']);
                                            $conn->query("INSERT INTO app_order_items SET OrderID = '$OrderID', ItemID = '{$order['TransactionArray']['Transaction']['Item']['ItemID']}', SKU = '{$order['TransactionArray']['Transaction']['Item']['SKU']}', ItemTitle = '$ItemTitle', ConditionDisplayName = '$Condition', QuantityPurchased = '{$order['TransactionArray']['Transaction']['QuantityPurchased']}', Price = '$UnitPrice', OrderType='1'");
                                        }
                                    }
                                } else {

                                        
                                    for ($t = 0, $it = count($order['TransactionArray']['Transaction']); $t < $it; $t++) {

                                        if (array_key_exists("Variation", $order['TransactionArray']['Transaction'][$t])) {
                                            $ItemTitle = removeEmoji($conn->real_escape_string($order['TransactionArray']['Transaction'][$t]['Item']['Title']));
                                            if (!IsSKUBanned($order['TransactionArray']['Transaction'][$t]['Variation']['SKU'])) {
                                                $allowInsert = true;
                                                check_add_item($conn, trim($order['TransactionArray']['Transaction'][$t]['Variation']['SKU']), $ItemTitle, $order['AmountPaid']);
                                                $UnitPrice = getPriceFromSKU($conn, trim($order['TransactionArray']['Transaction'][$t]['Variation']['SKU']), $accountRow['price_tag']);
                                                $conn->query("INSERT INTO app_order_items SET OrderID = '$OrderID', ItemID = '{$order['TransactionArray']['Transaction'][$t]['Item']['ItemID']}', SKU = '{$order['TransactionArray']['Transaction'][$t]['Variation']['SKU']}', ItemTitle = '$ItemTitle', ConditionDisplayName = '$Condition', QuantityPurchased = '{$order['TransactionArray']['Transaction'][$t]['QuantityPurchased']}', Price = '$UnitPrice', OrderType='1'");
                                            }
                                        } else {
                                            $ItemTitle = removeEmoji($conn->real_escape_string($order['TransactionArray']['Transaction'][$t]['Item']['Title']));
                                            if (!IsSKUBanned($order['TransactionArray']['Transaction'][$t]['Item']['SKU'])) {
                                                $allowInsert = true;
                                                check_add_item($conn, trim($order['TransactionArray']['Transaction'][$t]['Item']['SKU']), $ItemTitle, $order['AmountPaid']);
                                                $UnitPrice = getPriceFromSKU($conn, trim($order['TransactionArray']['Transaction'][$t]['Item']['SKU']), $accountRow['price_tag']);
                                                $conn->query("INSERT INTO app_order_items SET OrderID = '$OrderID', ItemID = '{$order['TransactionArray']['Transaction'][$t]['Item']['ItemID']}', SKU = '{$order['TransactionArray']['Transaction'][$t]['Item']['SKU']}', ItemTitle = '$ItemTitle', ConditionDisplayName = '$Condition', QuantityPurchased = '{$order['TransactionArray']['Transaction'][$t]['QuantityPurchased']}', Price = '$UnitPrice', OrderType='1'");
                                            }
                                        }
                                    }
                                }

                                $query .= "PaidTime='$PaidTime'";

                                // echo $query.'<br><br>';
                                if ($allowInsert) {
                                    if ($conn->query($query)) {
                                        // echo 'DONE<br>';
                                    } else {
                                        // echo 'FAILED: '.$conn->error.'<br>';
                                    }
                                }
                            }
                        } else {
                            // if(array_key_exists("ShippedTime",$order)){
                            //     echo 'SHIPPED '.$OrderID.'<br>';
                            //      $query = "update app_orders set ";
                            //     if(array_key_exists("ShipmentTrackingNumber",$order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails'])){
                            //         $query .= "ShipmentTrackingNumber='{$order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber']}', ";
                            //     }

                            //     $ShippedTime = date('Y-m-d H:i:s', strtotime($order['ShippedTime']));
                            //     $query .= "ShippedTime='$ShippedTime', ";
                            //     $query .= "IsPrinted='1', ";
                            //     $query .= "IsDispatched='1', ";
                            //     $query .= "IsRespond='1'";

                            //     $query .= " WHERE OrderID = '$OrderID'";
                            //     $conn->query($query);

                            // }
                        }
                    }

                    if ($res['PaginationResult']['TotalNumberOfPages'] > $page) {
                        sleep(2);
                        $page += 1;
                        getImportOrdersEbay($conn, $accountRow, $siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $page);
                    }
                }
            } else {
                if ($res['Errors']['ErrorCode'] == 931 || $res['Errors']['ErrorCode'] == 17470 || $res['Errors']['ErrorCode'] == 841 || $res['Errors']['ErrorCode'] == 21916013) {
                    $conn->query("update app_accounts set IsTokenInvalid = '1' WHERE id = '{$accountRow['id']}'");
                }
            }
        }
    }

    function logMessage($message, $type = 'info')
    {
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        $date = date('Y-m-d H:i:s');
        $logLine = "[$date][$type] $message" . PHP_EOL;
        file_put_contents(__DIR__ . '/logs/cron_log_' . date('Y_m_d') . '.log', $logLine, FILE_APPEND);
    }
    
try {

    $siteID = 0;
    $verb = 'GetOrders';
    $CreateTimeFrom = gmdate("Y-m-d\TH:i:s", strtotime('2025-07-17 00:00:00'));
    $CreateTimeTo   = gmdate("Y-m-d\TH:i:s", strtotime('2025-07-17 23:59:59'));


    $dd = date('Y-m-d H:i:s');

    ///GET ORDERS///

    $getAccounts = $conn->query("select * from app_accounts where active = '1' && auth_token != '' && account_type = '1' order by id asc");
    while ($accountRow = $getAccounts->fetch_assoc()) {

        $userToken = $accountRow['auth_token'];
        $AccountID = $accountRow['id'];

        logMessage("Processing account: {$accountRow['account_username']} (ID: {$accountRow['id']})");
        try {
            getImportOrdersEbay($conn, $accountRow, $siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, 1);
        } catch (Exception $e) {
             $errorMsg = $e->getMessage();
            if (
            str_contains($errorMsg, 'Invalid token') ||
            str_contains($errorMsg, 'Token') ||
            str_contains($errorMsg, 'Auth') ||
            str_contains($errorMsg, 'Access token expired') ||
            str_contains($errorMsg, 'Call usage limit') ||
            str_contains($errorMsg, 'Error Code 518')
        ) {
            logMessage("🔒 Token marked invalid for Account ID: {$AccountID}");
        }
            logMessage("Error processing account ID {$AccountID}: " . $e->getMessage(), 'error');
        }
        sleep(5);
    }

    // exit();

    ///POST ORDER RESPONSE///
    $verb = 'CompleteSale';
    $orders = $conn->query("select * from app_orders where IsDispatched = '1' and IsRespond = '0'");
    while ($order = $orders->fetch_assoc()) {
        $account = $conn->query("select * from app_accounts where id = '{$order['AccountID']}'")->fetch_assoc();
        $userToken = $account['auth_token'];
        ///Build the request Xml string
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestXmlBody .= '<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<OrderID>' . $order['OrderID'] . '</OrderID>';
        $requestXmlBody .= '<Shipped>true</Shipped>';
        $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
        $requestXmlBody .= '</CompleteSaleRequest>';

        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);

        //send the request and get response
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        if (stristr($responseXml, 'HTTP 404') || $responseXml == '') {
            echo '<p>Error sending request</p><br>' . $accountRow['account_username'];
        } else {
            $res = XML2Array($responseXml);
            // print_r($res);
            $conn->query("update app_orders set IsRespond = '1' where ID = '{$order['ID']}'");
        }
    }
 
} catch (Exception $e) {
    logMessage("CRON failed: " . $e->getMessage(), 'error');
}

$conn->close();
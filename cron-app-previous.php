<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit','-1');
use Ebay\DigitalSignature\Signature;

function getKeysEbay($token){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apiz.ebay.com/developer/key_management/v1/signing_key',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{ 
        "signingKeyCipher" : "Ed25519"
    }',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '.$token,
        'Content-Type: application/json'
      ),
));

$response = curl_exec($curl);

curl_close($curl);
return json_decode($response, true);
}

function curlifyHeaders($headers)
{
    $new_headers = [];
    foreach ($headers as $header_name => $header_value) {
        $new_headers[] = $header_name . ': ' . $header_value;
    }

    return $new_headers;
}

function logMessage($message, $type = 'info')
{
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    $date = date('Y-m-d H:i:s');
    $logLine = "[$date][$type] $message" . PHP_EOL;
    file_put_contents(__DIR__ . '/logs/cron_app_log_' . date('Y_m_d') . '.log', $logLine, FILE_APPEND);
}
if( isset( $argv ) )
{
    foreach( $argv as $arg ) {
        $e = explode( '=', $arg );
        if( count($e) == 2 )
            $_GET[$e[0]] = $e[1];
        else    
            $_GET[$e[0]] = 0;
    }
}

if(isset($_GET['time'])){
    
    
    if($_GET['time'] == '10m'){

        $newOrders = $conn->query("Select * from app_orders where IsPrinted = '0' && IsArchived = '0'")->num_rows;
        $shippedOrders = $conn->query("Select * from app_orders where IsPrinted = '1'")->num_rows;
        $archivedOrders = $conn->query("Select * from app_orders where IsArchived = '1'")->num_rows;
        $dashboardData = array();
        $dashboardData['newOrders'] = $newOrders;
        $dashboardData['shippedOrders'] = $shippedOrders;
        $dashboardData['archivedOrders'] = $archivedOrders;
        
        $dashboardData = json_encode($dashboardData);
        $conn->query("update app_settings set value = '$dashboardData' where name = 'dashboard_data'");
    
        $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
         logMessage("Processing account of 10m cron");
        while($account = $accounts->fetch_assoc()){
            
            $totalSale = $conn->query("SELECT IFNULL(SUM(a.QuantityPurchased*a.Price), 0) amount FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && b.IsArchived = '0' && b.AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
            $totalShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE  IsArchived = '0' && AccountID = '{$account['id']}'")->fetch_assoc()['amount'];
            $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}' and status = 100 and type = 1")->fetch_assoc()['amount']+0;
            $balance = round(($totalPayments-($totalSale+$totalShippingCost)), 2);
            $awaiting_shipments = $conn->query("select * from app_orders where AccountID = '{$account['id']}' && IsArchived = '0' && IsPrinted = '0'")->num_rows;
            
            
            
            
            $conn->query("update app_accounts set balance = '$balance', awaiting_shipments = '$awaiting_shipments', sp_balance = '0' where id = '{$account['id']}'");
            
        }
        
        
    
    }
    
    if($_GET['time'] == '1h'){
        $conn->query("DELETE FROM app_orders WHERE OrderID NOT IN (SELECT OrderID FROM app_order_items)");
        $conn->query("DELETE FROM app_order_items WHERE OrderID NOT IN (SELECT OrderID FROM app_orders)");
        
        $getRate = $conn->query("SELECT * FROM app_settings WHERE name='exchange_rate'")->fetch_assoc()['value'];
        $getRate = json_decode($getRate, true);
        $getRate['updated'] = 1;
        $exchangeRate = json_encode($getRate);
         logMessage("Processing payoutAccounts of 1h cron: {$exchangeRate}");
        $conn->query("update app_settings set value = '$exchangeRate' where name = 'exchange_rate'");
        require 'vendor/autoload.php';
        $date = date('Y-m-d');
        //  $date = date('Y-m-d',strtotime("-18 days"));
        //  echo '$date: <pre>' .print_r($date,true). '</pre>';
        // $payoutAccounts = $conn->query("select * from app_accounts where active = '1' && auth_token != '' && account_type = '1' && auto_payout = 1 && id not in (SELECT account_id FROM app_auto_payouts WHERE DATE(datetime) = '$date') order by id ASC");
        $payoutAccounts = $conn->query("SELECT a.* FROM app_accounts a LEFT JOIN app_auto_payouts p  ON a.id = p.account_id  AND DATE(p.datetime) = '$date'
                                                WHERE a.active = 1
                                                  AND a.auth_token != ''
                                                  AND a.account_type = 1
                                                  AND a.auto_payout = 1
                                                  AND p.account_id IS NULL
                                                ORDER BY a.id ASC");
        // echo '$payoutAccounts: <pre>' .print_r($payoutAccounts->fetch_assoc(),true). '</pre>';
        while($accountRow = $payoutAccounts->fetch_assoc()){
            $userToken = $accountRow['auth_token'];
            $AccountID = $accountRow['id'];
            
            $keys = getKeysEbay($userToken);
            if(array_key_exists("privateKey",$keys) && array_key_exists("jwe",$keys)){
           
                $privateKey = $keys['privateKey'];
                $jwe = $keys['jwe'];
                $config = '{
                      "digestAlgorithm": "sha-256",
                      "algorithm": "Ed25519",
                      "privateKey": "'.$privateKey.'",
                      "jwe": "'.$jwe.'",
                      "signatureParams": [
                        "content-digest",
                        "x-ebay-signature-key",
                        "@method",
                        "@path",
                        "@authority"
                      ]
                }';
                $signature = new Signature($config);
                $endpoint = 'https://apiz.ebay.com/sell/finances/v1/payout?filter=payoutDate:%5B'.$date.'T00:00:01.000Z..'.$date.'T23:59:59.000Z%5D';
                $headers = [
                    'Authorization' => 'Bearer ' . $userToken,
                    'X-EBAY-C-MARKETPLACE-ID' => 'EBAY_GB',
                    'Content-Type' => 'application/json'
                ];
                $body = null;
                $headers = $signature->generateSignatureHeaders($headers, $endpoint, "GET", $body);
                
                //Making a call
                $ch = curl_init($endpoint);
                if (!empty($body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, curlifyHeaders($headers));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                
                //  echo '$response: <pre>' .print_r($response,true). '</pre>';
                if(!empty($response)){
                    $res = json_decode($response, true);
                }else{
                    $res = array();
                }
                 echo '$res: <pre>' .print_r($res,true). '</pre>';
                if(array_key_exists("payouts",$res)){
                    foreach($res['payouts'] as $payout){
                        
                        if($payout['payoutStatus'] == 'SUCCEEDED'){
                            $description = "Payout to ".$payout['payoutInstrument']['instrumentType']." ".$payout['payoutInstrument']['accountLastFourDigits'];
                            
                            $payoutDate = date('Y-m-d H:i:s', strtotime($payout['payoutDate']));
                            $query = "INSERT INTO app_auto_payouts SET ";
                            $query .= "account_id = '$AccountID', ";
                            $query .= "description = '$description', ";
                            $query .= "amount = '{$payout['amount']['value']}', ";
                            $query .= "ebay_payout_id = '{$payout['payoutId']}', ";
                            $query .= "datetime = '$payoutDate'";
                            
                            $conn->query($query);
                        }
                        
                    }
                }
            }
            
            
        }
        
        $date = date('Y-m-d',strtotime("-1 days"));
        $payoutAccounts = $conn->query("select * from app_accounts where active = '1' && auth_token != '' && account_type = '1' && auto_payout = 1 && id not in (SELECT account_id FROM app_auto_payouts WHERE DATE(datetime) = '$date') order by id ASC");
        while($accountRow = $payoutAccounts->fetch_assoc()){
    
            $userToken = $accountRow['auth_token'];
            $AccountID = $accountRow['id'];
                            
            $payoutDate = date('Y-m-d H:i:s', strtotime($date));
            $query = "INSERT INTO app_auto_payouts SET ";
            $query .= "account_id = '$AccountID', ";
            $query .= "description = 'Payout was not sent', ";
            $query .= "amount = '0', ";
            $query .= "ebay_payout_id = '', ";
            $query .= "datetime = '$payoutDate'";
            
            $conn->query($query);
            
            
        }
        
    }
    
    // if($_GET['time'] == 'test'){
    //     $items = $conn->query("select * from app_items where deleted = 1 order by id asc limit 3000, 4000");
        
    //     while($item = $items->fetch_assoc()){
    //         $data = array();
    //         if($item['item_type']==1){
    //             $rmStock = getStock($conn, $item['id'], $item['sku']);
                
    //             if($rmStock > 0){
    //                 $now = date('Y-m-d H:i:s');
    //                 $conn->query("insert into app_stocks set item_id = '{$item['id']}', qty = '-$rmStock', description = 'Azhar Adjustment', datetime = '$now'");
                   
    //             }else if($rmStock < 0){
    //                 $now = date('Y-m-d H:i:s');
    //                  $conn->query("insert into app_stocks set item_id = '{$item['id']}', qty = '$rmStock', description = 'Azhar Adjustment', datetime = '$now'");
                   
    //             }
                
                
    //             $data['remain_stock'] = 0;
    //             $data['upcoming_stock'] = (int) $conn->query("Select SUM(qty) as qty from app_purchase_detail where item_id = '{$item['id']}' && status = 0 && purchase_id not in (105, 129, 130)")->fetch_assoc()['qty']+0;
    //             $data['cost_price'] = $conn->query("SELECT * FROM app_sellprices_amount WHERE item_id = '{$item['id']}' && name_id = '12' && type = '1'")->fetch_assoc()['price']+0;
    //         }else{
    //             $data['remain_stock'] = 0;
    //             $data['upcoming_stock'] = 0;
    //             $data['cost_price'] = 0;
                
    //         }
            
    //         $data = json_encode($data);
            
    //         $conn->query("update app_items set statistics = '$data' where id = '{$item['id']}'");
            
            
    //     }
    // }
    
    if($_GET['time'] == '2h'){
  
        logMessage("Processing items of 2h cron");
        
        $limit  = 500;
        $lastId = 0;
        
        $updateStmt = $conn->prepare(
            "UPDATE app_items SET statistics = ? WHERE id = ?"
        );
        
        do {
        
            $sql = "
                SELECT 
                    i.id,
                    i.sku,
                    i.item_type,
                    COALESCE(pd.upcoming_stock, 0) AS upcoming_stock,
                    COALESCE(sp.price, 0) AS cost_price
                FROM app_items i
        
                LEFT JOIN (
                    SELECT item_id, SUM(qty) AS upcoming_stock
                    FROM app_purchase_detail
                    WHERE status = 0
                      AND purchase_id NOT IN (105,129,130)
                    GROUP BY item_id
                ) pd ON pd.item_id = i.id
        
                LEFT JOIN app_sellprices_amount sp
                    ON sp.item_id = i.id
                    AND sp.name_id = 12
                    AND sp.type = 1
        
                WHERE i.deleted = 0
                  AND i.id > $lastId
                ORDER BY i.id ASC
                LIMIT $limit
            ";
        
            $result = $conn->query($sql);
            if ($result->num_rows === 0) {
                break;
            }
        
            while ($item = $result->fetch_assoc()) {
        
                $remainStock = ((int)$item['item_type'] === 1)
                    ? (int)getStock($conn, $item['id'], $item['sku'])
                    : 0;
        
                $json = json_encode([
                    'remain_stock'   => $remainStock,
                    'upcoming_stock' => (int)$item['upcoming_stock'],
                    'cost_price'     => (float)$item['cost_price']
                ], JSON_THROW_ON_ERROR);
        
                $updateStmt->bind_param("si", $json, $item['id']);
                $updateStmt->execute();
        
                $lastId = $item['id'];
            }
        
            $result->free();
        
        } while (true);
        
        $updateStmt->close();
        
        logMessage("Item statistics cron completed");
        
        require_once('inc/Keys.php');
        require_once('inc/eBaySession.php');
        
        $limit  = 100;
        $lastId = 0;
        
        $updateOrder = $conn->prepare(
            "UPDATE app_orders SET isTrackingUpload = 1 WHERE ID = ?"
        );
        
        do {
        
            $sql = "
                SELECT 
                    o.ID,
                    o.OrderID,
                    o.ShipmentTrackingNumber,
                    a.auth_token
                FROM app_orders o
                INNER JOIN app_accounts a ON a.id = o.AccountID
                WHERE o.isTrackingUpload = 0
                  AND o.ShipmentTrackingNumber IS NOT NULL
                  AND o.ShipmentTrackingNumber != ''
                  AND o.ID > $lastId
                ORDER BY o.ID ASC
                LIMIT $limit
            ";
        
            $orders = $conn->query($sql);
            if ($orders->num_rows === 0) {
                break;
            }
        
            while ($order = $orders->fetch_assoc()) {
        
               $xml = sprintf(
                '<?xml version="1.0" encoding="utf-8"?>
                <CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                    <OrderID>%s</OrderID>
                    <Shipped>true</Shipped>
                    <Shipment>
                        <ShipmentTrackingDetails>
                            <ShipmentTrackingNumber>%s</ShipmentTrackingNumber>
                            <ShippingCarrierUsed>Other</ShippingCarrierUsed>
                        </ShipmentTrackingDetails>
                    </Shipment>
                    <RequesterCredentials>
                        <eBayAuthToken>%s</eBayAuthToken>
                    </RequesterCredentials>
                </CompleteSaleRequest>',
                    htmlspecialchars($order['OrderID'], ENT_XML1),
                    htmlspecialchars($order['ShipmentTrackingNumber'], ENT_XML1),
                    htmlspecialchars($order['auth_token'], ENT_XML1)
                );
            
                $session = new eBaySession(
                    $order['auth_token'],
                    $devID, $appID, $certID,
                    $serverUrl, $compatabilityLevel,
                    0, 'CompleteSale'
                );
        
                $response = $session->sendHttpRequest($xml);
        
                if ($response && stripos($response, 'HTTP') === false) {
                    $res = XML2Array($response);
                    if (!empty($res['Ack']) && $res['Ack'] === 'Success') {
                        $updateOrder->bind_param("i", $order['ID']);
                        $updateOrder->execute();
                    }
                }
        
                $lastId = $order['ID'];
                usleep(200000); // rate-limit safety
            }
        
            $orders->free();
        
        } while (true);
        
        $updateOrder->close();
        $conn->close();
        
        logMessage("eBay tracking cron completed");

    }
    
    if($_GET['time'] == '5m'){
        
        $getRate = $conn->query("SELECT * FROM app_settings WHERE name='exchange_rate'")->fetch_assoc()['value'];
        $getRate = json_decode($getRate, true);
        logMessage("Processing getRate of 5m cron");
        if($getRate['updated']==1){
            $prices = $conn->query("SELECT * FROM app_sellprices_amount where name_id = '8'");
            while($price = $prices->fetch_assoc()){
                $newPrice = round($price['price']/$getRate['rate'], 2);
                $check_price_tag = $conn->query("select * from app_sellprices_amount where name_id = '12' && item_id = '{$price['item_id']}' && type = '{$price['type']}'");
                if($check_price_tag->num_rows > 0){
                    $conn->query("update app_sellprices_amount set price = '$newPrice' where name_id = '12' && item_id = '{$price['item_id']}' && type = '{$price['type']}'");
                   
                }else{
                    $conn->query("insert into app_sellprices_amount set item_id = '{$price['item_id']}', name_id = '12', price = '$newPrice', type = '{$price['type']}'");
                }
            }
            
           $getRate['updated'] = 0;
           $exchangeRate = json_encode($getRate);
           $conn->query("update app_settings set value = '$exchangeRate' where name = 'exchange_rate'");
        }
        
        
    }
    
    
} 

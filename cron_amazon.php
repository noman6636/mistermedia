<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

include __DIR__ . '/vendor/autoload.php';



function getImportOrdersAmazon($conn,$accountRow, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID){
       $config = [
        'http' => [
          'verify' => false,    //<--- NOT SAFE FOR PRODUCTION
          'debug' => false       //<--- NOT SAFE FOR PRODUCTION
        ],
        'refresh_token' => $userToken,
        'client_id' => 'amzn1.application-oa2-client.3ef4b863e6154e64a8e0d4edca56a0fa',
        'client_secret' => 'amzn1.oa2-cs.v1.1f5196bb09295893a41cfc06e48a8d8b919b2f2dc2ad9a7fd6aeaa776344be97',
        'access_key' => 'AKIAQ2SE7E2NEVTMIUKP',
        'secret_key' => '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',
        'role_arn' => 'arn:aws:iam::057052964506:role/DordersSellingApi' ,
        'region' => 'eu-west-1',
        'host' => 'sellingpartnerapi-eu.amazon.com'
      ];
      
      @unlink(__DIR__ .'/aws-tokens.txt');
      $tokenStorage = new DoubleBreak\Spapi\SimpleTokenStorage(__DIR__ .'/aws-tokens.txt');
    
      $signer = new DoubleBreak\Spapi\Signer();
    
      $credentials = new DoubleBreak\Spapi\Credentials($tokenStorage, $signer, $config);
      $cred = $credentials->getCredentials();
   
        try{
            $ordersClient = new \DoubleBreak\Spapi\Api\Orders($cred, $config);
        
        
            $params=["MarketplaceIds"=>"A1F83G8C2ARO7P","LastUpdatedAfter"=>$CreateTimeFrom];
        
            $orders = $ordersClient->getOrders($params);
            // print_r($orders);
            // exit;
            if (array_key_exists("payload",$orders)){
                foreach($orders['payload']['Orders'] as $order){
                    $OrderID = $order['AmazonOrderId'];
                    $check_order = $conn->query("select * from app_orders where OrderID = '$OrderID' && OrderType='4'");
                     if($check_order->num_rows == 0){
                        $CreatedTime = date('Y-m-d H:i:s', strtotime($order['PurchaseDate']));
                        $PaidTime = date('Y-m-d H:i:s', strtotime($order['PurchaseDate']));
                        if(($order['OrderStatus'] == 'Unshipped' || $order['OrderStatus'] == 'PartiallyShipped' || $order['OrderStatus'] == 'Shipped') && $order['FulfillmentChannel'] == 'MFN'){
                            $orderAddress = $ordersClient->getOrderAddress($OrderID);
                            $AmzShippingAddress = $orderAddress['payload']['ShippingAddress'];
                            $orderDetails = $ordersClient->getOrder($OrderID);
                          
                            $shippingAddress = array();
                            $shippingAddress['Name'] = '';
                            $shippingAddress['Street1'] = '';
                            $shippingAddress['Street2'] = '';
                            $shippingAddress['CityName'] = '';
                            $shippingAddress['StateOrProvince'] = '';
                            $shippingAddress['Country'] = '';
                            $shippingAddress['CountryName'] = '';
                            $shippingAddress['Phone'] = '';
                            $shippingAddress['PostalCode'] = '';
                            $postCode = '';
                            if(array_key_exists("Name",$AmzShippingAddress)){ $shippingAddress['Name'] = $AmzShippingAddress['Name']; }
                            if(array_key_exists("AddressLine1",$AmzShippingAddress)){ $shippingAddress['Street1'] = $AmzShippingAddress['AddressLine1']; }
                            if(array_key_exists("AddressLine2",$AmzShippingAddress)){ $shippingAddress['Street2'] = $AmzShippingAddress['AddressLine2']; }
                            if(array_key_exists("City",$AmzShippingAddress)){ $shippingAddress['CityName'] = $AmzShippingAddress['City']; }
                            if(array_key_exists("StateOrRegion",$AmzShippingAddress)){ $shippingAddress['StateOrProvince'] = $AmzShippingAddress['StateOrRegion']; }
                            if(array_key_exists("CountryCode",$AmzShippingAddress)){ $shippingAddress['Country'] = $AmzShippingAddress['CountryCode']; }
                            if(array_key_exists("CountryCode",$AmzShippingAddress)){ $shippingAddress['CountryName'] = $AmzShippingAddress['CountryCode']; }
                            if(array_key_exists("Phone",$AmzShippingAddress)){ $shippingAddress['Phone'] = $AmzShippingAddress['Phone']; }
                            if(array_key_exists("PostalCode",$AmzShippingAddress)){ $shippingAddress['PostalCode'] = $AmzShippingAddress['PostalCode'];$postCode=$AmzShippingAddress['PostalCode']; }
                            
                            $ShippingAddress = $conn->real_escape_string(json_encode($shippingAddress));
                            
                            $query = "insert into app_orders set ";
                            $query .= "AccountID='$AccountID', ";
                            $query .= "OrderID='$OrderID', ";
                            $query .= "OrderStatus='{$order['OrderStatus']}', ";
                            $query .= "AdjustmentAmount='0', ";
                            $query .= "AmountPaid='{$order['OrderTotal']['Amount']}', ";
                            $query .= "PaymentMethod='{$order['PaymentMethod']}', ";
                            $query .= "PaymentStatus='Complete', ";
                            $query .= "CreatedTime='$CreatedTime', ";
                            $query .= "Subtotal='{$order['OrderTotal']['Amount']}', ";
                            $query .= "Total='{$order['OrderTotal']['Amount']}', ";
                            $query .= "PostCode='$postCode', ";
                            
                            $query .= "SellingManagerSalesRecordNumber='0', ";
                           
                            
                            $query .= "ShippingAddress='$ShippingAddress', ";
                            
                            if (array_key_exists("ShipServiceLevel",$order)){
                                $query .= "ShippingService='{$order['ShipServiceLevel']}', ";
                            }
                            
                            $query .= "ShippingServiceCost='0', ";
                            
                            $orderBuyer = $ordersClient->getOrderBuyerInfo($OrderID);
                            
                            $query .= "BuyerUserID='{$orderBuyer['payload']['BuyerEmail']}', ";
                            
                            if(array_key_exists("BuyerCheckoutMessage",$order)){
                                $check_out_msg  = removeEmoji($conn->real_escape_string($order['BuyerCheckoutMessage']));
                                $query .= "BuyerCheckoutMessage='$check_out_msg', ";
                            }
                            
                            $query .= "ShipmentTrackingNumber='', ";
                            $ShippedTime = date('Y-m-d H:i:s', strtotime($CreatedTime));
                            $query .= "ShippedTime='$ShippedTime', ";
                            $query .= "IsPrinted='0', ";
                            $query .= "OrderType='4', ";
                            $query .= "IsDispatched='0', ";
                            $query .= "IsRespond='1', ";
                            
                            $orderItems = $ordersClient->getOrderItems($OrderID);
                           
                            
                            foreach($orderItems['payload']['OrderItems'] as $orderItem){
                                $ItemTitle = removeEmoji($conn->real_escape_string($orderItem['Title']));
                                if(!IsSKUBanned($orderItem['SellerSKU'])){
                                    check_add_item($conn, $orderItem['SellerSKU'], $ItemTitle, $orderItem['ItemPrice']['Amount']);
                                    $UnitPrice = getPriceFromSKU($conn, $orderItem['SellerSKU'], $accountRow['price_tag']);
                                    $conn->query("INSERT INTO app_order_items SET OrderID = '$OrderID', ItemID = '{$orderItem['OrderItemId']}', SKU = '{$orderItem['SellerSKU']}', ItemTitle = '$ItemTitle', ConditionDisplayName = '{$orderItem['ConditionId']}', QuantityPurchased = '{$orderItem['QuantityOrdered']}', Price = '$UnitPrice', OrderType='4'");
                                }
                            }
                             $query .= "PaidTime='$PaidTime'";
                            
                            if($conn->query($query)){
                                // echo 'DONE<br>';
                            }else{
                                echo 'FAILED: '.$conn->error.'<br>';
                            }
                            
                        }
                        
                     }
                }
            }
       
        }
        catch(Exception $e) {
          echo 'Message: ' .$e->getMessage();
        }

   
}





  

    // 2021-06-11T08:15:30-05:00
    // $CreateTimeFrom = gmdate("Y-m-d\TH:i:s",time()-3600*72).'-05:00';
$CreateTimeFrom = gmdate("Y-m-d\TH:i:s",time()-3600*36).'-05:00';
$CreateTimeTo = gmdate("Y-m-d\TH:i:s");


$dd = date('Y-m-d H:i:s');

///GET ORDERS///

$getAccounts = $conn->query("select * from app_accounts where  active = '1' && auth_token != '' && account_type = '4' order by id desc");
while($accountRow = $getAccounts->fetch_assoc()){
    
    $userToken = $accountRow['auth_token'];
    $AccountID = $accountRow['id'];
    // echo "ACCOUNT ID $AccountID <br>";
    getImportOrdersAmazon($conn, $accountRow, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID);
        
    
    
}



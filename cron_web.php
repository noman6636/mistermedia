<?php 
require_once "inc/config.php";
require_once "inc/functions.php";


$CreateTimeFrom = date("Y-m-d H:i:s",time()-3600*48); //current time minus 30 minutes
$CreateTimeTo = date("Y-m-d H:i:s",time()+3600*48);


$dd = date('Y-m-d H:i:s');

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://oxijan.co.uk/apis/orders/getOrders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('fromTime' => $CreateTimeFrom,'toTime' => $CreateTimeTo),
));

$response = curl_exec($curl);

curl_close($curl);
$ordersArray = json_decode($response, true);


foreach($ordersArray as $order){
    $orderdetail = $order['orderdetails'];
    $billing_details = $order['billing_details'];
    $shippingAddress = array();
    $shippingAddress['Name'] = $billing_details['full_name'];
    $shippingAddress['Street1'] = $billing_details['address'];
    $shippingAddress['Street2'] = $billing_details['street'];
    $shippingAddress['CityName'] = $billing_details['city'];
    $shippingAddress['StateOrProvince'] = '';
    $shippingAddress['Country'] = $billing_details['country'];
    $shippingAddress['CountryName'] = $billing_details['country'];
    $shippingAddress['Phone'] = $billing_details['phone'];
    $shippingAddress['PostalCode'] = $billing_details['zipcode'];
    $order_id = strtotime($orderdetail['created_date']).'-WEB-'.$orderdetail['id'];
    $shippingAddress = $conn->real_escape_string(json_encode($shippingAddress));
    $accountRow = $conn->query("SELECT * FROM app_accounts where id = '179'")->fetch_assoc();
    
    $check_order = $conn->query("SELECT * FROM app_orders where OrderID = '$order_id'");
    // echo $orderdetail['total_amount'];
    if($check_order->num_rows == 0){
        $pquery = "INSERT INTO app_orders SET AccountID = '179', OrderID = '$order_id', OrderStatus = 'Completed', PaymentMethod = 'DChannel', PaymentStatus = 'Complete', CreatedTime = '{$orderdetail['created_date']}', Subtotal = '{$orderdetail['total_amount']}', Total = '{$orderdetail['total_amount']}', ShippingAddress = '$shippingAddress', PostCode = '{$billing_details['zipcode']}', BuyerUserID = '{$orderdetail['customer_name']}', BuyerEmail = '{$orderdetail['customer_email']}', OrderType = '3'";
   
        if($conn->query($pquery)){
            foreach($order['orderedproducts'] as $product){
                check_add_item($conn, $product['sku'], $product['name'], $product['unit_price']);
                $UnitPrice = getPriceFromSKU($conn, $product['sku'], $accountRow['price_tag']);
                $conn->query("INSERT INTO app_order_items SET OrderID = '$order_id', SKU = '{$product['sku']}', ItemTitle = '{$product['name']}', QuantityPurchased = '{$product['orderquantity']}', Price = '$UnitPrice', OrderType = '3'");
            }
                                        
        }else{
            echo $conn->error;
        }
    }
    
}
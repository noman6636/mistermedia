<?php
if(isset($_POST['labeltype'])){
    $goBackUrl = $_SERVER['REQUEST_URI'];
    $labeltype = $_POST['labeltype'];
    if($labeltype == 1){
        $labelImg = 'DC_UNG.png';
    }elseif($labeltype == 2){
        $labelImg = 'DC_UNH.png';
    }
    $aorder = $_POST['aorder'];
    if(count($aorder) < 1){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast on order to print.</div></div>';
        header("location: $goBackUrl");
        exit();
    }
    
    if($labeltype == 100){
        $orderIds = array();
        foreach($aorder as $order){
            $orderId = strtok($order, '/');
            $conn->query("update app_orders set IsArchived = '1' where ID = '$orderId'");
            array_push($orderIds, $orderId);
        }
        
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'ORDER ARCHIVED', "Total $totalOrders has been archived", $orderIds);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders has been archived</div></div>';
        header("location: $goBackUrl");
        exit();
    }
    
    if($labeltype == 200){
        // foreach($aorder as $order){
        //     $orderId = strtok($order, '/');
        //     $conn->query("update app_orders set IsInReship = '1' where ID = '$orderId'");
        // }
        $date = date('Y-m-d H:i:s');
        $orderIds = array();
        foreach($aorder as $orderid){
            $orderid = strtok($orderid, '/');
            array_push($orderIds, $orderid);
            $order = $conn->query("select * from app_orders where ID = '$orderid'")->fetch_assoc();
            $order_items = $conn->query("select * from app_order_items where OrderID = '{$order['OrderID']}'");
            
            
            $count_orders = $conn->query("SELECT * FROM `app_orders` WHERE OrderID LIKE '%{$order['OrderID']}%'")->num_rows;
            
            $new_order_id = $order['OrderID'].'-R'.$count_orders;
            $pquery = "INSERT INTO app_orders SET AccountID = '221', OrderID = '$new_order_id', OrderStatus = 'Completed', PaymentMethod = 'D-Orders', PaymentStatus = 'Complete', CreatedTime = '$date', Subtotal = '{$order['Subtotal']}', Total = '{$order['Total']}', ShippingAddress = '{$order['ShippingAddress']}',ShippingService = '{$order['ShippingService']}', ShippingServiceCost = '{$order['ShippingServiceCost']}', PaidTime = '{$order['PaidTime']}', PostCode = '{$order['PostCode']}', Reference = '{$order['Reference']}', BuyerUserID = '{$order['BuyerUserID']}', BuyerCheckoutMessage = '{$order['BuyerCheckoutMessage']}', OrderType = '2', IsPrinted = '1', IsDispatched = '1', IsRespond = '1', IsInReship = '1'";
            
             if($conn->query($pquery)){
            
                    while ($itemso = $order_items->fetch_assoc()) {
                       
            			    $conn->query("INSERT INTO app_order_items SET OrderID = '$new_order_id', ItemID = '{$itemso['ItemID']}', SKU = '{$itemso['SKU']}', ItemTitle = '{$itemso['ItemTitle']}', QuantityPurchased = '{$itemso['QuantityPurchased']}', Price = '{$itemso['Price']}', OrderType = '2'");
                        
                    }
            
             }
            
        }
        
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'ORDER UNSHIPPED', "Total $totalOrders has been unshipped and created new orders", $orderIds);
        
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected orders has been marked as unshipped.</div></div>';
        header("location: $goBackUrl");
        exit();
    }
    
    if($labeltype == 3){
        $ids = '';
        $orderIds = array();
        foreach($aorder as $order){
            $ids .= strtok($order, '/').',';
            array_push($orderIds, strtok($order, '/'));
            
        }
        $ids = rtrim($ids, ',');
        $whereQuery = "WHERE ID IN ($ids)";
        $data=array();
        $headKeys=array();
        $keysOnly = $settings['csv_settings'];
        $keysOnlyDb = $keysOnly;
        $keysOnlyDb = str_replace(array('FullName,', 'AddressLine1,', 'AddressLine2,', 'City,', 'Country,', 'PhoneNo,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb = str_replace(array('ItemID,', 'SKU,', 'ItemTitle,', 'ConditionDisplayName,', 'QuantityPurchased,', 'SKUQTY,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb .= ", ShippingAddress";
        
        
        
        
        
        // echo $keysOnly;
        // exit;
        $query = "select OrderID, $keysOnlyDb from app_orders $whereQuery ORDER BY ID DESC";
        $query = $conn->query($query);
        while($row = $query->fetch_assoc()){
            
                $shipa = json_decode($row['ShippingAddress'], true);
                
        $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$row['OrderID']}'");
        $showSKUQTY = '';
        $showItemID = '';
        $showSKU = '';
        $showItemTitle = '';
        $showConditionDisplayName = '';
        $showQuantityPurchased = '';
        
        while($item = $itemsList->fetch_assoc()){
            $showSKUQTY .= $item['QuantityPurchased']." x ".$item['SKU']." = ";
            $showItemID .= $item['ItemID']." = ";
            $showSKU .= $item['SKU']." = ";
            $showItemTitle .= $item['ItemTitle']." = ";
            $showConditionDisplayName .= $item['ConditionDisplayName']." = ";
            $showQuantityPurchased .= $item['QuantityPurchased']." = ";
        }
        if (strpos($keysOnly, 'SKUQTY') !== false) {
           $row['SKU_QTY'] = rtrim($showSKUQTY, ' = ');
        }
        
        if (strpos($keysOnly, 'ItemID') !== false) {
           $row['ItemID'] = rtrim($showItemID, ' = ');
        }
        
        if (strpos($keysOnly, 'SKU') !== false) {
           $row['SKU'] = rtrim($showSKU, ' = ');
        }
        
        if (strpos($keysOnly, 'ItemTitle') !== false) {
           $row['ItemTitle'] = rtrim($showItemTitle, ' = ');
        }
        
        if (strpos($keysOnly, 'ConditionDisplayName') !== false) {
           $row['ConditionDisplayName'] = rtrim($showConditionDisplayName, ' = ');
        }
        
        if (strpos($keysOnly, 'QuantityPurchased') !== false) {
           $row['QuantityPurchased'] = rtrim($showQuantityPurchased, ' = ');
        }
                
                unset($row['ShippingAddress']);
                foreach($shipa as $key => $value){
                    if($key == 'Name' && (strpos($keysOnly, 'FullName') !== false)){
                        if(!is_array($value)){ $row['Name'] = $value; }else{ $row['Name'] = ''; }
                    }
                    
                    if($key == 'Street1' && (strpos($keysOnly, 'AddressLine1') !== false)){
                        if(!is_array($value)){ $row['Street1'] = $value; }else{ $row['Street1'] = ''; }
                    }
                    
                    if($key == 'Street2' && (strpos($keysOnly, 'AddressLine2') !== false)){
                        if(!is_array($value)){ $row['Street2'] = $value; }else{ $row['Street2'] = ''; }
                    }
                    
                    if($key == 'CityName' && (strpos($keysOnly, 'City') !== false)){
                        if(!is_array($value)){ $row['CityName'] = $value; }else{ $row['CityName'] = ''; }
                    }
                    
                    if($key == 'CountryName' && (strpos($keysOnly, 'Country') !== false)){
                        if(!is_array($value)){ $row['CountryName'] = $value; }else{ $row['CountryName'] = ''; }
                    }
                    if($key == 'Phone' && (strpos($keysOnly, 'PhoneNo') !== false)){
                       if(!is_array($value)){ $row['Phone'] = $value; }else{ $row['Phone'] = ''; }
                    }
                    // $row['ShippingAddress'] .= $key.": ".$value."\r\n";
                }
            
            $row['AccountID'] =  $conn->query("select * from app_accounts where id = '{$row['AccountID']}'")->fetch_assoc()['account_name'];
            // $row['AccountID'] = 
    
            $data[]=$row;
        }
        
        $dateNow = date('Y-m-d H:i:s');
        $conn->query("update app_orders set IsPrinted = '1', ShippedTime = '$dateNow' $whereQuery");
        
        $totalOrders = count($orderIds);
        $orderIds = implode(', ', $orderIds);
        addSystemLog($conn, 'ORDER PRINT', "Total $totalOrders has been marked as printed", $orderIds);
    
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="NewOrders.csv";');
        $output = fopen('php://output', 'w');
    
         
    
        $keysPut = 0;
        foreach($data as $product) {
            if($keysPut == 0){
               fputcsv($output, array_keys($product));  
               $keysPut = 1;
            }
            fputcsv($output, $product);  
            
        }
        fclose($output);
        exit();
    
        
    }
    $totalSelected = count($aorder);
    $n = 0;
    $printHTML = '<style type="text/css">
   table { page-break-inside:auto }
   tr    { page-break-inside:avoid; page-break-after:auto }
</style>
    <table width="100%">';
    $orderIds = array();
   foreach($aorder as $order){
     
       
       $orderId = strtok($order, '/');
       array_push($orderIds, $orderId);
       $order = $conn->query("select * from app_orders where ID = '$orderId'")->fetch_assoc();
       $shipa = json_decode($order['ShippingAddress'], true);
       $dateNow = date('Y-m-d H:i:s');
       $conn->query("update app_orders set IsPrinted = '1', ShippedTime = '$dateNow' where ID = '$orderId'");
       $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$order['OrderID']}'");
        $showSku = '';
        
        while($item = $itemsList->fetch_assoc()){
            $showSku .= $item['QuantityPurchased'].' x '.$item['SKU'].'<br>';
        }
       if(!$n%2) {$printHTML .= '<tr>';}
       $printHTML .= '<td style="width:50%;padding: 20px;">
            <div style="width:100%;display: flex;">
                <div style="width:60%;font-size: 16px;font-family: sans-serif;">';
                   if(!is_array($shipa['Name'])){ $printHTML .= $shipa['Name'] .'<br>'; }
                   if(!is_array($shipa['Street1'])){ $printHTML .= $shipa['Street1'] .'<br>'; }
                   if(!is_array($shipa['Street2'])){ $printHTML .= $shipa['Street2'] .'<br>'; }
                   if(!is_array($shipa['CityName'])){ $printHTML .= $shipa['CityName'] .'<br>'; }
                   if(!is_array($shipa['StateOrProvince'])){ $printHTML .= $shipa['StateOrProvince'] .'<br>'; }
                   if(!is_array($shipa['PostalCode'])){ $printHTML .= $shipa['PostalCode'] .'<br>'; }
                   if(!is_array($shipa['CountryName'])){ $printHTML .= $shipa['CountryName'] .'<br>'; }
                   
               $printHTML .= '</div>
                <div style="width:40%;text-align: right;font-size: 16px;font-family: sans-serif;">
                    <img src="assets/'.$labelImg.'" style="width:60%"/><br>
                    '.$order['OrderID'].'<br>
                    '.$showSku.'
                </div>
            </div>
        </td>';
       if($n%2) {$printHTML .= '</tr>';}
       $n++;
   }
   
    $totalOrders = count($orderIds);
    $orderIds = implode(', ', $orderIds);
    addSystemLog($conn, 'ORDER PRINT', "Total $totalOrders has been marked as printed", $orderIds);
   
   $printHTML .= '</table>';
   echo $printHTML;
   $title = 'Packing-Slip-'.date('Y-m-d');
   echo '<script type="text/javascript">
window.print();
window.onfocus=function(){ window.location.href="'.$goBackUrl.'";}
</script>';
    exit();
}
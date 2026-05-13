<?php
require_once "config.php";
require_once "functions.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
 
if(isset($_POST['editAddress'])){
    $ID = $_POST['editAddress'];
    $dataO = $conn->query("select * from app_orders where ID = '$ID'")->fetch_assoc();
    $items = $conn->query("select * from app_order_items where OrderID = '{$dataO['OrderID']}'");
    $data = json_decode($dataO['ShippingAddress'], true);
    
    $html = '';
    
    while($item = $items->fetch_assoc()){
        $html .= '<div class="row">
                        <input type="hidden" name="order_item_id[]" value="'.$item['id'].'" /> 
                        <div class="col-md-4">
                        <label>SKU: </label>
                            <div class="form-group">
                                <input type="text" placeholder="SKU" name="SKU[]" value="'.$item['SKU'].'" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Qty: </label>
                            <div class="form-group">
                                <input type="text" placeholder="QuantityPurchased" name="QuantityPurchased[]" value="'.$item['QuantityPurchased'].'" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Price: </label>
                            <div class="form-group">
                                <input type="text" placeholder="Price" name="Price[]" value="'.$item['Price'].'" class="form-control">
                            </div>
                        </div>
                    </div>';
    }
    function escape($val) {
        return htmlspecialchars(is_array($val) ? '' : $val, ENT_QUOTES, 'UTF-8');
    }

    $html .= '
    <label>Name:</label>
    <div class="form-group">
        <input type="text" placeholder="Name" name="Name" value="' . escape($data['Name'] ?? '') . '" class="form-control">
    </div>

    <label>Address 1:</label>
    <div class="form-group">
        <input type="text" placeholder="Address 1" name="Street1" value="' . escape($data['Street1'] ?? '') . '" class="form-control">
    </div>

    <label>Address 2:</label>
    <div class="form-group">
        <input type="text" placeholder="Address 2" name="Street2" value="' . escape($data['Street2'] ?? '') . '" class="form-control">
    </div>

    <label>City Name:</label>
    <div class="form-group">
        <input type="text" placeholder="City Name" name="CityName" value="' . escape($data['CityName'] ?? '') . '" class="form-control">
    </div>

    <label>State:</label>
    <div class="form-group">
        <input type="text" placeholder="State" name="StateOrProvince" value="' . escape($data['StateOrProvince'] ?? '') . '" class="form-control">
    </div>

    <label>Postal Code:</label>
    <div class="form-group">
        <input type="text" placeholder="Postal Code" name="PostalCode" value="' . escape($data['PostalCode'] ?? '') . '" class="form-control">
    </div>

    <label>Shipping Cost:</label>
    <div class="form-group">
        <input type="text" placeholder="Shipping Cost" name="ShippingServiceCost" value="' . escape($dataO['ShippingServiceCost'] ?? '') . '" class="form-control">
    </div>

    <input type="hidden" name="Country" value="' . escape($data['Country'] ?? '') . '"/>
    <input type="hidden" name="CountryName" value="' . escape($data['CountryName'] ?? '') . '"/>
    <input type="hidden" name="Phone" value="' . escape($data['Phone'] ?? '') . '"/>
    <input type="hidden" name="AddressID" value="' . escape($data['AddressID'] ?? '') . '"/>
    <input type="hidden" name="AddressOwner" value="' . escape($data['AddressOwner'] ?? '') . '"/>
';


$conn->close();
echo $html;

}


if(isset($_POST['transferPayout'])){
    $id = $_POST['transferPayout'];
    
    $type = $_POST['payoutType'];
    
    if($type == 'auto'){
        $totalPayouts = $conn->query("select SUM(amount) as amount from app_auto_payouts where account_id = '$id'")->fetch_assoc()['amount']+0;
        
        $options = '<option value="auto" selected>Auto Payouts</option><option value="manual">Manual Payouts</option>';
    }else{
        $totalPayouts = $conn->query("select SUM(amount) as amount from app_payouts where account_id = '$id'")->fetch_assoc()['amount']+0;
        $options = '<option value="auto" >Auto Payouts</option><option value="manual" selected>Manual Payouts</option>';
    }
    
    
    $html = '';
    
  
    $html .= '
                    <label>Payout Type: </label>
                    <div class="form-group">
                        <select name="type" id="type" class="form-control" onchange="transferPayout('.$id.', this.value, 0);">
                            '.$options.'
                        </select>
                    </div>
                    
                    <label>Transfer Amount: </label>
                    <div class="form-group">
                        <input type="number" step="0.01" placeholder="amount" name="amount" value="'.$totalPayouts.'" class="form-control">
                    </div>
                    
                     <input type="hidden" name="account_id" value="'.$id.'"/>';
                    $conn->close(); 
    echo $html;
}


if(isset($_GET['postEditAddress'])){
   
    $editId = $_POST['editId'];
    $order_item_id = $_POST['order_item_id'];
    $SKU = $_POST['SKU'];
    $QuantityPurchased = $_POST['QuantityPurchased'];
    $Price = $_POST['Price'];
    $ShippingServiceCost = $_POST['ShippingServiceCost'];
    
    
    for($n = 0, $i = count($order_item_id); $n < $i; $n++){
        $conn->query("update app_order_items SET SKU = '{$SKU[$n]}', QuantityPurchased = '{$QuantityPurchased[$n]}', Price = '{$Price[$n]}' WHERE id = '{$order_item_id[$n]}'");
    }
    
    unset($_POST['order_item_id']);
    unset($_POST['editId']);
    unset($_POST['SKU']);
    unset($_POST['Price']);
    unset($_POST['QuantityPurchased']);
    unset($_POST['ShippingServiceCost']);
    $ShippingAddress = json_encode($_POST);
    $conn->query("update app_orders set ShippingAddress = '$ShippingAddress', PostCode = '{$_POST['PostalCode']}', ShippingServiceCost = '$ShippingServiceCost' where ID = '$editId'");
    addSystemLog($conn, 'ORDER UPDATED', "Order with id ($editId) has been updated", "$editId");
    $conn->close();
    echo "SUCCESS";
}

if(isset($_POST['inhide_outofstock'])){
    $id = $_POST['inhide_outofstock'];
    $val = $_POST['value'];
    $conn->query("update app_items set inhide_outofstock = '$val' where id = '$id'");
    $conn->close();
    echo 'SUCCESS';
}
if(isset($_POST['inhide_lowstock'])){
    $id = $_POST['inhide_lowstock'];
    $val = $_POST['value'];
    $conn->query("update app_items set inhide_lowstock = '$val' where id = '$id'");
    $conn->close();
    echo 'SUCCESS';
}

if(isset($_POST['inhide_reorder'])){
    $id = $_POST['inhide_reorder'];
    $val = $_POST['value'];
    $conn->query("update app_items set inhide_reorder = '$val' where id = '$id'");
    $conn->close();
    echo 'SUCCESS';
}

if(isset($_GET['getPrice'])){
    $sid = $_POST['sid'];
    $sku = $_POST['item_id'];
    if($sid>0){
        $name_id = $conn->query("SELECT * FROM app_accounts where id = '$sid'")->fetch_assoc()['price_tag'];
    }else{
        $name_id = 1;
    }
    $UnitPrice = getPriceFromSKU($conn, $sku, $name_id);
    $conn->close();
    echo $UnitPrice;
}

if(isset($_POST['updateqty'])){
    $qty = $_POST['updateqty'];
    $item_id = $_POST['item_id'];
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');
    $description = 'Added Stock from ALL IN ONE - ITEM PAGE';
    $check_today_id = $conn->query("SELECT * FROM app_stocks where DATE(datetime) = '$today' && description = '$description' && item_id = '$item_id'");
    $item = $conn->query("SELECT * FROM app_items WHERE id = '$item_id'")->fetch_assoc();
    $remain_stock = getStock($conn, $item['id'], $item['sku']);
    if($check_today_id->num_rows > 0){
        $today_stock = $check_today_id->fetch_assoc();
        $added_qty = $today_stock['qty'];
        $remain_stock = $remain_stock - $added_qty;
        $qty = $qty - $remain_stock;
        if($qty == 0){
            $conn->query("delete from app_stocks where DATE(datetime) = '$today' && description = '$description' && item_id = '$item_id'");
        }else{
            $conn->query("update app_stocks set qty = '$qty' where DATE(datetime) = '$today' && description = '$description' && item_id = '$item_id'");
        }
        
    }else{
        $qty = $qty - $remain_stock;
        $conn->query("insert into app_stocks set item_id = '$item_id', qty = '$qty', description = '$description', datetime = '$now'");
    }
    
    addSystemLog($conn, 'STOCK UPDATED', "Item with id ($item_id) quantity has been updated from AIO Items Page", "");
    $conn->close();
    echo 'STOCK UPDATED';
}

if(isset($_FILES['item_image']) && !empty($_FILES['item_image']['name']) && isset($_POST['item_id'])){
      $errors= array();
      $file_name = strtotime(date('Y-m-d H:i:s')).$_FILES['item_image']['name'];
      $file_size =$_FILES['item_image']['size'];
      $file_tmp =$_FILES['item_image']['tmp_name'];
      $file_type=$_FILES['item_image']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['item_image']['name'])));
      
      $extensions= array("jpeg","jpg","png", "gif");
      
      if(in_array($file_ext,$extensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file.";
      }
      
      
      if(empty($errors)==true){
         move_uploaded_file($file_tmp,"../items_image/".$file_name);
         
      }else{
        
        $data['status'] = "error";
        $data['msg'] = implode('|',$errors);
        echo json_encode($data);
        // header("location: add_item.php");
        $conn->close();
        exit();
      }
      
      $conn->query("update app_items set image = '$file_name' where id = '{$_POST['item_id']}'");
      
      addSystemLog($conn, 'ITEM UPDATED', "Image updated for item with id (".$_POST['item_id'].")", "");
      $conn->close();
      $data['status'] = "success";
      $data['msg'] = 'https://d-orders.co.uk/items_image/'.$file_name;
      echo json_encode($data);
   }
   
 if(isset($_POST['add_price_tag'])){
    $name = $conn->real_escape_string($_POST['name']);
    $check_name = $conn->query("select * from app_sellprices_name where name = '$name'");
    if($check_name->num_rows > 0){
        $data['status'] = "error";
        $data['msg'] = 'Name exists in database.';
        $conn->close();
        echo json_encode($data);
        exit();
    }
    $conn->query("insert into app_sellprices_name set name = '$name'");
    $name_id = $conn->insert_id;
    $items = $conn->query("SELECT * FROM `app_items`");
    while($item = $items->fetch_assoc()){
        $conn->query("insert into app_sellprices_amount set item_id = '{$item['id']}', name_id = '$name_id', price = '{$item['price']}', type='1'");
    }
    
    $packages = $conn->query("SELECT * FROM app_packages");
    while($package = $packages->fetch_assoc()){
        $conn->query("insert into app_sellprices_amount set item_id = '{$package['id']}', name_id = '$name_id', price = '{$package['price']}', type='2'");
    }
    
    addSystemLog($conn, 'PRICE TAG ADDED', "New Price Tag with name ($name) has been added", "");
    $data['status'] = "success";
    $data['msg'] = 'Name added successfully.';
    $conn->close();
    echo json_encode($data);
    exit();
    
}
   
if(isset($_POST['edit_item'])){
    $editId = $_POST['edit_item'];
    $name = $conn->real_escape_string($_POST['name']);
    $sku = $conn->real_escape_string($_POST['sku']);
    $old_sku = $conn->real_escape_string($_POST['old_sku']);
    $description = $conn->real_escape_string($_POST['description']);
    $item_type = $conn->real_escape_string($_POST['item_type']);
    $stock_threshold = $conn->real_escape_string($_POST['stock_threshold']);
    $order_threshold = $conn->real_escape_string($_POST['order_threshold']);
    $packing_size_id = $conn->real_escape_string($_POST['packing_size_id']);
    $reference = $conn->real_escape_string($_POST['reference']);
    
    $price = $_POST['price'];
    $name_id = $_POST['name_id'];
    
    $check_sku = $conn->query("Select * from app_items where sku = '$sku' && id <> $editId")->num_rows;
    $check_sku_package = $conn->query("Select * from app_packages where sku = '$sku'")->num_rows;
    
    if($check_sku > 0 || $check_sku_package > 0){
       
        $data['status'] = "error";
        $data['msg'] = "SKU already exists.";
        $conn->close();
        echo json_encode($data);
        exit();
    }
     $st = $price[0];
    $conn->query("update app_items set sku = '$sku', name = '$name', price = '$st', description = '$description', item_type='$item_type', reference = '$reference', packing_size_id = '$packing_size_id', stock_threshold='$stock_threshold', order_threshold='$order_threshold' where id = '$editId'");
    
    for ($i = 0, $n = count($price); $i < $n; $i++) {
        if(!empty($price[$i])){
        $check_price_tag = $conn->query("select * from app_sellprices_amount where name_id = '{$name_id[$i]}' && item_id = '$editId' && type = '1'");
        if($check_price_tag->num_rows > 0){
            $conn->query("update app_sellprices_amount set price = '{$price[$i]}' where name_id = '{$name_id[$i]}' && item_id = '$editId'  && type = '1'");
           
        }else{
            $conn->query("insert into app_sellprices_amount set item_id = '$editId', name_id = '{$name_id[$i]}', price = '{$price[$i]}', type = '1'");
        }
        }
        
    }
    
    if($sku != $old_sku){
        $conn->query("update app_order_items set SKU = '$sku' where SKU = '$old_sku'");
    }
    
    
    addSystemLog($conn, 'ITEMS UPDATE', "Item with sku ($sku) has been updated", "");
    $conn->close();
    $data['status'] = "success";
    $data['msg'] = "Item has been updated.";
    echo json_encode($data);
    exit();
    
}
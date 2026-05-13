<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(isset($_GET['getLiveStock'])){
    $skus = $_POST['sku_list'];
    $skuDataList = array();
    if(is_array($skus)){
        foreach($skus as $sku){
            $skuData = array();
            $skuData['sku']=$sku;
            $item = $conn->query("SELECT * FROM app_items WHERE sku = '$sku'");
            $price = getPriceFromSKU($conn, $sku, 1);
            if($item->num_rows > 0){
                $item = $item->fetch_assoc();
                
                $remain_stock = getStock($conn, $item['id'], $item['sku']);
                if($remain_stock > 0){
                    $skuData['qty']=$remain_stock;
                    $skuData['price']= $price;
                }else{
                    $skuData['qty']=0;
                    $skuData['price']=$price;
                }
                
            }else{
                $skuData['qty']=0;
                $skuData['price']=$price;
            }
            
            $skuDataList[] = $skuData;
        }
    }
    
    echo json_encode($skuDataList);
}

<?php 
// require_once "inc/config.php";
// require_once "inc/functions.php";

// $file = fopen("csvRateUpdateNew.csv","r");

// while (($data = fgetcsv($file)) !== FALSE)
// {
//     $item_id = $data[0];
//     $dPrice = $data[2];
//     $vatPrice = $data[3];
//     $fbaPrice = $data[4];
    
    
//     $conn->query("update app_items set price = '$dPrice' where id = '$item_id'");
//     // $conn->query("update app_sellprices_amount set price = '$dPrice' where name_id = '1' && item_id = '$item_id'  && type = '1'");
//     // $conn->query("update app_sellprices_amount set price = '$vatPrice' where name_id = '2' && item_id = '$item_id'  && type = '1'");
//     // $conn->query("update app_sellprices_amount set price = '$fbaPrice' where name_id = '5' && item_id = '$item_id'  && type = '1'");
// }
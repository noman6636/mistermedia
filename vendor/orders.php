<?php
  include __DIR__ . '/vendor/autoload.php';





  $config = [
    'http' => [
      'verify' => false,    //<--- NOT SAFE FOR PRODUCTION
      'debug' => false       //<--- NOT SAFE FOR PRODUCTION
    ],
    'refresh_token' => 'Atzr|IwEBIBmpaElSI5Q9Cfy-HcDh5-yPUXp8YHLGTDYuT0K1ifDUL3LrCEi8cUjGXxr-KEXD6s3O9BRxjzY2lRC_67Gn1WsrcgkKlnyOUZCMvGJG67k_X4q7Klv8Jf7WFNgUoN0ksh4CvMn-m65blkV6YAt9DHYvafIXSVEWVMUlMVW1AUDNtxHVDsiSM6X9V24ab4y85YC2oAEODPN_btrQ2CbAxZLw2DW8j3lHNqPpbFVCrYXfWCSdOZeNpJtIzPkFFzKxua5L80cfvHQzc93cCQ4RDZmzc5D2ew98uURzNyzBXA9w4Yt_7T8IP964fl2YmJ21tV8',
    'client_id' => 'amzn1.application-oa2-client.28e01aa7a20b44ee8d5d4bb28229011d',
    'client_secret' => '5608f5a7ad30d5f16ea7706c144850abb03cdd949ced3efb8dceddf05b144edf',
    'access_key' => 'AKIA4TEEAPEZ5BYFCRGV',
    'secret_key' => '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',
    'role_arn' => 'arn:aws:iam::865712765235:role/SPAPI' ,
    'region' => 'us-west-2',
    'host' => 'sellingpartnerapi-fe.amazon.com'
  ];



  //Create token storage which will store the temporary tokens
  $tokenStorage = new DoubleBreak\Spapi\SimpleTokenStorage(__DIR__ .'/aws-tokens.txt');

  //Create the request signer which will be automatically used to sign all of the
  //requests to the API
  $signer = new DoubleBreak\Spapi\Signer();

  //Create Credentials service and call getCredentials() to obtain
  //all the tokens needed under the hood
  $credentials = new DoubleBreak\Spapi\Credentials($tokenStorage, $signer, $config);
  $cred = $credentials->getCredentials();


  /** The application logic implementation **/
  // $contentType = 'text/tab-separated-values; charset=UTF-8';
  //$contentType = 'text/xml; charset=UTF-8';

// create feed document
$feedClient = new \DoubleBreak\Spapi\Api\Orders($cred, $config);


$params=["MarketplaceIds"=>"A19VAU5U5O7RUS","CreatedAfter"=>"2021-01-05T08:15:30-05:00"];

// $orders = $feedClient->getOrders($params);
echo "<pre>"; 
print_r($orders);


// $orders = $feedClient->getOrderItems("503-1251457-9215833");
// echo "<pre>"; 
// print_r($orders);



// $catalogClinet = new DoubleBreak\Spapi\Api\CatalogItems($cred, $config);

//   echo "here";
//   //Check the catalog info for B074Z9QH5F ASIN
//   $result = $catalogClinet->getCatalogItem('1472947320', [
//     'MarketplaceId' => 'A19VAU5U5O7RUS',
//   ]);
// echo "<pre>";
// print_r($result);
// exit;






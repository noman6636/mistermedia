<?php
/*  � 2013 eBay Inc., All Rights Reserved */ 
/* Licensed under CDDL 1.0 -  http://opensource.org/licenses/cddl1.php */
    //show all errors
    error_reporting(E_ALL);

    // these keys can be obtained by registering at http://developer.ebay.com
    
    $production         = true;   // toggle to true if going against production
    // $compatabilityLevel = 1113;    // eBay API version
    $compatabilityLevel = 717;    // eBay API version
    
    
        $devID = '67e9ba9a-ed5b-468c-a361-3175860bbc07';   // these prod keys are different from sandbox keys
        $appID = 'Smartfut-EcomsTra-PRD-0a70f81c3-5683cf7c';
        $certID = 'PRD-a70f81c32c7a-830b-4fae-8b0d-e55a';
        //set the Server to use (Sandbox or Production)
        $serverUrl = 'https://api.ebay.com/ws/api.dll';      // server URL different for prod and sandbox
        //the token representing the eBay user to assign the call with

        function XML2Array($xmlSrting){
            $xml = simplexml_load_string($xmlSrting);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            return $array;
        }
    
    
?>
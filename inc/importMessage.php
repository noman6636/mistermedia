<?php 

function getMsgDetailEbay($conn,$siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $folder, $messageId){
   
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
    $requestXmlBody .= '<GetMyMessages xmlns="urn:ebay:apis:eBLBaseComponents">';
     $requestXmlBody .= "<DetailLevel>ReturnMessages</DetailLevel>";
     $requestXmlBody .= "<FolderID>$folder</FolderID>";
     $requestXmlBody .= "<MessageIDs><MessageID>$messageId</MessageID></MessageIDs>";
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= '</GetMyMessages>';
    
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);
    if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
        echo '<p>Error sending request 22</p><br>';
        $res = array('error'=>1);
    }else{
        $res = XML2Array($responseXml);
    }
    
    return $res;
    
}


function getImportMsgEbay($conn,$siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $folder){
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
    $requestXmlBody .= '<GetMyMessages xmlns="urn:ebay:apis:eBLBaseComponents">';
     $requestXmlBody .= "<StartTime>$CreateTimeFrom</StartTime><EndTime>$CreateTimeTo</EndTime>";
     $requestXmlBody .= "<DetailLevel>ReturnHeaders</DetailLevel>";
     $requestXmlBody .= "<FolderID>$folder</FolderID>";
    //  $requestXmlBody .= "<MessageIDs><MessageID>172956546580</MessageID></MessageIDs>";
     
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= '</GetMyMessages>';
    
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);
    if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
        $value =  '<p>Error sending request 1</p><br>';
    }else{
        $res = XML2Array($responseXml);
        
       if($res['Ack'] == 'Success' || $res['Ack'] == 'Warning'){
           if(!isset($res['Messages']['Message'][1])){
               $res['Messages']['Message'] = array($res['Messages']['Message']);
           }
            foreach($res['Messages']['Message'] as $message){
                $check_msg = $conn->query("select * from app_messages where MessageID = '{$message['MessageID']}'");
                $blockedSender = array('csfeedback@go.ebay.com', 'eBay');
                    if($check_msg->num_rows == 0 && !in_array($message['Sender'], $blockedSender)){
                        $messageDetail = getMsgDetailEbay($conn, $siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $AccountID, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $folder, $message['MessageID']);
                        // print_r($messageDetail);
                        if(!array_key_exists("error",$messageDetail)){
                            
                            $msg = getMessageTextFromContent($messageDetail['Messages']['Message']['Content']);
                            $message = $messageDetail['Messages']['Message'];
                            $ReceiveDate = date('Y-m-d H:i:s', strtotime($message['ReceiveDate']));
                            
                            if($message['Read']){
                                $Read = 1;
                            }else{
                                $Read = 0;
                            }
                            
                            $query = "insert into app_messages set ";
                            $query .= "AccountID='$AccountID', ";
                            $query .= "Sender='{$message['Sender']}', ";
                            $query .= "SendToName='{$message['SendToName']}', ";
                            $query .= "Subject='{$message['Subject']}', ";
                            $query .= "MessageID='{$message['MessageID']}', ";
                            $query .= "ExternalMessageID='{$message['ExternalMessageID']}', ";
                            $query .= "Text='$msg', ";
                            $query .= "ReceiveDate='$ReceiveDate', ";
                            $query .= "Folder='$folder', ";
                            $query .= "MessageType='{$message['MessageType']}', ";
                            $query .= "ItemID='{$message['ItemID']}', ";
                            $query .= "ItemTitle='{$message['ItemTitle']}', ";
                            $query .= "ReadStatus='$Read'";
                            
                            $conn->query($query);
                        }
                        
                    }
            }
        }
        
    }
}

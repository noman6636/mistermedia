<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
require_once('inc/Keys.php');
require_once('inc/eBaySession.php');

if(isset($_REQUEST['revoke'])){
    unset($_SESSION['ebaySid']);
    unset($_SESSION['account_username']);
    echo "<script>
    window.opener.location = 'add_account.php';
    window.close();
</script>";
exit();
}

if(isset($_SESSION['ebaySid'])){
    
    if(isset($_GET['username'])){
        $_SESSION['account_username']=$_GET['username'];
        echo "<script>
        window.opener.location = 'add_ebay_account.php';
        window.close();
        </script>";
        exit();
    }
}

if(isset($_SESSION['renewToken'])){
    
    if(isset($_GET['username'])){
        $username = $_GET['username'];
        $sid = $_SESSION['rebaySid'];
        
        // $old_username = $conn->query("select * from app_accounts WHERE id = '{$_SESSION['renewToken']}'")->fetch_assoc()['account_username'];
        
        // if($username != $old_username){
        //     unset($_SESSION['rebaySid']);
        //     unset($_SESSION['renewToken']);
        //     echo 'Invalid account loggedin from ebay. Please login with same username. <a href="index.php">Go to Dashboard</a>';
        //     exit;
        // }
        
        $check_username = $conn->query("SELECT * FROM app_accounts WHERE account_username = '$username' && id <> '{$_SESSION['renewToken']}'");
        
        if($check_username->num_rows > 0){
            unset($_SESSION['rebaySid']);
            unset($_SESSION['renewToken']);
            echo 'This username is already integrated in system. <a href="index.php">Go to Dashboard</a>';
            exit; 
        }
        
        $siteID = 0;
        $verb = 'FetchToken';
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
        $requestXmlBody .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<SessionID>'.$sid.'</SessionID>';
        $requestXmlBody .= '</FetchTokenRequest>';

        $session = new eBaySession('', $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
            die('<P>Error sending request');
        }
        $res = XML2Array($responseXml);
        // print_r($res);
        // exit();
        if($res['Ack'] == 'Failure'){
          header("location: index.php");
          exit();
        }
        $token = $res['eBayAuthToken'];
        $expire_time =  date('Y-m-d H:i:s', strtotime($res['HardExpirationTime']));
        $conn->query("update app_accounts set account_username = '$username', auth_token = '$token', token_expire = '$expire_time', IsTokenInvalid = '0' WHERE id = '{$_SESSION['renewToken']}'");
        // echo $_SESSION['renewToken'];
        unset($_SESSION['rebaySid']);
        unset($_SESSION['renewToken']);
        header("location: index.php");
        exit();
    }
}

if(isset($_GET['renewToken'])){
   
    $uid = $_GET['renewToken'];
    $siteID = 0;
    $verb = 'GetSessionID';
    
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= '<RuName>Smartfutureking-Smartfut-EcomsT-rtqvzw</RuName>';
    $requestXmlBody .= '</GetSessionIDRequest>';
    
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession('', $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);
    if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
        die('<P>Error sending request');
    }
    $res = XML2Array($responseXml);
    $sid = $res['SessionID'];
    $_SESSION['rebaySid'] = $sid;
    $_SESSION['renewToken'] = $uid;
    // echo $_SESSION['renewToken'];
    // exit();
    
    header("location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Smartfutureking-Smartfut-EcomsT-rtqvzw&SessID=".$sid);
    exit();
}




$siteID = 0;
$verb = 'GetSessionID';

$requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
$requestXmlBody .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
$requestXmlBody .= '<RuName>Smartfutureking-Smartfut-EcomsT-rtqvzw</RuName>';
$requestXmlBody .= '</GetSessionIDRequest>';

//Create a new eBay session with all details pulled in from included keys.php
$session = new eBaySession('', $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);

//send the request and get response
$responseXml = $session->sendHttpRequest($requestXmlBody);
if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
    die('<P>Error sending request');
}
$res = XML2Array($responseXml);
$sid = $res['SessionID'];
$_SESSION['ebaySid'] = $sid;
header("location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Smartfutureking-Smartfut-EcomsT-rtqvzw&SessID=".$sid);
// print_r($res);

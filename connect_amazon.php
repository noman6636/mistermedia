<?php 
require_once "inc/config.php";
require_once "inc/functions.php";


if(isset($_REQUEST['revoke'])){
    unset($_SESSION['amz_selling_partner_id']);
    unset($_SESSION['amz_access_token']);
    unset($_SESSION['amz_refresh_token']);
    echo "<script>
    window.opener.location = 'add_account.php';
    window.close();
</script>";
exit();
}

if(isset($_SESSION['amazon_state'])){
    if(isset($_GET['spapi_oauth_code']) && $_SESSION['amazon_state'] == $_GET['state']){
        exit;
        unset($_SESSION['amazon_state']);
        $spapi_oauth_code = $_GET['spapi_oauth_code'];
        $selling_partner_id = $_GET['selling_partner_id'];
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.amazon.com/auth/o2/token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'version=beta&grant_type=authorization_code&code='.$spapi_oauth_code.'&client_id=amzn1.application-oa2-client.3ef4b863e6154e64a8e0d4edca56a0fa&client_secret=amzn1.oa2-cs.v1.6e5164119e71877213691a6d94a5a4ff481a6b5a936475cae1114474c128c692',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $response = json_decode($response, true);
        
        $_SESSION['amz_selling_partner_id'] = $selling_partner_id;
        $_SESSION['amz_access_token'] = $response['access_token'];
        $_SESSION['amz_refresh_token'] = $response['refresh_token'];
        // print_r($_SESSION);
        // exit;
       
        echo "<script>
        window.opener.location = 'add_amazon_account.php';
        window.close();
        </script>";
        exit;
    }
}




$state = 'DO-CONNECT-'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$_SESSION['amazon_state'] = $state;
header("location: https://sellercentral.amazon.com/apps/authorize/consent?application_id=amzn1.sp.solution.f901554c-24ab-4f56-8426-2da409484d51&state=$state&version=beta");
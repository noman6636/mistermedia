<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(37, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(!isset($_GET['MessageID'])){
    header("location: index.php");
    exit();
}

$message = $conn->query("SELECT * FROM app_messages WHERE MessageID = '{$_GET['MessageID']}'")->fetch_assoc();

$conn->query("update app_messages set ReadStatus = 1 where MessageID = '{$_GET['MessageID']}'");
$messageId = $message['ExternalMessageID'];

$userAccount = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc();

if($userAccount['account_username']==$message['Sender']){
    $RecipientID = $message['SendToName'];
}else{
    $RecipientID = $message['Sender'];
}


if(isset($_POST['reply_message'])){
    $msg = $_POST['message'];
    
   
    
    require_once('inc/Keys.php');
    require_once('inc/eBaySession.php');
    
    
    
    $siteID = 0;
    $verb = 'AddMemberMessageRTQ';
    
    $userToken = $userAccount['auth_token'];
    
    
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
    $requestXmlBody .= '<AddMemberMessageRTQ xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= "<MemberMessage><Body>$msg</Body><ParentMessageID>$messageId</ParentMessageID><RecipientID>$RecipientID</RecipientID></MemberMessage>";
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= '</AddMemberMessageRTQ>';
    
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);
    if (stristr($responseXml, 'HTTP 404') || $responseXml == ''){
        echo '<p>Error sending request</p><br>';
        exit;
    }else{
        $res = XML2Array($responseXml);
        // print_r($res);
        // exit;
        
        if($res['Ack']=='Success'){
            sleep(5);
            require_once('inc/importMessage.php');
            
            $CreateTimeFrom = gmdate("Y-m-d\TH:i:s",strtotime($res['Timestamp'])-240); //current time minus 30 minutes
            $CreateTimeTo = gmdate("Y-m-d\TH:i:s",strtotime($res['Timestamp'])+240);
            
            $verb = 'GetMyMessages';
            getImportMsgEbay($conn, $siteID, $verb, $CreateTimeFrom, $CreateTimeTo, $userToken, $_GET['account_id'], $devID, $appID, $certID, $serverUrl, $compatabilityLevel, 1);
            $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Your message has been sent successfully.</div></div>';
            header("location: view_message?MessageID=".$_GET['MessageID']."&account_id=".$_GET['account_id']);
            exit();
        }else{
            echo '<p>Error sending request</p><br>';
            exit;
        }
    }
    
    
    
}
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>View Message || D-Orders</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/extensions/toastr.min.css">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/semi-dark-layout.css">

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/charts/chart-apex.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/extensions/ext-component-toastr.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
<style>
    .table th, .table td {
            padding: 0.72rem 10px;
            font-size: 11px;
            vertical-align: middle;
        }
</style>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">View Message
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">
            <style>
                .accordion {
  width: 100%;
}

.accordion-item {
  
  
  margin: 1rem 0;
  border-radius: 0.5rem;
  box-shadow: 0 2px 5px 0 rgba(0,0,0,0.25);
}

.accordion-item-header {
  padding: 0.5rem 3rem 0.5rem 1rem;
  background-color: #0e83ba;
  color: white;
  min-height: 3.5rem;
  line-height: 1.25rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  position: relative;
  cursor: pointer;
}

.accordion-item-header::after {
  content: "\002B";
  font-size: 2rem;
  position: absolute;
  right: 1rem;
}

.accordion-item-header.active::after {
  content: "\2212";
}

.accordion-item-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
}

.accordion-item-body-content {
  padding: 1rem;
  line-height: 1.5rem;
  border-top: 1px solid;
  border-image: linear-gradient(to right, transparent, #34495e, transparent) 1;
}

@media(max-width:767px) {
  html {
    font-size: 14px;
  }
}
</style>

                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 style="color:#0e83ba">Subject : <?=$message['Subject'];?></h4>
                                    <h6>Item : <?=$message['ItemTitle'];?></h6>
                                    <div class="accordion">
                                      <div class="accordion-item">
                                        <div class="accordion-item-header">
                                          Reply Message
                                        </div><!-- /.accordion-item-header -->
                                        <div class="accordion-item-body">
                                          <div class="accordion-item-body-content">
                                             <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Message</label>
                                                            <textarea rows="6" class="form-control" name="message"  required></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="reply_message" value="1" />
                                                        <button type="submit" onclick="this.disabled=true; this.value='Submitting...'; this.form.submit();" class="btn btn-primary mr-1 mt-1">Submit Reply</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                            </form>
                                          </div>
                                        </div><!-- /.accordion-item-body -->
                                      </div><!-- /.accordion-item -->
                                    </div>
                                    
                                    <style>
                                        .ticket-reply {
                                            margin: 10px 0;
                                            padding: 0;
                                            border: 1px solid #cce4fc;
                                            background-color: #fff;
                                            width: 100%;
                                        }
                                        .ticket-reply .date {
    float: right;
    padding: 8px 10px;
    font-size: .8em;
}

.ticket-reply .user {
    padding: 5px 0;
     background-color: #f2f9ff;
}
.ticket-reply .user i {
    color: #3f3d59;
}

.ticket-reply .user i {
    float: left;
    font-size: 2.2em;
    padding: 2px 15px;
}
.ticket-reply .user .name {
    color: #3f3d59;
}

.ticket-reply .user .name {
    display: block;
    font-size: .9em;
    margin-left: 14px;
}
.ticket-reply .user .type {
    color: #3f3d59;
}

.ticket-reply .user .type {
    display: block;
    font-weight: 700;
    font-size: .8em;
}
.ticket-reply .message {
    padding: 12px 15px;
}
.message p {
    color: #828e98;
    font-size: 15px;
    line-height: 22px;
}
                                    </style>
                                    <?php 
                                    $messages = $conn->query("SELECT * FROM app_messages WHERE AccountID = '{$message['AccountID']}' && ((Sender='{$message['Sender']}' && SendToName='{$message['SendToName']}') || (Sender='{$message['SendToName']}' && SendToName='{$message['Sender']}')) ORDER BY ReceiveDate DESC");
                                    while($message = $messages->fetch_assoc()){ ?>
                                    <div class="ticket-reply markdown-content staff">
                                        <div class="date">
                                            <?=date('F, j Y H:i', strtotime($message['ReceiveDate']));?>
                                        </div>
                                        <div class="user">
                                            <span class="name">
                                                <?=$message['Sender'];?>
                                            </span>
                                        </div>
                                        <div class="message">
                                            <p><?=$message['Text'];?></p>             
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Row grouping -->


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

     <!-- BEGIN: Vendor JS-->
     <script src="app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!-- BEGIN: Page Vendor JS-->
    <script src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <script src="app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    
    <script src="app-assets/vendors/js/editors/quill/katex.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/highlight.min.js"></script>
    <script src="app-assets/vendors/js/editors/quill/quill.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="app-assets/js/core/app-menu.js"></script>
    <script src="app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="app-assets/js/scripts/tables/table-datatables-basic.js"></script>

    <script>
    
    const accordionItemHeaders = document.querySelectorAll(".accordion-item-header");

accordionItemHeaders.forEach(accordionItemHeader => {
   accordionItemHeader.addEventListener("click", event => {
    
     // Uncomment in case you only want to allow for the display of only one collapsed item at a time!
    
//     const currentlyActiveAccordionItemHeader = document.querySelector(".accordion-item-header.active");
//     if(currentlyActiveAccordionItemHeader && currentlyActiveAccordionItemHeader!==accordionItemHeader) {
//        currentlyActiveAccordionItemHeader.classList.toggle("active");
//        currentlyActiveAccordionItemHeader.nextElementSibling.style.maxHeight = 0;
//      }

     accordionItemHeader.classList.toggle("active");
     const accordionItemBody = accordionItemHeader.nextElementSibling;
     if(accordionItemHeader.classList.contains("active")) {
      accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + "px";
     }
     else {
       accordionItemBody.style.maxHeight = 0;
     }
    
   });
});
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });
       
    $(".dt-row-grouping-t").DataTable();
 $("#selectall").click(function () {
                var checkAll = $("#selectall").prop('checked');
                    if (checkAll) {
                        $(".case").prop("checked", true);
                    } else {
                        $(".case").prop("checked", false);
                    }
            });

            $(".case").click(function(){
                if($(".case").length == $(".case:checked").length) {
                    $("#selectall").prop("checked", true);
                } else {
                    $("#selectall").prop("checked", false);
                }

            });
            function viewInvoice(url, id){
                window.open(url, "Invoice # "+id, 'width=793.7, height=1122.52');
            }
  
    </script>
</body>
<!-- END: Body-->

</html>
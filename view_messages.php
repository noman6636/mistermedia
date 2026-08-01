<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
        exit();
}


if(!in_array(37, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(!isset($_GET['account_id']) || !isset($_GET['folder'])){
    header("location: index.php");
    exit();
}

$accountId = (int)$_GET['account_id'];
$folder = (int)$_GET['folder'];

if ($accountId <= 0 || !in_array($folder, array(0, 1), true)) {
    header("location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM app_accounts WHERE id = ?");
$stmt->bind_param("i", $accountId);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Account not found.</div></div>';
    header("location: index.php");
    exit();
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
    <title>Manage Messages || D-Orders</title>
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
                                    <li class="breadcrumb-item active">View Messages
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">


                <!-- Row grouping -->
                <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <form action="" id="allordersdata" method="POST">
                            <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title"><?= htmlspecialchars($account['account_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <div>
                                        <?php 
                                        if($folder==1){ ?>
                                            <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="?account_id=<?=$accountId;?>&folder=0" >Inbox Box</a>
                                       <?php }else{  ?>
                                            <a class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" href="?account_id=<?=$accountId;?>&folder=1" >Sent Box</a>
                                      <?php  }
                                        ?>
                                    </div>
                                    
                                </div>
                                <style>
                                    .tr-hover:hover{
                                        color: #1192D2 !important;
                                        cursor: pointer;
                                    }
                                </style>
                                <div class="card-datatable">
                                   <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <?php if($folder==0){ ?><th>From</th><?php } ?>
                                                <?php if($folder==1){ ?><th>To</th><?php } ?>
                                                <th>Subject</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead> 
                                        <tbody>
                                        <?php 
                                            $stmt = $conn->prepare("SELECT * FROM app_messages WHERE AccountID = ? AND Folder = ? ORDER BY ReceiveDate DESC");
                                            $stmt->bind_param("ii", $accountId, $folder);
                                            $stmt->execute();
                                            $messages = $stmt->get_result();
                                        
                                        $sn = 0;
                                        
                                        while($message = $messages->fetch_assoc()){
                                            $sn++; ?>
                                            
                                    	    <tr style="<?php if($message['ReadStatus']==0){ echo 'font-weight:bold;color:black'; } ?>" onclick="window.location.href='view_message.php?MessageID=<?= urlencode($message['MessageID']); ?>&account_id=<?=$accountId;?>'"  class="tr-hover">
                                                <td><?=$sn; ?></td>
                                                <?php if($folder==0){ ?><td><?php echo htmlspecialchars($message['Sender'], ENT_QUOTES, 'UTF-8'); ?></td></th><?php } ?>
                                                <?php if($folder==1){ ?><td><?php echo htmlspecialchars($message['SendToName'], ENT_QUOTES, 'UTF-8'); ?></td></th><?php } ?>
                                                <td><?php echo htmlspecialchars($message['Subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo date('F j, Y', strtotime($message['ReceiveDate'])); ?></td>
                                                
                                               
                                            </tr>
                                            
                                        <?php }
                                        $stmt->close(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </form>
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
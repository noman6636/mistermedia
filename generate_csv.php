<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}
if(!in_array(11, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $file = $conn->query("select * from csv_files where id = '$id'")->fetch_assoc();
    @unlink('csv_files/'.$file['filename']);
    $conn->query("delete from csv_files where id = '$id'");
    
    addSystemLog($conn, 'CSV DELETED', "CSV File (".$file['filename'].") has been deleted", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Your file has been deleted.</div></div>';
    header("location: generate_csv.php");
    exit();
}

if(isset($_POST['generate'])){
$whereQuery = "WHERE ID <> 0";
    
    if($_POST['AccountID'] != 'all'){
       $whereQuery .= " && AccountID = '{$_POST['AccountID']}'";
       $username = $conn->query("select * from app_accounts where id = '{$_POST['AccountID']}'")->fetch_assoc()['account_name'];
       $filename = $username.'_';
    }else{
        $filename = 'All_';
    }
    if(isset($_POST['frmDate']) && !empty($_POST['frmDate'])){
        $frmDate = date('Y-m-d', strtotime($_POST['frmDate']));
        $whereQuery .= " && DATE(CreatedTime) >= '$frmDate'";
        $filename .= $frmDate.'_';
    }
    if(isset($_POST['toDate']) && !empty($_POST['toDate'])){
        $toDate = date('Y-m-d', strtotime($_POST['toDate']));
        $whereQuery .= " && DATE(CreatedTime) <= '$toDate'";
        $filename .= $toDate.'.csv';
    }
   
    $data=array();
        $headKeys=array();
        $keysOnly = $settings['csv_settings'];
        $keysOnlyDb = $keysOnly;
        $keysOnlyDb = str_replace(array('FullName,', 'AddressLine1,', 'AddressLine2,', 'City,', 'Country,', 'PhoneNo,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb = str_replace(array('ItemID,', 'SKU,', 'ItemTitle,', 'ConditionDisplayName,', 'QuantityPurchased,', 'SKUQTY,'), array('', '', '', '', '', ''), $keysOnlyDb);
        $keysOnlyDb .= ", ShippingAddress";
        
        
        
        
        
        // echo $keysOnly;
        // exit;
        $query = "select OrderID, $keysOnlyDb from app_orders $whereQuery ORDER BY ID DESC";
        $query = $conn->query($query);
        while($row = $query->fetch_assoc()){
            
                $shipa = json_decode($row['ShippingAddress'], true);
                
        $itemsList = $conn->query("SELECT * FROM app_order_items WHERE OrderID = '{$row['OrderID']}'");
        $showSKUQTY = '';
        $showItemID = '';
        $showSKU = '';
        $showItemTitle = '';
        $showConditionDisplayName = '';
        $showQuantityPurchased = '';
        
        while($item = $itemsList->fetch_assoc()){
            $showSKUQTY .= $item['QuantityPurchased']." x ".$item['SKU']."\r\n";
            $showItemID .= $item['ItemID']."\r\n";
            $showSKU .= $item['SKU']."\r\n";
            $showItemTitle .= $item['ItemTitle']."\r\n";
            $showConditionDisplayName .= $item['ConditionDisplayName']."\r\n";
            $showQuantityPurchased .= $item['QuantityPurchased']."\r\n";
        }
        if (strpos($keysOnly, 'SKUQTY') !== false) {
           $row['SKU_QTY'] = $showSKUQTY;
        }
        
        if (strpos($keysOnly, 'ItemID') !== false) {
           $row['ItemID'] = $showItemID;
        }
        
        if (strpos($keysOnly, 'SKU') !== false) {
           $row['SKU'] = $showSKU;
        }
        
        if (strpos($keysOnly, 'ItemTitle') !== false) {
           $row['ItemTitle'] = $showItemTitle;
        }
        
        if (strpos($keysOnly, 'ConditionDisplayName') !== false) {
           $row['ConditionDisplayName'] = $showConditionDisplayName;
        }
        
        if (strpos($keysOnly, 'QuantityPurchased') !== false) {
           $row['QuantityPurchased'] = $showQuantityPurchased;
        }
                
                unset($row['ShippingAddress']);
                foreach($shipa as $key => $value){
                    if($key == 'Name' && (strpos($keysOnly, 'FullName') !== false)){
                        if(!is_array($value)){ $row['Name'] = $value; }else{ $row['Name'] = ''; }
                    }
                    
                    if($key == 'Street1' && (strpos($keysOnly, 'AddressLine1') !== false)){
                        if(!is_array($value)){ $row['Street1'] = $value; }else{ $row['Street1'] = ''; }
                    }
                    
                    if($key == 'Street2' && (strpos($keysOnly, 'AddressLine2') !== false)){
                        if(!is_array($value)){ $row['Street2'] = $value; }else{ $row['Street2'] = ''; }
                    }
                    
                    if($key == 'CityName' && (strpos($keysOnly, 'City') !== false)){
                        if(!is_array($value)){ $row['CityName'] = $value; }else{ $row['CityName'] = ''; }
                    }
                    
                    if($key == 'CountryName' && (strpos($keysOnly, 'Country') !== false)){
                        if(!is_array($value)){ $row['CountryName'] = $value; }else{ $row['CountryName'] = ''; }
                    }
                    if($key == 'Phone' && (strpos($keysOnly, 'PhoneNo') !== false)){
                       if(!is_array($value)){ $row['Phone'] = $value; }else{ $row['Phone'] = ''; }
                    }
                    // $row['ShippingAddress'] .= $key.": ".$value."\r\n";
                }
            
            if(in_array('AccountID', $csvArray)){
                $row['AccountID'] =  $conn->query("select * from app_accounts where id = '{$row['AccountID']}'")->fetch_assoc()['account_name'];
            }
            
    
            $data[]=$row;
        }
    
 @unlink('csv_files/'.$filename);
 $output = fopen('csv_files/'.$filename, 'w'); 

     

$keysPut = 0;
foreach($data as $product) {
    if($keysPut == 0){
       fputcsv($output, array_keys($product));  
       $keysPut = 1;
    }
    fputcsv($output, $product);  
    
}
fclose($output);

$check_file = $conn->query("select * from csv_files where filename = '$filename'");
if($check_file->num_rows == 0){
    $today = date('Y-m-d H:i:s');
    $conn->query("insert into csv_files set filename = '$filename', datetime = '$today'");
    addSystemLog($conn, 'CSV CREATED', "CSV File ($filename) has been created", "");
}

header("location: generate_csv.php");
exit;
    
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
    <title>Generate CSV || D-Orders</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
    /* ===== GLOBAL MOBILE RESPONSIVE FIX ===== */

/* Make tables scrollable on mobile */
.card-datatable {
    width: 100%;
    overflow-x: auto;
}

/* Prevent table breaking */
.table {
    min-width: 800px;
}

/* Fix long text wrapping */
.table td, .table th {
    white-space: nowrap;
}

/* Buttons spacing */
.card-header .btn {
    margin-bottom: 5px;
}

/* ===== TABLE FONT OPTIMIZATION ===== */
@media (max-width: 1200px) {
    .table th, .table td {
        font-size: 11px;
        padding: 6px;
    }
}

@media (max-width: 992px) {
    .table th, .table td {
        font-size: 10px;
        padding: 5px;
    }
}

@media (max-width: 768px) {
    .table th, .table td {
        font-size: 9px;
        padding: 4px;
    }
}

/* ===== FORM + FILTER RESPONSIVE ===== */
@media (max-width: 768px) {

    .content-header .row,
    form .row {
        display: flex;
        flex-direction: column;
    }

    form .col-sm-3,
    form .col-sm-2,
    form .col-sm-5 {
        width: 100%;
        margin-bottom: 10px;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start !important;
    }

    .card-header .btn {
        width: 100%;
        margin-top: 5px;
    }
}

/* ===== EXTRA SMALL DEVICES ===== */
@media (max-width: 576px) {

    .table {
        min-width: 700px;
    }

    .card-title {
        font-size: 14px;
    }

    .breadcrumb {
        font-size: 12px;
    }

    .btn {
        font-size: 12px;
        padding: 6px 10px;
    }
}

/* ===== ULTRA SMALL DEVICES ===== */
@media (max-width: 400px) {

    .table {
        min-width: 600px;
    }

    .card-title {
        font-size: 13px;
    }

    .btn {
        font-size: 11px;
        padding: 5px 8px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
    <?php include("header.php"); ?>
    <style>
        .table th, .table td {
            padding: 0.72rem 10px;
            font-size: 10px;
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
                                    <li class="breadcrumb-item active">Orders
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
                            <form action="" method="POST" style="margin-bottom: 10px;">
                                <div class="row">
                                    <div class="col-sm-3 col-12">
                                        <select name="AccountID" class="form-control" required>
                                            <option value="all">All Accounts</option>
                                            <?php $accounts = $conn->query("select * from app_accounts order by account_name asc");
                                            while($row = $accounts->fetch_assoc()){ ?>
                                            <option value="<?php echo $row['id']; ?>" <?php if(isset($_GET['AccountID']) && $_GET['AccountID'] == $row['id']) { echo 'selected'; } ?>><?php echo $row['account_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2 col-12" >
                                        <input type="date" class="form-control" name="frmDate" value="<?php if(isset($_GET['frmDate'])){ echo $_GET['frmDate']; } ?>" required />
                                    </div>
                                    <div class="col-sm-2 col-12" >
                                        <input type="date" class="form-control" name="toDate" value="<?php if(isset($_GET['toDate'])){ echo $_GET['toDate']; } ?>" required />
                                    </div>
                                    <div class="col-sm-5 col-12">
                                        <input name="generate" value="1" type="hidden">
                                        <button type="submit" class="btn btn-primary">Generate</button>
                                        
                                        
                                    </div>
                                </div>
                            </form>
                           
                        </div>
                    </div>
                    <script>
    function submitPrint(val){
        document.getElementById("labeltype").value=val;
        var form = document.getElementById("allordersdata");
        form.submit();
    }
</script>
                </section>
                 <section id="row-grouping-datatable">
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Files</h4>
                                </div>
                                <script>
    function submitPrint(val){
        document.getElementById("labeltype").value=val;
        var form = document.getElementById("allordersdata");
        form.submit();
    }
</script>
                                <div class="card-datatable">
                                    <form action="" id="allordersdata" method="POST">
                        <input id="labeltype" value="0" type="hidden" name="labeltype"; />
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th style="width:10%">SN</th>
                                                <th style="width:45%">File</th>
                                                <th style="width:15%">Date/Time</th>
                                                
                                                <th style="width:20%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $query = "select * from csv_files ORDER BY ID DESC";
                                     
                                        $files = $conn->query($query);
                                        $sn = 0;
                                        while($file = $files->fetch_assoc()){
                                            
                                            $sn++; ?>
                                    	    <tr>
                                    	        <td><?php echo $sn; ?></td>
                                                <td><?php echo $file['filename']; ?></td>
                                                <td><?php echo $file['datetime']; ?></td>
                                               
                                                <td><a href="csv_files/<?php echo $file['filename']; ?>" class="btn btn-primary" downlaod>Download</a>
                                                <a href="?delete=<?php echo $file['id']; ?>" class="btn btn-danger">Delete</a></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    </form>
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
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>


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
        
        $(function() {
    $('#select_all').change(function(){
        console.log("Hi");
    	
        var checkboxes = $('#allordersdata').find(':checkbox').not(":disabled");
     
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });
});
//SELECT ALL FUNCITON BY MARKET
$(function() {
    $('input[id^="market_"]').change(function(){
        
    	var inputId = $(this).attr("id");
    	console.log("HI IS"+ inputId);
        var checkboxes = $('#'+inputId+' td').find(':checkbox').not(":disabled");
        
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true).closest('td').parent().addClass('highlight_row');
        } else {
          checkboxes.prop('checked', false).closest('td').parent().removeClass('highlight_row');
        }
    });
});
//CHECKBOX COUNT
$(function() {
    $('input[type="checkbox"]').change(function(){
		var numberNotChecked = $('input[name="aorder[]"]').filter(':checked').length;
		$("#ocount").html(numberNotChecked);
    });
});
        
        
    function printDiv(divName) {
         var printContents = document.getElementById(divName).innerHTML;
         var originalContents = document.body.innerHTML;
         document.body.innerHTML = printContents;
        //  window.print();
        //  document.body.innerHTML = originalContents;
    }
    
    function exportReportToExcel(divName) {
      let table = document.getElementById("ptbl"); // you can use document.getElementById('tableId') as well by providing id to the table tag
      TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
        name: `export.xlsx`, // fileName you could use any name
        sheet: {
          name: 'Sheet 1' // sheetName
        }
      });
    };
    
     $(".dt-row-grouping-t").DataTable({
            "bPaginate": false, //hide pagination
            "bInfo": false, // hide showing entries
        });
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
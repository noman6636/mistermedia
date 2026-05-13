<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
$time_start = microtime(true);



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
    <title>Statistics || IConnect</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">
     <?php include("header.php"); ?>

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
                                    <li class="breadcrumb-item active">Statistics
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">
                <?php if(isset($_GET['items'])){ ?>
                
                 <section id="page-account-settings" style="margin-top:20px">
                      
                    <?php 
                    
                                        $reorders = array();
                                        $lowstock = array();
                                        $outofstock = array();
                                        $itemsList = array();
                                        $items = $conn->query("select * from app_items where deleted = 0 order by sku asc");
                                        $sn = 0;
                                        $today = date('Y-m-d');
                                        $total_qty;
                                        $total_amount;
                                        $total_available_stock_amount = 0;
                                        $total_upcoming_stock_amount = 0;
                                        while($item = $items->fetch_assoc()){
                                            $itemsArray = array();
                                            if($item['item_type']==1){
                                                $remain_stock = getStock($conn, $item['id'], $item['sku']);
                                                $upcoming_stock = (int) $conn->query("Select SUM(qty) as qty from app_purchase_detail where item_id = '{$item['id']}' && status = 0 && purchase_id not in (105, 129, 130)")->fetch_assoc()['qty']+0;
                                                $cost_price = $conn->query("SELECT * FROM app_sellprices_amount WHERE item_id = '{$item['id']}' && name_id = '12' && type = '1'")->fetch_assoc()['price']+0;
                                                
                                            }else{
                                                $remain_stock = 0;
                                                $upcoming_stock = 0;
                                                $cost_price = 0;
                                                
                                            }
                                            
                                            
                                            $total_available_stock_amount += ($remain_stock*$cost_price);
                                            $total_upcoming_stock_amount += ($upcoming_stock*$cost_price);
                                            $sn++;
                                            $itemsArray['id'] = $item['id'];
                                            $itemsArray['item'] = $item['sku'];
                                            $itemsArray['qty']  = $remain_stock;
                                            $itemsArray['sku']  = $item['sku'];
                                            $itemsArray['name']  = $item['name'];
                                            $itemsArray['price']  = $item['price'];
                                            $itemsArray['image']  = $item['image'];
                                           
                                            if($remain_stock < 0){
                                                $itemsArray['inhide_outofstock'] = 0;
                                            }else{
                                                $itemsArray['inhide_outofstock']  = $item['inhide_outofstock'];
                                            }
                                            $itemsArray['inhide_lowstock']  = $item['inhide_lowstock'];
                                            $itemsArray['inhide_reorder']  = $item['inhide_reorder'];
                                            $itemsArray['upcoming']  = $upcoming_stock;
                                            $itemsList[] = $itemsArray;
                                            if(($remain_stock+$upcoming_stock)<=$item['order_threshold']){
                                                $reorders[]=$itemsArray;
                                            }
                                            if($remain_stock <= $item['stock_threshold'] && $remain_stock > 1){
                                                $lowstock[] = $itemsArray;
                                                
                                            }
                                            if($remain_stock < 1){
                                                $outofstock[] = $itemsArray;
                                            }
                                         }
                                            ?>
                    
                </section>
                
               
                
                <section id="row-grouping-datatable" style="margin-top:20px">
                    <div class="row" style="margin-top:10px;">
                       
                        <div class="col-12">
                            <form action="" id="allordersdata" method="POST">
                            <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Items</h4>
                                    <button type="button" class="btn-icon btn btn-primary btn-round btn-sm waves-effect waves-float waves-light" data_type="1"  onclick="window.location.href='statistics.php?items_csv=1'">Download Csv</button></td>
                                </div>
                                <div class="card-datatable">
                                   <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>SKU</th>
                                                <th>Price</th>
                                                <th>Total Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        
                                        
                                         
                                        $sn = 0;
                                       
                                        $total_qty;
                                        $total_amount;
                                         foreach($itemsList as $item){
                                          $sn++;
                                            $total_qty+=$item['qty'];
                                            $total_amount+=($item['qty']*$item['price']);
                                            ?>
                                    	    <tr>
                                                <td><?=$sn;?></td>
                                                <td><?php if($item['image']!=''){ ?><img src="items_image/<?=$item['image'];?>" style="width:50px;" /> <?php } ?></td>
                                                <td><?=$item['name'];?></td>
                                                <td><?=$item['sku'];?></td>
                                                
                                                <td><?=$item['price'];?></td>
                                                <td><?=$item['qty'];?> <span style="color:green">(<?=$item['upcoming'];?>)</span></td>
                                                
                                            </tr>
                                            
                                        <?php } ?>
                                        
                                        </tbody>
                                       
                                    </table>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </section>
                
                <?php }else if(isset($_GET['payables'])){ ?>
                <section id="row-grouping-datatable" style="margin-top:20px">
                    
                    <div class="row">
                       
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">Accounts Payable</h4>
                                    <h4 style="float:right;background: #1192d2;color: white;padding: 10px;" id="total_amount">Total Amount : 0</h4>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th style="width:15%">Sn</th>
                                                <th>Account Username</th>
                                                <th>Amount Due</th>
                                                <th>Last Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $accounts = $conn->query("select * from app_accounts where deleted = 0 order by account_name asc");
                                        $sn = 0;
                                        $total_amount = 0;
                                        while($account = $accounts->fetch_assoc()){
                                            $totalPayments = $conn->query("select SUM(amount) as amount from app_payments where account_id = '{$account['id']}' &&  type = '1' && status != 100")->fetch_assoc()['amount']+0;
                                            $balance = $totalPayments+$account['balance'];
                                            $last_paid = $conn->query("SELECT * FROM `app_payments` where account_id = '{$account['id']}' ORDER BY datetime DESC");
                                            if($last_paid->num_rows>0){
                                                $last_paid = $last_paid->fetch_assoc();
                                                $last_paid_date = date('Y-m-d', strtotime($last_paid['datetime']));
                                                $now = time(); // or your date as well
                                                $ls_date = strtotime($last_paid_date);
                                                $datediff = $now - $ls_date;
                                                $daysDiff = round($datediff / (60 * 60 * 24));
                                            }else{
                                                $last_paid_date = 'Never';
                                                $daysDiff = 0;
                                            }
                                            
                                           
                                            
                                            
                                            if(abs($balance) >= $account['amount_threshold'] || $daysDiff >= $account['days_threshold']){
                                                $sn++; 
                                                $total_amount += $balance;
                                            ?>
                                           
                                    	    <tr>
                                                <td><?php echo $sn; ?></td>
                                                <td><?php echo $account['account_name']; ?> <?php if($account['account_username']!=''){ echo '('.$account['account_username'].')';} ?></td>
                                                <td><?php if($balance > 0){ echo '<span style="color:green">'.number_format($balance, 2).'</span>';}else{echo '<span style="color:red">'.number_format(abs($balance), 2).'</span>';} ?></td>
                                                <td><?=$last_paid_date;?></td>
                                                
                                            </tr>
                                        <?php }} ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                
                <?php } ?>

<?php
                $time_end = microtime(true);

//dividing with 60 will give the execution time in minutes otherwise seconds
$execution_time = ($time_end - $time_start);

//execution time of the script
echo '<b>Total Execution Time:</b> '.$execution_time.' Seconds';
?>
            </div>
        </div>
    </div>
    <div id="printableArea" style="display:none">
                    
                    
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

    <?php if(isset($_GET['payables'])){ ?>
    
    <script>
        $(window).on('load', function() {
            $("#total_amount").html("Total Amount : <?php echo number_format(abs($total_amount), 2); ?>");
        });
    </script>
    
    <?php } ?>

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
        
        function inhide_outofstock(e, id, val) {
            if(val==1){
                 var tr = $(e).closest("tr").remove().clone();
                 tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_outofstock(this, "+id+", 0)");
                 $("#outofstock_hidden").append(tr);
                
            }else{
                 var tr = $(e).closest("tr").remove().clone();
                  tr.find("button")
                 .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                 .html(feather.icons['eye-off'].toSvg())
                 .attr("onclick", "inhide_outofstock(this, "+id+", 1)");
                 $("#outofstock_showen").append(tr);
                 
            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                    url : "inc/ajax",
                    method : "POST",
                    data : {inhide_outofstock: id, value: val},
                    async : true,
                    success: function(data){
                        console.log(data);
                        
                    }
                });
        }
        
        function inhide_lowstock(e, id, val) {
            if(val==1){
                 var tr = $(e).closest("tr").remove().clone();
                 tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_lowstock(this, "+id+", 0)");
                 $("#lowstock_hidden").append(tr);
                
            }else{
                 var tr = $(e).closest("tr").remove().clone();
                  tr.find("button")
                 .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                 .html(feather.icons['eye-off'].toSvg())
                 .attr("onclick", "inhide_lowstock(this, "+id+", 1)");
                 $("#lowstock_showen").append(tr);
                 
            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                    url : "inc/ajax",
                    method : "POST",
                    data : {inhide_lowstock: id, value: val},
                    async : true,
                    success: function(data){
                        console.log(data);
                        
                    }
                });
        }
        
        function inhide_reorder(e, id, val) {
            if(val==1){
                 var tr = $(e).closest("tr").remove().clone();
                 tr.find("button")
                    .attr("class", "btn-icon btn btn-success btn-round btn-sm waves-effect waves-float waves-light")
                    .html(feather.icons['eye'].toSvg())
                    .attr("onclick", "inhide_reorder(this, "+id+", 0)");
                 $("#reorder_hidden").append(tr);
                
            }else{
                 var tr = $(e).closest("tr").remove().clone();
                  tr.find("button")
                 .attr("class", "btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light")
                 .html(feather.icons['eye-off'].toSvg())
                 .attr("onclick", "inhide_reorder(this, "+id+", 1)");
                 $("#reorder_showen").append(tr);
                 
            }
            // var a = e.parentNode.parentNode;
            // a.parentNode.removeChild(a);
            $.ajax({
                    url : "inc/ajax",
                    method : "POST",
                    data : {inhide_reorder: id, value: val},
                    async : true,
                    success: function(data){
                        console.log(data);
                        
                    }
                });
        }
        
        function showHideOutofStock(){
            var btnOutofStock_type = $("#btnOutofStock").attr("data_type");
            if(btnOutofStock_type == 1){
                $("#btnOutofStock").html("Back");
                $("#btnOutofStock").attr("data_type","0");
                $("#outofstock_showen").hide();
                $("#outofstock_hidden").show();
            }else{
                $("#btnOutofStock").html("Hidden Items");
                $("#btnOutofStock").attr("data_type","1");
                $("#outofstock_hidden").hide();
                $("#outofstock_showen").show();
                
                
            }
        }
            
        function showHideLowStock(){
            var btnLowStock_type = $("#btnLowStock").attr("data_type");
            if(btnLowStock_type == 1){
                $("#btnLowStock").html("Back");
                $("#btnLowStock").attr("data_type","0");
                $("#lowstock_showen").hide();
                $("#lowstock_hidden").show();
            }else{
                $("#btnLowStock").html("Hidden Items");
                $("#btnLowStock").attr("data_type","1");
                $("#lowstock_hidden").hide();
                $("#lowstock_showen").show();
                
                
            }
        }
        
        function showHideReOrder(){
            var btnReOrder_type = $("#btnReOrder").attr("data_type");
            if(btnReOrder_type == 1){
                $("#btnReOrder").html("Back");
                $("#btnReOrder").attr("data_type","0");
                $("#reorder_showen").hide();
                $("#reorder_hidden").show();
            }else{
                $("#btnReOrder").html("Hidden Items");
                $("#btnReOrder").attr("data_type","1");
                $("#reorder_hidden").hide();
                $("#reorder_showen").show();
            }
        }
    </script>
</body>
<!-- END: Body-->

</html>
<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}


if(!in_array(15, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}

if(isset($_POST['deleteEntries'])){
        $case = $_POST['case'];
        if(count($case) < 1){
            $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Please select atleast row to delete.</div></div>';
            header("location: item_list.php");
            exit();
        }
        foreach($case as $delId){
            $conn->query("update app_items set deleted = '1' where id = '$delId'");
        }
        
        $totalItems = count($case);
        $itemIds = implode(', ', $case);
        addSystemLog($conn, 'ITEMS DELETE', "Total $totalItems items has been deleted", $itemIds);
        $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Selected rows has been deleted</div></div>';
        header("location: item_list.php");
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
    <title>Item List || IConnect</title>
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

/* ===== MOBILE FIX ===== */
@media (max-width: 768px) {

    /* Search section stack */
    .row > .col-sm-6,
    .row > .col-sm-5 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Buttons full width */
    .btn {
        width: 100%;
        margin-bottom: 8px;
    }

    /* Card padding */
    .card-body,
    .card-header {
        padding: 10px;
    }

    /* Table responsive scroll */
    .card-datatable {
        overflow-x: auto;
    }

    table {
        min-width: 600px; /* prevents collapse */
    }

    /* Reduce font */
    table th,
    table td {
        font-size: 12px;
        padding: 6px;
        white-space: nowrap;
    }

    /* Image fix */
    table img {
        width: 40px !important;
        height: auto;
    }

    /* Action button fix */
    table .btn {
        width: auto;
        font-size: 12px;
        padding: 4px 6px;
    }

    /* Checkbox alignment */
    input[type="checkbox"] {
        transform: scale(1.2);
    }

}

/* ===== EXTRA SMALL DEVICES ===== */
@media (max-width: 576px) {

    .content-wrapper {
        padding: 5px;
    }

    h4.card-title {
        font-size: 16px;
    }

    /* Header action button */
    .card-header .btn {
        width: auto;
        padding: 5px 8px;
    }

}

/* ===== TABLET ===== */
@media (min-width: 769px) and (max-width: 1024px) {

    .row > .col-sm-6,
    .row > .col-sm-5 {
        flex: 0 0 100%;
        max-width: 100%;
    }

}

/* ===== DATATABLE FIX ===== */
.dataTables_wrapper {
    overflow-x: auto;
}

/* Prevent layout breaking */
.table {
    width: 100% !important;
}

/* Fix header alignment */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* Space between title & delete button */
.card-header .btn {
    margin-top: 5px;
}

</style>
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
                                    <li class="breadcrumb-item active">Items
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
                                    
                                    <div class="col-sm-6 col-12" >
                                        <input type="text" class="form-control" name="sku" placeholder="Enter SKU or Name" value="<?php if(isset($_POST['sku'])){ echo $_POST['sku']; } ?>" />
                                    </div>
                                    
                                    <div class="col-sm-5 col-12">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                        <a href="?get_all=1" class="btn btn-primary">All Items</a>
                                        
                                    </div>
                                    
                                </div>
                            </form>
                           
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-12">
                            <?php echo flash_msg(); ?>
                            <form action="" id="allordersdata" method="POST">
                            <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Items</h4>
                                    <button type="submit" class="btn-icon btn btn-danger btn-round btn-sm waves-effect waves-float waves-light"><i data-feather='trash-2'></i></button>
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectall"/> Sn</th>
                                                <th>Image</th>
                                                <th>SKU</th>
                                                <!--<th>Name</th>-->
                                                <th>Price</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        
                                        if(isset($_POST['sku'])){
                                            $sku = $_POST['sku'];
                                            $items = $conn->query("select * from app_items WHERE (sku LIKE '%$sku%' || name LIKE '%$sku%') and deleted = 0 order by sku asc");
                                        }else{
                                            $items = $conn->query("select * from app_items where deleted = 0 order by sku asc");
                                        }
                                        
                                        $sn = 0;
                                        $today = date('Y-m-d');
                                        $total_qty;
                                        $total_amount;
                                        while($item = $items->fetch_assoc()){
                                            $sn++;
                                            ?>
                                    	    <tr>
                                                <td><input type="checkbox" class="case" name="case[]" value="<?php echo $item['id']; ?>"/><?=$item['id'];?></td>
                                                <td><?php if($item['image']!=''){ ?><img src="items_image/<?=$item['image'];?>" style="width:50px;" /> <?php } ?></td>
                                                <td><?=$item['sku'];?></td>
                                                <!--<td><?=$item['name'];?></td>-->
                                                <td><?=$item['price'];?></td>
                                                
                                                <td>
                                                    <a type="button" href="add_item.php?edit=<?=$item['id'];?>" class="btn btn-primary">Edit</a>
                                                </td>
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
        $(".dt-row-grouping-t").DataTable({
            "bPaginate": false, //hide pagination
            "bInfo": false, // hide showing entries,
            "bFilter": false
        });
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
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
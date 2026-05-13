<?php 
require_once "inc/config.php";
require_once "inc/functions.php";
// exit;
if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
    exit();
}

if(!in_array(31, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
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
    <title>All in One - Items Page || IConnect</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/extensions/toastr.min.css">
    <link rel="stylesheet" type="text/css" href="app-assets/vendors/css/forms/spinner/jquery.bootstrap-touchspin.css">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/bordered-layout.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/themes/semi-dark-layout.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/charts/chart-apex.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/plugins/extensions/ext-component-toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.css">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- END: Custom CSS-->
<style>
    /* ===============================
   GLOBAL MOBILE IMPROVEMENTS
================================= */
body {
    overflow-x: hidden;
}

img {
    max-width: 100%;
    height: auto;
}

/* ===============================
   SIDEBAR (LEFT MENU)
================================= */
.main-menu {
    width: 280px;
    transition: all 0.3s ease;
}

/* ===============================
   CONTENT AREA
================================= */
.app-content {
    margin-left: 280px;
    transition: all 0.3s ease;
}

/* ===============================
   TABLE (SKU LIST)
================================= */
#item_sku tr td {
    font-size: 13px;
    padding: 8px;
}

/* ===============================
   MOBILE DEVICES (≤ 991px)
================================= */
@media (max-width: 991px) {

    /* Sidebar becomes top section */
    .main-menu {
        position: relative;
        width: 100%;
        height: auto;
    }

    .app-content {
        margin-left: 0;
        padding: 10px;
    }

    /* Search input spacing */
    #sku {
        font-size: 14px;
    }

    /* SKU list scroll fix */
    #item_sku {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    #item_sku tr {
        display: block;
        border-bottom: 1px solid #eee;
    }

    #item_sku td {
        display: flex;
        align-items: center;
        font-size: 13px;
    }
}

/* ===============================
   TABLET DEVICES (≤ 768px)
================================= */
@media (max-width: 768px) {

    .navbar-header img {
        width: 140px !important;
    }

    .main-menu {
        padding: 10px;
    }

    /* Reduce height for better UX */
    .main-menu .col-12[style*="height"] {
        height: auto !important;
        max-height: 300px;
        overflow-y: auto;
    }

    #loading h1 {
        font-size: 18px;
    }
}

/* ===============================
   SMALL MOBILE (≤ 576px)
================================= */
@media (max-width: 576px) {

    .navbar-header p {
        font-size: 12px;
    }

    #sku {
        font-size: 13px;
        padding: 6px;
    }

    #item_sku td img {
        width: 25px !important;
    }

    #item_sku td {
        font-size: 12px;
    }

    #loading {
        padding: 20px;
        text-align: center;
    }

    #loading h1 {
        font-size: 16px;
    }
}

/* ===============================
   EXTRA SMALL DEVICES (≤ 400px)
================================= */
@media (max-width: 400px) {

    #item_sku td {
        flex-direction: column;
        align-items: flex-start;
    }

    #item_sku td img {
        margin-bottom: 5px;
    }

    #loading h1 {
        font-size: 14px;
    }
}
</style>
</head>
<!-- END: Head-->

<!-- BEGIN: Body-->
<style>
.select2-selection__arrow{
    display:none;
}

</style>

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="hover" data-menu="horizontal-menu" data-col="">



   
    <!-- END: Header-->

    <!-- BEGIN: Main Menu-->
    <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mr-auto">
                    <a class="navbar-brand" href="index.php" style="display: block;text-align: center;padding-bottom: 10px;">
                    <img src="assets/d-orders_logo.png" style="width: 190px;" />
                    <p>Back to Dashboard</p>
                    </a></li>
            </ul>
            
        </div>
        
        <div class="" style="margin-top:50px">
           
            <div class="col-12">
                <div class="form-group">
                    <label for="sku">Search SKU</label>
                    <input type="text" class="form-control" id="sku" name="sku" onkeyup="searchSKU()" placeholder=""/>
                </div>
            </div>
            
            <div class="col-12" style="height: calc(100vh - 200px);overflow: scroll;">
                <table width="100%" id="item_sku"  class="table table-bordered">
                    <?php $items = $conn->query("SELECT * FROM (select sku, image from app_items where deleted = 0 UNION SELECT sku, '' as image from app_packages where deleted = 0 && id < 1) A WHERE sku != ''order by sku ASC");
                           while($item = $items->fetch_assoc()){
                           $sku = $item['sku'];?>
                                                                                     
                    <tr style="cursor: pointer;" onclick="viewItemPage('<?=$sku;?>')">
                        <td style="padding: 5px;display: flex;align-items: center;"><img src="items_image/<?php if($item['image']!=''){ ?><?=$item['image']; }else{ echo '54818317.png'; } ?>" style="width: 30px;margin-right: 10px;"/> <?=$item['sku'];?></td>
                    </tr>
                    
                    <?php } ?>
                </table>
            </div>
            
        </div>
    </div>


    <!-- BEGIN: Content-->
    <div class="app-content content " style="padding-top: 20px;">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div id="loading" style="text-align: center;height: 100vh;align-items: center;display: flex;justify-content: center;">
                <h1>All In One - Items Page</h1>
            </div>
            <div id="databox"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="app-assets/js/scripts/forms/form-number-input.min.js"></script>
    
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js"></script>

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });
        
        
    </script>
    <script>
        function searchSKU() {
          var input, filter, table, tr, td, i, txtValue;
          input = document.getElementById("sku");
          filter = input.value.toUpperCase();
          table = document.getElementById("item_sku");
          tr = table.getElementsByTagName("tr");
          for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
              txtValue = td.textContent || td.innerText;
              if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
              } else {
                tr[i].style.display = "none";
              }
            }       
          }
        }
         
        function viewItemPage(sku){
            $("#databox").hide();
    		$("#loading").show();
            $("#loading").html("<img src='assets/ajax_loading.gif' />");
            $.ajax({
    			url:"item_aio_ajax.php",
    			dataType: "html",
    			type: "POST",
    			data: 'sku='+sku,
    			success: function(res) {
    			 //   console.log(res);
    				$("#databox").html(res);
    				$("#databox").show();
    				$("#loading").hide();
    			}
		    });
        }
        
        
    
    
    </script>
      <script src="footer.js"></script>
</body>
<!-- END: Body-->

</html>
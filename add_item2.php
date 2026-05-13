<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}



if(!in_array(14, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}


if(isset($_POST['add_price_tag'])){
    $name = $conn->real_escape_string($_POST['name']);
    $check_name = $conn->query("select * from app_sellprices_name where name = '$name'");
    if($check_name->num_rows > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Name exists in database.</div></div>';
        header("location: add_item.php");
        exit();
    }
    $conn->query("insert into app_sellprices_name set name = '$name'");
    $name_id = $conn->insert_id;
    $items = $conn->query("SELECT * FROM `app_items`");
    while($item = $items->fetch_assoc()){
        $conn->query("insert into app_sellprices_amount set item_id = '{$item['id']}', name_id = '$name_id', price = '{$item['price']}', type='1'");
    }
    
    $packages = $conn->query("SELECT * FROM app_packages");
    while($package = $packages->fetch_assoc()){
        $conn->query("insert into app_sellprices_amount set item_id = '{$package['id']}', name_id = '$name_id', price = '{$package['price']}', type='2'");
    }
    
    addSystemLog($conn, 'PRICE TAG', "New Price Tage ($name) has been added", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Name added successfully.</div></div>';
    header("location: add_item.php");
    exit();
    
}

if(isset($_POST['add_item'])){
    $name = $conn->real_escape_string($_POST['name']);
    $sku = $conn->real_escape_string($_POST['sku']);
    $description = $conn->real_escape_string($_POST['description']);
    $reference = $conn->real_escape_string($_POST['reference']);
    $item_type = $conn->real_escape_string($_POST['item_type']);
    $qty = $conn->real_escape_string($_POST['qty']);
    $stock_threshold = $conn->real_escape_string($_POST['stock_threshold']);
    $order_threshold = $conn->real_escape_string($_POST['order_threshold']);
    $packing_size_id = $conn->real_escape_string($_POST['packing_size_id']);
    $price = $_POST['price'];
    $name_id = $_POST['name_id'];

    $check_sku = $conn->query("select * from app_items where sku = '$sku'");
    $check_sku_package = $conn->query("Select * from app_packages where sku = '$sku'");
    if($check_sku->num_rows > 0 || $check_sku_package->num_rows > 0){
        
        if($check_sku->num_rows > 0){
            $check_sku=$check_sku->fetch_assoc();
            if($check_sku['deleted']==1){
                $conn->query("update app_items set deleted = '0' where id = '{$check_sku['id']}'");
                addSystemLog($conn, 'SKU ENABLED', "SKU ($sku) has been enabled from add item page", "");
                $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">SKU Already exists in deleted list and Enabled now.</div></div>';
            }else{
                $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">SKU Already exists in database.</div></div>';
            }
            header("location: add_item.php");
            exit();
        }
        
        
        
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">SKU Already exists in database.</div></div>';
        header("location: add_item.php");
        exit();
    }
    $file_name='';
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
    $errors = array();

    // Validate and sanitize file name
    $original_name = basename($_FILES['image']['name']);
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Generate a unique file name
    $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $original_name);
    $file_size = $_FILES['image']['size'];
    $file_tmp  = $_FILES['image']['tmp_name'];
    $file_type = $_FILES['image']['type'];

    $extensions = array("jpeg", "jpg", "png", "gif");

    if (!in_array($file_ext, $extensions)) {
        $errors[] = "Invalid file extension. Please upload a JPEG, PNG, or GIF image.";
    }

    if ($file_size > 5 * 1024 * 1024) { // Optional: limit file size to 5MB
        $errors[] = "File size must be less than 5MB.";
    }

    $upload_dir = __DIR__ . "/items_image2";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (empty($errors)) {
        $upload_path = $upload_dir . "/" . $file_name;
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Optionally save $file_name to database here
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">' . implode('<br>', $errors) . '</div></div>';
        header("Location: add_item.php");
        exit();
    }
}

    $st = $price[0];
  
    $conn->query("insert into app_items set sku = '$sku', name = '$name', price = '$st', image='$file_name', description = '$description', item_type='$item_type', reference = '$reference', packing_size_id = '$packing_size_id', stock_threshold='$stock_threshold', order_threshold='$order_threshold'");
    $item_id = $conn->insert_id;
    $now = date('Y-m-d H:i:s');
    if(!empty($_POST['qty'])){
    $conn->query("insert into app_stocks set item_id = '$item_id', qty = '$qty', description = 'Opening Balance', datetime = '$now'");
    }
    for ($i = 0, $n = count($price); $i < $n; $i++) {
        
            $conn->query("insert into app_sellprices_amount set item_id = '$item_id', name_id = '{$name_id[$i]}', price = '{$price[$i]}', type = '1'");
        
        
    }
    
    addSystemLog($conn, 'ITEM ADDED', "New item with sku ($sku) has been added", "");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item added successfully.</div></div>';
    header("location: add_item.php");
    exit();
}

if(isset($_POST['edit_item'])){
    $editId = $_POST['edit_item'];
    $name = $conn->real_escape_string($_POST['name']);
    $sku = $conn->real_escape_string($_POST['sku']);
    $old_sku = $conn->real_escape_string($_POST['old_sku']);
    $description = $conn->real_escape_string($_POST['description']);
    $item_type = $conn->real_escape_string($_POST['item_type']);
    $stock_threshold = $conn->real_escape_string($_POST['stock_threshold']);
    $order_threshold = $conn->real_escape_string($_POST['order_threshold']);
    $packing_size_id = $conn->real_escape_string($_POST['packing_size_id']);
    $reference = $conn->real_escape_string($_POST['reference']);
    
    $price = $_POST['price'];
    $name_id = $_POST['name_id'];
    
    $check_sku = $conn->query("Select * from app_items where sku = '$sku' && id <> $editId")->num_rows;
    $check_sku_package = $conn->query("Select * from app_packages where sku = '$sku'")->num_rows;
    
    if($check_sku > 0 || $check_sku_package > 0){
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">SKU already exists.</div></div>';
        header("location: add_item.php?edit=".$editId);
        exit();
    }
     $st = $price[0];
    $conn->query("update app_items set sku = '$sku', name = '$name', price = '$st', description = '$description', item_type='$item_type', reference = '$reference', packing_size_id = '$packing_size_id', stock_threshold='$stock_threshold', order_threshold='$order_threshold' where id = '$editId'");
    
    for ($i = 0, $n = count($price); $i < $n; $i++) {
        if(!empty($price[$i])){
        $check_price_tag = $conn->query("select * from app_sellprices_amount where name_id = '{$name_id[$i]}' && item_id = '$editId' && type = '1'");
        if($check_price_tag->num_rows > 0){
            $conn->query("update app_sellprices_amount set price = '{$price[$i]}' where name_id = '{$name_id[$i]}' && item_id = '$editId'  && type = '1'");
           
        }else{
            $conn->query("insert into app_sellprices_amount set item_id = '$editId', name_id = '{$name_id[$i]}', price = '{$price[$i]}', type = '1'");
        }
        }
        
    }
    
    if($sku != $old_sku){
        $conn->query("update app_order_items set SKU = '$sku' where SKU = '$old_sku'");
    }
    
    if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
      $errors= array();
      $file_name = strtotime(date('Y-m-d H:i:s')).$_FILES['image']['name'];
      $file_size =$_FILES['image']['size'];
      $file_tmp =$_FILES['image']['tmp_name'];
      $file_type=$_FILES['image']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));
      
      $extensions= array("jpeg","jpg","png", "gif");
      
      if(in_array($file_ext,$extensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file.";
      }
      
      
      if(empty($errors)==true){
         move_uploaded_file($file_tmp,"items_image/".$file_name);
         
      }else{
        
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">'.implode('|',$errors).'.</div></div>';
        header("location: add_item.php");
        exit();
      }
      
      $conn->query("update app_items set image = '$file_name' where id = '$editId'");
   }
   addSystemLog($conn, 'ITEM UPDATED', "Item with sku ($sku) has been updated", "$editId");
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Item has been updated.</div></div>';
    // header("location: item_list.php");
    header("location: add_item.php?edit=".$editId);
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
    <title>Add Item || IConnect</title>
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

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static" data-open="hover" data-menu="horizontal-menu" data-col="">
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
                                    <li class="breadcrumb-item active">Add Item
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="content-body">
                <!-- account setting page -->
                <section id="page-account-settings">
                    <div class="row">
                       

                        <!-- right content section -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                      <?php echo flash_msg(); ?>
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            <?php if(isset($_GET['edit'])){
                                            $item = $conn->query("select * from app_items where id = '{$_GET['edit']}'");
                                            if($item->num_rows < 1) {
                                                header("location: item_list.php");
                                                exit();
                                            }
                                            $item = $item->fetch_assoc();
                                            $orders = $conn->query("SELECT * FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && b.IsArchived = 0 && a.SKU = '{$package['sku']}'")->num_rows;
                                            ?>
                                            
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="row">
                                                
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="name">Item Name</label>
                                                            <input type="text" class="form-control" id="name" name="name" value="<?=$item['name'];?>" placeholder="Item Name" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="sku">SKU</label>
                                                            <input type="text" class="form-control" id="sku" name="sku" value="<?=$item['sku'];?>" placeholder="SKU" required/>
                                                            <input type="text" class="form-control" id="old_sku" name="old_sku" value="<?=$item['sku'];?>" placeholder="SKU" readonly/>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="description">Description</label>
                                                            <input type="text" class="form-control" id="description" name="description" value="<?=$item['description'];?>" placeholder="Description"/>
                                                        </div>
                                                    </div>
                                                     <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="reference">Reference</label>
                                                            <input type="text" class="form-control" id="reference" name="reference" value="<?=$item['reference'];?>" placeholder="Reference"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="stock_threshold">Stock Threshold</label>
                                                            <input type="text" class="form-control" id="stock_threshold" name="stock_threshold" value="<?=$item['stock_threshold'];?>" placeholder="Stock Threshold" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="order_threshold">Re-Orders Threshold</label>
                                                            <input type="text" class="form-control" id="order_threshold" name="order_threshold" placeholder="Re-Orders Threshold" value="<?=$item['order_threshold'];?>" required/>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="order_threshold">Packing Size</label>
                                                                    <select class="form-control" id="packing_size_id" name="packing_size_id" required>
                                                                        <option value="">Select Size</option>
                                                                        <?php 
                                                                        $sizes = $conn->query("SELECT * FROM app_packing_sizes ORDER BY name");
                                                                        while($size = $sizes->fetch_assoc()){ ?>
                                                                            <option value="<?=$size['id']; ?>" <?php if($item['packing_size_id']==$size['id']){ echo 'selected'; } ?>><?=$size['name']; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                             <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="item_type">Item Type</label>
                                                                    <select class="form-control" id="item_type" name="item_type" required>
                                                                        <option value="1" <?php if($item['item_type']==1){ echo 'selected'; } ?>>Physical Product</option>
                                                                        <option value="2" <?php if($item['item_type']==2){ echo 'selected'; } ?>>Digital Product</option>
                                                                       
                                                                    </select>
                                                                </div>
                                                            </div>
                                                    <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="image">New Image</label>
                                                                    <input type="file" class="form-control" id="image" name="image"/>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-6">
                                                                <?php if($item['image']!=''){ ?><img src="items_image/<?=$item['image'];?>" style="width:500px;" /> <?php } ?>
                                                            </div>
                                                    
                                                    <div class="col-12">
                                                        <input type="hidden" name="edit_item" value="<?=$item['id'];?>" />
                                                        <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                        <button type="button" class="btn btn-primary mr-1 mt-1" data-toggle="modal" data-target="#addPriceForm">New Price</button>
                                                    </div>
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ 
                                                            $cprice = $conn->query("Select * from app_sellprices_amount where item_id = '{$_GET['edit']}' && name_id = '{$price['id']}' && type = '1'")->fetch_assoc()['price']+0;
                                                            ?>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="price"><?php echo $price['name']; ?></label>
                                                                    <input type="text" class="form-control" id="price" name="price[]" placeholder="0.00" value="<?=$cprice;?>" <?php if($price['id']==12){ echo 'readonly'; } ?> required />
                                                                    <input type="hidden" name="name_id[]" value="<?php echo $price['id']; ?>" />
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                                
                                            <?php }else{ ?>
                                            
                                            <form class="" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="row">
                                                
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="name">Item Name</label>
                                                                    <input type="text" class="form-control" id="name" name="name" placeholder="Item Name" required/>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="sku">SKU</label>
                                                                    <input type="text" class="form-control" id="sku" name="sku" placeholder="SKU" required/>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="qty">Qty</label>
                                                                    <input type="text" class="form-control" id="qty" name="qty" placeholder="Qty"/>
                                                                </div>
                                                            </div>
                                                             <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="image">Image</label>
                                                                    <input type="file" class="form-control" id="image" name="image"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="description">Description</label>
                                                                    <input type="text" class="form-control" id="description" name="description" placeholder="Description"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="reference">Reference</label>
                                                                    <input type="text" class="form-control" id="reference" name="reference" value="" placeholder="Reference"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="stock_threshold">Low Stock Threshold</label>
                                                                    <input type="text" class="form-control" id="stock_threshold" name="stock_threshold" placeholder="Stock Threshold" value="0" required/>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="order_threshold">Re-Orders Threshold</label>
                                                                    <input type="text" class="form-control" id="order_threshold" name="order_threshold" placeholder="Re-Orders Threshold" value="0" required/>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="packing_size_id">Packing Size</label>
                                                                    <select class="form-control" id="packing_size_id" name="packing_size_id" required>
                                                                        <option value="">Select Size</option>
                                                                        <?php 
                                                                        $sizes = $conn->query("SELECT * FROM app_packing_sizes ORDER BY name");
                                                                        while($size = $sizes->fetch_assoc()){ ?>
                                                                            <option value="<?=$size['id']; ?>"><?=$size['name']; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label for="item_type">Item Type</label>
                                                                    <select class="form-control" id="item_type" name="item_type" required>
                                                                        <option value="1">Physical Product</option>
                                                                        <option value="2">Digital Product</option>
                                                                       
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            
                                                            <div class="col-12">
                                                                <input type="hidden" name="add_item" value="1" />
                                                                <button type="submit" class="btn btn-primary mr-1 mt-1">Save changes</button>
                                                                <button type="button" class="btn btn-primary mr-1 mt-1" data-toggle="modal" data-target="#addPriceForm">New Price</button>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ ?>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="price"><?php echo $price['name']; ?></label>
                                                                    <input type="text" class="form-control" id="price" name="price[]" placeholder="0.00" value="0" <?php if($price['id']==12){ echo 'readonly'; } ?> required/>
                                                                    <input type="hidden" name="name_id[]" value="<?php echo $price['id']; ?>" />
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                            <?php } ?>
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade text-left" id="addPriceForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel33">Add New Price</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="" method="post">
                                                            <div class="modal-body">
                                                                <label>Name: </label>
                                                                <div class="form-group">
                                                                    <input type="text" placeholder="Name" name="name" value="" class="form-control"  required/>
                                                                </div>

                                                            </div>
                                                             
                                                            <div class="modal-footer">
                                                                <input name="add_price_tag" value="1" type="hidden" />
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                        
                        <!--/ right content section -->
                    </div>
                </section>
                <!-- / account setting page -->

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
        function createPop(url, name)
            {    
                
            newwindow=window.open(url,name,'width=760,height=540,toolbar=0,menubar=0,location=0');  
            if (window.focus) {newwindow.focus()}
            }
    </script>
</body>
<!-- END: Body-->

</html>
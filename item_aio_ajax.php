<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    exit();
}

if(!isset($_POST['sku']) || empty($_POST['sku'])){
    exit();
}
$type = '';
$check_sku_item = $conn->query("SELECT * FROM app_items WHERE sku = '{$_POST['sku']}'");
$check_sku_packages = $conn->query("SELECT * FROM app_packages WHERE sku = '{$_POST['sku']}'");
if($check_sku_item->num_rows > 0){
   $type = 'item'; 
}elseif($check_sku_packages->num_rows > 0){
    $type = 'package'; 
}
?>
<style>
.preloader {
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   z-index: 9999;
   background-image: url('assets/ajax_loading.gif');
   background-repeat: no-repeat; 
   background-color: #ffffff94;
   background-position: center;
}
</style>
<div class="preloader" style="display:none"></div>
<div class="content-header row">
    <div class="content-header-left col-md-12 col-12 mb-2">
        <div class="row breadcrumbs-top">
            <div class="col-12">
                <div class="breadcrumb-wrapper">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item" style="font-size: 30px;"><?php echo strtoupper($_POST['sku']); ?></a>
                        </li>
                        <?php if(in_array(15, $permissions_allow)){ ?>
                                                                <?php if($type == 'item'){ ?>
                                                                    <button type="button" class="btn btn-primary" onclick="saveItem();" style="margin-left: auto;">Save changes</button>
                                                                <?php } ?>
                                                                <?php if($type == 'package'){ ?>
                                                                    <input type="hidden" name="edit_package" value="<?=$_POST['sku'];?>" />
                                                                    <button type="submit" class="btn btn-primary" style="margin-left: auto;">Save changes</button>
                                                                <?php } ?>
                                                                
                        <?php } ?>
                    </ol>
                    
                </div>
            </div>
        </div>
    </div>
   
</div>
<?php if($type == 'item'){ ?>
   <div class="content-body">
                <!-- account setting page -->
                <section id="page-account-settings">
                    <div class="row">
                       

                        <!-- right content section -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                        <!-- change password -->
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="account-pill-password" aria-expanded="false">
                                            <!-- form -->
                                            <?php 
                                            $item = $check_sku_item->fetch_assoc();
                                            $remain_stock = getStock($conn, $item['id'], $item['sku']);
                                            $pckg = @$package['sku'] ?? null;
                                            $orders = $conn->query("SELECT * FROM app_order_items a, app_orders b WHERE b.OrderID = a.OrderID && b.IsArchived = 0 && a.SKU = '{$pckg}'")->num_rows;
                                            ?>
                                            
                                            <form class="" id="itemForm" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                                <input type="hidden" name="edit_item" value="<?=$item['id'];?>" />
                                                <div class="row">
                                                    <div class="col-7">
                                                        <h2 style="text-align:center">Basic Details</h2>
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
                                                            <input type="hidden" class="form-control" id="old_sku" name="old_sku" value="<?=$item['sku'];?>" placeholder="SKU" required readonly/>
                                                            
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
                                                   
                                                    
                                                    
                                                   
                                                   
                                                    
                                                    
                                                </div>
                                                        <h2 style="text-align:center; margin-top:10px">Price Information</h2>
                                                        <div class="row">
                                                            <?php 
                                                            $prices = $conn->query("select * from app_sellprices_name order by id asc");
                                                            while($price = $prices->fetch_assoc()){ 
                                                            $cprice = $conn->query("Select * from app_sellprices_amount where item_id = '{$item['id']}' && name_id = '{$price['id']}' && type = '1'")->fetch_assoc()['price']+0;
                                                            ?>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="price_<?php echo $price['id']; ?>"><?php echo $price['name']; ?></label>
                                                                    <input type="text" class="form-control" id="price_<?php echo $price['id']; ?>" name="price[]" placeholder="0.00" value="<?=$cprice;?>" <?php if($price['id']==12){ echo 'readonly'; } ?> required />
                                                                    <input type="hidden" name="name_id[]" value="<?php echo $price['id']; ?>" />
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <?php if(in_array(15, $permissions_allow)){ ?>
                                                                <button type="button" class="btn btn-primary mr-1 mt-1" data-toggle="modal" data-target="#addPriceForm">New Price</button>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-5">
                                                        <h2 style="text-align:center">Product Image</h2>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <center>
                                                                    <?php if($item['image']!=''){ ?><img id="image_view" src="items_image/<?=$item['image'];?>" style="width:70%;" /> <?php }else{ ?> <img id="image_view" src="https://d-orders.co.uk/items_image/54818317.png" style="width:70%;" />  <?php } ?>
                                                                </center>
                                                                
                                                            </div>
                                                            <div class="col-12">
                                                                <?php if(in_array(15, $permissions_allow)){ ?>
                                                                <div class="form-group">
                                                                    <label for="image">Upload New Image</label>
                                                                    <input type="file" class="form-control" onchange="change_image()" id="item_image" name="item_image"/>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <style>
                                                            input,
textarea {
  border: 1px solid #eeeeee;
  box-sizing: border-box;
  margin: 0;
  outline: none;
  padding: 10px;
}

input[type="button"] {
  -webkit-appearance: button;
  cursor: pointer;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

.input-group {
  clear: both;
  margin: 15px 0;
  position: relative;
}

.input-group input[type='button'] {
  background-color: #eeeeee;
  min-width: 38px;
  width: auto;
  transition: all 300ms ease;
}

.input-group .button-minus,
.input-group .button-plus {
  font-weight: bold;
  height: 38px;
  padding: 0;
  width: 38px;
  position: relative;
}

.input-group .quantity-field {
  position: relative;
  height: 38px;
  left: -6px;
  text-align: center;
  width: 62px;
  display: inline-block;
  font-size: 13px;
  margin: 0 0 5px;
  resize: vertical;
}

.button-plus {
  left: -13px;
}

input[type="number"] {
  -moz-appearance: textfield;
  -webkit-appearance: none;
}

                                                        </style>
                                                        <div class="row" style="margin-top:10px">
                                                            <?php if($item['item_type']==1){ ?>
                                                            <div style="background: #1192d2;padding: 20px;border-radius: 5px;width:100%;text-align: center">
                                                                <h2 style="color:white">Quantity in Hand</h2>
                                                                <h1 style="color:white;font-size:35px" id="htmlqtyinhand"><?php echo $remain_stock; ?></h1>
                                                                <center>
                                                                    <div>
                                                                        <?php if(in_array(15, $permissions_allow)){ ?>
                                                                        <div class="input-group" style="margin-right:auto;margin-left:auto">
                                                                          <input type="button" style="margin-left:auto" value="-" class="button-minus" data-field="quantity">
                                                                          <input type="number" step="1" max="" value="<?php echo $remain_stock; ?>" onchange="updatevalue(this.value);" onkeyup="updatevalue(this.value);" name="quantity" class="quantity-field">
                                                                          <input type="button" style="margin-right:auto" value="+" class="button-plus" data-field="quantity">
                                                                        </div>
                                                                        <?php } ?>
                                                                    </div>
                                                                    
                                                                </center>
                                                                
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                                
                                            
                                            <!--/ form -->
                                        </div>
                                        <!--/ change password -->

                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="col-md-12">
                            <?php echo flash_msg(); ?>
                            <form action="" id="allordersdata" method="POST">
                            <input id="labeltype" value="1" type="hidden" name="deleteEntries" />
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title">List of Packages</h4>
                                   
                                    
                                </div>
                                <div class="card-datatable">
                                   <table class="dt-row-grouping-t table">
                                        <thead>
                                            <tr>
                                                <th style="width:15%">Package id</th>
                                                <th>SKU</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $packages = $conn->query("SELECT b.id, b.sku, a.qty FROM app_packages_items as a, app_packages as b WHERE b.id = a.package_id && a.item_id = '{$item['id']}'");
                                        $sn = 0;
                                        
                                        while($package = $packages->fetch_assoc()){
                                            
                                            $sn++; ?>
                                    	    <tr>
                                                <td><?php echo $package['id']; ?></td>
                                                <td><?php echo $package['sku']; ?></td>
                                                <td><?php echo $package['qty']; ?></td>
                                               
                                            </tr>
                                            
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </form>
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
                                                        <form action="" id="addnewPrice" onsubmit="return newPrice();" method="post">
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
<?php }elseif($type == 'package'){ ?>
    <div class="content-body">
     


</div>
<?php } ?>
<script>
    function incrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal)) {
      updatevalue(currentVal+1);
    parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
  }
}

function decrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal)) {
    parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
    updatevalue(currentVal-1);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
  }
}

$('.input-group').on('click', '.button-plus', function(e) {
  incrementValue(e);
});

$('.input-group').on('click', '.button-minus', function(e) {
  decrementValue(e);
});
<?php if($type=='item'){ ?>

function newPrice(){
    $(".preloader").show();
     $.ajax({
		url:"inc/ajax",
		type: "POST",
		data: $("#addnewPrice").serialize(),
		success: function(response) {
		    console.log(response);
		    $('.preloader').fadeOut('slow');
		    res = JSON.parse(response);
		    if(res.status =='success'){
		         $('#addPriceForm .close').click();
                   new Noty({
                          text: res.msg,
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'success',
                          killer: true
                        }).on('onClose', function() {
                          viewItemPage('<?php echo $item['sku']; ?>');
                    }).show();
                    
                 }else{
                    new Noty({
                          text: res.msg,
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'warning',
                          killer: true
                        // }).on('onClose', function() {
                        //   window.location.href = 'user/index.php';
                    }).show();
                 }
		}
    });
    return false;
}

$("#price_8").on('keyup change', function (){
   var price = $(this).val();
   <?php
        $getRate = $conn->query("SELECT * FROM app_settings WHERE name='exchange_rate'")->fetch_assoc()['value'];
        $getRate = json_decode($getRate, true);
    ?>
    
    var exchange_rate = parseFloat(<?=$getRate['rate'];?>);
    
    $("#price_12").val((price/exchange_rate).toFixed(2));
});
function updatevalue(val){
    $("#htmlqtyinhand").html(val);
    $.ajax({
		url:"inc/ajax.php",
		type: "POST",
		data: 'updateqty='+val+'&item_id='+<?php echo $item['id']; ?>,
		success: function(res) {
		    console.log(res);
			new Noty({
              text: "Quantity updated succesfully.",
              modal: true,
              timeout: 3000,
              layout: 'bottomRight',
              theme: "metroui",
              type: 'success',
              killer: true
            // }).on('onClose', function() {
            //   window.location.href = 'user/index.php';
            }).show();
		}
    });
}
function saveItem(){
    $(".preloader").show();
    $.ajax({
		url:"inc/ajax.php",
		type: "POST",
		data: $("#itemForm").serialize(),
		success: function(response) {
		    console.log(response);
		    $('.preloader').fadeOut('slow');
		    res = JSON.parse(response);
		    if(res.status =='success'){
                   new Noty({
                          text: res.msg,
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'success',
                          killer: true
                        // }).on('onClose', function() {
                        //   window.location.href = 'user/index.php';
                    }).show();
                 }else{
                    new Noty({
                          text: res.msg,
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'warning',
                          killer: true
                        // }).on('onClose', function() {
                        //   window.location.href = 'user/index.php';
                    }).show();
                 }
		}
    });
}
function change_image(){
        var fd = new FormData();
        var files = $('#item_image')[0].files;
        console.log('UPLOADING');
        $(".preloader").show();
        // Check file selected or not
        if(files.length > 0 ){
           fd.append('item_image',files[0]);
           fd.append('item_id',"<?php echo $item['id']; ?>");

           $.ajax({
              url:"inc/ajax.php",
              type: 'post',
              data: fd,
              enctype: 'multipart/form-data',
              contentType: false,
              processData: false,
              success: function(response){
                  console.log(response)
                  res = JSON.parse(response);
                  $('.preloader').fadeOut('slow');
                 if(res.status =='success'){
                    $("#image_view").attr("src",res.msg); 
                    $('#item_image').val(null);
                    new Noty({
                          text: "Image updated successfully",
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'success',
                          killer: true
                        // }).on('onClose', function() {
                        //   window.location.href = 'user/index.php';
                    }).show();
                 }else{
                    new Noty({
                          text: res.msg,
                          modal: true,
                          timeout: 3000,
                          layout: 'bottomRight',
                          theme: "metroui",
                          type: 'warning',
                          killer: true
                        // }).on('onClose', function() {
                        //   window.location.href = 'user/index.php';
                    }).show();
                 }
              },
           });
        }else{
        //   alert("Please select a file.");
        }
}
<?php } ?>
</script>
        
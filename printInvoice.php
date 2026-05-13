<?php 
require_once "inc/config.php";
require_once "inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
}

$account = $conn->query("select * from app_accounts where id = '{$_GET['account_id']}'")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">-->
  <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>-->
  <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>-->
	<title>Invoice # <?=$_GET['account_id'].'-'.date('mdY', strtotime($_GET['frmdate'])).date('mdY', strtotime($_GET['todate']));?></title>


	<style>
		/*body {*/
  /*          background-color: #ffffff;*/
  /*      }*/
		/* * { margin: 0; padding: 0; font-family: serif; }*/
		 body { font-size:12px; }
		 p { margin: 0; /* line-height: 17px; */ }
		table { width: 100%;border-collapse:collapse;top: 1.2in; }
		th { border: 1px solid black !important; padding: 5px; font-size:9px;color:#fff !important; background:#000 !important;transition: none !important; -webkit-print-color-adjust: exact; text-align:center; }
		
		td { text-align: left; vertical-align: center; border: 1px solid black;border-bottom: 1px solid;padding:5px;}
		 
         .for {position:absolute;top: 1.8in;left: .5in;width: 3.5in;margin-bottom: 10px;}
         .for h6{margin-top:0px; margin-bottom:3px;font-size:15px;}
         .for p{padding-top:0 !important;padding-bottom:0 !important;font-size:10px; border: 1px solid;}
         .totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
        @media print {
		th { 
		    border: 1px solid black !important;
            padding: 5px;
            font-size: 9px;
            color: #fff !important;
            background: #000 !important; }
		.totalAmount{
             text-align:right;
             border: 1px solid black; padding: 5px; font-size:12px;color:black; background:#cacaca;transition: none !important; -webkit-print-color-adjust: exact;
         }
		}
		@page {
          margin: 0;
        }
        @media print {
          html, body {
            width: 210mm;
            height: 296mm;
          }
          footer {
    position: fixed;
    bottom: 0;
  }
  footer img{
      height: auto;max-width : 800px;
  }
  header {
    position: fixed;
    top: 0;
  }
  header img{
      height: auto;max-width : 800px;
  }
        }
        @media print {
  .my-table {
    height: 50vh; 
  }
}
		.header, .header-space{
		    height: 200px;
		}
              .footer, .footer-space {
                height: 240px;
              }
		/*.body {*/
            /*background-image:  url(https://d-orders.co.uk/latterheads/dchannel_latterhead.png);*/
           /* background-size:   cover;                      /* <------ */
            /*background-repeat: no-repeat;*/
           /* background-position: top right;            /* optionally, center the image 
            /*background-attachment: fixed;*/
  /*          width: 210mm;*/
  /*          height: 296mm;*/
            
  /*      }*/


       
	</style>

</head>
<body style="padding:0px" class="body">
    <header>
        <img src="assets/inv logo.png" style="height: 80px;width: 150px;">
    </header>
    <div class="" style="">
        <div style="padding:5px;padding-left: 40px;padding-right: 40px;">
             <span class="for" style="float:left;">
                <h6 class="text-center shadowhead">Invoice # <?=$_GET['account_id'].'-'.date('mdY', strtotime($_GET['frmdate'])).date('mdY', strtotime($_GET['todate']));?></h6>
                 <p>
                     <span><b>Name: </b> <?=$account['account_name'];?></span><br>
                     <span><b>Phone: </b> <?=$account['phone'];?></span><br>
                     <span><b>Email: </b> <?=$account['email'];?></span><br>
                     <span><b>Address: </b> <?=$account['address'];?></span><br>
                </p>
            </span>
            <br><br>
        	<br>
        	<br>
        	<table class="voucher-table table">
        	    <thead><tr><td style="border:none;">
                  <div class="header-space"> </div>
                </td></tr></thead>
        	    <thead>
        	        <th style="width: 7%;background:#000">Sn.</th>
        	        <th style="width: 10%;">Date.</th>
        	        <th style="width: 18%;">OrderID</th>
        	        <th style="width: 10%;">SellRecordNo</th>
        	        <th style="width: 18%;">Reference</th>
        	        <th style="width: 40%;">SKU</th>
        	        <th style="width: 10%;">Qty</th>
        	        <th style="width: 15%;">Total</th>
        	    </thead>
        	    <tbody>
        	        <?php
        	        $sn=0;
        	        $total = 0;
        	        $totalqty = 0;
        	        $totalShippingCost = $conn->query("SELECT IFNULL(SUM(ShippingServiceCost), 0) amount FROM app_orders WHERE  IsArchived = '0' && AccountID = '{$_GET['account_id']}' && DATE(CreatedTime) >= '{$_GET['frmdate']}' && DATE(CreatedTime) <= '{$_GET['todate']}'")->fetch_assoc()['amount'];
        	        $orders = $conn->query("select * from app_order_items a, app_orders b where b.AccountID = '{$_GET['account_id']}' and DATE(b.CreatedTime) >= '{$_GET['frmdate']}' and DATE(b.CreatedTime) <= '{$_GET['todate']}' && b.IsArchived = '0' && b.OrderID = a.OrderID order by b.CreatedTime asc");
        	        while($order = $orders->fetch_assoc()){
        	            $totalqty += $order['QuantityPurchased'];
        	            $total +=($order['QuantityPurchased']*$order['Price']);
        	            
        	        $sn++;?>
        	        <tr>
        	            <td><?=$sn;?></td>
        	            <td><?=date('Y-m-d', strtotime($order['CreatedTime']));?></td>
        	            <td><?=$order['OrderID'];?></td>
        	            <td><?=$order['SellingManagerSalesRecordNumber'];?></td>
        	            <td><?=$order['Reference'];?></td>
        	            <td><?=$order['SKU'];?></td>
        	            <td style="text-align: center"><?=$order['QuantityPurchased'];?></td>
        	            <td style="text-align: right"><?=($order['QuantityPurchased']*$order['Price']);?></td>
        	        </tr>
        	        <?php }
        	        if($orders->num_rows > 0){
        	            
        	            echo '<tr>
        	            <td colspan="6" class="totalAmount">Total : </td>
        	            <td class="totalAmount" style="text-align: center">'.$totalqty.'</td>
        	            <td class="totalAmount">'.$total.'</td>
        	            </tr>';
        	            
        	            echo '<tr>
        	            <td colspan="7" class="totalAmount">Shipping Cost : </td>
        	            <td class="totalAmount">'.round($totalShippingCost, 2).'</td>
        	            </tr>';
        	            
        	            echo '<tr>
        	            <td colspan="7" class="totalAmount">Net Total : </td>
        	            <td class="totalAmount">'.round($total+$totalShippingCost, 2).'</td>
        	            </tr>';
        	        }
        	        
        	        
        	        ?>
        	        
        	    </tbody>
        	    <tfoot><tr><td style="border:none;">
                  <div class="footer-space"> </div>
                </td></tr></tfoot>
        	</table>
            
        </div>
     <footer style="position:fixed; bottom:0; left:0; right:0; width:100%; text-align:center; font-size:12px; padding:10px; border-top:1px solid #ccc; background:#fff;">
  <p style="margin:0;">
    📍 Unit N/G/6B, Nortex Business Centre, 105 Chorley Old Road, Bolton, BL1 3AS <br>
    📞 0044 7588 420529
  </p>
</footer>
        
   

<script type="text/javascript">
		window.print();
</script>

</div>
</body>
</html>
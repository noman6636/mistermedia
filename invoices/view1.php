<?php 
require_once "../inc/config.php";
require_once "../inc/functions.php";

if(!isset($_SESSION['admin_id'])){
    header("location: login.php");
    exit;
}

if(!in_array(32, $permissions_allow)){
    $_SESSION['flash'] = '<div class="alert alert-success" role="alert"><div class="alert-body">Access denied to this page.</div></div>';
    header("location: index.php");
    exit();
}
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $invoice = $conn->query("SELECT * FROM app_invoices WHERE id = '$id'");
    if($invoice->num_rows > 0){
        $invoice = $invoice->fetch_assoc();
    }else{
        header("location: index.php");
        exit;
    }
}else{
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">-->
  <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>-->
  <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>-->
	<title>Invoice # <?=date('Ymd', strtotime($invoice['date'])).'-'.sprintf('%04d', $invoice['id']);?></title>


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
        <img src="https://d-orders.co.uk/latterheads/oxijan_header.png" style="height: auto;width: -webkit-fill-available;">
    </header>
    <div class="" style="">
        <div style="padding:5px;padding-left: 40px;padding-right: 40px;">
             <span class="for" style="float:left;">
                <h6 class="text-center shadowhead">Invoice # <?=date('Ymd', strtotime($invoice['date'])).'-'.sprintf('%04d', $invoice['id']);?></h6>
                 <p>
                     <span><b>Name: </b> <?=$invoice['name'];?></span><br>
                     <span><b>Phone: </b> <?=$invoice['phone'];?></span><br>
                     <span><b>Email: </b> <?=$invoice['email'];?></span><br>
                     <span><b>Address: </b> <?=$invoice['address'];?></span><br>
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
        	        <th style="width: 5%;background:#000">Sn.</th>
        	        <th style="width: 40%;">Title.</th>
        	        <th style="width: 15%;">Qty</th>
        	        <th style="width: 15%;">Amount</th>
        	        <th style="width: 15%;">Total</th>
        	    </thead>
        	    <tbody>
        	        <?php
        	        $sn=0;
        	        $total = 0;
        	        $totalqty = 0;
        	        $details = $conn->query("SELECT * FROM app_invoices_details WHERE invoice_id = '$id'");
        	        while($detail = $details->fetch_assoc()){
        	            $totalqty += $detail['qty'];
        	            $total += $detail['amount'];
        	            
        	        $sn++;?>
        	        <tr>
        	            <td><?=$sn;?></td>
        	            
        	            <td><?=$detail['title'];?></td>
        	            <td style="text-align: center"><?=$detail['qty'];?></td>
        	            <td style="text-align: right"><?=$detail['price'];?></td>
        	            <td style="text-align: right"><?=$detail['amount'];?></td>
        	            
        	        </tr>
        	        <?php }
        	        if($details->num_rows > 0){
        	            echo '<tr>
        	            <td colspan="4" class="totalAmount">Total : </td>
        	            <td class="totalAmount">'.$total.'</td>
        	            </tr>';
        	        }
        	        
        	        
        	        ?>
        	        
        	    </tbody>
        	    <tfoot><tr><td style="border:none;">
                  <div class="footer-space"> </div>
                </td></tr></tfoot>
        	</table>
            
        </div>
        <footer>
            <img src="https://d-orders.co.uk/latterheads/oxijan_footer.png" style="height: auto;width: -webkit-fill-available;">
        </footer>
        
   

<script type="text/javascript">
		window.print();
</script>

</div>
</body>
</html>
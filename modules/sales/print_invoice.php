<?php
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$id=intval($_GET['id']??0);
$sale=getSingleRow("SELECT * FROM sales WHERE sale_id=?","i",[$id]);
if(!$sale){exit('Invoice not found');}
$items=getAllRows("SELECT * FROM sale_items WHERE sale_id=?","i",[$id]);
$returns=getAllRows("SELECT r.*, p.product_name FROM returns r JOIN products p ON r.product_id=p.product_id WHERE r.sale_id=?","i",[$id]);
$settings=getAllRows("SELECT setting_key,setting_value FROM settings");
$cfg=[]; foreach($settings as $s) $cfg[$s['setting_key']]=$s['setting_value'];
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice <?php echo $sale['invoice_number'];?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
<style>body{font-size:13px;} @media print{.no-print{display:none!important;} body{margin:0;}}</style>
</head><body class="p-4">
<div class="no-print mb-3"><button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button> <a href="view_invoice.php?id=<?php echo $id;?>" class="btn btn-secondary">Back</a></div>
<div style="max-width:700px;margin:0 auto;border:1px solid #ddd;padding:30px;border-radius:8px">
<div class="row mb-3">
<div class="col-6">
    <?php if(!empty($cfg['company_logo'])):?>
        <div class="mb-2"><img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($cfg['company_logo']);?>" style="max-height:60px;max-width:200px;"></div>
    <?php endif;?>
    <h4><?php echo htmlspecialchars($cfg['company_name']??'Company Name');?></h4><small><?php echo htmlspecialchars($cfg['company_address']??'');?></small><br><small><?php echo htmlspecialchars($cfg['company_phone']??'');?></small></div>
<div class="col-6 text-end"><h3 class="text-primary">INVOICE</h3><strong><?php echo htmlspecialchars($sale['invoice_number']);?></strong><br><small>Date: <?php echo date('d M Y',strtotime($sale['created_at']));?></small></div>
</div>
<hr>
<div class="row mb-3">
<div class="col-6"><strong>Bill To:</strong><br><?php echo htmlspecialchars($sale['customer_name']??'Walk-in');?></div>
<div class="col-6 text-end"><strong>Payment:</strong> <?php echo $sale['payment_method'];?><br><strong>Status:</strong> <?php echo $sale['payment_status'];?></div>
</div>
<table class="table table-bordered table-sm">
<thead class="table-dark"><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
<tbody>
<?php foreach($items as $i=>$it):?>
<tr><td><?php echo $i+1;?></td><td><?php echo htmlspecialchars($it['product_name']);?></td><td>PKR <?php echo number_format($it['unit_price'],2);?></td><td><?php echo $it['quantity'];?></td><td>PKR <?php echo number_format($it['subtotal'],2);?></td></tr>
<?php endforeach;?>
</tbody></table>
<div class="row"><div class="col-6 offset-6">
<table class="table table-sm"><tbody>
<tr><td>Subtotal:</td><td class="text-end">PKR <?php echo number_format($sale['subtotal'],2);?></td></tr>
<tr><td>Tax (<?php echo $sale['tax_rate'];?>%):</td><td class="text-end">PKR <?php echo number_format($sale['tax_amount'],2);?></td></tr>
<?php if($sale['discount']>0):?><tr><td>Discount:</td><td class="text-end">-PKR <?php echo number_format($sale['discount'],2);?></td></tr><?php endif;?>
<tr class="table-dark"><td><strong>TOTAL:</strong></td><td class="text-end"><strong>PKR <?php echo number_format($sale['total_amount'],2);?></strong></td></tr>
</tbody></table></div></div>
<?php if(!empty($returns)):?>
<div class="mt-3 mb-3">
    <h6 class="text-danger"><i class="fas fa-undo"></i> Returned Items (Refunded)</h6>
    <table class="table table-bordered table-sm" style="font-size:12px;">
        <thead class="table-light"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Refunded Total</th><th>Reason</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach($returns as $ret):?>
            <tr>
                <td><?php echo htmlspecialchars($ret['product_name']);?></td>
                <td>PKR <?php echo number_format($ret['price'],2);?></td>
                <td><?php echo $ret['quantity'];?></td>
                <td>PKR <?php echo number_format($ret['price']*$ret['quantity'],2);?></td>
                <td><?php echo htmlspecialchars($ret['reason']??'--');?></td>
                <td><?php echo date('d M Y',strtotime($ret['created_at']));?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
<?php endif;?>
<?php if($sale['notes']):?><p><strong>Notes:</strong> <?php echo htmlspecialchars($sale['notes']);?></p><?php endif;?>
<hr><p class="text-center text-muted mb-0">Thank you for your business!</p>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body></html>

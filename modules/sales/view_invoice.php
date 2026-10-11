<?php
$page_title="View Invoice";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$id=intval($_GET['id']??0);
$sale=getSingleRow("SELECT s.*,u.full_name FROM sales s LEFT JOIN users u ON s.user_id=u.user_id WHERE s.sale_id=?","i",[$id]);
if(!$sale){header("Location: manage_invoices.php");exit();}
$items=getAllRows("SELECT * FROM sale_items WHERE sale_id=?","i",[$id]);
$returns=getAllRows("SELECT r.*, p.product_name FROM returns r JOIN products p ON r.product_id=p.product_id WHERE r.sale_id=?","i",[$id]);
$settings=getAllRows("SELECT setting_key,setting_value FROM settings");
$cfg=[]; foreach($settings as $s) $cfg[$s['setting_key']]=$s['setting_value'];
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<div class="page-header">
<div><h2><i class="fas fa-file-invoice"></i> Invoice #<?php echo htmlspecialchars($sale['invoice_number']);?></h2></div>
<div>
<a href="print_invoice.php?id=<?php echo $id;?>" class="btn btn-secondary me-2" target="_blank"><i class="fas fa-print"></i> Print</a>
<a href="manage_invoices.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div></div>
<div class="card"><div class="card-body">
<div class="row mb-4">
<div class="col-md-6">
    <?php if(!empty($cfg['company_logo'])):?>
        <div class="mb-2"><img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($cfg['company_logo']);?>" style="max-height:60px;max-width:200px;border-radius:4px;"></div>
    <?php endif;?>
    <h5><?php echo htmlspecialchars($cfg['company_name']??'Company');?></h5>
    <p class="text-muted mb-0"><?php echo htmlspecialchars($cfg['company_address']??'');?></p>
    <p class="text-muted mb-0"><?php echo htmlspecialchars($cfg['company_phone']??'');?></p>
</div>
<div class="col-md-6 text-md-end"><h4 class="text-primary">INVOICE</h4><p class="mb-1"><strong><?php echo htmlspecialchars($sale['invoice_number']);?></strong></p><p class="text-muted mb-0">Date: <?php echo date('d M Y',strtotime($sale['created_at']));?></p></div>
</div>
<div class="row mb-4">
<div class="col-md-6"><h6>Bill To:</h6><p><strong><?php echo htmlspecialchars($sale['customer_name']??'Walk-in Customer');?></strong></p></div>
<div class="col-md-6 text-md-end"><p class="mb-1">Payment: <span class="badge bg-success"><?php echo $sale['payment_method'];?></span></p><p class="mb-1">Status: <span class="badge bg-primary"><?php echo $sale['payment_status'];?></span></p></div>
</div>
<div class="table-responsive mb-4">
<table class="table table-bordered"><thead class="table-primary"><tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
<tbody>
<?php foreach($items as $i=>$it):?>
<tr><td><?php echo $i+1;?></td><td><?php echo htmlspecialchars($it['product_name']);?></td><td>PKR <?php echo number_format($it['unit_price'],2);?></td><td><?php echo $it['quantity'];?></td><td>PKR <?php echo number_format($it['subtotal'],2);?></td></tr>
<?php endforeach;?>
</tbody></table></div>
<div class="row"><div class="col-md-6 offset-md-6">
<table class="table table-sm"><tbody>
<tr><td>Subtotal:</td><td class="text-end">PKR <?php echo number_format($sale['subtotal'],2);?></td></tr>
<tr><td>Tax (<?php echo $sale['tax_rate'];?>%):</td><td class="text-end">PKR <?php echo number_format($sale['tax_amount'],2);?></td></tr>
<tr><td>Discount:</td><td class="text-end">-PKR <?php echo number_format($sale['discount'],2);?></td></tr>
<tr class="table-primary"><td><strong>Total:</strong></td><td class="text-end"><strong>PKR <?php echo number_format($sale['total_amount'],2);?></strong></td></tr>
</tbody></table></div></div>
<?php if(!empty($returns)):?>
<div class="mt-4 mb-4">
    <h5 class="text-danger"><i class="fas fa-undo"></i> Returned Items (Refunded)</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-danger"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Refunded Total</th><th>Reason</th><th>Date</th></tr></thead>
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
</div>
<?php endif;?>
<?php if($sale['notes']):?><p><strong>Notes:</strong> <?php echo htmlspecialchars($sale['notes']);?></p><?php endif;?>
</div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>

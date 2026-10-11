<?php
$page_title="Activity Logs";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$page=max(1,intval($_GET['page']??1)); $limit=25; $offset=($page-1)*$limit;
$total=$conn->query("SELECT COUNT(*) as c FROM activity_logs")->fetch_assoc()['c'];
$pages=max(1,ceil($total/$limit));
$logs=getAllRows("SELECT l.*,u.full_name,u.username FROM activity_logs l JOIN users u ON l.user_id=u.user_id ORDER BY l.created_at DESC LIMIT $offset,$limit");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<div class="page-header"><div><h2><i class="fas fa-clipboard-list"></i> Activity Logs</h2><p>Total: <?php echo $total;?> records</p></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>Date/Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>
<?php if(empty($logs)):?><tr><td colspan="5" class="text-center py-4 text-muted">No logs</td></tr>
<?php else: $ac=['Login'=>'success','Logout'=>'secondary','Add'=>'primary','Edit'=>'info','Delete'=>'danger','Update'=>'info','Return'=>'warning','Stock'=>'success','Create'=>'primary']; foreach($logs as $l):
$color='secondary'; foreach($ac as $k=>$v) if(strpos($l['action'],$k)!==false){$color=$v;break;}?>
<tr><td><?php echo date('d M Y H:i',strtotime($l['created_at']));?></td>
<td><strong><?php echo htmlspecialchars($l['full_name']);?></strong><br><small class="text-muted">@<?php echo htmlspecialchars($l['username']);?></small></td>
<td><span class="badge bg-<?php echo $color;?>"><?php echo htmlspecialchars($l['action']);?></span></td>
<td><?php echo htmlspecialchars($l['description']??'');?></td>
<td><code><?php echo htmlspecialchars($l['ip_address']??'');?></code></td></tr>
<?php endforeach; endif;?></tbody></table></div>
<?php if($pages>1):?><div class="p-3"><nav><ul class="pagination justify-content-center mb-0">
<li class="page-item <?php echo $page<=1?'disabled':'';?>"><a class="page-link" href="?page=<?php echo $page-1;?>">Prev</a></li>
<?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++):?><li class="page-item <?php echo $i==$page?'active':'';?>"><a class="page-link" href="?page=<?php echo $i;?>"><?php echo $i;?></a></li><?php endfor;?>
<li class="page-item <?php echo $page>=$pages?'disabled':'';?>"><a class="page-link" href="?page=<?php echo $page+1;?>">Next</a></li>
</ul></nav></div><?php endif;?>
</div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>

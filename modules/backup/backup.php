<?php
$page_title="Database Backup";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
if(isset($_POST['backup'])){
    $tables=$conn->query("SHOW TABLES");
    $sql="-- IMS Backup ".date('Y-m-d H:i:s')."\n-- Database: ".DB_NAME."\n\n";
    while($row=$tables->fetch_row()){
        $t=$row[0];
        $sql.="DROP TABLE IF EXISTS `$t`;\n";
        $cr=$conn->query("SHOW CREATE TABLE `$t`")->fetch_row();
        $sql.=$cr[1].";\n\n";
        $rows=$conn->query("SELECT * FROM `$t`");
        while($r=$rows->fetch_assoc()){
            $vals=array_map(function($v) use($conn){ return $v===null?'NULL':"'".$conn->real_escape_string($v)."'"; },$r);
            $sql.="INSERT INTO `$t` VALUES (".implode(',',$vals).");\n";
        }
        $sql.="\n";
    }
    $dir=$_SERVER['DOCUMENT_ROOT'].'/inventory_system/database/backups/';
    if(!is_dir($dir)) mkdir($dir,0777,true);
    $fn='backup_'.date('Y-m-d_H-i-s').'.sql';
    file_put_contents($dir.$fn,$sql);
    logActivity($_SESSION['user_id'],'Backup','Database backed up: '.$fn);
    $success='Backup created: '.$fn;
}
if(isset($_GET['download'])){
    $fn=basename($_GET['download']);
    $dir=$_SERVER['DOCUMENT_ROOT'].'/inventory_system/database/backups/';
    $fp=$dir.$fn;
    if(file_exists($fp) && strpos($fn,'.sql')!==false){
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$fn.'"');
        readfile($fp); exit();
    }
}
if(isset($_GET['delete_backup'])){
    $fn=basename($_GET['delete_backup']);
    $fp=$_SERVER['DOCUMENT_ROOT'].'/inventory_system/database/backups/'.$fn;
    if(file_exists($fp)) unlink($fp);
    $success='Backup deleted.';
}
$dir=$_SERVER['DOCUMENT_ROOT'].'/inventory_system/database/backups/';
$backups=is_dir($dir)?array_filter(glob($dir.'*.sql'),function($f){return is_file($f);}):[];
rsort($backups);
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-database"></i> Database Backup</h2></div></div>
<div class="row g-3">
<div class="col-md-4"><div class="card"><div class="card-header">Create Backup</div><div class="card-body">
<p class="text-muted">Creates a complete SQL dump of all database tables.</p>
<form method="POST"><button type="submit" name="backup" class="btn btn-primary w-100"><i class="fas fa-download"></i> Create Backup Now</button></form>
</div></div></div>
<div class="col-md-8"><div class="card"><div class="card-header">Backup Files (<?php echo count($backups);?>)</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Filename</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead><tbody>
<?php if(empty($backups)):?><tr><td colspan="4" class="text-center py-3 text-muted">No backups yet</td></tr>
<?php else: foreach($backups as $b): $fn=basename($b); $sz=round(filesize($b)/1024,1); $mt=date('d M Y H:i',filemtime($b));?>
<tr><td><?php echo htmlspecialchars($fn);?></td><td><?php echo $sz;?> KB</td><td><?php echo $mt;?></td><td>
<a href="?download=<?php echo urlencode($fn);?>" class="btn btn-sm btn-success me-1"><i class="fas fa-download"></i></a>
<a href="?delete_backup=<?php echo urlencode($fn);?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this backup?')"><i class="fas fa-trash"></i></a>
</td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>

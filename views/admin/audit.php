<?php
requireAuth(); if(!isOrgAdmin()) redirect('/dashboard','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $page=max(1,(int)($_GET['page']??1));
$filterAction=$_GET['action']??'all'; $where='a.org_id=?'; $params=[$orgId];
if ($filterAction!=='all') { $where.=' AND a.action=?'; $params[]=$filterAction; }
$total=$db->fetchColumn("SELECT COUNT(*) FROM audit_logs a WHERE $where",$params);
$logs=$db->fetchAll("SELECT a.*,u.name as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id WHERE $where ORDER BY a.created_at DESC LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),$params);
$pageTitle='Audit Logs'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5">
  <h1 style="font-size:22px;font-weight:800">Audit Logs</h1>
  <div class="d-flex gap-2">
    <?php foreach(['all'=>'All','create'=>'Create','update'=>'Update','delete'=>'Delete','login'=>'Login','logout'=>'Logout'] as $v=>$l): ?>
    <a href="?action=<?= $v ?>" class="btn btn-sm <?= $filterAction===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
    <?php endforeach;?>
  </div>
</div>
<div class="pf-card">
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>User</th><th>Action</th><th>Entity</th><th>Detail</th><th>IP</th><th>Time</th></tr></thead>
      <tbody>
        <?php foreach($logs as $a): ?>
        <tr><td style="font-size:13px"><?= e($a['user_name']??'System') ?></td>
        <td><span class="badge badge-soft-<?= in_array($a['action'],['delete','logout'])?'danger':($a['action']==='create'?'success':($a['action']==='login'?'info':'secondary')) ?>"><?= e($a['action']) ?></span></td>
        <td style="font-size:13px"><?= e($a['entity']) ?> #<?= $a['entity_id'] ?></td>
        <td style="font-size:12px;color:var(--pf-text-3)"><?= e(truncate($a['detail']??'',70)) ?></td>
        <td style="font-size:12px;color:var(--pf-text-3)"><?= e($a['ip']) ?></td>
        <td style="font-size:12px;color:var(--pf-text-3);white-space:nowrap"><?= fDate($a['created_at'],'d M Y H:i') ?></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <div class="pf-card-footer"><?= pagination($total,$page,PER_PAGE,url('admin/audit').'?action='.$filterAction) ?></div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

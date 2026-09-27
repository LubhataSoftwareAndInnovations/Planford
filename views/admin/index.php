<?php
requireAuth(); if(!isOrgAdmin()) redirect('/dashboard','Permission denied.','danger');
$db=db(); $orgId=authOrgId();
$stats=['users'=>$db->count('users','org_id=?',[$orgId]),'programs'=>$db->count('programs','org_id=?',[$orgId]),'tasks'=>$db->fetchColumn("SELECT COUNT(*) FROM tasks t JOIN programs p ON t.program_id=p.id WHERE p.org_id=?",[$orgId]),'audit'=>$db->count('audit_logs','org_id=?',[$orgId])];
$recentAudit=$db->fetchAll("SELECT a.*,u.name as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id WHERE a.org_id=? ORDER BY a.created_at DESC LIMIT 10",[$orgId]);
$pageTitle='Admin Panel'; ob_start();
?>
<h1 style="font-size:22px;font-weight:800;margin-bottom:24px">Admin Panel</h1>
<div class="pf-kpi-grid mb-6">
  <div class="pf-kpi" style="--kpi-color:var(--pf-indigo)"><div class="pf-kpi-icon"><i class="bi bi-people"></i></div><div class="pf-kpi-value"><?= $stats['users'] ?></div><div class="pf-kpi-label">Users</div></div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-blue)"><div class="pf-kpi-icon"><i class="bi bi-collection"></i></div><div class="pf-kpi-value"><?= $stats['programs'] ?></div><div class="pf-kpi-label">Programs</div></div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-green)"><div class="pf-kpi-icon"><i class="bi bi-check2-square"></i></div><div class="pf-kpi-value"><?= $stats['tasks'] ?></div><div class="pf-kpi-label">Tasks</div></div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-amber)"><div class="pf-kpi-icon"><i class="bi bi-shield-check"></i></div><div class="pf-kpi-value"><?= number_format($stats['audit']) ?></div><div class="pf-kpi-label">Audit Events</div></div>
</div>
<div class="d-flex gap-3 mb-5 flex-wrap">
  <a href="<?= url('admin/users') ?>" class="btn btn-outline"><i class="bi bi-people"></i> Manage Users</a>
  <a href="<?= url('admin/audit') ?>" class="btn btn-outline"><i class="bi bi-shield-check"></i> Audit Logs</a>
  <a href="<?= url('admin/settings') ?>" class="btn btn-outline"><i class="bi bi-gear"></i> Settings</a>
</div>
<div class="pf-card">
  <div class="pf-card-header"><div class="pf-card-title">Recent Audit Events</div></div>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>User</th><th>Action</th><th>Entity</th><th>Detail</th><th>IP</th><th>Time</th></tr></thead>
      <tbody>
        <?php foreach($recentAudit as $a): ?>
        <tr><td style="font-size:13px"><?= e($a['user_name']??'System') ?></td><td><span class="badge badge-soft-<?= in_array($a['action'],['delete','logout'])?'danger':($a['action']==='create'?'success':'secondary') ?>"><?= e($a['action']) ?></span></td><td style="font-size:13px"><?= e($a['entity']) ?></td><td style="font-size:12px;color:var(--pf-text-3)"><?= e(truncate($a['detail']??'',60)) ?></td><td style="font-size:12px;color:var(--pf-text-3)"><?= e($a['ip']) ?></td><td style="font-size:12px;color:var(--pf-text-3)"><?= timeAgo($a['created_at']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

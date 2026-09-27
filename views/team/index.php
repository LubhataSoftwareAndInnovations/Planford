<?php
requireAuth(); if(!isPM()) redirect('/dashboard','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $page=max(1,(int)($_GET['page']??1));
$users=$db->fetchAll("SELECT u.*,(SELECT COUNT(DISTINCT pm.program_id) FROM program_members pm WHERE pm.user_id=u.id) as program_count FROM users u WHERE u.org_id=? ORDER BY u.name LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),[$orgId]);
$total=$db->count('users','org_id=?',[$orgId]);
$pageTitle='Team'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div><h1 style="font-size:22px;font-weight:800;margin-bottom:4px">Team</h1><p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> member<?= $total!=1?'s':'' ?></p></div>
  <?php if(isOrgAdmin()):?><a href="<?= url('admin/users/new') ?>" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add Member</a><?php endif;?>
</div>
<div class="pf-card">
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Name</th><th>Role</th><th>Title</th><th>Department</th><th>Programs</th><th>Last Login</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr>
          <td><div class="d-flex align-items-center gap-2"><?= avatar($u['name'],'','32px') ?><div><div class="fw-600"><?= e($u['name']) ?></div><div style="font-size:12px;color:var(--pf-text-3)"><?= e($u['email']) ?></div></div></div></td>
          <td><span class="badge badge-soft-<?= $u['role']==='super_admin'?'danger':($u['role']==='pm'?'primary':'secondary') ?>"><?= e(ucfirst(str_replace('_',' ',$u['role']))) ?></span></td>
          <td style="font-size:13px"><?= e($u['title']??'—') ?></td>
          <td style="font-size:13px"><?= e($u['department']??'—') ?></td>
          <td style="font-size:13px"><?= $u['program_count'] ?></td>
          <td style="font-size:12px;color:var(--pf-text-3)"><?= $u['last_login']?timeAgo($u['last_login']):'Never' ?></td>
          <td><?= $u['is_active']?'<span class="badge badge-soft-success">Active</span>':'<span class="badge badge-soft-danger">Inactive</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

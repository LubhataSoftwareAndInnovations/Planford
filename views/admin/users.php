<?php
requireAuth(); if(!isOrgAdmin()) redirect('/dashboard','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $page=max(1,(int)($_GET['page']??1));
$users=$db->fetchAll("SELECT * FROM users WHERE org_id=? ORDER BY name LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),[$orgId]);
$total=$db->count('users','org_id=?',[$orgId]);
$pageTitle='Manage Users'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div><h1 style="font-size:22px;font-weight:800;margin-bottom:4px">Users</h1><p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> users</p></div>
  <a href="<?= url('admin/users/new') ?>" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add User</a>
</div>
<div class="pf-card">
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr><td><div class="d-flex align-items-center gap-2"><?= avatar($u['name'],'','28px') ?><span class="fw-600"><?= e($u['name']) ?></span></div></td>
        <td style="font-size:13px"><?= e($u['email']) ?></td>
        <td><span class="badge badge-soft-secondary"><?= e(ucfirst(str_replace('_',' ',$u['role']))) ?></span></td>
        <td><?= $u['is_active']?'<span class="badge badge-soft-success">Active</span>':'<span class="badge badge-soft-danger">Inactive</span>' ?></td>
        <td style="font-size:12px;color:var(--pf-text-3)"><?= $u['last_login']?timeAgo($u['last_login']):'Never' ?></td>
        <td><a href="<?= url('admin/users/edit?id='.$u['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-pencil"></i></a></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

<?php
requireAuth(); if(!isOrgAdmin()) redirect('/admin','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $id=(int)($_GET['id']??0); $isEdit=$id>0; $user=null; $errors=[];
if ($isEdit) { $user=$db->fetchOne("SELECT * FROM users WHERE id=? AND org_id=?",[$id,$orgId]); if(!$user) redirect('/admin/users','Not found.','danger'); }
if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $data=['name'=>trim($_POST['name']??''),'email'=>trim($_POST['email']??''),'role'=>$_POST['role']??'member','title'=>trim($_POST['title']??''),'department'=>trim($_POST['department']??''),'is_active'=>isset($_POST['is_active'])?1:0,'org_id'=>$orgId];
    if (!$data['name']) $errors[]='Name required.';
    if (!filter_var($data['email'],FILTER_VALIDATE_EMAIL)) $errors[]='Valid email required.';
    $pw=$_POST['password']??'';
    if (!$isEdit && !$pw) $errors[]='Password required.';
    if ($pw && strlen($pw)<8) $errors[]='Min 8 chars.';
    if (empty($errors)) {
        if ($pw) $data['password']=password_hash($pw,PASSWORD_DEFAULT);
        if ($isEdit) { $db->update('users',$data,'id=?',[$id]); redirect('/admin/users','User updated.','success'); }
        else { $db->insert('users',$data); audit('create','users',db()->lastId(),'User created'); redirect('/admin/users','User created.','success'); }
    }
}
$pageTitle=$isEdit?'Edit User':'Add User'; ob_start();
?>
<div style="max-width:560px">
  <a href="<?= url('admin/users') ?>" class="btn btn-ghost btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back to Users</a>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title"><?= $isEdit?'Edit User':'Add New User' ?></div></div>
    <div class="pf-card-body">
      <?php if($errors): ?><div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div><?php endif;?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Full Name <span class="req">*</span></label><input type="text" name="name" class="pf-input" value="<?= e($user['name']??'') ?>" required></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Email <span class="req">*</span></label><input type="email" name="email" class="pf-input" value="<?= e($user['email']??'') ?>" required></div>
        </div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Role</label>
            <select name="role" class="pf-select">
              <?php foreach(['org_admin','pm','member','viewer','stakeholder'] as $r): ?><option value="<?= $r ?>" <?= ($user['role']??'member')===$r?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option><?php endforeach;?>
            </select></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Password <?= !$isEdit?'<span class="req">*</span>':'' ?></label><input type="password" name="password" class="pf-input" <?= !$isEdit?'required':'' ?> minlength="8" placeholder="<?= $isEdit?'Leave blank to keep current':'' ?>"></div>
        </div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Title</label><input type="text" name="title" class="pf-input" value="<?= e($user['title']??'') ?>"></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Department</label><input type="text" name="department" class="pf-input" value="<?= e($user['department']??'') ?>"></div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
          <input type="checkbox" name="is_active" id="ia" value="1" <?= ($user['is_active']??1)?'checked':'' ?>>
          <label for="ia" class="pf-label mb-0">Active (can log in)</label>
        </div>
        <div class="d-flex gap-3">
          <button type="submit" class="btn btn-primary"><?= $isEdit?'Save Changes':'Create User' ?></button>
          <a href="<?= url('admin/users') ?>" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

<?php
requireAuth(); $db=db(); $uid=authId(); $errors=[]; $success='';
$user=$db->fetchOne("SELECT * FROM users WHERE id=?",[$uid]);
if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $name=trim($_POST['name']??''); $title=trim($_POST['title']??''); $dept=trim($_POST['department']??'');
    if ($name) $db->update('users',['name'=>$name,'title'=>$title,'department'=>$dept],'id=?',[$uid]);
    $np=$_POST['new_password']??''; $cp=$_POST['cur_password']??'';
    if ($np) {
        if (!password_verify($cp,$user['password'])) $errors[]='Current password incorrect.';
        elseif (strlen($np)<8) $errors[]='New password must be at least 8 chars.';
        else { $db->update('users',['password'=>password_hash($np,PASSWORD_DEFAULT)],'id=?',[$uid]); $success='Password changed.'; }
    } else { $success='Profile updated.'; }
    $_SESSION['pf_user']['name']=$name;
    $user=$db->fetchOne("SELECT * FROM users WHERE id=?",[$uid]);
}
$pageTitle='My Profile'; ob_start();
?>
<div style="max-width:560px">
  <h1 style="font-size:22px;font-weight:800;margin-bottom:24px">My Profile</h1>
  <?php if($errors): ?><div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div><?php endif;?>
  <?php if($success): ?><div class="pf-alert pf-alert-success mb-4"><i class="bi bi-check-circle-fill"></i><?= e($success) ?></div><?php endif;?>
  <div class="pf-card mb-4">
    <div class="pf-card-header"><div class="pf-card-title">Profile Information</div></div>
    <div class="pf-card-body">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
        <?= avatar($user['name'],'',56) ?>
        <div><div class="fw-700" style="font-size:16px"><?= e($user['name']) ?></div><div style="font-size:13px;color:var(--pf-text-3)"><?= e(ucfirst(str_replace('_',' ',$user['role']))) ?></div></div>
      </div>
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-group"><label class="pf-label">Full Name</label><input type="text" name="name" class="pf-input" value="<?= e($user['name']) ?>"></div>
        <div class="pf-form-group"><label class="pf-label">Email</label><input type="email" class="pf-input" value="<?= e($user['email']) ?>" disabled style="opacity:.6"></div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Job Title</label><input type="text" name="title" class="pf-input" value="<?= e($user['title']??'') ?>"></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Department</label><input type="text" name="department" class="pf-input" value="<?= e($user['department']??'') ?>"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
      </form>
    </div>
  </div>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title">Change Password</div></div>
    <div class="pf-card-body">
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-group"><label class="pf-label">Current Password</label><input type="password" name="cur_password" class="pf-input"></div>
        <div class="pf-form-group"><label class="pf-label">New Password</label><input type="password" name="new_password" class="pf-input" minlength="8"></div>
        <button type="submit" class="btn btn-primary">Change Password</button>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

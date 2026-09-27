<?php
requireAuth(); if(!isOrgAdmin()) redirect('/dashboard','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $org=$db->fetchOne("SELECT * FROM organizations WHERE id=?",[$orgId]);
if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $db->update('organizations',['name'=>trim($_POST['org_name']??$org['name']),'industry'=>trim($_POST['industry']??'')],'id=?',[$orgId]);
    redirect('/admin/settings','Settings saved.','success');
}
$pageTitle='Settings'; ob_start();
?>
<div style="max-width:560px">
  <h1 style="font-size:22px;font-weight:800;margin-bottom:24px">Organization Settings</h1>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title">Organization</div></div>
    <div class="pf-card-body">
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-group"><label class="pf-label">Organization Name</label><input type="text" name="org_name" class="pf-input" value="<?= e($org['name']) ?>"></div>
        <div class="pf-form-group"><label class="pf-label">Industry</label><input type="text" name="industry" class="pf-input" value="<?= e($org['industry']??'') ?>"></div>
        <div class="pf-form-group"><label class="pf-label">Plan</label><input type="text" class="pf-input" value="<?= ucfirst($org['plan']) ?>" disabled style="opacity:.6"></div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

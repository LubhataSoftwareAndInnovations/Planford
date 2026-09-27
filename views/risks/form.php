<?php
requireAuth(); if(!isPM()) redirect('/risks','Permission denied.','danger');
$db=db(); $orgId=authOrgId(); $id=(int)($_GET['id']??0); $isEdit=$id>0; $risk=null; $errors=[];
if ($isEdit) { $risk=$db->fetchOne("SELECT r.* FROM risks r JOIN programs p ON r.program_id=p.id WHERE r.id=? AND p.org_id=?",[$id,$orgId]); if(!$risk) redirect('/risks','Not found.','danger'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf()) redirect('/risks','Invalid.','danger');
    $data=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),'mitigation'=>trim($_POST['mitigation']??''),'type'=>$_POST['type']??'risk','status'=>$_POST['status']??'open','likelihood'=>$_POST['likelihood']??'medium','impact'=>$_POST['impact']??'medium','owner_id'=>(int)($_POST['owner_id']??0)?:null,'due_date'=>$_POST['due_date']??null,'program_id'=>(int)($_POST['program_id']??0)];
    if (!$data['title']) $errors[]='Title required.';
    if (empty($errors)) {
        if ($isEdit) { $db->update('risks',$data,'id=?',[$id]); redirect('/risks','Updated.','success'); }
        else { $data['created_by']=authId(); $db->insert('risks',$data); redirect('/risks','Risk logged.','success'); }
    }
}
$programs=$db->fetchAll("SELECT id,name FROM programs WHERE org_id=? ORDER BY name",[$orgId]);
$members=$db->fetchAll("SELECT id,name FROM users WHERE org_id=? AND is_active=1 ORDER BY name",[$orgId]);
$pageTitle=$isEdit?'Edit Risk':'Log Risk / Issue'; ob_start();
?>
<div style="max-width:650px">
  <a href="<?= url('risks') ?>" class="btn btn-ghost btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back</a>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title"><?= $isEdit?'Edit':'Log' ?> Risk / Issue</div></div>
    <div class="pf-card-body">
      <?php if($errors): ?><div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div><?php endif;?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Program <span class="req">*</span></label>
            <select name="program_id" class="pf-select" required><option value="">— Select —</option>
            <?php foreach($programs as $p): ?><option value="<?= $p['id'] ?>" <?= ($risk['program_id']??$_GET['program_id']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach;?></select></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Type</label>
            <select name="type" class="pf-select">
              <?php foreach(['risk','issue','dependency','assumption'] as $t): ?><option value="<?= $t ?>" <?= ($risk['type']??'risk')===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach;?>
            </select></div>
        </div>
        <div class="pf-form-group"><label class="pf-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="pf-input" value="<?= e($risk['title']??'') ?>" required></div>
        <div class="pf-form-group"><label class="pf-label">Description</label>
          <textarea name="description" class="pf-textarea"><?= e($risk['description']??'') ?></textarea></div>
        <div class="pf-form-group"><label class="pf-label">Mitigation Plan</label>
          <textarea name="mitigation" class="pf-textarea"><?= e($risk['mitigation']??'') ?></textarea></div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Impact</label>
            <select name="impact" class="pf-select">
              <?php foreach(['critical','high','medium','low'] as $v): ?><option value="<?= $v ?>" <?= ($risk['impact']??'medium')===$v?'selected':'' ?>><?= ucfirst($v) ?></option><?php endforeach;?>
            </select></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Likelihood</label>
            <select name="likelihood" class="pf-select">
              <?php foreach(['high','medium','low'] as $v): ?><option value="<?= $v ?>" <?= ($risk['likelihood']??'medium')===$v?'selected':'' ?>><?= ucfirst($v) ?></option><?php endforeach;?>
            </select></div>
        </div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Status</label>
            <select name="status" class="pf-select">
              <?php foreach(['open','monitoring','mitigated','closed'] as $v): ?><option value="<?= $v ?>" <?= ($risk['status']??'open')===$v?'selected':'' ?>><?= ucfirst($v) ?></option><?php endforeach;?>
            </select></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Owner</label>
            <select name="owner_id" class="pf-select"><option value="">— None —</option>
            <?php foreach($members as $m): ?><option value="<?= $m['id'] ?>" <?= ($risk['owner_id']??'')==$m['id']?'selected':'' ?>><?= e($m['name']) ?></option><?php endforeach;?>
            </select></div>
        </div>
        <div class="d-flex gap-3">
          <button type="submit" class="btn btn-primary"><?= $isEdit?'Save':'Log Risk' ?></button>
          <a href="<?= url('risks') ?>" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

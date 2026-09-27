<?php
requireAuth(); if (!isPM()) redirect('/milestones','Permission denied.','danger');
$db=$db??db(); $orgId=authOrgId(); $id=(int)($_GET['id']??0); $isEdit=$id>0; $ms=null; $errors=[];
if ($isEdit) { $ms=$db->fetchOne("SELECT m.* FROM milestones m JOIN programs p ON m.program_id=p.id WHERE m.id=? AND p.org_id=?",[$id,$orgId]); if(!$ms) redirect('/milestones','Not found.','danger'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf()) redirect('/milestones','Invalid request.','danger');
    $data=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),'due_date'=>$_POST['due_date']??null,'achieved_date'=>$_POST['achieved_date']??null,'status'=>$_POST['status']??'pending','is_client_visible'=>isset($_POST['is_client_visible'])?1:0,'program_id'=>(int)($_POST['program_id']??0)];
    if (!$data['title']) $errors[]='Title required.';
    if (!$data['due_date']) $errors[]='Due date required.';
    if (empty($errors)) {
        if ($isEdit) { $db->update('milestones',$data,'id=?',[$id]); redirect('/milestones','Updated.','success'); }
        else { $data['created_by']=authId(); $db->insert('milestones',$data); redirect('/milestones','Milestone added.','success'); }
    }
}
$programs=$db->fetchAll("SELECT id,name FROM programs WHERE org_id=? ORDER BY name",[$orgId]);
$pageTitle=$isEdit?'Edit Milestone':'New Milestone'; ob_start();
?>
<div style="max-width:600px">
  <a href="<?= url('milestones') ?>" class="btn btn-ghost btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back</a>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title"><?= $isEdit?'Edit':'New' ?> Milestone</div></div>
    <div class="pf-card-body">
      <?php if($errors): ?><div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div><?php endif; ?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="pf-form-group"><label class="pf-label">Program <span class="req">*</span></label>
          <select name="program_id" class="pf-select" required>
            <option value="">— Select —</option>
            <?php foreach($programs as $p): ?><option value="<?= $p['id'] ?>" <?= ($ms['program_id']??$_GET['program_id']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="pf-form-group"><label class="pf-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="pf-input" value="<?= e($ms['title']??'') ?>" required></div>
        <div class="pf-form-group"><label class="pf-label">Description</label>
          <textarea name="description" class="pf-textarea"><?= e($ms['description']??'') ?></textarea></div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Due Date <span class="req">*</span></label>
            <input type="date" name="due_date" class="pf-input" value="<?= e($ms['due_date']??'') ?>" required></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Achieved Date</label>
            <input type="date" name="achieved_date" class="pf-input" value="<?= e($ms['achieved_date']??'') ?>"></div>
        </div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Status</label>
            <select name="status" class="pf-select">
              <?php foreach(['pending','achieved','delayed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= ($ms['status']??'pending')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
            </select></div>
          <div class="pf-form-group mb-0" style="display:flex;align-items:center;padding-top:24px;gap:10px">
            <input type="checkbox" name="is_client_visible" id="cv" value="1" <?= ($ms['is_client_visible']??1)?'checked':'' ?>>
            <label for="cv" class="pf-label mb-0">Client Visible (shown in portal)</label>
          </div>
        </div>
        <div class="d-flex gap-3">
          <button type="submit" class="btn btn-primary"><?= $isEdit?'Save':'Add Milestone' ?></button>
          <a href="<?= url('milestones') ?>" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

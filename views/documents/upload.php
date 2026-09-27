<?php
requireAuth(); $db=db(); $orgId=authOrgId(); $errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf()) redirect('/documents','Invalid request.','danger');
    $title = trim($_POST['title']??'');
    $programId = (int)($_POST['program_id']??0)?:null;
    if (!$title) $errors[]='Title required.';
    if (empty($_FILES['file']['name'])) $errors[]='Please select a file.';
    if (empty($errors)) {
        $upload = uploadFile($_FILES['file'],'documents');
        if (!$upload['ok']) { $errors[] = $upload['error']; }
        else {
            $db->insert('documents',['org_id'=>$orgId,'program_id'=>$programId,'title'=>$title,'description'=>trim($_POST['description']??''),'category'=>$_POST['category']??'general','filename'=>$upload['filename'],'origname'=>$upload['origname'],'size'=>$upload['size'],'mime'=>$upload['mime'],'version'=>$_POST['version']??'1.0','status'=>$_POST['doc_status']??'draft','is_client_visible'=>isset($_POST['client_visible'])?1:0,'uploaded_by'=>authId()]);
            audit('upload','documents',db()->lastId(),'Document uploaded: '.$title);
            redirect('/documents','Document uploaded.','success');
        }
    }
}
$programs=$db->fetchAll("SELECT id,name FROM programs WHERE org_id=? ORDER BY name",[$orgId]);
$pageTitle='Upload Document'; ob_start();
?>
<div style="max-width:600px">
  <a href="<?= url('documents') ?>" class="btn btn-ghost btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back</a>
  <div class="pf-card">
    <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-upload me-2"></i>Upload Document</div></div>
    <div class="pf-card-body">
      <?php if($errors): ?><div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div><?php endif;?>
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="pf-form-group"><label class="pf-label">Title <span class="req">*</span></label><input type="text" name="title" class="pf-input" required></div>
        <div class="pf-form-group"><label class="pf-label">File <span class="req">*</span></label><input type="file" name="file" class="pf-input" required></div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Program</label>
            <select name="program_id" class="pf-select"><option value="">General / Org-wide</option>
            <?php foreach($programs as $p): ?><option value="<?= $p['id'] ?>" <?= ($_GET['program_id']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach;?></select></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Category</label>
            <select name="category" class="pf-select">
              <?php foreach(['general','specification','design','report','contract','compliance','other'] as $c): ?><option><?= ucfirst($c) ?></option><?php endforeach;?>
            </select></div>
        </div>
        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0"><label class="pf-label">Version</label><input type="text" name="version" class="pf-input" value="1.0" placeholder="1.0"></div>
          <div class="pf-form-group mb-0"><label class="pf-label">Status</label>
            <select name="doc_status" class="pf-select">
              <?php foreach(['draft','review','approved'] as $s): ?><option value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach;?>
            </select></div>
        </div>
        <div class="pf-form-group"><label class="pf-label">Description</label><textarea name="description" class="pf-textarea" rows="2"></textarea></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
          <input type="checkbox" name="client_visible" id="cv" value="1">
          <label for="cv" class="pf-label mb-0">Visible in client portal</label>
        </div>
        <div class="d-flex gap-3">
          <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
          <a href="<?= url('documents') ?>" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

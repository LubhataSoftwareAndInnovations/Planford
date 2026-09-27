<?php
requireAuth(); $db=db(); $orgId=authOrgId(); $page=max(1,(int)($_GET['page']??1));
$docs=$db->fetchAll("SELECT d.*,u.name as uploader_name,p.name as program_name FROM documents d JOIN users u ON d.uploaded_by=u.id LEFT JOIN programs p ON d.program_id=p.id WHERE d.org_id=? ORDER BY d.created_at DESC LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),[$orgId]);
$total=$db->count('documents','org_id=?',[$orgId]);
$pageTitle='Documents'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div><h1 style="font-size:22px;font-weight:800;margin-bottom:4px">Documents</h1><p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> document<?= $total!=1?'s':'' ?></p></div>
  <a href="<?= url('documents/upload') ?>" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Document</a>
</div>
<div class="pf-card">
  <?php if(empty($docs)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-folder2"></i></div><div class="pf-empty-title">No documents yet</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Document</th><th>Program</th><th>Category</th><th>Version</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
      <tbody>
        <?php foreach($docs as $d): ?>
        <tr>
          <td><div class="d-flex align-items-center gap-2"><i class="bi <?= fileIcon($d['mime']??'',pathinfo($d['origname'],PATHINFO_EXTENSION)) ?> fs-18"></i><div><div class="fw-600"><?= e($d['title']) ?></div><div style="font-size:11.5px;color:var(--pf-text-3)"><?= e($d['origname']) ?> · <?= formatBytes($d['size']) ?></div></div></div></td>
          <td style="font-size:13px"><?= e($d['program_name']??'General') ?></td>
          <td><span class="badge badge-soft-secondary"><?= e(ucfirst($d['category'])) ?></span></td>
          <td style="font-size:13px">v<?= e($d['version']) ?></td>
          <td><?= statusBadge($d['status']) ?></td>
          <td style="font-size:12px;color:var(--pf-text-3)"><?= e($d['uploader_name']) ?><br><?= fDate($d['created_at']) ?></td>
          <td><a href="<?= url('documents/download?id='.$d['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-download"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

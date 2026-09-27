<?php
requireAuth();
$db = db(); $orgId = authOrgId();
$filterStatus = $_GET['status'] ?? 'all';
$page = max(1,(int)($_GET['page']??1));
$where = 'p.org_id=?'; $params = [$orgId];
if ($filterStatus !== 'all') { $where .= ' AND m.status=?'; $params[] = $filterStatus; }
$total = $db->fetchColumn("SELECT COUNT(*) FROM milestones m JOIN programs p ON m.program_id=p.id WHERE $where", $params);
$milestones = $db->fetchAll(
    "SELECT m.*, p.name as program_name, p.cover_color FROM milestones m
     JOIN programs p ON m.program_id=p.id WHERE $where ORDER BY m.due_date LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE), $params
);
$pageTitle = 'Milestones'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div>
    <h1 style="font-size:22px;font-weight:800;margin-bottom:4px">Milestones</h1>
    <p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> milestone<?= $total!=1?'s':'' ?></p>
  </div>
</div>
<div class="d-flex gap-2 mb-4 flex-wrap">
  <?php foreach(['all'=>'All','pending'=>'Pending','achieved'=>'Achieved','delayed'=>'Delayed','cancelled'=>'Cancelled'] as $v=>$l): ?>
  <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filterStatus===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="pf-card">
  <?php if(empty($milestones)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-flag"></i></div><div class="pf-empty-title">No milestones found</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Milestone</th><th>Program</th><th>Due Date</th><th>Status</th><th>Client Visible</th></tr></thead>
      <tbody>
        <?php foreach($milestones as $m): ?>
        <tr>
          <td><div class="fw-600"><?= e($m['title']) ?></div></td>
          <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($m['cover_color']) ?>;margin-right:5px"></span><?= e($m['program_name']) ?></td>
          <td><?= fDate($m['due_date']) ?><br><?= daysLeft($m['due_date'],$m['status']) ?></td>
          <td><?= statusBadge($m['status']) ?></td>
          <td><?= $m['is_client_visible']?'<span class="badge badge-soft-success">Yes</span>':'<span class="badge badge-soft-secondary">No</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

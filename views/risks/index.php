<?php
requireAuth(); $db=db(); $orgId=authOrgId();
$filterStatus=$_GET['status']??'open'; $page=max(1,(int)($_GET['page']??1));
$where='p.org_id=?'; $params=[$orgId];
if ($filterStatus!=='all') { $where.=' AND r.status=?'; $params[]=$filterStatus; }
$total=$db->fetchColumn("SELECT COUNT(*) FROM risks r JOIN programs p ON r.program_id=p.id WHERE $where",$params);
$risks=$db->fetchAll("SELECT r.*,p.name as program_name,p.cover_color,u.name as owner_name FROM risks r JOIN programs p ON r.program_id=p.id LEFT JOIN users u ON r.owner_id=u.id WHERE $where ORDER BY FIELD(r.impact,'critical','high','medium','low'),r.created_at DESC LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),$params);
$pageTitle='Risks & Issues'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div><h1 style="font-size:22px;font-weight:800;margin-bottom:4px">Risks & Issues</h1><p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> items</p></div>
  <?php if(isPM()):?><a href="<?= url('risks/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Log Risk / Issue</a><?php endif;?>
</div>
<div class="d-flex gap-2 mb-4 flex-wrap">
  <?php foreach(['open'=>'Open','monitoring'=>'Monitoring','mitigated'=>'Mitigated','closed'=>'Closed','all'=>'All'] as $v=>$l): ?>
  <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filterStatus===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="pf-card">
  <?php if(empty($risks)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-shield-check"></i></div><div class="pf-empty-title">No risks found</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Title</th><th>Program</th><th>Type</th><th>Impact</th><th>Likelihood</th><th>Status</th><th>Owner</th><?php if(isPM()):?><th></th><?php endif;?></tr></thead>
      <tbody>
        <?php foreach($risks as $r): ?>
        <tr>
          <td><div class="fw-600"><?= e($r['title']) ?></div><?php if($r['mitigation']): ?><div style="font-size:12px;color:var(--pf-text-3)"><?= e(truncate($r['mitigation'],60)) ?></div><?php endif;?></td>
          <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($r['cover_color']) ?>;margin-right:5px"></span><?= e($r['program_name']) ?></td>
          <td><span class="badge badge-soft-secondary"><?= ucfirst($r['type']) ?></span></td>
          <td><?= priorityBadge($r['impact']) ?></td>
          <td><?= priorityBadge($r['likelihood']) ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td style="font-size:13px"><?= e($r['owner_name']??'—') ?></td>
          <?php if(isPM()):?><td><a href="<?= url('risks/edit?id='.$r['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-pencil"></i></a></td><?php endif;?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

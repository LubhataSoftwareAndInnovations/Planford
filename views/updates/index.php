<?php
requireAuth(); $db=db(); $orgId=authOrgId(); $page=max(1,(int)($_GET['page']??1));
$updates=$db->fetchAll("SELECT u.*,us.name as user_name,p.name as program_name,p.cover_color,t.title as task_title FROM updates u JOIN users us ON u.user_id=us.id JOIN programs p ON u.program_id=p.id LEFT JOIN tasks t ON u.task_id=t.id WHERE p.org_id=? ORDER BY u.created_at DESC LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),[$orgId]);
$total=$db->fetchColumn("SELECT COUNT(*) FROM updates u JOIN programs p ON u.program_id=p.id WHERE p.org_id=?",[$orgId]);
$pageTitle='Activity Feed'; ob_start();
?>
<h1 style="font-size:22px;font-weight:800;margin-bottom:24px">Activity Feed</h1>
<?php if(empty($updates)): ?><div class="pf-card"><div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-activity"></i></div><div class="pf-empty-title">No activity yet</div></div></div>
<?php else: ?>
<div class="pf-feed">
  <?php foreach($updates as $u): ?>
  <div class="pf-feed-item">
    <div class="pf-feed-dot"><?= avatar($u['user_name'],colorForString($u['user_name']),'38px') ?></div>
    <div class="pf-feed-content">
      <div class="d-flex align-items-center justify-content-between mb-1">
        <div><strong><?= e($u['user_name']) ?></strong> <span style="font-size:12px;color:var(--pf-text-3)">in</span> <a href="<?= url('programs/view?id='.$u['program_id'].'&tab=updates') ?>" style="color:var(--pf-indigo);font-size:13px"><?= e($u['program_name']) ?></a></div>
        <span style="font-size:12px;color:var(--pf-text-3)"><?= timeAgo($u['created_at']) ?></span>
      </div>
      <?php if($u['task_title']): ?><div style="font-size:11.5px;color:var(--pf-text-3);margin-bottom:6px"><i class="bi bi-check2-square"></i> <?= e($u['task_title']) ?></div><?php endif; ?>
      <div style="font-size:13.5px;line-height:1.6"><?= nl2br(e($u['body'])) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div style="margin-top:16px"><?= pagination($total,$page,PER_PAGE,url('updates')) ?></div>
<?php endif; ?>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

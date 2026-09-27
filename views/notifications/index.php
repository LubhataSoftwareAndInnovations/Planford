<?php
requireAuth(); $db=db(); $uid=authId(); $page=max(1,(int)($_GET['page']??1));
if ($_GET['mark_all'] ?? false) { $db->query("UPDATE notifications SET is_read=1 WHERE user_id=?",[$uid]); redirect('/notifications','All notifications marked as read.','success'); }
$total=$db->count('notifications','user_id=?',[$uid]);
$notifs=$db->fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ".PER_PAGE." OFFSET ".(($page-1)*PER_PAGE),[$uid]);
$pageTitle='Notifications'; ob_start();
?>
<div class="d-flex align-items-center justify-content-between mb-5">
  <h1 style="font-size:22px;font-weight:800">Notifications</h1>
  <a href="?mark_all=1" class="btn btn-outline btn-sm"><i class="bi bi-check-all"></i> Mark all read</a>
</div>
<div class="pf-card">
  <?php if(empty($notifs)): ?><div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-bell-slash"></i></div><div class="pf-empty-title">No notifications</div></div>
  <?php else: foreach($notifs as $n): $unread=!$n['is_read']; ?>
  <div class="d-flex align-items-start gap-3 p-3" style="border-bottom:1px solid var(--pf-border);background:<?= $unread?'rgba(99,102,241,.04)':'transparent' ?>">
    <div style="width:8px;height:8px;border-radius:50%;background:<?= $unread?'var(--pf-indigo)':'transparent' ?>;margin-top:6px;flex-shrink:0"></div>
    <div style="flex:1">
      <div class="fw-600" style="font-size:13.5px"><?= e($n['title']) ?></div>
      <?php if($n['body']): ?><div style="font-size:13px;color:var(--pf-text-2);margin-top:2px"><?= e($n['body']) ?></div><?php endif;?>
      <div style="font-size:11.5px;color:var(--pf-text-3);margin-top:4px"><?= timeAgo($n['created_at']) ?></div>
    </div>
    <?= statusBadge($n['type']) ?>
  </div>
  <?php endforeach; endif; ?>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

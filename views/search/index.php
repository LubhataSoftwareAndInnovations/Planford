<?php
requireAuth(); $db=db(); $orgId=authOrgId();
$q = trim($_GET['q'] ?? '');
$results = ['programs'=>[],'tasks'=>[],'documents'=>[]];
if (strlen($q) >= 2) {
    $like = "%$q%";
    $results['programs'] = $db->fetchAll("SELECT id,name,code,status,cover_color FROM programs WHERE org_id=? AND (name LIKE ? OR code LIKE ? OR description LIKE ?) LIMIT 10",[$orgId,$like,$like,$like]);
    $results['tasks'] = $db->fetchAll("SELECT t.id,t.title,t.status,t.priority,p.name as program_name,p.cover_color FROM tasks t JOIN programs p ON t.program_id=p.id WHERE p.org_id=? AND (t.title LIKE ? OR t.description LIKE ?) LIMIT 10",[$orgId,$like,$like]);
    $results['documents'] = $db->fetchAll("SELECT d.id,d.title,d.origname,d.mime FROM documents d WHERE d.org_id=? AND (d.title LIKE ? OR d.origname LIKE ?) LIMIT 8",[$orgId,$like,$like]);
}
$total = array_sum(array_map('count',$results));
$pageTitle = 'Search'; ob_start();
?>
<h1 style="font-size:22px;font-weight:800;margin-bottom:20px">Search</h1>
<form method="GET" class="d-flex gap-3 mb-6" style="max-width:500px">
  <div class="pf-input-group flex-1">
    <input type="text" name="q" class="pf-input" value="<?= e($q) ?>" placeholder="Search programs, tasks, documents…" autofocus>
    <button type="submit" class="btn btn-primary">Search</button>
  </div>
</form>
<?php if ($q): ?>
<div style="margin-bottom:20px;font-size:13.5px;color:var(--pf-text-3)"><?= $total ?> result<?= $total!=1?'s':'' ?> for "<strong><?= e($q) ?></strong>"</div>
<?php if(!empty($results['programs'])): ?>
<div class="pf-card mb-4"><div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-collection me-2"></i>Programs</div></div>
<?php foreach($results['programs'] as $p): ?>
<a href="<?= url('programs/view?id='.$p['id']) ?>" style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--pf-border);text-decoration:none;color:var(--pf-text)">
  <div style="width:4px;height:40px;border-radius:2px;background:<?= e($p['cover_color']) ?>"></div>
  <div><div class="fw-600"><?= e($p['name']) ?></div><div style="font-size:12px;color:var(--pf-text-3)"><?= e($p['code']) ?> · <?= statusBadge($p['status']) ?></div></div>
</a>
<?php endforeach; ?></div><?php endif; ?>
<?php if(!empty($results['tasks'])): ?>
<div class="pf-card mb-4"><div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-check2-square me-2"></i>Tasks</div></div>
<?php foreach($results['tasks'] as $t): ?>
<a href="<?= url('tasks/view?id='.$t['id']) ?>" class="pf-task-row" style="text-decoration:none">
  <div style="flex:1"><div class="fw-600"><?= e($t['title']) ?></div><div style="font-size:12px;color:var(--pf-text-3)"><?= e($t['program_name']) ?></div></div>
  <?= statusBadge($t['status']) ?><?= priorityBadge($t['priority']) ?>
</a>
<?php endforeach; ?></div><?php endif; ?>
<?php if($total===0): ?><div class="pf-card"><div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-search"></i></div><div class="pf-empty-title">No results found</div><div class="pf-empty-sub">Try different keywords</div></div></div><?php endif; ?>
<?php endif; ?>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';

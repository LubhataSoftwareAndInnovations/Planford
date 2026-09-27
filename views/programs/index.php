<?php
requireAuth();
$db    = db();
$orgId = authOrgId();

$search     = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? 'all';
$page       = max(1, (int)($_GET['page'] ?? 1));

$where  = 'p.org_id=?';
$params = [$orgId];
if ($filterStatus !== 'all') { $where .= ' AND p.status=?'; $params[] = $filterStatus; }
if ($search) { $where .= ' AND (p.name LIKE ? OR p.code LIKE ? OR p.client_name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$total    = db()->fetchColumn("SELECT COUNT(*) FROM programs p WHERE $where", $params);
$offset   = ($page - 1) * PER_PAGE;
$programs = db()->fetchAll(
    "SELECT p.*, u.name as owner_name,
     (SELECT COUNT(*) FROM tasks WHERE program_id=p.id AND status='completed') as done_tasks,
     (SELECT COUNT(*) FROM tasks WHERE program_id=p.id) as total_tasks,
     (SELECT COUNT(*) FROM milestones WHERE program_id=p.id AND status='pending' AND due_date < CURDATE()) as overdue_milestones
     FROM programs p LEFT JOIN users u ON p.owner_id=u.id
     WHERE $where
     ORDER BY FIELD(p.status,'active','planning','on_hold','completed','cancelled'), p.updated_at DESC
     LIMIT " . PER_PAGE . " OFFSET $offset", $params
);

$statusCounts = db()->fetchAll(
    "SELECT status, COUNT(*) as cnt FROM programs WHERE org_id=? GROUP BY status", [$orgId]
);
$scMap = ['all' => $total];
foreach ($statusCounts as $s) $scMap[$s['status']] = $s['cnt'];

$pageTitle = 'Programs';
ob_start();
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-6 flex-wrap gap-3">
  <div>
    <h1 style="font-size:22px;font-weight:800;color:var(--pf-text);margin-bottom:4px">Programs</h1>
    <p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> program<?= $total!=1?'s':'' ?> in your organization</p>
  </div>
  <?php if (isPM()): ?>
  <a href="<?= url('programs/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Program</a>
  <?php endif; ?>
</div>

<!-- Filter bar -->
<div class="d-flex align-items-center gap-3 mb-5 flex-wrap">
  <?php
  $statuses = ['all'=>'All', 'active'=>'Active', 'planning'=>'Planning', 'on_hold'=>'On Hold', 'completed'=>'Completed', 'cancelled'=>'Cancelled'];
  foreach ($statuses as $val => $label):
    $active = $filterStatus === $val ? 'btn-primary' : 'btn-outline';
    $cnt    = $scMap[$val] ?? 0;
  ?>
  <a href="?status=<?= $val ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="btn btn-sm <?= $active ?>">
    <?= $label ?> <span style="opacity:.6">(<?= $cnt ?>)</span>
  </a>
  <?php endforeach; ?>

  <form method="GET" class="d-flex gap-2 ms-auto">
    <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
    <div class="pf-search" style="min-width:200px">
      <i class="bi bi-search" style="color:var(--pf-text-3);font-size:13px"></i>
      <input type="text" name="search" placeholder="Search programs…" value="<?= e($search) ?>">
    </div>
    <button class="btn btn-outline">Search</button>
    <?php if ($search): ?><a href="?status=<?= $filterStatus ?>" class="btn btn-ghost">Clear</a><?php endif; ?>
  </form>
</div>

<!-- Programs grid -->
<?php if (empty($programs)): ?>
<div class="pf-card">
  <div class="pf-empty">
    <div class="pf-empty-icon"><i class="bi bi-collection"></i></div>
    <div class="pf-empty-title">No programs found</div>
    <div class="pf-empty-sub"><?= $search ? 'Try a different search.' : 'Create your first program to get started.' ?></div>
    <?php if (isPM() && !$search): ?>
    <a href="<?= url('programs/new') ?>" class="btn btn-primary">Create Program</a>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="pf-program-grid">
  <?php foreach ($programs as $p):
    $prog = $p['total_tasks'] > 0 ? round($p['done_tasks'] / $p['total_tasks'] * 100) : 0;
  ?>
  <a href="<?= url('programs/view?id=' . $p['id']) ?>" class="pf-program-card">
    <div class="pf-program-cover" style="background:<?= e($p['cover_color']) ?>"></div>
    <div class="pf-program-body">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="pf-program-code"><?= e($p['code']) ?></div>
        <?= statusBadge($p['status']) ?>
      </div>
      <div class="pf-program-name"><?= e($p['name']) ?></div>
      <?php if ($p['client_name']): ?>
      <div style="font-size:12px;color:var(--pf-text-3);margin-bottom:4px"><i class="bi bi-building"></i> <?= e($p['client_name']) ?></div>
      <?php endif; ?>
      <?php if ($p['description']): ?>
      <div style="font-size:12.5px;color:var(--pf-text-3);margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($p['description']) ?></div>
      <?php endif; ?>
      <div style="margin-bottom:8px">
        <?= progressBar($prog) ?>
      </div>
      <div class="pf-program-meta">
        <span><i class="bi bi-check2-square"></i> <?= $p['done_tasks'] ?>/<?= $p['total_tasks'] ?> tasks</span>
        <?php if ($p['overdue_milestones'] > 0): ?>
        <span style="color:var(--pf-red)"><i class="bi bi-flag-fill"></i> <?= $p['overdue_milestones'] ?> overdue</span>
        <?php endif; ?>
        <?php if ($p['end_date']): ?>
        <span><?= fDate($p['end_date']) ?></span>
        <?php endif; ?>
        <?php if ($p['owner_name']): ?>
        <span class="ms-auto"><?= avatar($p['owner_name'], '', '22px') ?></span>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<div style="margin-top:24px">
  <?= pagination($total, $page, PER_PAGE, url('programs') . '?status=' . $filterStatus . ($search ? '&search=' . urlencode($search) : '')) ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

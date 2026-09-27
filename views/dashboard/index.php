<?php
requireAuth();
$db = db();
$orgId = authOrgId();
$uid   = authId();

// KPIs
$totalPrograms  = $db->count('programs', 'org_id=?', [$orgId]);
$activePrograms = $db->count('programs', "org_id=? AND status='active'", [$orgId]);
$totalTasks     = $db->fetchColumn("SELECT COUNT(*) FROM tasks t JOIN programs p ON t.program_id=p.id WHERE p.org_id=?", [$orgId]);
$myTasks        = $db->count('tasks', 'assigned_to=?', [$uid]);
$overdueTasks   = $db->fetchColumn(
    "SELECT COUNT(*) FROM tasks t JOIN programs p ON t.program_id=p.id
     WHERE p.org_id=? AND t.due_date < CURDATE() AND t.status NOT IN ('completed','cancelled')", [$orgId]
);
$completedTasks = $db->fetchColumn(
    "SELECT COUNT(*) FROM tasks t JOIN programs p ON t.program_id=p.id WHERE p.org_id=? AND t.status='completed'", [$orgId]
);
$completionRate = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;

// My open tasks (quick view)
$myOpenTasks = $db->fetchAll(
    "SELECT t.*, p.name as program_name, p.cover_color
     FROM tasks t JOIN programs p ON t.program_id=p.id
     WHERE t.assigned_to=? AND t.status NOT IN ('completed','cancelled')
     ORDER BY FIELD(t.priority,'critical','high','medium','low'), t.due_date ASC
     LIMIT 8", [$uid]
);

// Active programs
$programs = $db->fetchAll(
    "SELECT p.*, u.name as owner_name,
     (SELECT COUNT(*) FROM tasks WHERE program_id=p.id AND status='completed') as done_tasks,
     (SELECT COUNT(*) FROM tasks WHERE program_id=p.id) as total_tasks
     FROM programs p LEFT JOIN users u ON p.owner_id=u.id
     WHERE p.org_id=? AND p.status IN ('active','planning')
     ORDER BY p.updated_at DESC LIMIT 6", [$orgId]
);

// Upcoming milestones
$milestones = $db->fetchAll(
    "SELECT m.*, p.name as program_name, p.cover_color
     FROM milestones m JOIN programs p ON m.program_id=p.id
     WHERE p.org_id=? AND m.status='pending' AND m.due_date >= CURDATE()
     ORDER BY m.due_date ASC LIMIT 5", [$orgId]
);

// Recent activity
$activity = $db->fetchAll(
    "SELECT u.*, us.name as user_name, p.name as program_name, p.cover_color,
     t.title as task_title
     FROM updates u
     JOIN users us ON u.user_id=us.id
     JOIN programs p ON u.program_id=p.id
     LEFT JOIN tasks t ON u.task_id=t.id
     WHERE p.org_id=?
     ORDER BY u.created_at DESC LIMIT 10", [$orgId]
);

// Task status breakdown for chart
$taskBreakdown = $db->fetchAll(
    "SELECT t.status, COUNT(*) as cnt FROM tasks t
     JOIN programs p ON t.program_id=p.id
     WHERE p.org_id=? GROUP BY t.status", [$orgId]
);
$statusMap = [];
foreach ($taskBreakdown as $r) $statusMap[$r['status']] = (int)$r['cnt'];

$pageTitle    = 'Executive Control Tower';
$pageSubtitle = 'Enterprise Program & Portfolio Delivery Control Tower';

// EVM & Agile portfolio rollup
$defaultProgram = $programs[0] ?? null;
$portfolioEvm = $defaultProgram ? EVMEngine::calculateForProgram($defaultProgram['id']) : null;

ob_start();
?>

<!-- Executive Control Tower Banner -->
<div class="pf-card mb-4" style="background:linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(6,182,212,0.05) 100%);border:1px solid rgba(99,102,241,0.2);padding:20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
        <h4 style="margin:0;font-weight:800;color:var(--pf-text)">Enterprise Executive Control Tower</h4>
        <?= ragBadge($portfolioEvm['rag'] ?? 'green') ?>
      </div>
      <div style="font-size:13px;color:var(--pf-text-2)">
        Multi-Methodology Program Delivery Engine (Scrum, Kanban, Waterfall, EVM, iQMS)
      </div>
    </div>

    <!-- Quick Module Navigation Bar -->
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="<?= url('agile/board') ?>" class="pf-btn pf-btn-sm pf-btn-primary">
        <i class="bi bi-kanban me-1"></i> Sprint Board
      </a>
      <a href="<?= url('sprints') ?>" class="pf-btn pf-btn-sm pf-btn-secondary">
        <i class="bi bi-arrow-repeat me-1"></i> Sprints
      </a>
      <a href="<?= url('governance/gates') ?>" class="pf-btn pf-btn-sm pf-btn-secondary">
        <i class="bi bi-shield-check me-1"></i> Quality Gates
      </a>
      <a href="<?= url('capacity') ?>" class="pf-btn pf-btn-sm pf-btn-secondary">
        <i class="bi bi-person-badge me-1"></i> RACI Matrix
      </a>
      <a href="<?= url('evm') ?>" class="pf-btn pf-btn-sm pf-btn-secondary">
        <i class="bi bi-graph-up-arrow me-1"></i> EVM Financials
      </a>
    </div>
  </div>
</div>

<!-- KPI Row -->
<div class="pf-kpi-grid mb-6">
  <div class="pf-kpi" style="--kpi-color:var(--pf-indigo)">
    <div class="pf-kpi-icon"><i class="bi bi-collection"></i></div>
    <div class="pf-kpi-value"><?= $activePrograms ?></div>
    <div class="pf-kpi-label">Active Programs</div>
    <div class="pf-kpi-sub"><?= $totalPrograms ?> total</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-blue)">
    <div class="pf-kpi-icon"><i class="bi bi-check2-square"></i></div>
    <div class="pf-kpi-value"><?= $myTasks ?></div>
    <div class="pf-kpi-label">My Tasks</div>
    <div class="pf-kpi-sub">assigned to me</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-red)">
    <div class="pf-kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
    <div class="pf-kpi-value"><?= $overdueTasks ?></div>
    <div class="pf-kpi-label">Overdue Tasks</div>
    <div class="pf-kpi-sub">need attention</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-green)">
    <div class="pf-kpi-icon"><i class="bi bi-graph-up"></i></div>
    <div class="pf-kpi-value"><?= $completionRate ?>%</div>
    <div class="pf-kpi-label">Completion Rate</div>
    <div class="pf-kpi-sub"><?= $completedTasks ?>/<?= $totalTasks ?> tasks</div>
  </div>
</div>

<div class="d-flex gap-5 flex-wrap" style="align-items:flex-start">

  <!-- Left col: programs + tasks -->
  <div style="flex:1;min-width:0">

    <!-- Active Programs -->
    <div class="pf-card mb-5">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-collection me-2" style="color:var(--pf-indigo)"></i>Active Programs</div>
        <?php if (isPM()): ?>
        <a href="<?= url('programs/new') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New</a>
        <?php endif; ?>
      </div>
      <?php if (empty($programs)): ?>
      <div class="pf-empty">
        <div class="pf-empty-icon"><i class="bi bi-collection"></i></div>
        <div class="pf-empty-title">No programs yet</div>
        <div class="pf-empty-sub">Create your first program to get started</div>
        <?php if (isPM()): ?>
        <a href="<?= url('programs/new') ?>" class="btn btn-primary">Create Program</a>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div style="padding:16px">
        <div class="pf-program-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
          <?php foreach ($programs as $p):
            $prog = $p['total_tasks'] > 0 ? round($p['done_tasks']/$p['total_tasks']*100) : 0;
          ?>
          <a href="<?= url('programs/view?id=' . $p['id']) ?>" class="pf-program-card">
            <div class="pf-program-cover" style="background:<?= e($p['cover_color']) ?>"></div>
            <div class="pf-program-body">
              <div class="pf-program-code"><?= e($p['code']) ?></div>
              <div class="pf-program-name truncate"><?= e($p['name']) ?></div>
              <?= statusBadge($p['status']) ?>
              <div style="margin-top:12px">
                <?= progressBar($prog) ?>
              </div>
              <div class="pf-program-meta">
                <span><i class="bi bi-check2-square"></i> <?= $p['done_tasks'] ?>/<?= $p['total_tasks'] ?></span>
                <?php if ($p['end_date']): ?>
                <span><?= daysLeft($p['end_date'], $p['status']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <div style="text-align:right;margin-top:12px">
          <a href="<?= url('programs') ?>" class="btn btn-ghost btn-sm">View all programs <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- My open tasks -->
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-person-check me-2" style="color:var(--pf-blue)"></i>My Open Tasks</div>
        <a href="<?= url('tasks') ?>" class="btn btn-ghost btn-sm">View all</a>
      </div>
      <?php if (empty($myOpenTasks)): ?>
      <div class="pf-empty" style="padding:40px">
        <div class="pf-empty-icon"><i class="bi bi-check2-all"></i></div>
        <div class="pf-empty-title">All clear!</div>
        <div class="pf-empty-sub">No open tasks assigned to you</div>
      </div>
      <?php else: ?>
      <?php foreach ($myOpenTasks as $t): ?>
      <a href="<?= url('tasks/view?id=' . $t['id']) ?>" class="pf-task-row" style="text-decoration:none">
        <div class="pf-task-check <?= $t['status']==='completed'?'done':'' ?>">
          <?php if($t['status']==='completed'): ?><i class="bi bi-check" style="font-size:11px"></i><?php endif; ?>
        </div>
        <div style="flex:1;min-width:0">
          <div class="pf-task-title truncate <?= $t['status']==='completed'?'done':'' ?>"><?= e($t['title']) ?></div>
          <div style="font-size:11.5px;color:var(--pf-text-3);margin-top:2px">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($t['cover_color']) ?>;margin-right:4px"></span>
            <?= e($t['program_name']) ?>
          </div>
        </div>
        <?= priorityBadge($t['priority']) ?>
        <?= statusBadge($t['status']) ?>
        <?php if ($t['due_date']): ?>
        <span style="font-size:11.5px;white-space:nowrap"><?= daysLeft($t['due_date'], $t['status']) ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>

  <!-- Right col: chart + milestones + activity -->
  <div style="width:300px;flex-shrink:0">

    <!-- Task breakdown donut -->
    <div class="pf-card mb-4">
      <div class="pf-card-header"><div class="pf-card-title">Task Breakdown</div></div>
      <div class="pf-card-body" style="padding:16px">
        <canvas id="taskDonut" height="200"></canvas>
      </div>
    </div>

    <!-- Upcoming milestones -->
    <div class="pf-card mb-4">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-flag me-2" style="color:var(--pf-amber)"></i>Upcoming Milestones</div>
      </div>
      <?php if (empty($milestones)): ?>
      <div style="padding:20px;text-align:center;color:var(--pf-text-3);font-size:13px">No upcoming milestones</div>
      <?php else: foreach ($milestones as $m): ?>
      <div style="padding:12px 16px;border-bottom:1px solid var(--pf-border);display:flex;align-items:center;gap:10px">
        <div style="width:3px;border-radius:2px;align-self:stretch;background:<?= e($m['cover_color']) ?>"></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;truncate"><?= e(truncate($m['title'],40)) ?></div>
          <div style="font-size:11.5px;color:var(--pf-text-3)"><?= e($m['program_name']) ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <?= daysLeft($m['due_date']) ?>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Activity feed -->
    <div class="pf-card">
      <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-activity me-2" style="color:var(--pf-green)"></i>Recent Activity</div></div>
      <div style="padding:16px">
        <?php if (empty($activity)): ?>
        <div style="text-align:center;color:var(--pf-text-3);font-size:13px;padding:20px 0">No activity yet</div>
        <?php else: ?>
        <div class="pf-feed">
          <?php foreach ($activity as $a): ?>
          <div class="pf-feed-item">
            <div class="pf-feed-dot" style="border-color:<?= e($a['cover_color']) ?>">
              <?= avatar($a['user_name'], $a['cover_color'], '36px') ?>
            </div>
            <div style="flex:1;min-width:0;padding-top:8px">
              <div style="font-size:12.5px;line-height:1.5">
                <strong><?= e(explode(' ',$a['user_name'])[0]) ?></strong>
                <?php if($a['task_title']): ?>updated <em><?= e(truncate($a['task_title'],25)) ?></em><?php endif; ?>
              </div>
              <div style="font-size:11.5px;color:var(--pf-text-3);margin-top:2px"><?= timeAgo($a['created_at']) ?> · <?= e($a['program_name']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php
$content = ob_get_clean();

ob_start();
?>
<script>
(function() {
  const labels = ['Not Started','In Progress','Review','Completed','Blocked','Cancelled'];
  const data   = [
    <?= (int)($statusMap['not_started'] ?? 0) ?>,
    <?= (int)($statusMap['in_progress'] ?? 0) ?>,
    <?= (int)($statusMap['review']      ?? 0) ?>,
    <?= (int)($statusMap['completed']   ?? 0) ?>,
    <?= (int)($statusMap['blocked']     ?? 0) ?>,
    <?= (int)($statusMap['cancelled']   ?? 0) ?>
  ];
  const colors = ['#94a3b8','#6366f1','#06b6d4','#22c55e','#ef4444','#475569'];
  Charts.donut('taskDonut', labels, data, colors);
})();
</script>
<?php
$extraJs = ob_get_clean();

require VIEW_PATH . 'layouts/app.php';

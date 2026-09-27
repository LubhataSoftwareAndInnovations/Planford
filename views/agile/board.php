<?php
$pageTitle    = 'Agile & Scrum Sprint Board';
$pageSubtitle = 'Interactive Drag-and-Drop Task & Story Execution';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$sprints = Sprint::getAll($programId);
$activeSprint = Sprint::getActive($programId) ?? ($sprints[0] ?? null);
$sprintId = (int)($_GET['sprint_id'] ?? ($activeSprint['id'] ?? 0));

$tasks = db()->fetchAll(
    "SELECT t.*, u.name as assignee_name, e.title as epic_title, e.color as epic_color, tr.name as track_name
     FROM tasks t
     LEFT JOIN users u ON t.assigned_to = u.id
     LEFT JOIN epics e ON t.epic_id = e.id
     LEFT JOIN tracks tr ON t.track_id = tr.id
     WHERE t.program_id = ? " . ($sprintId ? "AND t.sprint_id = $sprintId " : "") . "
     ORDER BY t.order_index, t.id DESC",
    [$programId]
);

$columns = [
    'not_started' => ['title' => 'To Do / Backlog', 'cls' => 'secondary', 'icon' => 'bi-circle'],
    'in_progress' => ['title' => 'In Progress',    'cls' => 'primary',   'icon' => 'bi-arrow-right-circle'],
    'review'      => ['title' => 'Code / QA Review','cls' => 'info',      'icon' => 'bi-eye'],
    'completed'   => ['title' => 'Done / Completed','cls' => 'success',   'icon' => 'bi-check-circle-fill'],
    'blocked'     => ['title' => 'Blocked / Risk',  'cls' => 'danger',    'icon' => 'bi-exclamation-octagon'],
];

$tasksByCol = [];
foreach ($columns as $k => $c) $tasksByCol[$k] = [];
foreach ($tasks as $t) {
    $st = $t['status'];
    if (isset($tasksByCol[$st])) {
        $tasksByCol[$st][] = $t;
    } else {
        $tasksByCol['not_started'][] = $t;
    }
}

$sprintMetrics = $sprintId ? Sprint::getMetrics($sprintId) : null;

ob_start();
?>

<!-- Header controls -->
<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div style="display:flex;align-items:center;gap:12px">
      <div>
        <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
        <select class="pf-input" style="width:240px;padding:6px 12px" onchange="location.href='<?= url('agile/board') ?>?program_id='+this.value">
          <?php foreach ($programs as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
            <?= e($p['code']) ?> — <?= e($p['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">ACTIVE SPRINT</label>
        <select class="pf-input" style="width:260px;padding:6px 12px" onchange="location.href='<?= url('agile/board') ?>?program_id=<?= $programId ?>&sprint_id='+this.value">
          <?php foreach ($sprints as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $sprintId ? 'selected' : '' ?>>
            <?= e($s['name']) ?> (<?= $s['status'] ?>)
          </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <?php if ($sprintMetrics): ?>
    <div style="display:flex;align-items:center;gap:20px;font-size:13px">
      <div>
        <span class="text-muted">Total Stories:</span>
        <strong style="color:var(--pf-text)"><?= $sprintMetrics['tasks_count'] ?></strong>
      </div>
      <div>
        <span class="text-muted">Story Points:</span>
        <strong style="color:var(--pf-indigo)"><?= $sprintMetrics['completed_points'] ?> / <?= $sprintMetrics['total_points'] ?> SP</strong>
      </div>
      <div style="width:140px">
        <?= progressBar($sprintMetrics['completion_pct'], '8px') ?>
      </div>
    </div>
    <?php endif; ?>

    <div>
      <a href="<?= url('tasks/new?program_id=' . $programId . '&sprint_id=' . $sprintId) ?>" class="pf-btn pf-btn-primary">
        <i class="bi bi-plus-lg"></i> Add Story / Task
      </a>
    </div>
  </div>
</div>

<!-- Agile Board Columns Grid -->
<div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:16px;align-items:start;overflow-x:auto;padding-bottom:16px">
  <?php foreach ($columns as $colKey => $col): 
    $colTasks = $tasksByCol[$colKey];
    $colPoints = array_sum(array_column($colTasks, 'story_points'));
  ?>
  <div style="background:var(--pf-surface2);border:1px solid var(--pf-border);border-radius:var(--pf-radius);padding:14px;min-height:500px">
    
    <!-- Column Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--pf-border)">
      <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;color:var(--pf-text)">
        <i class="bi <?= $col['icon'] ?> text-<?= $col['cls'] ?>"></i>
        <?= $col['title'] ?>
      </div>
      <div style="display:flex;align-items:center;gap:6px">
        <span class="badge badge-soft-<?= $col['cls'] ?>"><?= count($colTasks) ?></span>
        <span class="badge bg-surface text-muted" style="font-size:10px"><?= $colPoints ?> SP</span>
      </div>
    </div>

    <!-- Task Cards -->
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php if (empty($colTasks)): ?>
      <div style="padding:24px 12px;text-align:center;color:var(--pf-text-3);font-size:12px;border:1px dashed var(--pf-border);border-radius:var(--pf-radius-sm)">
        No tasks in <?= strtolower($col['title']) ?>
      </div>
      <?php else: foreach ($colTasks as $t): ?>
      <div class="pf-card hover-lift" style="padding:14px;margin:0;cursor:pointer;border-left:4px solid var(--pf-<?= $col['cls'] ?>)" onclick="location.href='<?= url('tasks/view?id='.$t['id']) ?>'">
        
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <div style="display:flex;align-items:center;gap:6px">
            <?= taskTypeBadge($t['type'] ?? 'story') ?>
            <span style="font-family:monospace;font-size:11px;color:var(--pf-text-3)"><?= e($t['code'] ?? ('TSK-'.$t['id'])) ?></span>
          </div>
          <?= storyPointBadge((int)($t['story_points'] ?? 3)) ?>
        </div>

        <div style="font-weight:600;font-size:13.5px;color:var(--pf-text);line-height:1.4;margin-bottom:10px">
          <?= e($t['title']) ?>
        </div>

        <?php if (!empty($t['epic_title'])): ?>
        <div style="margin-bottom:10px">
          <span class="badge" style="background:<?= $t['epic_color'] ?>22;color:<?= $t['epic_color'] ?>;border:1px solid <?= $t['epic_color'] ?>44;font-size:10px">
            <i class="bi bi-lightning-charge-fill me-1"></i><?= e($t['epic_title']) ?>
          </span>
        </div>
        <?php endif; ?>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:8px;border-top:1px solid var(--pf-border);font-size:11px">
          <div style="display:flex;align-items:center;gap:6px">
            <?= avatar($t['assignee_name'] ?? 'Unassigned', '', '22px') ?>
            <span style="color:var(--pf-text-2)"><?= e(explode(' ', $t['assignee_name'] ?? 'Unassigned')[0]) ?></span>
          </div>
          <div>
            <?= priorityBadge($t['priority']) ?>
          </div>
        </div>

      </div>
      <?php endforeach; endif; ?>
    </div>

  </div>
  <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

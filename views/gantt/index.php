<?php
$pageTitle    = 'Gantt Chart & Task Dependencies';
$pageSubtitle = 'Visual Timeline, Critical Path, and Finish-to-Start Task Linkages';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$tasks = db()->fetchAll(
    "SELECT t.*, u.name as assignee_name, p.name as phase_name
     FROM tasks t
     LEFT JOIN users u ON t.assigned_to = u.id
     LEFT JOIN phases p ON t.phase_id = p.id
     WHERE t.program_id = ?
     ORDER BY t.start_date ASC, t.id ASC",
    [$programId]
);

$deps = db()->fetchAll(
    "SELECT d.*, t1.title as task_title, t2.title as depends_title
     FROM task_dependencies d
     JOIN tasks t1 ON d.task_id = t1.id
     JOIN tasks t2 ON d.depends_on_task_id = t2.id
     WHERE t1.program_id = ?",
    [$programId]
);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('gantt') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <span class="badge bg-indigo-soft text-indigo fw-bold p-2" style="font-size:12px">
        <i class="bi bi-diagram-2 me-1"></i> Critical Path Analysis Active
      </span>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Gantt Timeline Grid -->
  <div class="col-lg-8">
    <div class="pf-card">
      <div class="pf-card-header" style="display:flex;justify-content:space-between;align-items:center">
        <div class="pf-card-title"><i class="bi bi-bar-chart-steps me-2 text-primary"></i>Program Timeline Schedule</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px">
        <?php foreach ($tasks as $t): 
          $sDate = $t['start_date'] ? date('d M', strtotime($t['start_date'])) : 'TBD';
          $dDate = $t['due_date'] ? date('d M', strtotime($t['due_date'])) : 'TBD';
          $pct = (int)($t['progress'] ?? 0);
        ?>
        <div style="padding:14px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);border-left:4px solid var(--pf-<?= $t['status'] === 'completed' ? 'green' : ($t['status'] === 'blocked' ? 'red' : 'indigo') ?>)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="font-weight:700;font-size:13.5px;color:var(--pf-text)">
              <span class="text-muted font-monospace me-2"><?= e($t['code'] ?: 'TSK-'.$t['id']) ?></span>
              <?= e($t['title']) ?>
            </div>
            <div style="display:flex;align-items:center;gap:8px;font-size:12px">
              <span class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= $sDate ?> — <?= $dDate ?></span>
              <?= statusBadge($t['status']) ?>
            </div>
          </div>
          <?= progressBar($pct, '8px') ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Dependency Links -->
  <div class="col-lg-4">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-link-45deg me-2 text-indigo"></i>Task Dependencies (FS / SS)</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php if (empty($deps)): ?>
        <div class="text-muted text-center py-3" style="font-size:12px">No dependency links configured.</div>
        <?php else: foreach ($deps as $d): ?>
        <div style="padding:10px 12px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);border:1px solid var(--pf-border);font-size:12.5px">
          <div style="font-weight:600;color:var(--pf-text)"><?= e(truncate($d['task_title'], 28)) ?></div>
          <div style="font-size:11.5px;color:var(--pf-text-3);margin-top:4px">
            <i class="bi bi-arrow-return-right me-1"></i>Depends on: <strong><?= e(truncate($d['depends_title'], 28)) ?></strong>
            <span class="badge bg-indigo-soft text-indigo ms-1"><?= $d['type'] ?></span>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

<?php
$pageTitle    = 'Sprints & Backlog Management';
$pageSubtitle = 'Time-boxed Iterations, Capacity Planning, and Velocity Tracking';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$sprints = Sprint::getAll($programId);
$epics   = db()->fetchAll("SELECT * FROM epics WHERE program_id=? ORDER BY id DESC", [$programId]);

$unassignedTasks = db()->fetchAll(
    "SELECT t.*, u.name as assignee_name 
     FROM tasks t 
     LEFT JOIN users u ON t.assigned_to = u.id 
     WHERE t.program_id=? AND (t.sprint_id IS NULL OR t.sprint_id=0)",
    [$programId]
);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:280px" onchange="location.href='<?= url('sprints') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display:flex;gap:10px">
      <a href="<?= url('agile/board?program_id=' . $programId) ?>" class="pf-btn pf-btn-secondary">
        <i class="bi bi-kanban"></i> Open Sprint Board
      </a>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left: Sprints List -->
  <div class="col-lg-8">
    <div style="display:flex;flex-direction:column;gap:20px">
      <?php if (empty($sprints)): ?>
      <div class="pf-card" style="padding:40px;text-align:center;color:var(--pf-text-3)">
        <i class="bi bi-arrow-repeat" style="font-size:48px;color:var(--pf-indigo)"></i>
        <h5 class="mt-3 mb-1" style="color:var(--pf-text)">No Sprints Configured</h5>
        <p class="text-muted" style="font-size:13px">Create your first Sprint iteration to start Agile planning.</p>
      </div>
      <?php else: foreach ($sprints as $s): 
        $metrics = Sprint::getMetrics($s['id']);
      ?>
      <div class="pf-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div>
            <div style="display:flex;align-items:center;gap:10px">
              <h5 style="margin:0;font-weight:700;color:var(--pf-text)"><?= e($s['name']) ?></h5>
              <?php
              $stMap = [
                'active' => 'success',
                'planning' => 'info',
                'completed' => 'primary',
                'closed' => 'secondary'
              ];
              $cls = $stMap[$s['status']] ?? 'secondary';
              ?>
              <span class="badge badge-soft-<?= $cls ?> text-uppercase"><?= $s['status'] ?></span>
            </div>
            <div class="text-muted style-sub" style="font-size:12px;margin-top:4px">
              <i class="bi bi-calendar3 me-1"></i><?= fDate($s['start_date']) ?> — <?= fDate($s['end_date']) ?>
            </div>
          </div>

          <div style="text-align:right">
            <div style="font-size:18px;font-weight:800;color:var(--pf-indigo)">
              <?= $metrics['completed_points'] ?> / <?= $metrics['total_points'] ?> SP
            </div>
            <div style="font-size:11px;color:var(--pf-text-3)">Velocity Commitment</div>
          </div>
        </div>

        <?php if (!empty($s['goal'])): ?>
        <div style="background:var(--pf-surface2);padding:10px 14px;border-radius:var(--pf-radius-sm);font-size:12.5px;color:var(--pf-text-2);margin-bottom:16px">
          <strong>Sprint Goal:</strong> <?= e($s['goal']) ?>
        </div>
        <?php endif; ?>

        <div style="margin-bottom:16px">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
            <span class="text-muted">Completion Progress</span>
            <strong style="color:var(--pf-text)"><?= $metrics['completion_pct'] ?>%</strong>
          </div>
          <?= progressBar($metrics['completion_pct'], '8px') ?>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12.5px">
          <div style="display:flex;gap:16px;color:var(--pf-text-3)">
            <span><i class="bi bi-check2-square text-primary me-1"></i><?= $metrics['tasks_count'] ?> Tasks</span>
            <span><i class="bi bi-clock-history text-warning me-1"></i><?= $s['capacity_hours'] ?> Hours Capacity</span>
          </div>
          <a href="<?= url('agile/board?program_id='.$programId.'&sprint_id='.$s['id']) ?>" class="pf-btn pf-btn-sm pf-btn-secondary">
            View Board <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Right: Backlog & Epics Overview -->
  <div class="col-lg-4">
    <div class="pf-card mb-4">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-layers me-2 text-indigo"></i>Program Epics</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach ($epics as $ep): ?>
        <div style="padding:10px 12px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);border-left:4px solid <?= $ep['color'] ?>">
          <div style="font-weight:600;font-size:13px;color:var(--pf-text)"><?= e($ep['title']) ?></div>
          <div style="font-size:11.5px;color:var(--pf-text-3);margin-top:2px"><?= e($ep['summary'] ?: 'No summary') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-inbox me-2 text-warning"></i>Unassigned Backlog (<?= count($unassignedTasks) ?>)</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;max-height:360px;overflow-y:auto">
        <?php if(empty($unassignedTasks)): ?>
        <div style="padding:20px;text-align:center;color:var(--pf-text-3);font-size:12px">All items assigned to Sprints ✓</div>
        <?php else: foreach($unassignedTasks as $ut): ?>
        <div style="padding:10px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);display:flex;align-items:center;justify-content:space-between;font-size:12.5px">
          <div>
            <div style="font-weight:600;color:var(--pf-text)"><?= e(truncate($ut['title'], 30)) ?></div>
            <div style="font-size:11px;color:var(--pf-text-3)"><?= e($ut['assignee_name'] ?? 'Unassigned') ?></div>
          </div>
          <?= storyPointBadge((int)($ut['story_points'] ?? 3)) ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

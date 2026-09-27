<?php
$pageTitle    = 'RACI Matrix & Resource Capacity';
$pageSubtitle = 'Governance Responsibilities and Developer Workload Allocations';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$raci = db()->fetchAll(
    "SELECT r.*, u.name as user_name, u.role as user_app_role, u.department
     FROM program_raci r
     JOIN users u ON r.user_id = u.id
     WHERE r.program_id=?",
    [$programId]
);

$allocations = db()->fetchAll(
    "SELECT a.*, u.name as user_name, u.email as user_email
     FROM resource_allocations a
     JOIN users u ON a.user_id = u.id
     WHERE a.program_id=?",
    [$programId]
);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('capacity') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- RACI Matrix -->
  <div class="col-lg-6">
    <div class="pf-card h-100">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-diagram-3 me-2 text-indigo"></i>RACI Governance Matrix</div>
      </div>
      <div class="table-responsive">
        <table class="pf-table">
          <thead>
            <tr>
              <th>Team Member</th>
              <th>Department</th>
              <th>RACI Assignment</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($raci as $r): ?>
            <tr>
              <td class="fw-semibold">
                <?= avatar($r['user_name'], '', '24px') ?>
                <span class="ms-2" style="color:var(--pf-text)"><?= e($r['user_name']) ?></span>
              </td>
              <td class="text-muted" style="font-size:12px"><?= e($r['department'] ?: 'Engineering') ?></td>
              <td><?= raciBadge($r['raci_role']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Resource Capacity Allocations -->
  <div class="col-lg-6">
    <div class="pf-card h-100">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-cpu me-2 text-primary"></i>Resource Allocation & Capacity</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px">
        <?php foreach ($allocations as $al): ?>
        <div style="padding:12px 14px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);border:1px solid var(--pf-border)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:8px">
              <?= avatar($al['user_name'], '', '28px') ?>
              <div>
                <div style="font-weight:700;font-size:13px;color:var(--pf-text)"><?= e($al['user_name']) ?></div>
                <div style="font-size:11px;color:var(--pf-text-3)"><?= e($al['role_name']) ?></div>
              </div>
            </div>
            <div style="text-align:right">
              <span class="badge <?= $al['allocation_pct'] > 100 ? 'badge-soft-danger' : 'badge-soft-success' ?> fw-bold" style="font-size:12px">
                <?= $al['allocation_pct'] ?>% Allocated
              </span>
            </div>
          </div>
          <?= progressBar($al['allocation_pct'], '6px') ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

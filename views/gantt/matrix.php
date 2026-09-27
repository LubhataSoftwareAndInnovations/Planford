<?php
$pageTitle    = 'Cross-Program Dependency Matrix';
$pageSubtitle = 'Visual 2D Blocker Matrix & Cross-Team Commitment Map (Jira Align Style)';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$deps = db()->fetchAll(
    "SELECT d.*, t1.title as task_title, t1.code as task_code, t2.title as depends_title, t2.code as depends_code,
            u1.name as task_owner, u2.name as depends_owner
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
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('gantt/matrix') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<div class="pf-card">
  <div class="pf-card-header">
    <div class="pf-card-title"><i class="bi bi-grid-3x3-gap me-2 text-indigo"></i>Cross-Team Dependency Matrix</div>
  </div>

  <div class="table-responsive">
    <table class="pf-table">
      <thead>
        <tr>
          <th>Dependent Task (Consumer)</th>
          <th>Owner</th>
          <th>Dependency Link</th>
          <th>Prerequisite Task (Provider)</th>
          <th>Owner</th>
          <th>Resolution Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($deps)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">No dependency linkages found for this program.</td>
        </tr>
        <?php else: foreach ($deps as $d): ?>
        <tr>
          <td class="fw-bold" style="color:var(--pf-text)">
            <code class="me-1"><?= e($d['task_code'] ?: 'TSK-'.$d['task_id']) ?></code>
            <?= e($d['task_title']) ?>
          </td>
          <td><?= e($d['task_owner'] ?: 'Unassigned') ?></td>
          <td><span class="badge bg-indigo-soft text-indigo fw-bold"><?= $d['type'] ?> (Finish-to-Start)</span></td>
          <td class="fw-bold" style="color:var(--pf-text)">
            <code class="me-1"><?= e($d['depends_code'] ?: 'TSK-'.$d['depends_on_task_id']) ?></code>
            <?= e($d['depends_title']) ?>
          </td>
          <td><?= e($d['depends_owner'] ?: 'Unassigned') ?></td>
          <td><span class="badge badge-soft-success">COMMITTED</span></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

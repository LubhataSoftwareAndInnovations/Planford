<?php
$pageTitle    = 'Strategic Portfolio OKRs';
$pageSubtitle = 'Alignment of Program Deliverables to Enterprise Objectives & Key Results';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$okrs = db()->fetchAll(
    "SELECT o.*, u.name as owner_name 
     FROM portfolio_okrs o 
     LEFT JOIN users u ON o.owner_id = u.id 
     WHERE o.program_id = ?",
    [$programId]
);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('portfolio/okrs') ?>?program_id='+this.value">
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
  <?php foreach ($okrs as $o): 
    $pct = $o['target_value'] > 0 ? round(($o['current_value'] / $o['target_value']) * 100) : 0;
  ?>
  <div class="col-lg-6">
    <div class="pf-card h-100">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <span class="badge bg-indigo-soft text-indigo fw-bold text-uppercase" style="font-size:11px">
          <?= e($o['category']) ?> Objective
        </span>
        <span class="badge badge-soft-<?= $o['status'] === 'on_track' ? 'success' : 'warning' ?> text-uppercase fw-bold">
          <?= str_replace('_', ' ', $o['status']) ?>
        </span>
      </div>

      <h5 style="margin:0 0 10px 0;font-weight:700;color:var(--pf-text);line-height:1.4">
        <?= e($o['title']) ?>
      </h5>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px">
        <span class="text-muted">Target Progress:</span>
        <strong style="color:var(--pf-indigo)"><?= $o['current_value'] ?> / <?= $o['target_value'] ?> <?= e($o['unit']) ?> (<?= $pct ?>%)</strong>
      </div>
      <?= progressBar($pct, '8px') ?>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid var(--pf-border);font-size:12px">
        <div>
          <span class="text-muted me-1">Owner:</span>
          <strong><?= e($o['owner_name'] ?: 'Executive Sponsor') ?></strong>
        </div>
        <div class="text-muted">
          <i class="bi bi-calendar3 me-1"></i>Target: <?= fDate($o['due_date']) ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

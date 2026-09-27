<?php
$pageTitle    = '5x5 Enterprise Risk Matrix & Heatmap';
$pageSubtitle = 'Visual 5x5 Risk Exposure Assessment & Mitigation Control (ISO 31000 Standard)';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$risks = db()->fetchAll("SELECT * FROM risks WHERE program_id = ?", [$programId]);

$matrix = [];
for ($l = 5; $l >= 1; $l--) {
    for ($i = 1; $i <= 5; $i++) {
        $matrix[$l][$i] = [];
    }
}

foreach ($risks as $r) {
    $lMap = ['low' => 2, 'medium' => 3, 'high' => 4];
    $iMap = ['low' => 2, 'medium' => 3, 'high' => 4, 'critical' => 5];
    $lVal = $lMap[$r['likelihood']] ?? 3;
    $iVal = $iMap[$r['impact']] ?? 3;
    $matrix[$lVal][$iVal][] = $r;
}

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('risks/heatmap') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- 5x5 Heatmap Matrix -->
  <div class="col-lg-8">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-grid-fill me-2 text-danger"></i>5x5 Likelihood vs Impact Heatmap</div>
      </div>
      <div style="overflow-x:auto">
        <table class="table table-bordered text-center align-middle" style="min-width:550px">
          <thead>
            <tr>
              <th style="width:120px">Likelihood \ Impact</th>
              <th style="width:20%">1 - Low</th>
              <th style="width:20%">2 - Moderate</th>
              <th style="width:20%">3 - High</th>
              <th style="width:20%">4 - Critical</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($l = 5; $l >= 1; $l--): 
              $lName = ['1'=>'1-Rare','2'=>'2-Unlikely','3'=>'3-Possible','4'=>'4-Likely','5'=>'5-Almost Certain'][$l];
            ?>
            <tr>
              <td class="fw-bold bg-surface" style="font-size:11px"><?= $lName ?></td>
              <?php for ($i = 1; $i <= 4; $i++): 
                $cellRisks = $matrix[$l][$i] ?? [];
                $score = $l * $i;
                $bgColor = $score >= 12 ? 'rgba(239,68,68,0.2)' : ($score >= 6 ? 'rgba(245,158,11,0.2)' : 'rgba(34,197,94,0.15)');
                $borderColor = $score >= 12 ? '#ef4444' : ($score >= 6 ? '#f59e0b' : '#22c55e');
              ?>
              <td style="background:<?= $bgColor ?>;border:1px solid <?= $borderColor ?>;height:80px;vertical-align:middle">
                <?php if (empty($cellRisks)): ?>
                <span class="text-muted" style="font-size:11px"><?= $score ?></span>
                <?php else: foreach ($cellRisks as $rk): ?>
                <span class="badge bg-danger text-white m-1" style="font-size:11px;cursor:pointer" title="<?= e($rk['title']) ?>">
                  <?= e(truncate($rk['title'], 16)) ?>
                </span>
                <?php endforeach; endif; ?>
              </td>
              <?php endfor; ?>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Risk Log Details -->
  <div class="col-lg-4">
    <div class="pf-card h-100">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-shield-exclamation me-2 text-warning"></i>Active RIDA Incidents</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($risks as $rk): ?>
        <div style="padding:12px;background:var(--pf-surface2);border-radius:var(--pf-radius-sm);border-left:4px solid var(--pf-<?= $rk['type'] === 'issue' ? 'red' : 'amber' ?>)">
          <div style="font-weight:700;font-size:13px;color:var(--pf-text)"><?= e($rk['title']) ?></div>
          <div style="font-size:11.5px;color:var(--pf-text-2);margin-top:4px"><?= e($rk['description']) ?></div>
          <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:11px" class="text-muted">
            <span>Type: <strong><?= strtoupper($rk['type']) ?></strong></span>
            <span>Impact: <strong><?= strtoupper($rk['impact']) ?></strong></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

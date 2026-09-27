<?php
$pageTitle    = 'Earned Value Management (EVM) & Financial Control';
$pageSubtitle = 'Standard Financial Performance Index (CPI / SPI / EVM Metrics)';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$evm = EVMEngine::calculateForProgram($programId);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('evm') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <?= ragBadge($evm['rag'] ?? 'green') ?>
    </div>
  </div>
</div>

<!-- Key Performance Gauges (CPI & SPI) -->
<div class="row g-4 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px;letter-spacing:.5px">Cost Performance Index (CPI)</div>
      <div style="font-size:36px;font-weight:800;color:<?= ($evm['cpi'] ?? 1) >= 1 ? 'var(--pf-green)' : 'var(--pf-red)' ?>;margin:8px 0">
        <?= number_format($evm['cpi'] ?? 1, 2) ?>
      </div>
      <div style="font-size:12px;color:var(--pf-text-3)">
        <?= ($evm['cpi'] ?? 1) >= 1 ? 'Under Budget (Efficient)' : 'Over Budget (Cost Variance)' ?>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px;letter-spacing:.5px">Schedule Performance Index (SPI)</div>
      <div style="font-size:36px;font-weight:800;color:<?= ($evm['spi'] ?? 1) >= 1 ? 'var(--pf-green)' : 'var(--pf-amber)' ?>;margin:8px 0">
        <?= number_format($evm['spi'] ?? 1, 2) ?>
      </div>
      <div style="font-size:12px;color:var(--pf-text-3)">
        <?= ($evm['spi'] ?? 1) >= 1 ? 'Ahead of Schedule' : 'Behind Baseline Schedule' ?>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px;letter-spacing:.5px">Cost Variance (CV)</div>
      <div style="font-size:30px;font-weight:800;color:<?= ($evm['cv'] ?? 0) >= 0 ? 'var(--pf-green)' : 'var(--pf-red)' ?>;margin:8px 0">
        <?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['cv'] ?? 0, 2) ?>
      </div>
      <div style="font-size:12px;color:var(--pf-text-3)">EV - AC</div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px;letter-spacing:.5px">Schedule Variance (SV)</div>
      <div style="font-size:30px;font-weight:800;color:<?= ($evm['sv'] ?? 0) >= 0 ? 'var(--pf-green)' : 'var(--pf-amber)' ?>;margin:8px 0">
        <?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['sv'] ?? 0, 2) ?>
      </div>
      <div style="font-size:12px;color:var(--pf-text-3)">EV - PV</div>
    </div>
  </div>
</div>

<!-- Breakdown Table -->
<div class="pf-card">
  <div class="pf-card-header">
    <div class="pf-card-title"><i class="bi bi-table me-2 text-primary"></i>Financial Baseline Breakdown</div>
  </div>
  <div class="table-responsive">
    <table class="pf-table">
      <thead>
        <tr>
          <th>Metric Name</th>
          <th>Abbreviation</th>
          <th>Calculated Value</th>
          <th>Benchmark Standard</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="fw-bold">Total Planned Budget (BAC)</td>
          <td><code>BAC</code></td>
          <td class="fw-bold"><?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['budget'] ?? 0, 2) ?></td>
          <td>Approved Financial Baseline</td>
        </tr>
        <tr>
          <td class="fw-bold">Planned Value</td>
          <td><code>PV</code></td>
          <td><?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['pv'] ?? 0, 2) ?></td>
          <td>Scheduled Progress Target</td>
        </tr>
        <tr>
          <td class="fw-bold">Earned Value</td>
          <td><code>EV</code></td>
          <td class="text-primary fw-bold"><?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['ev'] ?? 0, 2) ?></td>
          <td>Completed Work Value</td>
        </tr>
        <tr>
          <td class="fw-bold">Actual Cost</td>
          <td><code>AC</code></td>
          <td class="text-danger fw-bold"><?= ($evm['currency'] ?? 'INR') === 'INR' ? '₹' : '$' ?><?= number_format($evm['ac'] ?? 0, 2) ?></td>
          <td>Actual Financial Expenditure</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

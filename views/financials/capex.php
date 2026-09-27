<?php
$pageTitle    = 'CAPEX vs OPEX Financial Accounting';
$pageSubtitle = 'Capital Expenditure Accounting, Operational Expenses, & Capitalization Audits';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

$logs = db()->fetchAll(
    "SELECT c.*, u.name as logger_name 
     FROM capex_opex_logs c 
     LEFT JOIN users u ON c.logged_by = u.id 
     WHERE c.program_id = ? 
     ORDER BY c.id DESC",
    [$programId]
);

$totalCapex = array_sum(array_map(fn($l) => $l['category'] === 'capex' ? $l['amount'] : 0, $logs));
$totalOpex  = array_sum(array_map(fn($l) => $l['category'] === 'opex' ? $l['amount'] : 0, $logs));
$totalSpent = $totalCapex + $totalOpex;

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('financials/capex') ?>?program_id='+this.value">
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
  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Capital Expenditure (CAPEX)</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-indigo);margin:6px 0">₹<?= number_format($totalCapex, 2) ?></div>
      <div style="font-size:12px;color:var(--pf-text-3)">IP & Asset Development</div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Operational Expense (OPEX)</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-amber);margin:6px 0">₹<?= number_format($totalOpex, 2) ?></div>
      <div style="font-size:12px;color:var(--pf-text-3)">Maintenance & Subscriptions</div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Capitalization Ratio</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-green);margin:6px 0">
        <?= $totalSpent > 0 ? round(($totalCapex / $totalSpent) * 100) : 0 ?>%
      </div>
      <div style="font-size:12px;color:var(--pf-text-3)">Tax Capitalization Percentage</div>
    </div>
  </div>
</div>

<div class="pf-card">
  <div class="pf-card-header">
    <div class="pf-card-title"><i class="bi bi-cash-stack me-2 text-primary"></i>CAPEX / OPEX Expenditure Audit Log</div>
  </div>
  <div class="table-responsive">
    <table class="pf-table">
      <thead>
        <tr>
          <th>Category</th>
          <th>Item / Asset Name</th>
          <th>Fiscal Quarter</th>
          <th>Amount</th>
          <th>Capitalization Status</th>
          <th>Logged By</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
        <tr>
          <td>
            <span class="badge badge-soft-<?= $l['category'] === 'capex' ? 'primary' : 'warning' ?> text-uppercase fw-bold">
              <?= $l['category'] ?>
            </span>
          </td>
          <td class="fw-bold" style="color:var(--pf-text)"><?= e($l['item_name']) ?></td>
          <td><code><?= e($l['fiscal_quarter']) ?></code></td>
          <td class="fw-bold">₹<?= number_format($l['amount'], 2) ?></td>
          <td>
            <span class="badge badge-soft-<?= $l['capitalization_status'] === 'eligible' ? 'success' : 'secondary' ?> text-uppercase">
              <?= $l['capitalization_status'] ?>
            </span>
          </td>
          <td><?= e($l['logger_name'] ?: 'Finance Lead') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

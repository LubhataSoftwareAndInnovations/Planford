<?php
$pageTitle    = 'Weekly Timesheets & Worklog Matrix';
$pageSubtitle = 'Daily Worklog Submissions, Manager Approvals, and Billable Utilization';

$uid = authId();
$timesheets = db()->fetchAll(
    "SELECT ts.*, p.name as program_name, t.title as task_title, u.name as user_name
     FROM timesheets ts
     JOIN programs p ON ts.program_id = p.id
     LEFT JOIN tasks t ON ts.task_id = t.id
     JOIN users u ON ts.user_id = u.id
     ORDER BY ts.work_date DESC, ts.id DESC LIMIT 30"
);

$totalHours = array_sum(array_column($timesheets, 'hours'));
$billableHours = array_sum(array_map(fn($t) => $t['billable'] ? $t['hours'] : 0, $timesheets));
$utilizationPct = $totalHours > 0 ? round(($billableHours / $totalHours) * 100) : 0;

ob_start();
?>

<div class="row g-4 mb-4">
  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Total Logged Hours</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-indigo);margin:6px 0"><?= number_format($totalHours, 1) ?> hrs</div>
      <div style="font-size:12px;color:var(--pf-text-3)">Current Period Worklogs</div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Billable Hours</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-green);margin:6px 0"><?= number_format($billableHours, 1) ?> hrs</div>
      <div style="font-size:12px;color:var(--pf-text-3)">Client Chargeable Effort</div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6">
    <div class="pf-card text-center" style="padding:20px">
      <div class="text-muted text-uppercase fw-bold" style="font-size:11px">Resource Utilization</div>
      <div style="font-size:32px;font-weight:800;color:var(--pf-blue);margin:6px 0"><?= $utilizationPct ?>%</div>
      <div style="font-size:12px;color:var(--pf-text-3)">Billable vs Total Ratio</div>
    </div>
  </div>
</div>

<div class="pf-card">
  <div class="pf-card-header" style="display:flex;justify-content:space-between;align-items:center">
    <div class="pf-card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Weekly Timesheet Submissions</div>
  </div>
  <div class="table-responsive">
    <table class="pf-table">
      <thead>
        <tr>
          <th>Team Member</th>
          <th>Date</th>
          <th>Program</th>
          <th>Task / Activity</th>
          <th>Hours</th>
          <th>Type</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($timesheets as $ts): ?>
        <tr>
          <td class="fw-bold">
            <?= avatar($ts['user_name'], '', '22px') ?>
            <span class="ms-2" style="color:var(--pf-text)"><?= e($ts['user_name']) ?></span>
          </td>
          <td><?= fDate($ts['work_date'], 'd M Y') ?></td>
          <td class="fw-semibold" style="color:var(--pf-indigo)"><?= e($ts['program_name']) ?></td>
          <td style="font-size:12.5px"><?= e($ts['task_title'] ?: $ts['description']) ?></td>
          <td class="fw-bold"><?= number_format($ts['hours'], 1) ?> hrs</td>
          <td>
            <span class="badge <?= $ts['billable'] ? 'badge-soft-success' : 'badge-soft-secondary' ?>">
              <?= $ts['billable'] ? 'Billable' : 'Non-Billable' ?>
            </span>
          </td>
          <td>
            <span class="badge badge-soft-<?= $ts['status'] === 'approved' ? 'success' : 'warning' ?> text-uppercase">
              <?= $ts['status'] ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

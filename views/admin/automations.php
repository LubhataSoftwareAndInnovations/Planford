<?php
$pageTitle    = 'Automated Escalation & Rule Engine';
$pageSubtitle = 'Event-Driven Workflow Automation Rules (Jira Automation Style)';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['trigger_event'])) {
    $name    = trim($_POST['name']);
    $trigger = $_POST['trigger_event'];
    $action  = $_POST['action_type'];

    try {
        db()->insert('automation_rules', [
            'program_id'    => $programId,
            'name'          => $name,
            'trigger_event' => $trigger,
            'action_type'   => $action,
            'is_active'     => 1,
        ]);
        setFlash("Automation Rule '$name' created.");
    } catch (Throwable $e) {
        setFlash("Error creating rule: " . $e->getMessage(), 'danger');
    }
    redirect('/admin/automations?program_id=' . $programId);
}

$rules = db()->fetchAll("SELECT * FROM automation_rules WHERE program_id=? ORDER BY id DESC", [$programId]);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('admin/automations') ?>?program_id='+this.value">
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
  <div class="col-lg-4">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-lightning-charge me-2 text-warning"></i>Create Automation Rule</div>
      </div>
      <form method="POST">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Rule Name</label>
          <input type="text" name="name" class="pf-input" placeholder="e.g. Escalate Blocked Task to PM" required>
        </div>

        <div class="mb-3">
          <label class="form-label">WHEN (Trigger Event)</label>
          <select name="trigger_event" class="pf-input">
            <option value="task_blocked">Task Status Changed to Blocked</option>
            <option value="task_overdue">Task Due Date Overdue</option>
            <option value="gate_failed">Phase Quality Gate Failed</option>
            <option value="budget_exceeded">Financial EVM CPI drops below 0.85</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">THEN (Action to Perform)</label>
          <select name="action_type" class="pf-input">
            <option value="escalate_priority">Escalate Priority to Critical</option>
            <option value="notify_pm">Send High-Priority PM Notification</option>
            <option value="change_status">Change Status to Blocked</option>
            <option value="audit_log">Record Audit Incident Entry</option>
          </select>
        </div>

        <button type="submit" class="pf-btn pf-btn-primary w-100">
          <i class="bi bi-check-lg me-1"></i> Activate Rule
        </button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-gear-wide-connected me-2 text-indigo"></i>Active Program Automation Rules</div>
      </div>
      <div class="table-responsive">
        <table class="pf-table">
          <thead>
            <tr>
              <th>Rule Name</th>
              <th>Trigger Event</th>
              <th>Action Executed</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rules as $r): ?>
            <tr>
              <td class="fw-bold" style="color:var(--pf-text)">
                <i class="bi bi-lightning-fill text-warning me-2"></i><?= e($r['name']) ?>
              </td>
              <td><code><?= e($r['trigger_event']) ?></code></td>
              <td><span class="badge bg-indigo-soft text-indigo fw-bold"><?= e($r['action_type']) ?></span></td>
              <td><span class="badge badge-soft-success">ACTIVE</span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

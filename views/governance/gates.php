<?php
$pageTitle    = 'Quality Gates & Governance (iQMS)';
$pageSubtitle = 'Phase Sign-Offs, Compliance Criteria, and Quality Audit Controls';

$programs = db()->fetchAll("SELECT * FROM programs ORDER BY name");
$programId = (int)($_GET['program_id'] ?? ($programs[0]['id'] ?? 1));

// Handle gate approval POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gate_id'], $_POST['action'])) {
    $gateId = (int)$_POST['gate_id'];
    $status = $_POST['action'] === 'approve' ? 'passed' : 'failed';
    $comments = trim($_POST['comments'] ?? '');

    db()->update('phase_gates', [
        'status'        => $status,
        'signed_off_by' => authId(),
        'signed_off_at' => date('Y-m-d H:i:s'),
        'comments'      => $comments,
    ], 'id=?', [$gateId]);

    audit('gate_signoff', 'phase_gates', $gateId, "Quality Gate status set to $status");
    setFlash("Quality Gate status updated to " . strtoupper($status) . ".");
    redirect('/governance/gates?program_id=' . $programId);
}

$gates = db()->fetchAll(
    "SELECT g.*, p.name as phase_name, u.name as signer_name 
     FROM phase_gates g
     LEFT JOIN phases p ON g.phase_id = p.id
     LEFT JOIN users u ON g.signed_off_by = u.id
     WHERE g.program_id=?
     ORDER BY g.id ASC",
    [$programId]
);

ob_start();
?>

<div class="pf-card mb-4" style="padding:16px 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
    <div>
      <label class="form-label text-muted mb-1" style="font-size:11px;font-weight:700">PROGRAM</label>
      <select class="pf-input" style="width:300px" onchange="location.href='<?= url('governance/gates') ?>?program_id='+this.value">
        <?php foreach ($programs as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $programId ? 'selected' : '' ?>>
          <?= e($p['code']) ?> — <?= e($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <span class="badge bg-indigo-soft text-indigo fw-bold p-2" style="font-size:12px">
        <i class="bi bi-shield-check me-1"></i> CMMI Level 5 & ISO 27001 Compliant
      </span>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-12">
    <div class="pf-card">
      <div class="pf-card-header" style="display:flex;justify-content:space-between;align-items:center">
        <div class="pf-card-title"><i class="bi bi-patch-check me-2 text-primary"></i>Phase Quality Sign-Off Gates</div>
      </div>

      <div class="table-responsive">
        <table class="pf-table">
          <thead>
            <tr>
              <th>Gate Name</th>
              <th>Phase</th>
              <th>Status</th>
              <th>Signed Off By</th>
              <th>Signed Off At</th>
              <th>Comments / Audit Note</th>
              <?php if (isPM()): ?>
              <th style="text-align:right">Action</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($gates)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No Quality Gates configured for this program.</td>
            </tr>
            <?php else: foreach ($gates as $g): ?>
            <tr>
              <td class="fw-bold" style="color:var(--pf-text)">
                <i class="bi bi-shield-lock text-indigo me-2"></i><?= e($g['gate_name']) ?>
              </td>
              <td><?= e($g['phase_name'] ?: 'All Phases') ?></td>
              <td>
                <?php
                $stMap = [
                  'passed' => ['success', 'PASSED / SIGNED-OFF'],
                  'pending'=> ['warning', 'PENDING REVIEW'],
                  'failed' => ['danger',  'FAILED / REJECTED'],
                  'waived' => ['info',    'WAIVED'],
                ];
                [$cls, $lbl] = $stMap[$g['status']] ?? ['secondary', strtoupper($g['status'])];
                ?>
                <span class="badge badge-soft-<?= $cls ?> fw-bold"><?= $lbl ?></span>
              </td>
              <td>
                <?= $g['signer_name'] ? (avatar($g['signer_name'],'','22px').' <span class="ms-1">'.e($g['signer_name']).'</span>') : '<span class="text-muted">—</span>' ?>
              </td>
              <td><?= fDate($g['signed_off_at'], 'd M Y H:i') ?></td>
              <td style="font-size:12.5px;color:var(--pf-text-2)"><?= e($g['comments'] ?: '—') ?></td>
              <?php if (isPM()): ?>
              <td style="text-align:right">
                <?php if ($g['status'] === 'pending'): ?>
                <form method="POST" style="display:inline-flex;gap:6px">
                  <?= csrfField() ?>
                  <input type="hidden" name="gate_id" value="<?= $g['id'] ?>">
                  <input type="hidden" name="comments" value="Approved via Quality Gate Control">
                  <button type="submit" name="action" value="approve" class="pf-btn pf-btn-sm pf-btn-primary">
                    <i class="bi bi-check-lg"></i> Sign-Off Gate
                  </button>
                </form>
                <?php else: ?>
                <span class="text-muted" style="font-size:11px">Complete</span>
                <?php endif; ?>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

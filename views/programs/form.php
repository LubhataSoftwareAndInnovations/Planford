<?php
requireAuth();
if (!isPM()) redirect('/programs', 'You do not have permission to create or edit programs.', 'danger');

$db    = db();
$orgId = authOrgId();
$id    = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$prog  = null;
$errors = [];

if ($isEdit) {
    $prog = $db->fetchOne("SELECT * FROM programs WHERE id=? AND org_id=?", [$id, $orgId]);
    if (!$prog) redirect('/programs', 'Program not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) redirect('/programs', 'Invalid request.', 'danger');

    $data = [
        'name'        => trim($_POST['name'] ?? ''),
        'code'        => strtoupper(trim($_POST['code'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'status'      => $_POST['status'] ?? 'planning',
        'priority'    => $_POST['priority'] ?? 'medium',
        'start_date'  => $_POST['start_date'] ?: null,
        'end_date'    => $_POST['end_date'] ?: null,
        'client_name' => trim($_POST['client_name'] ?? ''),
        'cover_color' => $_POST['cover_color'] ?? '#6366f1',
        'owner_id'    => (int)($_POST['owner_id'] ?? authId()) ?: null,
        'budget'      => $_POST['budget'] !== '' ? (float)$_POST['budget'] : null,
        'currency'    => $_POST['currency'] ?? 'INR',
    ];

    if (!$data['name'])  $errors[] = 'Program name is required.';
    if (!$data['code'])  $errors[] = 'Program code is required.';

    if (empty($errors)) {
        if ($isEdit) {
            $db->update('programs', $data, 'id=? AND org_id=?', [$id, $orgId]);
            audit('update', 'programs', $id, 'Program updated: ' . $data['name']);
            redirect('/programs/view?id=' . $id, 'Program updated successfully.', 'success');
        } else {
            $data['org_id']     = $orgId;
            $data['created_by'] = authId();
            $data['stakeholder_token'] = bin2hex(random_bytes(32));
            $newId = $db->insert('programs', $data);

            // Auto-create default tracks
            $defaultTracks = [
                ['Engineering',  '#3b82f6', 'bi-tools'],
                ['IT / Software','#6366f1', 'bi-code-slash'],
                ['Business',     '#10b981', 'bi-briefcase'],
                ['Compliance',   '#f59e0b', 'bi-shield-check'],
            ];
            foreach ($defaultTracks as [$tname, $tcolor, $ticon]) {
                $db->insert('tracks', ['program_id'=>$newId,'name'=>$tname,'color'=>$tcolor,'icon'=>$ticon]);
            }

            audit('create', 'programs', $newId, 'Program created: ' . $data['name']);
            redirect('/programs/view?id=' . $newId, 'Program created! Add your first tasks.', 'success');
        }
    }
}

// Members list for owner dropdown
$members = $db->fetchAll("SELECT id, name, role FROM users WHERE org_id=? AND is_active=1 ORDER BY name", [$orgId]);
$colors  = ['#6366f1','#8b5cf6','#ec4899','#3b82f6','#06b6d4','#10b981','#f59e0b','#ef4444','#f97316','#14b8a6'];

$pageTitle = $isEdit ? 'Edit Program' : 'New Program';
ob_start();
?>

<div style="max-width:680px">
  <!-- Back -->
  <a href="<?= $isEdit ? url('programs/view?id='.$id) : url('programs') ?>" class="btn btn-ghost btn-sm mb-5">
    <i class="bi bi-arrow-left"></i> Back
  </a>

  <div class="pf-card">
    <div class="pf-card-header">
      <div class="pf-card-title">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2" style="color:var(--pf-indigo)"></i>
        <?= $isEdit ? 'Edit Program' : 'Create New Program' ?>
      </div>
    </div>
    <div class="pf-card-body">

      <?php if ($errors): ?>
      <div class="pf-alert pf-alert-danger mb-5">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div><?php foreach ($errors as $e) echo e($e) . '<br>'; ?></div>
      </div>
      <?php endif; ?>

      <form method="POST">
        <?= csrfField() ?>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Program Name <span class="req">*</span></label>
            <input type="text" name="name" class="pf-input" placeholder="e.g. Digital Transformation 2026"
                   value="<?= e($prog['name'] ?? $_POST['name'] ?? '') ?>" required>
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Code <span class="req">*</span></label>
            <input type="text" name="code" class="pf-input" placeholder="e.g. DTP-2026" maxlength="30"
                   value="<?= e($prog['code'] ?? $_POST['code'] ?? '') ?>" required style="text-transform:uppercase">
            <div class="pf-form-hint">Short unique identifier</div>
          </div>
        </div>

        <div class="pf-form-group">
          <label class="pf-label">Description</label>
          <textarea name="description" class="pf-textarea" rows="3" placeholder="What is this program about?"><?= e($prog['description'] ?? $_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Status</label>
            <select name="status" class="pf-select">
              <?php foreach (['planning','active','on_hold','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($prog['status']??'planning')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Priority</label>
            <select name="priority" class="pf-select">
              <?php foreach (['critical','high','medium','low'] as $s): ?>
              <option value="<?= $s ?>" <?= ($prog['priority']??'medium')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Start Date</label>
            <input type="date" name="start_date" class="pf-input" value="<?= e($prog['start_date'] ?? '') ?>">
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">End Date</label>
            <input type="date" name="end_date" class="pf-input" value="<?= e($prog['end_date'] ?? '') ?>">
          </div>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Client / Customer</label>
            <input type="text" name="client_name" class="pf-input" placeholder="e.g. Acme Corp"
                   value="<?= e($prog['client_name'] ?? '') ?>">
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Program Manager</label>
            <select name="owner_id" class="pf-select">
              <option value="">— None —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>" <?= ($prog['owner_id']??authId())==$m['id']?'selected':'' ?>><?= e($m['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Budget</label>
            <input type="number" name="budget" class="pf-input" placeholder="0.00" step="0.01"
                   value="<?= e($prog['budget'] ?? '') ?>">
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Currency</label>
            <select name="currency" class="pf-select">
              <?php foreach (['INR','USD','EUR','GBP','AED','SGD'] as $c): ?>
              <option value="<?= $c ?>" <?= ($prog['currency']??'INR')===$c?'selected':'' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Cover color -->
        <div class="pf-form-group">
          <label class="pf-label">Cover Color</label>
          <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($colors as $c): ?>
            <label style="cursor:pointer">
              <input type="radio" name="cover_color" value="<?= $c ?>" style="display:none"
                     <?= ($prog['cover_color']??'#6366f1')===$c?'checked':'' ?>>
              <div class="color-swatch" style="width:30px;height:30px;border-radius:8px;background:<?= $c ?>;border:3px solid <?= ($prog['cover_color']??'#6366f1')===$c?'#fff':'transparent' ?>;box-shadow:<?= ($prog['cover_color']??'#6366f1')===$c?'0 0 0 2px '.$c:'none' ?>;transition:all .15s"></div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="d-flex gap-3 mt-6">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $isEdit ? 'save' : 'plus-circle' ?>"></i>
            <?= $isEdit ? 'Save Changes' : 'Create Program' ?>
          </button>
          <a href="<?= $isEdit ? url('programs/view?id='.$id) : url('programs') ?>" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Color swatch selection
document.querySelectorAll('input[name="cover_color"]').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.color-swatch').forEach(s => {
      s.style.border = '3px solid transparent';
      s.style.boxShadow = 'none';
    });
    const sw = r.nextElementSibling;
    sw.style.border = '3px solid #fff';
    sw.style.boxShadow = '0 0 0 2px ' + r.value;
  });
});
</script>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

<?php
requireAuth();
$db    = db();
$orgId = authOrgId();
$id    = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$task  = null;
$errors = [];

if ($isEdit) {
    $task = $db->fetchOne(
        "SELECT t.* FROM tasks t JOIN programs p ON t.program_id=p.id WHERE t.id=? AND p.org_id=?", [$id, $orgId]
    );
    if (!$task) redirect('/tasks', 'Task not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) redirect('/tasks', 'Invalid request.', 'danger');

    $data = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status'      => $_POST['status'] ?? 'not_started',
        'priority'    => $_POST['priority'] ?? 'medium',
        'progress'    => min(100,max(0,(int)($_POST['progress']??0))),
        'assigned_to' => (int)($_POST['assigned_to']??0) ?: null,
        'track_id'    => (int)($_POST['track_id']??0) ?: null,
        'start_date'  => $_POST['start_date'] ?: null,
        'due_date'    => $_POST['due_date'] ?: null,
        'effort_est'  => $_POST['effort_est'] !== '' ? (float)$_POST['effort_est'] : null,
        'program_id'  => (int)($_POST['program_id']??0),
    ];

    if (!$data['title'])      $errors[] = 'Task title is required.';
    if (!$data['program_id']) $errors[] = 'Program is required.';

    // Verify org owns the program
    if ($data['program_id'] && !$db->count('programs','id=? AND org_id=?',[$data['program_id'],$orgId])) {
        $errors[] = 'Invalid program.';
    }

    if (empty($errors)) {
        if ($data['status'] === 'completed' && (!$isEdit || $task['status'] !== 'completed')) {
            $data['completed_at'] = date('Y-m-d H:i:s');
            $data['progress']     = 100;
        }

        if ($isEdit) {
            $db->update('tasks', $data, 'id=?', [$id]);
            audit('update','tasks',$id,'Task updated: '.$data['title']);
            redirect('/tasks/view?id='.$id, 'Task updated.', 'success');
        } else {
            $data['created_by'] = authId();
            $newId = $db->insert('tasks', $data);
            audit('create','tasks',$newId,'Task created: '.$data['title']);
            redirect('/tasks/view?id='.$newId, 'Task created!', 'success');
        }
    }
}

$programs = $db->fetchAll("SELECT id,name FROM programs WHERE org_id=? AND status NOT IN ('cancelled','completed') ORDER BY name", [$orgId]);
$members  = $db->fetchAll("SELECT id,name FROM users WHERE org_id=? AND is_active=1 ORDER BY name", [$orgId]);
$selectedProgram = (int)($_POST['program_id'] ?? $task['program_id'] ?? $_GET['program_id'] ?? 0);
$tracks = $selectedProgram ? $db->fetchAll("SELECT id,name,color FROM tracks WHERE program_id=? AND is_active=1 ORDER BY order_index", [$selectedProgram]) : [];

$pageTitle = $isEdit ? 'Edit Task' : 'New Task';
ob_start();
?>

<div style="max-width:700px">
  <a href="javascript:history.back()" class="btn btn-ghost btn-sm mb-5"><i class="bi bi-arrow-left"></i> Back</a>

  <div class="pf-card">
    <div class="pf-card-header">
      <div class="pf-card-title"><i class="bi bi-<?= $isEdit?'pencil':'plus-circle' ?> me-2" style="color:var(--pf-indigo)"></i><?= $isEdit?'Edit Task':'Create Task' ?></div>
    </div>
    <div class="pf-card-body">

      <?php if ($errors): ?>
      <div class="pf-alert pf-alert-danger mb-4"><i class="bi bi-exclamation-circle-fill"></i><div><?php foreach($errors as $e) echo e($e).'<br>'; ?></div></div>
      <?php endif; ?>

      <form method="POST">
        <?= csrfField() ?>

        <div class="pf-form-group">
          <label class="pf-label">Task Title <span class="req">*</span></label>
          <input type="text" name="title" class="pf-input" placeholder="What needs to be done?"
                 value="<?= e($task['title'] ?? $_POST['title'] ?? '') ?>" required>
        </div>

        <div class="pf-form-group">
          <label class="pf-label">Description</label>
          <textarea name="description" class="pf-textarea" rows="3" placeholder="Details, acceptance criteria, notes…"><?= e($task['description'] ?? '') ?></textarea>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Program <span class="req">*</span></label>
            <select name="program_id" class="pf-select" id="programSelect" required>
              <option value="">— Select Program —</option>
              <?php foreach($programs as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $selectedProgram==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Track</label>
            <select name="track_id" class="pf-select" id="trackSelect">
              <option value="">— None —</option>
              <?php foreach($tracks as $tr): ?>
              <option value="<?= $tr['id'] ?>" <?= ($task['track_id']??$_GET['track_id']??'')==$tr['id']?'selected':'' ?>><?= e($tr['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Status</label>
            <select name="status" class="pf-select">
              <?php foreach(['not_started','in_progress','review','completed','blocked','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($task['status']??'not_started')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Priority</label>
            <select name="priority" class="pf-select">
              <?php foreach(['critical','high','medium','low'] as $p): ?>
              <option value="<?= $p ?>" <?= ($task['priority']??'medium')===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="pf-form-group">
          <label class="pf-label">Assign To</label>
          <select name="assigned_to" class="pf-select">
            <option value="">— Unassigned —</option>
            <?php foreach($members as $m): ?>
            <option value="<?= $m['id'] ?>" <?= ($task['assigned_to']??'')==$m['id']?'selected':'' ?>><?= e($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Start Date</label>
            <input type="date" name="start_date" class="pf-input" value="<?= e($task['start_date']??'') ?>">
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Due Date</label>
            <input type="date" name="due_date" class="pf-input" value="<?= e($task['due_date']??'') ?>">
          </div>
        </div>

        <div class="pf-form-row mb-4">
          <div class="pf-form-group mb-0">
            <label class="pf-label">Estimated Effort (hours)</label>
            <input type="number" name="effort_est" class="pf-input" step="0.5" placeholder="e.g. 8"
                   value="<?= e($task['effort_est']??'') ?>">
          </div>
          <div class="pf-form-group mb-0">
            <label class="pf-label">Progress — <span id="progressVal"><?= $task['progress']??0 ?>%</span></label>
            <input type="range" name="progress" min="0" max="100" step="5"
                   value="<?= $task['progress']??0 ?>"
                   data-progress-slider data-progress-display="progressVal"
                   style="width:100%;accent-color:var(--pf-indigo)">
          </div>
        </div>

        <div class="d-flex gap-3 mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $isEdit?'save':'plus-circle' ?>"></i>
            <?= $isEdit ? 'Save Changes' : 'Create Task' ?>
          </button>
          <a href="javascript:history.back()" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Load tracks when program changes
document.getElementById('programSelect').addEventListener('change', async function() {
  const pid = this.value;
  const sel = document.getElementById('trackSelect');
  sel.innerHTML = '<option value="">— Loading… —</option>';
  if (!pid) { sel.innerHTML = '<option value="">— None —</option>'; return; }
  const res = await fetch('<?= url('api/tracks') ?>?program_id=' + pid);
  const data = await res.json();
  sel.innerHTML = '<option value="">— None —</option>';
  (data.tracks || []).forEach(t => {
    sel.innerHTML += `<option value="${t.id}">${t.name}</option>`;
  });
});
</script>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

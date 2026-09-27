<?php
$pageTitle    = 'Dynamic Custom Field Engine';
$pageSubtitle = 'Extend Programs and Tasks with Dynamic Field Definitions (Jira / SAP Extensions)';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field_name'], $_POST['field_label'])) {
    $entityType = $_POST['entity_type'] ?? 'task';
    $fieldName  = slug($_POST['field_name']);
    $fieldLabel = trim($_POST['field_label']);
    $fieldType  = $_POST['field_type'] ?? 'text';
    $isRequired = isset($_POST['is_required']) ? 1 : 0;

    try {
        db()->insert('custom_fields', [
            'entity_type' => $entityType,
            'field_name'  => $fieldName,
            'field_label' => $fieldLabel,
            'field_type'  => $fieldType,
            'is_required' => $isRequired,
        ]);
        setFlash("Custom field '$fieldLabel' created successfully.");
    } catch (Throwable $e) {
        setFlash("Failed to create custom field: " . $e->getMessage(), 'danger');
    }
    redirect('/admin/custom_fields');
}

$fields = db()->fetchAll("SELECT * FROM custom_fields ORDER BY entity_type, id ASC");

ob_start();
?>

<div class="row g-4 mb-4">
  <div class="col-lg-4">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Custom Field</div>
      </div>
      <form method="POST">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Entity Type</label>
          <select name="entity_type" class="pf-input">
            <option value="task">Task / User Story</option>
            <option value="program">Program / Project</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Field Name (Key)</label>
          <input type="text" name="field_name" class="pf-input" placeholder="e.g. security_classification" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Display Label</label>
          <input type="text" name="field_label" class="pf-input" placeholder="e.g. Security Classification" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Field Type</label>
          <select name="field_type" class="pf-input">
            <option value="text">Text Input</option>
            <option value="number">Numeric Input</option>
            <option value="select">Dropdown Select</option>
            <option value="date">Date Picker</option>
          </select>
        </div>

        <button type="submit" class="pf-btn pf-btn-primary w-100">
          <i class="bi bi-plus-lg me-1"></i> Register Field
        </button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-sliders me-2 text-indigo"></i>Registered Custom Fields</div>
      </div>
      <div class="table-responsive">
        <table class="pf-table">
          <thead>
            <tr>
              <th>Entity</th>
              <th>Field Label</th>
              <th>Key Name</th>
              <th>Field Type</th>
              <th>Required</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fields as $f): ?>
            <tr>
              <td>
                <span class="badge badge-soft-<?= $f['entity_type'] === 'program' ? 'primary' : 'info' ?> text-uppercase">
                  <?= $f['entity_type'] ?>
                </span>
              </td>
              <td class="fw-bold" style="color:var(--pf-text)"><?= e($f['field_label']) ?></td>
              <td><code><?= e($f['field_name']) ?></code></td>
              <td><span class="badge bg-surface text-muted text-uppercase"><?= $f['field_type'] ?></span></td>
              <td><?= $f['is_required'] ? '<span class="text-danger fw-bold">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
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

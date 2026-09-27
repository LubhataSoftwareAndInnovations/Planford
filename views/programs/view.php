<?php
requireAuth();
$db    = db();
$orgId = authOrgId();
$id    = (int)($_GET['id'] ?? 0);

$prog = $db->fetchOne(
    "SELECT p.*, u.name as owner_name FROM programs p
     LEFT JOIN users u ON p.owner_id=u.id
     WHERE p.id=? AND p.org_id=?", [$id, $orgId]
);
if (!$prog) redirect('/programs', 'Program not found.', 'danger');

$tab = $_GET['tab'] ?? 'overview';

// Stats
$totalTasks    = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE program_id=?", [$id]);
$doneTasks     = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE program_id=? AND status='completed'", [$id]);
$blockedTasks  = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE program_id=? AND status='blocked'", [$id]);
$openRisks     = $db->fetchColumn("SELECT COUNT(*) FROM risks WHERE program_id=? AND status='open'", [$id]);
$progress      = $totalTasks > 0 ? round($doneTasks / $totalTasks * 100) : 0;

// Tracks
$tracks = $db->fetchAll("SELECT t.*, COUNT(tk.id) as task_count,
    SUM(tk.status='completed') as done_count
    FROM tracks t LEFT JOIN tasks tk ON t.id=tk.track_id
    WHERE t.program_id=? AND t.is_active=1 GROUP BY t.id ORDER BY t.order_index", [$id]);

// Tasks by track
$tasks = $db->fetchAll(
    "SELECT t.*, u.name as assignee_name, tr.name as track_name, tr.color as track_color
     FROM tasks t
     LEFT JOIN users u ON t.assigned_to=u.id
     LEFT JOIN tracks tr ON t.track_id=tr.id
     WHERE t.program_id=? AND t.parent_id IS NULL
     ORDER BY t.track_id, FIELD(t.priority,'critical','high','medium','low'), t.due_date", [$id]
);

// Milestones
$milestones = $db->fetchAll("SELECT * FROM milestones WHERE program_id=? ORDER BY due_date", [$id]);

// Team members
$members = $db->fetchAll(
    "SELECT u.id, u.name, u.email, u.role, u.title, pm.role as prog_role
     FROM program_members pm JOIN users u ON pm.user_id=u.id
     WHERE pm.program_id=? ORDER BY u.name", [$id]
);

// Recent updates
$updates = $db->fetchAll(
    "SELECT u.*, us.name as user_name, t.title as task_title
     FROM updates u JOIN users us ON u.user_id=us.id
     LEFT JOIN tasks t ON u.task_id=t.id
     WHERE u.program_id=? ORDER BY u.created_at DESC LIMIT 15", [$id]
);

// Risks
$risks = $db->fetchAll(
    "SELECT r.*, u.name as owner_name FROM risks r
     LEFT JOIN users u ON r.owner_id=u.id
     WHERE r.program_id=? ORDER BY FIELD(r.impact,'critical','high','medium','low'), r.created_at DESC", [$id]
);

// Documents
$documents = $db->fetchAll(
    "SELECT d.*, u.name as uploader_name FROM documents d
     JOIN users u ON d.uploaded_by=u.id
     WHERE d.program_id=? ORDER BY d.created_at DESC LIMIT 10", [$id]
);

$pageTitle    = $prog['name'];
$pageSubtitle = $prog['code'];
ob_start();
?>

<!-- Program header -->
<div style="background:linear-gradient(135deg,<?= e($prog['cover_color']) ?>22,transparent);border:1px solid <?= e($prog['cover_color']) ?>33;border-radius:var(--pf-radius);padding:24px;margin-bottom:24px;position:relative;overflow:hidden">
  <div style="position:absolute;top:0;left:0;right:0;height:4px;background:<?= e($prog['cover_color']) ?>"></div>
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--pf-text-3);margin-bottom:6px"><?= e($prog['code']) ?></div>
      <h2 style="font-size:22px;font-weight:800;margin-bottom:8px"><?= e($prog['name']) ?></h2>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <?= statusBadge($prog['status']) ?>
        <?= priorityBadge($prog['priority']) ?>
        <?php if ($prog['client_name']): ?>
        <span style="font-size:12.5px;color:var(--pf-text-3)"><i class="bi bi-building me-1"></i><?= e($prog['client_name']) ?></span>
        <?php endif; ?>
        <?php if ($prog['owner_name']): ?>
        <span style="font-size:12.5px;color:var(--pf-text-3)"><i class="bi bi-person me-1"></i><?= e($prog['owner_name']) ?></span>
        <?php endif; ?>
        <?php if ($prog['end_date']): ?>
        <span style="font-size:12.5px"><?= daysLeft($prog['end_date'], $prog['status']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php if (isPM()): ?>
    <div class="d-flex gap-2">
      <a href="<?= url('tasks/new?program_id='.$id) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Task</a>
      <div class="pf-dropdown">
        <button data-dropdown class="btn btn-outline btn-sm"><i class="bi bi-three-dots"></i></button>
        <div class="pf-dropdown-menu">
          <a class="pf-dropdown-item" href="<?= url('programs/edit?id='.$id) ?>"><i class="bi bi-pencil"></i> Edit Program</a>
          <a class="pf-dropdown-item" href="<?= url('milestones/new?program_id='.$id) ?>"><i class="bi bi-flag"></i> Add Milestone</a>
          <div class="pf-dropdown-divider"></div>
          <a class="pf-dropdown-item" href="<?= url('portal/'.$prog['stakeholder_token']) ?>" target="_blank"><i class="bi bi-share"></i> Stakeholder Portal</a>
          <a class="pf-dropdown-item" data-copy="<?= url('portal/'.$prog['stakeholder_token']) ?>"><i class="bi bi-link-45deg"></i> Copy Portal Link</a>
          <div class="pf-dropdown-divider"></div>
          <a class="pf-dropdown-item danger" href="<?= url('programs/delete?id='.$id) ?>"
             data-confirm="Delete this program? This cannot be undone."><i class="bi bi-trash"></i> Delete Program</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Progress bar -->
  <div style="margin-top:16px">
    <div class="d-flex align-items-center justify-content-between mb-1">
      <span style="font-size:12px;font-weight:600;color:var(--pf-text-3)">Overall Progress</span>
      <span style="font-size:13px;font-weight:700;color:<?= e($prog['cover_color']) ?>"><?= $progress ?>%</span>
    </div>
    <div class="pf-progress" style="height:8px">
      <div class="pf-progress-bar" style="width:<?= $progress ?>%;background:<?= e($prog['cover_color']) ?>"></div>
    </div>
  </div>
</div>

<!-- KPI strip -->
<div class="pf-kpi-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:24px">
  <div class="pf-kpi" style="--kpi-color:var(--pf-indigo);padding:14px 16px">
    <div class="pf-kpi-value" style="font-size:20px"><?= $totalTasks ?></div>
    <div class="pf-kpi-label">Total Tasks</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-green);padding:14px 16px">
    <div class="pf-kpi-value" style="font-size:20px"><?= $doneTasks ?></div>
    <div class="pf-kpi-label">Completed</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-red);padding:14px 16px">
    <div class="pf-kpi-value" style="font-size:20px"><?= $blockedTasks ?></div>
    <div class="pf-kpi-label">Blocked</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-amber);padding:14px 16px">
    <div class="pf-kpi-value" style="font-size:20px"><?= count($milestones) ?></div>
    <div class="pf-kpi-label">Milestones</div>
  </div>
  <div class="pf-kpi" style="--kpi-color:var(--pf-orange);padding:14px 16px">
    <div class="pf-kpi-value" style="font-size:20px"><?= $openRisks ?></div>
    <div class="pf-kpi-label">Open Risks</div>
  </div>
</div>

<!-- Tabs -->
<div class="pf-tabs">
  <?php
  $tabs = ['overview'=>['bi-grid','Overview'], 'tasks'=>['bi-check2-square','Tasks ('.count($tasks).')'],
           'milestones'=>['bi-flag','Milestones'], 'risks'=>['bi-shield-exclamation','Risks'],
           'documents'=>['bi-folder2','Documents'], 'team'=>['bi-people','Team'], 'updates'=>['bi-activity','Updates']];
  foreach ($tabs as $k => [$icon,$label]): ?>
  <a href="?id=<?= $id ?>&tab=<?= $k ?>" class="pf-tab <?= $tab===$k?'active':'' ?>">
    <i class="bi <?= $icon ?>"></i> <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Tab content -->
<?php

// ── OVERVIEW ────────────────────────────────────────────────
if ($tab === 'overview'):
?>
<div class="d-flex gap-4 flex-wrap" style="align-items:flex-start">
  <div style="flex:1;min-width:0">
    <?php if ($prog['description']): ?>
    <div class="pf-card mb-4">
      <div class="pf-card-header"><div class="pf-card-title">About</div></div>
      <div class="pf-card-body" style="font-size:14px;line-height:1.7;color:var(--pf-text-2)"><?= nl2br(e($prog['description'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Tracks overview -->
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-layers me-2"></i>Tracks</div>
        <?php if (isPM()): ?>
        <a href="?id=<?= $id ?>&tab=tasks" class="btn btn-ghost btn-sm">Manage Tasks</a>
        <?php endif; ?>
      </div>
      <?php foreach ($tracks as $tr):
        $tp = $tr['task_count'] > 0 ? round($tr['done_count']/$tr['task_count']*100) : 0;
      ?>
      <div style="padding:14px 20px;border-bottom:1px solid var(--pf-border)">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <i class="bi <?= e($tr['icon']) ?>" style="color:<?= e($tr['color']) ?>"></i>
            <span style="font-weight:600;font-size:13.5px"><?= e($tr['name']) ?></span>
            <span style="font-size:11.5px;color:var(--pf-text-3)"><?= $tr['done_count'] ?>/<?= $tr['task_count'] ?> tasks</span>
          </div>
          <span style="font-weight:700;color:<?= e($tr['color']) ?>;font-size:13px"><?= $tp ?>%</span>
        </div>
        <div class="pf-progress" style="height:5px">
          <div class="pf-progress-bar" style="width:<?= $tp ?>%;background:<?= e($tr['color']) ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div style="width:280px;flex-shrink:0">
    <!-- Details -->
    <div class="pf-card mb-4">
      <div class="pf-card-header"><div class="pf-card-title">Details</div></div>
      <div class="pf-card-body" style="padding:0">
        <?php
        $details = [
          ['Status',    statusBadge($prog['status']),  ''],
          ['Priority',  priorityBadge($prog['priority']), ''],
          ['Start',     fDate($prog['start_date']),    'bi-calendar-event'],
          ['End',       fDate($prog['end_date']),      'bi-calendar-check'],
          ['Budget',    $prog['budget'] ? number_format($prog['budget'],2).' '.$prog['currency'] : '—', 'bi-currency-dollar'],
          ['Client',    $prog['client_name'] ?: '—',  'bi-building'],
        ];
        foreach ($details as [$label, $val, $icon]): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--pf-border);font-size:13px">
          <span style="color:var(--pf-text-3)"><?= $label ?></span>
          <span class="fw-600"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Upcoming milestones -->
    <?php if (!empty($milestones)): ?>
    <div class="pf-card">
      <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-flag me-2" style="color:var(--pf-amber)"></i>Milestones</div></div>
      <?php foreach ($milestones as $m): ?>
      <div style="padding:10px 16px;border-bottom:1px solid var(--pf-border);font-size:13px">
        <div class="d-flex align-items-center justify-content-between">
          <span class="fw-600 truncate"><?= e(truncate($m['title'],32)) ?></span>
          <?= statusBadge($m['status']) ?>
        </div>
        <div style="color:var(--pf-text-3);font-size:11.5px;margin-top:2px"><?= fDate($m['due_date']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
// ── TASKS ────────────────────────────────────────────────────
elseif ($tab === 'tasks'):
$grouped = [];
foreach ($tasks as $t) $grouped[$t['track_id'] ?? 0][] = $t;
?>
<?php if (isPM()): ?>
<div class="d-flex justify-content-end mb-4">
  <a href="<?= url('tasks/new?program_id='.$id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Task</a>
</div>
<?php endif; ?>

<?php foreach ($tracks as $tr):
  $trackTasks = $grouped[$tr['id']] ?? [];
?>
<div class="pf-card mb-4">
  <div class="pf-card-header" style="background:<?= e($tr['color']) ?>0d;border-left:4px solid <?= e($tr['color']) ?>">
    <div class="d-flex align-items-center gap-2">
      <i class="bi <?= e($tr['icon']) ?>" style="color:<?= e($tr['color']) ?>"></i>
      <span class="pf-card-title"><?= e($tr['name']) ?></span>
      <span class="badge badge-soft-secondary"><?= count($trackTasks) ?></span>
    </div>
    <?php if (isPM()): ?>
    <a href="<?= url('tasks/new?program_id='.$id.'&track_id='.$tr['id']) ?>" class="btn btn-ghost btn-sm">
      <i class="bi bi-plus"></i> Add
    </a>
    <?php endif; ?>
  </div>
  <?php if (empty($trackTasks)): ?>
  <div class="pf-empty" style="padding:24px"><div class="pf-empty-sub">No tasks in this track yet</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr>
        <th style="width:40%">Task</th><th>Status</th><th>Priority</th>
        <th>Assignee</th><th>Due</th><th>Progress</th><?php if(isPM()):?><th></th><?php endif;?>
      </tr></thead>
      <tbody>
        <?php foreach ($trackTasks as $t): ?>
        <tr>
          <td>
            <a href="<?= url('tasks/view?id='.$t['id']) ?>" style="font-weight:600;color:var(--pf-text);text-decoration:none" class="truncate d-block" style="max-width:280px">
              <?= e($t['title']) ?>
            </a>
            <?php if ($t['progress'] > 0): ?>
            <div class="pf-progress mt-1" style="height:3px;max-width:120px">
              <div class="pf-progress-bar" style="width:<?= $t['progress'] ?>%;background:<?= e($tr['color']) ?>"></div>
            </div>
            <?php endif; ?>
          </td>
          <td><?= statusBadge($t['status']) ?></td>
          <td><?= priorityBadge($t['priority']) ?></td>
          <td>
            <?php if ($t['assignee_name']): ?>
            <div class="d-flex align-items-center gap-1">
              <?= avatar($t['assignee_name'], '', '24px') ?>
              <span style="font-size:12px"><?= e(explode(' ',$t['assignee_name'])[0]) ?></span>
            </div>
            <?php else: ?><span style="color:var(--pf-text-3)">—</span><?php endif; ?>
          </td>
          <td style="font-size:12.5px"><?= daysLeft($t['due_date'], $t['status']) ?></td>
          <td style="font-size:12px;color:var(--pf-text-3);min-width:70px"><?= $t['progress'] ?>%</td>
          <?php if (isPM()): ?>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= url('tasks/edit?id='.$t['id']) ?>" class="btn btn-ghost btn-sm btn-icon" data-tip="Edit"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="<?= url('tasks/delete') ?>">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--pf-red)" data-confirm="Delete this task?" data-tip="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endforeach;

// ── MILESTONES ───────────────────────────────────────────────
elseif ($tab === 'milestones'):
?>
<?php if (isPM()): ?>
<div class="d-flex justify-content-end mb-4">
  <a href="<?= url('milestones/new?program_id='.$id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Milestone</a>
</div>
<?php endif; ?>
<div class="pf-card">
  <?php if (empty($milestones)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-flag"></i></div><div class="pf-empty-title">No milestones yet</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Milestone</th><th>Due Date</th><th>Achieved</th><th>Status</th><th>Client Visible</th><?php if(isPM()):?><th></th><?php endif;?></tr></thead>
      <tbody>
        <?php foreach ($milestones as $m): ?>
        <tr>
          <td><div class="fw-600"><?= e($m['title']) ?></div><?php if($m['description']): ?><div style="font-size:12px;color:var(--pf-text-3)"><?= e(truncate($m['description'],60)) ?></div><?php endif; ?></td>
          <td style="font-size:13px"><?= fDate($m['due_date']) ?><br><?= daysLeft($m['due_date'],$m['status']) ?></td>
          <td style="font-size:13px;color:var(--pf-green)"><?= fDate($m['achieved_date']) ?></td>
          <td><?= statusBadge($m['status']) ?></td>
          <td><?= $m['is_client_visible']?'<span class="badge badge-soft-success">Yes</span>':'<span class="badge badge-soft-secondary">No</span>' ?></td>
          <?php if(isPM()):?>
          <td><a href="<?= url('milestones/edit?id='.$m['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-pencil"></i></a></td>
          <?php endif;?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php
// ── RISKS ────────────────────────────────────────────────────
elseif ($tab === 'risks'):
?>
<?php if (isPM()): ?>
<div class="d-flex justify-content-end mb-4">
  <a href="<?= url('risks/new?program_id='.$id) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Risk / Issue</a>
</div>
<?php endif; ?>
<div class="pf-card">
  <?php if (empty($risks)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-shield"></i></div><div class="pf-empty-title">No risks logged</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Title</th><th>Type</th><th>Impact</th><th>Likelihood</th><th>Status</th><th>Owner</th><?php if(isPM()):?><th></th><?php endif;?></tr></thead>
      <tbody>
        <?php foreach ($risks as $r): ?>
        <tr>
          <td><div class="fw-600"><?= e($r['title']) ?></div><?php if($r['mitigation']): ?><div style="font-size:12px;color:var(--pf-text-3)"><?= e(truncate($r['mitigation'],60)) ?></div><?php endif;?></td>
          <td><span class="badge badge-soft-secondary"><?= ucfirst($r['type']) ?></span></td>
          <td><?= priorityBadge($r['impact']) ?></td>
          <td><?= priorityBadge($r['likelihood']) ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td style="font-size:13px"><?= e($r['owner_name'] ?: '—') ?></td>
          <?php if(isPM()):?><td><a href="<?= url('risks/edit?id='.$r['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-pencil"></i></a></td><?php endif;?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php
// ── DOCUMENTS ────────────────────────────────────────────────
elseif ($tab === 'documents'):
?>
<div class="d-flex justify-content-end mb-4">
  <a href="<?= url('documents/upload?program_id='.$id) ?>" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Document</a>
</div>
<div class="pf-card">
  <?php if (empty($documents)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-folder2"></i></div><div class="pf-empty-title">No documents yet</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Document</th><th>Category</th><th>Version</th><th>Status</th><th>Uploaded By</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($documents as $d): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi <?= fileIcon($d['mime'] ?? '', pathinfo($d['origname'],PATHINFO_EXTENSION)) ?> fs-18"></i>
              <div>
                <div class="fw-600"><?= e($d['title']) ?></div>
                <div style="font-size:11.5px;color:var(--pf-text-3)"><?= e($d['origname']) ?> · <?= formatBytes($d['size']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-soft-secondary"><?= e(ucfirst($d['category'])) ?></span></td>
          <td style="font-size:13px">v<?= e($d['version']) ?></td>
          <td><?= statusBadge($d['status']) ?></td>
          <td style="font-size:13px"><?= e($d['uploader_name']) ?></td>
          <td style="font-size:12px;color:var(--pf-text-3)"><?= fDate($d['created_at']) ?></td>
          <td><a href="<?= url('documents/download?id='.$d['id']) ?>" class="btn btn-ghost btn-sm btn-icon"><i class="bi bi-download"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php
// ── TEAM ────────────────────────────────────────────────────
elseif ($tab === 'team'):
?>
<div class="pf-card">
  <?php if (empty($members)): ?>
  <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-people"></i></div><div class="pf-empty-title">No members assigned</div></div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th>Name</th><th>Role</th><th>Program Role</th><th>Department</th></tr></thead>
      <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <?= avatar($m['name'], '', '32px') ?>
              <div>
                <div class="fw-600"><?= e($m['name']) ?></div>
                <div style="font-size:12px;color:var(--pf-text-3)"><?= e($m['email']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-soft-secondary"><?= e(ucfirst(str_replace('_',' ',$m['role']))) ?></span></td>
          <td><span class="badge badge-soft-primary"><?= e(ucfirst($m['prog_role'])) ?></span></td>
          <td style="font-size:13px;color:var(--pf-text-3)"><?= e($m['title'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php
// ── UPDATES ─────────────────────────────────────────────────
elseif ($tab === 'updates'):
?>
<?php if (canEdit()): ?>
<div class="pf-card mb-4">
  <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-plus-circle me-2"></i>Post Update</div></div>
  <div class="pf-card-body">
    <form method="POST" action="<?= url('updates/new') ?>">
      <?= csrfField() ?>
      <input type="hidden" name="program_id" value="<?= $id ?>">
      <textarea name="body" class="pf-textarea" rows="3" placeholder="Share a progress update, note, or status change…" required></textarea>
      <div class="d-flex gap-3 align-items-center mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Post Update</button>
        <span style="font-size:12px;color:var(--pf-text-3)">Visible to all program members</span>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="pf-feed">
  <?php if (empty($updates)): ?>
  <div class="pf-empty" style="padding:40px"><div class="pf-empty-icon"><i class="bi bi-activity"></i></div><div class="pf-empty-title">No updates yet</div></div>
  <?php else: foreach ($updates as $u): ?>
  <div class="pf-feed-item">
    <div class="pf-feed-dot">
      <?= avatar($u['user_name'], '', '38px') ?>
    </div>
    <div class="pf-feed-content">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <strong style="font-size:13.5px"><?= e($u['user_name']) ?></strong>
        <span style="font-size:12px;color:var(--pf-text-3)"><?= timeAgo($u['created_at']) ?></span>
      </div>
      <?php if ($u['task_title']): ?>
      <div style="font-size:11.5px;color:var(--pf-text-3);margin-bottom:6px">
        <i class="bi bi-check2-square me-1"></i>Re: <?= e($u['task_title']) ?>
      </div>
      <?php endif; ?>
      <div style="font-size:13.5px;line-height:1.6"><?= nl2br(e($u['body'])) ?></div>
      <?php if ($u['progress'] !== null): ?>
      <div style="margin-top:8px;font-size:12px;color:var(--pf-text-3)">Progress updated to <strong><?= $u['progress'] ?>%</strong></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

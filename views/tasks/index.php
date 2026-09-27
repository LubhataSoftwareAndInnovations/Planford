<?php
requireAuth();
$db  = db();
$uid = authId();
$orgId = authOrgId();

$filterStatus   = $_GET['status'] ?? 'open';
$filterPriority = $_GET['priority'] ?? 'all';
$filterProgram  = (int)($_GET['program_id'] ?? 0);
$page = max(1,(int)($_GET['page']??1));

$where  = "t.program_id IN (SELECT id FROM programs WHERE org_id=?)";
$params = [$orgId];
if (!isOrgAdmin()) { $where .= " AND t.assigned_to=?"; $params[] = $uid; }
if ($filterStatus === 'open')  { $where .= " AND t.status NOT IN ('completed','cancelled')"; }
elseif ($filterStatus !== 'all') { $where .= " AND t.status=?"; $params[] = $filterStatus; }
if ($filterPriority !== 'all') { $where .= " AND t.priority=?"; $params[] = $filterPriority; }
if ($filterProgram)            { $where .= " AND t.program_id=?"; $params[] = $filterProgram; }

$total = $db->fetchColumn("SELECT COUNT(*) FROM tasks t WHERE $where", $params);
$tasks = $db->fetchAll(
    "SELECT t.*, p.name as program_name, p.cover_color, u.name as assignee_name,
     tr.name as track_name, tr.color as track_color
     FROM tasks t
     JOIN programs p ON t.program_id=p.id
     LEFT JOIN users u ON t.assigned_to=u.id
     LEFT JOIN tracks tr ON t.track_id=tr.id
     WHERE $where
     ORDER BY FIELD(t.priority,'critical','high','medium','low'), t.due_date ASC
     LIMIT " . PER_PAGE . " OFFSET " . (($page-1)*PER_PAGE), $params
);

$programs = $db->fetchAll("SELECT id,name FROM programs WHERE org_id=? ORDER BY name", [$orgId]);

$pageTitle = 'Tasks';
ob_start();
?>

<div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
  <div>
    <h1 style="font-size:22px;font-weight:800;margin-bottom:4px"><?= isOrgAdmin() ? 'All Tasks' : 'My Tasks' ?></h1>
    <p style="color:var(--pf-text-3);font-size:13px"><?= number_format($total) ?> task<?= $total!=1?'s':'' ?></p>
  </div>
  <?php if (canEdit()): ?>
  <a href="<?= url('tasks/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Task</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="pf-card mb-5">
  <div class="pf-card-body" style="padding:14px 20px">
    <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
      <div>
        <label class="pf-label">Status</label>
        <select name="status" class="pf-select" style="width:130px">
          <option value="open" <?= $filterStatus==='open'?'selected':'' ?>>Open</option>
          <option value="all" <?= $filterStatus==='all'?'selected':'' ?>>All</option>
          <?php foreach(['not_started','in_progress','review','completed','blocked','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="pf-label">Priority</label>
        <select name="priority" class="pf-select" style="width:110px">
          <option value="all">All</option>
          <?php foreach(['critical','high','medium','low'] as $p): ?>
          <option value="<?= $p ?>" <?= $filterPriority===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="pf-label">Program</label>
        <select name="program_id" class="pf-select" style="width:180px">
          <option value="">All Programs</option>
          <?php foreach($programs as $pr): ?>
          <option value="<?= $pr['id'] ?>" <?= $filterProgram==$pr['id']?'selected':'' ?>><?= e(truncate($pr['name'],25)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:8px;align-items:center;margin-top:auto">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="<?= url('tasks') ?>" class="btn btn-outline btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Table -->
<div class="pf-card">
  <?php if (empty($tasks)): ?>
  <div class="pf-empty">
    <div class="pf-empty-icon"><i class="bi bi-check2-all"></i></div>
    <div class="pf-empty-title">No tasks found</div>
    <div class="pf-empty-sub">Try adjusting the filters above.</div>
  </div>
  <?php else: ?>
  <div class="pf-table-wrap">
    <table class="pf-table">
      <thead><tr><th style="width:35%">Task</th><th>Program</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Due</th><th>Progress</th></tr></thead>
      <tbody>
        <?php foreach($tasks as $t): ?>
        <tr onclick="location='<?= url('tasks/view?id='.$t['id']) ?>'" style="cursor:pointer">
          <td>
            <div class="fw-600 truncate" style="max-width:260px"><?= e($t['title']) ?></div>
            <?php if($t['track_name']): ?>
            <span style="font-size:11px;color:<?= e($t['track_color']??'var(--pf-text-3)') ?>"><?= e($t['track_name']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($t['cover_color']) ?>;margin-right:5px"></span>
            <span style="font-size:12.5px"><?= e(truncate($t['program_name'],24)) ?></span>
          </td>
          <td><?= statusBadge($t['status']) ?></td>
          <td><?= priorityBadge($t['priority']) ?></td>
          <td>
            <?php if($t['assignee_name']): ?>
            <?= avatar($t['assignee_name'],'','24px') ?>
            <?php else: ?><span style="color:var(--pf-text-3)">—</span><?php endif; ?>
          </td>
          <td style="font-size:12.5px"><?= daysLeft($t['due_date'],$t['status']) ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="pf-progress" style="width:60px;height:4px"><div class="pf-progress-bar" style="width:<?= $t['progress'] ?>%"></div></div>
              <span style="font-size:12px;color:var(--pf-text-3)"><?= $t['progress'] ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="pf-card-footer"><?= pagination($total,$page,PER_PAGE,url('tasks').'?status='.$filterStatus.'&priority='.$filterPriority.'&program_id='.$filterProgram) ?></div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

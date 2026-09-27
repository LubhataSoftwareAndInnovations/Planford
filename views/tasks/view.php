<?php
requireAuth();
$db    = db();
$orgId = authOrgId();
$id    = (int)($_GET['id'] ?? 0);

$task = $db->fetchOne(
    "SELECT t.*, p.name as program_name, p.cover_color, p.id as pid,
     u.name as assignee_name, u2.name as creator_name,
     tr.name as track_name, tr.color as track_color
     FROM tasks t
     JOIN programs p ON t.program_id=p.id
     LEFT JOIN users u ON t.assigned_to=u.id
     LEFT JOIN users u2 ON t.created_by=u2.id
     LEFT JOIN tracks tr ON t.track_id=tr.id
     WHERE t.id=? AND p.org_id=?", [$id, $orgId]
);
if (!$task) redirect('/tasks', 'Task not found.', 'danger');

// Handle comment post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $body = trim($_POST['comment_body'] ?? '');
    if ($body) {
        $db->insert('task_comments', ['task_id'=>$id,'user_id'=>authId(),'body'=>$body]);
        audit('comment','tasks',$id,'Comment added');
        redirect('/tasks/view?id='.$id, 'Comment added.', 'success');
    }
}

// Handle progress update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress']) && verifyCsrf()) {
    $progress = min(100,max(0,(int)$_POST['progress']));
    $status   = $_POST['task_status'] ?? $task['status'];
    $updateData = ['progress'=>$progress,'status'=>$status];
    if ($status === 'completed') { $updateData['completed_at'] = date('Y-m-d H:i:s'); $updateData['progress'] = 100; }
    $db->update('tasks', $updateData, 'id=?', [$id]);
    if (isset($_POST['update_body']) && trim($_POST['update_body'])) {
        $db->insert('updates', ['program_id'=>$task['program_id'],'task_id'=>$id,'user_id'=>authId(),'body'=>trim($_POST['update_body']),'progress'=>$progress,'status_from'=>$task['status'],'status_to'=>$status]);
    }
    audit('update','tasks',$id,"Progress: {$progress}%, Status: {$status}");
    redirect('/tasks/view?id='.$id, 'Task updated.', 'success');
}

$comments = $db->fetchAll(
    "SELECT c.*, u.name as user_name FROM task_comments c
     JOIN users u ON c.user_id=u.id
     WHERE c.task_id=? AND c.parent_id IS NULL ORDER BY c.created_at", [$id]
);

$pageTitle    = truncate($task['title'], 50);
$pageSubtitle = $task['program_name'];
ob_start();
?>

<div class="d-flex gap-5 flex-wrap" style="align-items:flex-start">

  <!-- Main -->
  <div style="flex:1;min-width:0">
    <!-- Back -->
    <a href="<?= url('programs/view?id='.$task['pid'].'&tab=tasks') ?>" class="btn btn-ghost btn-sm mb-4">
      <i class="bi bi-arrow-left"></i> <?= e($task['program_name']) ?>
    </a>

    <!-- Task header -->
    <div class="pf-card mb-4">
      <div class="pf-card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= statusBadge($task['status']) ?>
            <?= priorityBadge($task['priority']) ?>
            <?php if ($task['track_name']): ?>
            <span class="badge" style="background:<?= e($task['track_color']) ?>22;color:<?= e($task['track_color']) ?>"><?= e($task['track_name']) ?></span>
            <?php endif; ?>
          </div>
          <?php if (isPM() || authId()==$task['assigned_to']): ?>
          <div class="d-flex gap-2">
            <a href="<?= url('tasks/edit?id='.$id) ?>" class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i> Edit</a>
          </div>
          <?php endif; ?>
        </div>

        <h1 style="font-size:20px;font-weight:800;line-height:1.3;margin-bottom:12px"><?= e($task['title']) ?></h1>

        <?php if ($task['description']): ?>
        <div style="font-size:14px;line-height:1.7;color:var(--pf-text-2)"><?= nl2br(e($task['description'])) ?></div>
        <?php endif; ?>

        <!-- Progress -->
        <div style="margin-top:20px">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span style="font-size:12px;font-weight:600;color:var(--pf-text-3)">Progress</span>
            <span style="font-size:13px;font-weight:700;color:var(--pf-indigo)"><?= $task['progress'] ?>%</span>
          </div>
          <?= progressBar($task['progress'], '8px') ?>
        </div>
      </div>
    </div>

    <!-- Update progress (PM or assignee) -->
    <?php if (isPM() || authId()==$task['assigned_to']): ?>
    <div class="pf-card mb-4">
      <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-graph-up me-2"></i>Update Progress</div></div>
      <div class="pf-card-body">
        <form method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="update_progress" value="1">
          <div class="pf-form-row mb-3">
            <div>
              <label class="pf-label">Progress — <span id="progressDisplay"><?= $task['progress'] ?>%</span></label>
              <input type="range" name="progress" min="0" max="100" step="5" value="<?= $task['progress'] ?>"
                     data-progress-slider data-progress-display="progressDisplay"
                     style="width:100%;accent-color:var(--pf-indigo)">
            </div>
            <div>
              <label class="pf-label">Status</label>
              <select name="task_status" class="pf-select">
                <?php foreach(['not_started','in_progress','review','completed','blocked','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $task['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <textarea name="update_body" class="pf-textarea mb-3" rows="2" placeholder="Add a note about this update (optional)"></textarea>
          <button type="submit" class="btn btn-primary btn-sm">Save Update</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Comments -->
    <div class="pf-card">
      <div class="pf-card-header">
        <div class="pf-card-title"><i class="bi bi-chat-dots me-2"></i>Comments (<?= count($comments) ?>)</div>
      </div>
      <div class="pf-card-body" style="padding:20px">

        <!-- Add comment -->
        <form method="POST" style="margin-bottom:24px">
          <?= csrfField() ?>
          <div class="d-flex gap-3">
            <?= avatar(auth()['name'], '', '36px') ?>
            <div style="flex:1">
              <textarea name="comment_body" class="pf-textarea" rows="2" placeholder="Add a comment…"></textarea>
              <button type="submit" class="btn btn-primary btn-sm mt-2">Comment</button>
            </div>
          </div>
        </form>

        <!-- Comment list -->
        <?php if (empty($comments)): ?>
        <div style="text-align:center;color:var(--pf-text-3);font-size:13px;padding:20px 0">No comments yet. Start the conversation.</div>
        <?php else: ?>
        <div class="pf-feed">
          <?php foreach($comments as $c): ?>
          <div class="pf-feed-item">
            <div class="pf-feed-dot"><?= avatar($c['user_name'],'','38px') ?></div>
            <div class="pf-feed-content">
              <div class="d-flex align-items-center gap-2 mb-1">
                <strong style="font-size:13.5px"><?= e($c['user_name']) ?></strong>
                <span style="font-size:12px;color:var(--pf-text-3)"><?= timeAgo($c['created_at']) ?></span>
              </div>
              <div style="font-size:13.5px;line-height:1.6"><?= nl2br(e($c['body'])) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar details -->
  <div style="width:260px;flex-shrink:0">
    <div class="pf-card">
      <div class="pf-card-header"><div class="pf-card-title">Details</div></div>
      <div style="padding:0">
        <?php
        $details = [
          ['Program',  '<a href="'.url('programs/view?id='.$task['pid']).'" style="color:var(--pf-indigo)">'.e($task['program_name']).'</a>', ''],
          ['Assigned', $task['assignee_name'] ? (avatar($task['assignee_name'],'','22px').'<span style="margin-left:6px;font-size:13px">'.e($task['assignee_name']).'</span>') : '<span style="color:var(--pf-text-3)">Unassigned</span>', ''],
          ['Created',  fDate($task['created_at']), ''],
          ['Due Date', $task['due_date'] ? (fDate($task['due_date']).' · '.daysLeft($task['due_date'],$task['status'])) : '—', ''],
          ['Start',    fDate($task['start_date']), ''],
          ['Effort',   $task['effort_est'] ? $task['effort_est'].'h estimated' : '—', ''],
        ];
        ?>
        <?php foreach($details as [$label, $val]): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--pf-border);font-size:13px;gap:8px">
          <span style="color:var(--pf-text-3);flex-shrink:0"><?= $label ?></span>
          <div class="d-flex align-items-center" style="gap:4px"><?= $val ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . 'layouts/app.php';

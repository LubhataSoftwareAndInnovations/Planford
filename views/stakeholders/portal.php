<?php
$token = ltrim(str_replace('/portal/', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
$prog = db()->fetchOne("SELECT p.*,u.name as owner_name FROM programs p LEFT JOIN users u ON p.owner_id=u.id WHERE p.stakeholder_token=?", [$token]);
if (!$prog) { http_response_code(404); echo 'Invalid or expired portal link.'; exit; }
$progId = $prog['id'];
$milestones = db()->fetchAll("SELECT * FROM milestones WHERE program_id=? AND is_client_visible=1 ORDER BY due_date", [$progId]);
$documents  = db()->fetchAll("SELECT * FROM documents WHERE program_id=? AND is_client_visible=1 ORDER BY created_at DESC", [$progId]);
$updates    = db()->fetchAll("SELECT u.*,us.name as user_name FROM updates u JOIN users us ON u.user_id=us.id WHERE u.program_id=? ORDER BY u.created_at DESC LIMIT 10", [$progId]);
$totalT=$doneTasks=0;
$taskStats = db()->fetchAll("SELECT status,COUNT(*) as cnt FROM tasks WHERE program_id=? GROUP BY status", [$progId]);
foreach($taskStats as $s){$totalT+=$s['cnt']; if($s['status']==='completed') $doneTasks=$s['cnt'];}
$progress = $totalT>0?round($doneTasks/$totalT*100):0;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($prog['name']) ?> — Client Portal</title>
<link href="<?= url('assets/css/planford.css') ?>" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script>const t=localStorage.getItem('pf_theme')||'light';document.documentElement.setAttribute('data-theme',t);</script>
</head>
<body>
<div style="min-height:100vh;background:var(--pf-bg)">
  <!-- Portal header -->
  <header style="background:var(--pf-surface);border-bottom:1px solid var(--pf-border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--pf-indigo),var(--pf-violet));border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px"><i class="bi bi-hexagon-fill"></i></div>
      <div><div style="font-weight:700;font-size:15px">Planford Client Portal</div><div style="font-size:11px;color:var(--pf-text-3)">by Lubhata</div></div>
    </div>
    <div style="font-size:13px;color:var(--pf-text-3)">Confidential · <?= fDate(date('Y-m-d')) ?></div>
  </header>

  <div style="max-width:900px;margin:0 auto;padding:32px 20px">
    <!-- Program header -->
    <div class="pf-card mb-5" style="border-top:4px solid <?= e($prog['cover_color']) ?>">
      <div class="pf-card-body">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--pf-text-3);margin-bottom:4px"><?= e($prog['code']) ?></div>
        <h1 style="font-size:24px;font-weight:800;margin-bottom:8px"><?= e($prog['name']) ?></h1>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
          <?= statusBadge($prog['status']) ?><?= priorityBadge($prog['priority']) ?>
          <?php if($prog['end_date']): ?><span style="font-size:13px;color:var(--pf-text-3)"><i class="bi bi-calendar-check"></i> Target: <?= fDate($prog['end_date']) ?></span><?php endif;?>
        </div>
        <div style="margin-top:12px">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px">
            <span style="font-size:13px;font-weight:600">Overall Progress</span>
            <span style="font-weight:800;color:<?= e($prog['cover_color']) ?>"><?= $progress ?>%</span>
          </div>
          <div class="pf-progress" style="height:10px"><div class="pf-progress-bar" style="width:<?= $progress ?>%;background:<?= e($prog['cover_color']) ?>"></div></div>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
      <div class="pf-kpi" style="--kpi-color:var(--pf-indigo)"><div class="pf-kpi-value"><?= $totalT ?></div><div class="pf-kpi-label">Total Tasks</div></div>
      <div class="pf-kpi" style="--kpi-color:var(--pf-green)"><div class="pf-kpi-value"><?= $doneTasks ?></div><div class="pf-kpi-label">Completed</div></div>
    </div>

    <?php if(!empty($milestones)):?>
    <div class="pf-card mb-5">
      <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-flag me-2" style="color:var(--pf-amber)"></i>Milestones</div></div>
      <?php foreach($milestones as $m): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--pf-border)">
        <div><div class="fw-600"><?= e($m['title']) ?></div><div style="font-size:12px;color:var(--pf-text-3)"><?= fDate($m['due_date']) ?></div></div>
        <?= statusBadge($m['status']) ?>
      </div>
      <?php endforeach;?>
    </div>
    <?php endif;?>

    <?php if(!empty($updates)):?>
    <div class="pf-card">
      <div class="pf-card-header"><div class="pf-card-title"><i class="bi bi-activity me-2"></i>Latest Updates</div></div>
      <div class="pf-card-body" style="padding:20px">
        <div class="pf-feed">
          <?php foreach($updates as $u):?>
          <div class="pf-feed-item">
            <div class="pf-feed-dot"><?= avatar($u['user_name'],'','38px') ?></div>
            <div class="pf-feed-content">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px"><strong><?= e($u['user_name']) ?></strong><span style="font-size:12px;color:var(--pf-text-3)"><?= timeAgo($u['created_at']) ?></span></div>
              <div style="font-size:13.5px;line-height:1.6"><?= nl2br(e($u['body'])) ?></div>
            </div>
          </div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
    <?php endif;?>
  </div>
</div>
<script src="<?= url('assets/js/planford.js') ?>"></script>
</body></html>

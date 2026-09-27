<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf" content="<?= csrf() ?>">
<title><?= e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⬡</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= url('assets/css/planford.css') ?>" rel="stylesheet">
<?= $extraHead ?? '' ?>
</head>
<body>

<script>
window.PF = { url: '<?= rtrim(APP_URL, '/') ?>' };
// Apply saved theme immediately to avoid flash
const t = localStorage.getItem('pf_theme') || 'light';
document.documentElement.setAttribute('data-theme', t);
</script>

<div class="pf-shell">

  <!-- ── Sidebar ──────────────────────────────────────────── -->
  <aside class="pf-sidebar">
    <a href="<?= url('dashboard') ?>" class="pf-sidebar-logo">
      <div class="pf-logo-icon"><i class="bi bi-hexagon-fill"></i></div>
      <div class="pf-logo-text">
        <div class="pf-logo-name"><?= APP_NAME ?></div>
        <div class="pf-logo-by">by <?= APP_COMPANY ?></div>
      </div>
    </a>

    <nav class="pf-sidebar-nav">

      <div class="pf-nav-section"><span class="pf-nav-label">Overview</span></div>
      <a href="<?= url('dashboard') ?>" class="pf-nav-item <?= isActive('/dashboard', '/') ?>">
        <i class="bi bi-speedometer2"></i> Executive Tower
      </a>
      <a href="<?= url('portfolio/okrs') ?>" class="pf-nav-item <?= isActive('/portfolio/okrs') ?>">
        <i class="bi bi-bullseye"></i> Strategic OKRs
      </a>
      <a href="<?= url('programs') ?>" class="pf-nav-item <?= isActive('/programs') ?>">
        <i class="bi bi-collection"></i> Programs & Clients
      </a>

      <div class="pf-nav-section"><span class="pf-nav-label">Agile & Scrum</span></div>
      <a href="<?= url('agile/board') ?>" class="pf-nav-item <?= isActive('/agile/board') ?>">
        <i class="bi bi-kanban"></i> Sprint Board
      </a>
      <a href="<?= url('sprints') ?>" class="pf-nav-item <?= isActive('/sprints') ?>">
        <i class="bi bi-arrow-repeat"></i> Sprints & Backlog
      </a>

      <div class="pf-nav-section"><span class="pf-nav-label">Enterprise Control</span></div>
      <a href="<?= url('gantt') ?>" class="pf-nav-item <?= isActive('/gantt') ?>">
        <i class="bi bi-diagram-2"></i> Gantt & Timeline
      </a>
      <a href="<?= url('gantt/matrix') ?>" class="pf-nav-item <?= isActive('/gantt/matrix') ?>">
        <i class="bi bi-grid-3x3-gap"></i> Dependency Matrix
      </a>
      <a href="<?= url('timesheets') ?>" class="pf-nav-item <?= isActive('/timesheets') ?>">
        <i class="bi bi-clock-history"></i> Weekly Timesheets
      </a>
      <a href="<?= url('governance/gates') ?>" class="pf-nav-item <?= isActive('/governance/gates') ?>">
        <i class="bi bi-shield-check"></i> Quality Gates (iQMS)
      </a>
      <a href="<?= url('capacity') ?>" class="pf-nav-item <?= isActive('/capacity') ?>">
        <i class="bi bi-person-badge"></i> RACI & Capacity
      </a>
      <a href="<?= url('evm') ?>" class="pf-nav-item <?= isActive('/evm') ?>">
        <i class="bi bi-graph-up-arrow"></i> EVM Financials
      </a>
      <a href="<?= url('financials/capex') ?>" class="pf-nav-item <?= isActive('/financials/capex') ?>">
        <i class="bi bi-cash-stack"></i> CAPEX vs OPEX
      </a>

      <div class="pf-nav-section"><span class="pf-nav-label">Work & Execution</span></div>
      <a href="<?= url('tasks') ?>" class="pf-nav-item <?= isActive('/tasks') ?>">
        <i class="bi bi-check2-square"></i> My Tasks & Stories
      </a>
      <a href="<?= url('milestones') ?>" class="pf-nav-item <?= isActive('/milestones') ?>">
        <i class="bi bi-flag"></i> Milestones
      </a>
      <a href="<?= url('risks') ?>" class="pf-nav-item <?= isActive('/risks') ?>">
        <i class="bi bi-shield-exclamation"></i> Risks & Issues (RIDA)
      </a>
      <a href="<?= url('risks/heatmap') ?>" class="pf-nav-item <?= isActive('/risks/heatmap') ?>">
        <i class="bi bi-grid-fill"></i> 5x5 Risk Heatmap
      </a>
      <a href="<?= url('updates') ?>" class="pf-nav-item <?= isActive('/updates') ?>">
        <i class="bi bi-activity"></i> Progress Log
      </a>

      <div class="pf-nav-section"><span class="pf-nav-label">Assets</span></div>
      <a href="<?= url('documents') ?>" class="pf-nav-item <?= isActive('/documents') ?>">
        <i class="bi bi-folder2-open"></i> Documents & Artifacts
      </a>

      <?php if (isPM()): ?>
      <div class="pf-nav-section"><span class="pf-nav-label">Team</span></div>
      <a href="<?= url('team') ?>" class="pf-nav-item <?= isActive('/team') ?>">
        <i class="bi bi-people"></i> Team
      </a>
      <?php endif; ?>

      <?php if (isOrgAdmin()): ?>
      <div class="pf-nav-section"><span class="pf-nav-label">Admin</span></div>
      <a href="<?= url('admin') ?>" class="pf-nav-item <?= isActive('/admin') ?>">
        <i class="bi bi-shield-lock"></i> Admin Panel
      </a>
      <?php endif; ?>

      <div class="pf-nav-section"><span class="pf-nav-label">Account</span></div>
      <a href="<?= url('search') ?>" class="pf-nav-item <?= isActive('/search') ?>">
        <i class="bi bi-search"></i> Search
      </a>
      <a href="<?= url('notifications') ?>" class="pf-nav-item <?= isActive('/notifications') ?>">
        <i class="bi bi-bell"></i> Notifications
        <?php $nc = unreadNotifCount(); if ($nc > 0): ?>
        <span class="pf-nav-badge"><?= $nc ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= url('profile') ?>" class="pf-nav-item <?= isActive('/profile') ?>">
        <i class="bi bi-person-circle"></i> Profile
      </a>

    </nav>

    <!-- Sidebar footer: org + user -->
    <div class="pf-sidebar-footer">
      <div class="d-flex align-items-center gap-2 mb-3">
        <?php $u = auth(); ?>
        <?= avatar($u['name'] ?? 'U', '', '32px') ?>
        <div style="min-width:0">
          <div class="fw-600 truncate" style="font-size:12.5px;color:var(--pf-text)"><?= e($u['name'] ?? '') ?></div>
          <div class="text-muted" style="font-size:11px"><?= e(ucfirst(str_replace('_',' ', $u['role'] ?? ''))) ?></div>
        </div>
      </div>
      <a href="<?= url('logout') ?>" class="pf-nav-item" style="padding:7px 12px;border-radius:var(--pf-radius-sm);background:var(--pf-surface2);font-size:12.5px;color:var(--pf-red)">
        <i class="bi bi-box-arrow-right"></i> Sign out
      </a>
    </div>
  </aside>

  <!-- ── Main ──────────────────────────────────────────────── -->
  <main class="pf-main">

    <!-- Topbar -->
    <header class="pf-topbar">
      <button id="sidebarToggle" style="display:none;border:none;background:none;font-size:20px;color:var(--pf-text-2);cursor:pointer;padding:4px">
        <i class="bi bi-list"></i>
      </button>

      <div class="pf-topbar-title">
        <?= e($pageTitle ?? 'Dashboard') ?>
        <?php if (!empty($pageSubtitle)): ?>
        <span class="pf-topbar-sub"><?= e($pageSubtitle) ?></span>
        <?php endif; ?>
      </div>

      <!-- Search -->
      <div class="pf-search">
        <i class="bi bi-search" style="color:var(--pf-text-3);font-size:13px"></i>
        <input type="text" id="globalSearch" placeholder="Search programs, tasks…" autocomplete="off">
        <kbd style="font-size:10px;color:var(--pf-text-3);background:var(--pf-border);padding:1px 5px;border-radius:3px">⌘K</kbd>
      </div>

      <!-- Theme toggle -->
      <button id="themeToggle" onclick="Theme.toggle()" style="border:none;background:var(--pf-surface2);color:var(--pf-text-2);width:34px;height:34px;border-radius:var(--pf-radius-sm);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">
        <i class="bi bi-moon-fill"></i>
      </button>

      <!-- Notifications bell -->
      <div class="pf-dropdown">
        <button data-dropdown style="border:none;background:var(--pf-surface2);color:var(--pf-text-2);width:34px;height:34px;border-radius:var(--pf-radius-sm);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;position:relative;flex-shrink:0">
          <i class="bi bi-bell"></i>
          <?php $nc = unreadNotifCount(); if ($nc > 0): ?>
          <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;background:var(--pf-red);border-radius:50%;border:2px solid var(--pf-surface)"></span>
          <?php endif; ?>
        </button>
        <div class="pf-dropdown-menu" style="width:300px;max-height:380px;overflow-y:auto">
          <?php
          $notifs = db()->fetchAll("SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 8", [authId()]);
          ?>
          <div style="padding:12px 16px;font-weight:700;font-size:13px;border-bottom:1px solid var(--pf-border);display:flex;justify-content:space-between;align-items:center">
            Notifications
            <?php if($notifs): ?>
            <a href="<?= url('notifications') ?>" style="font-size:11px;font-weight:500;color:var(--pf-indigo)">See all</a>
            <?php endif; ?>
          </div>
          <?php if (empty($notifs)): ?>
          <div style="padding:24px;text-align:center;color:var(--pf-text-3);font-size:13px">All caught up ✓</div>
          <?php else: foreach ($notifs as $n): ?>
          <a class="pf-dropdown-item" href="<?= e($n['link'] ?: url('notifications')) ?>" data-notif-id="<?= $n['id'] ?>">
            <i class="bi bi-dot" style="font-size:24px;color:var(--pf-indigo);margin:-4px -6px -4px -8px"></i>
            <div>
              <div style="font-weight:600;font-size:12.5px"><?= e($n['title']) ?></div>
              <div style="font-size:11.5px;color:var(--pf-text-3)"><?= timeAgo($n['created_at']) ?></div>
            </div>
          </a>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- User menu -->
      <div class="pf-dropdown">
        <?php $u = auth(); ?>
        <button data-dropdown style="border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:8px;padding:4px 8px;border-radius:var(--pf-radius-sm)">
          <?= avatar($u['name'] ?? 'U', '', '30px') ?>
          <span style="font-size:13px;font-weight:600;color:var(--pf-text)"><?= e(explode(' ', $u['name'] ?? 'User')[0]) ?></span>
          <i class="bi bi-chevron-down" style="font-size:10px;color:var(--pf-text-3)"></i>
        </button>
        <div class="pf-dropdown-menu">
          <div style="padding:12px 16px;border-bottom:1px solid var(--pf-border)">
            <div style="font-weight:700;font-size:13px"><?= e($u['name'] ?? '') ?></div>
            <div style="font-size:11.5px;color:var(--pf-text-3)"><?= e($u['email'] ?? '') ?></div>
          </div>
          <a class="pf-dropdown-item" href="<?= url('profile') ?>"><i class="bi bi-person"></i> Profile</a>
          <?php if (isOrgAdmin()): ?>
          <a class="pf-dropdown-item" href="<?= url('admin/settings') ?>"><i class="bi bi-gear"></i> Settings</a>
          <?php endif; ?>
          <div class="pf-dropdown-divider"></div>
          <a class="pf-dropdown-item danger" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a>
        </div>
      </div>
    </header>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="pf-flash-auto" style="margin:16px 32px 0;padding:0">
      <div class="pf-alert pf-alert-<?= e($flash['type']) ?>">
        <i class="bi bi-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?>-fill"></i>
        <span><?= e($flash['msg']) ?></span>
        <button onclick="this.parentElement.parentElement.remove()" style="border:none;background:none;cursor:pointer;color:inherit;margin-left:auto;font-size:16px">×</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <div class="pf-content fade-in">
      <?= $content ?? '' ?>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= url('assets/js/planford.js') ?>"></script>
<?= $extraJs ?? '' ?>

<style>
@media(max-width:768px){#sidebarToggle{display:flex!important}}
</style>
</body>
</html>

<?php
if (isLoggedIn()):
$pageTitle='Page Not Found'; ob_start(); ?>
<div style="text-align:center;padding:80px 20px">
  <div style="font-size:80px;font-weight:900;color:var(--pf-border);line-height:1">404</div>
  <div style="font-size:22px;font-weight:700;margin:16px 0 8px">Page not found</div>
  <div style="color:var(--pf-text-3);margin-bottom:24px">The page you're looking for doesn't exist.</div>
  <a href="<?= url('dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
</div>
<?php $content=ob_get_clean(); require VIEW_PATH.'layouts/app.php';
else: ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title><link href="<?= url('assets/css/planford.css') ?>" rel="stylesheet"></head>
<body><div class="pf-auth-shell"><div class="pf-auth-card" style="text-align:center">
<div style="font-size:60px;font-weight:900;color:rgba(255,255,255,.2)">404</div>
<div style="color:#fff;font-size:18px;font-weight:700;margin:12px 0">Page not found</div>
<a href="<?= url('login') ?>" class="pf-auth-btn" style="display:inline-block;padding:10px 24px;text-decoration:none;width:auto;margin-top:16px">Sign In</a>
</div></div></body></html>
<?php endif; ?>

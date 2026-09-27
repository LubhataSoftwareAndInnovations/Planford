<?php
if (isLoggedIn()) redirect('/dashboard');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $user = db()->fetchOne(
            "SELECT u.*, o.name as org_name, o.is_active as org_active
             FROM users u JOIN organizations o ON u.org_id=o.id
             WHERE u.email=? AND u.is_active=1",
            [$email]
        );

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['org_active']) {
                $error = 'Your organization account is inactive. Contact support.';
            } else {
                $_SESSION['pf_user'] = [
                    'id'       => $user['id'],
                    'org_id'   => $user['org_id'],
                    'name'     => $user['name'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                    'org_name' => $user['org_name'],
                ];
                db()->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id=?', [$user['id']]);
                audit('login', 'users', $user['id'], 'Login from ' . ($_SERVER['REMOTE_ADDR'] ?? ''), $user['id'], $user['org_id']);
                redirect('/dashboard');
            }
        } else {
            $error = 'Invalid email or password.';
            // Small delay to prevent brute force
            usleep(300000);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= url('assets/css/planford.css') ?>" rel="stylesheet">
<script>
// Keep dark on login page
document.documentElement.setAttribute('data-theme','dark');
</script>
</head>
<body>

<div class="pf-auth-shell">
  <div class="pf-auth-card fade-in">

    <!-- Logo -->
    <div class="pf-auth-logo">
      <div class="pf-auth-logo-icon"><i class="bi bi-hexagon-fill"></i></div>
      <div class="pf-auth-title"><?= APP_NAME ?></div>
      <div class="pf-auth-sub"><?= APP_TAGLINE ?></div>
    </div>

    <?php if ($error): ?>
    <div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 14px;color:#fca5a5;font-size:13px;display:flex;align-items:center;gap:8px;margin-bottom:20px">
      <i class="bi bi-exclamation-circle-fill"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">
      <?= csrfField() ?>

      <div style="margin-bottom:16px">
        <label class="pf-auth-label">Email Address</label>
        <input type="email" name="email" class="pf-auth-input" placeholder="you@company.com"
               value="<?= e($_POST['email'] ?? '') ?>" required autofocus autocomplete="email">
      </div>

      <div style="margin-bottom:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <label class="pf-auth-label" style="margin:0">Password</label>
        </div>
        <input type="password" name="password" class="pf-auth-input" placeholder="••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="pf-auth-btn">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
      </button>
    </form>

    <!-- Demo credentials -->
    <div style="margin-top:28px;padding:16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:10px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:rgba(255,255,255,.35);margin-bottom:10px">Demo Credentials</div>
      <?php
      $demos = [
        ['admin@planford.io', 'Admin@123', 'Super Admin', '#ef4444'],
        ['pm@planford.io',    'Admin@123', 'Program Manager', '#6366f1'],
        ['member@planford.io','Admin@123', 'Team Member', '#22c55e'],
      ];
      foreach ($demos as [$em, $pw, $role, $color]): ?>
      <button type="button" onclick="document.querySelector('[name=email]').value='<?= $em ?>';document.querySelector('[name=password]').value='<?= $pw ?>'"
        style="display:flex;align-items:center;gap:8px;width:100%;background:none;border:none;cursor:pointer;padding:4px 0;color:rgba(255,255,255,.65);font-size:12.5px;font-family:var(--pf-font)">
        <span style="width:8px;height:8px;border-radius:50%;background:<?= $color ?>;flex-shrink:0"></span>
        <span style="flex:1;text-align:left"><?= $role ?></span>
        <span style="font-family:monospace;font-size:11.5px;opacity:.5"><?= $em ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:20px;font-size:11.5px;color:rgba(255,255,255,.25)">
      <?= APP_NAME ?> v<?= APP_VERSION ?> · Open Source by <?= APP_COMPANY ?>
    </div>
  </div>
</div>

<script src="<?= url('assets/js/planford.js') ?>"></script>
</body>
</html>

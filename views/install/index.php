<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head><meta charset="UTF-8"><title>Install Planford</title>
<link href="../assets/css/planford.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head><body>
<div class="pf-auth-shell">
  <div class="pf-auth-card" style="max-width:540px">
    <div class="pf-auth-logo">
      <div class="pf-auth-logo-icon"><i class="bi bi-hexagon-fill"></i></div>
      <div class="pf-auth-title">Install Planford</div>
      <div class="pf-auth-sub">Set up your database and create the admin account</div>
    </div>
    <div id="result"></div>
    <div class="pf-form-group" style="margin-bottom:14px"><label class="pf-auth-label">DB Host</label><input type="text" id="db_host" class="pf-auth-input" value="localhost"></div>
    <div class="pf-form-group" style="margin-bottom:14px"><label class="pf-auth-label">DB Name</label><input type="text" id="db_name" class="pf-auth-input" value="planford"></div>
    <div class="pf-form-group" style="margin-bottom:14px"><label class="pf-auth-label">DB User</label><input type="text" id="db_user" class="pf-auth-input" value="root"></div>
    <div class="pf-form-group" style="margin-bottom:24px"><label class="pf-auth-label">DB Password</label><input type="password" id="db_pass" class="pf-auth-input"></div>
    <button onclick="runInstall()" class="pf-auth-btn"><i class="bi bi-arrow-right-circle"></i> Run Install</button>
    <p style="color:rgba(255,255,255,.3);font-size:11.5px;text-align:center;margin-top:16px">This will create all tables and seed demo data. Delete this file after setup.</p>
  </div>
</div>
<script>
async function runInstall() {
  document.getElementById('result').innerHTML='<p style="color:rgba(255,255,255,.5);text-align:center;padding:10px">Installing…</p>';
  const res = await fetch('?run=1', {method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({host:db_host.value,name:db_name.value,user:db_user.value,pass:db_pass.value})});
  const d = await res.json();
  document.getElementById('result').innerHTML = d.ok
    ? '<div style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);border-radius:8px;padding:12px;color:#86efac;margin-bottom:16px"><strong>✓ Installed!</strong> Login at <a href="../" style="color:#86efac">/planford/</a><br>admin@planford.io / Admin@123</div>'
    : '<div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:12px;color:#fca5a5;margin-bottom:16px">Error: '+d.error+'</div>';
}
</script>
</body></html>

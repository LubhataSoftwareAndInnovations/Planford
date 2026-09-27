<?php
requireAuth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) redirect('/dashboard','Invalid.','danger');
$body = trim($_POST['body'] ?? '');
$programId = (int)($_POST['program_id'] ?? 0);
if (!$body || !$programId) redirect('/dashboard','Update body required.','danger');
$prog = db()->fetchOne("SELECT id FROM programs WHERE id=? AND org_id=?", [$programId, authOrgId()]);
if (!$prog) redirect('/dashboard','Program not found.','danger');
db()->insert('updates', ['program_id'=>$programId,'user_id'=>authId(),'body'=>$body]);
audit('update','programs',$programId,'Progress update posted');
$ref = $_SERVER['HTTP_REFERER'] ?? url('programs/view?id='.$programId.'&tab=updates');
setFlash('Update posted.','success');
header('Location: '.$ref); exit;

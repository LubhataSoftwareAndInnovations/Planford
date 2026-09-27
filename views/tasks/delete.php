<?php
requireAuth();
if (!isPM()) redirect('/tasks','Permission denied.','danger');
if (!verifyCsrf()) redirect('/tasks','Invalid request.','danger');
$id = (int)($_POST['id'] ?? 0);
$task = db()->fetchOne("SELECT t.*,p.org_id FROM tasks t JOIN programs p ON t.program_id=p.id WHERE t.id=?",[$id]);
if (!$task || $task['org_id'] != authOrgId()) redirect('/tasks','Task not found.','danger');
db()->delete('tasks','id=?',[$id]);
audit('delete','tasks',$id,'Task deleted');
redirect('/programs/view?id='.$task['program_id'].'&tab=tasks','Task deleted.','success');

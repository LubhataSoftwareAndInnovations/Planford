<?php
requireAuth();
if (!isOrgAdmin()) redirect('/programs','Permission denied.','danger');
if (!verifyCsrf()) redirect('/programs','Invalid request.','danger');
$id = (int)($_GET['id'] ?? 0);
$prog = db()->fetchOne("SELECT * FROM programs WHERE id=? AND org_id=?",[$id,authOrgId()]);
if (!$prog) redirect('/programs','Program not found.','danger');
// Cascade
db()->query("DELETE FROM tasks WHERE program_id=?",[$id]);
db()->query("DELETE FROM milestones WHERE program_id=?",[$id]);
db()->query("DELETE FROM risks WHERE program_id=?",[$id]);
db()->query("DELETE FROM updates WHERE program_id=?",[$id]);
db()->query("DELETE FROM tracks WHERE program_id=?",[$id]);
db()->delete('programs','id=?',[$id]);
audit('delete','programs',$id,'Program deleted: '.$prog['name']);
redirect('/programs','Program deleted.','success');

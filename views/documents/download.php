<?php
requireAuth();
$id  = (int)($_GET['id'] ?? 0);
$doc = db()->fetchOne("SELECT d.* FROM documents d WHERE d.id=? AND d.org_id=?", [$id, authOrgId()]);
if (!$doc) { http_response_code(404); exit('Not found'); }
$path = UPLOAD_PATH . 'documents/' . $doc['filename'];
if (!file_exists($path)) { http_response_code(404); exit('File not found'); }
header('Content-Type: ' . ($doc['mime'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . $doc['origname'] . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;

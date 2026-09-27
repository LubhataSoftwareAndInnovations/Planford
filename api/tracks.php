<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/src/Models/DB.php';
require_once dirname(__DIR__) . '/src/Helpers/helpers.php';
requireAuth();
$programId = (int)($_GET['program_id'] ?? 0);
$prog = db()->fetchOne("SELECT id FROM programs WHERE id=? AND org_id=?", [$programId, authOrgId()]);
if (!$prog) jsonError('Not found');
$tracks = db()->fetchAll("SELECT id, name, color FROM tracks WHERE program_id=? AND is_active=1 ORDER BY order_index", [$programId]);
jsonSuccess(['tracks' => $tracks]);

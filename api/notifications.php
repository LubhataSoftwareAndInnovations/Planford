<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/src/Models/DB.php';
require_once dirname(__DIR__) . '/src/Helpers/helpers.php';
requireAuth();
$action = $_GET['action'] ?? (str_contains($_SERVER['REQUEST_URI'], '/read') ? 'read' : '');
if ($action === 'read') {
    $body = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($body['id'] ?? 0);
    if ($id) db()->update('notifications', ['is_read' => 1], 'id=? AND user_id=?', [$id, authId()]);
    else db()->query("UPDATE notifications SET is_read=1 WHERE user_id=?", [authId()]);
    jsonSuccess([], 'Marked as read');
}
jsonError('Unknown action');

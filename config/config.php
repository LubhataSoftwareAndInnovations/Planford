<?php
// ============================================================
// Planford v0.1 — by Lubhata
// Core Configuration
// ============================================================

// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'planford');
define('DB_PORT', 3306);

// App
define('APP_NAME',    'Planford');
define('APP_COMPANY', 'Lubhata');
define('APP_VERSION', '0.1.0');
define('APP_TAGLINE', 'Every program, under control.');

// Dynamic APP_URL auto-detection
$pf_proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$pf_host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
$pf_dir   = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '/planford-v0.1/planford';
$pf_dir   = rtrim($pf_dir, '/');
define('APP_URL', $pf_proto . '://' . $pf_host . ($pf_dir ?: ''));
define('APP_ENV',     'development'); // development | production

// Paths
define('ROOT_PATH',    dirname(__DIR__));
define('UPLOAD_PATH',  ROOT_PATH . '/uploads/');
define('UPLOAD_URL',   APP_URL . '/uploads/');
define('VIEW_PATH',    ROOT_PATH . '/views/');
define('SRC_PATH',     ROOT_PATH . '/src/');

// Upload limits
define('MAX_FILE_SIZE',  20 * 1024 * 1024); // 20MB
define('ALLOWED_TYPES',  ['pdf','doc','docx','xlsx','xls','pptx','ppt','png','jpg','jpeg','gif','mp4','zip','csv','txt','dwg','dxf']);

// Session
define('SESSION_NAME',     'planford_session');
define('SESSION_LIFETIME', 86400 * 7); // 7 days

// Pagination
define('PER_PAGE', 25);

// Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ORG_ADMIN',   'org_admin');
define('ROLE_PM',          'pm');
define('ROLE_MEMBER',      'member');
define('ROLE_STAKEHOLDER', 'stakeholder');
define('ROLE_VIEWER',      'viewer');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// PHP settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

<?php
// ============================================================
// Planford — Front Controller
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Models/DB.php';
require_once __DIR__ . '/src/Models/Sprint.php';
require_once __DIR__ . '/src/Services/EVMEngine.php';
require_once __DIR__ . '/src/Helpers/helpers.php';

// Route the request
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = parse_url(APP_URL, PHP_URL_PATH);
$path   = '/' . trim(substr($uri, strlen($base)), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Support query parameter fallback (e.g., index.php?route=/login) or PATH_INFO
if (isset($_GET['route'])) {
    $path = '/' . trim($_GET['route'], '/');
} elseif (!empty($_SERVER['PATH_INFO'])) {
    $path = '/' . trim($_SERVER['PATH_INFO'], '/');
}

// Normalize empty path
if ($path === '' || $path === '/index.php') $path = '/';

// ── Public routes (no auth) ─────────────────────────────────
$publicRoutes = [
    '/login'             => 'auth/login',
    '/logout'            => 'auth/logout',
    '/install'           => 'install/index',
    '/install/run'       => 'install/run',
];

// ── Stakeholder portal (token-based) ───────────────────────
if (str_starts_with($path, '/portal/')) {
    require __DIR__ . '/views/stakeholders/portal.php';
    exit;
}

// Public route check
foreach ($publicRoutes as $route => $view) {
    if ($path === $route) {
        require __DIR__ . '/views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        exit;
    }
}

// ── All other routes require auth ───────────────────────────
requireAuth();

// Route map: path => [view_file, roles_allowed (empty = all authed)]
$routes = [
    '/'                        => ['dashboard/index',     []],
    '/dashboard'               => ['dashboard/index',     []],
    '/agile/board'             => ['agile/board',         []],
    '/sprints'                 => ['sprints/index',       []],
    '/gantt'                   => ['gantt/index',         []],
    '/timesheets'              => ['timesheets/index',    []],
    '/governance/gates'        => ['governance/gates',    []],
    '/capacity'                => ['capacity/index',      []],
    '/evm'                     => ['evm/index',           []],
    '/portfolio/okrs'          => ['portfolio/okrs',      []],
    '/risks/heatmap'           => ['risks/heatmap',       []],
    '/financials/capex'        => ['financials/capex',    []],
    '/gantt/matrix'            => ['gantt/matrix',        []],
    '/admin/custom_fields'     => ['admin/custom_fields', ['super_admin','org_admin']],
    '/admin/automations'       => ['admin/automations',   ['super_admin','org_admin','pm']],
    '/programs'                => ['programs/index',      []],
    '/programs/new'            => ['programs/form',       ['super_admin','org_admin','pm']],
    '/programs/view'           => ['programs/view',       []],
    '/programs/edit'           => ['programs/form',       ['super_admin','org_admin','pm']],
    '/programs/delete'         => ['programs/delete',     ['super_admin','org_admin']],
    '/tasks'                   => ['tasks/index',         []],
    '/tasks/view'              => ['tasks/view',          []],
    '/tasks/new'               => ['tasks/form',          ['super_admin','org_admin','pm','member']],
    '/tasks/edit'              => ['tasks/form',          ['super_admin','org_admin','pm','member']],
    '/tasks/delete'            => ['tasks/delete',        ['super_admin','org_admin','pm']],
    '/milestones'              => ['milestones/index',    []],
    '/milestones/new'          => ['milestones/form',     ['super_admin','org_admin','pm']],
    '/milestones/edit'         => ['milestones/form',     ['super_admin','org_admin','pm']],
    '/documents'               => ['documents/index',     []],
    '/documents/upload'        => ['documents/upload',    ['super_admin','org_admin','pm','member']],
    '/documents/download'      => ['documents/download',  []],
    '/risks'                   => ['risks/index',         []],
    '/risks/new'               => ['risks/form',          ['super_admin','org_admin','pm']],
    '/risks/edit'              => ['risks/form',          ['super_admin','org_admin','pm']],
    '/updates'                 => ['updates/index',       []],
    '/updates/new'             => ['updates/form',        ['super_admin','org_admin','pm','member']],
    '/team'                    => ['team/index',          ['super_admin','org_admin','pm']],
    '/search'                  => ['search/index',        []],
    '/notifications'           => ['notifications/index', []],
    '/profile'                 => ['profile/index',       []],
    '/admin'                   => ['admin/index',         ['super_admin','org_admin']],
    '/admin/users'             => ['admin/users',         ['super_admin','org_admin']],
    '/admin/users/new'         => ['admin/user_form',     ['super_admin','org_admin']],
    '/admin/users/edit'        => ['admin/user_form',     ['super_admin','org_admin']],
    '/admin/audit'             => ['admin/audit',         ['super_admin','org_admin']],
    '/admin/settings'          => ['admin/settings',      ['super_admin','org_admin']],
];

// Match route
$matched = false;
foreach ($routes as $route => [$view, $roles]) {
    if ($path === $route || $path === $route . '/') {
        // Role check
        if (!empty($roles) && !in_array(authRole(), $roles)) {
            redirect('/dashboard', 'You do not have permission to access that page.', 'danger');
        }
        $matched = true;
        require VIEW_PATH . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        break;
    }
}

// 404
if (!$matched) {
    http_response_code(404);
    require VIEW_PATH . 'errors/404.php';
}

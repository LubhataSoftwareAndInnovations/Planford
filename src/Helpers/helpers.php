<?php

// ============================================================
// AUTH
// ============================================================

function auth(): ?array {
    return $_SESSION['pf_user'] ?? null;
}

function authId(): int {
    return (int)($_SESSION['pf_user']['id'] ?? 0);
}

function authRole(): string {
    return $_SESSION['pf_user']['role'] ?? ROLE_VIEWER;
}

function authOrgId(): int {
    return (int)($_SESSION['pf_user']['org_id'] ?? 0);
}

function isLoggedIn(): bool {
    return isset($_SESSION['pf_user']['id']);
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

function hasRole(string ...$roles): bool {
    return in_array(authRole(), $roles, true);
}

function isSuperAdmin(): bool  { return authRole() === ROLE_SUPER_ADMIN; }
function isOrgAdmin(): bool    { return in_array(authRole(), [ROLE_SUPER_ADMIN, ROLE_ORG_ADMIN]); }
function isPM(): bool          { return in_array(authRole(), [ROLE_SUPER_ADMIN, ROLE_ORG_ADMIN, ROLE_PM]); }
function isStakeholder(): bool { return authRole() === ROLE_STAKEHOLDER; }
function canEdit(): bool       { return isPM(); }

// ============================================================
// ROUTING / RESPONSE
// ============================================================

function redirect(string $path, string $flash = '', string $type = 'success'): never {
    if ($flash) setFlash($flash, $type);
    $base = rtrim(APP_URL, '/');
    header('Location: ' . $base . $path);
    exit;
}

function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['pf_flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash(): ?array {
    if (isset($_SESSION['pf_flash'])) {
        $f = $_SESSION['pf_flash'];
        unset($_SESSION['pf_flash']);
        return $f;
    }
    return null;
}

function currentPath(): string {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

function url(string $path = ''): string {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function isActive(string ...$paths): string {
    $current = currentPath();
    foreach ($paths as $p) {
        if (str_starts_with($current, rtrim(APP_URL, '/') . $p)) return 'active';
    }
    return '';
}

function json(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function jsonSuccess(mixed $data = [], string $msg = 'OK'): never {
    json(['success' => true, 'message' => $msg, 'data' => $data]);
}

function jsonError(string $msg, int $code = 400): never {
    json(['success' => false, 'message' => $msg], $code);
}

// ============================================================
// VIEWS
// ============================================================

function view(string $tpl, array $data = []): void {
    extract($data);
    $file = VIEW_PATH . str_replace('.', '/', $tpl) . '.php';
    if (!file_exists($file)) {
        throw new RuntimeException("View not found: $tpl");
    }
    require $file;
}

function partial(string $tpl, array $data = []): void {
    view('components.' . $tpl, $data);
}

// ============================================================
// UI HELPERS
// ============================================================

function e(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function statusBadge(string $status): string {
    $map = [
        'active'       => ['primary',  'Active'],
        'planning'     => ['info',     'Planning'],
        'on_track'     => ['success',  'On Track'],
        'at_risk'      => ['warning',  'At Risk'],
        'delayed'      => ['danger',   'Delayed'],
        'completed'    => ['success',  'Completed'],
        'cancelled'    => ['secondary','Cancelled'],
        'on_hold'      => ['warning',  'On Hold'],
        'not_started'  => ['secondary','Not Started'],
        'in_progress'  => ['primary',  'In Progress'],
        'blocked'      => ['danger',   'Blocked'],
        'review'       => ['info',     'In Review'],
        'approved'     => ['success',  'Approved'],
        'rejected'     => ['danger',   'Rejected'],
        'draft'        => ['secondary','Draft'],
        'open'         => ['primary',  'Open'],
        'resolved'     => ['success',  'Resolved'],
        'closed'       => ['secondary','Closed'],
    ];
    [$cls, $label] = $map[$status] ?? ['secondary', ucfirst(str_replace('_', ' ', $status))];
    return "<span class=\"badge badge-soft-$cls\">$label</span>";
}

function priorityBadge(string $p): string {
    $map = [
        'critical' => 'danger',
        'high'     => 'warning',
        'medium'   => 'info',
        'low'      => 'secondary',
    ];
    $cls = $map[$p] ?? 'secondary';
    return "<span class=\"badge badge-soft-$cls text-uppercase\" style=\"font-size:10px\">$p</span>";
}

function progressRing(float $pct, string $size = 'md'): string {
    $p   = min(100, max(0, round($pct)));
    $r   = $size === 'sm' ? 18 : ($size === 'lg' ? 36 : 26);
    $circ = 2 * pi() * $r;
    $dash = $circ * ($p / 100);
    $color = $p >= 100 ? '#22c55e' : ($p >= 60 ? '#6366f1' : ($p >= 30 ? '#f59e0b' : '#e11d48'));
    $sz   = $size === 'sm' ? 44 : ($size === 'lg' ? 88 : 64);
    $sz_h = $sz / 2;
    $fs   = $size === 'sm' ? '9px' : ($size === 'lg' ? '14px' : '11px');
    return <<<SVG
<svg width="$sz" height="$sz" viewBox="0 0 {$sz} {$sz}" class="progress-ring">
  <circle cx="{$sz_h}" cy="{$sz_h}" r="$r" fill="none" stroke="var(--pf-border)" stroke-width="3"/>
  <circle cx="{$sz_h}" cy="{$sz_h}" r="$r" fill="none" stroke="$color" stroke-width="3"
    stroke-dasharray="$dash {$circ}" stroke-dashoffset="0"
    transform="rotate(-90 {$sz_h} {$sz_h})" stroke-linecap="round"/>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="$color" font-size="$fs" font-weight="700">{$p}%</text>
</svg>
SVG;
}

function progressBar(float $pct, string $height = '6px'): string {
    $p = min(100, max(0, round($pct)));
    $color = $p >= 100 ? 'var(--pf-green)' : ($p >= 60 ? 'var(--pf-indigo)' : ($p >= 30 ? 'var(--pf-amber)' : 'var(--pf-red)'));
    return "<div class='pf-progress' style='height:$height'><div class='pf-progress-bar' style='width:{$p}%;background:$color'></div></div>";
}

function avatar(string $name, string $color = '', string $size = '36px'): string {
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', trim($name)), 0, 2)));
    if (!$color) {
        $colors = ['#6366f1','#8b5cf6','#ec4899','#06b6d4','#10b981','#f59e0b','#ef4444','#3b82f6'];
        $color  = $colors[crc32($name) % count($colors)];
    }
    return "<div class='pf-avatar' style='width:$size;height:$size;background:$color;font-size:calc($size * 0.4)'>{$initials}</div>";
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}

function fDate(?string $d, string $fmt = 'd M Y'): string {
    return $d ? date($fmt, strtotime($d)) : '—';
}

function daysLeft(?string $date, string $status = ''): string {
    if (!$date || in_array($status, ['completed','cancelled'])) return '—';
    $diff = (int)ceil((strtotime($date) - time()) / 86400);
    if ($diff < 0)  return "<span class='text-danger fw-semibold'>" . abs($diff) . "d overdue</span>";
    if ($diff === 0) return "<span class='text-warning fw-semibold'>Due today</span>";
    if ($diff <= 3) return "<span class='text-warning'>{$diff}d left</span>";
    return "<span class='text-muted'>{$diff}d left</span>";
}

function fileIcon(string $mime, string $ext = ''): string {
    if (str_contains($mime, 'pdf'))          return 'bi-file-earmark-pdf text-danger';
    if (str_contains($mime, 'word'))         return 'bi-file-earmark-word text-primary';
    if (str_contains($mime, 'sheet'))        return 'bi-file-earmark-excel text-success';
    if (str_contains($mime, 'presentation')) return 'bi-file-earmark-ppt text-warning';
    if (str_contains($mime, 'image'))        return 'bi-file-earmark-image text-info';
    if (str_contains($mime, 'video'))        return 'bi-file-earmark-play text-purple';
    if (str_contains($mime, 'zip'))          return 'bi-file-earmark-zip text-secondary';
    if (in_array($ext, ['dwg','dxf']))       return 'bi-file-earmark-ruled text-teal';
    return 'bi-file-earmark text-muted';
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024) . ' KB';
    return $bytes . ' B';
}

// ============================================================
// AUDIT
// ============================================================

function audit(string $action, string $entity, int $entityId, string $detail = '', ?int $userId = null, ?int $orgId = null): void {
    try {
        db()->insert('audit_logs', [
            'user_id'   => $userId ?? authId(),
            'org_id'    => $orgId  ?? authOrgId(),
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'detail'    => $detail,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
            'ua'        => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
        ]);
    } catch (Throwable) { /* never crash on audit */ }
}

// ============================================================
// NOTIFICATIONS
// ============================================================

function notify(int $userId, string $title, string $body, string $type = 'info', string $link = ''): void {
    try {
        db()->insert('notifications', [
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'link'    => $link,
            'is_read' => 0,
        ]);
    } catch (Throwable) {}
}

function unreadNotifCount(): int {
    if (!isLoggedIn()) return 0;
    return (int)db()->fetchColumn(
        "SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0", [authId()]
    );
}

// ============================================================
// FILE UPLOAD
// ============================================================

function uploadFile(array $file, string $subdir = 'attachments'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload error: ' . $file['error']];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_TYPES)) {
        return ['ok' => false, 'error' => "File type .$ext not allowed"];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['ok' => false, 'error' => 'File exceeds 20MB limit'];
    }
    $dir  = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $name = date('Ymd_His') . '_' . uniqid() . '_' . $safe;
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return ['ok' => false, 'error' => 'Failed to save file'];
    }
    return [
        'ok'       => true,
        'filename' => $name,
        'origname' => $file['name'],
        'size'     => $file['size'],
        'mime'     => mime_content_type($dir . $name),
        'ext'      => $ext,
        'url'      => UPLOAD_URL . $subdir . '/' . $name,
    ];
}

// ============================================================
// MISC
// ============================================================

function slug(string $text): string {
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
}

function truncate(string $text, int $len = 80): string {
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}

function colorForString(string $s): string {
    $colors = ['#6366f1','#8b5cf6','#ec4899','#06b6d4','#10b981','#f59e0b','#ef4444','#3b82f6','#14b8a6','#f97316'];
    return $colors[abs(crc32($s)) % count($colors)];
}

function csrf(): string {
    if (empty($_SESSION['pf_csrf'])) {
        $_SESSION['pf_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['pf_csrf'];
}

function verifyCsrf(): bool {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['pf_csrf'] ?? '', $token);
}

function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf() . '">';
}

function pagination(int $total, int $page, int $perPage, string $baseUrl): string {
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) return '';
    $html = '<nav><ul class="pf-pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $page ? ' active' : '';
        $sep    = str_contains($baseUrl, '?') ? '&' : '?';
        $html  .= "<li class='pf-page-item{$active}'><a class='pf-page-link' href='{$baseUrl}{$sep}page={$i}'>{$i}</a></li>";
    }
    return $html . '</ul></nav>';
}

// Enterprise MNC Badges & Formatters
function ragBadge(string $rag): string {
    $map = [
        'green' => ['success', 'ON TRACK (GREEN)'],
        'amber' => ['warning', 'AT RISK (AMBER)'],
        'red'   => ['danger',  'ESCALATED (RED)'],
    ];
    [$cls, $label] = $map[strtolower($rag)] ?? ['secondary', strtoupper($rag)];
    return "<span class=\"badge badge-soft-$cls fw-bold\"><i class=\"bi bi-circle-fill me-1\" style=\"font-size:8px\"></i>$label</span>";
}

function storyPointBadge(int $pts): string {
    return "<span class=\"badge bg-indigo-soft text-indigo fw-bold\" style=\"font-size:11px\">{$pts} SP</span>";
}

function taskTypeBadge(string $type): string {
    $map = [
        'epic'  => ['purple', 'bi-lightning-charge-fill', 'EPIC'],
        'story' => ['primary', 'bi-bookmark-star-fill', 'STORY'],
        'task'  => ['info', 'bi-check2-circle', 'TASK'],
        'bug'   => ['danger', 'bi-bug-fill', 'BUG'],
        'spike' => ['warning', 'bi-search', 'SPIKE'],
    ];
    [$cls, $icon, $label] = $map[strtolower($type)] ?? ['secondary', 'bi-circle', strtoupper($type)];
    return "<span class=\"badge badge-soft-$cls\" style=\"font-size:10px\"><i class=\"bi $icon me-1\"></i>$label</span>";
}

function raciBadge(string $role): string {
    $map = [
        'R' => ['danger', 'RESPONSIBLE (R)'],
        'A' => ['primary', 'ACCOUNTABLE (A)'],
        'C' => ['warning', 'CONSULTED (C)'],
        'I' => ['info', 'INFORMED (I)'],
    ];
    [$cls, $label] = $map[strtoupper($role)] ?? ['secondary', $role];
    return "<span class=\"badge badge-soft-$cls fw-bold\">$label</span>";
}

function methodologyBadge(string $m): string {
    $map = [
        'scrum'     => ['primary', 'bi-arrow-repeat', 'Agile Scrum'],
        'kanban'    => ['info',    'bi-kanban', 'Kanban Flow'],
        'waterfall' => ['warning', 'bi-distribute-vertical', 'Waterfall / V-Model'],
        'hybrid'    => ['purple',  'bi-diagram-3', 'Hybrid Delivery'],
    ];
    [$cls, $icon, $label] = $map[strtolower($m)] ?? ['secondary', 'bi-gear', ucfirst($m)];
    return "<span class=\"badge badge-soft-$cls fw-semibold\"><i class=\"bi $icon me-1\"></i>$label</span>";
}


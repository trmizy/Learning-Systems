<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void {
    if (!isset($_SESSION['auth'])) {
        header('Location: /public/index.php');
        exit;
    }
}

function require_role(array $allowed): void {
    require_login();
    $current = $_SESSION['auth']['role'] ?? null;
    
    if (!in_array($current, $allowed, true)) {
        http_response_code(403);
        include __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

function current_user(): ?array {
    return $_SESSION['auth'] ?? null;
}

function has_role(string $role): bool {
    return isset($_SESSION['auth']['roles']) && 
           in_array($role, $_SESSION['auth']['roles'], true);
}

function switch_role(string $role): bool {
    if (has_role($role)) {
        $_SESSION['auth']['role'] = $role;
        session_regenerate_id(true);
        return true;
    }
    return false;
}

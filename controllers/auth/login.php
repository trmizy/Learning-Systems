<?php
session_start();
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/auth.php';

// Check if already logged in
if (isset($_SESSION['auth'])) {
    $_SESSION['flash_info'] = sprintf(
        'Bạn đã đăng nhập với tài khoản %s (Vai trò: %s)',
        $_SESSION['auth']['full_name'],
        getRoleName($_SESSION['auth']['role'])
    );
    header('Location: /public/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $auth = new Auth();
    $user = $auth->login($username, $password);
    
    if ($user) {
        $_SESSION['auth'] = $user;
        $_SESSION['flash_success'] = 'Đăng nhập thành công với vai trò ' . getRoleName($user['role']);
        header('Location: /public/index.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác';
        header('Location: /public/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../views/auth/login.php';

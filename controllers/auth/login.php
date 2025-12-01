<?php
// File: controllers/auth/login.php
session_start();
require_once __DIR__ . '/../../models/auth.php';

// Chỉ xử lý nếu là POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // === 🚀 SỬA LỖI Ở ĐÂY ===
    // Thử lấy 'username', nếu không có thì lấy 'email' (đề phòng view đặt tên khác)
    $usernameInput = $_POST['username'] ?? $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    // ========================

    // Kiểm tra dữ liệu nhập vào
    if (empty($usernameInput) || empty($passwordInput)) {
        $_SESSION['flash_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
        header('Location: /public/index.php');
        exit;
    }

    $auth = new Auth();
    // Gọi hàm login trong Model
    $user = $auth->login($usernameInput, $passwordInput);

    if ($user) {
        // Đăng nhập thành công
        $_SESSION['auth'] = $user;
        
        // Chuyển hướng dựa trên vai trò (Tuỳ chọn, hiện tại về trang chủ)
        header('Location: /public/index.php');
        exit;
    } else {
        // Đăng nhập thất bại
        $_SESSION['flash_error'] = "Tên đăng nhập hoặc mật khẩu không chính xác.";
        header('Location: /public/index.php'); // Quay lại login
        exit;
    }
}

// Nếu không phải POST, hiển thị form login
// (Đảm bảo đường dẫn này đúng với cấu trúc của bạn)
require_once __DIR__ . '/../../views/auth/login.php'; 
?>
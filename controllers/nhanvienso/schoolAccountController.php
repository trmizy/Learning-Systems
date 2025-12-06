<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/nhanvienso/SchoolAccountModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

// Kiểm tra đăng nhập và quyền nhân viên sở
require_role(['nhanvienso']);

// Khởi tạo session messages nếu chưa có
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

$schoolAccountModel = new SchoolAccountModel();
$currentUser = current_user();

// Lấy mã nhân viên sở từ session
$maNhanVienSo = null;
if (isset($currentUser['username'])) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT nvs.maNhanVienSo 
        FROM taikhoan tk
        INNER JOIN nhanvienso nvs ON tk.maTaiKhoan = nvs.maTaiKhoan
        WHERE tk.tenDangNhap = :username
        LIMIT 1
    ");
    $stmt->execute(['username' => $currentUser['username']]);
    $result = $stmt->fetch();
    
    if ($result) {
        $maNhanVienSo = $result['maNhanVienSo'];
    }
}

if (!$maNhanVienSo) {
    die('Không tìm thấy thông tin nhân viên sở trong hệ thống. Vui lòng liên hệ quản trị viên.');
}

// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Thêm trường mới vào hệ thống
    if ($_POST['action'] === 'add_school') {
        $data = [
            'tenTruong' => trim($_POST['tenTruong'] ?? ''),
            'diaChi' => trim($_POST['diaChi'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'soDienThoai' => trim($_POST['soDienThoai'] ?? '')
        ];
        
        $result = $schoolAccountModel->themTruongMoi($data);
        
        if ($result['success']) {
            $_SESSION['messages'][] = [
                'type' => 'success',
                'text' => $result['message'] . ' (Mã trường: ' . $result['data']['maTruong'] . ')'
            ];
        } else {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => $result['message']
            ];
        }
        
        header("Location: /public/index.php?action=schoolAccount_nhanvienso");
        exit;
    }
    
    // Tạo tài khoản cho trường có sẵn
    if ($_POST['action'] === 'create_account') {
        $maTruong = $_POST['maTruong'] ?? '';
        
        if (empty($maTruong)) {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => 'Vui lòng chọn trường cần cấp tài khoản'
            ];
            header("Location: /public/index.php?action=schoolAccount_nhanvienso");
            exit;
        }
        
        // Tạo tài khoản
        $result = $schoolAccountModel->taoTaiKhoanChoTruong($maTruong, $maNhanVienSo);
        
        if ($result['success']) {
            // Gửi email
            $emailSent = $schoolAccountModel->guiEmailThongTinTaiKhoan($result['data']);
            
            if ($emailSent) {
                $_SESSION['messages'][] = [
                    'type' => 'success',
                    'text' => 'Tạo tài khoản thành công! Email đã được gửi đến ' . $result['data']['email']
                ];
            } else {
                $_SESSION['messages'][] = [
                    'type' => 'warning',
                    'text' => 'Tạo tài khoản thành công nhưng không thể gửi email. Vui lòng thông báo trực tiếp cho trường.'
                ];
            }
            
            // Lưu thông tin tài khoản để hiển thị
            $_SESSION['new_account_info'] = $result['data'];
        } else {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => $result['message']
            ];
        }
        
        header("Location: /public/index.php?action=schoolAccount_nhanvienso");
        exit;
    }
    
    if ($_POST['action'] === 'delete_account') {
        $maTruong = $_POST['maTruong'] ?? '';
        
        if (empty($maTruong)) {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => 'Vui lòng chọn trường cần xóa tài khoản'
            ];
            header("Location: /public/index.php?action=schoolAccount_nhanvienso");
            exit;
        }
        
        // Xóa tài khoản
        $result = $schoolAccountModel->xoaTaiKhoanTruong($maTruong);
        
        if ($result['success']) {
            $_SESSION['messages'][] = [
                'type' => 'success',
                'text' => 'Xóa tài khoản thành công'
            ];
        } else {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => $result['message']
            ];
        }
        
        header("Location: /public/index.php?action=schoolAccount_nhanvienso");
        exit;
    }
}

// Lấy danh sách trường
$danhSachTruong = $schoolAccountModel->getDanhSachTruongVaTrangThaiTaiKhoan();

// Lấy thông tin tài khoản mới tạo (nếu có)
$newAccountInfo = null;
if (isset($_SESSION['new_account_info'])) {
    $newAccountInfo = $_SESSION['new_account_info'];
    unset($_SESSION['new_account_info']);
}

// Load view
$pageTitle = "Cấp tài khoản cho trường";
require_once __DIR__ . '/../../views/nhanvienso/schoolAccountView.php';

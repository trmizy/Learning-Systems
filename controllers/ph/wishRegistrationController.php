<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/ph/WishRegistrationModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

// Kiểm tra đăng nhập và quyền phụ huynh
require_role(['ph']); // ph = phụ huynh

// Khởi tạo session messages
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

$wishModel = new WishRegistrationModel();
$currentUser = current_user();

// --- Pre-check: nếu phụ huynh đã liên kết với học sinh thì khi truy cập trang đăng ký sẽ hiện thông báo và redirect về dashboard
try {
    $parentId = $currentUser['parent_id'] ?? null;
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Nếu lookup theo email đăng nhập
    if (empty($parentId) && !empty($currentUser['email'])) {
        $stmtEmail = $conn->prepare("SELECT maPH FROM PhuHuynh WHERE email = ? LIMIT 1");
        $stmtEmail->execute([$currentUser['email']]);
        $rEmail = $stmtEmail->fetch();
        if ($rEmail && !empty($rEmail['maPH'])) {
            $parentId = $rEmail['maPH'];
        }
    }

    if (!empty($parentId)) {
        $stmt = $conn->prepare("SELECT 1 FROM phuhuynh_hocsinh WHERE maPH = :maPH LIMIT 1");
        $stmt->execute(['maPH' => $parentId]);
        $hasStudent = (bool)$stmt->fetchColumn();
        if ($hasStudent) {
            // Đặt thông báo theo yêu cầu
            $_SESSION['messages'][] = [
                'type' => 'info',
                'text' => 'Con em đang học thpt. Phụ huynh không cần đăng ký nguyện vọng nữa.'
            ];
            header('Location: /views/ph/dashboard.php');
            exit;
        }
    }
} catch (PDOException $e) {
    error_log('wishRegistrationController pre-check error: ' . $e->getMessage());
    // Nếu lỗi DB thì tiếp tục như bình thường (không chặn)
}

// Xử lý POST request - Đăng ký nguyện vọng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'register_wish') {
        // Lấy thông tin thí sinh
        $dataThiSinh = [
            'hoTen' => trim($_POST['hoTen'] ?? ''),
            'soCCCD' => trim($_POST['soCCCD'] ?? ''),
            'soDienThoai' => trim($_POST['soDienThoai'] ?? '')
        ];

        // Lấy danh sách nguyện vọng từ form
        $danhSachNguyenVong = [];
        if (isset($_POST['nguyenVong']) && is_array($_POST['nguyenVong'])) {
            foreach ($_POST['nguyenVong'] as $nv) {
                if (!empty($nv['maTruong']) && !empty($nv['thuTuUuTien'])) {
                    $danhSachNguyenVong[] = [
                        'maTruong' => $nv['maTruong'],
                        'thuTuUuTien' => intval($nv['thuTuUuTien'])
                    ];
                }
            }
        }

        // Đăng ký nguyện vọng
        $result = $wishModel->dangKyNguyenVong($dataThiSinh, $danhSachNguyenVong);

        if ($result['success']) {
            $_SESSION['messages'][] = [
                'type' => 'success',
                'text' => $result['message']
            ];
            $_SESSION['registration_success'] = $result['data'];
        } else {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => $result['message']
            ];
        }

        header("Location: /public/index.php?action=wishRegistration_ph");
        exit;
    }
}

// Lấy danh sách trường
$danhSachTruong = $wishModel->getDanhSachTruong();

// Lấy thông tin đăng ký thành công (nếu có) cho view
$successData = null;
if (isset($_SESSION['registration_success'])) {
    $successData = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Get messages
$messages = $_SESSION['messages'] ?? [];
unset($_SESSION['messages']);

// Load view
$pageTitle = "Đăng ký nguyện vọng";
require_once __DIR__ . '/../../views/ph/wishRegistrationView.php';

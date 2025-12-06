<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/bgh/StatisticsModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

// Kiểm tra đăng nhập và quyền ban giám hiệu
require_role(['bgh']);

// Khởi tạo model
$statisticsModel = new StatisticsModel();

// Khởi tạo session messages
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// Lấy dữ liệu cho bộ lọc
$danhSachNamHoc = $statisticsModel->getDanhSachNamHoc();
$danhSachHocKy = $statisticsModel->getDanhSachHocKy();
$danhSachKhoi = $statisticsModel->getDanhSachKhoi();
$danhSachLop = $statisticsModel->getDanhSachLop();

// Lấy năm học và học kỳ gần nhất
$macDinh = $statisticsModel->getNamHocHocKyGanNhat();

// Xử lý request
$thongKeData = null;
$filters = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'view_statistics') {
    // Lấy filters từ POST
    $filters = [
        'namHoc' => $_POST['namHoc'] ?? '',
        'hocKy' => $_POST['hocKy'] ?? '',
        'khoi' => $_POST['khoi'] ?? '',
        'maLop' => $_POST['maLop'] ?? ''
    ];

    // Thực hiện thống kê
    $result = $statisticsModel->thongKeDiemHanhKiem($filters);

    if ($result['success']) {
        $thongKeData = $result['data'];
    } else {
        $_SESSION['messages'][] = [
            'type' => 'warning',
            'text' => $result['message']
        ];
    }
}

// Load view
require_once __DIR__ . '/../../views/bgh/statisticsView.php';

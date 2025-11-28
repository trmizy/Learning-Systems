<?php
/**
 * Controller: Xem điểm (Học sinh)
 * Path: controllers/hs/XemDiemController.php
 * Xử lý logic nghiệp vụ và gọi View
 */

require_once __DIR__ . '/../../models/DiemModel.php';

// Start session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'hs') {
    header('Location: /public/index.php');
    exit;
}

// Lấy thông tin user hiện tại
$user = $_SESSION['auth'];

// Khởi tạo model
$diemModel = new DiemModel();

// Lấy mã học sinh từ username/email
$maHS = null;
if ($user && isset($user['username'])) {
    $maHS = $diemModel->getMaHocSinhByUsername($user['username']);
}

// Nếu không tìm thấy, dùng mã demo
if (!$maHS) {
    $maHS = 'HS0000'; // Mã demo để test
}

// Lấy danh sách năm học
$danhSachNamHoc = $diemModel->getAllNamHoc();

// Lấy tham số lọc từ URL (mặc định là năm học và học kỳ hiện tại)
$namHocHienTai = date('Y') . '-' . (date('Y') + 1); // VD: 2024-2025
$hocKyHienTai = (date('n') >= 1 && date('n') <= 5) ? 'HK2' : 'HK1';

$namHoc = isset($_GET['namHoc']) ? $_GET['namHoc'] : $namHocHienTai;
$hocKy = isset($_GET['hocKy']) ? $_GET['hocKy'] : $hocKyHienTai;

// Validate học kỳ
$validHocKy = ['HK1', 'HK2', 'Cả năm'];
if (!in_array($hocKy, $validHocKy)) {
    $hocKy = $hocKyHienTai;
}

// Lấy thông tin học sinh
$thongTinHS = $diemModel->getThongTinHocSinh($maHS);

// Lấy điểm đầy đủ (bao gồm môn chưa có điểm)
$danhSachDiem = $diemModel->getDiemHocSinhDayDu($maHS, $namHoc, $hocKy);

// Tính điểm trung bình chung
$diemTBC = $diemModel->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);

// Gọi View để hiển thị
require_once __DIR__ . '/../../views/hs/xem_diem.php';

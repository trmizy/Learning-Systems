<?php
/**
 * Controller: Xem điểm (Học sinh)
 * Path: controllers/hs/diem_controller.php
 */

require_once __DIR__ . '/../../models/DiemModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

// Xác thực bắt buộc
require_role(['hs']);

// Lấy thông tin user hiện tại
$user = current_user();

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

// Return data cho view
return [
    'thongTinHS' => $thongTinHS,
    'danhSachDiem' => $danhSachDiem,
    'danhSachNamHoc' => $danhSachNamHoc,
    'namHoc' => $namHoc,
    'hocKy' => $hocKy,
    'diemTBC' => $diemTBC,
    'validHocKy' => $validHocKy
];

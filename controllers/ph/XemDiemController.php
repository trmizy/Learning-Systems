<?php
/**
 * Controller: Xem điểm con (Phụ huynh)
 * Path: controllers/ph/XemDiemController.php
 * Xử lý logic nghiệp vụ và gọi View
 */

require_once __DIR__ . '/../../models/DiemModel.php';

// Start session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'ph') {
    header('Location: /public/index.php');
    exit;
}

// Lấy thông tin user hiện tại
$user = $_SESSION['auth'];

// Khởi tạo model
$diemModel = new DiemModel();

// Lấy mã phụ huynh từ username/email
$maPH = null;
if ($user && isset($user['username'])) {
    $maPH = $diemModel->getMaPhuHuynhByUsername($user['username']);
}

// Nếu không tìm thấy, dùng mã demo
if (!$maPH) {
    $maPH = 'PH0001'; // Mã demo để test
}

// Lấy danh sách con
$danhSachCon = $diemModel->getDanhSachConCuaPhuHuynh($maPH);

// Lấy mã học sinh được chọn (mặc định là con đầu tiên)
$maHS = isset($_GET['maHS']) ? $_GET['maHS'] : null;

// Nếu chưa chọn, lấy con đầu tiên
if (!$maHS) {
    $danhSachCon->execute(); // Reset pointer
    $conDauTien = $danhSachCon->fetch();
    if ($conDauTien) {
        $maHS = $conDauTien['maHS'];
    }
    $danhSachCon->execute(); // Reset lại để dùng trong view
}

// Kiểm tra quyền: Phụ huynh chỉ xem điểm của con mình
if ($maHS && !$diemModel->kiemTraQuyen($maPH, $maHS)) {
    die('Bạn không có quyền xem điểm của học sinh này.');
}

// Lấy danh sách năm học
$danhSachNamHoc = $diemModel->getAllNamHoc();

// Lấy tham số lọc
$namHocHienTai = date('Y') . '-' . (date('Y') + 1);
$hocKyHienTai = (date('n') >= 1 && date('n') <= 5) ? 'HK2' : 'HK1';

$namHoc = isset($_GET['namHoc']) ? $_GET['namHoc'] : $namHocHienTai;
$hocKy = isset($_GET['hocKy']) ? $_GET['hocKy'] : $hocKyHienTai;

// Validate học kỳ
$validHocKy = ['HK1', 'HK2', 'Cả năm'];
if (!in_array($hocKy, $validHocKy)) {
    $hocKy = $hocKyHienTai;
}

// Lấy thông tin học sinh
$thongTinHS = null;
$danhSachDiem = null;
$diemTBC = null;

if ($maHS) {
    $thongTinHS = $diemModel->getThongTinHocSinh($maHS);
    $danhSachDiem = $diemModel->getDiemHocSinhDayDu($maHS, $namHoc, $hocKy);
    $diemTBC = $diemModel->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);
}

// Gọi View để hiển thị
require_once __DIR__ . '/../../views/ph/xem_diem.php';

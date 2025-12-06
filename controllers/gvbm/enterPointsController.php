<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/gvbm/enterPointsModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

// Kiểm tra đăng nhập và quyền giáo viên bộ môn
require_role(['gvbm']);

// Khởi tạo session messages nếu chưa có
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

$diemModel = new EnterPointsModel();
$currentUser = current_user();

// Lấy mã giáo viên từ database dựa vào username
$maGV = null;
if (isset($currentUser['username'])) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Tìm mã giáo viên từ bảng taikhoan và giaovienbomon
    $stmt = $conn->prepare("
        SELECT gv.maGV 
        FROM taikhoan tk
        INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
        WHERE tk.tenDangNhap = :username
        LIMIT 1
    ");
    $stmt->execute(['username' => $currentUser['username']]);
    $result = $stmt->fetch();
    
    if ($result) {
        $maGV = $result['maGV'];
    }
}

if (!$maGV) {
    die('Không tìm thấy thông tin giáo viên trong hệ thống. Vui lòng liên hệ quản trị viên.');
}

// Lấy năm học và học kỳ hiện tại
$currentInfo = $diemModel->getNamHocHocKyHienTai();
$namHoc = $_GET['namHoc'] ?? $currentInfo['namHoc'];
$hocKy = $_GET['hocKy'] ?? $currentInfo['hocKy'];

// Lấy danh sách lớp được phân công
$danhSachLop = $diemModel->getDanhSachLopPhanCong($maGV, $namHoc, $hocKy);

// Lấy mã lớp và mã môn học được chọn (nếu có)
$selectedMaLop = $_GET['maLop'] ?? null;
$selectedMaMonHoc = $_GET['maMonHoc'] ?? null;

$bangDiem = [];
$selectedLopInfo = null;

if ($selectedMaLop && $selectedMaMonHoc) {
    // Lấy bảng điểm
    $bangDiem = $diemModel->getBangDiem($selectedMaLop, $selectedMaMonHoc, $maGV, $namHoc, $hocKy);
    
    // Lấy thông tin lớp đã chọn
    foreach ($danhSachLop as $lop) {
        if ($lop['maLop'] == $selectedMaLop && $lop['maMonHoc'] == $selectedMaMonHoc) {
            $selectedLopInfo = $lop;
            break;
        }
    }
}

// Xử lý POST request để lưu điểm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grades'])) {
    $maLop = $_POST['maLop'];
    $maMonHoc = $_POST['maMonHoc'];
    $namHocPost = $_POST['namHoc'];
    $hocKyPost = $_POST['hocKy'];
    
    $danhSachDiem = [];
    $diemThuongXuyen = $_POST['diemThuongXuyen'] ?? [];
    $diemGiuaKy = $_POST['diemGiuaKy'] ?? [];
    $diemCuoiKy = $_POST['diemCuoiKy'] ?? [];
    
    // Lấy danh sách học sinh từ form
    $danhSachHS = array_unique(array_merge(
        array_keys($diemThuongXuyen),
        array_keys($diemGiuaKy),
        array_keys($diemCuoiKy)
    ));
    
    foreach ($danhSachHS as $maHS) {
        $tx = !empty($diemThuongXuyen[$maHS]) ? floatval($diemThuongXuyen[$maHS]) : null;
        $gk = !empty($diemGiuaKy[$maHS]) ? floatval($diemGiuaKy[$maHS]) : null;
        $ck = !empty($diemCuoiKy[$maHS]) ? floatval($diemCuoiKy[$maHS]) : null;
        
        // Chỉ lưu nếu có ít nhất một điểm
        if ($tx !== null || $gk !== null || $ck !== null) {
            $danhSachDiem[] = [
                'maHS' => $maHS,
                'maMonHoc' => $maMonHoc,
                'maGV' => $maGV,
                'namHoc' => $namHocPost,
                'hocKy' => $hocKyPost,
                'diemThuongXuyen' => $tx,
                'diemGiuaKy' => $gk,
                'diemCuoiKy' => $ck
            ];
        }
    }
    
    if (!empty($danhSachDiem)) {
        $result = $diemModel->luuNhieuDiem($danhSachDiem);
        
        if ($result['success']) {
            $_SESSION['messages'][] = [
                'type' => 'success',
                'text' => 'Lưu điểm thành công!'
            ];
        } else {
            $_SESSION['messages'][] = [
                'type' => 'danger',
                'text' => 'Lỗi: ' . ($result['message'] ?? 'Không thể lưu điểm')
            ];
        }
    } else {
        $_SESSION['messages'][] = [
            'type' => 'warning',
            'text' => 'Không có điểm nào để lưu'
        ];
    }
    
    // Redirect để tránh resubmit
    header("Location: /public/index.php?action=enterPoints_gvbm&maLop=$maLop&maMonHoc=$maMonHoc&namHoc=$namHocPost&hocKy=$hocKyPost");
    exit;
}

// Load view
$pageTitle = "Nhập điểm";
require_once __DIR__ . '/../../views/gvbm/enterPointsView.php';

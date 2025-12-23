<?php

require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../../models/bgh/GiaoVienModel.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'bgh') {
    $_SESSION['flash_error'] = 'Không có quyền truy cập';
    header('Location: /public/index.php');
    exit;
}

// Khởi tạo model
$giaoVienModel = new GiaoVienModel();

// Xử lý các hành động
$action = $_GET['sub_action'] ?? 'list';
$message = null;
$messageType = 'info';

// Lấy flash messages từ session
if (isset($_SESSION['flash_success'])) {
    $message = $_SESSION['flash_success'];
    $messageType = 'success';
    unset($_SESSION['flash_success']);
} elseif (isset($_SESSION['flash_error'])) {
    $message = $_SESSION['flash_error'];
    $messageType = 'danger';
    unset($_SESSION['flash_error']);
}

try {
    switch ($action) {
        case 'list':
        default:
            // Xử lý tìm kiếm
            $keyword = $_GET['search'] ?? '';
            if (!empty($keyword)) {
                $danhSachGiaoVien = $giaoVienModel->timKiemGiaoVien($keyword);
            } else {
                $danhSachGiaoVien = $giaoVienModel->getAllGiaoVien();
            }
            require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php';
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Xử lý thêm mới
                $maTruong = strtoupper(trim($_POST['maTruong'] ?? 'TR001'));
                $namVao = (int)($_POST['namVao'] ?? date('Y'));
                
                // Validate mã trường
                if (!preg_match('/^[A-Z0-9]{3,10}$/', $maTruong)) {
                    throw new Exception("Mã trường không hợp lệ (3-10 ký tự viết hoa, số)");
                }
                
                // Tạo mã giáo viên tự động
                $maGV = $giaoVienModel->taoMaGiaoVien($maTruong, $namVao);
                
                $data = [
                    'maGV' => $maGV,
                    'hoTen' => trim($_POST['hoTen'] ?? ''),
                    'gioiTinh' => $_POST['gioiTinh'] ?? '',
                    'ngaySinh' => $_POST['ngaySinh'] ?? '',
                    'soCCCD' => $_POST['soCCCD'] ?? '', // ⚠️ PHẢI CÓ DÒNG NÀY
                    'soDienThoai' => trim($_POST['soDienThoai'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'diaChi' => trim($_POST['diaChi'] ?? ''),
                    'monHocPhuTrach' => trim($_POST['monHocPhuTrach'] ?? ''),
                    'trinhDoHocVan' => trim($_POST['trinhDoHocVan'] ?? ''),
                    'chucVu' => trim($_POST['chucVu'] ?? 'Giáo viên'),
                    'tinhTrangTaiKhoan' => $_POST['tinhTrangTaiKhoan'] ?? 'ACTIVE'
                ];

                // === DEBUG LOG TRƯỚC KHI GỌI MODEL ===
                error_log("=== Controller - Data truyền vào Model ===");
                error_log("soCCCD: '" . $data['soCCCD'] . "'");
                error_log("Full data: " . print_r($data, true));
                // === END DEBUG ===

                $giaoVienModel->themGiaoVien($data);
                $_SESSION['flash_success'] = "Thêm giáo viên thành công với mã: {$maGV}";
                header('Location: /public/index.php?page=bgh-quan-ly-giao-vien');
                exit;
            }
            
            // Hiển thị form tạo mới
            require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/tao_giao_vien.php';
            break;

        case 'edit':
            $maGV = $_GET['maGV'] ?? '';
            
            if (empty($maGV)) {
                throw new Exception('Không tìm thấy mã giáo viên');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Xử lý cập nhật
                $data = [
                    'hoTen' => trim($_POST['hoTen'] ?? ''),
                    'gioiTinh' => $_POST['gioiTinh'] ?? '',
                    'ngaySinh' => $_POST['ngaySinh'] ?? '',
                    'soCCCD' => $_POST['soCCCD'] ?? '', // ⚠️ PHẢI CÓ DÒNG NÀY
                    'soDienThoai' => trim($_POST['soDienThoai'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'diaChi' => trim($_POST['diaChi'] ?? ''),
                    'monHocPhuTrach' => trim($_POST['monHocPhuTrach'] ?? ''),
                    'trinhDoHocVan' => trim($_POST['trinhDoHocVan'] ?? ''),
                    'chucVu' => trim($_POST['chucVu'] ?? 'Giáo viên'),
                    'tinhTrangTaiKhoan' => $_POST['tinhTrangTaiKhoan'] ?? 'ACTIVE'
                ];

                $giaoVienModel->capNhatGiaoVien($maGV, $data);
                $_SESSION['flash_success'] = 'Cập nhật thông tin giáo viên thành công';
                header('Location: /public/index.php?page=bgh-quan-ly-giao-vien');
                exit;
            }

            // Lấy thông tin giáo viên
            $giaoVien = $giaoVienModel->getGiaoVienByMa($maGV);
            if (!$giaoVien) {
                throw new Exception('Không tìm thấy giáo viên');
            }

            // Hiển thị form cập nhật
            require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php';
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $maGV = $_POST['maGV'] ?? '';
                
                if (empty($maGV)) {
                    throw new Exception('Không tìm thấy mã giáo viên');
                }

                $giaoVienModel->xoaGiaoVien($maGV);
                $_SESSION['flash_success'] = 'Xóa giáo viên thành công';
            }
            
            header('Location: /public/index.php?page=bgh-quan-ly-giao-vien');
            exit;
    }

} catch (Exception $e) {
    error_log("Error in QuanLyGiaoVienController: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    $message = $e->getMessage();
    $messageType = 'danger';
    
    // Nếu đang ở form thì hiển thị lỗi tại chỗ
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST; // Preserve form data
        require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/tao_giao_vien.php';
        exit;
    } elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $giaoVien = $giaoVienModel->getGiaoVienByMa($_GET['maGV'] ?? '');
        if (!$giaoVien) {
            $giaoVien = $_POST; // Fallback to POST data
        }
        require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php';
        exit;
    } else {
        // Hiển thị lỗi trực tiếp thay vì redirect
        $danhSachGiaoVien = $giaoVienModel->getAllGiaoVien();
        require_once __DIR__ . '/../../../views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php';
        exit;
    }
}

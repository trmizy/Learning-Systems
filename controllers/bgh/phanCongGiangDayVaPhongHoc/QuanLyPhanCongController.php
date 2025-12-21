<?php
/**
 * Controller: Quản lý Phân công GVCN và Phòng học
 * Path: controllers/bgh/phanCongGiangDayVaPhongHoc/QuanLyPhanCongController.php
 * Chức năng: 
 * - Hiển thị danh sách lớp học
 * - Phân công giáo viên chủ nhiệm (GVCN)
 * - Phân công phòng học
 * - Sử dụng PHP thuần, không AJAX
 */

require_once __DIR__ . '/../../../models/bgh/PhanCongModel.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'bgh') {
    $_SESSION['flash_error'] = 'Không có quyền truy cập';
    header('Location: /public/index.php');
    exit;
}

// Khởi tạo model
$phanCongModel = new PhanCongModel();

// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $maLop = $_POST['maLop'] ?? '';
    
    try {
        switch ($action) {
            case 'gan_gvcn':
                $maGV = trim($_POST['maGV'] ?? '');
                if (empty($maLop) || empty($maGV)) {
                    throw new Exception('Vui lòng chọn đầy đủ thông tin');
                }
                
                // Kiểm tra GV đã làm GVCN chưa
                $lopDangChuNhiem = $phanCongModel->kiemTraGVCN($maGV);
                if ($lopDangChuNhiem && $lopDangChuNhiem['lop'] !== $maLop) {
                    throw new Exception("Giáo viên này đã là GVCN của lớp {$lopDangChuNhiem['tenLop']}");
                }
                
                $phanCongModel->ganGVCN($maLop, $maGV);
                $_SESSION['flash_success'] = 'Gán giáo viên chủ nhiệm thành công';
                break;
                
            case 'gan_phong':
                $maPhong = trim($_POST['maPhong'] ?? '');
                if (empty($maLop) || empty($maPhong)) {
                    throw new Exception('Vui lòng chọn đầy đủ thông tin');
                }
                
                $lopInfo = $phanCongModel->getThongTinLop($maLop);
                $namHoc = $lopInfo['namHoc'] ?? '2024-2025';
                
                // Kiểm tra phòng đã được gán chưa
                $lopDangGan = $phanCongModel->kiemTraPhongHoc($maPhong, $namHoc);
                if ($lopDangGan && $lopDangGan['maLop'] !== $maLop) {
                    throw new Exception("Phòng này đã được gán cho lớp {$lopDangGan['tenLop']}");
                }
                
                $phanCongModel->ganPhongHoc($maLop, $maPhong);
                $_SESSION['flash_success'] = 'Gán phòng học thành công';
                break;
                
            case 'xoa_gvcn':
                if (empty($maLop)) {
                    throw new Exception('Không tìm thấy mã lớp');
                }
                $phanCongModel->xoaGVCN($maLop);
                $_SESSION['flash_success'] = 'Xóa giáo viên chủ nhiệm thành công';
                break;
                
            case 'xoa_phong':
                if (empty($maLop)) {
                    throw new Exception('Không tìm thấy mã lớp');
                }
                $phanCongModel->xoaPhongHoc($maLop);
                $_SESSION['flash_success'] = 'Xóa phòng học thành công';
                break;
                
            default:
                throw new Exception('Hành động không hợp lệ');
        }
        
        // Redirect để tránh form resubmission
        header("Location: /public/index.php?action=bgh-phan-cong&namHoc=" . urlencode($_GET['namHoc'] ?? '2024-2025'));
        exit;
        
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        header("Location: /public/index.php?action=bgh-phan-cong&namHoc=" . urlencode($_GET['namHoc'] ?? '2024-2025'));
        exit;
    }
}

// Lấy dữ liệu
$namHocFilter = $_GET['namHoc'] ?? '2024-2025';
$danhSachNamHoc = $phanCongModel->getAllNamHoc();
$danhSachLop = $phanCongModel->getAllLopHoc($namHocFilter);

// Lấy danh sách GV và Phòng học để hiển thị trong form (convert sang array)
$danhSachGiaoVienStmt = $phanCongModel->getGiaoVienChuaChuNhiem(null);
$danhSachGiaoVien = $danhSachGiaoVienStmt->fetchAll(PDO::FETCH_ASSOC);

$danhSachPhongHocStmt = $phanCongModel->getPhongHocChuaGan(null, $namHocFilter);
$danhSachPhongHoc = $danhSachPhongHocStmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy flash messages từ session
$message = null;
$messageType = 'info';
if (isset($_SESSION['flash_success'])) {
    $message = $_SESSION['flash_success'];
    $messageType = 'success';
    unset($_SESSION['flash_success']);
} elseif (isset($_SESSION['flash_error'])) {
    $message = $_SESSION['flash_error'];
    $messageType = 'danger';
    unset($_SESSION['flash_error']);
}

// Gọi View
require_once __DIR__ . '/../../../views/bgh/phanCongGiangDayVaPhongHoc/quan_ly_phan_cong.php';

require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../../config/database.php';

class QuanLyPhanCongController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Hiển thị danh sách phân công giảng dạy
     */
    public function index() {
        require_role(['bgh']);

        try {
            // Lấy danh sách phân công
            $stmt = $this->db->query("
                SELECT 
                    pc.maPhanCong,
                    lh.tenLop,
                    mh.tenMon,
                    gv.hoTen as tenGiaoVien,
                    pc.namHoc,
                    pc.hocKy,
                    pc.ghiChu
                FROM phanconggiangday pc
                INNER JOIN lophoc lh ON pc.maLop = lh.maLop
                INNER JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                LEFT JOIN giaovienbomon gv ON pc.maGV = gv.maGV
                WHERE pc.namHoc = '2024-2025'
                ORDER BY lh.tenLop, mh.tenMon
            ");
            
            $danhSachPhanCong = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Render view
            require_once __DIR__ . '/../../../views/bgh/phan_cong_list.php';
            
        } catch (PDOException $e) {
            error_log("Error QuanLyPhanCongController::index: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Không thể tải danh sách phân công.';
            header('Location: /public/index.php');
            exit;
        }
    }

    // ...existing code...
}

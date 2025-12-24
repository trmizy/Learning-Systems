<?php
/**
 * Controller: Phân công Môn học (GV bộ môn)
 * Path: controllers/bgh/phanCongGiangDayVaPhongHoc/PhanCongMonHocController.php
 * Chức năng:
 * - Hiển thị danh sách môn học của một lớp
 * - Phân công giáo viên bộ môn cho từng môn học
 * - Cập nhật/xóa phân công giảng dạy
 * - Sử dụng PHP thuần, không AJAX
 * Ràng buộc nghiệp vụ:
 * - Giáo viên chỉ được phụ trách tối đa 5 lớp
 * - GVCN phải dạy môn của mình cho lớp mình chủ nhiệm
 */

require_once __DIR__ . '/../../../models/bgh/PhanCongModel.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'bgh') {
    $_SESSION['flash_error'] = 'Không có quyền truy cập';
    header('Location: /public/index.php');
    exit;
}

$phanCongModel = new PhanCongModel();

// Lấy và validate tham số
$maLop = $_GET['maLop'] ?? null;
$hocKy = $_GET['hocKy'] ?? '1';

if (!$maLop) {
    $_SESSION['flash_error'] = 'Không tìm thấy mã lớp';
    header('Location: /public/index.php?action=bgh-phan-cong');
    exit;
}

// Lấy thông tin lớp học
$lopInfo = $phanCongModel->getThongTinLop($maLop);
if (!$lopInfo) {
    $_SESSION['flash_error'] = 'Không tìm thấy thông tin lớp';
    header('Location: /public/index.php?action=bgh-phan-cong');
    exit;
}

$namHoc = $lopInfo['namHoc'];

// Xử lý POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'them_phan_cong':
                $maMonHoc = $_POST['maMonHoc'] ?? null;
                $maGV = $_POST['maGV'] ?? null;
                $ghiChu = $_POST['ghiChu'] ?? null;
                
                if (!$maMonHoc || !$maGV) {
                    throw new Exception('Vui lòng chọn môn học và giáo viên');
                }
                
                // Kiểm tra đã tồn tại chưa
                if ($phanCongModel->kiemTraPhanCongTonTai($maLop, $maMonHoc, $namHoc, $hocKy)) {
                    // Cập nhật
                    $phanCongModel->capNhatPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy);
                    $_SESSION['flash_success'] = 'Cập nhật phân công giảng dạy thành công';
                } else {
                    // Thêm mới
                    $phanCongModel->themPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy, $ghiChu);
                    $_SESSION['flash_success'] = 'Thêm phân công giảng dạy thành công';
                }
                break;
                
            case 'xoa_phan_cong':
                $maPhanCong = $_POST['maPhanCong'] ?? null;
                
                if (!$maPhanCong) {
                    throw new Exception('Không tìm thấy mã phân công');
                }
                
                $phanCongModel->xoaPhanCongGiangDay($maPhanCong);
                $_SESSION['flash_success'] = 'Xóa phân công giảng dạy thành công';
                break;
                
            default:
                throw new Exception('Hành động không hợp lệ');
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    
    // Redirect về trang hiện tại
    header("Location: /public/index.php?action=bgh-phan-cong-mon-hoc&maLop=" . urlencode($maLop) . "&hocKy=" . urlencode($hocKy));
    exit;
}

// Lấy danh sách tất cả môn học của năm học này (không lọc theo học kỳ ở đây)
$monHocsStmt = $phanCongModel->getAllMonHoc($namHoc);
$allMonHocs = [];
while ($mon = $monHocsStmt->fetch()) {
    $allMonHocs[] = $mon;
}

// Lấy danh sách phân công hiện tại (lọc theo học kỳ)
$phanCongsStmt = $phanCongModel->getPhanCongGiangDay($maLop, $namHoc, $hocKy);
$phanCongMap = [];
while ($pc = $phanCongsStmt->fetch()) {
    $phanCongMap[$pc['maMonHoc']] = $pc;
}

// Merge: môn học + thông tin phân công (nếu có) + danh sách GV theo môn
$monHocsWithAssignment = [];
foreach ($allMonHocs as $mon) {
    // Lấy danh sách GV dạy môn này (chưa đủ 5 lớp)
    $giaoViensResult = $phanCongModel->getGiaoVienTheoMon($mon['tenMon']);
    $danhSachGV = [];
    
    while ($gv = $giaoViensResult->fetch()) {
        // Kiểm tra số lớp đang phụ trách
        $soLop = $phanCongModel->demSoLopGVDang($gv['maGV'], $namHoc, $hocKy);
        
        // Chỉ thêm GV nếu chưa đủ 5 lớp
        if ($soLop < 5) {
            $danhSachGV[] = $gv;
        }
    }
    
    $item = [
        'maMonHoc' => $mon['maMonHoc'],
        'tenMon' => $mon['tenMon'],
        'soTietTuan' => $mon['soTietTuan'],
        'daPhanCong' => false,
        'maPhanCong' => null,
        'maGV' => null,
        'tenGV' => null,
        'emailGV' => null,
        'sdtGV' => null,
        'danhSachGV' => $danhSachGV, // Danh sách GV cho môn này
    ];
    
    if (isset($phanCongMap[$mon['maMonHoc']])) {
        $pc = $phanCongMap[$mon['maMonHoc']];
        $item['daPhanCong'] = true;
        $item['maPhanCong'] = $pc['maPhanCong'];
        $item['maGV'] = $pc['maGV'];
        $item['tenGV'] = $pc['tenGV'];
        $item['emailGV'] = $pc['emailGV'];
        $item['sdtGV'] = $pc['sdtGV'];
    }
    
    $monHocsWithAssignment[] = $item;
}

// Load view
require_once __DIR__ . '/../../../views/bgh/phanCongGiangDayVaPhongHoc/phan_cong_mon_hoc.php';

require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../../config/database.php';

class PhanCongMonHocController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Hiển thị form phân công môn học
     */
    public function index() {
        require_role(['bgh']);

        try {
            // Lấy danh sách lớp
            $stmtLop = $this->db->query("SELECT maLop, tenLop, khoi FROM lophoc ORDER BY tenLop");
            $danhSachLop = $stmtLop->fetchAll(PDO::FETCH_ASSOC);

            // Lấy danh sách môn học
            $stmtMon = $this->db->query("SELECT maMonHoc, tenMon FROM monhoc ORDER BY tenMon");
            $danhSachMon = $stmtMon->fetchAll(PDO::FETCH_ASSOC);

            // Lấy danh sách giáo viên
            $stmtGV = $this->db->query("
                SELECT maGV, hoTen, monHocPhuTrach 
                FROM giaovienbomon 
                WHERE tinhTrangTaiKhoan = 'ACTIVE'
                ORDER BY hoTen
            ");
            $danhSachGiaoVien = $stmtGV->fetchAll(PDO::FETCH_ASSOC);

            // Render view
            
            
        } catch (PDOException $e) {
            error_log("Error PhanCongMonHocController::index: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Không thể tải dữ liệu phân công.';
            header('Location: /public/index.php');
            exit;
        }
    }

    /**
     * Xử lý lưu phân công môn học
     */
    public function store() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=bgh-phan-cong-mon-hoc');
            exit;
        }

        try {
            $maLop = $_POST['maLop'] ?? '';
            $maMonHoc = $_POST['maMonHoc'] ?? '';
            $maGV = $_POST['maGV'] ?? '';
            $namHoc = $_POST['namHoc'] ?? '2024-2025';
            $hocKy = $_POST['hocKy'] ?? 'HK1';
            $ghiChu = $_POST['ghiChu'] ?? '';

            // Validate
            if (empty($maLop) || empty($maMonHoc) || empty($maGV)) {
                $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin.';
                header('Location: /public/index.php?action=bgh-phan-cong-mon-hoc');
                exit;
            }

            // Tạo mã phân công
            $maPhanCong = 'PC_' . $maLop . '_' . $maMonHoc . '_' . time();

            // Insert
            $stmt = $this->db->prepare("
                INSERT INTO phanconggiangday 
                (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy, ghiChu, ngayPhanCong)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $maPhanCong,
                $maLop,
                $maMonHoc,
                $maGV,
                $namHoc,
                $hocKy,
                $ghiChu
            ]);

            $_SESSION['flash_success'] = 'Phân công môn học thành công!';
            header('Location: /public/index.php?action=bgh-phan-cong');
            exit;
            
        } catch (PDOException $e) {
            error_log("Error PhanCongMonHocController::store: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Không thể lưu phân công. Vui lòng thử lại.';
            header('Location: /public/index.php?action=bgh-phan-cong-mon-hoc');
            exit;
        }
    }
}

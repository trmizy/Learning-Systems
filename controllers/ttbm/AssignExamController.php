<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class AssignExamController {
    private AssignExamModel $model;

    public function __construct() {
        // Kiểm tra quyền tổ trưởng bộ môn
        require_role(['ttbm']);
        
        $this->model = new AssignExamModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // Lấy thông tin user
        $user = current_user();
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được tổ trưởng đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã giáo viên (tổ trưởng) từ username
        $maTT = $this->getGiaoVienByUsername($user['username']);
        
        if (!$maTT) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin tổ trưởng.';
            header('Location: /public/index.php');
            exit;
        }

        $phanCong = $this->model->getDanhSachPhanCong();
        $khoi = $this->model->getDanhSachKhoi();
        $listGV = $this->model->getGiaoVienTheoToTruong($maTT);
        
        include __DIR__ . '/../../views/ttbm/assign_exam.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // --- Lấy dữ liệu từ form ---
        $hocKy      = trim($_POST['hocKy'] ?? '');
        $kyThi      = trim($_POST['kyThi'] ?? '');
        $soLuongDe  = (int)($_POST['soLuongDe'] ?? 0);
        $thoiHan    = trim($_POST['thoiHan'] ?? '');
        $ghiChu     = trim($_POST['ghiChu'] ?? '');
        $listGV     = $_POST['listGV'] ?? [];

        // --- 8.3 Chưa chọn Kỳ thi hoặc Học kỳ ---
        if ($hocKy === '' || $kyThi === '') {
            $_SESSION['flash_error'] = "Vui lòng chọn Học kỳ và Kỳ thi.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // --- 8.2 Số lượng đề <= 0 ---
        if ($soLuongDe <= 0) {
            $_SESSION['flash_error'] = "Số lượng đề thi không hợp lệ.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // --- 8.1 Thời hạn nộp đề không hợp lệ ---
        if ($thoiHan === '') {
            $_SESSION['flash_error'] = "Thời hạn không hợp lệ (để trống).";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        $timestamp = strtotime($thoiHan);
        if ($timestamp === false) {
            $_SESSION['flash_error'] = "Thời hạn không hợp lệ (sai định dạng).";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        if ($timestamp < time()) {
            $_SESSION['flash_error'] = "Thời hạn không hợp lệ (ngày đã qua).";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // --- Không chọn giáo viên ---
        if (empty($listGV)) {
            $_SESSION['flash_error'] = "Vui lòng chọn ít nhất một giáo viên để phân công.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // --- Lưu dữ liệu ---
        $ok = $this->model->luuPhanCongNhieuGV($listGV, $hocKy, $kyThi, $soLuongDe, $thoiHan, $ghiChu);

        if ($ok) {
            $_SESSION['flash_success'] = "Phân công giáo viên ra đề thành công.";
        } else {
            $_SESSION['flash_error'] = "Không thể lưu phân công, vui lòng thử lại.";
        }

        header('Location: index.php?action=assign_exam');
        exit;
    }

    /**
     * Helper: Lấy mã giáo viên từ username
     */
    private function getGiaoVienByUsername(string $username): ?string {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT maTaiKhoan 
                FROM TaiKhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $taiKhoan = $stmt->fetch();
            
            if (!$taiKhoan) {
                return null;
            }
            
            $stmt2 = $conn->prepare("
                SELECT maGV 
                FROM GiaoVienBoMon 
                WHERE maTaiKhoan = ?
                LIMIT 1
            ");
            $stmt2->execute([$taiKhoan['maTaiKhoan']]);
            $gv = $stmt2->fetch();
            
            return $gv ? $gv['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getGiaoVienByUsername: " . $e->getMessage());
            return null;
        }
    }
}

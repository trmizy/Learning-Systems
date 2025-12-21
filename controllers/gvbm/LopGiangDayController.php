<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/gvbm/LopGiangDayModel.php';

class LopGiangDayController {
    private $model;

    public function __construct() {
        $this->model = new LopGiangDayModel();
    }

    /**
     * Hiển thị danh sách lớp giảng dạy
     */
    public function index() {
        // Kiểm tra quyền: GVBM, GVCN, TTBM
        require_role(['gvbm', 'gvcn', 'ttbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã giáo viên
        $maGV = $this->model->getMaGiaoVienByUsername($user['username']);
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy học kỳ, năm học hiện tại
        $namHoc = $_GET['namHoc'] ?? '2024-2025';
        $hocKy = $_GET['hocKy'] ?? '1';

        // Lấy danh sách lớp giảng dạy
        $danhSachLop = $this->model->getDanhSachLopGiangDay($maGV, $namHoc, $hocKy);

        // Nếu là GVCN, thêm lớp chủ nhiệm
        $lopChuNhiem = null;
        if ($user['role'] === 'gvcn') {
            $lopChuNhiem = $this->model->getLopChuNhiem($maGV);
        }

        // Biến cho view
        $selected_namHoc = $namHoc;
        $selected_hocKy = $hocKy;

        // Render view
        require_once __DIR__ . '/../../views/gvbm/lop_giang_day.php';
    }

    /**
     * Xem chi tiết lớp và danh sách học sinh
     */
    public function chiTietLop() {
        require_role(['gvbm', 'gvcn', 'ttbm']);

        $maLop = $_GET['maLop'] ?? null;
        if (!$maLop) {
            $_SESSION['flash_error'] = 'Không xác định được lớp học.';
            header('Location: /public/index.php?action=lop_giang_day');
            exit;
        }

        // Lấy danh sách học sinh
        $danhSachHocSinh = $this->model->getDanhSachHocSinhTheoLop($maLop);

        // Lấy thông tin lớp (từ học sinh đầu tiên)
        $thongTinLop = null;
        if (!empty($danhSachHocSinh)) {
            $firstStudent = $danhSachHocSinh[0];
            // Query lại để lấy thông tin lớp đầy đủ
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM lophoc WHERE maLop = ? LIMIT 1");
                $stmt->execute([$maLop]);
                $thongTinLop = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
            }
        }

        // Render view
        require_once __DIR__ . '/../../views/gvbm/chi_tiet_lop.php';
    }

    /**
     * Xem chi tiết học sinh
     */
    public function chiTietHocSinh() {
        require_role(['gvbm', 'gvcn', 'ttbm']);

        $maHS = $_GET['maHS'] ?? null;
        if (!$maHS) {
            $_SESSION['flash_error'] = 'Không xác định được học sinh.';
            header('Location: /public/index.php?action=lop_giang_day');
            exit;
        }

        // Lấy thông tin chi tiết học sinh
        $hocSinh = $this->model->getChiTietHocSinh($maHS);
        if (!$hocSinh) {
            $_SESSION['flash_error'] = 'Không tìm thấy học sinh.';
            header('Location: /public/index.php?action=lop_giang_day');
            exit;
        }

        // Lấy điểm trung bình
        $diemTrungBinh = $this->model->getDiemTrungBinhHocSinh($maHS);

        // Render view
        require_once __DIR__ . '/../../views/gvbm/chi_tiet_hoc_sinh.php';
    }
}

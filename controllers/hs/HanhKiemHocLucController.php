<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/hs/HanhKiemHocLucModel.php';

class HanhKiemHocLucController {
    private $model;

    public function __construct() {
        $this->model = new HanhKiemHocLucModel();
    }

    /**
     * Hiển thị hạnh kiểm & học lực cho học sinh
     */
    public function indexHocSinh() {
        require_role(['hs']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được học sinh đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        $maHocSinh = $this->model->getMaHocSinhByUsername($user['username']);
        
        if (!$maHocSinh) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin học sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $this->hienThiHanhKiemHocLuc($maHocSinh);
    }

    /**
     * Hiển thị hạnh kiểm & học lực cho phụ huynh
     */
    public function indexPhuHuynh() {
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được phụ huynh đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        $maPhuHuynh = $this->model->getMaPhuHuynhByUsername($user['username']);
        if (!$maPhuHuynh) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        $danhSachCon = $this->model->getDanhSachConCuaPhuHuynh($maPhuHuynh);
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em.';
            header('Location: /public/index.php');
            exit;
        }

        $maHocSinhChon = $_GET['maHocSinh'] ?? $danhSachCon[0]['maHocSinh'];

        $this->hienThiHanhKiemHocLuc($maHocSinhChon, $danhSachCon);
    }

    /**
     * Logic chung hiển thị hạnh kiểm & học lực
     */
    private function hienThiHanhKiemHocLuc($maHocSinh, $danhSachCon = null) {
        // Lấy thông tin học sinh
        $thongTinHS = $this->model->getThongTinHocSinh($maHocSinh);
        
        // ⚠️ FIX: Kiểm tra null trước khi tiếp tục
        if (!$thongTinHS) {
            error_log("❌ Không tìm thấy thông tin học sinh: $maHocSinh");
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin học sinh.';
            header('Location: /public/index.php');
            exit;
        }

        // ⚠️ FIX: Đảm bảo các trường tồn tại
        $thongTinHS['hoTen'] = $thongTinHS['hoTen'] ?? 'Học sinh';
        $thongTinHS['tenLop'] = $thongTinHS['tenLop'] ?? 'Chưa có lớp';

        // Lấy dữ liệu hạnh kiểm và học lực
        $danhSachHanhKiem = $this->model->getHanhKiemHocSinh($maHocSinh);
        $danhSachHocLuc = $this->model->getHocLucHocSinh($maHocSinh);

        // Render view
        require_once __DIR__ . '/../../views/shared/hanh_kiem_hoc_luc.php';
    }
}

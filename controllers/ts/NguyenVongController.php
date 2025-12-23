<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ts/NguyenVongModel.php';

class NguyenVongController {
    private $model;

    public function __construct() {
        $this->model = new NguyenVongModel();
    }

    /**
     * Hiển thị trang đăng ký nguyện vọng
     */
    public function index() {
        // Kiểm tra quyền - CHỈ thí sinh
        require_role(['ts']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy thông tin thí sinh
        $thongTinTS = $this->model->getThongTinThiSinhByUsername($user['username']);
        
        if (!$thongTinTS) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin thí sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $maThiSinh = $thongTinTS['maThiSinh'];

        // Kiểm tra đã đăng ký chưa
        $daDangKy = $this->model->kiemTraDaDangKy($maThiSinh);
        $nguyenVongHienTai = [];
        $truongDaChon = [];

        if ($daDangKy) {
            $nguyenVongHienTai = $this->model->getDanhSachNguyenVong($maThiSinh);
            // ⚠️ Lấy danh sách trường đã chọn để loại trừ
            $truongDaChon = $this->model->getDanhSachTruongDaChon($maThiSinh);
        }

        // ⚠️ Lấy danh sách trường - LOẠI TRỪ trường đã chọn
        $danhSachTruong = $this->model->getDanhSachTruong($maThiSinh, $truongDaChon);

        // Truyền biến vào view
        require_once __DIR__ . '/../../views/ts/nguyen_vong_view.php';
    }

    /**
     * Xử lý đăng ký nguyện vọng - CẬP NHẬT: CHỈ NHẬN 1 NGUYỆN VỌNG
     */
    public function dangKy() {
        require_role(['ts']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=ts-nguyen-vong');
            exit;
        }

        $user = current_user();
        $thongTinTS = $this->model->getThongTinThiSinhByUsername($user['username']);
        
        if (!$thongTinTS) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin thí sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $maThiSinh = $thongTinTS['maThiSinh'];

        // Kiểm tra quyền đăng ký nguyện vọng tiếp theo
        $checkNext = $this->model->kiemTraNguyenVongTiepTheo($maThiSinh);
        
        if (!$checkNext['allowed']) {
            $_SESSION['flash_error'] = $checkNext['message'];
            header('Location: /public/index.php?action=ts-nguyen-vong');
            exit;
        }

        $nextPriority = $checkNext['nextPriority'];

        // Lấy dữ liệu POST - CHỈ NHẬN 1 NGUYỆN VỌNG
        $maTruong = $_POST["maTruong_{$nextPriority}"] ?? '';
        
        if (empty($maTruong)) {
            $_SESSION['flash_error'] = 'Vui lòng chọn trường';
            header('Location: /public/index.php?action=ts-nguyen-vong');
            exit;
        }

        $danhSachNguyenVong = [
            [
                'maTruong' => $maTruong,
                'thuTuUuTien' => $nextPriority
            ]
        ];

        // Đăng ký
        $result = $this->model->dangKyNguyenVong($maThiSinh, $danhSachNguyenVong);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: /public/index.php?action=ts-nguyen-vong');
        exit;
    }

    /**
     * Hủy đăng ký nguyện vọng
     */
    public function huyDangKy() {
        require_role(['ts']);

        $user = current_user();
        $thongTinTS = $this->model->getThongTinThiSinhByUsername($user['username']);
        
        if (!$thongTinTS) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin thí sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $result = $this->model->huyDangKyNguyenVong($thongTinTS['maThiSinh']);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: /public/index.php?action=ts-nguyen-vong');
        exit;
    }
}

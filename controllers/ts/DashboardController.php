<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ts/DashboardModel.php';

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel();
    }

    /**
     * Hiển thị dashboard thí sinh
     */
    public function index() {
        require_role(['ts']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy thông tin thí sinh
        $thongTinTS = $this->model->getThongTinThiSinh($user['username']);
        
        if (!$thongTinTS) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin thí sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $maThiSinh = $thongTinTS['maThiSinh'];

        // Lấy các thống kê
        $diemTB = $this->model->getDiemThiTongKet($maThiSinh);
        $soNguyenVong = $this->model->demNguyenVongDaDangKy($maThiSinh);
        $soHoSo = $this->model->demHoSoDaNop($maThiSinh);
        $daDangKy = $soNguyenVong > 0;

        // Truyền biến vào view
        require_once __DIR__ . '/../../views/ts/dashboard.php';
    }
}

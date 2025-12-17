<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/gvbm/ViewAssignModel.php';

class ViewAssignController {
    private $model;

    public function __construct() {
        $this->model = new ViewAssignModel();
    }

    /**
     * Hiển thị danh sách phân công của giáo viên - TRANG ĐỘC LẬP
     */
    public function index() {
        require_role(['gvbm', 'gvcn']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Phiên đăng nhập hết hạn.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy mã GV từ Model
        $maGV = $this->model->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php?action=gvbm-dashboard');
            exit;
        }

        // Lấy danh sách phân công từ Model
        $phanCong = $this->model->getPhanCongRaDe($maGV);

        // Render view độc lập, KHÔNG include vào dashboard
        require_once __DIR__ . '/../../views/gvbm/view_assign.php';
        exit;
    }
}

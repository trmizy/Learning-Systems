<?php
declare(strict_types=1);
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class ViewAssignController {
    private AssignExamModel $model;

    public function __construct() {
        $this->model = new AssignExamModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        $maGV = $_SESSION['auth']['maGV'] ?? '';
        if (!$maGV) {
            echo "<div class='alert alert-danger'>Không xác định được giáo viên đăng nhập.</div>";
            return;
        }
        $phanCong = $this->model->getPhanCongTheoGiaoVien($maGV);
        include __DIR__ . '/../../views/gvbm/view_assign.php';
    }
}

<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/admin/DanhSachHocSinhModel.php';

class DanhSachHocSinhController {
    private $model;

    public function __construct() {
        $this->model = new DanhSachHocSinhModel();
    }

    /**
     * Hiển thị danh sách học sinh theo khối -> lớp
     */
    public function danhSachTheoKhoiVaLop() {
        require_role(['admin', 'bgh']);

        // Lấy khối được chọn (mặc định khối 10)
        $khoiChon = $_GET['khoi'] ?? '10';
        
        // Lấy lớp được chọn (nếu có)
        $lopChon = $_GET['lop'] ?? null;

        // Lấy danh sách lớp theo khối
        $danhSachLop = $this->model->getDanhSachLopTheoKhoi($khoiChon);

        // Lấy danh sách học sinh
        $danhSachHocSinh = [];
        if ($lopChon) {
            $danhSachHocSinh = $this->model->getDanhSachHocSinhTheoLop($lopChon);
        }

        // Render view
        require_once __DIR__ . '/../../views/admin/danh_sach_hoc_sinh.php';
    }
}

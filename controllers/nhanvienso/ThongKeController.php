<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/nhanvienso/ThongKeModel.php';

class ThongKeController {
    private $model;

    public function __construct() {
        $this->model = new ThongKeModel();
    }

    /**
     * Hiển thị trang thống kê điểm tuyển sinh
     */
    public function diemTuyenSinh() {
        // Kiểm tra quyền
        require_role(['nhanvienso']);

        // Lấy năm tuyển sinh (mặc định là năm hiện tại)
        $namTuyenSinh = $_GET['nam'] ?? date('Y');

        // Lấy dữ liệu thống kê
        $thongKeChung = $this->model->getThongKeChung($namTuyenSinh);
        $thongKeTheoMon = $this->model->getThongKeTheoMon($namTuyenSinh);
        $thongKeTheoTruong = $this->model->getThongKeTheoTruong($namTuyenSinh);
        $phanPoiDiem = $this->model->getPhanPoiDiem($namTuyenSinh);
        $topThiSinh = $this->model->getTopThiSinh($namTuyenSinh, 10);

        // Danh sách năm để filter
        $danhSachNam = $this->model->getDanhSachNamTuyenSinh();

        // Render view
        require_once __DIR__ . '/../../views/nhanvienso/thong_ke_diem.php';
    }
}

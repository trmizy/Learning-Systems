<?php
// File: controllers/nhanvienso/BaoCaoController.php
require_once __DIR__ . '/../../models/nhanvienso/BaoCaoModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class BaoCaoController {
    private $model;

    public function __construct() {
        $this->model = new BaoCaoModel();
    }

    public function showBaoCaoPage() {
        require_role(['nhanvienso']); 
        
        // Lấy dữ liệu danh mục từ CSDL
        $danhSachTruong = $this->model->getAllTruong();
        $danhSachKhoi = $this->model->getAllKhoi();
        $danhSachNamHoc = $this->model->getAllNamHoc(); // <-- Dữ liệu động
        $danhSachLop = []; 
        
        $ketQuaBaoCao = [];
        $chartData = [];
        $error_message = null;
        
        $maTruong = $_GET['maTruong'] ?? '';
        $namHoc = $_GET['namHoc'] ?? '';
        $hocKy = $_GET['hocKy'] ?? 'HK1';
        $maKhoi = $_GET['maKhoi'] ?? '';
        $maLop = $_GET['maLop'] ?? '';
        
        // Load danh sách lớp nếu đủ thông tin
        if ($maTruong && $maKhoi && $namHoc) {
            $danhSachLop = $this->model->getLopByTruongKhoi($maTruong, $maKhoi, $namHoc);
        }

        if (isset($_GET['search'])) {
            if (empty($maTruong) || empty($namHoc)) {
                $error_message = "Vui lòng chọn Trường và Năm học.";
            } else {
                $ketQuaBaoCao = $this->model->findBaoCao($maTruong, $namHoc, $hocKy);
                $rawStats = $this->model->getThongKeHocLuc($maTruong, $namHoc, $hocKy, $maKhoi, $maLop);
                
                $labels = ['Giỏi', 'Khá', 'Trung bình', 'Yếu', 'Kém'];
                $data = [];
                foreach ($labels as $label) {
                    $data[] = $rawStats[$label] ?? 0;
                }
                
                $chartData = [
                    'labels' => json_encode($labels),
                    'data' => json_encode($data)
                ];
            }
        }

        $pageTitle = "Xem báo cáo - thống kê";
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/nhanvienso/xem_bao_cao.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }
}
?>
<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/nhanvienso/XetTuyenModel.php';

class XetTuyenController {
    private $model;

    public function __construct() {
        $this->model = new XetTuyenModel();
    }

    /**
     * Hiển thị danh sách thí sinh xét tuyển
     */
    public function index() {
        require_role(['nhanvienso']);

        $maTruong = $_GET['maTruong'] ?? 'TR001';
        $namTuyenSinh = $_GET['namTuyenSinh'] ?? date('Y');

        // Lấy danh sách thí sinh
        $danhSachThiSinh = $this->model->getDanhSachThiSinhXetTuyen($maTruong, $namTuyenSinh);
        $diemChuan = $this->model->getDiemChuan($maTruong, $namTuyenSinh);
        $thongKe = $this->model->getThongKeXetTuyen($maTruong, $namTuyenSinh);

        // Render view
        require_once __DIR__ . '/../../views/nhanvienso/xet_tuyen_list.php';
    }

    /**
     * Xét tuyển một thí sinh
     */
    public function xetTuyenMotThiSinh() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maThiSinh = $_POST['maThiSinh'] ?? '';
            $maLop = $_POST['maLop'] ?? '';

            if (empty($maThiSinh) || empty($maLop)) {
                $_SESSION['flash_error'] = 'Thiếu thông tin thí sinh hoặc lớp';
                header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
                exit;
            }

            $result = $this->model->xetTuyenThiSinh($maThiSinh, $maLop);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'] . " - Mã HS: {$result['maHS']}";
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }

            header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
            exit;
        }
    }

    /**
     * Xét tuyển tự động (hàng loạt)
     */
    public function xetTuyenTuDong() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTruong = $_POST['maTruong'] ?? '';
            $namTuyenSinh = $_POST['namTuyenSinh'] ?? '';

            $danhSachThiSinh = $this->model->getDanhSachThiSinhXetTuyen($maTruong, $namTuyenSinh);
            $diemChuan = $this->model->getDiemChuan($maTruong, $namTuyenSinh);

            if (!$diemChuan) {
                $_SESSION['flash_error'] = 'Chưa có điểm chuẩn cho năm ' . $namTuyenSinh;
                header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
                exit;
            }

            // Lọc thí sinh đạt điểm chuẩn
            $danhSachDat = [];
            foreach ($danhSachThiSinh as $ts) {
                if ($ts['tongDiem'] >= $diemChuan) {
                    $danhSachDat[] = $ts['maThiSinh'];
                }
            }

            if (empty($danhSachDat)) {
                $_SESSION['flash_warning'] = 'Không có thí sinh nào đạt điểm chuẩn';
                header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
                exit;
            }

            // Xét tuyển hàng loạt
            $ketQua = $this->model->xetTuyenHangLoat($maTruong, $namTuyenSinh, $danhSachDat);

            $_SESSION['flash_success'] = "Xét tuyển thành công: {$ketQua['thanh_cong']} - Thất bại: {$ketQua['that_bai']}";
            header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
            exit;
        }
    }
}

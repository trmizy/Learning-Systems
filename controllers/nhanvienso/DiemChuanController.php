<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/nhanvienso/DiemChuanModel.php';

class DiemChuanController {
    private $model;

    public function __construct() {
        $this->model = new DiemChuanModel();
    }

    /**
     * Danh sách điểm chuẩn chờ duyệt
     */
    public function index() {
        require_role(['nhanvienso']);

        $namTuyenSinh = $_GET['namTuyenSinh'] ?? date('Y');

        // Lấy danh sách điểm chuẩn
        $danhSachDiemChuan = $this->model->getDanhSachDiemChuan($namTuyenSinh);
        $thongKe = $this->model->getThongKeDiemChuan($namTuyenSinh);

        require_once __DIR__ . '/../../views/nhanvienso/diem_chuan_list.php';
    }

    /**
     * Duyệt và công bố điểm chuẩn → Tự động xét tuyển
     */
    public function duyetVaCongBo() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
            exit;
        }

        $maDiemChuan = $_POST['maDiemChuan'] ?? '';

        if (empty($maDiemChuan)) {
            $_SESSION['flash_error'] = 'Thiếu mã điểm chuẩn';
            header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
            exit;
        }

        // ✅ BƯỚC 1: Công bố điểm chuẩn
        $resultCongBo = $this->model->congBoDiemChuan($maDiemChuan);

        if (!$resultCongBo['success']) {
            $_SESSION['flash_error'] = $resultCongBo['message'];
            header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
            exit;
        }

        // ✅ BƯỚC 2: Tự động xét tuyển tất cả nguyện vọng
        $resultXetTuyen = $this->model->xetTuyenTuDongTheoTruong($maDiemChuan);

        $_SESSION['flash_success'] = $resultCongBo['message'] . ' | ' . $resultXetTuyen['message'];
        header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
        exit;
    }

    /**
     * Từ chối điểm chuẩn
     */
    public function tuChoi() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
            exit;
        }

        $maDiemChuan = $_POST['maDiemChuan'] ?? '';
        $lyDo = $_POST['lyDoTuChoi'] ?? '';

        if (empty($maDiemChuan)) {
            $_SESSION['flash_error'] = 'Thiếu mã điểm chuẩn';
            header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
            exit;
        }

        $result = $this->model->tuChoiDiemChuan($maDiemChuan, $lyDo);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: /public/index.php?action=nhanvienso-duyet-diem-chuan');
        exit;
    }
}

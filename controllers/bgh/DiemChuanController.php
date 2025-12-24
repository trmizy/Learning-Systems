<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models\bgh\DiemChuanModel.php';

class DiemChuanController {
    private $model;

    public function __construct() {
        $this->model = new DiemChuanModel();
    }

    /**
     * Hiển thị danh sách điểm chuẩn
     */
    public function index() {
        require_role(['bgh']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy mã trường từ BGH
        $maTruong = $this->model->getMaTruongByBGH($user['username']);
        if (!$maTruong) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin trường';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách điểm chuẩn
        $danhSachDiemChuan = $this->model->getDanhSachDiemChuan($maTruong);

        // Render view
        require_once __DIR__ . '/../../views/bgh/diem_chuan_list.php';
    }

    /**
     * Thêm điểm chuẩn mới
     */
    public function them() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = current_user();
            $maTruong = $this->model->getMaTruongByBGH($user['username']);

            // Validate dữ liệu
            $soDiem = floatval($_POST['soDiem'] ?? 0);
            $namTuyenSinh = intval($_POST['namTuyenSinh'] ?? 0);

            // Kiểm tra điều kiện
            if ($soDiem <= 0 || $soDiem > 30) {
                $_SESSION['flash_error'] = 'Điểm chuẩn phải từ 0 đến 30';
                header('Location: /public/index.php?action=bgh-diem-chuan');
                exit;
            }

            if ($namTuyenSinh < 2020 || $namTuyenSinh > 2100) {
                $_SESSION['flash_error'] = 'Năm tuyển sinh không hợp lệ';
                header('Location: /public/index.php?action=bgh-diem-chuan');
                exit;
            }

            // Kiểm tra trùng
            if ($this->model->kiemTraTonTai($maTruong, $namTuyenSinh)) {
                $_SESSION['flash_error'] = 'Điểm chuẩn năm ' . $namTuyenSinh . ' đã tồn tại';
                header('Location: /public/index.php?action=bgh-diem-chuan');
                exit;
            }

            // Thêm mới
            $data = [
                'soDiem' => $soDiem,
                'maTruong' => $maTruong,
                'namTuyenSinh' => $namTuyenSinh
            ];

            $result = $this->model->themDiemChuan($data);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }

            header('Location: /public/index.php?action=bgh-diem-chuan');
            exit;
        }

        // Hiển thị form (có thể tích hợp trong view list)
        $this->index();
    }

    /**
     * Cập nhật điểm chuẩn
     */
    public function capNhat() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maDiemChuan = $_POST['maDiemChuan'] ?? '';
            $soDiem = floatval($_POST['soDiem'] ?? 0);

            // Validate
            if ($soDiem <= 0 || $soDiem > 30) {
                $_SESSION['flash_error'] = 'Điểm chuẩn phải từ 0 đến 30';
                header('Location: /public/index.php?action=bgh-diem-chuan');
                exit;
            }

            // Cập nhật
            $result = $this->model->capNhatDiemChuan($maDiemChuan, $soDiem);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }

            header('Location: /public/index.php?action=bgh-diem-chuan');
            exit;
        }
    }

    /**
     * Xóa điểm chuẩn
     */
    public function xoa() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maDiemChuan = $_POST['maDiemChuan'] ?? '';

            $result = $this->model->xoaDiemChuan($maDiemChuan);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }

            header('Location: /public/index.php?action=bgh-diem-chuan');
            exit;
        }
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class AssignExamController {
    private AssignExamModel $model;

    public function __construct() {
        $this->model = new AssignExamModel();
    }

    /** Trang phân công */
    public function index(): void {
        require_role(['ttbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy maGV TTBM
        $maTTBM = $this->model->getMaTTBMByUsername($user['username']);
        if (!$maTTBM) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin Tổ trưởng bộ môn.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy môn phụ trách (JOIN THEO maMonHoc)
        $mon = $this->model->layMonCuaToTruong($maTTBM);
        if (!$mon) {
            $_SESSION['flash_error'] = 'Tổ trưởng chưa được phân công môn phụ trách.';
            header('Location: /public/index.php');
            exit;
        }

        $maMonHoc = $mon['maMonHoc'];

        // Giáo viên cùng môn
        $listGV = $this->model->layGiaoVienTheoMon($maMonHoc);

        // Danh sách khối
        $khoi = $this->model->layDanhSachKhoi();

        // Danh sách phân công
        $phanCong = $this->model->layDanhSachPhanCong();

        require_once __DIR__ . '/../../views/ttbm/assign_exam.php';
    }

    /** Lưu phân công */
    public function store(): void {
        require_role(['ttbm']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        // Lấy maMonHoc THEO USERNAME (KHÔNG TIN FORM)
        $maMonHoc = $this->model->layMaMonHocTheoUsername($user['username']);
        if (!$maMonHoc) {
            $_SESSION['flash_error'] = 'Không xác định được môn phụ trách.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        $data = [
            'maMonHoc'  => $maMonHoc,
            'khoi'      => $_POST['khoi'] ?? '',
            'listGV'    => $_POST['listGV'] ?? [],
            'hocKy'     => $_POST['hocKy'] ?? '',
            'kyThi'     => $_POST['kyThi'] ?? '',
            'soLuongDe' => (int)($_POST['soLuongDe'] ?? 0),
            'thoiHan'   => $_POST['thoiHan'] ?? '',
            'ghiChu'    => $_POST['ghiChu'] ?? ''
        ];

        /* ===== VALIDATE ===== */

        // 1️⃣ Giáo viên
        if (empty($data['listGV'])) {
            $_SESSION['flash_error'] = 'Vui lòng chọn ít nhất một giáo viên.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        // 2️⃣ Học kỳ – Kỳ thi
        if (empty($data['hocKy']) || empty($data['kyThi'])) {
            $_SESSION['flash_error'] = 'Vui lòng chọn Học kỳ và Kỳ thi.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        // 3️⃣ Số lượng đề
        if ($data['soLuongDe'] <= 0) {
            $_SESSION['flash_error'] = 'Số lượng đề thi phải lớn hơn 0.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        // 4️⃣ Thời hạn (nới điều kiện tránh lỗi âm thầm)
        if (empty($data['thoiHan']) || strtotime($data['thoiHan']) < time() + 60) {
            $_SESSION['flash_error'] = 'Thời hạn phải lớn hơn thời điểm hiện tại.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        /* ===== LƯU ===== */
        if ($this->model->luuPhanCong($data)) {
            $_SESSION['flash_success'] = 'Đã lưu phân công ra đề!';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi lưu phân công.';
        }

        header('Location: /public/index.php?action=assign_exam');
        exit;
    }

    /** Hủy phân công */
    public function cancel(): void {
        require_role(['ttbm']);

        if (!isset($_GET['maPhanCongRaDe'])) {
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        $this->model->xoaPhanCong((int)$_GET['maPhanCongRaDe']);
        $_SESSION['flash_success'] = 'Đã hủy phân công';
        header('Location: /public/index.php?action=assign_exam');
        exit;
    }
}

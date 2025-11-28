<?php
declare(strict_types=1);
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class AssignExamController {
    private AssignExamModel $model;

    public function __construct() {
        $this->model = new AssignExamModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {

        // Lấy mã tài khoản trong session
        $maTaiKhoan = $_SESSION['auth']['maTaiKhoan'] ?? '';

        // Lấy mã GV của tổ trưởng
        $maGV = $this->model->layMaGVTheoTaiKhoan($maTaiKhoan);

        // Lấy môn
        $monPhuTrach = $this->model->layMonCuaToTruong($maGV);

        if (!$monPhuTrach) {
            $_SESSION['flash_error'] = "Không tìm thấy thông tin tổ trưởng bộ môn.";
            header('Location: index.php');
            exit;
        }

        // Lấy dữ liệu render
        $khoi     = $this->model->getDanhSachKhoi();
        $listGV   = $this->model->getGiaoVienTheoToTruong($monPhuTrach);
        $phanCong = $this->model->getDanhSachPhanCong($monPhuTrach);

        include __DIR__ . '/../../views/ttbm/assign_exam.php';
    }

    public function store(): void {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // Lấy form
        $khoi      = trim($_POST['khoi'] ?? '');
        $hocKy     = trim($_POST['hocKy'] ?? '');
        $kyThi     = trim($_POST['kyThi'] ?? '');
        $soLuongDe = (int)($_POST['soLuongDe'] ?? 0);
        $thoiHan   = trim($_POST['thoiHan'] ?? '');
        $ghiChu    = trim($_POST['ghiChu'] ?? '');
        $listGV    = $_POST['listGV'] ?? [];

        // Validate
        if ($khoi === '') {
            $_SESSION['flash_error'] = "Vui lòng chọn khối.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        if ($hocKy === '' || $kyThi === '') {
            $_SESSION['flash_error'] = "Vui lòng chọn Học kỳ và Kỳ thi.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        if ($soLuongDe <= 0) {
            $_SESSION['flash_error'] = "Số lượng đề không hợp lệ.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        if ($thoiHan === '' || strtotime($thoiHan) < time()) {
            $_SESSION['flash_error'] = "Thời hạn không hợp lệ.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        if (empty($listGV)) {
            $_SESSION['flash_error'] = "Vui lòng chọn giáo viên.";
            header('Location: index.php?action=assign_exam');
            exit;
        }

        // Lưu
        $ok = $this->model->luuPhanCongNhieuGV(
            $listGV,
            $khoi,
            $hocKy,
            $kyThi,
            $soLuongDe,
            $thoiHan,
            $ghiChu
        );

        $_SESSION['flash_' . ($ok ? 'success' : 'error')] =
            $ok ? "Phân công thành công." : "Không thể lưu phân công.";

        header('Location: index.php?action=assign_exam');
        exit;
    }
}

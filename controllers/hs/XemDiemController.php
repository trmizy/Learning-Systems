<?php
/**
 * Controller: Xem điểm (Học sinh)
 * Path: controllers/hs/XemDiemController.php
 * Xử lý logic nghiệp vụ và gọi View
 */

require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/hs/DiemModel.php';

class XemDiemController {
    private $model;

    public function __construct() {
        $this->model = new DiemModel();
    }

    public function index() {
        // Kiểm tra đăng nhập và quyền
        require_role(['hs']);

        // Lấy thông tin user hiện tại
        $user = $_SESSION['auth'];

        // Lấy mã học sinh từ username/email
        $maHS = null;
        if ($user && isset($user['username'])) {
            $maHS = $this->model->getMaHocSinhByUsername($user['username']);
        }

        // Nếu không tìm thấy, dùng mã demo
        if (!$maHS) {
            $maHS = 'HS0000'; // Mã demo để test
        }

        // Lấy danh sách năm học
        $danhSachNamHoc = $this->model->getAllNamHoc();

        // Lấy tham số lọc từ URL (mặc định là năm học và học kỳ hiện tại)
        $namHocHienTai = date('Y') . '-' . (date('Y') + 1); // VD: 2024-2025
        $hocKyHienTai = (date('n') >= 1 && date('n') <= 5) ? 'HK2' : 'HK1';

        $namHoc = isset($_GET['namHoc']) ? $_GET['namHoc'] : $namHocHienTai;
        $hocKy = isset($_GET['hocKy']) ? $_GET['hocKy'] : $hocKyHienTai;

        // Validate học kỳ
        $validHocKy = ['HK1', 'HK2', 'Cả năm'];
        if (!in_array($hocKy, $validHocKy)) {
            $hocKy = $hocKyHienTai;
        }

        // Lấy thông tin học sinh
        $thongTinHS = $this->model->getThongTinHocSinh($maHS);

        // Lấy điểm đầy đủ (bao gồm môn chưa có điểm)
        $danhSachDiem = $this->model->getDiemHocSinhDayDu($maHS, $namHoc, $hocKy);

        // Tính điểm trung bình chung
        $diemTBC = $this->model->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);

        // Gọi View để hiển thị
        require_once __DIR__ . '/../../views/hs/xem_diem.php';
    }
}

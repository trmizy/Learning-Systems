<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/DiemModel.php';

class DiemController {
    private $diemModel;

    public function __construct() {
        $this->diemModel = new DiemModel();
    }

    /**
     * Xem điểm của con (dành cho phụ huynh)
     */
    public function xemDiemPhuHuynh() {
        // Kiểm tra quyền
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã phụ huynh
        $maPH = $this->diemModel->getMaPhuHuynhByUsername($user['username']);
        if (!$maPH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách con
        $danhSachCon = $this->diemModel->getDanhSachConCuaPhuHuynh($maPH);
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy học sinh được chọn (mặc định là con đầu tiên)
        $maHS = $_GET['maHS'] ?? $danhSachCon[0]['maHS'];

        // Lấy thông tin học sinh
        $thongTinHS = $this->diemModel->getThongTinHocSinh($maHS);
        
        // Lấy năm học và học kỳ
        $namHoc = $_GET['namHoc'] ?? $thongTinHS['namHoc'] ?? '2024-2025';
        $hocKy = $_GET['hocKy'] ?? 'HK1';

        // Lấy bảng điểm
        $bangDiem = $this->diemModel->getBangDiemHocSinh($maHS, $hocKy, $namHoc);
        
        // Tính điểm trung bình
        $diemTB = $this->tinhDiemTrungBinh($bangDiem);

        // Truyền biến cho view
        $pageTitle = 'Bảng điểm con em - Phụ huynh';
        
        // Load view
        require_once __DIR__ . '/../../views/ph/xem_diem.php';
    }

    /**
     * Tính điểm trung bình
     */
    private function tinhDiemTrungBinh($bangDiem) {
        if (empty($bangDiem)) {
            return 0;
        }

        $tongDiem = 0;
        $soMon = count($bangDiem);

        foreach ($bangDiem as $mon) {
            $diemTB = ($mon['diemThuongXuyen'] + $mon['diemGiuaKy'] + $mon['diemCuoiKy'] * 2) / 4;
            $tongDiem += $diemTB;
        }

        return round($tongDiem / $soMon, 2);
    }
}

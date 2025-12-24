<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/hs/DiemHSModel.php';

class DiemController {
    private $diemModel;

    public function __construct() {
        $this->diemModel = new DiemModel();
    }

    /**
     * Xem điểm của học sinh - SỬA LẠI HOÀN TOÀN
     */
    public function xemDiem() {
        require_role(['hs']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập';
            header('Location: /public/index.php');
            exit;
        }

        $maHS = $this->diemModel->getMaHocSinhByUsername($user['username']);
        if (!$maHS) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin học sinh';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy thông tin học sinh
        $thongTinHS = $this->diemModel->getThongTinHocSinh($maHS);
        
        // Lấy năm học và học kỳ từ GET hoặc mặc định
        $namHoc = $_GET['namHoc'] ?? ($thongTinHS['namHoc'] ?? '2024-2025');
        $hocKy = $_GET['hocKy'] ?? 'HK1';

        // Lấy danh sách năm học (cho dropdown)
        $danhSachNamHoc = $this->diemModel->getAllNamHoc();
        
        // Danh sách học kỳ hợp lệ
        $validHocKy = ['HK1', 'HK2', 'Cả năm'];

        // Lấy bảng điểm THEO VIEW - TRẢ VỀ PDOStatement
        $bangDiem = $this->diemModel->getDiemHocSinh($maHS, $namHoc, $hocKy);
        
        // ⚠️ FIX: Gán lại biến để view nhận diện
        $danhSachDiem = $bangDiem; // View đang dùng biến này
        
        // Tính điểm trung bình chung
        $diemTBC = $this->diemModel->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);

        // Truyền biến cho view
        $pageTitle = 'Bảng điểm - Học sinh';
        
        // Load view
        require_once __DIR__ . '/../../views/hs/xem_diem.php';
    }

    /**
     * Xem điểm của con (phụ huynh) - SỬ DỤNG VIEW v_diem_phuhuynh
     */
    public function xemDiemPhuHuynh() {
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập';
            header('Location: /public/index.php');
            exit;
        }

        $maPH = $this->diemModel->getMaPhuHuynhByUsername($user['username']);
        if (!$maPH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách con từ VIEW v_diem_phuhuynh
        $danhSachCon = $this->diemModel->getDanhSachConCuaPhuHuynh($maPH);
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em';
            header('Location: /public/index.php');
            exit;
        }

        $maHS = $_GET['maHS'] ?? $danhSachCon[0]['maHS'];
        $this->hienThiBangDiem($maHS, $danhSachCon, $maPH);
    }

    /**
     * Logic chung hiển thị bảng điểm - FIX: Truyền đúng biến cho view
     */
    private function hienThiBangDiem($maHocSinh, $danhSachCon = null, $maPH = null) {
        // Lấy thông tin từ VIEW v_diem_hocsinh
        $thongTinHS = $this->diemModel->getThongTinHocSinh($maHocSinh);
        
        $namHoc = $_GET['namHoc'] ?? $thongTinHS['namHoc'] ?? '2024-2025';
        $hocKy = $_GET['hocKy'] ?? 'HK1';

        // Nếu là phụ huynh, dùng VIEW v_diem_phuhuynh
        if ($maPH) {
            $bangDiem = $this->diemModel->getBangDiemConCuaPhuHuynh($maPH, $maHocSinh, $hocKy, $namHoc);
        } else {
            // Nếu là học sinh, dùng VIEW v_diem_hocsinh
            $bangDiem = $this->diemModel->getBangDiemHocSinh($maHocSinh, $hocKy, $namHoc);
        }

        $diemTB = $this->tinhDiemTrungBinh($bangDiem);

        // ⚠️ FIX: Gán lại biến $maHS để view nhận diện được
        $maHS = $maHocSinh;

        $pageTitle = 'Bảng điểm - ' . ($danhSachCon ? 'Phụ huynh' : 'Học sinh');
        require_once __DIR__ . '/../../views/hs/xem_diem.php';
    }

    /**
     * Tính điểm trung bình - Sử dụng diemTrungBinhMon từ VIEW
     */
    private function tinhDiemTrungBinh($bangDiem) {
        if (empty($bangDiem)) {
            return 0;
        }

        $tongDiem = 0;
        $soMon = 0;

        foreach ($bangDiem as $mon) {
            // Ưu tiên dùng diemTrungBinhMon từ VIEW
            if (isset($mon['diemTrungBinhMon']) && $mon['diemTrungBinhMon'] !== null) {
                $tongDiem += $mon['diemTrungBinhMon'];
                $soMon++;
            } elseif (isset($mon['diemThuongXuyen'], $mon['diemGiuaKy'], $mon['diemCuoiKy'])) {
                $diemTB = ($mon['diemThuongXuyen'] + $mon['diemGiuaKy'] + $mon['diemCuoiKy'] * 2) / 4;
                $tongDiem += $diemTB;
                $soMon++;
            }
        }

        return $soMon > 0 ? round($tongDiem / $soMon, 2) : 0;
    }
}

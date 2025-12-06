<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/hs/ThoiKhoaBieuModel.php';

class ThoiKhoaBieuController {
    private $model;

    public function __construct() {
        $this->model = new ThoiKhoaBieuModel();
    }

    /**
     * Hiển thị thời khóa biểu cho phụ huynh
     */
    public function indexPhuHuynh() {
        // Kiểm tra quyền
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được phụ huynh đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã phụ huynh
        $maPhuHuynh = $this->model->getMaPhuHuynhByUsername($user['username']);
        if (!$maPhuHuynh) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách con
        $danhSachCon = $this->model->getDanhSachConCuaPhuHuynh($maPhuHuynh);
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy học sinh được chọn (mặc định là con đầu tiên)
        $maHocSinhChon = $_GET['maHocSinh'] ?? $danhSachCon[0]['maHocSinh'];

        $this->hienThiThoiKhoaBieu($maHocSinhChon, $danhSachCon);
    }

    /**
     * Logic chung hiển thị thời khóa biểu
     */
    private function hienThiThoiKhoaBieu($maHocSinh, $danhSachCon = null) {
        // Lấy tuần được chọn
        $selectedDate = $_GET['week'] ?? date('Y-m-d');
        $timestamp = strtotime($selectedDate);
        
        // Tính thứ 2 của tuần
        $dayOfWeek = date('N', $timestamp);
        $monday = date('Y-m-d', strtotime("-" . ($dayOfWeek - 1) . " days", $timestamp));
        $sunday = date('Y-m-d', strtotime("+6 days", strtotime($monday)));

        // Navigation
        $prevWeekDate = date('Y-m-d', strtotime('-7 days', strtotime($monday)));
        $nextWeekDate = date('Y-m-d', strtotime('+7 days', strtotime($monday)));

        // Lấy mã lớp từ mã học sinh
        $maLop = $this->model->getMaLopByMaHocSinh($maHocSinh);
        
        if (!$maLop) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin lớp học.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy TKB theo lớp
        $thoiKhoaBieu = $this->model->getThoiKhoaBieuTheoLop($maLop, $monday, $sunday);
        $thongTinLop = $this->model->getThongTinLopHocSinh($maHocSinh);

        // Tạo grid TKB
        $tkbGrid = $this->taoGridTKB($thoiKhoaBieu);
        $daysOfWeek = $this->getDaysOfWeek($monday);
        $tuanHienTai = date('d/m/Y', strtotime($monday)) . ' - ' . date('d/m/Y', strtotime($sunday));
        
        // Biến cho view
        $selected_date = $selectedDate;

        // Render view
        require_once __DIR__ . '/../../views/shared/thoi_khoa_bieu_hs.php';
    }

    /**
     * Tạo grid thời khóa biểu [ngày][tiết]
     */
    private function taoGridTKB($thoiKhoaBieu) {
        $grid = [];
        foreach ($thoiKhoaBieu as $tiet) {
            $ngay = (int)$tiet['thuTrongTuan'];
            if ($ngay == 1) $ngay = 8;
            $ngay = $ngay - 1;

            $tietHoc = (int)$tiet['tietHoc'];
            $grid[$ngay][$tietHoc] = $tiet;
        }
        return $grid;
    }

    /**
     * Lấy danh sách ngày trong tuần
     */
    private function getDaysOfWeek($monday) {
        $days = [];
        $daysName = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days", strtotime($monday)));
            $days[] = [
                'name' => $daysName[$i],
                'date' => date('d/m', strtotime($date))
            ];
        }
        return $days;
    }
}

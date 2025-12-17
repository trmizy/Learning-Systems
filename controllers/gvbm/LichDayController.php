<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/gvbm/LichDayModel.php';

class LichDayController {
    private $model;

    public function __construct() {
        $this->model = new LichDayModel();
    }

    /**
     * Hiển thị lịch dạy của giáo viên
     */
    public function index() {
        require_role(['gvbm', 'gvcn', 'ttbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Phiên đăng nhập hết hạn.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy mã GV từ Model
        $maGV = $this->model->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php?action=gvbm-dashboard');
            exit;
        }

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

        // Lấy lịch dạy từ Model
        $lichDay = $this->model->getLichDayTheoTuan($maGV, $monday, $sunday);
        
        // Thông tin giáo viên từ Model
        $thongTinGV = $this->model->getThongTinGiaoVien($maGV);
        
        // Tạo grid lịch dạy
        $lichDayGrid = $this->taoGridLichDay($lichDay);
        $daysOfWeek = $this->getDaysOfWeek($monday);
        $tuanHienTai = date('d/m/Y', strtotime($monday)) . ' - ' . date('d/m/Y', strtotime($sunday));
        
        // Biến cho view
        $selected_date = $selectedDate;

        // Render view
        require_once __DIR__ . '/../../views/gvbm/lich_day.php';
    }

    /**
     * Tạo grid lịch dạy [ngày][tiết]
     */
    private function taoGridLichDay($lichDay) {
        $grid = [];
        foreach ($lichDay as $tiet) {
            $ngay = (int)$tiet['thuTrongTuan'];
            if ($ngay == 1) $ngay = 8; // CN về cuối
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

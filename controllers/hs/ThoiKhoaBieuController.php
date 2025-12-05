<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/hs/ThoiKhoaBieuModel.php';

class ThoiKhoaBieuController {
    private $model;

    public function __construct() {
        $this->model = new ThoiKhoaBieuModel();
    }

    /**
     * Hiển thị thời khóa biểu cho học sinh
     */
    public function indexHocSinh() {
        // Kiểm tra quyền - ĐÚNG TÊN ROLE
        require_role(['hs']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được học sinh đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã học sinh
        $maHocSinh = $this->model->getMaHocSinhByUsername($user['username']);
        
        // === DEBUG LOG ===
        error_log("=== DEBUG indexHocSinh ===");
        error_log("Username: " . $user['username']);
        error_log("maHocSinh: " . ($maHocSinh ?: 'NULL'));
        // === END DEBUG ===
        
        if (!$maHocSinh) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin học sinh.';
            header('Location: /public/index.php');
            exit;
        }

        $this->hienThiThoiKhoaBieu($maHocSinh);
    }

    /**
     * Hiển thị thời khóa biểu cho phụ huynh
     */
    public function indexPhuHuynh() {
        // Kiểm tra quyền - ĐÚNG TÊN ROLE
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
     * Logic chung hiển thị thời khóa biểu - TỐI ƯU
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

        // BƯỚC 1: Lấy mã lớp từ mã học sinh
        $maLop = $this->model->getMaLopByMaHocSinh($maHocSinh);
        
        // === DEBUG LOG CHI TIẾT ===
        error_log("=== DEBUG hienThiThoiKhoaBieu ===");
        error_log("maHocSinh: $maHocSinh");
        error_log("maLop: " . ($maLop ?: 'NULL'));
        error_log("monday: $monday");
        error_log("sunday: $sunday");
        
        // Kiểm tra xem học sinh thuộc lớp nào
        if ($maLop) {
            error_log("Query SQL sẽ chạy:");
            error_log("SELECT ... FROM thoikhoabieu WHERE maLop = '$maLop' AND ngayHoc BETWEEN '$monday' AND '$sunday'");
        }
        // === END DEBUG ===
        
        if (!$maLop) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin lớp học.';
            header('Location: /public/index.php');
            exit;
        }

        // BƯỚC 2: Lấy TKB THEO LỚP
        $thoiKhoaBieu = $this->model->getThoiKhoaBieuTheoLop($maLop, $monday, $sunday);
        
        // === DEBUG RESULT ===
        error_log("Số bản ghi TKB trả về: " . count($thoiKhoaBieu));
        if (count($thoiKhoaBieu) > 0) {
            error_log("Bản ghi đầu tiên: " . print_r($thoiKhoaBieu[0], true));
        } else {
            error_log("KHÔNG CÓ DỮ LIỆU TKB!");
            
            // Kiểm tra ngược lại: Có dữ liệu trong DB không?
            try {
                $db = Database::getInstance()->getConnection();
                $checkStmt = $db->prepare("SELECT COUNT(*) as total FROM thoikhoabieu WHERE maLop = ?");
                $checkStmt->execute([$maLop]);
                $totalRows = $checkStmt->fetch()['total'];
                error_log("Tổng số bản ghi TKB trong DB cho lớp $maLop: $totalRows");
            } catch (Exception $e) {
                error_log("Lỗi kiểm tra DB: " . $e->getMessage());
            }
        }
        // === END DEBUG ===
        
        $thongTinLop = $this->model->getThongTinLopHocSinh($maHocSinh);

        // Tạo grid TKB - GIỐNG GVCN
        $tkbGrid = $this->taoGridTKB($thoiKhoaBieu);
        $daysOfWeek = $this->getDaysOfWeek($monday);
        $tuanHienTai = date('d/m/Y', strtotime($monday)) . ' - ' . date('d/m/Y', strtotime($sunday));
        
        // Biến cho view
        $selected_date = $selectedDate;

        // Render view - FIX: Sửa đường dẫn
        require_once __DIR__ . '/../../views/shared/thoi_khoa_bieu_hs.php';
    }

    /**
     * Tạo grid thời khóa biểu [ngày][tiết]
     */
    private function taoGridTKB($thoiKhoaBieu) {
        $grid = [];
        foreach ($thoiKhoaBieu as $tiet) {
            $ngay = (int)$tiet['thuTrongTuan']; // 1=CN, 2=T2, ..., 7=T7
            if ($ngay == 1) $ngay = 8; // Chuyển CN về cuối tuần
            $ngay = $ngay - 1; // Điều chỉnh: T2=1, T3=2,...

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

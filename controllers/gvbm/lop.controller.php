<?php
// File: controllers/gvbm/LopController.php
// (ĐÃ CẬP NHẬT - THÊM NÚT QUA LẠI TUẦN)

require_once __DIR__ . '/../../models/gvbm/lop.model.php'; 
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class LopController {
    
    private $lopModel;

    public function __construct() {
        $this->lopModel = new LopModel();
    }

    /**
     * Action 1: Hiển thị trang "Xem thông tin lớp phụ trách" (3 tab)
     */
    public function xemLopChuNhiem() {
        require_role(['gvcn']); 
        $user = current_user();
        $maGiaoVien = $user['teacher_id'] ?? ''; 

        $thongTinLop = $this->lopModel->getThongTinLopChuNhiemByMaGV($maGiaoVien);

        // Khai báo biến
        $danhSachHocSinh = $danhSachMonHoc = $bangDiemLookup = $tkbGrid = [];
        $tuanHienTai = '';
        $selected_date = '';
        $daysOfWeek = []; 
        $prevWeekDate = ''; // <-- Biến mới
        $nextWeekDate = ''; // <-- Biến mới

        if (!$thongTinLop) {
            $tenLop = "Chưa được phân công";
            $tenGiaoVien = htmlspecialchars($user['full_name'] ?? 'Giáo viên');
            $error_message = "Giáo viên chưa được phân công chủ nhiệm lớp nào.";
        } else {
            // Lấy dữ liệu cơ bản
            $tenLop = $thongTinLop['tenLop'];
            $tenGiaoVien = $thongTinLop['tenGiaoVien'];
            $maLop = $thongTinLop['maLop'];
            
            // --- Tab 1: Danh sách học sinh ---
            $danhSachHocSinh = $this->lopModel->getDanhSachHocSinhByLopId($maLop);
            
            // --- Tab 2: Bảng điểm ---
            $danhSachMonHoc = $this->lopModel->getDanhSachMonHoc($maLop);
            $diemTho = $this->lopModel->getBangDiemTho($maLop);
            foreach ($diemTho as $diem) {
                $bangDiemLookup[$diem['maHS']][$diem['maMonHoc']] = $diem;
            }
            
            // --- Tab 3: Thời khóa biểu ---
            $selected_date = $_GET['week'] ?? date('Y-m-d');
            $dateObj = new DateTime($selected_date, new DateTimeZone('Asia/Ho_Chi_Minh'));
            
            $ngayTrongTuan = (int) $dateObj->format('N'); 
            $startDateObj = (clone $dateObj)->modify('-' . ($ngayTrongTuan - 1) . ' days');
            $endDateObj = (clone $dateObj)->modify('+' . (7 - $ngayTrongTuan) . ' days');
            
            $startDate = $startDateObj->format('Y-m-d');
            $endDate = $endDateObj->format('Y-m-d');
            $tuanHienTai = $startDateObj->format('d/m') . ' - ' . $endDateObj->format('d/m/Y');

            // === 🚀 CODE MỚI: TÍNH NGÀY CHO NÚT QUA LẠI TUẦN ===
            // (Lấy ngày Thứ 2 của tuần trước và tuần sau)
            $prevWeekDate = (clone $startDateObj)->modify('-7 days')->format('Y-m-d');
            $nextWeekDate = (clone $startDateObj)->modify('+7 days')->format('Y-m-d');
            // === KẾT THÚC CODE MỚI ===

            // Tạo mảng 7 ngày của tuần
            $daysOfWeek = [];
            $currentDay = clone $startDateObj;
            $dayNames = ["", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7", "Chủ Nhật"];
            for ($i = 1; $i <= 7; $i++) {
                $daysOfWeek[$i] = [
                    'name' => $dayNames[$i],
                    'date' => $currentDay->format('d/m/Y')
                ];
                $currentDay->modify('+1 day');
            }

            // Lấy TKB và xây dựng Grid
            $tkbTho = $this->lopModel->getThoiKhoaBieuByWeek($maLop, $startDate, $endDate);
            foreach ($tkbTho as $tietHoc) {
                $ngayTrongTuanKey = (int) (new DateTime($tietHoc['ngayHoc']))->format('N');
                $tietKey = (int) $tietHoc['tiet'];
                $tkbGrid[$ngayTrongTuanKey][$tietKey] = [
                    'tenMon' => $tietHoc['tenMon'],
                    'tenPhong' => $tietHoc['tenPhong']
                ];
            }
        }

        // Xác định tab nào đang active
        $activeTab = 'danhsach'; 
        if (isset($_GET['week'])) $activeTab = 'tkb';
        if (isset($_GET['tab']) && $_GET['tab'] == 'diem') $activeTab = 'diem';
        
        $pageTitle = 'Thông tin lớp chủ nhiệm';
        require_once __DIR__ . '/../../views/layouts/header.php';
        // Truyền $prevWeekDate và $nextWeekDate sang View
        require_once __DIR__ . '/../../views/gvbm/xem_lop_phu_trach.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }

    /**
     * Action 2: Hiển thị trang "Xem chi tiết 1 học sinh"
     */
    public function xemChiTietHocSinh() {
        require_role(['gvcn', 'gvbm', 'ttbm']); 
        
        $maHS = $_GET['id'] ?? null;
        if (!$maHS) {
            $_SESSION['flash_error'] = "Không có mã học sinh được cung cấp.";
            header('Location: index.php?action=xem_lop_cn'); 
            exit;
        }
        
        $hocSinh = $this->lopModel->getChiTietHocSinh($maHS); 
        if (!$hocSinh) {
            $_SESSION['flash_error'] = "Không tìm thấy học sinh với mã " . htmlspecialchars($maHS);
            header('Location: index.php?action=xem_lop_cn');
            exit;
        }
        
        $pageTitle = 'Chi tiết học sinh - ' . $hocSinh['hoTen'];
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/gvbm/xem_chi_tiet_hoc_sinh.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }
}
?>
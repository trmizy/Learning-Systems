<?php
// File: controllers/gvbm/LopController.php
require_once __DIR__ . '/../../models/gvbm/lop.model.php'; 
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class LopController {
    public function xemLopChuNhiem() {
        require_role(['gvcn', 'gvbm']); 
        
        $user = current_user();
        $model = new LopModel();

        // =================================================================
        // 🚀 BƯỚC QUAN TRỌNG: Lấy maGV từ username (Thay vì lấy từ session)
        // =================================================================
        $username = $user['username'] ?? ''; // Lấy username từ Auth
        $maGiaoVien = $model->getMaGVByUsername($username); // Gọi hàm mới trong Model

        // Kiểm tra nếu không tìm thấy giáo viên
        if (!$maGiaoVien) {
            $_SESSION['flash_error'] = "Không tìm thấy hồ sơ giáo viên. Vui lòng liên hệ Admin.";
            $info = null;
        } else {
            // Có maGV rồi mới lấy thông tin lớp
            $info = $model->getThongTinLopChuNhiemByMaGV($maGiaoVien);
        }

        // --- (Phần code bên dưới giữ nguyên logic hiển thị) ---
        $tenLop = ''; $tenGiaoVien = ''; 
        $danhSachHocSinh = []; $danhSachMonHoc = []; $bangDiemLookup = []; 
        $tkbGrid = []; $daysOfWeek = [];
        $tuanHienTai = '';

        if (!$info) {
            $error_message = "Bạn chưa được phân công chủ nhiệm lớp nào.";
        } else {
            $tenLop = $info['tenLop'];
            $tenGiaoVien = $info['tenGiaoVien'];
            $maLop = $info['maLop'];

            // 1. Lấy danh sách HS
            $danhSachHocSinh = $model->getDanhSachHocSinhByLopId($maLop);
            
            // 2. Lấy danh sách Môn & Điểm
            $danhSachMonHoc = $model->getDanhSachMonHoc($maLop);
            $diemRaw = $model->getBangDiemTho($maLop);
            foreach ($diemRaw as $d) {
                $bangDiemLookup[$d['maHS']][$d['maMonHoc']] = $d;
            }

            // 3. Xử lý TKB
            $selected_date = $_GET['week'] ?? date('Y-m-d');
            try { $dateObj = new DateTime($selected_date); } catch(Exception $e) { $dateObj = new DateTime(); }
            
            // Tìm ngày đầu tuần (Thứ 2) và cuối tuần (CN)
            $ngayTrongTuan = (int)$dateObj->format('N'); // 1=T2 ... 7=CN
            $startObj = (clone $dateObj)->modify('-' . ($ngayTrongTuan - 1) . ' days');
            $endObj   = (clone $dateObj)->modify('+' . (7 - $ngayTrongTuan) . ' days');
            
            $startDate = $startObj->format('Y-m-d');
            $endDate   = $endObj->format('Y-m-d');
            $tuanHienTai = $startObj->format('d/m') . ' - ' . $endObj->format('d/m/Y');

            $curr = clone $startObj;
            // Khai báo tên các thứ trong tuần
            $dayNames = ["", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7", "Chủ Nhật"];
            
            for ($i = 1; $i <= 7; $i++) {
                $daysOfWeek[$i] = [
                    'name' => $dayNames[$i], // <--- Đã thêm lại key 'name'
                    'date' => $curr->format('d/m')
                ];
                $curr->modify('+1 day');
            }

            // Lấy dữ liệu TKB từ Model
            $tkbData = $model->getThoiKhoaBieuByWeek($maLop, $startDate, $endDate);
            
            foreach ($tkbData as $row) {
                // SQL DAYOFWEEK: 1=CN, 2=T2 ... 7=T7
                $sqlDay = (int)$row['thu_trong_tuan'];
                
                // Convert sang hệ 1=T2 ... 7=CN của view
                $phpDay = ($sqlDay == 1) ? 7 : ($sqlDay - 1);

                $tiet = (int)$row['tiet'];
                $tkbGrid[$phpDay][$tiet] = [
                    'tenMon'   => $row['tenMon'],
                    'tenPhong' => $row['tenPhong']
                ];
            }
        }

        // Setup Tab active
        $activeTab = 'danhsach';
        if (isset($_GET['week']) || (isset($_GET['tab']) && $_GET['tab'] == 'tkb')) $activeTab = 'tkb';
        elseif (isset($_GET['tab']) && $_GET['tab'] == 'diem') $activeTab = 'diem';

        // Điều hướng tuần
        $prevWeekDate = (isset($startObj) ? (clone $startObj)->modify('-7 days')->format('Y-m-d') : '');
        $nextWeekDate = (isset($startObj) ? (clone $startObj)->modify('+7 days')->format('Y-m-d') : '');

        $pageTitle = 'Lớp chủ nhiệm';
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/gvbm/xem_lop_phu_trach.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }

    public function xemChiTietHocSinh() {
        require_role(['gvcn', 'gvbm']);
        $maHS = $_GET['id'] ?? '';
        $model = new LopModel();
        $hocSinh = $model->getChiTietHocSinh($maHS);
        
        if (!$hocSinh) { header('Location: index.php?action=xem_lop_cn'); exit; }
        
        $pageTitle = 'Chi tiết HS';
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/gvbm/xem_chi_tiet_hoc_sinh.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }
}
?>
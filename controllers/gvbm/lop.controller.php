<?php
// File: controllers/gvbm/LopController.php
require_once __DIR__ . '/../../models/gvbm/lop.model.php'; 
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class LopController {
    public function xemLopChuNhiem() {
        // Lưu ý: Trong CSDL mới, GVCN chỉ có role là 'gvbm', nên ta phải cho phép 'gvbm' truy cập
        require_role(['gvcn', 'gvbm']); 
        
        $user = current_user();
        $maGiaoVien = $user['teacher_id'] ?? ''; 
        
        $model = new LopModel();
        $info = $model->getThongTinLopChuNhiemByMaGV($maGiaoVien);

        // Khởi tạo biến mặc định để tránh lỗi Undefined variable
        $tenLop = ''; $tenGiaoVien = ''; 
        $danhSachHocSinh = []; $danhSachMonHoc = []; $bangDiemLookup = []; 
        $tkbGrid = []; $daysOfWeek = [];
        $tuanHienTai = ''; $selected_date = date('Y-m-d');
        $prevWeekDate = ''; $nextWeekDate = '';

        if (!$info) {
            $error_message = "Bạn chưa được phân công chủ nhiệm lớp nào.";
        } else {
            $tenLop = $info['tenLop'];
            $tenGiaoVien = $info['tenGiaoVien'];
            $maLop = $info['maLop'];

            // Tab 1 & 2:
            $danhSachHocSinh = $model->getDanhSachHocSinhByLopId($maLop);
            $danhSachMonHoc = $model->getDanhSachMonHoc($maLop);
            $diemRaw = $model->getBangDiemTho($maLop);
            foreach ($diemRaw as $d) {
                $bangDiemLookup[$d['maHS']][$d['maMonHoc']] = $d;
            }

            // Tab 3: TKB (Xử lý ngày tháng)
            $selected_date = $_GET['week'] ?? date('Y-m-d');
            $dateObj = new DateTime($selected_date);
            
            // Tính đầu tuần (T2) và cuối tuần (CN)
            $ngayTrongTuan = (int)$dateObj->format('N'); // 1=T2
            $startObj = (clone $dateObj)->modify('-' . ($ngayTrongTuan - 1) . ' days');
            $endObj   = (clone $dateObj)->modify('+' . (7 - $ngayTrongTuan) . ' days');
            
            $startDate = $startObj->format('Y-m-d');
            $endDate   = $endObj->format('Y-m-d');
            $tuanHienTai = $startObj->format('d/m') . ' - ' . $endObj->format('d/m/Y');

            $prevWeekDate = (clone $startObj)->modify('-7 days')->format('Y-m-d');
            $nextWeekDate = (clone $startObj)->modify('+7 days')->format('Y-m-d');

            // Tạo header bảng TKB
            $curr = clone $startObj;
            $dayNames = ["", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7", "Chủ Nhật"];
            for ($i = 1; $i <= 7; $i++) {
                $daysOfWeek[$i] = ['name' => $dayNames[$i], 'date' => $curr->format('d/m')];
                $curr->modify('+1 day');
            }

            // Lấy dữ liệu TKB và map vào lưới
            $tkbData = $model->getThoiKhoaBieuByWeek($maLop, $startDate, $endDate);
            foreach ($tkbData as $row) {
                // SQL trả về: thu_trong_tuan (1=CN, 2=T2...). PHP: 1=T2, 7=CN
                // Ta cần convert lại cho khớp với vòng lặp view
                $sqlDay = (int)$row['thu_trong_tuan'];
                $phpDay = ($sqlDay == 1) ? 7 : ($sqlDay - 1); // Convert SQL day to PHP ISO-8601 day
                
                $tiet = (int)$row['tiet'];
                $tkbGrid[$phpDay][$tiet] = [
                    'tenMon' => $row['tenMon'],
                    'tenPhong' => $row['tenPhong']
                ];
            }
        }

        $activeTab = 'danhsach';
        if (isset($_GET['week']) || (isset($_GET['tab']) && $_GET['tab'] == 'tkb')) $activeTab = 'tkb';
        elseif (isset($_GET['tab']) && $_GET['tab'] == 'diem') $activeTab = 'diem';

        // Gọi View
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
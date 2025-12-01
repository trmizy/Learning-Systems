<?php
// File: controllers/gvbm/XepLoaiController.php
// (ĐÃ CẬP NHẬT LOGIC ĐỀ XUẤT TỰ ĐỘNG CHUẨN XÁC)

require_once __DIR__ . '/../../models/gvbm/XepLoaiModel.php';
require_once __DIR__ . '/../../models/gvbm/lop.model.php'; 
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class XepLoaiController {
    private $model;
    private $lopModel;

    public function __construct() {
        $this->model = new XepLoaiModel();
        $this->lopModel = new LopModel();
    }

    public function showXepLoaiPage() {
        require_role(['gvcn']);
        $user = current_user();
        $maGV = $user['teacher_id'] ?? '';

        // 1. Lấy thông tin lớp
        // (Sử dụng LopModel để lấy đúng lớp của GVCN)
        $lopInfo = $this->lopModel->getThongTinLopChuNhiemByMaGV($maGV);
        
        if (!$lopInfo) {
            $_SESSION['flash_error'] = "Bạn chưa được phân công chủ nhiệm lớp nào.";
            header('Location: index.php');
            exit;
        }

        $danhSach = $this->model->getDanhSachXepLoai($lopInfo['maLop']);
        $tenLop = $lopInfo['tenLop'];

        // 2. === 🚀 LOGIC TỰ ĐỘNG ĐỀ XUẤT (Theo quy tắc mới) ===
        foreach ($danhSach as &$hs) {
            // --- ĐỀ XUẤT HỌC LỰC ---
            $dtb = is_numeric($hs['diemTrungBinh']) ? (float)$hs['diemTrungBinh'] : -1;
            
            if ($dtb == -1) {
                $hs['auto_HL'] = '-'; // Chưa có điểm
            } elseif ($dtb >= 8.0) {
                $hs['auto_HL'] = 'Giỏi';
            } elseif ($dtb >= 6.5) {
                $hs['auto_HL'] = 'Khá';
            } elseif ($dtb >= 5.0) {
                $hs['auto_HL'] = 'Trung bình';
            } elseif ($dtb >= 3.5) {
                $hs['auto_HL'] = 'Yếu';
            } else {
                $hs['auto_HL'] = 'Kém';
            }

            // --- ĐỀ XUẤT HẠNH KIỂM ---
            // Logic phân cấp: Kiểm tra từ mức thấp nhất (Yếu) lên cao dần
            $nghi = (int)$hs['soBuoiNghiKhongCoPhep'];
            $vipham = (int)$hs['soLanViPham'];
            
            if ($nghi > 5 || $vipham > 3) {
                // Yếu: Nghỉ > 5 HOẶC vi phạm nhiều (>3)
                $hs['auto_HK'] = 'Yếu';
            } elseif ($nghi > 3 || $vipham >= 2) {
                // Trung bình: (Nghỉ 4-5) HOẶC (Vi phạm 2-3)
                // (Đã loại trừ trường hợp Yếu ở trên, nên ở đây nghi <= 5)
                $hs['auto_HK'] = 'Trung bình';
            } elseif ($nghi > 1 || $vipham == 1) {
                // Khá: (Nghỉ 2-3) HOẶC (Vi phạm 1)
                // (Đã loại trừ TB/Yếu, nên ở đây nghi <= 3 và vipham <= 1)
                $hs['auto_HK'] = 'Khá';
            } else {
                // Tốt: Còn lại (Nghỉ <= 1 VÀ Vi phạm = 0)
                $hs['auto_HK'] = 'Tốt';
            }
        }

        // 3. Xử lý lọc (nếu có)
        $filterHL = $_GET['filter_hl'] ?? '';
        $filterHK = $_GET['filter_hk'] ?? '';
        if ($filterHL || $filterHK) {
            $danhSach = array_filter($danhSach, function($hs) use ($filterHL, $filterHK) {
                // So sánh với kết quả đã lưu (hoặc đề xuất nếu chưa lưu)
                $currentHL = $hs['xepLoaiHocLuc'] ?: $hs['auto_HL'];
                $currentHK = $hs['loaiHanhKiem'] ?: $hs['auto_HK'];
                
                $matchHL = empty($filterHL) || ($currentHL == $filterHL);
                $matchHK = empty($filterHK) || ($currentHK == $filterHK);
                return $matchHL && $matchHK;
            });
        }

        $pageTitle = "Xếp loại - $tenLop";
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/gvbm/xep_loai.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }

    public function saveXepLoai() {
        require_role(['gvcn']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
            $count = 0;
            foreach ($_POST['data'] as $maHS => $val) {
                $hl = $val['hl'];
                $hk = $val['hk'];
                $nhanXet = $val['nhanXet']; // Lấy nhận xét từ form
                
                if ($this->model->updateXepLoai($maHS, $hl, $hk, $nhanXet)) {
                    $count++;
                }
            }
            $_SESSION['flash_success'] = "Đã lưu kết quả xếp loại cho $count học sinh.";
        }
        
        header('Location: index.php?action=xep_loai');
        exit;
    }
}
?>
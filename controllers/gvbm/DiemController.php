<?php
require_once __DIR__ . '/../../models/gvbm/diemModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class DiemController {
    private $model;

    public function __construct() {
        $this->model = new DiemModel();
    }

    // Hiển thị form yêu cầu (Danh sách điểm)
    // Hiển thị form yêu cầu & Lịch sử
    public function showYeuCauForm() {
        require_role(['gvbm', 'gvcn', 'ttbm']); 
        $user = current_user();
        $maGV = $user['teacher_id'] ?? '';

        $danhSachLop = $this->model->getLopGiangDay($maGV);
        $bangDiem = [];
        $lichSuYeuCau = []; // <-- Biến mới
        
        $selectedLop = $_GET['maLop'] ?? '';
        $selectedMon = $_GET['maMon'] ?? '';

        if ($selectedLop && $selectedMon) {
            $canAccess = false;
            foreach ($danhSachLop as $l) {
                if ($l['maLop'] == $selectedLop && $l['maMonHoc'] == $selectedMon) {
                    $canAccess = true; break;
                }
            }
            
            if ($canAccess) {
                // 1. Lấy bảng điểm để sửa
                $bangDiem = $this->model->getBangDiemLop($selectedLop, $selectedMon);
                // 2. Lấy lịch sử yêu cầu để xem trạng thái
                $lichSuYeuCau = $this->model->getLichSuYeuCau($selectedLop, $selectedMon);
            } else {
                $_SESSION['flash_error'] = "Bạn không được phân công giảng dạy lớp/môn này.";
            }
        }

        $pageTitle = "Yêu cầu sửa điểm";
        require_once __DIR__ . '/../../views/layouts/header.php';
        require_once __DIR__ . '/../../views/gvbm/yeu_cau_sua_diem.php';
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }

    // Xử lý gửi yêu cầu (POST)
    public function submitYeuCau() {
        require_role(['gvbm', 'gvcn', 'ttbm']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maBangDiem = $_POST['maBangDiem'] ?? '';
            $loaiDiem = $_POST['loaiDiem'] ?? ''; // diemThuongXuyen, diemGiuaKy...
            $diemCu = $_POST['diemCu'] ?? 0;
            $diemMoi = $_POST['diemMoi'] ?? '';
            $lyDo = trim($_POST['lyDo'] ?? '');
            $tenMon = $_POST['tenMon'] ?? 'Môn học';
            
            // Validation (Basic Flow & Alternative Flow)
            if ($diemMoi === '' || $lyDo === '') { // 4.1 Dữ liệu thiếu
                $_SESSION['flash_error'] = "Vui lòng nhập điểm mới và lý do.";
            } elseif (!is_numeric($diemMoi) || $diemMoi < 0 || $diemMoi > 10) { // 4.1 Sai định dạng
                $_SESSION['flash_error'] = "Điểm mới không hợp lệ (phải từ 0-10).";
            } elseif ((float)$diemMoi == (float)$diemCu) { // 6.1 Điểm trùng
                $_SESSION['flash_error'] = "Điểm đề nghị sửa phải khác điểm hiện tại.";
            } elseif ($this->model->checkYeuCauPending($maBangDiem, $loaiDiem)) {
                $_SESSION['flash_error'] = "Đang có yêu cầu chờ duyệt cho ô điểm này rồi.";
            } else {
                // 10. Lưu yêu cầu
                $result = $this->model->taoYeuCauSuaDiem($maBangDiem, $loaiDiem, $diemCu, $diemMoi, $lyDo, $tenMon);
                if ($result) {
                    $_SESSION['flash_success'] = "Gửi yêu cầu sửa điểm thành công! Đang chờ BGH duyệt.";
                } else {
                    $_SESSION['flash_error'] = "Có lỗi xảy ra khi lưu yêu cầu.";
                }
            }
            
            // Quay lại trang cũ với tham số lọc
            $maLop = $_POST['maLop'] ?? '';
            $maMon = $_POST['maMon'] ?? '';
            header("Location: index.php?action=yeu_cau_sua_diem&maLop=$maLop&maMon=$maMon");
            exit;
        }
    }
}
?>
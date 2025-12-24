<?php
require_once __DIR__ . '/../../models/gvbm/diemModel.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';

class DiemController {
    private $model;

    public function __construct() {
        $this->model = new DiemModel();
    }

    public function showYeuCauForm() {
        // Cho phép cả gvcn truy cập
        require_role(['gvbm', 'gvcn', 'ttbm']); 
        
        $user = current_user();

        // --- ⚠️ FIX: Lấy maGV chuẩn từ username thay vì teacher_id ---
        $username = $user['username'] ?? '';
        $maGV = $this->model->getMaGVByUsername($username);

        if (!$maGV) {
            $_SESSION['flash_error'] = "Không tìm thấy hồ sơ giáo viên.";
            // Tránh lỗi foreach bên dưới nếu $danhSachLop rỗng
            $danhSachLop = []; 
        } else {
            // Lấy danh sách lớp (Logic đã update trong Model để hỗ trợ GVCN)
            $danhSachLop = $this->model->getLopGiangDay($maGV);
        }
        // -----------------------------------------------------------

        $bangDiem = [];
        $lichSuYeuCau = [];
        
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
                $bangDiem = $this->model->getBangDiemLop($selectedLop, $selectedMon);
                $lichSuYeuCau = $this->model->getLichSuYeuCau($selectedLop, $selectedMon);
            } else {
                $_SESSION['flash_error'] = "Bạn không được phân công lớp/môn này.";
            }
        }

        $pageTitle = "Yêu cầu sửa điểm";
        // Đảm bảo đường dẫn view đúng
        require_once __DIR__ . '/../../views/layouts/header.php';
        if (file_exists(__DIR__ . '/../../views/gvbm/yeu_cau_sua_diem.php')) {
            require_once __DIR__ . '/../../views/gvbm/yeu_cau_sua_diem.php';
        } else {
            echo "Lỗi: Không tìm thấy file view yeu_cau_sua_diem.php";
        }
        require_once __DIR__ . '/../../views/layouts/footer.php';
    }

    public function submitYeuCau() {
        require_role(['gvbm', 'gvcn', 'ttbm']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maBangDiem = $_POST['maBangDiem'] ?? '';
            $loaiDiem = $_POST['loaiDiem'] ?? '';
            $diemCu = $_POST['diemCu'] ?? 0;
            $diemMoi = $_POST['diemMoi'] ?? '';
            $lyDo = trim($_POST['lyDo'] ?? '');
            $tenMon = $_POST['tenMon'] ?? 'Môn học';
            
            // Validation
            if ($diemMoi === '' || $lyDo === '') {
                $_SESSION['flash_error'] = "Vui lòng nhập điểm mới và lý do.";
            } elseif (!is_numeric($diemMoi) || $diemMoi < 0 || $diemMoi > 10) {
                $_SESSION['flash_error'] = "Điểm mới không hợp lệ (0-10).";
            } elseif ((float)$diemMoi == (float)$diemCu) {
                $_SESSION['flash_error'] = "Điểm mới phải khác điểm cũ.";
            } elseif ($this->model->checkYeuCauPending($maBangDiem, $loaiDiem)) {
                $_SESSION['flash_error'] = "Đang có yêu cầu chờ duyệt cho ô điểm này.";
            } else {
                $result = $this->model->taoYeuCauSuaDiem($maBangDiem, $loaiDiem, $diemCu, $diemMoi, $lyDo, $tenMon);
                if ($result) {
                    $_SESSION['flash_success'] = "Gửi yêu cầu thành công!";
                } else {
                    $_SESSION['flash_error'] = "Lỗi hệ thống khi lưu yêu cầu.";
                }
            }
            
            $maLop = $_POST['maLop'] ?? '';
            $maMon = $_POST['maMon'] ?? '';
            header("Location: index.php?action=yeu_cau_sua_diem&maLop=$maLop&maMon=$maMon");
            exit;
        }
    }
}
?>
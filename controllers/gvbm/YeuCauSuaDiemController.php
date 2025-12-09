<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/gvbm/YeuCauSuaDiemModel.php';

class YeuCauSuaDiemController {
    private $model;

    public function __construct() {
        $this->model = new YeuCauSuaDiemModel();
    }

    public function index() {
        require_role(['gvbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // ⚠️ FIX: Lấy mã giáo viên từ username
        $maGV = $this->model->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // ⚠️ FIX: Lấy danh sách lớp và môn của giáo viên
        $danhSachLop = $this->model->getDanhSachLopVaMonTheoGV($maGV);
        
        // DEBUG
        error_log("=== YeuCauSuaDiemController::index ===");
        error_log("maGV: $maGV");
        error_log("Số lớp-môn: " . count($danhSachLop));
        
        if (empty($danhSachLop)) {
            $_SESSION['flash_error'] = 'Bạn chưa được phân công giảng dạy lớp nào.';
            $bangDiem = [];
            $lichSuYeuCau = [];
            $selectedLop = null;
            $selectedMon = null;
            require_once __DIR__ . '/../../views/gvbm/yeu_cau_sua_diem.php';
            return;
        }

        // Lấy lớp và môn được chọn
        $selectedLop = $_GET['maLop'] ?? null;
        $selectedMon = $_GET['maMon'] ?? null;

        // Khởi tạo biến
        $bangDiem = [];
        $lichSuYeuCau = [];

        // Nếu đã chọn lớp và môn
        if ($selectedLop && $selectedMon) {
            // Lấy bảng điểm
            $bangDiem = $this->model->getBangDiemTheoLopVaMon($selectedLop, $selectedMon);
            
            // Lấy lịch sử yêu cầu
            $lichSuYeuCau = $this->model->getLichSuYeuCauTheoGV($maGV, $selectedLop, $selectedMon);
            
            // DEBUG
            error_log("selectedLop: $selectedLop | selectedMon: $selectedMon");
            error_log("Số học sinh: " . count($bangDiem));
            error_log("Số yêu cầu: " . count($lichSuYeuCau));
        }

        // Render view
        require_once __DIR__ . '/../../views/gvbm/yeu_cau_sua_diem.php';
    }

    public function guiYeuCau() {
        require_role(['gvbm']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=yeu_cau_sua_diem');
            exit;
        }

        $user = current_user();
        $maGV = $this->model->getMaGVByUsername($user['username']);

        $data = [
            'maBangDiem' => $_POST['maBangDiem'] ?? '',
            'loaiDiem' => $_POST['loaiDiem'] ?? '',
            'diemCu' => $_POST['diemCu'] ?? 0,
            'diemMoi' => $_POST['diemMoi'] ?? 0,
            'lyDo' => $_POST['lyDo'] ?? '',
            'maGV' => $maGV
        ];

        // Validate
        if (empty($data['maBangDiem']) || empty($data['loaiDiem']) || empty($data['lyDo'])) {
            $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin.';
            header('Location: /public/index.php?action=yeu_cau_sua_diem&maLop=' . ($_POST['maLop'] ?? '') . '&maMon=' . ($_POST['maMon'] ?? ''));
            exit;
        }

        // Gửi yêu cầu
        $result = $this->model->taoYeuCauSuaDiem($data);

        if ($result) {
            $_SESSION['flash_success'] = 'Gửi yêu cầu sửa điểm thành công. Chờ BGH phê duyệt.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }

        header('Location: /public/index.php?action=yeu_cau_sua_diem&maLop=' . ($_POST['maLop'] ?? '') . '&maMon=' . ($_POST['maMon'] ?? ''));
        exit;
    }
}

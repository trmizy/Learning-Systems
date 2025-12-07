<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class AssignExamController {
    private AssignExamModel $model;

    public function __construct() {
        $this->model = new AssignExamModel();
    }

    public function index(): void {
        require_role(['ttbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã TTBM từ username
        $maTTBM = $this->model->getMaTTBMByUsername($user['username']);
        
        if (!$maTTBM) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin trưởng tổ môn học.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách môn của tổ trưởng
        $monList = $this->model->layMonCuaToTruong($maTTBM);
        
        // DEBUG
        error_log("=== AssignExamController::index ===");
        error_log("maTTBM: $maTTBM");
        error_log("Số môn: " . count($monList));
        
        // ⚠️ FIX: Lấy môn được chọn hoặc môn đầu tiên
        $selectedMon = null;
        $listGV = [];
        
        if (!empty($monList)) {
            // Lấy môn từ GET hoặc mặc định môn đầu tiên
            $selectedMon = $_GET['mon'] ?? $monList[0]['maMonHoc'];
            
            // Lấy danh sách giáo viên theo môn
            $listGV = $this->model->layGiaoVienTheoMon($selectedMon);
            
            // DEBUG
            error_log("selectedMon: $selectedMon");
            error_log("Số giáo viên: " . count($listGV));
        }

        // Lấy danh sách khối
        $khoi = $this->model->layDanhSachKhoi();

        // Lấy danh sách phân công hiện có
        $phanCong = $this->model->layDanhSachPhanCong();

        // Render view
        require_once __DIR__ . '/../../views/ttbm/assign_exam.php';
    }

    /**
     * Xử lý lưu phân công
     */
    public function store(): void {
        require_role(['ttbm']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        $data = [
            'khoi' => $_POST['khoi'] ?? '',
            'listGV' => $_POST['listGV'] ?? [],
            'hocKy' => $_POST['hocKy'] ?? '',
            'kyThi' => $_POST['kyThi'] ?? '',
            'soLuongDe' => $_POST['soLuongDe'] ?? 1,
            'thoiHan' => $_POST['thoiHan'] ?? '',
            'ghiChu' => $_POST['ghiChu'] ?? ''
        ];

        // Validate
        if (empty($data['khoi']) || empty($data['listGV']) || empty($data['hocKy']) || empty($data['kyThi'])) {
            $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
            header('Location: /public/index.php?action=assign_exam');
            exit;
        }

        // Lưu vào database
        if ($this->model->luuPhanCong($data)) {
            $_SESSION['flash_success'] = 'Phân công ra đề thành công!';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }

        header('Location: /public/index.php?action=assign_exam');
        exit;
    }
}

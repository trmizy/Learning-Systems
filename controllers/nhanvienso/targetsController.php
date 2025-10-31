<?php
// filepath: controllers/nhanvienso/targetsController.php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/admissionTargetsModel.php';

class TargetsController {
    private $model;

    public function __construct() {
        // Kiểm tra quyền nhân viên sở
        require_role(['nhanvienso']);
        
        $this->model = new AdmissionTargetsModel();
    }

    /**
     * Hiển thị trang phân bổ chỉ tiêu tuyển sinh
     */
    public function index() {
        // Lấy năm học từ query string
        $namHocSelected = $_GET['namHoc'] ?? '';
        
        // Lấy danh sách năm học
        $danhSachNamHoc = $this->model->loadNamHoc();
        
        // Nếu chưa chọn năm học, lấy năm học mới nhất
        if (empty($namHocSelected) && !empty($danhSachNamHoc)) {
            $namHocSelected = $danhSachNamHoc[0];
        }

        // Lấy danh sách trường
        $danhSachTruong = $this->model->getDanhSachTruong();

        // Lấy chỉ tiêu đã phân bổ (nếu có)
        $chiTieuDaPhanBo = [];
        if (!empty($namHocSelected)) {
            $chiTieuList = $this->model->getChiTieuTheoNamHoc($namHocSelected);
            foreach ($chiTieuList as $ct) {
                $chiTieuDaPhanBo[$ct['maTruong']] = $ct['chiTieuPhanBo'];
            }
        }

        // Tính gợi ý chỉ tiêu cho từng trường
        $goiYChiTieu = [];
        foreach ($danhSachTruong as $truong) {
            $goiYChiTieu[$truong['maTruong']] = $this->model->tinhGoiYChiTieu(
                $truong['maTruong'], 
                $namHocSelected
            );
        }

        // Lấy tổng chỉ tiêu
        $tongChiTieuPheDuyet = $this->model->getTongPheDuyet($namHocSelected);
        $tongChiTieuDaPhanBo = $this->model->getTongChiTieuDaPhanBo($namHocSelected);

        // Lấy lịch sử phân bổ
        $lichSuPhanBo = $this->model->getLichSuPhanBo(5);

        // Render view
        require_once __DIR__ . '/../../views/nhanvienso/targetAllocation.php';
    }

    /**
     * Xử lý submit phân bổ chỉ tiêu
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?controller=targets&action=index');
            exit;
        }

        $namHoc = $_POST['namHoc'] ?? '';
        
        // KHÓA năm 2023-2024: Không cho phép chỉnh sửa
        if ($namHoc === '2023-2024') {
            $_SESSION['error'] = '❌ Năm học 2023-2024 đã được cố định, không thể chỉnh sửa!';
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }
        
        $chiTieuData = [];

        // Lấy mã nhân viên sở từ database thông qua username
        $user = current_user();
        $maNhanVienSo = null;
        
        if ($user && isset($user['username'])) {
            // Gọi method trong model để lấy maNhanVienSo từ username
            $maNhanVienSo = $this->model->getMaNhanVienSoByUsername($user['username']);
        }

        // Thu thập dữ liệu chỉ tiêu từ form
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'chitieu_') === 0) {
                $maTruong = str_replace('chitieu_', '', $key);
                $soLuong = trim($value);
                
                if (!empty($soLuong)) {
                    $chiTieuData[$maTruong] = (int)$soLuong;
                }
            }
        }

        // Validate dữ liệu
        $validation = $this->model->kiemTraTongChiTieu($chiTieuData, $namHoc);
        
        if (!$validation['valid']) {
            $_SESSION['error'] = implode('<br>', $validation['errors']);
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }

        // Kiểm tra đã nhập đủ chỉ tiêu cho tất cả các trường chưa
        $danhSachTruong = $this->model->getDanhSachTruong();
        if (count($chiTieuData) < count($danhSachTruong)) {
            $_SESSION['error'] = 'Vui lòng nhập chỉ tiêu cho tất cả các trường!';
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }

        // Lưu phân bổ chỉ tiêu
        $result = $this->model->luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
        exit;
    }

    /**
     * API lấy gợi ý chỉ tiêu (AJAX)
     */
    public function getGoiY() {
        header('Content-Type: application/json');
        
        $maTruong = $_GET['maTruong'] ?? '';
        $namHoc = $_GET['namHoc'] ?? '';

        if (empty($maTruong) || empty($namHoc)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số']);
            exit;
        }

        $goiY = $this->model->tinhGoiYChiTieu($maTruong, $namHoc);
        
        echo json_encode([
            'success' => true,
            'goiY' => $goiY
        ]);
        exit;
    }

    /**
     * Hủy phân bổ
     */
    public function cancel() {
        $namHoc = $_GET['namHoc'] ?? '';
        $_SESSION['info'] = 'Đã hủy phân bổ chỉ tiêu tuyển sinh.';
        header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
        exit;
    }
}

// Xử lý routing
$action = $_GET['action'] ?? 'index';
$controller = new TargetsController();

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'submit':
        $controller->submit();
        break;
    case 'getGoiY':
        $controller->getGoiY();
        break;
    case 'cancel':
        $controller->cancel();
        break;
    default:
        $controller->index();
        break;
}

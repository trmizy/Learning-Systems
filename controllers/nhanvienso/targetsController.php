<?php
// Controller: Phân bổ chỉ tiêu tuyển sinh
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/admissionTargetsModel.php';

class TargetsController {
    private $model;

    public function __construct() {
        require_role(['nhanvienso']); // Chỉ nhân viên sở mới truy cập được
        $this->model = new AdmissionTargetsModel();
    }

    /**
     * Hiển thị trang phân bổ chỉ tiêu
     */
    public function index() {
        $namHocSelected = $_GET['namHoc'] ?? '';
        $danhSachNamHoc = $this->model->loadNamHoc();
        if (empty($namHocSelected) && !empty($danhSachNamHoc)) $namHocSelected = $danhSachNamHoc[0];

        $danhSachTruong = $this->model->getDanhSachTruong();
        $chiTieuDaPhanBo = $this->layChiTieuDaPhanBo($namHocSelected);
        $chiTieuNamTruoc = $this->layChiTieuNamTruoc($danhSachTruong, $namHocSelected);
        $goiYChiTieu = $this->tinhGoiYChoTungTruong($danhSachTruong, $namHocSelected);

        $tongChiTieuPheDuyet = $this->model->getTongPheDuyet($namHocSelected);
        $tongChiTieuDaPhanBo = $this->model->getTongChiTieuDaPhanBo($namHocSelected);
        $lichSuPhanBo = $this->model->getLichSuPhanBo(5);

        require_once __DIR__ . '/../../views/nhanvienso/targetAllocation.php';
    }

    /**
     * Lấy chỉ tiêu đã phân bổ cho các trường
     */
    private function layChiTieuDaPhanBo($namHoc) {
        $map = [];
        if (empty($namHoc)) return $map;
        $list = $this->model->getChiTieuTheoNamHoc($namHoc);
        foreach ($list as $r) $map[$r['maTruong']] = (int)$r['chiTieuPhanBo'];
        return $map;
    }

    /**
     * Lấy chỉ tiêu năm trước thực tế từ database
     */
    private function layChiTieuNamTruoc($danhSachTruong, $namHocHienTai) {
        $res = [];
        foreach ($danhSachTruong as $t) $res[$t['maTruong']] = $this->model->getChiTieuNamTruocThucTe($t['maTruong'], $namHocHienTai);
        return $res;
    }

    /**
     * Tính gợi ý chỉ tiêu cho từng trường
     */
    private function tinhGoiYChoTungTruong($danhSachTruong, $namHoc) {
        $out = [];
        foreach ($danhSachTruong as $t) $out[$t['maTruong']] = $this->model->tinhGoiYChiTieu($t['maTruong'], $namHoc);
        return $out;
    }

    /**
     * Xử lý submit phân bổ chỉ tiêu
     */
    public function submit() {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: targetsController.php');
            exit;
        }

        $namHoc = $_POST['namHoc'] ?? '';
        
        // Kiểm tra năm học 2023-2024 (đã cố định, không cho sửa)
        if ($this->kiemTraNamHocCodinh($namHoc)) {
            $_SESSION['error'] = '❌ Năm học 2023-2024 đã được cố định, không thể chỉnh sửa!';
            $this->redirect($namHoc);
        }

        // Bước 1: Thu thập dữ liệu chỉ tiêu từ form
        $chiTieuData = $this->thuThapDuLieuChiTieu();

        // Bước 2: Lấy mã nhân viên sở từ user đang đăng nhập
        $maNhanVienSo = $this->layMaNhanVienSo();

        // Bước 3: Validate dữ liệu
        if (!$this->validateChiTieu($chiTieuData, $namHoc)) {
            $this->redirect($namHoc);
        }

        // Bước 4: Lưu phân bổ vào database
        $result = $this->model->luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo);

        // Bước 5: Hiển thị kết quả
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect($namHoc);
    }

    /**
     * Kiểm tra năm học có phải năm cố định không
     */
    private function kiemTraNamHocCodinh($namHoc) {
        return $namHoc === '2023-2024';
    }

    /**
     * Thu thập dữ liệu chỉ tiêu từ form POST
     */
    private function thuThapDuLieuChiTieu() {
        $chiTieuData = [];
        
        foreach ($_POST as $key => $value) {
            // Chỉ lấy các field có tên bắt đầu bằng 'chitieu_'
            if (strpos($key, 'chitieu_') === 0) {
                $maTruong = str_replace('chitieu_', '', $key);
                $soLuong = trim($value);
                
                if (!empty($soLuong)) {
                    $chiTieuData[$maTruong] = (int)$soLuong;
                }
            }
        }
        
        return $chiTieuData;
    }

    /**
     * Lấy mã nhân viên sở từ user hiện tại
     */
    private function layMaNhanVienSo() {
        $user = current_user();
        
        if ($user && isset($user['username'])) {
            return $this->model->getMaNhanVienSoByUsername($user['username']);
        }
        
        return null;
    }

    /**
     * Validate dữ liệu chỉ tiêu
     */
    private function validateChiTieu($chiTieuData, $namHoc) {
        // Kiểm tra 1: Validate tổng chỉ tiêu
        $validation = $this->model->kiemTraTongChiTieu($chiTieuData, $namHoc);
        
        if (!$validation['valid']) {
            $_SESSION['error'] = implode('<br>', $validation['errors']);
            return false;
        }

        // Kiểm tra 2: Đã nhập đủ cho tất cả các trường chưa
        $danhSachTruong = $this->model->getDanhSachTruong();
        if (count($chiTieuData) < count($danhSachTruong)) {
            $_SESSION['error'] = 'Vui lòng nhập chỉ tiêu cho tất cả các trường!';
            return false;
        }

        return true;
    }

    /**
     * Redirect về trang chủ với năm học
     */
    private function redirect($namHoc) {
        header('Location: targetsController.php?namHoc=' . urlencode($namHoc));
        exit;
    }

    /**
     * API lấy gợi ý chỉ tiêu (AJAX)
     * Dùng cho nút "Gợi ý" trên form
     */
    public function getGoiY() {
        header('Content-Type: application/json');
        
        $maTruong = $_GET['maTruong'] ?? '';
        $namHoc = $_GET['namHoc'] ?? '';

        // Kiểm tra tham số đầu vào
        if (empty($maTruong) || empty($namHoc)) {
            $this->jsonResponse(false, 'Thiếu tham số maTruong hoặc namHoc');
        }

        // Tính gợi ý chỉ tiêu
        $goiY = $this->model->tinhGoiYChiTieu($maTruong, $namHoc);
        $this->jsonResponse(true, 'Thành công', $goiY);
    }

    /**
     * Trả về JSON response
     */
    private function jsonResponse($success, $message, $goiY = null) {
        $response = ['success' => $success, 'message' => $message];
        if ($goiY !== null) {
            $response['goiY'] = $goiY;
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Hủy phân bổ (chỉ hủy form, KHÔNG xóa database)
     */
    public function cancel() {
        $namHoc = $_GET['namHoc'] ?? '';
        $_SESSION['info'] = 'Đã hủy thao tác nhập liệu.';
        $this->redirect($namHoc);
    }

    /**
     * Reset phân bổ (XÓA toàn bộ dữ liệu trong DATABASE)
     */
    public function reset() {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: targetsController.php');
            exit;
        }

        $namHoc = $_POST['namHoc'] ?? '';
        
        // Kiểm tra năm học cố định
        if ($this->kiemTraNamHocCodinh($namHoc)) {
            $_SESSION['error'] = '❌ Năm học 2023-2024 đã được cố định, không thể xóa!';
            $this->redirect($namHoc);
        }

        // Kiểm tra năm học có được chọn không
        if (empty($namHoc)) {
            $_SESSION['error'] = 'Vui lòng chọn năm học!';
            $this->redirect('');
        }

        // Xóa phân bổ
        $result = $this->model->xoaPhanBoTheoNamHoc($namHoc);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect($namHoc);
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
    case 'reset':
        $controller->reset();
        break;
    default:
        $controller->index();
        break;
}

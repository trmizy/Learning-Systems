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
     * Xử lý submit phân bổ - CHO PHÉP PHÂN BỔ 1 HOẶC NHIỀU TRƯỜNG
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?controller=targets&action=index');
            exit;
        }

        $namHoc = $_POST['namHoc'] ?? '';
        
        // KHÓA năm 2023-2024
        if ($namHoc === '2023-2024') {
            $_SESSION['error'] = '❌ Năm 2023-2024 đã cố định, không thể sửa!';
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }
        
        // Thu thập dữ liệu - CHỈ LẤY CÁC TRƯỜNG CÓ NHẬP
        $chiTieuData = [];
        $chiTieuMoi = []; // Dữ liệu mới nhập
        $chiTieuCu = [];  // Dữ liệu cũ giữ nguyên
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'chitieu_') === 0) {
                $maTruong = str_replace('chitieu_', '', $key);
                $soLuong = trim($value);
                
                // Nếu có nhập giá trị (không để trống)
                if ($soLuong !== '') {
                    $soLuongInt = (int)$soLuong;
                    
                    if ($soLuongInt > 0) {
                        $chiTieuData[$maTruong] = $soLuongInt;
                        $chiTieuMoi[$maTruong] = $soLuongInt;
                    }
                }
            }
        }

        // Validate: Phải nhập ít nhất 1 trường
        if (empty($chiTieuData)) {
            $_SESSION['error'] = '⚠️ Vui lòng nhập chỉ tiêu cho ít nhất 1 trường!';
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }

        // Validate số âm/0 - KHÔNG kiểm tra tổng
        $validation = $this->model->kiemTraTongChiTieu($chiTieuData, $namHoc);
        if (!$validation['valid']) {
            $_SESSION['error'] = implode('<br>', $validation['errors']);
            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }

        // Lấy chỉ tiêu cũ (các trường không được chỉnh sửa)
        $chiTieuCuData = $this->model->getChiTieuTheoNamHoc($namHoc);
        foreach ($chiTieuCuData as $ct) {
            if (!isset($chiTieuMoi[$ct['maTruong']])) {
                // Trường không được nhập mới → giữ nguyên giá trị cũ
                $chiTieuCu[$ct['maTruong']] = $ct['chiTieuPhanBo'];
            }
        }

        // Merge: Dữ liệu mới + dữ liệu cũ
        $chiTieuHoanChinh = array_merge($chiTieuCu, $chiTieuMoi);

        // Lấy mã nhân viên
        $user = current_user();
        $maNhanVienSo = $this->model->getMaNhanVienSoByUsername($user['username'] ?? '');

        // Lưu vào DB (CẬP NHẬT PARTIAL)
        $result = $this->model->luuPhanBoPartial($namHoc, $chiTieuMoi, $chiTieuHoanChinh, $maNhanVienSo);

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

    /**
     * Reset toàn bộ phân bổ của 1 năm học
     */
    public function reset() {
        $namHoc = $_GET['namHoc'] ?? '';

        if (empty($namHoc)) {
            $_SESSION['error'] = '⚠️ Thiếu thông tin năm học!';
            header('Location: /public/index.php?controller=targets&action=index');
            exit;
        }

        // Xác nhận từ form POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->resetPhanBoTheoNam($namHoc);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }

            header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
            exit;
        }

        // Nếu GET, redirect về trang chính (không cho phép reset trực tiếp qua URL)
        $_SESSION['error'] = '⚠️ Yêu cầu không hợp lệ!';
        header('Location: /public/index.php?controller=targets&action=index&namHoc=' . urlencode($namHoc));
        exit;
    }

    /**
     * Xóa 1 lịch sử phân bổ cụ thể
     */
    public function deleteHistory() {
        $namHoc = $_GET['namHoc'] ?? '';
        $ngayBanHanh = $_GET['ngayBanHanh'] ?? '';

        if (empty($namHoc) || empty($ngayBanHanh)) {
            $_SESSION['error'] = '⚠️ Thiếu thông tin!';
            header('Location: /public/index.php?controller=targets&action=index');
            exit;
        }

        $result = $this->model->xoaLichSu($namHoc, $ngayBanHanh);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: /public/index.php?controller=targets&action=index');
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
    case 'reset':
        $controller->reset();
        break;
    case 'deleteHistory':
        $controller->deleteHistory();
        break;
    default:
        $controller->index();
        break;
}

<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/bgh/DuyetSuaDiemModel.php';

class DuyetSuaDiemController {
    private $model;

    public function __construct() {
        $this->model = new DuyetSuaDiemModel();
    }

    /**
     * Hiển thị danh sách yêu cầu sửa điểm chờ duyệt
     */
    public function index() {
        require_role(['bgh']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách yêu cầu chờ duyệt
        $danhSachYeuCau = $this->model->getDanhSachYeuCauChoDuyet();

        // DEBUG
        error_log("=== DuyetSuaDiemController::index ===");
        error_log("Số yêu cầu chờ duyệt: " . count($danhSachYeuCau));

        // Render view
        require_once __DIR__ . '/../../views/bgh/duyet_sua_diem_list.php';
    }

    /**
     * Xem chi tiết yêu cầu sửa điểm
     */
    public function chiTiet() {
        require_role(['bgh']);

        $maYeuCau = $_GET['id'] ?? null;
        
        if (!$maYeuCau) {
            $_SESSION['flash_error'] = 'Không tìm thấy yêu cầu.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        // Lấy thông tin chi tiết yêu cầu
        $yeuCau = $this->model->getChiTietYeuCau($maYeuCau);

        if (!$yeuCau) {
            $_SESSION['flash_error'] = 'Yêu cầu không tồn tại.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        // DEBUG
        error_log("=== DuyetSuaDiemController::chiTiet ===");
        error_log("maYeuCau: $maYeuCau");
        error_log(print_r($yeuCau, true));

        // Render view
        require_once __DIR__ . '/../../views/bgh/duyet_sua_diem_detail.php';
    }

    /**
     * Xử lý duyệt yêu cầu - FIX: Thêm validation và debug
     */
    public function duyet() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        $maYeuCau = $_POST['maYeuCau'] ?? null;
        $ghiChu = $_POST['ghiChu'] ?? '';

        // ⚠️ FIX: Validate input
        if (!$maYeuCau) {
            $_SESSION['flash_error'] = 'Không tìm thấy yêu cầu.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        // DEBUG
        error_log("=== DuyetSuaDiemController::duyet ===");
        error_log("maYeuCau: $maYeuCau");
        error_log("ghiChu: $ghiChu");

        // Lấy thông tin BGH
        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng BGH.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        $maBGH = $this->model->getMaBGHByUsername($user['username']);
        
        error_log("username: " . $user['username']);
        error_log("maBGH: " . ($maBGH ?: 'NULL'));

        // ⚠️ FIX: Kiểm tra maBGH
        if (!$maBGH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin BGH. Vui lòng liên hệ quản trị viên.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        // Xử lý duyệt
        $result = $this->model->duyetYeuCau($maYeuCau, $maBGH, $ghiChu);

        error_log("Result: " . ($result ? 'SUCCESS' : 'FAILED'));

        if ($result) {
            $_SESSION['flash_success'] = 'Đã duyệt yêu cầu sửa điểm thành công. Điểm đã được cập nhật.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi duyệt yêu cầu. Vui lòng kiểm tra log để biết chi tiết.';
        }

        header('Location: /public/index.php?action=bgh-duyet-sua-diem');
        exit;
    }

    /**
     * Xử lý từ chối yêu cầu
     */
    public function tuChoi() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        $maYeuCau = $_POST['maYeuCau'] ?? null;
        $lyDoTuChoi = $_POST['lyDoTuChoi'] ?? '';

        if (!$maYeuCau) {
            $_SESSION['flash_error'] = 'Không tìm thấy yêu cầu.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem');
            exit;
        }

        if (empty($lyDoTuChoi)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập lý do từ chối.';
            header('Location: /public/index.php?action=bgh-duyet-sua-diem-chi-tiet&id=' . $maYeuCau);
            exit;
        }

        // Lấy thông tin BGH
        $user = current_user();
        $maBGH = $this->model->getMaBGHByUsername($user['username']);

        // Xử lý từ chối
        $result = $this->model->tuChoiYeuCau($maYeuCau, $maBGH, $lyDoTuChoi);

        if ($result) {
            $_SESSION['flash_success'] = 'Đã từ chối yêu cầu sửa điểm.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi từ chối yêu cầu.';
        }

        header('Location: /public/index.php?action=bgh-duyet-sua-diem');
        exit;
    }

    /**
     * Hiển thị lịch sử yêu cầu đã xử lý
     */
    public function lichSu() {
        require_role(['bgh']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được người dùng.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy filter từ URL
        $trangThai = $_GET['trangThai'] ?? 'ALL'; // ALL, DA_DUYET, TU_CHOI
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Lấy danh sách lịch sử
        $lichSuYeuCau = $this->model->getLichSuYeuCau($perPage, $offset);

        // Filter theo trạng thái nếu cần
        if ($trangThai !== 'ALL') {
            $lichSuYeuCau = array_filter($lichSuYeuCau, function($yc) use ($trangThai) {
                return $yc['trangThai'] === $trangThai;
            });
        }

        // Đếm tổng số bản ghi cho pagination
        $tongSo = $this->model->demTongLichSu();
        $tongTrang = ceil($tongSo / $perPage);

        // DEBUG
        error_log("=== DuyetSuaDiemController::lichSu ===");
        error_log("Số lịch sử: " . count($lichSuYeuCau));
        error_log("Trang: $page/$tongTrang");

        // Render view
        require_once __DIR__ . '/../../views/bgh/duyet_sua_diem_history.php';
    }
}

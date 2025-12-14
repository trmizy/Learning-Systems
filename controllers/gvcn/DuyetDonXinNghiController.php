<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/gvcn/DuyetDonXinNghiModel.php';

class DuyetDonXinNghiController {
    private $model;

    public function __construct() {
        $this->model = new DuyetDonXinNghiModel();
    }

    /**
     * Hiển thị danh sách đơn chờ duyệt
     */
    public function pending() {
        require_role(['gvcn']);

        $user = current_user();
        $maGV = $this->model->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy lớp chủ nhiệm
        $lopChuNhiem = $this->model->getLopChuNhiem($maGV);
        if (!$lopChuNhiem) {
            $_SESSION['flash_error'] = 'Bạn chưa được phân công lớp chủ nhiệm.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách đơn chờ duyệt
        $danhSachDon = $this->model->getDonChoPheDuyet($lopChuNhiem['maLop']);

        require_once __DIR__ . '/../../views/gvcn/duyet-don/pending.php';
    }

    /**
     * Hiển thị tất cả đơn (đã duyệt, từ chối, hủy)
     */
    public function list() {
        require_role(['gvcn']);

        $user = current_user();
        $maGV = $this->model->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        $lopChuNhiem = $this->model->getLopChuNhiem($maGV);
        if (!$lopChuNhiem) {
            $_SESSION['flash_error'] = 'Bạn chưa được phân công lớp chủ nhiệm.';
            header('Location: /public/index.php');
            exit;
        }

        // Lọc theo trạng thái
        $trangThai = $_GET['status'] ?? 'all';
        $danhSachDon = $this->model->getDonTheoTrangThai($lopChuNhiem['maLop'], $trangThai);

        require_once __DIR__ . '/../../views/gvcn/duyet-don/list.php';
    }

    /**
     * Phê duyệt đơn
     */
    public function approve() {
        require_role(['gvcn']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        $maDonXinPhep = $_POST['maDonXinPhep'] ?? '';
        $ghiChu = $_POST['ghiChu'] ?? '';

        if (empty($maDonXinPhep)) {
            $_SESSION['flash_error'] = 'Không xác định được đơn cần duyệt.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        // Lấy thông tin đơn
        $don = $this->model->getThongTinDon($maDonXinPhep);
        if (!$don) {
            $_SESSION['flash_error'] = 'Không tìm thấy đơn xin nghỉ.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        // Kiểm tra quyền (phải là GVCN của lớp)
        $user = current_user();
        $maGV = $this->model->getMaGVByUsername($user['username']);
        $lopChuNhiem = $this->model->getLopChuNhiem($maGV);

        if ($don['maLop'] !== $lopChuNhiem['maLop']) {
            $_SESSION['flash_error'] = 'Bạn không có quyền duyệt đơn này.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        // Bắt đầu transaction
        $this->model->beginTransaction();

        try {
            // 1. Cập nhật trạng thái đơn
            $this->model->capNhatTrangThaiDon($maDonXinPhep, 'Da duoc duyet', $ghiChu);

            // 2. Cập nhật hạnh kiểm - Tăng số buổi nghỉ có phép
            $namHoc = '2024-2025'; // TODO: Lấy năm học hiện tại
            $hocKy = 'HK1'; // TODO: Xác định học kỳ từ ngày nghỉ

            $this->model->capNhatHanhKiem($don['maHS'], $namHoc, $hocKy, $don['soBuoi']);

            $this->model->commit();

            $_SESSION['flash_success'] = 'Đã phê duyệt đơn xin nghỉ thành công. Hạnh kiểm đã được cập nhật.';
            
        } catch (Exception $e) {
            $this->model->rollback();
            error_log("Error approve: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi phê duyệt đơn. Vui lòng thử lại.';
        }

        header('Location: /public/index.php?action=gvcn-duyet-don-pending');
        exit;
    }

    /**
     * Từ chối đơn
     */
    public function reject() {
        require_role(['gvcn']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        $maDonXinPhep = $_POST['maDonXinPhep'] ?? '';
        $lyDoTuChoi = $_POST['lyDoTuChoi'] ?? '';

        if (empty($maDonXinPhep) || empty($lyDoTuChoi)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập lý do từ chối.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        // Kiểm tra quyền
        $don = $this->model->getThongTinDon($maDonXinPhep);
        $user = current_user();
        $maGV = $this->model->getMaGVByUsername($user['username']);
        $lopChuNhiem = $this->model->getLopChuNhiem($maGV);

        if ($don['maLop'] !== $lopChuNhiem['maLop']) {
            $_SESSION['flash_error'] = 'Bạn không có quyền từ chối đơn này.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        // Cập nhật trạng thái
        $result = $this->model->capNhatTrangThaiDon($maDonXinPhep, 'Bi tu choi', $lyDoTuChoi);

        if ($result) {
            $_SESSION['flash_success'] = 'Đã từ chối đơn xin nghỉ.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi từ chối đơn.';
        }

        header('Location: /public/index.php?action=gvcn-duyet-don-pending');
        exit;
    }

    /**
     * Xem chi tiết đơn
     */
    public function detail() {
        require_role(['gvcn']);

        $maDonXinPhep = $_GET['id'] ?? '';
        if (empty($maDonXinPhep)) {
            $_SESSION['flash_error'] = 'Không xác định được đơn.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        $don = $this->model->getThongTinDonChiTiet($maDonXinPhep);
        if (!$don) {
            $_SESSION['flash_error'] = 'Không tìm thấy đơn xin nghỉ.';
            header('Location: /public/index.php?action=gvcn-duyet-don-pending');
            exit;
        }

        require_once __DIR__ . '/../../views/gvcn/duyet-don/detail.php';
    }
}

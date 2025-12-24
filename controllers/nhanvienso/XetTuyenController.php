<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/nhanvienso/XetTuyenModel.php';

class XetTuyenController {
    private $model;

    public function __construct() {
        require_role(['nhanvienso']);
        $this->model = new XetTuyenModel();
    }

    /**
     * Hiển thị trang xét tuyển - Dashboard
     */
    public function index() {
        // Lấy thống kê trước khi xét tuyển
        $stats = $this->model->getThongKeXetTuyen();
        
        // Kiểm tra điều kiện sẵn sàng
        $readyCheck = $this->model->kiemTraSanSang();
        
        require_once __DIR__ . '/../../views/nhanvienso/xet_tuyen/index.php';
    }

    /**
     * Chạy thuật toán xét tuyển
     */
    public function chayXetTuyen() {
        try {
            // Kiểm tra điều kiện
            $readyCheck = $this->model->kiemTraSanSang();
            
            if (!$readyCheck['ready']) {
                $_SESSION['flash_error'] = 'Không đủ điều kiện xét tuyển: ' . implode(', ', $readyCheck['errors']);
                header('Location: /public/index.php?action=nhanvienso-xet-tuyen');
                exit;
            }

            // Chạy xét tuyển
            $result = $this->model->chayXetTuyenToanBo();
            
            if ($result['success']) {
                $_SESSION['flash_success'] = sprintf(
                    'Xét tuyển thành công! %d thí sinh trúng tuyển, %d thí sinh trượt.',
                    $result['tong_trung_tuyen'],
                    $result['tong_truot']
                );
            } else {
                $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $result['message'];
            }
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
        
        header('Location: /public/index.php?action=nhanvienso-xet-tuyen-ket-qua');
        exit;
    }

    /**
     * Xem kết quả xét tuyển
     */
    public function xemKetQua() {
        // Lấy kết quả xét tuyển
        $ketQuaTheoTruong = $this->model->getKetQuaTheoTruong();
        $ketQuaChiTiet = $this->model->getKetQuaChiTiet();
        $thongKeTongQuat = $this->model->getThongKeXetTuyen();
        
        require_once __DIR__ . '/../../views/nhanvienso/xet_tuyen/ket_qua.php';
    }
}

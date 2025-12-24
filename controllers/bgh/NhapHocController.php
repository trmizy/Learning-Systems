<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/bgh/NhapHocModel.php';

class NhapHocController {
    private $model;

    public function __construct() {
        require_role(['bgh']);
        $this->model = new NhapHocModel();
    }

    /**
     * Hiển thị trang xác nhận nhập học
     */
    public function index() {
        // Lấy danh sách thí sinh đậu (chưa nhập học)
        $thiSinhDau = $this->model->getDanhSachThiSinhDau();
        
        // Lấy thống kê lớp khối 10
        $thongKeLop = $this->model->getThongKeLopKhoi10();
        
        // Kiểm tra điều kiện nhập học
        $readyCheck = $this->model->kiemTraDieuKienNhapHoc();
        
        require_once __DIR__ . '/../../views/bgh/nhap_hoc/index.php';
    }

    /**
     * Xử lý xác nhận nhập học - Chạy thuật toán phân lớp
     */
    public function xacNhanNhapHoc() {
        try {
            // Kiểm tra điều kiện
            $readyCheck = $this->model->kiemTraDieuKienNhapHoc();
            
            if (!$readyCheck['ready']) {
                $_SESSION['flash_error'] = 'Không đủ điều kiện nhập học: ' . implode(', ', $readyCheck['errors']);
                header('Location: /public/index.php?action=bgh-nhap-hoc');
                exit;
            }

            // Chạy nhập học tự động
            $result = $this->model->chayNhapHocTuDong();
            
            if ($result['success']) {
                $_SESSION['flash_success'] = sprintf(
                    'Nhập học thành công! %d học sinh mới đã được phân lớp và tạo tài khoản.',
                    $result['tong_nhap_hoc']
                );
            } else {
                $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $result['message'];
            }
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
        
        header('Location: /public/index.php?action=bgh-nhap-hoc');
        exit;
    }
}

<?php
/**
 * Controller: Quản lý phân công giảng dạy và phòng học
 * Role: BGH, Admin
 * Path: controllers/bgh/assignment_controller.php
 */

class AssignmentController {
    private $phanCongModel;
    private $message = '';
    private $messageType = '';
    
    public function __construct() {
        require_once __DIR__ . '/../../models/bgh/PhanCongModel.php';
        $this->phanCongModel = new PhanCongModel();
    }
    
    /**
     * Hiển thị trang quản lý phân công
     */
    public function index() {
        // Xử lý POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }
        
        // Lấy năm học được chọn
        $namHocFilter = $_GET['namHoc'] ?? '2024-2025';
        
        // Lấy dữ liệu
        $danhSachNamHoc = $this->phanCongModel->getAllNamHoc();
        $danhSachLop = $this->phanCongModel->getAllLopHoc($namHocFilter);
        
        // Load view
        $data = [
            'namHocFilter' => $namHocFilter,
            'danhSachNamHoc' => $danhSachNamHoc,
            'danhSachLop' => $danhSachLop,
            'message' => $this->message,
            'messageType' => $this->messageType
        ];
        
        $this->loadView('manage', $data);
    }
    
    /**
     * Xử lý các POST request (gán/xóa GVCN, phòng học)
     */
    private function handlePostRequest() {
        $action = $_POST['action'] ?? '';
        $maLop = $_POST['maLop'] ?? '';
        
        switch ($action) {
            case 'gan_gvcn':
                $this->ganGVCN($maLop, $_POST['maGV'] ?? '');
                break;
                
            case 'gan_phong':
                $this->ganPhongHoc($maLop, $_POST['maPhong'] ?? '');
                break;
                
            case 'xoa_gvcn':
                $this->xoaGVCN($maLop);
                break;
                
            case 'xoa_phong':
                $this->xoaPhongHoc($maLop);
                break;
                
            default:
                $this->message = 'Hành động không hợp lệ';
                $this->messageType = 'danger';
        }
    }
    
    /**
     * Gán GVCN cho lớp
     */
    private function ganGVCN($maLop, $maGV) {
        if (empty($maLop) || empty($maGV)) {
            $this->message = 'Vui lòng chọn đầy đủ thông tin';
            $this->messageType = 'danger';
            return;
        }
        
        // Kiểm tra GV đã làm GVCN chưa
        $lopDangChuNhiem = $this->phanCongModel->kiemTraGVCN($maGV);
        if ($lopDangChuNhiem && $lopDangChuNhiem['lop'] !== $maLop) {
            $this->message = "Giáo viên này đã là GVCN của lớp {$lopDangChuNhiem['tenLop']}";
            $this->messageType = 'warning';
            return;
        }
        
        // Gán GVCN
        if ($this->phanCongModel->ganGVCN($maLop, $maGV)) {
            $this->message = 'Gán giáo viên chủ nhiệm thành công';
            $this->messageType = 'success';
        } else {
            $this->message = 'Lỗi khi gán giáo viên chủ nhiệm';
            $this->messageType = 'danger';
        }
    }
    
    /**
     * Gán phòng học cho lớp
     */
    private function ganPhongHoc($maLop, $maPhong) {
        if (empty($maLop) || empty($maPhong)) {
            $this->message = 'Vui lòng chọn đầy đủ thông tin';
            $this->messageType = 'danger';
            return;
        }
        
        // Lấy năm học của lớp
        $lopInfo = $this->phanCongModel->getThongTinLop($maLop);
        $namHoc = $lopInfo['namHoc'] ?? '2024-2025';
        
        // Kiểm tra phòng đã được gán chưa
        $lopDangGan = $this->phanCongModel->kiemTraPhongHoc($maPhong, $namHoc);
        if ($lopDangGan && $lopDangGan['maLop'] !== $maLop) {
            $this->message = "Phòng này đã được gán cho lớp {$lopDangGan['tenLop']} trong năm học {$namHoc}";
            $this->messageType = 'warning';
            return;
        }
        
        // Gán phòng
        if ($this->phanCongModel->ganPhongHoc($maLop, $maPhong)) {
            $this->message = 'Gán phòng học thành công';
            $this->messageType = 'success';
        } else {
            $this->message = 'Lỗi khi gán phòng học';
            $this->messageType = 'danger';
        }
    }
    
    /**
     * Xóa GVCN của lớp
     */
    private function xoaGVCN($maLop) {
        if (empty($maLop)) {
            $this->message = 'Không tìm thấy lớp học';
            $this->messageType = 'danger';
            return;
        }
        
        if ($this->phanCongModel->xoaGVCN($maLop)) {
            $this->message = 'Xóa giáo viên chủ nhiệm thành công';
            $this->messageType = 'success';
        } else {
            $this->message = 'Lỗi khi xóa giáo viên chủ nhiệm';
            $this->messageType = 'danger';
        }
    }
    
    /**
     * Xóa phòng học của lớp
     */
    private function xoaPhongHoc($maLop) {
        if (empty($maLop)) {
            $this->message = 'Không tìm thấy lớp học';
            $this->messageType = 'danger';
            return;
        }
        
        if ($this->phanCongModel->xoaPhongHoc($maLop)) {
            $this->message = 'Xóa phòng học thành công';
            $this->messageType = 'success';
        } else {
            $this->message = 'Lỗi khi xóa phòng học';
            $this->messageType = 'danger';
        }
    }
    
    /**
     * Load view với dữ liệu
     */
    private function loadView($viewName, $data = []) {
        extract($data);
        require_once __DIR__ . '/../../views/bgh/assignments/' . $viewName . '.php';
    }
    
    /**
     * API: Lấy danh sách GVCN chưa gán
     */
    public function getGiaoVien() {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $maLop = $_GET['maLop'] ?? null;
        $result = $this->phanCongModel->getGiaoVienChuaChuNhiem($maLop);
        
        $giaoViens = [];
        while ($row = $result->fetch()) {
            $giaoViens[] = $row;
        }
        
        echo json_encode($giaoViens, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * API: Lấy danh sách phòng học khả dụng
     */
    public function getPhongHoc() {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $maLop = $_GET['maLop'] ?? null;
        
        // Debug log
        error_log("API getPhongHoc - maLop: " . $maLop);
        
        // Lấy năm học của lớp
        $namHoc = null;
        if ($maLop) {
            $lopInfo = $this->phanCongModel->getThongTinLop($maLop);
            $namHoc = $lopInfo['namHoc'] ?? '2024-2025';
            error_log("API getPhongHoc - namHoc: " . $namHoc);
        }
        
        $result = $this->phanCongModel->getPhongHocChuaGan($maLop, $namHoc);
        
        $phongHocs = [];
        while ($row = $result->fetch()) {
            $phongHocs[] = $row;
        }
        
        error_log("API getPhongHoc - Số phòng tìm thấy: " . count($phongHocs));
        
        echo json_encode($phongHocs, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

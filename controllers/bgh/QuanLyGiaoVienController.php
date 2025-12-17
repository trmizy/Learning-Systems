<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/bgh/GiaoVienModel.php';

class QuanLyGiaoVienController {
    private $model;

    public function __construct() {
        $this->model = new GiaoVienModel();
    }

    /**
     * Hiển thị danh sách giáo viên
     */
    public function index() {
        require_role(['bgh']);

        try {
            // Xử lý tìm kiếm
            $keyword = $_GET['search'] ?? '';
            if (!empty($keyword)) {
                $danhSachGiaoVien = $this->model->timKiemGiaoVien($keyword);
            } else {
                $danhSachGiaoVien = $this->model->getAllGiaoVien();
            }

            // Render view
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php';

        } catch (Exception $e) {
            error_log("Error index QuanLyGiaoVienController: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Lỗi khi tải danh sách giáo viên: ' . $e->getMessage();
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php';
        }
    }

    /**
     * Hiển thị form tạo mới giáo viên
     */
    public function create() {
        require_role(['bgh']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }

        // Render form
        require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/tao_giao_vien.php';
    }

    /**
     * Xử lý thêm giáo viên mới
     */
    private function store() {
        try {
            // Validate và lấy dữ liệu
            $maTruong = strtoupper(trim($_POST['maTruong'] ?? 'TR001'));
            $namVao = (int)($_POST['namVao'] ?? date('Y'));
            
            // Validate mã trường
            if (!preg_match('/^[A-Z0-9]{3,10}$/', $maTruong)) {
                throw new Exception("Mã trường không hợp lệ (3-10 ký tự viết hoa, số)");
            }
            
            // Tạo mã giáo viên tự động
            $maGV = $this->model->taoMaGiaoVien($maTruong, $namVao);
            
            $data = [
                'maGV' => $maGV,
                'hoTen' => trim($_POST['hoTen'] ?? ''),
                'gioiTinh' => $_POST['gioiTinh'] ?? '',
                'ngaySinh' => $_POST['ngaySinh'] ?? '',
                'soDienThoai' => trim($_POST['soDienThoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'diaChi' => trim($_POST['diaChi'] ?? ''),
                'monHocPhuTrach' => trim($_POST['monHocPhuTrach'] ?? ''),
                'trinhDoHocVan' => trim($_POST['trinhDoHocVan'] ?? ''),
                'chucVu' => trim($_POST['chucVu'] ?? 'Giao vien bo mon'),
                'tinhTrangTaiKhoan' => $_POST['tinhTrangTaiKhoan'] ?? 'ACTIVE'
            ];

            $this->model->themGiaoVien($data);
            $_SESSION['flash_success'] = "Thêm giáo viên thành công với mã: {$maGV}";
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;

        } catch (Exception $e) {
            error_log("Error store QuanLyGiaoVienController: " . $e->getMessage());
            $_SESSION['flash_error'] = $e->getMessage();
            
            // Giữ lại dữ liệu form
            $data = $_POST;
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/tao_giao_vien.php';
        }
    }

    /**
     * Hiển thị form cập nhật giáo viên
     */
    public function edit() {
        require_role(['bgh']);

        $maGV = $_GET['maGV'] ?? '';
        
        if (empty($maGV)) {
            $_SESSION['flash_error'] = 'Không tìm thấy mã giáo viên';
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($maGV);
        }

        try {
            // Lấy thông tin giáo viên
            $giaoVien = $this->model->getGiaoVienByMa($maGV);
            if (!$giaoVien) {
                throw new Exception('Không tìm thấy giáo viên');
            }

            // Render form
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php';

        } catch (Exception $e) {
            error_log("Error edit QuanLyGiaoVienController: " . $e->getMessage());
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;
        }
    }

    /**
     * Xử lý cập nhật giáo viên
     */
    private function update($maGV) {
        try {
            $data = [
                'hoTen' => trim($_POST['hoTen'] ?? ''),
                'gioiTinh' => $_POST['gioiTinh'] ?? '',
                'ngaySinh' => $_POST['ngaySinh'] ?? '',
                'soDienThoai' => trim($_POST['soDienThoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'diaChi' => trim($_POST['diaChi'] ?? ''),
                'monHocPhuTrach' => trim($_POST['monHocPhuTrach'] ?? ''),
                'trinhDoHocVan' => trim($_POST['trinhDoHocVan'] ?? ''),
                'chucVu' => trim($_POST['chucVu'] ?? 'Giao vien bo mon'),
                'tinhTrangTaiKhoan' => $_POST['tinhTrangTaiKhoan'] ?? 'ACTIVE'
            ];

            $this->model->capNhatGiaoVien($maGV, $data);
            $_SESSION['flash_success'] = 'Cập nhật thông tin giáo viên thành công';
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;

        } catch (Exception $e) {
            error_log("Error update QuanLyGiaoVienController: " . $e->getMessage());
            $_SESSION['flash_error'] = $e->getMessage();
            
            // Giữ lại dữ liệu form
            $giaoVien = array_merge($this->model->getGiaoVienByMa($maGV) ?? [], $_POST);
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php';
        }
    }

    /**
     * Xem chi tiết giáo viên
     */
    public function view() {
        require_role(['bgh']);

        $maGV = $_GET['maGV'] ?? '';
        
        if (empty($maGV)) {
            $_SESSION['flash_error'] = 'Không tìm thấy mã giáo viên';
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;
        }

        try {
            $giaoVien = $this->model->getGiaoVienByMa($maGV);
            if (!$giaoVien) {
                throw new Exception('Không tìm thấy giáo viên');
            }

            // TODO: Tạo view chi tiết nếu cần
            require_once __DIR__ . '/../../views/bgh/quanLyHoSoGiaoVien/chi_tiet_giao_vien.php';

        } catch (Exception $e) {
            error_log("Error view QuanLyGiaoVienController: " . $e->getMessage());
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: /public/index.php?action=bgh-giao-vien-list');
            exit;
        }
    }
}

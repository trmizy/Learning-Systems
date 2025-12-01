<?php

require_once __DIR__ . '/../../models/admin/quanLyTaiKhoanModel.php';

class QuanLyTaiKhoanController {
    private $model;

    public function __construct($database) {
        $this->model = new QuanLyTaiKhoanModel($database);
    }

    /**
     * Xử lý tạo tài khoản mới
     */
    public function taoTaiKhoan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTaiKhoan = trim($_POST['maTaiKhoan'] ?? '');
            $tenDangNhap = trim($_POST['tenDangNhap'] ?? '');
            $matKhau = trim($_POST['matKhau'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $soDienThoai = trim($_POST['soDienThoai'] ?? '');
            $trangThai = trim($_POST['trangThai'] ?? 'ACTIVE');
            $maTruong = !empty($_POST['maTruong']) ? trim($_POST['maTruong']) : null;
            $vaiTro = $_POST['vaiTro'] ?? [];

            // Validation
            $errors = [];
            if (empty($maTaiKhoan)) {
                $errors[] = 'Mã tài khoản không được để trống';
            }
            if (empty($tenDangNhap)) {
                $errors[] = 'Tên đăng nhập không được để trống';
            }
            if (empty($matKhau) || strlen($matKhau) < 6) {
                $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }
            if (empty($email)) {
                $errors[] = 'Email không được để trống';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => implode('<br>', $errors)];
            }

            // Tạo tài khoản
            $result = $this->model->taoTaiKhoan($maTaiKhoan, $tenDangNhap, $matKhau, $email, $soDienThoai, $trangThai, $maTruong);

            if ($result['success']) {
                // Gán vai trò nếu có
                if (!empty($vaiTro)) {
                    $vaiTroResult = $this->model->ganVaiTro($maTaiKhoan, $vaiTro);
                    if (!$vaiTroResult['success']) {
                        error_log('Lỗi gán vai trò: ' . $vaiTroResult['message']);
                    }
                } else {
                    // Nếu không chọn vai trò, rollback: xóa vĩnh viễn tài khoản vừa tạo
                    $this->model->hardDeleteTaiKhoan($maTaiKhoan);
                    return ['success' => false, 'message' => 'Vui lòng chọn ít nhất một vai trò'];
                }
            }

            return $result;
        }
        return ['success' => false, 'message' => 'Yêu cầu không hợp lệ'];
    }

    /**
     * Xử lý cập nhật tài khoản
     */
    public function capNhatTaiKhoan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTaiKhoan = trim($_POST['maTaiKhoan'] ?? '');
            $tenDangNhap = trim($_POST['tenDangNhap'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $soDienThoai = trim($_POST['soDienThoai'] ?? '');
            $trangThai = trim($_POST['trangThai'] ?? 'ACTIVE');
            $maTruong = trim($_POST['maTruong'] ?? null);
            $vaiTro = $_POST['vaiTro'] ?? [];

            // Validation
            $errors = [];
            if (empty($maTaiKhoan)) {
                $errors[] = 'Mã tài khoản không được để trống';
            }
            if (empty($tenDangNhap)) {
                $errors[] = 'Tên đăng nhập không được để trống';
            }
            if (empty($email)) {
                $errors[] = 'Email không được để trống';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => implode('<br>', $errors)];
            }

            // Cập nhật tài khoản
            $result = $this->model->capNhatTaiKhoan($maTaiKhoan, $tenDangNhap, $email, $soDienThoai, $trangThai, $maTruong);

            if ($result['success'] && !empty($vaiTro)) {
                $this->model->ganVaiTro($maTaiKhoan, $vaiTro);
            }

            return $result;
        }
        return ['success' => false, 'message' => 'Yêu cầu không hợp lệ'];
    }

    /**
     * Xử lý cập nhật mật khẩu
     */
    public function capNhatMatKhau() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTaiKhoan = trim($_POST['maTaiKhoan'] ?? '');
            $matKhauMoi = trim($_POST['matKhauMoi'] ?? '');
            $matKhauConfirm = trim($_POST['matKhauConfirm'] ?? '');

            // Validation
            if (empty($maTaiKhoan)) {
                return ['success' => false, 'message' => 'Mã tài khoản không được để trống'];
            }
            if (empty($matKhauMoi) || strlen($matKhauMoi) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
            }
            if ($matKhauMoi !== $matKhauConfirm) {
                return ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp'];
            }

            return $this->model->capNhatMatKhau($maTaiKhoan, $matKhauMoi);
        }
        return ['success' => false, 'message' => 'Yêu cầu không hợp lệ'];
    }

    /**
     * Xử lý gán vai trò
     */
    public function ganVaiTro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTaiKhoan = trim($_POST['maTaiKhoan'] ?? '');
            $vaiTro = $_POST['vaiTro'] ?? [];

            if (empty($maTaiKhoan)) {
                return ['success' => false, 'message' => 'Mã tài khoản không được để trống'];
            }

            if (empty($vaiTro)) {
                return ['success' => false, 'message' => 'Vui lòng chọn ít nhất một vai trò'];
            }

            return $this->model->ganVaiTro($maTaiKhoan, $vaiTro);
        }
        return ['success' => false, 'message' => 'Yêu cầu không hợp lệ'];
    }

    /**
     * Xử lý xóa tài khoản
     */
    public function xoaTaiKhoan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maTaiKhoan = trim($_POST['maTaiKhoan'] ?? '');

            if (empty($maTaiKhoan)) {
                return ['success' => false, 'message' => 'Mã tài khoản không được để trống'];
            }

            // Không cho phép xóa tài khoản admin
            $taiKhoan = $this->model->getChiTietTaiKhoan($maTaiKhoan);
            if ($taiKhoan && strpos($taiKhoan['vaiTro'], 'admin') !== false) {
                return ['success' => false, 'message' => 'Không thể xóa tài khoản admin'];
            }

            return $this->model->xoaTaiKhoan($maTaiKhoan);
        }
        return ['success' => false, 'message' => 'Yêu cầu không hợp lệ'];
    }

    /**
     * Lấy danh sách tài khoản
     */
    public function getDanhSachTaiKhoan($search = '', $trangThai = '') {
        return $this->model->getDanhSachTaiKhoan($search, $trangThai);
    }

    /**
     * Lấy chi tiết tài khoản
     */
    public function getChiTietTaiKhoan($maTaiKhoan) {
        return $this->model->getChiTietTaiKhoan($maTaiKhoan);
    }

    /**
     * Lấy danh sách vai trò
     */
    public function getDanhSachVaiTroTaiKhoan($maTaiKhoan) {
        return $this->model->getDanhSachVaiTroTaiKhoan($maTaiKhoan);
    }

    /**
     * Lấy danh sách vai trò có sẵn
     */
    public function getDanhSachVaiTroCoSan() {
        return $this->model->getDanhSachVaiTroCoSan();
    }

    /**
     * Lấy danh sách trạng thái
     */
    public function getDanhSachTrangThai() {
        return $this->model->getDanhSachTrangThai();
    }
}
?>

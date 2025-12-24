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
            $tenDangNhap = trim($_POST['tenDangNhap'] ?? '');
            $matKhau = trim($_POST['matKhau'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $soDienThoai = trim($_POST['soDienThoai'] ?? '');
            $trangThai = trim($_POST['trangThai'] ?? 'ACTIVE');
            $maTruong = !empty($_POST['maTruong']) ? trim($_POST['maTruong']) : null;
            $vaiTro = $_POST['vaiTro'] ?? [];

            // Validation
            $errors = [];
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
            if (empty($vaiTro)) {
                $errors[] = 'Vui lòng chọn loại tài khoản';
            }

            // Kiểm tra vai trò (chỉ cho phép hs, ph, gvbm)
            $allowedRoles = ['hs', 'ph', 'gvbm'];
            foreach ($vaiTro as $role) {
                if (!in_array($role, $allowedRoles)) {
                    $errors[] = 'Loại tài khoản không hợp lệ';
                    break;
                }
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => implode('<br>', $errors)];
            }

            // Phát sinh mã tài khoản dựa trên vai trò được chọn
            $selectedRole = $vaiTro[0]; // Lấy vai trò đầu tiên từ danh sách
            $namVao = date('y'); // Năm hiện tại (2 chữ số)
            
            $maTaiKhoan = $this->model->generateMaTaiKhoan($selectedRole, $maTruong, $namVao);
            
            if (empty($maTaiKhoan)) {
                return ['success' => false, 'message' => 'Không thể phát sinh mã tài khoản. Vui lòng thử lại'];
            }

            // Tạo tài khoản với mã vừa phát sinh
            $result = $this->model->taoTaiKhoan($maTaiKhoan, $tenDangNhap, $matKhau, $email, $soDienThoai, $trangThai, $maTruong);

            if ($result['success']) {
                // Gán vai trò
                $vaiTroResult = $this->model->ganVaiTro($maTaiKhoan, $vaiTro);
                if (!$vaiTroResult['success']) {
                    error_log('Lỗi gán vai trò: ' . $vaiTroResult['message']);
                }

                // Nếu là tài khoản Học Sinh, tạo thêm bản ghi ở bảng hocsinh
                if (in_array('hs', $vaiTro, true)) {
                    $hsRes = $this->model->taoHocSinhLienKet($maTaiKhoan, $maTruong ?: 'TR001', $email, $soDienThoai, $namVao);
                    if (!$hsRes['success']) {
                        // Rollback tài khoản nếu tạo HS thất bại
                        $this->model->hardDeleteTaiKhoan($maTaiKhoan);
                        return ['success' => false, 'message' => 'Tạo tài khoản HS thất bại: ' . ($hsRes['message'] ?? 'Lỗi không xác định')];
                    }
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

            // Transaction để đảm bảo đồng bộ giữa taikhoan và hồ sơ liên quan
            $this->model->beginTransaction();
            $result = $this->model->capNhatTaiKhoan($maTaiKhoan, $tenDangNhap, $email, $soDienThoai, $trangThai, $maTruong);
            error_log('[capNhatTaiKhoan] ma=' . $maTaiKhoan . ' success=' . ($result['success'] ? '1' : '0') . ' msg=' . ($result['message'] ?? ''));

            if ($result['success'] && !empty($vaiTro)) {
                $this->model->ganVaiTro($maTaiKhoan, $vaiTro);
            }

            // Nếu tài khoản có vai trò HS, cập nhật bảng hocsinh theo dữ liệu form
            $isHS = (!empty($vaiTro) && in_array('hs', $vaiTro, true));
            if (!$isHS) {
                // nếu vaiTrò không gửi, dùng vai trò hiện tại trong DB
                $rolesNow = $this->getDanhSachVaiTroTaiKhoan($maTaiKhoan);
                $isHS = in_array('hs', $rolesNow, true);
            }
            if ($result['success'] && $isHS) {
                $hsData = [
                    'hoTen'      => $_POST['hs_hoTen'] ?? null,
                    'ngaySinh'   => $_POST['hs_ngaySinh'] ?? null,
                    'soCCCD'     => $_POST['hs_soCCCD'] ?? null,
                    'diaChi'     => $_POST['hs_diaChi'] ?? null,
                    'emailHS'    => $_POST['hs_email'] ?? null,
                    'gioiTinh'   => $_POST['hs_gioiTinh'] ?? null,
                    'sdtHS'      => $_POST['hs_sdt'] ?? null,
                    'maLop'      => $_POST['hs_maLop'] ?? null,
                    'trangThaiHS'=> $_POST['hs_trangThai'] ?? null,
                ];
                $resHS = $this->model->capNhatHocSinhByMaTaiKhoan($maTaiKhoan, $hsData);
                if (!$resHS['success']) {
                    $this->model->rollBack();
                    error_log('Update HS failed for ' . $maTaiKhoan . ': ' . ($resHS['message'] ?? '')); 
                    return $resHS; // trả lỗi chi tiết cập nhật học sinh
                }
            }

            // Nếu tài khoản có vai trò PH, cập nhật bảng phuhuynh theo dữ liệu form
            $isPH = (!empty($vaiTro) && in_array('ph', $vaiTro, true));
            if (!$isPH) {
                $rolesNow = $this->getDanhSachVaiTroTaiKhoan($maTaiKhoan);
                $isPH = in_array('ph', $rolesNow, true);
            }
            if ($result['success'] && $isPH) {
                $phData = [
                    'hoTen'       => $_POST['ph_hoTen'] ?? null,
                    'emailPH'     => $_POST['ph_email'] ?? null,
                    'sdtPH'       => $_POST['ph_sdt'] ?? null,
                    'diaChi'      => $_POST['ph_diaChi'] ?? null,
                    'gioiTinh'    => $_POST['ph_gioiTinh'] ?? null,
                    'moiQuanHe'   => $_POST['ph_moiQuanHe'] ?? null,
                ];
                $resPH = $this->model->capNhatPhuHuynhByMaTaiKhoan($maTaiKhoan, $phData);
                if (!$resPH['success']) {
                    $this->model->rollBack();
                    error_log('Update PH failed for ' . $maTaiKhoan . ': ' . ($resPH['message'] ?? ''));
                    return $resPH;
                }
            }

            // Nếu tài khoản có vai trò GV, cập nhật bảng giaovienbomon theo dữ liệu form
            $isGV = (!empty($vaiTro) && in_array('gv', $vaiTro, true));
            if (!$isGV) {
                $rolesNow = $this->getDanhSachVaiTroTaiKhoan($maTaiKhoan);
                $isGV = in_array('gv', $rolesNow, true) || in_array('gvbm', $rolesNow, true);
            }
            if ($result['success'] && $isGV) {
                $gvData = [
                    'hoTen'            => $_POST['gv_hoTen'] ?? null,
                    'ngaySinh'         => $_POST['gv_ngaySinh'] ?? null,
                    'gioiTinh'         => $_POST['gv_gioiTinh'] ?? null,
                    'emailGV'          => $_POST['gv_email'] ?? null,
                    'sdtGV'            => $_POST['gv_sdt'] ?? null,
                    'diaChi'           => $_POST['gv_diaChi'] ?? null,
                    'monHocPhuTrach'   => $_POST['gv_monHocPhuTrach'] ?? null,
                    'trinhDoHocVan'    => $_POST['gv_trinhDoHocVan'] ?? null,
                    'chucVu'           => $_POST['gv_chucVu'] ?? null,
                    'anhDaiDien'       => $_POST['gv_anhDaiDien'] ?? null,
                    'soCCCD'           => $_POST['gv_soCCCD'] ?? null,
                    'tinhTrangTaiKhoan'=> $_POST['gv_tinhTrangTaiKhoan'] ?? null,
                ];
                $resGV = $this->model->capNhatGiaoVienByMaTaiKhoan($maTaiKhoan, $gvData);
                if (!$resGV['success']) {
                    $this->model->rollBack();
                    error_log('Update GV failed for ' . $maTaiKhoan . ': ' . ($resGV['message'] ?? ''));
                    return $resGV;
                }
            }

            // Commit nếu mọi thứ đều thành công
            if ($result['success']) {
                $this->model->commit();
            } else {
                $this->model->rollBack();
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

    /** Lấy thông tin học sinh theo maTaiKhoan (phục vụ form edit) */
    public function getHocSinhByMaTaiKhoan($maTaiKhoan) {
        return $this->model->getHocSinhByMaTaiKhoan($maTaiKhoan);
    }

    /** Lấy thông tin phụ huynh theo maTaiKhoan (phục vụ form edit) */
    public function getPhuHuynhByMaTaiKhoan($maTaiKhoan) {
        return $this->model->getPhuHuynhByMaTaiKhoan($maTaiKhoan);
    }

    /** Lấy thông tin giáo viên theo maTaiKhoan (phục vụ form edit) */
    public function getGiaoVienByMaTaiKhoan($maTaiKhoan) {
        return $this->model->getGiaoVienByMaTaiKhoan($maTaiKhoan);
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

    /**
     * Lấy danh sách trường
     */
    public function getDanhSachTruong() {
        return $this->model->getDanhSachTruong();
    }

    /**
     * Phát sinh mã tài khoản dựa trên role
     * Format:
     * - Học sinh: TRXXXHSYYZZZZ
     * - Phụ Huynh: TRXXXPHYYZZZZ
     * - Giáo viên: TRXXXGVYYZZZZ
     */
    public function generateMaTaiKhoan($role, $maTruong = 'TR001', $namVao = null) {
        return $this->model->generateMaTaiKhoan($role, $maTruong, $namVao);
    }
}
?>

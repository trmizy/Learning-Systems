<?php

class QuanLyTaiKhoanModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Lấy danh sách tất cả tài khoản
     */
    public function getDanhSachTaiKhoan($search = '', $trangThai = '') {
        try {
            $query = "SELECT tk.*, GROUP_CONCAT(tv.maVaiTro SEPARATOR ', ') as vaiTro 
                      FROM taikhoan tk 
                      LEFT JOIN taikhoan_vaitro tv ON tk.maTaiKhoan = tv.maTaiKhoan
                      WHERE 1=1";
            
            if (!empty($search)) {
                $query .= " AND (tk.tenDangNhap LIKE :search OR tk.email LIKE :search OR tk.maTaiKhoan LIKE :search)";
            }
            
            // By default, hide INACTIVE accounts from the listing. If a specific trangThai filter is provided, apply it.
            if (!empty($trangThai)) {
                $query .= " AND tk.trangThai = :trangThai";
            } else {
                // Exclude INACTIVE accounts by default (case-insensitive, trimming whitespace)
                $query .= " AND UPPER(TRIM(COALESCE(tk.trangThai, ''))) != 'INACTIVE'";
            }
            
            $query .= " GROUP BY tk.maTaiKhoan ORDER BY tk.maTaiKhoan DESC";
            
            $stmt = $this->db->prepare($query);
            
            if (!empty($search)) {
                $searchParam = "%{$search}%";
                $stmt->bindParam(':search', $searchParam);
            }
            
            if (!empty($trangThai)) {
                $stmt->bindParam(':trangThai', $trangThai);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy chi tiết một tài khoản
     */
    public function getChiTietTaiKhoan($maTaiKhoan) {
        try {
            $query = "SELECT tk.*, GROUP_CONCAT(tv.maVaiTro SEPARATOR ', ') as vaiTro 
                      FROM taikhoan tk 
                      LEFT JOIN taikhoan_vaitro tv ON tk.maTaiKhoan = tv.maTaiKhoan
                      WHERE tk.maTaiKhoan = :maTaiKhoan
                      GROUP BY tk.maTaiKhoan";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Lấy danh sách vai trò của một tài khoản
     */
    public function getDanhSachVaiTroTaiKhoan($maTaiKhoan) {
        try {
            $query = "SELECT maVaiTro FROM taikhoan_vaitro WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $vaiTro = [];
            foreach ($result as $row) {
                $vaiTro[] = $row['maVaiTro'];
            }
            return $vaiTro;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Tạo tài khoản mới
     */
    public function taoTaiKhoan($maTaiKhoan, $tenDangNhap, $matKhau, $email, $soDienThoai, $trangThai = 'ACTIVE', $maTruong = null) {
        try {
            // Kiểm tra xem maTaiKhoan đã tồn tại?
            $checkQuery = "SELECT maTaiKhoan FROM taikhoan WHERE maTaiKhoan = :maTaiKhoan";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Mã tài khoản đã tồn tại'];
            }

            // Kiểm tra xem tenDangNhap đã tồn tại?
            $checkQuery = "SELECT maTaiKhoan FROM taikhoan WHERE tenDangNhap = :tenDangNhap";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':tenDangNhap', $tenDangNhap);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
            }

            // Mã hóa mật khẩu
            $matKhauHash = password_hash($matKhau, PASSWORD_DEFAULT);

            $query = "INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                      VALUES (:maTaiKhoan, :tenDangNhap, :matKhau, :email, :soDienThoai, :trangThai, :maTruong)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $stmt->bindParam(':tenDangNhap', $tenDangNhap);
            $stmt->bindParam(':matKhau', $matKhauHash);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':soDienThoai', $soDienThoai);
            $stmt->bindParam(':trangThai', $trangThai);
            $stmt->bindParam(':maTruong', $maTruong);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tài khoản được tạo thành công'];
            } else {
                return ['success' => false, 'message' => 'Lỗi khi tạo tài khoản'];
            }
        } catch (PDOException $e) {
            error_log('Lỗi tạo tài khoản: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function capNhatTaiKhoan($maTaiKhoan, $tenDangNhap, $email, $soDienThoai, $trangThai, $maTruong = null) {
        try {
            // Kiểm tra tenDangNhap trùng với tài khoản khác
            $checkQuery = "SELECT maTaiKhoan FROM taikhoan WHERE tenDangNhap = :tenDangNhap AND maTaiKhoan != :maTaiKhoan";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':tenDangNhap', $tenDangNhap);
            $checkStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng bởi tài khoản khác'];
            }

            $query = "UPDATE taikhoan 
                      SET tenDangNhap = :tenDangNhap, email = :email, soDienThoai = :soDienThoai, 
                          trangThai = :trangThai, maTruong = :maTruong
                      WHERE maTaiKhoan = :maTaiKhoan";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $stmt->bindParam(':tenDangNhap', $tenDangNhap);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':soDienThoai', $soDienThoai);
            $stmt->bindParam(':trangThai', $trangThai);
            $stmt->bindParam(':maTruong', $maTruong);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tài khoản được cập nhật thành công'];
            } else {
                return ['success' => false, 'message' => 'Lỗi khi cập nhật tài khoản'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Cập nhật mật khẩu
     */
    public function capNhatMatKhau($maTaiKhoan, $matKhauMoi) {
        try {
            $matKhauHash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            
            $query = "UPDATE taikhoan SET matKhau = :matKhau WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':matKhau', $matKhauHash);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Mật khẩu được cập nhật thành công'];
            } else {
                return ['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Gán vai trò cho tài khoản
     */
    public function ganVaiTro($maTaiKhoan, $danhSachVaiTro) {
        try {
            // Xóa tất cả vai trò cũ
            $deleteQuery = "DELETE FROM taikhoan_vaitro WHERE maTaiKhoan = :maTaiKhoan";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $deleteStmt->execute();

            // Thêm vai trò mới
            $insertQuery = "INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) VALUES (:maTaiKhoan, :maVaiTro)";
            $insertStmt = $this->db->prepare($insertQuery);

            foreach ($danhSachVaiTro as $maVaiTro) {
                $insertStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
                $insertStmt->bindParam(':maVaiTro', $maVaiTro);
                $insertStmt->execute();
            }

            return ['success' => true, 'message' => 'Vai trò được cập nhật thành công'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Xóa tài khoản
     */
    public function xoaTaiKhoan($maTaiKhoan) {
        try {
            // Soft-delete: mark account as INACTIVE so it is hidden from default UI lists
            $query = "UPDATE taikhoan SET trangThai = 'INACTIVE' WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            if ($stmt->execute()) {
                $affected = $stmt->rowCount();
                error_log("Soft-delete executed for maTaiKhoan={$maTaiKhoan}, affected={$affected}");
                return ['success' => true, 'message' => 'Tài khoản đã được ẩn (vô hiệu hóa) thành công'];
            } else {
                $err = $stmt->errorInfo();
                error_log("Soft-delete failed for maTaiKhoan={$maTaiKhoan}, error=" . print_r($err, true));
                return ['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái tài khoản'];
            }
        } catch (PDOException $e) {
            error_log('Lỗi CSDL khi soft-delete: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Xóa vĩnh viễn tài khoản (dùng nội bộ để rollback khi tạo mới thất bại)
     */
    public function hardDeleteTaiKhoan($maTaiKhoan) {
        try {
            $this->db->beginTransaction();

            // Xóa vai trò liên kết
            $deleteVaiTroQuery = "DELETE FROM taikhoan_vaitro WHERE maTaiKhoan = :maTaiKhoan";
            $deleteVaiTroStmt = $this->db->prepare($deleteVaiTroQuery);
            $deleteVaiTroStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $deleteVaiTroStmt->execute();

            // Xóa tài khoản
            $deleteQuery = "DELETE FROM taikhoan WHERE maTaiKhoan = :maTaiKhoan";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':maTaiKhoan', $maTaiKhoan);

            if ($deleteStmt->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Tài khoản được xóa vĩnh viễn'];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Lỗi khi xóa tài khoản'];
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy danh sách các vai trò có sẵn
     */
    public function getDanhSachVaiTroCoSan() {
        return [
            'admin' => 'Quản trị viên',
            'bgh' => 'Ban giám hiệu',
            'ph' => 'Phó hiệu trưởng',
            'gvbm' => 'Giáo viên bộ môn',
            'gv' => 'Giáo viên',
            'nhanvienso' => 'Nhân viên sở',
            'hs' => 'Học sinh'
        ];
    }

    /**
     * Lấy danh sách trạng thái tài khoản
     */
    public function getDanhSachTrangThai() {
        return [
            'ACTIVE' => 'Đang hoạt động',
            'INACTIVE' => 'Đã vô hiệu hóa',
            'SUSPENDED' => 'Tạm khóa'
        ];
    }
}
?>

<?php

class QuanLyTaiKhoanModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // Transaction helpers
    public function beginTransaction() { return $this->db->beginTransaction(); }
    public function commit() { return $this->db->commit(); }
    public function rollBack() { return $this->db->rollBack(); }

    /**
     * Lấy danh sách tất cả tài khoản
     */
    public function getDanhSachTaiKhoan($search = '', $trangThai = '') {
        try {
            $query = "SELECT tk.*, GROUP_CONCAT(tv.maVaiTro SEPARATOR ', ') as vaiTro 
                      FROM taikhoan tk 
                      LEFT JOIN taikhoan_vaitro tv ON tk.maTaiKhoan = tv.maTaiKhoan
                      WHERE tk.maTaiKhoan NOT IN (
                          SELECT DISTINCT maTaiKhoan 
                          FROM taikhoan_vaitro 
                          WHERE maVaiTro IN ('ts', 'admin', 'nhanvienso')
                      )";
            
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

            $query = "INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                      VALUES (:maTaiKhoan, :tenDangNhap, :matKhau, :email, :soDienThoai, :trangThai, :maTruong)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            $stmt->bindParam(':tenDangNhap', $tenDangNhap);
            $stmt->bindParam(':matKhau', $matKhau);
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
            if (empty($maTaiKhoan)) {
                return ['success' => false, 'message' => 'Thiếu mã tài khoản khi cập nhật'];
            }

            // Lấy username hiện tại để biết có thực sự thay đổi không
            $curStmt = $this->db->prepare("SELECT tenDangNhap FROM taikhoan WHERE maTaiKhoan = :ma LIMIT 1");
            $curStmt->bindParam(':ma', $maTaiKhoan);
            $curStmt->execute();
            $current = $curStmt->fetchColumn();

            $isUsernameChanged = ($current !== false && $tenDangNhap !== $current);
            if ($isUsernameChanged) {
                // Chỉ kiểm tra trùng khi có thay đổi username
                $checkQuery = "SELECT maTaiKhoan FROM taikhoan WHERE tenDangNhap = :tenDangNhap AND maTaiKhoan != :maTaiKhoan";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bindParam(':tenDangNhap', $tenDangNhap);
                $checkStmt->bindParam(':maTaiKhoan', $maTaiKhoan);
                $checkStmt->execute();
                $conflicts = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($conflicts)) {
                    error_log('[capNhatTaiKhoan] tenDangNhap thay đổi nhưng trùng: new=' . $tenDangNhap . ' | ma=' . $maTaiKhoan . ' | đụng với: ' . implode(',', $conflicts));
                    return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng bởi tài khoản khác'];
                }
            } else {
                // Không thay đổi username: không chặn bởi trùng lặp lịch sử
                error_log('[capNhatTaiKhoan] giữ nguyên tenDangNhap=' . $tenDangNhap . ' | ma=' . $maTaiKhoan . ' -> bỏ qua kiểm tra trùng');
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
            $query = "UPDATE taikhoan SET matKhau = :matKhau WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':matKhau', $matKhauMoi);
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
            'ph' => 'Phụ Huynh',
            'gvbm' => 'Giáo viên',
            'gvcn' => 'Giáo viên chủ nhiệm',
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

    /**
     * Lấy danh sách tất cả trường từ database
     */
    public function getDanhSachTruong() {
        try {
            $query = "SELECT maTruong, tenTruong FROM truong ORDER BY tenTruong ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Lỗi lấy danh sách trường: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Phát sinh mã tài khoản dựa trên role, mã trường và năm vào
     * Format:
     * - Học sinh: TRXXXHSYYZZZZ
     * - Phụ Huynh: TRXXXPHYYZZZZ
     * - Giáo viên: TRXXXGVYYZZZZ
     */
    public function generateMaTaiKhoan($role, $maTruong = 'TR001', $namVao = null) {
        try {
            // Theo format DB: maTaiKhoan bắt đầu bằng TK + mã vai trò + số thứ tự
            // Ví dụ: HS -> TKHS0001, PH -> TKPH0001, GV -> TKGV0001, BGH -> TKBGH001
            $map = [
                'hs'   => ['prefix' => 'TKHS', 'pad' => 4],
                'ph'   => ['prefix' => 'TKPH', 'pad' => 4],
                'gv'   => ['prefix' => 'TKGV', 'pad' => 4],
                'gvbm' => ['prefix' => 'TKGV', 'pad' => 4],
                'gvcn' => ['prefix' => 'TKGV', 'pad' => 4],
                'bgh'  => ['prefix' => 'TKBGH', 'pad' => 3],
            ];

            $cfg = $map[$role] ?? ['prefix' => 'TK', 'pad' => 4];
            $prefix = $cfg['prefix'];
            $pad = $cfg['pad'];

            // Lấy mã lớn nhất theo phần số ở cuối để tăng tiếp
            $offset = strlen($prefix) + 1; // vị trí bắt đầu phần số (1-based cho SUBSTRING)
            $sql = "SELECT maTaiKhoan 
                    FROM taikhoan 
                    WHERE maTaiKhoan LIKE :prefix
                    ORDER BY CAST(SUBSTRING(maTaiKhoan, $offset) AS UNSIGNED) DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $like = $prefix . '%';
            $stmt->bindParam(':prefix', $like);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $lastNumber = 0;
            if ($row && isset($row['maTaiKhoan'])) {
                if (preg_match('/^(?:' . preg_quote($prefix, '/') . ')(\d+)$/', $row['maTaiKhoan'], $m)) {
                    $lastNumber = (int)$m[1];
                } else {
                    // Thử tách dãy số cuối cùng
                    if (preg_match('/(\d+)$/', $row['maTaiKhoan'], $m2)) {
                        $lastNumber = (int)$m2[1];
                    }
                }
            }

            $next = $lastNumber + 1;
            return $prefix . str_pad((string)$next, $pad, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log('Lỗi phát sinh mã tài khoản: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Phát sinh mã học sinh theo format DB: TRxxxHSYY + 4 số
     */
    public function generateMaHS($maTruong = 'TR001', $namVao = null) {
        try {
            $yy = $namVao ? substr($namVao, -2) : date('y');
            $base = strtoupper($maTruong) . 'HS' . $yy; // ví dụ: TR001HS25
            $offset = strlen($base) + 1; // vị trí bắt đầu phần số thứ tự
            $sql = "SELECT maHS FROM hocsinh 
                    WHERE maHS LIKE :pattern
                    ORDER BY CAST(SUBSTRING(maHS, $offset) AS UNSIGNED) DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $pattern = $base . '%';
            $stmt->bindParam(':pattern', $pattern);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $last = 0;
            if ($row && isset($row['maHS']) && preg_match('/(\d+)$/', $row['maHS'], $m)) {
                $last = (int)$m[1];
            }
            $next = $last + 1;
            return $base . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log('Lỗi phát sinh mã học sinh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Phát sinh mã phụ huynh theo format DB: TRxxxPHYY + 4 số
     */
    public function generateMaPH($maTruong = 'TR001', $namVao = null) {
        try {
            $yy = $namVao ? substr($namVao, -2) : date('y');
            $base = strtoupper($maTruong) . 'PH' . $yy; // ví dụ: TR001PH25
            $offset = strlen($base) + 1;
            $sql = "SELECT maPH FROM phuhuynh 
                    WHERE maPH LIKE :pattern
                    ORDER BY CAST(SUBSTRING(maPH, $offset) AS UNSIGNED) DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $pattern = $base . '%';
            $stmt->bindParam(':pattern', $pattern);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $last = 0;
            if ($row && isset($row['maPH']) && preg_match('/(\d+)$/', $row['maPH'], $m)) {
                $last = (int)$m[1];
            }
            $next = $last + 1;
            return $base . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log('Lỗi phát sinh mã phụ huynh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Phát sinh mã giáo viên theo format DB: TRxxxGVYY + 4 số
     */
    public function generateMaGV($maTruong = 'TR001', $namVao = null) {
        try {
            $yy = $namVao ? substr($namVao, -2) : date('y');
            $base = strtoupper($maTruong) . 'GV' . $yy; // ví dụ: TR001GV25
            $offset = strlen($base) + 1;
            $sql = "SELECT maGV FROM giaovienbomon 
                    WHERE maGV LIKE :pattern
                    ORDER BY CAST(SUBSTRING(maGV, $offset) AS UNSIGNED) DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $pattern = $base . '%';
            $stmt->bindParam(':pattern', $pattern);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $last = 0;
            if ($row && isset($row['maGV']) && preg_match('/(\d+)$/', $row['maGV'], $m)) {
                $last = (int)$m[1];
            }
            $next = $last + 1;
            return $base . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log('Lỗi phát sinh mã giáo viên: ' . $e->getMessage());
            return null;
        }
    }

    /** Lấy mã trường theo `maTaiKhoan` */
    private function getMaTruongByTaiKhoan($maTaiKhoan) {
        try {
            $sql = "SELECT maTruong FROM taikhoan WHERE maTaiKhoan = :ma LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma', $maTaiKhoan);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['maTruong'] ?? '';
        } catch (PDOException $e) {
            return '';
        }
    }

    /**
     * Tạo bản ghi hocsinh tối thiểu và liên kết với tài khoản (role HS)
     */
    public function taoHocSinhLienKet($maTaiKhoan, $maTruong = 'TR001', $email = null, $soDienThoai = null, $namVao = null) {
        try {
            $maHS = $this->generateMaHS($maTruong, $namVao);
            if (!$maHS) {
                return ['success' => false, 'message' => 'Không thể phát sinh mã học sinh'];
            }

            $sql = "INSERT INTO hocsinh (maHS, email, sdt, trangThai, maTaiKhoan, maLop)
                    VALUES (:maHS, :email, :sdt, 'DANGHOC', :maTaiKhoan, NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':maHS', $maHS);
            $stmt->bindValue(':email', $email ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':sdt', $soDienThoai ?: null, PDO::PARAM_STR);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Đã tạo hồ sơ học sinh', 'maHS' => $maHS];
            }
            return ['success' => false, 'message' => 'Lỗi khi tạo hồ sơ học sinh'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy thông tin học sinh theo maTaiKhoan (liên kết tài khoản)
     */
    public function getHocSinhByMaTaiKhoan($maTaiKhoan) {
        try {
            $sql = "SELECT * FROM hocsinh WHERE maTaiKhoan = :ma LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma', $maTaiKhoan);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Lỗi lấy học sinh theo maTaiKhoan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật đầy đủ thông tin học sinh theo maTaiKhoan
     */
    public function capNhatHocSinhByMaTaiKhoan($maTaiKhoan, array $data) {
        try {
            $sql = "UPDATE hocsinh SET 
                        hoTen = :hoTen,
                        ngaySinh = :ngaySinh,
                        soCCCD = :soCCCD,
                        diaChi = :diaChi,
                        email = :email,
                        gioiTinh = :gioiTinh,
                        sdt = :sdt,
                        maLop = :maLop,
                        trangThai = :trangThai
                    WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hoTen', $data['hoTen'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':ngaySinh', $data['ngaySinh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':soCCCD', $data['soCCCD'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':diaChi', $data['diaChi'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['emailHS'] ?? $data['email'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':gioiTinh', $data['gioiTinh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':sdt', $data['sdtHS'] ?? $data['sdt'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':maLop', $data['maLop'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':trangThai', $data['trangThaiHS'] ?? $data['trangThai'] ?? null, PDO::PARAM_STR);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return ['success' => true, 'message' => 'Cập nhật học sinh thành công'];
                }
                // Không có bản ghi để cập nhật -> tạo mới (upsert)
                $maTruong = $this->getMaTruongByTaiKhoan($maTaiKhoan);
                $maHS = $this->generateMaHS($maTruong, date('y'));
                if (!$maHS) {
                    return ['success' => false, 'message' => 'Không thể phát sinh mã học sinh'];
                }
                $ins = $this->db->prepare("INSERT INTO hocsinh (maHS, hoTen, ngaySinh, soCCCD, diaChi, email, gioiTinh, sdt, maLop, trangThai, maTaiKhoan)
                                            VALUES (:maHS, :hoTen, :ngaySinh, :soCCCD, :diaChi, :email, :gioiTinh, :sdt, :maLop, :trangThai, :maTaiKhoan)");
                $ins->bindValue(':maHS', $maHS);
                $ins->bindValue(':hoTen', $data['hoTen'] ?? null);
                $ins->bindValue(':ngaySinh', $data['ngaySinh'] ?? null);
                $ins->bindValue(':soCCCD', $data['soCCCD'] ?? null);
                $ins->bindValue(':diaChi', $data['diaChi'] ?? null);
                $ins->bindValue(':email', $data['emailHS'] ?? $data['email'] ?? null);
                $ins->bindValue(':gioiTinh', $data['gioiTinh'] ?? null);
                $ins->bindValue(':sdt', $data['sdtHS'] ?? $data['sdt'] ?? null);
                $ins->bindValue(':maLop', $data['maLop'] ?? null);
                $ins->bindValue(':trangThai', $data['trangThaiHS'] ?? $data['trangThai'] ?? 'DANGHOC');
                $ins->bindValue(':maTaiKhoan', $maTaiKhoan);
                if ($ins->execute()) {
                    return ['success' => true, 'message' => 'Đã tạo hồ sơ học sinh mới', 'maHS' => $maHS];
                }
                return ['success' => false, 'message' => 'Lỗi khi tạo hồ sơ học sinh'];
            }
            return ['success' => false, 'message' => 'Lỗi khi cập nhật học sinh'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy thông tin phụ huynh theo maTaiKhoan (liên kết tài khoản)
     */
    public function getPhuHuynhByMaTaiKhoan($maTaiKhoan) {
        try {
            $sql = "SELECT * FROM phuhuynh WHERE maTaiKhoan = :ma LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma', $maTaiKhoan);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Lỗi lấy phụ huynh theo maTaiKhoan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật đầy đủ thông tin phụ huynh theo maTaiKhoan
     */
    public function capNhatPhuHuynhByMaTaiKhoan($maTaiKhoan, array $data) {
        try {
            // Kiểm tra đã có hồ sơ phụ huynh chưa
            $existSql = "SELECT maPH FROM phuhuynh WHERE maTaiKhoan = :ma LIMIT 1";
            $existStmt = $this->db->prepare($existSql);
            $existStmt->bindParam(':ma', $maTaiKhoan);
            $existStmt->execute();
            $exists = $existStmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // Đã có hồ sơ -> cập nhật
                $sql = "UPDATE phuhuynh SET 
                            hoTen = :hoTen,
                            email = :email,
                            soDienThoai = :soDienThoai,
                            diaChi = :diaChi,
                            gioiTinh = :gioiTinh,
                            moiQuanHe = :moiQuanHe
                        WHERE maTaiKhoan = :maTaiKhoan";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':hoTen', $data['hoTen'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':email', $data['emailPH'] ?? $data['email'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':soDienThoai', $data['sdtPH'] ?? $data['soDienThoai'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':diaChi', $data['diaChi'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':gioiTinh', $data['gioiTinh'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':moiQuanHe', $data['moiQuanHe'] ?? null, PDO::PARAM_STR);
                $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
                if ($stmt->execute()) {
                    // MySQL có thể trả rowCount=0 nếu giá trị không đổi, nên vẫn coi là thành công
                    return ['success' => true, 'message' => 'Cập nhật phụ huynh thành công'];
                }
                return ['success' => false, 'message' => 'Lỗi khi cập nhật phụ huynh'];
            }

            // Chưa có hồ sơ -> tạo mới (upsert)
            $maTruong = $this->getMaTruongByTaiKhoan($maTaiKhoan);
            $maPH = $this->generateMaPH($maTruong, date('y'));
            if (!$maPH) {
                return ['success' => false, 'message' => 'Không thể phát sinh mã phụ huynh'];
            }
            $ins = $this->db->prepare("INSERT INTO phuhuynh (maPH, hoTen, email, soDienThoai, diaChi, gioiTinh, moiQuanHe, maTaiKhoan)
                                        VALUES (:maPH, :hoTen, :email, :soDienThoai, :diaChi, :gioiTinh, :moiQuanHe, :maTaiKhoan)");
            $ins->bindValue(':maPH', $maPH);
            $ins->bindValue(':hoTen', $data['hoTen'] ?? null);
            $ins->bindValue(':email', $data['emailPH'] ?? $data['email'] ?? null);
            $ins->bindValue(':soDienThoai', $data['sdtPH'] ?? $data['soDienThoai'] ?? null);
            $ins->bindValue(':diaChi', $data['diaChi'] ?? null);
            $ins->bindValue(':gioiTinh', $data['gioiTinh'] ?? null);
            $ins->bindValue(':moiQuanHe', $data['moiQuanHe'] ?? null);
            $ins->bindValue(':maTaiKhoan', $maTaiKhoan);
            if ($ins->execute()) {
                return ['success' => true, 'message' => 'Đã tạo hồ sơ phụ huynh mới', 'maPH' => $maPH];
            }
            return ['success' => false, 'message' => 'Lỗi khi tạo hồ sơ phụ huynh'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy thông tin giáo viên theo maTaiKhoan (liên kết tài khoản)
     */
    public function getGiaoVienByMaTaiKhoan($maTaiKhoan) {
        try {
            $sql = "SELECT * FROM giaovienbomon WHERE maTaiKhoan = :ma LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma', $maTaiKhoan);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Lỗi lấy giáo viên theo maTaiKhoan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật đầy đủ thông tin giáo viên theo maTaiKhoan
     */
    public function capNhatGiaoVienByMaTaiKhoan($maTaiKhoan, array $data) {
        try {
            $sql = "UPDATE giaovienbomon SET 
                        hoTen = :hoTen,
                        ngaySinh = :ngaySinh,
                        gioiTinh = :gioiTinh,
                        email = :email,
                        soDienThoai = :soDienThoai,
                        diaChi = :diaChi,
                        monHocPhuTrach = :monHocPhuTrach,
                        trinhDoHocVan = :trinhDoHocVan,
                        chucVu = :chucVu,
                        anhDaiDien = :anhDaiDien,
                        soCCCD = :soCCCD,
                        tinhTrangTaiKhoan = :tinhTrangTaiKhoan
                    WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hoTen', $data['hoTen'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':ngaySinh', $data['ngaySinh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':gioiTinh', $data['gioiTinh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['emailGV'] ?? $data['email'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':soDienThoai', $data['sdtGV'] ?? $data['soDienThoai'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':diaChi', $data['diaChi'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':monHocPhuTrach', $data['monHocPhuTrach'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':trinhDoHocVan', $data['trinhDoHocVan'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':chucVu', $data['chucVu'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':anhDaiDien', $data['anhDaiDien'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':soCCCD', $data['soCCCD'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':tinhTrangTaiKhoan', $data['tinhTrangTaiKhoan'] ?? null, PDO::PARAM_STR);
            $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return ['success' => true, 'message' => 'Cập nhật giáo viên thành công'];
                }
                // Upsert: không có bản ghi -> tạo mới
                $maTruong = $this->getMaTruongByTaiKhoan($maTaiKhoan);
                $maGV = $this->generateMaGV($maTruong, date('y'));
                if (!$maGV) {
                    return ['success' => false, 'message' => 'Không thể phát sinh mã giáo viên'];
                }
                $ins = $this->db->prepare("INSERT INTO giaovienbomon (maGV, hoTen, ngaySinh, gioiTinh, email, soDienThoai, diaChi, monHocPhuTrach, trinhDoHocVan, chucVu, anhDaiDien, soCCCD, tinhTrangTaiKhoan, maTaiKhoan)
                                            VALUES (:maGV, :hoTen, :ngaySinh, :gioiTinh, :email, :soDienThoai, :diaChi, :monHocPhuTrach, :trinhDoHocVan, :chucVu, :anhDaiDien, :soCCCD, :tinhTrangTaiKhoan, :maTaiKhoan)");
                $ins->bindValue(':maGV', $maGV);
                $ins->bindValue(':hoTen', $data['hoTen'] ?? null);
                $ins->bindValue(':ngaySinh', $data['ngaySinh'] ?? null);
                $ins->bindValue(':gioiTinh', $data['gioiTinh'] ?? null);
                $ins->bindValue(':email', $data['emailGV'] ?? $data['email'] ?? null);
                $ins->bindValue(':soDienThoai', $data['sdtGV'] ?? $data['soDienThoai'] ?? null);
                $ins->bindValue(':diaChi', $data['diaChi'] ?? null);
                $ins->bindValue(':monHocPhuTrach', $data['monHocPhuTrach'] ?? null);
                $ins->bindValue(':trinhDoHocVan', $data['trinhDoHocVan'] ?? null);
                $ins->bindValue(':chucVu', $data['chucVu'] ?? null);
                $ins->bindValue(':anhDaiDien', $data['anhDaiDien'] ?? null);
                $ins->bindValue(':soCCCD', $data['soCCCD'] ?? null);
                $ins->bindValue(':tinhTrangTaiKhoan', $data['tinhTrangTaiKhoan'] ?? 'ACTIVE');
                $ins->bindValue(':maTaiKhoan', $maTaiKhoan);
                if ($ins->execute()) {
                    return ['success' => true, 'message' => 'Đã tạo hồ sơ giáo viên mới', 'maGV' => $maGV];
                }
                return ['success' => false, 'message' => 'Lỗi khi tạo hồ sơ giáo viên'];
            }
            return ['success' => false, 'message' => 'Lỗi khi cập nhật giáo viên'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }
}
?>

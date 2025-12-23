<?php
/**
 * Model: Quản lý hồ sơ giáo viên
 * Chức năng: CRUD giáo viên bộ môn
 */

// ⚠️ FIX: Sửa đường dẫn - Phải lên 2 cấp (models/bgh/ → Learning_System/)
require_once __DIR__ . '/../../config/database.php';

class GiaoVienModel {
    private $db;

    public function __construct($connection = null) {
        if ($connection) {
            $this->db = $connection;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
    }

    /**
     * Lấy danh sách tất cả giáo viên
     * @return PDOStatement
     */
    public function getAllGiaoVien() {
        $stmt = $this->db->query("
            SELECT 
                maGV,
                hoTen,
                gioiTinh,
                ngaySinh,
                soDienThoai,
                email,
                diaChi,
                monHocPhuTrach,
                trinhDoHocVan,
                chucVu,
                soCCCD,
                tinhTrangTaiKhoan
            FROM giaovienbomon
            ORDER BY hoTen ASC
        ");
        return $stmt;
    }

    /**
     * Lấy thông tin chi tiết một giáo viên
     * @param string $maGV
     * @return array|false
     */
    public function getGiaoVienByMa($maGV) {
        $stmt = $this->db->prepare("
            SELECT 
                maGV,
                hoTen,
                gioiTinh,
                ngaySinh,
                soDienThoai,
                email,
                diaChi,
                monHocPhuTrach,
                trinhDoHocVan,
                chucVu,
                soCCCD,
                tinhTrangTaiKhoan
            FROM giaovienbomon
            WHERE maGV = ?
        ");
        $stmt->execute([$maGV]);
        return $stmt->fetch();
    }

    /**
     * Kiểm tra mã giáo viên đã tồn tại chưa
     * @param string $maGV
     * @return bool
     */
    public function kiemTraMaGVTonTai($maGV) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE maGV = ?");
        $stmt->execute([$maGV]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Tạo mã giáo viên theo quy tắc: TRXXXGVYYZZZZ
     * @param string $maTruong Mã trường (VD: TR001)
     * @param int $namVao Năm vào trường (VD: 2022)
     * @return string Mã giáo viên mới
     * @throws Exception
     */
    public function taoMaGiaoVien($maTruong, $namVao) {
        // Validate năm vào
        if ($namVao < 1900 || $namVao > 2100) {
            throw new Exception("Năm vào không hợp lệ");
        }

        // Lấy 2 số cuối của năm
        $namCuoi = substr((string)$namVao, -2);

        // Thử tạo mã với số ngẫu nhiên, kiểm tra trùng lặp
        $soLanThu = 0;
        $maxTries = 100;

        while ($soLanThu < $maxTries) {
            // Tạo 4 số ngẫu nhiên
            $soNgauNhien = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Format: TRXXXGVYYZZZZ
            $maGV = $maTruong . 'GV' . $namCuoi . $soNgauNhien;

            // Kiểm tra mã đã tồn tại chưa
            if (!$this->kiemTraMaGVTonTai($maGV)) {
                return $maGV;
            }

            $soLanThu++;
        }

        throw new Exception("Không thể tạo mã giáo viên duy nhất sau {$maxTries} lần thử");
    }

    /**
     * Kiểm tra email đã tồn tại chưa (trừ giáo viên hiện tại khi cập nhật)
     * @param string $email
     * @param string|null $maGVHienTai
     * @return bool
     */
    public function kiemTraEmailTonTai($email, $maGVHienTai = null) {
        if ($maGVHienTai) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE email = ? AND maGV != ?");
            $stmt->execute([$email, $maGVHienTai]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra số điện thoại đã tồn tại chưa (trừ giáo viên hiện tại khi cập nhật)
     * @param string $soDienThoai
     * @param string|null $maGVHienTai
     * @return bool
     */
    public function kiemTraSDTTonTai($soDienThoai, $maGVHienTai = null) {
        if ($maGVHienTai) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE soDienThoai = ? AND maGV != ?");
            $stmt->execute([$soDienThoai, $maGVHienTai]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE soDienThoai = ?");
            $stmt->execute([$soDienThoai]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra CCCD đã tồn tại chưa (trừ giáo viên hiện tại khi cập nhật)
     * @param string $soCCCD
     * @param string|null $maGVHienTai
     * @return bool
     */
    public function kiemTraCCCDTonTai($soCCCD, $maGVHienTai = null) {
        if ($maGVHienTai) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE soCCCD = ? AND maGV != ?");
            $stmt->execute([$soCCCD, $maGVHienTai]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM giaovienbomon WHERE soCCCD = ?");
            $stmt->execute([$soCCCD]);
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * ⚠️ MỚI: Tạo mã tài khoản tự động theo pattern TKGVXXXX
     */
    private function taoMaTaiKhoan() {
        $stmt = $this->db->query("
            SELECT maTaiKhoan 
            FROM taikhoan 
            WHERE maTaiKhoan LIKE 'TKGV%' 
            ORDER BY maTaiKhoan DESC 
            LIMIT 1
        ");
        $result = $stmt->fetch();
        
        if ($result) {
            $lastNumber = (int)substr($result['maTaiKhoan'], 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'TKGV' . str_pad((string)$newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ⚠️ CẬP NHẬT: Tạo tài khoản với username = email, password = 123456
     * @param string $email Email giáo viên (dùng làm username)
     * @param string $soDienThoai Số điện thoại
     * @param string $maTruong Mã trường (VD: TR001)
     * @return string Mã tài khoản đã tạo
     * @throws Exception
     */
    private function taoTaiKhoanGiaoVien($email, $soDienThoai, $maTruong) {
        try {
            // 1. Tạo mã tài khoản
            $maTaiKhoan = $this->taoMaTaiKhoan();
            
            // 2. Username = Email
            $username = $email;
            
            // 3. Mật khẩu mặc định = "123456" (không mã hóa)
            $passwordHash = '123456';
            
            // 4. Insert vào bảng taikhoan - CẬP NHẬT: Thêm soDienThoai và maTruong
            $stmtTK = $this->db->prepare("
                INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, maTruong, trangThai)
                VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE')
            ");
            $stmtTK->execute([$maTaiKhoan, $username, $passwordHash, $email, $soDienThoai, $maTruong]);
            
            // 5. Thêm vai trò 'gvbm' vào bảng taikhoan_vaitro
            $stmtVT = $this->db->prepare("
                INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro)
                VALUES (?, 'gvbm')
            ");
            $stmtVT->execute([$maTaiKhoan]);
            
            // Log thông tin tài khoản đã tạo
            error_log("=== TẠO TÀI KHOẢN GIÁO VIÊN ===");
            error_log("Mã TK: $maTaiKhoan | Username: $username | Password: 123456 | SĐT: $soDienThoai | Trường: $maTruong");
            
            return $maTaiKhoan;
            
        } catch (PDOException $e) {
            error_log("Lỗi tạo tài khoản giáo viên: " . $e->getMessage());
            throw new Exception("Không thể tạo tài khoản cho giáo viên");
        }
    }

    /**
     * Tạo mới hồ sơ giáo viên - CẬP NHẬT: Truyền thêm soDienThoai và maTruong
     */
    public function themGiaoVien($data) {
        $data = $this->trimData($data);
        $this->validateGiaoVien($data);

        // Kiểm tra email đã tồn tại
        if ($this->kiemTraEmailTonTai($data['email'])) {
            throw new Exception("Email đã được sử dụng");
        }

        // Kiểm tra số điện thoại đã tồn tại
        if ($this->kiemTraSDTTonTai($data['soDienThoai'])) {
            throw new Exception("Số điện thoại đã được sử dụng");
        }

        // Kiểm tra CCCD đã tồn tại
        if ($this->kiemTraCCCDTonTai($data['soCCCD'])) {
            throw new Exception("Số CCCD đã được sử dụng");
        }

        try {
            $this->db->beginTransaction();

            // ⚠️ CẬP NHẬT: Truyền thêm soDienThoai và maTruong vào hàm tạo tài khoản
            $maTruong = explode('GV', $data['maGV'])[0]; // Lấy TR001 từ TR001GV250001
            $maTaiKhoan = $this->taoTaiKhoanGiaoVien(
                $data['email'], 
                $data['soDienThoai'],
                $maTruong
            );

            // INSERT GIÁO VIÊN
            $stmt = $this->db->prepare("
                INSERT INTO giaovienbomon (
                    maGV, hoTen, gioiTinh, ngaySinh, soDienThoai, 
                    email, diaChi, monHocPhuTrach, trinhDoHocVan, chucVu, 
                    soCCCD, tinhTrangTaiKhoan, maTaiKhoan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $data['maGV'],
                $data['hoTen'],
                $data['gioiTinh'],
                $data['ngaySinh'],
                $data['soDienThoai'],
                $data['email'],
                $data['diaChi'],
                $data['monHocPhuTrach'],
                $data['trinhDoHocVan'] ?? '',
                $data['chucVu'] ?? 'Giáo viên',
                $data['soCCCD'],
                $data['tinhTrangTaiKhoan'] ?? 'ACTIVE',
                $maTaiKhoan
            ]);

            $this->db->commit();
            return $result;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Lỗi thêm giáo viên: " . $e->getMessage());
            throw new Exception("Không thể thêm giáo viên: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin giáo viên - ⚠️ CẬP NHẬT: Thêm CCCD
     * @param string $maGV
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function capNhatGiaoVien($maGV, $data) {
        // Kiểm tra giáo viên tồn tại
        if (!$this->kiemTraMaGVTonTai($maGV)) {
            throw new Exception("Không tìm thấy giáo viên");
        }

        // ⚠️ FIX: Trim dữ liệu trước khi validate
        $data = $this->trimData($data);
        
        // Validate dữ liệu
        $this->validateGiaoVien($data, $maGV);

        // Kiểm tra email trùng (trừ email của chính giáo viên)
        if ($this->kiemTraEmailTonTai($data['email'], $maGV)) {
            throw new Exception("Email đã được sử dụng bởi giáo viên khác");
        }

        // Kiểm tra SĐT trùng (trừ SĐT của chính giáo viên)
        if ($this->kiemTraSDTTonTai($data['soDienThoai'], $maGV)) {
            throw new Exception("Số điện thoại đã được sử dụng bởi giáo viên khác");
        }

        // ⚠️ Kiểm tra CCCD trùng (trừ CCCD của chính giáo viên)
        if ($this->kiemTraCCCDTonTai($data['soCCCD'], $maGV)) {
            throw new Exception("Số CCCD đã được sử dụng bởi giáo viên khác");
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE giaovienbomon 
                SET hoTen = ?,
                    gioiTinh = ?,
                    ngaySinh = ?,
                    soDienThoai = ?,
                    email = ?,
                    diaChi = ?,
                    monHocPhuTrach = ?,
                    trinhDoHocVan = ?,
                    chucVu = ?,
                    soCCCD = ?,
                    tinhTrangTaiKhoan = ?
                WHERE maGV = ?
            ");

            return $stmt->execute([
                $data['hoTen'],
                $data['gioiTinh'],
                $data['ngaySinh'],
                $data['soDienThoai'],
                $data['email'],
                $data['diaChi'],
                $data['monHocPhuTrach'],
                $data['trinhDoHocVan'] ?? '',
                $data['chucVu'] ?? 'Giáo viên',
                $data['soCCCD'], // ⚠️ THÊM CCCD
                $data['tinhTrangTaiKhoan'] ?? 'ACTIVE',
                $maGV
            ]);
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật giáo viên: " . $e->getMessage());
            throw new Exception("Không thể cập nhật thông tin giáo viên");
        }
    }

    /**
     * ⚠️ MỚI: Trim tất cả dữ liệu string trong array
     * @param array $data
     * @return array
     */
    private function trimData($data) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }

    /**
     * Validate dữ liệu giáo viên - ⚠️ FIX: Lấy đúng giá trị từ $data
     */
    private function validateGiaoVien($data, $maGVHienTai = null) {
        // Kiểm tra họ tên
        if (empty($data['hoTen']) || strlen(trim($data['hoTen'])) < 3) {
            throw new Exception("Họ tên phải có ít nhất 3 ký tự");
        }

        // Kiểm tra giới tính
        if (!in_array($data['gioiTinh'], ['Nam', 'Nữ'])) {
            throw new Exception("Giới tính không hợp lệ");
        }

        // Kiểm tra ngày sinh
        if (empty($data['ngaySinh'])) {
            throw new Exception("Ngày sinh không được để trống");
        }

        $ngaySinh = new DateTime($data['ngaySinh']);
        $now = new DateTime();
        $tuoi = $now->diff($ngaySinh)->y;

        if ($tuoi < 22 || $tuoi > 65) {
            throw new Exception("Tuổi giáo viên phải từ 22 đến 65");
        }

        // ⚠️ FIX: Lấy soCCCD trực tiếp từ $data (đã được trim ở Controller)
        $soCCCD = $data['soCCCD'] ?? '';
        
        // DEBUG: Log giá trị CCCD
        error_log("=== Model validateGiaoVien ===");
        error_log("soCCCD từ \$data: '$soCCCD'");
        error_log("Length: " . strlen($soCCCD));
        
        if (empty($soCCCD)) {
            throw new Exception("Số CCCD không được để trống");
        }
        
        if (!preg_match('/^[0-9]{12}$/', $soCCCD)) {
            error_log("CCCD validation failed: '$soCCCD' (length: " . strlen($soCCCD) . ")");
            throw new Exception("Số CCCD không hợp lệ (phải là 12 chữ số, không có khoảng trắng)");
        }

        // Kiểm tra số điện thoại
        if (empty($data['soDienThoai']) || !preg_match('/^0[0-9]{9}$/', $data['soDienThoai'])) {
            throw new Exception("Số điện thoại không hợp lệ (10 số, bắt đầu bằng 0)");
        }

        // Kiểm tra email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email không hợp lệ");
        }

        // Kiểm tra môn học phụ trách
        if (empty($data['monHocPhuTrach'])) {
            throw new Exception("Môn học phụ trách không được để trống");
        }
    }

    /**
     * Tìm kiếm giáo viên
     * @param string $keyword
     * @return PDOStatement
     */
    public function timKiemGiaoVien($keyword) {
        $stmt = $this->db->prepare("
            SELECT 
                maGV,
                hoTen,
                gioiTinh,
                ngaySinh,
                soDienThoai,
                email,
                diaChi,
                monHocPhuTrach,
                trinhDoHocVan,
                chucVu,
                tinhTrangTaiKhoan
            FROM giaovienbomon
            WHERE maGV LIKE ? 
               OR hoTen LIKE ?
               OR email LIKE ?
               OR soDienThoai LIKE ?
               OR monHocPhuTrach LIKE ?
            ORDER BY hoTen ASC
        ");
        $searchTerm = "%{$keyword}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt;
    }
}

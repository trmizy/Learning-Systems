<?php
/**
 * Model: Quản lý hồ sơ giáo viên
 * Chức năng: CRUD giáo viên bộ môn
 */

require_once __DIR__ . '/../config/database.php';

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
     * Tạo mới hồ sơ giáo viên
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function themGiaoVien($data) {
        // Validate dữ liệu
        $this->validateGiaoVien($data);

        // Kiểm tra email đã tồn tại
        if ($this->kiemTraEmailTonTai($data['email'])) {
            throw new Exception("Email đã được sử dụng");
        }

        // Kiểm tra số điện thoại đã tồn tại
        if ($this->kiemTraSDTTonTai($data['soDienThoai'])) {
            throw new Exception("Số điện thoại đã được sử dụng");
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO giaovienbomon (
                    maGV, hoTen, gioiTinh, ngaySinh, soDienThoai, 
                    email, diaChi, monHocPhuTrach, trinhDoHocVan, chucVu, tinhTrangTaiKhoan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
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
                $data['tinhTrangTaiKhoan'] ?? 'ACTIVE'
            ]);
        } catch (PDOException $e) {
            error_log("Lỗi thêm giáo viên: " . $e->getMessage());
            throw new Exception("Không thể thêm giáo viên");
        }
    }

    /**
     * Cập nhật thông tin giáo viên
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
                $data['tinhTrangTaiKhoan'] ?? 'ACTIVE',
                $maGV
            ]);
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật giáo viên: " . $e->getMessage());
            throw new Exception("Không thể cập nhật thông tin giáo viên");
        }
    }

    /**
     * Xóa giáo viên (kiểm tra ràng buộc trước)
     * @param string $maGV
     * @return bool
     * @throws Exception
     */
    public function xoaGiaoVien($maGV) {
        // Kiểm tra giáo viên có đang làm GVCN không
        $stmtGVCN = $this->db->prepare("SELECT COUNT(*) FROM giaovienchunhiem WHERE maGV = ?");
        $stmtGVCN->execute([$maGV]);
        if ($stmtGVCN->fetchColumn() > 0) {
            throw new Exception("Không thể xóa. Giáo viên đang làm chủ nhiệm lớp");
        }

        // Kiểm tra giáo viên có phân công giảng dạy không
        $stmtPC = $this->db->prepare("SELECT COUNT(*) FROM phanconggiangday WHERE maGV = ?");
        $stmtPC->execute([$maGV]);
        if ($stmtPC->fetchColumn() > 0) {
            throw new Exception("Không thể xóa. Giáo viên đang có phân công giảng dạy");
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM giaovienbomon WHERE maGV = ?");
            return $stmt->execute([$maGV]);
        } catch (PDOException $e) {
            error_log("Lỗi xóa giáo viên: " . $e->getMessage());
            throw new Exception("Không thể xóa giáo viên");
        }
    }

    /**
     * Validate dữ liệu giáo viên
     * @param array $data
     * @param string|null $maGVHienTai
     * @throws Exception
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

<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';

class AssignExamModel {
    private PDO $conn;

    public function __construct() {
        $db = method_exists('Database', 'getInstance') ? Database::getInstance() : new Database();
        $this->conn = method_exists($db, 'connect') ? $db->getConnection() : $db->getConnection();
    }

    /** Lấy danh sách khối */
    public function getDanhSachKhoi(): array {
        $stmt = $this->conn->query("
            SELECT DISTINCT REPLACE(khoiLop, 'Khoi ', '') AS soKhoi
            FROM Khoi
            ORDER BY soKhoi ASC
        ");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /** Lấy mã GV từ tài khoản */
    public function layMaGVTheoTaiKhoan(string $maTaiKhoan): ?string {
        $sql = "SELECT maGV 
                FROM giaovienbomon 
                WHERE maTaiKhoan = :tk 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':tk' => $maTaiKhoan]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * ⚠️ FIX: Lấy mã trưởng tổ từ username - JOIN QUA giaovienbomon
     */
    public function getMaTTBMByUsername($username) {
        try {
            // ⚠️ FIX: totruongbomon chỉ có maGV, phải join qua giaovienbomon
            $sql = "SELECT ttbm.maGV
                    FROM taikhoan tk
                    INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
                    INNER JOIN totruongbomon ttbm ON gv.maGV = ttbm.maGV
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            // DEBUG
            error_log("=== getMaTTBMByUsername ===");
            error_log("Username: $username");
            error_log("maGV: " . ($result ? $result['maGV'] : 'NULL'));
            
            return $result ? $result['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaTTBMByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ⚠️ FIX: Lấy môn từ trưởng tổ - DỰA VÀO CỘT monPhuTrach
     */
    public function layMonCuaToTruong(string $maGV) {
        try {
            // ⚠️ FIX: totruongbomon có cột monPhuTrach (VARCHAR) chứa TÊN MÔN
            $sql = "SELECT DISTINCT 
                        mh.maMonHoc, 
                        mh.tenMon
                    FROM totruongbomon ttbm
                    INNER JOIN monhoc mh ON ttbm.monPhuTrach = mh.tenMon
                    WHERE ttbm.maGV = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maGV]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG
            error_log("=== layMonCuaToTruong ===");
            error_log("maGV: $maGV");
            error_log("Rows: " . count($result));
            if (count($result) > 0) {
                error_log("First row: " . print_r($result[0], true));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error layMonCuaToTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy giáo viên theo môn
     */
    public function layGiaoVienTheoMon($maMonHoc) {
        try {
            $sql = "SELECT 
                        gv.maGV,
                        gv.hoTen,
                        gv.monHocPhuTrach
                    FROM giaovienbomon gv
                    WHERE gv.monHocPhuTrach = ?
                      AND gv.tinhTrangTaiKhoan = 'ACTIVE'
                    ORDER BY gv.hoTen";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maMonHoc]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error layGiaoVienTheoMon: " . $e->getMessage());
            return [];
        }
    }

    /** Lưu phân công nhiều GV */
    public function luuPhanCongNhieuGV(
        array $listGV,
        string $khoi,
        string $hocKy,
        string $kyThi,
        int $soLuongDe,
        string $thoiHan,
        string $ghiChu
    ): bool {

        $sql = "
            INSERT INTO BangPhanCongRaDe 
            (khoi, hocKy, kyThi, soLuongDe, thoiHan, ghiChu, maGV)
            VALUES 
            (:khoi, :hocKy, :kyThi, :soLuongDe, :thoiHan, :ghiChu, :maGV)
        ";

        $stmt = $this->conn->prepare($sql);

        foreach ($listGV as $maGV) {
            $stmt->execute([
                ':khoi'      => $khoi,
                ':hocKy'     => $hocKy,
                ':kyThi'     => $kyThi,
                ':soLuongDe' => $soLuongDe,
                ':thoiHan'   => $thoiHan,
                ':ghiChu'    => $ghiChu,
                ':maGV'      => $maGV
            ]);
        }

        return true;
    }

    /** Lấy danh sách phân công */
    public function getDanhSachPhanCong(string $monPhuTrach): array {
        $sql = "
            SELECT 
                pc.khoi,
                pc.hocKy,
                pc.kyThi,
                pc.soLuongDe,
                pc.thoiHan,
                pc.ghiChu,
                gv.hoTen AS giaoVien
            FROM BangPhanCongRaDe pc
            JOIN giaovienbomon gv ON gv.maGV = pc.maGV
            WHERE gv.monHocPhuTrach = :mon
            ORDER BY pc.thoiHan ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':mon' => $monPhuTrach]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy danh sách phân công của 1 giáo viên */
    public function getPhanCongTheoGiaoVien(string $maGV): array {
        $sql = "
            SELECT 
                hocKy,
                kyThi,
                soLuongDe,
                thoiHan,
                ghiChu,
                maGV
            FROM bangphancongrade
            WHERE maGV = :maGV
            ORDER BY thoiHan ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':maGV' => $maGV]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách phân công - SỬA THEO BẢNG bangphancongrade
     */
    public function layDanhSachPhanCong($filters = []) {
        try {
            $sql = "SELECT 
                        pc.hocKy as khoi,
                        pc.hocKy,
                        pc.kyThi,
                        pc.soLuongDe,
                        pc.thoiHan,
                        pc.ghiChu,
                        gv.hoTen as giaoVien
                    FROM bangphancongrade pc
                    INNER JOIN giaovienbomon gv ON pc.maGV = gv.maGV
                    WHERE 1=1";
            
            $params = [];
            
            // Filter theo học kỳ
            if (!empty($filters['hocKy'])) {
                $sql .= " AND pc.hocKy = ?";
                $params[] = $filters['hocKy'];
            }
            
            // Filter theo kỳ thi
            if (!empty($filters['kyThi'])) {
                $sql .= " AND pc.kyThi = ?";
                $params[] = $filters['kyThi'];
            }
            
            $sql .= " ORDER BY pc.thoiHan DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error layDanhSachPhanCong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ⚠️ THÊM MỚI: Lấy danh sách khối từ bảng khoi
     */
    public function layDanhSachKhoi() {
        try {
            $sql = "SELECT DISTINCT 
                        maKhoi as soKhoi,
                        khoiLop as tenKhoi
                    FROM khoi
                    ORDER BY maKhoi ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG
            error_log("=== layDanhSachKhoi ===");
            error_log("Rows: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error layDanhSachKhoi: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ⚠️ THÊM MỚI: Lưu phân công ra đề
     */
    public function luuPhanCong($data) {
        try {
            $this->conn->beginTransaction();
            
            // Lưu cho từng giáo viên trong danh sách
            foreach ($data['listGV'] as $maGV) {
                $sql = "INSERT INTO bangphancongrade 
                        (hocKy, kyThi, soLuongDe, thoiHan, ghiChu, maGV) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($sql);
                $result = $stmt->execute([
                    $data['hocKy'],
                    $data['kyThi'],
                    $data['soLuongDe'],
                    $data['thoiHan'],
                    $data['ghiChu'],
                    $maGV
                ]);
                
                if (!$result) {
                    throw new Exception("Không thể lưu phân công cho giáo viên $maGV");
                }
            }
            
            $this->conn->commit();
            
            // DEBUG
            error_log("=== luuPhanCong SUCCESS ===");
            error_log("Số GV được phân công: " . count($data['listGV']));
            
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error luuPhanCong: " . $e->getMessage());
            return false;
        }
    }
}

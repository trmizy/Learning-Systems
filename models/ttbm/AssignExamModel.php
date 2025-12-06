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

    /** Lấy môn của tổ trưởng */
    public function layMonCuaToTruong(string $maGV): ?string {
        $sql = "SELECT monPhuTrach 
                FROM totruongbomon 
                WHERE maGV = :gv 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':gv' => $maGV]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Lấy danh sách GVBM cùng môn */
    public function getGiaoVienTheoToTruong(string $monPhuTrach): array {
        $sql = "
            SELECT maGV, hoTen, monHocPhuTrach
            FROM giaovienbomon
            WHERE chucVu = 'Giao vien bo mon'
              AND monHocPhuTrach = :mon
            ORDER BY hoTen ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':mon' => $monPhuTrach]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                khoi,
                hocKy,
                kyThi,
                soLuongDe,
                thoiHan,
                ghiChu
            FROM BangPhanCongRaDe
            WHERE maGV = :maGV
            ORDER BY thoiHan ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':maGV' => $maGV]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

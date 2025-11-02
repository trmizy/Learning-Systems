<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';

class AssignExamModel {
    private PDO $conn;

    public function __construct() {
        $db = method_exists('Database', 'getInstance') ? Database::getInstance() : new Database();
        $this->conn = method_exists($db, 'connect') ? $db->getConnection() : $db->getConnection();
    }

    /** Lấy danh sách khối (10,11,12) */
    public function getDanhSachKhoi(): array {
        $stmt = $this->conn->query("SELECT DISTINCT khoiLop FROM Khoi ORDER BY khoiLop ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /** Lấy danh sách giáo viên thuộc tổ trưởng phụ trách */
    public function getGiaoVienTheoToTruong(string $maTT): array {
        $sql = "SELECT maGV, hoTen, monHocPhuTrach, gioiTinh 
                FROM GiaoVienBoMon 
                WHERE maGV != :maTT
                ORDER BY hoTen";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':maTT' => $maTT]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lưu nhiều phân công cho nhiều giáo viên */
    public function luuPhanCongNhieuGV(array $listGV, string $hocKy, string $kyThi, int $soLuongDe, string $thoiHan, string $ghiChu): bool {
        $sql = "INSERT INTO BangPhanCongRaDe (hocKy, kyThi, soLuongDe, thoiHan, ghiChu, maGV)
                VALUES (:hocKy, :kyThi, :soLuongDe, :thoiHan, :ghiChu, :maGV)";
        $stmt = $this->conn->prepare($sql);

        foreach ($listGV as $maGV) {
            $stmt->execute([
                ':hocKy' => $hocKy,
                ':kyThi' => $kyThi,
                ':soLuongDe' => $soLuongDe,
                ':thoiHan' => $thoiHan,
                ':ghiChu' => $ghiChu,
                ':maGV' => $maGV
            ]);
        }
        return true;
    }

    /** Lấy danh sách phân công của tổ trưởng */
    public function getDanhSachPhanCong(): array {
        $sql = "SELECT pc.hocKy, pc.kyThi, pc.soLuongDe, pc.thoiHan, pc.ghiChu, gv.hoTen AS giaoVien
                FROM BangPhanCongRaDe pc
                JOIN GiaoVienBoMon gv ON pc.maGV = gv.maGV
                ORDER BY pc.thoiHan DESC";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /** Lấy danh sách phân công theo giáo viên */
    public function getPhanCongTheoGiaoVien(string $maGV): array {
        $sql = "SELECT hocKy, kyThi, soLuongDe, thoiHan, ghiChu
                FROM BangPhanCongRaDe
                WHERE maGV = :maGV
                ORDER BY thoiHan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':maGV' => $maGV]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

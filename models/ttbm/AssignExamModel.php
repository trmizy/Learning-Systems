<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class AssignExamModel {
    private PDO $conn;

    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }

    /* ========= TTBM ========= */

    public function getMaTTBMByUsername(string $username): ?string {
        $sql = "
            SELECT ttbm.maGV
            FROM taikhoan tk
            JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
            JOIN totruongbomon ttbm ON gv.maGV = ttbm.maGV
            WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Lấy maMonHoc THEO USERNAME – JOIN CHUẨN */
    public function layMaMonHocTheoUsername(string $username): ?string {
        $sql = "
            SELECT mh.maMonHoc
            FROM taikhoan tk
            JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
            JOIN totruongbomon ttbm ON gv.maGV = ttbm.maGV
            JOIN monhoc mh ON ttbm.monPhuTrach = mh.maMonHoc
            WHERE tk.tenDangNhap = ?
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Lấy môn TTBM phụ trách */
    public function layMonCuaToTruong(string $maGV): ?array {
        $sql = "
            SELECT mh.maMonHoc, mh.tenMon
            FROM totruongbomon ttbm
            JOIN monhoc mh ON ttbm.monPhuTrach = mh.maMonHoc
            WHERE ttbm.maGV = ?
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maGV]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function layGiaoVienTheoMon(string $maMonHoc): array {
        $sql = "
            SELECT maGV, hoTen
            FROM giaovienbomon
            WHERE monHocPhuTrach = ?
              AND tinhTrangTaiKhoan = 'ACTIVE'
            ORDER BY hoTen
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maMonHoc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function layDanhSachKhoi(): array {
        return $this->conn
            ->query("SELECT maKhoi AS soKhoi, khoiLop AS tenKhoi FROM khoi ORDER BY maKhoi")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ========= PHÂN CÔNG ========= */

    public function layDanhSachPhanCong(): array {
        $sql = "
            SELECT
                pc.maPhanCongRaDe,
                pc.khoi,
                mh.tenMon,
                pc.hocKy,
                pc.kyThi,
                pc.soLuongDe,
                pc.thoiHan,
                pc.ghiChu,
                gv.hoTen AS giaoVien
            FROM bangphancongrade pc
            LEFT JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
            LEFT JOIN giaovienbomon gv ON pc.maGV = gv.maGV
            ORDER BY pc.thoiHan DESC
        ";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function luuPhanCong(array $data): bool {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO bangphancongrade
                (khoi, maMonHoc, hocKy, kyThi, soLuongDe, thoiHan, ghiChu, maGV)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($data['listGV'] as $maGV) {
                $stmt->execute([
                    $data['khoi'],
                    $data['maMonHoc'],
                    $data['hocKy'],
                    $data['kyThi'],
                    $data['soLuongDe'],
                    $data['thoiHan'],
                    $data['ghiChu'],
                    $maGV
                ]);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('luuPhanCong: ' . $e->getMessage());
            return false;
        }
    }

    public function xoaPhanCong(int $maPhanCongRaDe): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM bangphancongrade WHERE maPhanCongRaDe = ?"
        );
        return $stmt->execute([$maPhanCongRaDe]);
    }
}

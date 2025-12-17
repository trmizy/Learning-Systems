<?php
require_once __DIR__ . '/../../config/database.php';

class DanhSachHocSinhModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách lớp theo khối
     */
    public function getDanhSachLopTheoKhoi($khoi) {
        try {
            $sql = "SELECT maLop, tenLop, siSo 
                    FROM lophoc 
                    WHERE khoi = ? 
                    ORDER BY tenLop";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$khoi]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachLopTheoKhoi: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách học sinh theo lớp
     */
    public function getDanhSachHocSinhTheoLop($maLop) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        hs.ngaySinh,
                        hs.gioiTinh,
                        hs.email,
                        hs.sdt,
                        hs.trangThai,
                        lh.tenLop,
                        lh.khoi
                    FROM hocsinh hs
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE hs.maLop = ?
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachHocSinhTheoLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thống kê nhanh
     */
    public function getThongKeTheoLop($maLop) {
        try {
            $sql = "SELECT 
                        COUNT(*) as tongSo,
                        SUM(CASE WHEN gioiTinh = 'Nam' THEN 1 ELSE 0 END) as soNam,
                        SUM(CASE WHEN gioiTinh = 'Nu' THEN 1 ELSE 0 END) as soNu,
                        SUM(CASE WHEN trangThai = 'DANGHOC' THEN 1 ELSE 0 END) as dangHoc
                    FROM hocsinh
                    WHERE maLop = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongKeTheoLop: " . $e->getMessage());
            return null;
        }
    }
}

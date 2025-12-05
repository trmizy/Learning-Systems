<?php
// File: models/nhanvienso/BaoCaoModel.php
require_once __DIR__ . '/../../config/database.php';

class BaoCaoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllTruong() {
        try {
            return $this->db->query("SELECT maTruong, tenTruong FROM truong ORDER BY tenTruong ASC")->fetchAll();
        } catch (Exception $e) { return []; }
    }

    public function getAllKhoi() {
        try {
            return $this->db->query("SELECT maKhoi, khoiLop FROM khoi ORDER BY maKhoi ASC")->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // === 🚀 HÀM MỚI: Lấy danh sách Năm học có trong hệ thống ===
    public function getAllNamHoc() {
        try {
            // Lấy các năm học duy nhất từ bảng Lớp học
            $sql = "SELECT DISTINCT namHoc FROM lophoc ORDER BY namHoc DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { return ['2024-2025']; } // Mặc định nếu lỗi
    }

    // === 🚀 CẬP NHẬT: Lấy Lớp thuộc đúng Trường + Khối + Năm ===
    public function getLopByTruongKhoi($maTruong, $maKhoi, $namHoc) {
        try {
            // JOIN 4 bảng để đảm bảo lớp thuộc trường đó (thông qua GVCN)
            $sql = "SELECT lh.maLop, lh.tenLop 
                    FROM lophoc lh
                    JOIN giaovienchunhiem gvcn ON lh.maLop = gvcn.lop
                    JOIN giaovienbomon gv ON gvcn.maGV = gv.maGV
                    JOIN taikhoan tk ON gv.maTaiKhoan = tk.maTaiKhoan
                    WHERE tk.maTruong = :maTruong 
                      AND lh.khoi = :maKhoi 
                      AND lh.namHoc = :namHoc
                    ORDER BY lh.tenLop ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':maTruong' => $maTruong,
                ':maKhoi' => $maKhoi,
                ':namHoc' => $namHoc
            ]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    public function findBaoCao($maTruong, $namHoc, $hocKy) {
        try {
            $sql = "SELECT * FROM bangbaocaothongke WHERE maTruong = ? AND namHoc = ? AND hocKy = ? ORDER BY ngayLap DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong, $namHoc, $hocKy]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    public function getThongKeHocLuc($maTruong, $namHoc, $hocKy, $maKhoi = '', $maLop = '') {
        try {
            $sql = "SELECT hl.xepLoaiHocLuc, COUNT(*) as soLuong
                    FROM hocluc hl
                    JOIN hocsinh hs ON hl.maHS = hs.maHS
                    JOIN taikhoan tk ON hs.maTaiKhoan = tk.maTaiKhoan
                    JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE tk.maTruong = :maTruong
                      AND lh.namHoc = :namHoc
                      AND hl.xepLoaiHocLuc IS NOT NULL 
                      AND hl.xepLoaiHocLuc != ''";
            
            $params = [':maTruong' => $maTruong, ':namHoc' => $namHoc];

            if (!empty($maKhoi)) {
                $sql .= " AND lh.khoi = :maKhoi";
                $params[':maKhoi'] = $maKhoi;
            }
            if (!empty($maLop)) {
                $sql .= " AND lh.maLop = :maLop";
                $params[':maLop'] = $maLop;
            }

            $sql .= " GROUP BY hl.xepLoaiHocLuc";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) { return []; }
    }
}
?>
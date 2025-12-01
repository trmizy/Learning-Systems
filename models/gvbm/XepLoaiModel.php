<?php
// File: models/gvbm/XepLoaiModel.php
require_once __DIR__ . '/../../config/database.php';

class XepLoaiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy thông tin lớp chủ nhiệm
    public function getLopChuNhiem($maGV) {
        try {
            $sql = "SELECT gvcn.lop AS maLop, lh.tenLop 
                    FROM giaovienchunhiem gvcn
                    JOIN lophoc lh ON gvcn.lop = lh.maLop
                    WHERE gvcn.maGV = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]);
            return $stmt->fetch();
        } catch (Exception $e) { return false; }
    }

    // Lấy danh sách học sinh để xếp loại
    public function getDanhSachXepLoai($maLop) {
        try {
            // JOIN bảng hocsinh với hocluc và hanhkiem
            // THÊM GROUP BY ĐỂ CHẶN TRÙNG LẶP
            $sql = "SELECT 
                        hs.maHS, hs.hoTen,
                        MAX(hl.diemTrungBinh) as diemTrungBinh, 
                        MAX(hl.xepLoaiHocLuc) as xepLoaiHocLuc, 
                        MAX(hl.nhanXet) as nhanXet,
                        MAX(hk.soBuoiNghiKhongCoPhep) as soBuoiNghiKhongCoPhep, 
                        MAX(hk.soLanViPham) as soLanViPham, 
                        MAX(hk.loaiHanhKiem) as loaiHanhKiem
                    FROM hocsinh hs
                    LEFT JOIN hocluc hl ON hs.maHS = hl.maHS
                    LEFT JOIN hanhkiem hk ON hs.maHS = hk.maHS
                    WHERE hs.maLop = ?
                    GROUP BY hs.maHS  -- <--- DÒNG QUAN TRỌNG GIÚP CHỐNG TRÙNG
                    ORDER BY hs.hoTen ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // Lưu kết quả xếp loại
    public function updateXepLoai($maHS, $hl, $hk, $nhanXet) {
        try {
            $this->db->beginTransaction();

            // Cập nhật hoặc thêm mới Học Lực
            // Kiểm tra xem đã có dòng nào chưa
            $checkHL = $this->db->prepare("SELECT maHocLuc FROM hocluc WHERE maHS = ?");
            $checkHL->execute([$maHS]);
            if ($checkHL->rowCount() > 0) {
                $sql1 = "UPDATE hocluc SET xepLoaiHocLuc = ?, nhanXet = ? WHERE maHS = ?";
                $stmt1 = $this->db->prepare($sql1);
                $stmt1->execute([$hl, $nhanXet, $maHS]);
            } else {
                // Nếu chưa có (dữ liệu thiếu), tạo mới
                $sql1 = "INSERT INTO hocluc (maHocLuc, xepLoaiHocLuc, nhanXet, maHS) VALUES (?, ?, ?, ?)";
                $stmt1 = $this->db->prepare($sql1);
                $maHocLucMoi = 'HL_' . uniqid();
                $stmt1->execute([$maHocLucMoi, $hl, $nhanXet, $maHS]);
            }

            // Cập nhật hoặc thêm mới Hạnh Kiểm
            $checkHK = $this->db->prepare("SELECT maHanhKiem FROM hanhkiem WHERE maHS = ?");
            $checkHK->execute([$maHS]);
            if ($checkHK->rowCount() > 0) {
                $sql2 = "UPDATE hanhkiem SET loaiHanhKiem = ? WHERE maHS = ?";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([$hk, $maHS]);
            } else {
                 // Nếu chưa có, tạo mới
                $sql2 = "INSERT INTO hanhkiem (maHanhKiem, loaiHanhKiem, maHS) VALUES (?, ?, ?)";
                $stmt2 = $this->db->prepare($sql2);
                $maHanhKiemMoi = 'HK_' . uniqid();
                $stmt2->execute([$maHanhKiemMoi, $hk, $maHS]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>
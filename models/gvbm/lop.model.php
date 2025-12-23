<?php
// File: models/gvbm/LopModel.php

require_once __DIR__ . '/../../config/database.php';

class LopModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    // === 🚀 HÀM MỚI CẦN THÊM ===
    public function getMaGVByUsername($username) {
        try {
            // JOIN bảng giaovienbomon với taikhoan để lấy maGV từ username
            $sql = "SELECT gv.maGV 
                    FROM giaovienbomon gv
                    JOIN taikhoan tk ON gv.maTaiKhoan = tk.maTaiKhoan
                    WHERE tk.tenDangNhap = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['maGV'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    // Lấy thông tin lớp chủ nhiệm
    public function getThongTinLopChuNhiemByMaGV($maGiaoVien) {
        try {
            $sql = "SELECT gvcn.lop AS maLop, lh.tenLop, gv.hoTen AS tenGiaoVien
                    FROM giaovienchunhiem gvcn
                    JOIN lophoc lh ON gvcn.lop = lh.maLop
                    JOIN giaovienbomon gv ON gvcn.maGV = gv.maGV
                    WHERE gvcn.maGV = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGiaoVien]);
            return $stmt->fetch();
        } catch (Exception $e) { return false; }
    }

    // Lấy danh sách học sinh
    public function getDanhSachHocSinhByLopId($maLop) {
        try {
            // JOIN với bảng hocluc để lấy diemTrungBinh
            $sql = "SELECT 
                        hs.maHS AS hocSinhId, 
                        hs.hoTen, 
                        hs.ngaySinh, 
                        hs.gioiTinh,
                        hl.diemTrungBinh  -- <--- QUAN TRỌNG: Phải có cột này
                    FROM hocsinh hs
                    LEFT JOIN hocluc hl ON hs.maHS = hl.maHS
                    WHERE hs.maLop = :maLop 
                    ORDER BY hs.hoTen ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maLop' => $maLop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy danh sách môn học có điểm
    public function getDanhSachMonHoc($maLop) {
        try {
            $sql = "SELECT DISTINCT m.maMonHoc, m.tenMon 
                    FROM monhoc m JOIN bangdiem bd ON m.maMonHoc = bd.maMonHoc 
                    JOIN hocsinh hs ON bd.maHS = hs.maHS WHERE hs.maLop = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // Lấy bảng điểm thô
    public function getBangDiemTho($maLop) {
        try {
            $sql = "SELECT bd.* FROM bangdiem bd JOIN hocsinh hs ON bd.maHS = hs.maHS WHERE hs.maLop = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    /**
     * === LOGIC TKB MỚI ===
     * Lấy TKB dựa trên khoảng thời gian hiệu lực (ngayHoc -> ngayKetThuc)
     */
    public function getThoiKhoaBieuByWeek($maLop, $startDate, $endDate) {
        try {
            // Logic: Tìm các dòng TKB mà thời gian hiệu lực BAO TRÙM tuần đang chọn
            // $startDate: Thứ 2 của tuần chọn
            // $endDate: Chủ nhật của tuần chọn
            
            $sql = "SELECT 
                        DAYOFWEEK(tkb.ngayHoc) AS thu_trong_tuan, -- 2=Thứ 2, ..., 7=Thứ 7, 1=CN
                        tkb.tiet,
                        m.tenMon,
                        tkb.maPhong AS tenPhong
                    FROM thoikhoabieu tkb
                    JOIN monhoc m ON tkb.maMonHoc = m.maMonHoc
                    WHERE tkb.maLop = ?
                      AND tkb.ngayHoc <= ?      -- Ngày bắt đầu hiệu lực <= Cuối tuần này
                      AND tkb.ngayKetThuc >= ?  -- Ngày kết thúc hiệu lực >= Đầu tuần này";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $endDate, $startDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }
    
    public function getChiTietHocSinh($maHS) {
        try {
            $sql = "SELECT hs.*, lh.tenLop 
                    FROM hocsinh AS hs 
                    LEFT JOIN lophoc AS lh ON hs.maLop = lh.maLop 
                    WHERE hs.maHS = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            return $stmt->fetch();
        } catch (Exception $e) { return false; }
    }
}
?>
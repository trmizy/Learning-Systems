<?php
// File: models/gvbm/LopModel.php

require_once __DIR__ . '/../../config/database.php';

class LopModel {
    private $db; 

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * === SỬA ĐỔI QUAN TRỌNG ===
     * Tìm kiếm bằng 'maGV' (VD: 'GV_TR001_02')
     */
    public function getThongTinLopChuNhiemByMaGV($maGiaoVien) {
        try {
            $sql = "SELECT 
                        gvcn.lop AS maLop,
                        lh.tenLop,
                        gvbm.hoTen AS tenGiaoVien
                    FROM GiaoVienChuNhiem AS gvcn
                    JOIN LopHoc AS lh ON gvcn.lop = lh.maLop
                    JOIN GiaoVienBoMon AS gvbm ON gvcn.maGV = gvbm.maGV
                    WHERE gvcn.maGV = :ma_gv_param"; // <-- Tìm bằng maGV
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_gv_param' => $maGiaoVien]); // <-- Tìm bằng maGV
            return $stmt->fetch(); 

        } catch (PDOException $e) {
            error_log("Lỗi Model::getThongTinLopChuNhiemByMaGV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hàm này vẫn chính xác, giữ nguyên
     */
    public function getDanhSachHocSinhByLopId($maLop) {
        try {
            $sql = "SELECT 
                        maHS AS hocSinhId, 
                        hoTen,
                        ngaySinh,
                        gioiTinh
                    FROM HocSinh
                    WHERE maLop = :ma_lop_param
                    ORDER BY hoTen ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_lop_param' => $maLop]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Lỗi Model::getDanhSachHocSinhByLopId: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Lấy danh sách các môn học có điểm của lớp
     * @param string $maLop Mã của lớp học
     * @return array Danh sách các môn học (['maMonHoc' => 'TOAN', 'tenMon' => 'Toán Học'])
     */
    public function getDanhSachMonHoc($maLop) {
        try {
            // Lấy các môn học MÀ học sinh trong lớp NÀY có điểm
            $sql = "SELECT DISTINCT m.maMonHoc, m.tenMon
                    FROM MonHoc m
                    JOIN BangDiem bd ON m.maMonHoc = bd.maMonHoc
                    JOIN HocSinh hs ON bd.maHS = hs.maHS
                    WHERE hs.maLop = :maLop";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maLop' => $maLop]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Lỗi Model::getDanhSachMonHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy TẤT CẢ điểm của TẤT CẢ học sinh trong lớp (dạng dữ liệu thô)
     * @param string $maLop Mã của lớp học
     * @return array Danh sách điểm
     */
    public function getBangDiemTho($maLop) {
        try {
            // Lấy TẤT CẢ điểm của học sinh thuộc lớp này
            $sql = "SELECT bd.maHS, bd.maMonHoc, bd.diemThuongXuyen, bd.diemGiuaKy, bd.diemCuoiKy
                    FROM BangDiem bd
                    JOIN HocSinh hs ON bd.maHS = hs.maHS
                    WHERE hs.maLop = :maLop";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maLop' => $maLop]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Lỗi Model::getBangDiemTho: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Lấy TKB của một lớp trong một khoảng thời gian (một tuần)
     * @param string $maLop Mã của lớp học
     * @param string $startDate Ngày bắt đầu (YYYY-MM-DD)
     * @param string $endDate Ngày kết thúc (YYYY-MM-DD)
     * @return array Danh sách các tiết học
     */
    public function getThoiKhoaBieuByWeek($maLop, $startDate, $endDate) {
        try {
            // JOIN 4 bảng: TKB, MonHoc, PhongHoc, HocSinh
            $sql = "SELECT 
                        tkb.tiet,
                        tkb.ngayHoc,
                        mh.tenMon,
                        ph.tenPhong
                    FROM ThoiKhoaBieu AS tkb
                    JOIN MonHoc AS mh ON tkb.maMonHoc = mh.maMonHoc
                    LEFT JOIN PhongHoc AS ph ON tkb.maPhong = ph.maPhong
                    WHERE tkb.maLop = :maLop
                      AND tkb.ngayHoc BETWEEN :startDate AND :endDate";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':maLop' => $maLop,
                ':startDate' => $startDate,
                ':endDate' => $endDate
            ]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Lỗi Model::getThoiKhoaBieuByWeek: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Lấy thông tin chi tiết của MỘT học sinh (bao gồm cả tên lớp)
     * @param string $maHS Mã của học sinh
     * @return array|false
     */
    public function getChiTietHocSinh($maHS) {
        try {
            // Lấy thông tin HS và JOIN với LopHoc để lấy tenLop
            $sql = "SELECT hs.*, lh.tenLop
                    FROM HocSinh AS hs
                    LEFT JOIN LopHoc AS lh ON hs.maLop = lh.maLop
                    WHERE hs.maHS = :maHS";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maHS' => $maHS]);
            return $stmt->fetch(); 

        } catch (PDOException $e) {
            error_log("Lỗi Model::getChiTietHocSinh: " . $e->getMessage());
            return false;
        }
    }
}
?>
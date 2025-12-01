<?php
require_once __DIR__ . '/../../config/database.php';

class DiemModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy danh sách các lớp mà GV này được phân công giảng dạy
    public function getLopGiangDay($maGV) {
        try {
            // JOIN bảng PhanCongGiangDay với LopHoc và MonHoc
            $sql = "SELECT pc.maLop, lh.tenLop, pc.maMonHoc, mh.tenMon
                    FROM phanconggiangday pc
                    JOIN lophoc lh ON pc.maLop = lh.maLop
                    JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                    WHERE pc.maGV = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // Lấy bảng điểm của một lớp cho môn học cụ thể
    public function getBangDiemLop($maLop, $maMonHoc) {
        try {
            $sql = "SELECT 
                        bd.maBangDiem,
                        hs.maHS, hs.hoTen,
                        bd.diemThuongXuyen, bd.diemGiuaKy, bd.diemCuoiKy
                    FROM bangdiem bd
                    JOIN hocsinh hs ON bd.maHS = hs.maHS
                    WHERE bd.maHS IN (SELECT maHS FROM hocsinh WHERE maLop = ?)
                    AND bd.maMonHoc = ?
                    ORDER BY hs.hoTen ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $maMonHoc]);
            return $stmt->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // Lưu yêu cầu sửa điểm
    public function taoYeuCauSuaDiem($maBangDiem, $loaiDiem, $diemCu, $diemMoi, $lyDo, $monHoc) {
        try {
            $maYeuCau = 'YC_' . uniqid();
            $sql = "INSERT INTO yeucausuadiem 
                    (maYeuCau, maBangDiem, loaiDiem, monHoc, diemCu, diemMoi, lyDo, ngayYeuCau, trangThai)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'CHO_DUYET')";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$maYeuCau, $maBangDiem, $loaiDiem, $monHoc, $diemCu, $diemMoi, $lyDo]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    // Kiểm tra xem đã có yêu cầu nào đang chờ duyệt cho ô điểm này chưa
    public function checkYeuCauPending($maBangDiem, $loaiDiem) {
        $sql = "SELECT count(*) FROM yeucausuadiem 
                WHERE maBangDiem = ? AND loaiDiem = ? AND trangThai = 'CHO_DUYET'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maBangDiem, $loaiDiem]);
        return $stmt->fetchColumn() > 0;
    }
    /**
     * Lấy lịch sử các yêu cầu sửa điểm của một lớp + môn cụ thể
     */
    public function getLichSuYeuCau($maLop, $maMonHoc) {
        try {
            $sql = "SELECT 
                        yc.*,
                        hs.hoTen,
                        hs.maHS
                    FROM yeucausuadiem yc
                    JOIN bangdiem bd ON yc.maBangDiem = bd.maBangDiem
                    JOIN hocsinh hs ON bd.maHS = hs.maHS
                    WHERE hs.maLop = ? AND bd.maMonHoc = ?
                    ORDER BY yc.ngayYeuCau DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $maMonHoc]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
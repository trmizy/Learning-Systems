<?php
require_once __DIR__ . '/../../config/database.php';

class DiemModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách Lớp & Môn GIÁO VIÊN ĐANG DẠY.
     * Nguyên tắc: Chỉ lấy dữ liệu từ bảng phanconggiangday.
     * Không quan tâm có phải chủ nhiệm hay không.
     */
    public function getLopGiangDay($maGV) {
        try {
            // Chỉ truy vấn bảng Phân công giảng dạy
            $sql = "SELECT DISTINCT pc.maLop, lh.tenLop, pc.maMonHoc, mh.tenMon
                    FROM phanconggiangday pc
                    JOIN lophoc lh ON pc.maLop = lh.maLop
                    JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                    WHERE pc.maGV = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]); // Chỉ truyền 1 tham số maGV
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { 
            error_log("Lỗi getLopGiangDay: " . $e->getMessage());
            return []; 
        }
    }

    // ... Các hàm khác (getBangDiemLop, taoYeuCauSuaDiem...) giữ nguyên không đổi ...
    
    public function getBangDiemLop($maLop, $maMonHoc) {
        try {
            $sql = "SELECT 
                        bd.maBangDiem,
                        hs.maHS, hs.hoTen,
                        bd.diemThuongXuyen, bd.diemGiuaKy, bd.diemCuoiKy
                    FROM bangdiem bd
                    JOIN hocsinh hs ON bd.maHS = hs.maHS
                    WHERE hs.maLop = ? 
                    AND bd.maMonHoc = ?
                    ORDER BY hs.hoTen ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $maMonHoc]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function taoYeuCauSuaDiem($maBangDiem, $loaiDiem, $diemCu, $diemMoi, $lyDo, $monHoc) {
        try {
            $maYeuCau = 'YC' . time() . rand(10,99); 
            $sql = "INSERT INTO yeucausuadiem 
                    (maYeuCau, maBangDiem, loaiDiem, monHoc, diemCu, diemMoi, lyDo, ngayYeuCau, trangThai)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'CHO_DUYET')";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$maYeuCau, $maBangDiem, $loaiDiem, $monHoc, $diemCu, $diemMoi, $lyDo]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function checkYeuCauPending($maBangDiem, $loaiDiem) {
        $sql = "SELECT count(*) FROM yeucausuadiem 
                WHERE maBangDiem = ? AND loaiDiem = ? AND trangThai = 'CHO_DUYET'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maBangDiem, $loaiDiem]);
        return $stmt->fetchColumn() > 0;
    }

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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getMaGVByUsername($username) {
        try {
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
}
?>
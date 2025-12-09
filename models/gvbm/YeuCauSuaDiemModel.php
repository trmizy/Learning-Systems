<?php
require_once __DIR__ . '/../../config/database.php';

class YeuCauSuaDiemModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy mã giáo viên từ username
     */
    public function getMaGVByUsername($username) {
        try {
            $sql = "SELECT gv.maGV
                    FROM taikhoan tk
                    INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaGVByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ⚠️ FIX TRIỆT ĐỂ: Lấy danh sách LỚP-MÔN theo GV - KIỂM TRA TẤT CẢ
     */
    public function getDanhSachLopVaMonTheoGV($maGV) {
        try {
            // BƯỚC 1: Kiểm tra GV có tồn tại không
            $checkGV = $this->db->prepare("SELECT maGV FROM giaovienbomon WHERE maGV = ?");
            $checkGV->execute([$maGV]);
            if (!$checkGV->fetch()) {
                error_log("❌ GV không tồn tại: $maGV");
                return [];
            }
            
            // BƯỚC 2: Kiểm tra có phân công nào không
            $checkPC = $this->db->prepare("SELECT COUNT(*) as total FROM phanconggiangday WHERE maGV = ?");
            $checkPC->execute([$maGV]);
            $totalPC = $checkPC->fetch()['total'];
            error_log("✅ Tổng số phân công cho GV $maGV: $totalPC");
            
            if ($totalPC == 0) {
                error_log("❌ GV chưa được phân công lớp nào!");
                return [];
            }
            
            // BƯỚC 3: Lấy danh sách lớp-môn
            $sql = "SELECT DISTINCT
                        lh.maLop,
                        lh.tenLop,
                        mh.maMonHoc,
                        mh.tenMon
                    FROM phanconggiangday pc
                    INNER JOIN lophoc lh ON pc.maLop = lh.maLop
                    INNER JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                    WHERE pc.maGV = ?
                      AND pc.namHoc = '2024-2025'
                      AND pc.hocKy = 'HK1'
                    ORDER BY lh.maLop, mh.tenMon";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG CHI TIẾT
            error_log("=== getDanhSachLopVaMonTheoGV ===");
            error_log("maGV: $maGV");
            error_log("Rows trả về: " . count($result));
            if (count($result) > 0) {
                error_log("Bản ghi đầu tiên: " . print_r($result[0], true));
            } else {
                // Kiểm tra ngược: có dữ liệu nhưng điều kiện sai?
                $debugSQL = "SELECT pc.*, lh.tenLop, mh.tenMon 
                            FROM phanconggiangday pc
                            LEFT JOIN lophoc lh ON pc.maLop = lh.maLop
                            LEFT JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                            WHERE pc.maGV = ?
                            LIMIT 5";
                $debugStmt = $this->db->prepare($debugSQL);
                $debugStmt->execute([$maGV]);
                $debugResult = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("🔍 DEBUG - Phân công thô cho GV:");
                error_log(print_r($debugResult, true));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachLopVaMonTheoGV: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy bảng điểm theo lớp và môn
     */
    public function getBangDiemTheoLopVaMon($maLop, $maMonHoc) {
        try {
            $sql = "SELECT 
                        bd.maBangDiem,
                        bd.maHS,
                        hs.hoTen,
                        bd.diemThuongXuyen,
                        bd.diemGiuaKy,
                        bd.diemCuoiKy,
                        bd.namHoc,
                        bd.hocKy
                    FROM bangdiem bd
                    INNER JOIN hocsinh hs ON bd.maHS = hs.maHS
                    WHERE hs.maLop = ?
                      AND bd.maMonHoc = ?
                      AND bd.namHoc = '2024-2025'
                      AND bd.hocKy = 'HK1'
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $maMonHoc]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getBangDiemTheoLopVaMon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy lịch sử yêu cầu sửa điểm
     */
    public function getLichSuYeuCauTheoGV($maGV, $maLop, $maMonHoc) {
        try {
            $sql = "SELECT 
                        yc.*,
                        hs.hoTen
                    FROM yeucausuadiem yc
                    INNER JOIN bangdiem bd ON yc.maBangDiem = bd.maBangDiem
                    INNER JOIN hocsinh hs ON bd.maHS = hs.maHS
                    WHERE yc.maGV = ?
                      AND hs.maLop = ?
                      AND bd.maMonHoc = ?
                    ORDER BY yc.ngayYeuCau DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV, $maLop, $maMonHoc]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getLichSuYeuCauTheoGV: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tạo yêu cầu sửa điểm
     */
    public function taoYeuCauSuaDiem($data) {
        try {
            $sql = "INSERT INTO yeucausuadiem 
                    (maBangDiem, loaiDiem, diemCu, diemMoi, lyDo, maGV, trangThai, ngayYeuCau)
                    VALUES (?, ?, ?, ?, ?, ?, 'CHO_DUYET', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['maBangDiem'],
                $data['loaiDiem'],
                $data['diemCu'],
                $data['diemMoi'],
                $data['lyDo'],
                $data['maGV']
            ]);
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error taoYeuCauSuaDiem: " . $e->getMessage());
            return false;
        }
    }
}

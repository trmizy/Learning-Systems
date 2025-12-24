<?php
require_once __DIR__ . '/../../config/database.php';

class HanhKiemHocLucModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách hạnh kiểm theo học sinh
     */
    public function getHanhKiemHocSinh($maHS) {
        try {
            $sql = "SELECT 
                        maHanhKiem,
                        hocKy,
                        namHoc,
                        COALESCE(loaiHanhKiem, 'Chưa đánh giá') as loaiHanhKiem,
                        COALESCE(soBuoiNghiCoPhep, 0) as soBuoiNghiCoPhep,
                        COALESCE(soBuoiNghiKhongCoPhep, 0) as soBuoiNghiKhongCoPhep,
                        COALESCE(soLanViPham, 0) as soLanViPham
                    FROM hanhkiem
                    WHERE maHS = ?
                    ORDER BY namHoc DESC, 
                             CASE hocKy 
                                 WHEN 'HK2' THEN 2 
                                 WHEN 'HK1' THEN 1 
                                 ELSE 0 
                             END DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("=== getHanhKiemHocSinh ===");
            error_log("maHS: $maHS | Rows: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getHanhKiemHocSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách học lực theo học sinh
     */
    public function getHocLucHocSinh($maHS) {
        try {
            $sql = "SELECT 
                        hl.maHocLuc,
                        COALESCE(hl.diemTrungBinh, 0) as diemTrungBinh,
                        COALESCE(hl.hanhKiem, 'Chưa đánh giá') as hanhKiem,
                        COALESCE(hl.xepLoaiHocLuc, 'Chưa xếp loại') as xepLoaiHocLuc,
                        hl.nhanXet,
                        'HK1' as hocKy,
                        '2024-2025' as namHoc
                    FROM hocluc hl
                    WHERE hl.maHS = ?
                    ORDER BY hl.maHocLuc DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("=== getHocLucHocSinh ===");
            error_log("maHS: $maHS | Rows: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getHocLucHocSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin học sinh
     */
    public function getThongTinHocSinh($maHS) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        lh.tenLop,
                        lh.namHoc
                    FROM hocsinh hs
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE hs.maHS = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy mã học sinh từ username
     */
    public function getMaHocSinhByUsername($username) {
        try {
            $sql = "SELECT hs.maHS
                    FROM taikhoan tk
                    INNER JOIN hocsinh hs ON tk.maTaiKhoan = hs.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['maHS'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaHocSinhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách con của phụ huynh
     */
    public function getDanhSachConCuaPhuHuynh($maPhuHuynh) {
        try {
            $sql = "SELECT 
                        hs.maHS as maHocSinh,
                        hs.hoTen,
                        lh.tenLop
                    FROM phuhuynh_hocsinh ph_hs
                    INNER JOIN hocsinh hs ON ph_hs.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE ph_hs.maPH = ?
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPhuHuynh]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachConCuaPhuHuynh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy mã phụ huynh từ username
     */
    public function getMaPhuHuynhByUsername($username) {
        try {
            $sql = "SELECT ph.maPH
                    FROM taikhoan tk
                    INNER JOIN phuhuynh ph ON tk.maTaiKhoan = ph.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['maPH'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaPhuHuynhByUsername: " . $e->getMessage());
            return null;
        }
    }
}

<?php
require_once __DIR__ . '/../../config/database.php';

class ThoiKhoaBieuModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy thời khóa biểu của học sinh theo tuần
     */
    public function getThoiKhoaBieuHocSinh($maHocSinh, $startDate, $endDate) {
        try {
            $sql = "SELECT 
                        tkb.ngayHoc,
                        tkb.tietHoc,
                        mh.tenMon,
                        ph.tenPhong,
                        gv.hoTen AS tenGiaoVien,
                        DAYOFWEEK(tkb.ngayHoc) AS thuTrongTuan
                    FROM ThoiKhoaBieu tkb
                    INNER JOIN MonHoc mh ON tkb.maMonHoc = mh.maMonHoc
                    INNER JOIN PhongHoc ph ON tkb.maPhong = ph.maPhong
                    INNER JOIN GiaoVienBoMon gv ON tkb.maGV = gv.maGV
                    INNER JOIN LopHoc lh ON tkb.maLop = lh.maLop
                    INNER JOIN HocSinh hs ON lh.maLop = hs.maLop
                    WHERE hs.maHS = ?
                        AND tkb.ngayHoc BETWEEN ? AND ?
                    ORDER BY tkb.ngayHoc, tkb.tietHoc";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHocSinh, $startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThoiKhoaBieuHocSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thời khóa biểu theo MÃ LỚP - KHỚP SCHEMA MỚI
     */
    public function getThoiKhoaBieuTheoLop($maLop, $startDate, $endDate) {
        try {
            $sql = "SELECT 
                        tkb.tiet as tietHoc,
                        tkb.ngayHoc,
                        mh.tenMon,
                        ph.tenPhong,
                        DAYOFWEEK(tkb.ngayHoc) AS thuTrongTuan
                    FROM thoikhoabieu AS tkb
                    INNER JOIN monhoc AS mh ON tkb.maMonHoc = mh.maMonHoc
                    LEFT JOIN phonghoc AS ph ON tkb.maPhong = ph.maPhong
                    WHERE tkb.maLop = ?
                      AND tkb.ngayHoc BETWEEN ? AND ?
                    ORDER BY tkb.ngayHoc, tkb.tiet";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $startDate, $endDate]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG LOG
            error_log("=== getThoiKhoaBieuTheoLop ===");
            error_log("maLop: $maLop | startDate: $startDate | endDate: $endDate");
            error_log("Rows: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getThoiKhoaBieuTheoLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin lớp của học sinh
     */
    public function getThongTinLopHocSinh($maHocSinh) {
        try {
            $sql = "SELECT 
                        lh.maLop,
                        lh.tenLop,
                        lh.namHoc,
                        gv.hoTen AS tenGVCN
                    FROM hocsinh hs
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    LEFT JOIN giaovienchunhiem gvcn ON lh.maLop = gvcn.lop
                    LEFT JOIN giaovienbomon gv ON gvcn.maGV = gv.maGV
                    WHERE hs.maHS = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHocSinh]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinLopHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy mã học sinh từ username
     */
    public function getMaHocSinhByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT hs.maHS
                FROM taikhoan tk
                INNER JOIN hocsinh hs ON tk.maTaiKhoan = hs.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maHS'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaHocSinhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách con của phụ huynh - SỬA THEO BẢNG phuhuynh_hocsinh
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
            $stmt = $this->db->prepare("
                SELECT ph.maPH
                FROM taikhoan tk
                INNER JOIN phuhuynh ph ON tk.maTaiKhoan = ph.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maPH'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaPhuHuynhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy mã lớp từ mã học sinh
     */
    public function getMaLopByMaHocSinh($maHocSinh) {
        try {
            $stmt = $this->db->prepare("
                SELECT maLop 
                FROM hocsinh 
                WHERE maHS = ?
                LIMIT 1
            ");
            $stmt->execute([$maHocSinh]);
            $result = $stmt->fetch();
            
            return $result ? $result['maLop'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaLopByMaHocSinh: " . $e->getMessage());
            return null;
        }
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

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
     * Lấy thời khóa biểu theo MÃ LỚP - ĐÚNG SCHEMA
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
                    JOIN monhoc AS mh ON tkb.maMonHoc = mh.maMonHoc
                    LEFT JOIN phonghoc AS ph ON tkb.maPhong = ph.maPhong
                    WHERE tkb.maLop = ?
                      AND tkb.ngayHoc BETWEEN ? AND ?
                    ORDER BY tkb.ngayHoc, tkb.tiet";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThoiKhoaBieuTheoLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin lớp - ĐÚNG SCHEMA
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
                SELECT maTaiKhoan 
                FROM taikhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $taiKhoan = $stmt->fetch();
            
            if (!$taiKhoan) {
                error_log("getMaHocSinhByUsername: Không tìm thấy TaiKhoan cho username: " . $username);
                return null;
            }
            
            $stmt2 = $this->db->prepare("
                SELECT maHS 
                FROM hocsinh 
                WHERE maTaiKhoan = ?
                LIMIT 1
            ");
            $stmt2->execute([$taiKhoan['maTaiKhoan']]);
            $hs = $stmt2->fetch();
            
            if (!$hs) {
                error_log("getMaHocSinhByUsername: Không tìm thấy HocSinh cho maTaiKhoan: " . $taiKhoan['maTaiKhoan']);
            }
            
            return $hs ? $hs['maHS'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaHocSinhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách con của phụ huynh - SỬA QUAN HỆ NHIỀU-NHIỀU
     */
    public function getDanhSachConCuaPhuHuynh($maPhuHuynh) {
        try {
            // SỬA: Dùng bảng phuhuynh_hocsinh để JOIN
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
     * Lấy mã phụ huynh từ username - SỬA TABLE NAME
     */
    public function getMaPhuHuynhByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT maTaiKhoan 
                FROM taikhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $taiKhoan = $stmt->fetch();
            
            if (!$taiKhoan) {
                return null;
            }
            
            // SỬA: Bảng phuhuynh KHÔNG CÓ maTaiKhoan
            // Phải tìm qua bảng trung gian hoặc email
            $stmt2 = $this->db->prepare("
                SELECT maPH
                FROM phuhuynh 
                WHERE email = (SELECT email FROM taikhoan WHERE maTaiKhoan = ?)
                LIMIT 1
            ");
            $stmt2->execute([$taiKhoan['maTaiKhoan']]);
            $ph = $stmt2->fetch();
            
            return $ph ? $ph['maPH'] : null;
            
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

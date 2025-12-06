<?php
/**
 * Model: DiemModel
 * Quản lý xem điểm học sinh
 * Path: models/DiemModel.php
 */

require_once __DIR__ . '/../config/database.php';

class DiemModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách năm học có trong hệ thống
     * Sử dụng VIEW v_diem_hocsinh
     * @return PDOStatement
     */
    public function getAllNamHoc() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT namHoc 
            FROM v_diem_hocsinh
            WHERE namHoc IS NOT NULL 
            ORDER BY namHoc DESC
        ");
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy điểm của 1 học sinh theo năm học và học kỳ
     * Sử dụng VIEW v_diem_hocsinh (chỉ lấy môn có điểm)
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học (VD: 2024-2025)
     * @param string $hocKy - Học kỳ (HK1, HK2, Cả năm)
     * @return PDOStatement
     */
    public function getDiemHocSinh($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                maHS,
                maMonHoc,
                tenMon as tenMonHoc,
                diemThuongXuyen,
                diemGiuaKy,
                diemCuoiKy,
                diemTrungBinhMon as diemTrungBinh,
                namHoc,
                hocKy
            FROM v_diem_hocsinh
            WHERE maHS = ?
              AND namHoc = ?
              AND hocKy = ?
              AND diemTrungBinhMon IS NOT NULL
            ORDER BY tenMon
        ");
        $stmt->execute([$maHS, $namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Lấy tất cả môn học trong năm học (để hiển thị cả môn chưa có điểm)
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return PDOStatement
     */
    public function getAllMonHoc($namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                maMonHoc,
                tenMon,
                soTietTuan,
                loaiMonHoc
            FROM monhoc
            WHERE namHoc = ?
              AND (hocKy = ? OR hocKy = 'Cả năm')
            ORDER BY tenMon
        ");
        $stmt->execute([$namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Lấy điểm học sinh với đầy đủ môn học (bao gồm môn chưa nhập điểm)
     * Sử dụng VIEW v_diem_hocsinh
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return PDOStatement
     */
    public function getDiemHocSinhDayDu($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                maMonHoc,
                tenMon as tenMonHoc,
                diemThuongXuyen,
                diemGiuaKy,
                diemCuoiKy,
                diemTrungBinhMon as diemTrungBinh
            FROM v_diem_hocsinh
            WHERE maHS = ?
              AND namHoc = ?
              AND hocKy = ?
            ORDER BY tenMon
        ");
        $stmt->execute([$maHS, $namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Lấy thông tin học sinh theo mã
     * @param string $maHS - Mã học sinh
     * @return array|false
     */
    public function getThongTinHocSinh($maHS) {
        // Lấy thông tin học sinh kèm lớp học từ bảng hocsinh (có cột maLop)
        $stmt = $this->db->prepare("
            SELECT 
                hs.maHS,
                hs.hoTen,
                hs.ngaySinh,
                hs.email,
                hs.gioiTinh,
                hs.trangThai,
                hs.maLop,
                l.tenLop,
                l.khoi,
                l.namHoc,
                gvcn.maGV as maGVCN,
                gv.hoTen as tenGVCN,
                gv.email as emailGVCN,
                gv.soDienThoai as sdtGVCN
            FROM hocsinh hs
            LEFT JOIN lophoc l ON l.maLop = hs.maLop
            LEFT JOIN giaovienchunhiem gvcn ON gvcn.lop = l.maLop
            LEFT JOIN giaovienbomon gv ON gv.maGV = gvcn.maGV
            WHERE hs.maHS = ?
        ");
        $stmt->execute([$maHS]);
        $thongTin = $stmt->fetch();
        
        return $thongTin ? $thongTin : false;
    }

    /**
     * Kiểm tra phụ huynh có quyền xem điểm của học sinh không
     * @param string $maPH - Mã phụ huynh
     * @param string $maHS - Mã học sinh
     * @return bool
     */
    public function kiemTraQuyen($maPH, $maHS) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM phuhuynh_hocsinh
            WHERE maPH = ? AND maHS = ?
        ");
        $stmt->execute([$maPH, $maHS]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Tính điểm trung bình chung của học sinh
     * Sử dụng VIEW v_diem_hocsinh
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return float|null
     */
    public function getDiemTrungBinhChung($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                AVG(diemTrungBinhMon) as diemTBC
            FROM v_diem_hocsinh
            WHERE maHS = ?
              AND namHoc = ?
              AND hocKy = ?
              AND diemTrungBinhMon IS NOT NULL
        ");
        $stmt->execute([$maHS, $namHoc, $hocKy]);
        $result = $stmt->fetch();
        return $result['diemTBC'] ? round($result['diemTBC'], 2) : null;
    }

    /**
     * Lấy mã học sinh từ username trong TaiKhoan
     * @param string $username - Tên đăng nhập
     * @return string|null
     */
    public function getMaHocSinhByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT hs.maHS
            FROM hocsinh hs
            JOIN taikhoan tk ON tk.maTaiKhoan = hs.maTaiKhoan
            WHERE tk.tenDangNhap = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ? $result['maHS'] : null;
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
     * Lấy danh sách con của phụ huynh - TRẢ VỀ ARRAY
     */
    public function getDanhSachConCuaPhuHuynh($maPH) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        lh.tenLop,
                        lh.namHoc
                    FROM phuhuynh_hocsinh ph_hs
                    INNER JOIN hocsinh hs ON ph_hs.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE ph_hs.maPH = ?
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPH]);
            
            // TRẢ VỀ ARRAY, KHÔNG PHẢI STATEMENT
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachConCuaPhuHuynh: " . $e->getMessage());
            return []; // Trả về array rỗng khi lỗi
        }
    }

    /**
     * Lấy thông tin phụ huynh theo mã PH
     */
    public function getThongTinPhuHuynhByMaPH($maPH) {
        try {
            $sql = "SELECT 
                        maPH,
                        hoTen,
                        email,
                        soDienThoai,
                        diaChi,
                        moiQuanHe,
                        gioiTinh
                    FROM phuhuynh
                    WHERE maPH = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPH]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinPhuHuynhByMaPH: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy bảng điểm của học sinh theo học kỳ
     */
    public function getBangDiemHocSinh($maHS, $hocKy, $namHoc) {
        try {
            $sql = "SELECT 
                        maMonHoc,
                        tenMon,
                        diemThuongXuyen,
                        diemGiuaKy,
                        diemCuoiKy,
                        diemTrungBinhMon
                    FROM v_diem_hocsinh
                    WHERE maHS = ? 
                      AND hocKy = ? 
                      AND namHoc = ?
                    ORDER BY tenMon";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS, $hocKy, $namHoc]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getBangDiemHocSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy bảng điểm của con theo phụ huynh - SỬ DỤNG VIEW v_diem_phuhuynh
     */
    public function getBangDiemConCuaPhuHuynh($maPH, $maHS, $hocKy, $namHoc) {
        try {
            $sql = "SELECT 
                        maMonHoc,
                        tenMon,
                        diemThuongXuyen,
                        diemGiuaKy,
                        diemCuoiKy,
                        diemTrungBinhMon
                    FROM v_diem_phuhuynh
                    WHERE maPH = ?
                      AND maHS = ?
                      AND hocKy = ?
                      AND namHoc = ?
                    ORDER BY tenMon";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPH, $maHS, $hocKy, $namHoc]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getBangDiemConCuaPhuHuynh: " . $e->getMessage());
            return [];
        }
    }
}

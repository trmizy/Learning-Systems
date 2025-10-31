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
     * @return PDOStatement
     */
    public function getAllNamHoc() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT namHoc 
            FROM BangDiem 
            WHERE namHoc IS NOT NULL 
            ORDER BY namHoc DESC
        ");
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy điểm của 1 học sinh theo năm học và học kỳ
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học (VD: 2024-2025)
     * @param string $hocKy - Học kỳ (HK1, HK2, Cả năm)
     * @return PDOStatement
     */
    public function getDiemHocSinh($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                bd.maBangDiem,
                bd.maHS,
                bd.maMonHoc,
                mh.tenMon,
                bd.diemThuongXuyen,
                bd.diemGiuaKy,
                bd.diemCuoiKy,
                bd.namHoc,
                bd.hocKy,
                bd.maGV,
                gv.hoTen as tenGiaoVien,
                -- Tính điểm trung bình môn (công thức có thể điều chỉnh)
                CASE 
                    WHEN bd.diemThuongXuyen IS NOT NULL 
                         AND bd.diemGiuaKy IS NOT NULL 
                         AND bd.diemCuoiKy IS NOT NULL 
                    THEN ROUND((bd.diemThuongXuyen + bd.diemGiuaKy + bd.diemCuoiKy * 2) / 4, 2)
                    ELSE NULL
                END as diemTrungBinh
            FROM BangDiem bd
            JOIN MonHoc mh ON mh.maMonHoc = bd.maMonHoc
            LEFT JOIN GiaoVienBoMon gv ON gv.maGV = bd.maGV
            WHERE bd.maHS = ?
              AND bd.namHoc = ?
              AND bd.hocKy = ?
            ORDER BY mh.tenMon
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
            FROM MonHoc
            WHERE namHoc = ?
              AND (hocKy = ? OR hocKy = 'Cả năm')
            ORDER BY tenMon
        ");
        $stmt->execute([$namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Lấy điểm học sinh với đầy đủ môn học (bao gồm môn chưa nhập điểm)
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return PDOStatement
     */
    public function getDiemHocSinhDayDu($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                mh.maMonHoc,
                mh.tenMon,
                mh.soTietTuan,
                mh.loaiMonHoc,
                bd.maBangDiem,
                bd.diemThuongXuyen,
                bd.diemGiuaKy,
                bd.diemCuoiKy,
                bd.maGV,
                gv.hoTen as tenGiaoVien,
                -- Tính điểm trung bình
                CASE 
                    WHEN bd.diemThuongXuyen IS NOT NULL 
                         AND bd.diemGiuaKy IS NOT NULL 
                         AND bd.diemCuoiKy IS NOT NULL 
                    THEN ROUND((bd.diemThuongXuyen + bd.diemGiuaKy + bd.diemCuoiKy * 2) / 4, 2)
                    ELSE NULL
                END as diemTrungBinh
            FROM MonHoc mh
            LEFT JOIN BangDiem bd ON bd.maMonHoc = mh.maMonHoc 
                AND bd.maHS = ?
                AND bd.namHoc = ?
                AND bd.hocKy = ?
            LEFT JOIN GiaoVienBoMon gv ON gv.maGV = bd.maGV
            WHERE mh.namHoc = ?
              AND (mh.hocKy = ? OR mh.hocKy = 'Cả năm')
            ORDER BY mh.tenMon
        ");
        $stmt->execute([$maHS, $namHoc, $hocKy, $namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Lấy thông tin học sinh theo mã
     * @param string $maHS - Mã học sinh
     * @return array|false
     */
    public function getThongTinHocSinh($maHS) {
        // Thử lấy thông tin cơ bản trước
        $stmt = $this->db->prepare("
            SELECT 
                hs.maHS,
                hs.hoTen,
                hs.ngaySinh,
                hs.email,
                hs.gioiTinh,
                hs.trangThai
            FROM HocSinh hs
            WHERE hs.maHS = ?
        ");
        $stmt->execute([$maHS]);
        $thongTin = $stmt->fetch();
        
        if (!$thongTin) {
            return false;
        }
        
        // Thử lấy thông tin lớp nếu có bảng HocSinh_Lop
        try {
            $stmtLop = $this->db->prepare("
                SELECT 
                    l.maLop,
                    l.tenLop,
                    l.khoi,
                    gvcn.maGV as maGVCN,
                    gv.hoTen as tenGVCN
                FROM HocSinh_Lop hs_lop
                JOIN LopHoc l ON l.maLop = hs_lop.maLop
                LEFT JOIN GiaoVienChuNhiem gvcn ON gvcn.lop = l.maLop
                LEFT JOIN GiaoVienBoMon gv ON gv.maGV = gvcn.maGV
                WHERE hs_lop.maHS = ?
                ORDER BY hs_lop.namHoc DESC
                LIMIT 1
            ");
            $stmtLop->execute([$maHS]);
            $lopInfo = $stmtLop->fetch();
            
            if ($lopInfo) {
                $thongTin = array_merge($thongTin, $lopInfo);
            }
        } catch (PDOException $e) {
            // Bảng HocSinh_Lop chưa tồn tại, bỏ qua
            $thongTin['maLop'] = null;
            $thongTin['tenLop'] = null;
            $thongTin['khoi'] = null;
        }
        
        return $thongTin;
    }

    /**
     * Lấy danh sách con của phụ huynh
     * @param string $maPH - Mã phụ huynh
     * @return PDOStatement
     */
    public function getDanhSachConCuaPhuHuynh($maPH) {
        // Lấy danh sách con cơ bản (không cần bảng HocSinh_Lop)
        $stmt = $this->db->prepare("
            SELECT 
                hs.maHS,
                hs.hoTen,
                hs.ngaySinh,
                hs.gioiTinh,
                '' as maLop,
                '' as tenLop,
                '' as khoi
            FROM PhuHuynh_HocSinh ph_hs
            JOIN HocSinh hs ON hs.maHS = ph_hs.maHS
            WHERE ph_hs.maPH = ?
            ORDER BY hs.hoTen
        ");
        $stmt->execute([$maPH]);
        return $stmt;
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
            FROM PhuHuynh_HocSinh
            WHERE maPH = ? AND maHS = ?
        ");
        $stmt->execute([$maPH, $maHS]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Tính điểm trung bình chung của học sinh
     * @param string $maHS - Mã học sinh
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return float|null
     */
    public function getDiemTrungBinhChung($maHS, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                AVG(
                    CASE 
                        WHEN diemThuongXuyen IS NOT NULL 
                             AND diemGiuaKy IS NOT NULL 
                             AND diemCuoiKy IS NOT NULL 
                        THEN (diemThuongXuyen + diemGiuaKy + diemCuoiKy * 2) / 4
                        ELSE NULL
                    END
                ) as diemTBC
            FROM BangDiem
            WHERE maHS = ?
              AND namHoc = ?
              AND hocKy = ?
              AND diemThuongXuyen IS NOT NULL 
              AND diemGiuaKy IS NOT NULL 
              AND diemCuoiKy IS NOT NULL
        ");
        $stmt->execute([$maHS, $namHoc, $hocKy]);
        $result = $stmt->fetch();
        return $result['diemTBC'] ? round($result['diemTBC'], 2) : null;
    }

    /**
     * Lấy thông tin phụ huynh theo mã phụ huynh
     * @param string $maPH - Mã phụ huynh
     * @return array|false
     */
    public function getThongTinPhuHuynhByMaPH($maPH) {
        $stmt = $this->db->prepare("
            SELECT 
                ph.maPH,
                ph.hoTen,
                ph.email,
                ph.soDienThoai,
                ph.gioiTinh
            FROM PhuHuynh ph
            WHERE ph.maPH = ?
        ");
        $stmt->execute([$maPH]);
        return $stmt->fetch();
    }

    /**
     * Lấy mã học sinh từ username trong TaiKhoan
     * @param string $username - Tên đăng nhập
     * @return string|null
     */
    public function getMaHocSinhByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT hs.maHS
            FROM HocSinh hs
            JOIN TaiKhoan tk ON tk.maTaiKhoan = hs.maTaiKhoan
            WHERE tk.tenDangNhap = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ? $result['maHS'] : null;
    }

    /**
     * Lấy mã phụ huynh từ username trong TaiKhoan
     * @param string $username - Tên đăng nhập
     * @return string|null
     */
    public function getMaPhuHuynhByUsername($username) {
        // Lưu ý: Bảng PhuHuynh không có cột maTaiKhoan theo schema hiện tại
        // Cần tạo liên kết hoặc dùng email để tìm
        $stmt = $this->db->prepare("
            SELECT ph.maPH
            FROM PhuHuynh ph
            JOIN TaiKhoan tk ON tk.email = ph.email
            WHERE tk.tenDangNhap = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ? $result['maPH'] : null;
    }
}

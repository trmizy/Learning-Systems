<?php
/**
 * Model: PhanCongModel
 * Quản lý phân công giảng dạy, GVCN và phòng học
 * Path: models/bgh/PhanCongModel.php
 */

require_once __DIR__ . '/../../config/database.php';

class PhanCongModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách tất cả lớp học
     * @param string|null $namHoc - Lọc theo năm học
     * @return PDOStatement
     */
    public function getAllLopHoc($namHoc = null) {
        $sql = "
            SELECT 
                l.maLop,
                l.tenLop,
                l.siSo,
                l.khoi,
                l.namHoc,
                gvcn.maGV as maGVCN,
                gv.hoTen as tenGVCN,
                pc.maPhong,
                p.tenPhong
            FROM LopHoc l
            LEFT JOIN GiaoVienChuNhiem gvcn ON gvcn.lop = l.maLop COLLATE utf8mb4_unicode_ci
            LEFT JOIN GiaoVienBoMon gv ON gv.maGV = gvcn.maGV COLLATE utf8mb4_unicode_ci
            LEFT JOIN PhanCongPhongHoc pc ON pc.maLop COLLATE utf8mb4_unicode_ci = l.maLop COLLATE utf8mb4_unicode_ci 
                AND pc.namHoc COLLATE utf8mb4_unicode_ci = l.namHoc COLLATE utf8mb4_unicode_ci
            LEFT JOIN PhongHoc p ON p.maPhong COLLATE utf8mb4_unicode_ci = pc.maPhong COLLATE utf8mb4_unicode_ci
        ";
        
        if ($namHoc) {
            $sql .= " WHERE l.namHoc = ?";
            $stmt = $this->db->prepare($sql . " ORDER BY l.khoi, l.tenLop");
            $stmt->execute([$namHoc]);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY l.khoi, l.tenLop");
            $stmt->execute();
        }
        
        return $stmt;
    }

    /**
     * Lấy danh sách các năm học có trong hệ thống
     * @return PDOStatement
     */
    public function getAllNamHoc() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT namHoc 
            FROM LopHoc 
            WHERE namHoc IS NOT NULL 
            ORDER BY namHoc DESC
        ");
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy danh sách giáo viên chưa làm GVCN (hoặc đã làm GVCN lớp hiện tại)
     * @param string|null $maLopHienTai - Mã lớp đang xét (để cho phép giữ nguyên GVCN cũ)
     * @return PDOStatement
     */
    public function getGiaoVienChuaChuNhiem($maLopHienTai = null) {
        if ($maLopHienTai) {
            // Lấy GV chưa làm GVCN HOẶC đang làm GVCN lớp hiện tại
            $stmt = $this->db->prepare("
                SELECT 
                    gv.maGV,
                    gv.hoTen,
                    gv.monHocPhuTrach,
                    gvcn.lop as lopDangChuNhiem
                FROM GiaoVienBoMon gv
                LEFT JOIN GiaoVienChuNhiem gvcn ON gvcn.maGV = gv.maGV
                WHERE gv.tinhTrangTaiKhoan = 'ACTIVE'
                  AND (gvcn.maGV IS NULL OR gvcn.lop = ?)
                ORDER BY gv.hoTen
            ");
            $stmt->execute([$maLopHienTai]);
        } else {
            // Lấy tất cả GV chưa làm GVCN
            $stmt = $this->db->prepare("
                SELECT 
                    gv.maGV,
                    gv.hoTen,
                    gv.monHocPhuTrach
                FROM GiaoVienBoMon gv
                LEFT JOIN GiaoVienChuNhiem gvcn ON gvcn.maGV = gv.maGV
                WHERE gv.tinhTrangTaiKhoan = 'ACTIVE'
                  AND gvcn.maGV IS NULL
                ORDER BY gv.hoTen
            ");
            $stmt->execute();
        }
        return $stmt;
    }

    /**
     * Lấy danh sách phòng học khả dụng cho năm học
     * @param string $namHoc - Năm học cần kiểm tra
     * @param string|null $maLopHienTai - Mã lớp đang xét (để cho phép giữ phòng cũ)
     * @return PDOStatement
     */
    public function getPhongHocChuaGan($maLopHienTai = null, $namHoc = null) {
        if (!$namHoc) {
            // Lấy năm học từ lớp hiện tại
            if ($maLopHienTai) {
                $stmtYear = $this->db->prepare("SELECT namHoc FROM LopHoc WHERE maLop = ?");
                $stmtYear->execute([$maLopHienTai]);
                $result = $stmtYear->fetch();
                $namHoc = $result['namHoc'] ?? '2024-2025';
            } else {
                $namHoc = '2024-2025'; // Mặc định
            }
        }
        
        // Lấy phòng chưa được gán trong năm học này (hoặc đang gán cho lớp hiện tại)
        if ($maLopHienTai) {
            $stmt = $this->db->prepare("
                SELECT 
                    p.maPhong,
                    p.tenPhong,
                    p.sucChua,
                    pc.maLop as dangGiaoChoMaLop
                FROM PhongHoc p
                LEFT JOIN PhanCongPhongHoc pc 
                    ON p.maPhong = pc.maPhong
                    AND pc.namHoc = ?
                WHERE (p.trangThai IN ('ACTIVE', 'DANG_SU_DUNG') OR p.trangThai IS NULL)
                  AND (pc.maPhong IS NULL OR pc.maLop = ?)
                ORDER BY p.tenPhong
            ");
            $stmt->execute([$namHoc, $maLopHienTai]);
        } else {
            $stmt = $this->db->prepare("
                SELECT 
                    p.maPhong,
                    p.tenPhong,
                    p.sucChua
                FROM PhongHoc p
                LEFT JOIN PhanCongPhongHoc pc 
                    ON p.maPhong = pc.maPhong
                    AND pc.namHoc = ?
                WHERE (p.trangThai IN ('ACTIVE', 'DANG_SU_DUNG') OR p.trangThai IS NULL)
                  AND pc.maPhong IS NULL
                ORDER BY p.tenPhong
            ");
            $stmt->execute([$namHoc]);
        }
        return $stmt;
    }

    /**
     * Kiểm tra GV đã làm GVCN lớp nào chưa
     * @param string $maGV
     * @return array|false - Trả về thông tin lớp nếu GV đã làm GVCN, false nếu chưa
     */
    public function kiemTraGVCN($maGV) {
        $stmt = $this->db->prepare("
            SELECT gvcn.lop, l.tenLop
            FROM GiaoVienChuNhiem gvcn
            JOIN LopHoc l ON l.maLop = gvcn.lop
            WHERE gvcn.maGV = ?
        ");
        $stmt->execute([$maGV]);
        return $stmt->fetch();
    }

    /**
     * Kiểm tra phòng đã gán cho lớp nào trong năm học cụ thể
     * @param string $maPhong
     * @param string $namHoc
     * @return array|false
     */
    public function kiemTraPhongHoc($maPhong, $namHoc = '2024-2025') {
        $stmt = $this->db->prepare("
            SELECT pc.maLop, l.tenLop
            FROM PhanCongPhongHoc pc
            JOIN LopHoc l ON l.maLop COLLATE utf8mb4_unicode_ci = pc.maLop COLLATE utf8mb4_unicode_ci
            WHERE pc.maPhong = ? AND pc.namHoc = ?
        ");
        $stmt->execute([$maPhong, $namHoc]);
        return $stmt->fetch();
    }

    /**
     * Gán GVCN cho lớp
     * @param string $maLop
     * @param string $maGV
     * @return bool
     */
    public function ganGVCN($maLop, $maGV) {
        try {
            // Xóa GVCN cũ nếu có
            $stmtDelete = $this->db->prepare("DELETE FROM GiaoVienChuNhiem WHERE lop = ?");
            $stmtDelete->execute([$maLop]);

            // Thêm GVCN mới
            $stmtInsert = $this->db->prepare("INSERT INTO GiaoVienChuNhiem (maGV, lop) VALUES (?, ?)");
            return $stmtInsert->execute([$maGV, $maLop]);
        } catch (PDOException $e) {
            error_log("Lỗi gán GVCN: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gán phòng học cho lớp theo năm học
     * @param string $maLop
     * @param string $maPhong
     * @return bool
     */
    public function ganPhongHoc($maLop, $maPhong) {
        try {
            // Lấy năm học của lớp
            $stmtYear = $this->db->prepare("SELECT namHoc FROM LopHoc WHERE maLop = ?");
            $stmtYear->execute([$maLop]);
            $result = $stmtYear->fetch();
            $namHoc = $result['namHoc'] ?? '2024-2025';
            
            // Xóa phân công phòng cũ của lớp trong năm này (nếu có)
            $stmtDelete = $this->db->prepare("
                DELETE FROM PhanCongPhongHoc 
                WHERE maLop = ? AND namHoc = ?
            ");
            $stmtDelete->execute([$maLop, $namHoc]);

            // Tạo mã phân công
            $maPhanCong = $maPhong . '_' . $maLop . '_' . str_replace('-', '', $namHoc);
            
            // Thêm phân công mới
            $stmtInsert = $this->db->prepare("
                INSERT INTO PhanCongPhongHoc (maPhanCong, maPhong, maLop, namHoc, ngayPhanCong)
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmtInsert->execute([$maPhanCong, $maPhong, $maLop, $namHoc]);
        } catch (PDOException $e) {
            error_log("Lỗi gán phòng học: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa phân công GVCN của lớp
     * @param string $maLop
     * @return bool
     */
    public function xoaGVCN($maLop) {
        try {
            $stmt = $this->db->prepare("DELETE FROM GiaoVienChuNhiem WHERE lop = ?");
            return $stmt->execute([$maLop]);
        } catch (PDOException $e) {
            error_log("Lỗi xóa GVCN: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa phân công phòng học của lớp
     * @param string $maLop
     * @return bool
     */
    public function xoaPhongHoc($maLop) {
        try {
            // Lấy năm học của lớp
            $stmtYear = $this->db->prepare("SELECT namHoc FROM LopHoc WHERE maLop = ?");
            $stmtYear->execute([$maLop]);
            $result = $stmtYear->fetch();
            $namHoc = $result['namHoc'] ?? '2024-2025';
            
            // Xóa phân công trong bảng PhanCongPhongHoc
            $stmt = $this->db->prepare("
                DELETE FROM PhanCongPhongHoc 
                WHERE maLop = ? AND namHoc = ?
            ");
            return $stmt->execute([$maLop, $namHoc]);
        } catch (PDOException $e) {
            error_log("Lỗi xóa phòng học: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin chi tiết 1 lớp
     * @param string $maLop
     * @return array|false
     */
    public function getThongTinLop($maLop) {
        $stmt = $this->db->prepare("
            SELECT 
                l.maLop,
                l.tenLop,
                l.siSo,
                l.khoi,
                l.namHoc,
                gvcn.maGV as maGVCN,
                gv.hoTen as tenGVCN,
                gv.monHocPhuTrach,
                p.maPhong,
                p.tenPhong,
                p.sucChua
            FROM LopHoc l
            LEFT JOIN GiaoVienChuNhiem gvcn ON gvcn.lop = l.maLop
            LEFT JOIN GiaoVienBoMon gv ON gv.maGV = gvcn.maGV
            LEFT JOIN PhongHoc p ON p.dangGiaoChoMaLop = l.maLop
            WHERE l.maLop = ?
        ");
        $stmt->execute([$maLop]);
        return $stmt->fetch();
    }

    // ========== PHÂN CÔNG GIẢNG DẠY (GV BỘ MÔN) ==========

    /**
     * Lấy danh sách môn học
     * @param string|null $namHoc
     * @return PDOStatement
     */
    public function getAllMonHoc($namHoc = null) {
        if ($namHoc) {
            $stmt = $this->db->prepare("
                SELECT maMonHoc, tenMon, soTietTuan, loaiMonHoc, hocKy, namHoc
                FROM MonHoc
                WHERE namHoc = ?
                ORDER BY tenMon
            ");
            $stmt->execute([$namHoc]);
        } else {
            $stmt = $this->db->prepare("
                SELECT maMonHoc, tenMon, soTietTuan, loaiMonHoc, hocKy, namHoc
                FROM MonHoc
                ORDER BY tenMon
            ");
            $stmt->execute();
        }
        return $stmt;
    }

    /**
     * Lấy danh sách GV theo môn học (chỉ GV phụ trách môn đó)
     * @param string $monHoc - Tên môn học (ví dụ: 'Toán', 'Văn', 'Giáo dục công dân')
     * @return PDOStatement
     */
    public function getGiaoVienTheoMon($monHoc) {
        // Map tên môn đầy đủ về tên viết tắt để tìm kiếm
        $monHocMap = [
            'Giáo dục công dân' => 'GDCD',
            'Ngữ văn' => 'Văn',
            'Tiếng Anh' => 'Anh',
            'Vật lý' => 'Lý',
            'Hóa học' => 'Hóa',
            'Sinh học' => 'Sinh',
            'Lịch sử' => 'Sử',
            'Địa lý' => 'Địa',
            'Thể dục' => 'TD',
            'Quốc phòng' => 'QP',
            'Tin học' => 'Tin',
        ];
        
        // Nếu có trong map thì dùng tên viết tắt, không thì dùng tên gốc
        $searchKeyword = $monHocMap[$monHoc] ?? $monHoc;
        
        // CHỈ lấy GV phụ trách đúng môn đó
        $stmt = $this->db->prepare("
            SELECT 
                maGV,
                hoTen,
                monHocPhuTrach,
                email,
                soDienThoai
            FROM GiaoVienBoMon
            WHERE tinhTrangTaiKhoan = 'ACTIVE'
              AND monHocPhuTrach LIKE ?
            ORDER BY hoTen
        ");
        $stmt->execute(["%$searchKeyword%"]);
        return $stmt;
    }

    /**
     * Lấy danh sách phân công giảng dạy của 1 lớp
     * @param string $maLop
     * @param string|null $namHoc
     * @param string|null $hocKy
     * @return PDOStatement
     */
    public function getPhanCongGiangDay($maLop, $namHoc = null, $hocKy = null) {
        $sql = "
            SELECT 
                pc.maPhanCong,
                pc.maLop,
                pc.maMonHoc,
                pc.maGV,
                pc.namHoc,
                pc.hocKy,
                pc.ghiChu,
                pc.ngayPhanCong,
                mh.tenMon,
                mh.soTietTuan,
                gv.hoTen as tenGV,
                gv.email as emailGV,
                gv.soDienThoai as sdtGV
            FROM PhanCongGiangDay pc
            JOIN MonHoc mh ON mh.maMonHoc = pc.maMonHoc
            JOIN GiaoVienBoMon gv ON gv.maGV = pc.maGV
            WHERE pc.maLop = ?
        ";
        
        $params = [$maLop];
        
        if ($namHoc) {
            $sql .= " AND pc.namHoc = ?";
            $params[] = $namHoc;
        }
        
        if ($hocKy) {
            $sql .= " AND pc.hocKy = ?";
            $params[] = $hocKy;
        }
        
        $sql .= " ORDER BY mh.tenMon";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Kiểm tra GV đã được phân công dạy môn này ở lớp này chưa
     * @param string $maLop
     * @param string $maMonHoc
     * @param string $namHoc
     * @param string $hocKy
     * @return array|false
     */
    public function kiemTraPhanCongTonTai($maLop, $maMonHoc, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT pc.*, gv.hoTen as tenGV
            FROM PhanCongGiangDay pc
            JOIN GiaoVienBoMon gv ON gv.maGV = pc.maGV
            WHERE pc.maLop = ? AND pc.maMonHoc = ? AND pc.namHoc = ? AND pc.hocKy = ?
        ");
        $stmt->execute([$maLop, $maMonHoc, $namHoc, $hocKy]);
        return $stmt->fetch();
    }

    /**
     * Thêm phân công giảng dạy
     * @param string $maLop
     * @param string $maMonHoc
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @param string|null $ghiChu
     * @return bool
     */
    public function themPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy, $ghiChu = null) {
        try {
            $maPhanCong = 'PC_' . $maLop . '_' . $maMonHoc . '_' . time();
            
            $stmt = $this->db->prepare("
                INSERT INTO PhanCongGiangDay 
                (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy, ghiChu)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$maPhanCong, $maLop, $maMonHoc, $maGV, $namHoc, $hocKy, $ghiChu]);
        } catch (PDOException $e) {
            error_log("Lỗi thêm phân công giảng dạy: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật phân công giảng dạy (đổi GV)
     * @param string $maLop
     * @param string $maMonHoc
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return bool
     */
    public function capNhatPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE PhanCongGiangDay
                SET maGV = ?
                WHERE maLop = ? AND maMonHoc = ? AND namHoc = ? AND hocKy = ?
            ");
            return $stmt->execute([$maGV, $maLop, $maMonHoc, $namHoc, $hocKy]);
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật phân công giảng dạy: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa phân công giảng dạy
     * @param string $maPhanCong
     * @return bool
     */
    public function xoaPhanCongGiangDay($maPhanCong) {
        try {
            $stmt = $this->db->prepare("DELETE FROM PhanCongGiangDay WHERE maPhanCong = ?");
            return $stmt->execute([$maPhanCong]);
        } catch (PDOException $e) {
            error_log("Lỗi xóa phân công giảng dạy: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm số môn GV đang dạy
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return int
     */
    public function demSoMonGVDang($maGV, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT maMonHoc) as soMon
            FROM PhanCongGiangDay
            WHERE maGV = ? AND namHoc = ? AND hocKy = ?
        ");
        $stmt->execute([$maGV, $namHoc, $hocKy]);
        $result = $stmt->fetch();
        return (int)($result['soMon'] ?? 0);
    }

    /**
     * Đếm số lớp GV đang dạy
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return int
     */
    public function demSoLopGVDang($maGV, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT maLop) as soLop
            FROM PhanCongGiangDay
            WHERE maGV = ? AND namHoc = ? AND hocKy = ?
        ");
        $stmt->execute([$maGV, $namHoc, $hocKy]);
        $result = $stmt->fetch();
        return (int)($result['soLop'] ?? 0);
    }

    /**
     * Kiểm tra GV có vượt giới hạn số lớp không
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @param int $maxClasses - Số lớp tối đa (mặc định từ config)
     * @return array ['success' => bool, 'currentCount' => int, 'message' => string]
     */
    public function kiemTraGioiHanLop($maGV, $namHoc, $hocKy, $maxClasses = null) {
        if ($maxClasses === null) {
            require_once __DIR__ . '/../../config/config.php';
            $maxClasses = defined('MAX_CLASSES_PER_TEACHER') ? MAX_CLASSES_PER_TEACHER : 5;
        }
        
        $currentCount = $this->demSoLopGVDang($maGV, $namHoc, $hocKy);
        
        if ($currentCount >= $maxClasses) {
            return [
                'success' => false,
                'currentCount' => $currentCount,
                'maxClasses' => $maxClasses,
                'message' => "Giáo viên này đã dạy {$currentCount} lớp (đạt giới hạn {$maxClasses} lớp)"
            ];
        }
        
        return [
            'success' => true,
            'currentCount' => $currentCount,
            'maxClasses' => $maxClasses,
            'message' => "Còn có thể phân công (đang dạy {$currentCount}/{$maxClasses} lớp)"
        ];
    }

    /**
     * Lấy thông tin chi tiết phân công của giáo viên
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return array
     */
    public function getThongTinPhanCongGV($maGV, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT 
                pc.maLop,
                l.tenLop,
                l.khoi,
                pc.maMonHoc,
                mh.tenMon,
                mh.soTietTuan
            FROM PhanCongGiangDay pc
            JOIN LopHoc l ON l.maLop = pc.maLop
            JOIN MonHoc mh ON mh.maMonHoc = pc.maMonHoc
            WHERE pc.maGV = ? AND pc.namHoc = ? AND pc.hocKy = ?
            ORDER BY l.khoi, l.tenLop, mh.tenMon
        ");
        $stmt->execute([$maGV, $namHoc, $hocKy]);
        
        $danhSach = [];
        while ($row = $stmt->fetch()) {
            $danhSach[] = $row;
        }
        
        return $danhSach;
    }
}

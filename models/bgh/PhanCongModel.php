<?php
/**
 * Model: PhanCongModel
 * Quản lý phân công giảng dạy, GVCN và phòng học
 * Path: models/PhanCongModel.php
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
            FROM lophoc l
            LEFT JOIN giaovienchunhiem gvcn ON gvcn.lop = l.maLop COLLATE utf8mb4_unicode_ci
            LEFT JOIN giaovienbomon gv ON gv.maGV = gvcn.maGV COLLATE utf8mb4_unicode_ci
            LEFT JOIN phancongphonghoc pc ON pc.maLop COLLATE utf8mb4_unicode_ci = l.maLop COLLATE utf8mb4_unicode_ci 
                AND pc.namHoc COLLATE utf8mb4_unicode_ci = l.namHoc COLLATE utf8mb4_unicode_ci
            LEFT JOIN phonghoc p ON p.maPhong COLLATE utf8mb4_unicode_ci = pc.maPhong COLLATE utf8mb4_unicode_ci
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
            FROM lophoc 
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
                FROM giaovienbomon gv
                LEFT JOIN giaovienchunhiem gvcn ON gvcn.maGV = gv.maGV
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
                FROM giaovienbomon gv
                LEFT JOIN giaovienchunhiem gvcn ON gvcn.maGV = gv.maGV
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
            $namHoc = '2024-2025'; // Mặc định
        }
        
        // Lấy TẤT CẢ phòng học kèm thông tin lớp đã gán (để JS xử lý ẩn/hiện)
        $stmt = $this->db->prepare("
            SELECT 
                p.maPhong,
                p.tenPhong,
                p.sucChua,
                pc.maLop as lopDangGan
            FROM phonghoc p
            LEFT JOIN phancongphonghoc pc 
                ON p.maPhong = pc.maPhong
                AND pc.namHoc = ?
            ORDER BY p.tenPhong
        ");
        $stmt->execute([$namHoc]);
        
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
            FROM giaovienchunhiem gvcn
            JOIN lophoc l ON l.maLop = gvcn.lop
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
            FROM phancongphonghoc pc
            JOIN lophoc l ON l.maLop COLLATE utf8mb4_unicode_ci = pc.maLop COLLATE utf8mb4_unicode_ci
            WHERE pc.maPhong = ? AND pc.namHoc = ?
        ");
        $stmt->execute([$maPhong, $namHoc]);
        return $stmt->fetch();
    }

    /**
     * Gán GVCN cho lớp
     * Tự động phân công môn học cho GVCN (môn mà GV phụ trách)
     * @param string $maLop
     * @param string $maGV
     * @return bool
     */
    public function ganGVCN($maLop, $maGV) {
        try {
            $this->db->beginTransaction();
            
            // Xóa GVCN cũ nếu có
            $stmtDelete = $this->db->prepare("DELETE FROM giaovienchunhiem WHERE lop = ?");
            $stmtDelete->execute([$maLop]);

            // Thêm GVCN mới
            $stmtInsert = $this->db->prepare("INSERT INTO giaovienchunhiem (maGV, lop) VALUES (?, ?)");
            $stmtInsert->execute([$maGV, $maLop]);
            
            // Tự động phân công môn học cho GVCN
            // Lấy thông tin GV và lớp
            $stmtGV = $this->db->prepare("SELECT monHocPhuTrach FROM giaovienbomon WHERE maGV = ?");
            $stmtGV->execute([$maGV]);
            $gvInfo = $stmtGV->fetch();
            
            $stmtLop = $this->db->prepare("SELECT namHoc FROM lophoc WHERE maLop = ?");
            $stmtLop->execute([$maLop]);
            $lopInfo = $stmtLop->fetch();
            
            if ($gvInfo && $lopInfo && !empty($gvInfo['monHocPhuTrach'])) {
                $monPhuTrach = $gvInfo['monHocPhuTrach'];
                $namHoc = $lopInfo['namHoc'];
                
                // Tìm môn học tương ứng trong bảng monhoc
                // Ưu tiên: 1) Match mã môn, 2) Match tên chính xác, 3) Match tên bắt đầu/kết thúc
                $stmtMon = $this->db->prepare("
                    SELECT maMonHoc, tenMon FROM monhoc 
                    WHERE namHoc = ? 
                    AND (
                        maMonHoc = ?
                        OR maMonHoc LIKE ?
                        OR tenMon = ? 
                        OR tenMon LIKE CONCAT(?, ' %')
                        OR tenMon LIKE CONCAT('% ', ?)
                    )
                    ORDER BY 
                        CASE 
                            WHEN maMonHoc = ? THEN 1
                            WHEN maMonHoc LIKE ? THEN 2
                            WHEN tenMon = ? THEN 3
                            WHEN tenMon LIKE CONCAT(?, ' %') THEN 4
                            WHEN tenMon LIKE CONCAT('% ', ?) THEN 5
                            ELSE 6
                        END,
                        LENGTH(tenMon) ASC
                    LIMIT 1
                ");
                $stmtMon->execute([
                    $namHoc,
                    $monPhuTrach,           // Match mã chính xác: 'GDCD' = 'GDCD'
                    "%{$monPhuTrach}%",     // Match mã có chứa: 'GDCD' trong mã
                    $monPhuTrach,           // Match tên chính xác: 'Toán' = 'Toán'
                    $monPhuTrach,           // Match tên bắt đầu: 'Địa' → 'Địa lý'
                    $monPhuTrach,           // Match tên kết thúc: 'Lý' → 'Vật lý'
                    $monPhuTrach,           // ORDER: priority 1 (mã chính xác)
                    "%{$monPhuTrach}%",     // ORDER: priority 2 (mã chứa)
                    $monPhuTrach,           // ORDER: priority 3 (tên chính xác)
                    $monPhuTrach,           // ORDER: priority 4 (tên bắt đầu)
                    $monPhuTrach            // ORDER: priority 5 (tên kết thúc)
                ]);
                $monHoc = $stmtMon->fetch();
                
                if ($monHoc) {
                    // Xóa phân công cũ cho môn này (nếu có)
                    $stmtDelPC = $this->db->prepare("
                        DELETE FROM phanconggiangday 
                        WHERE maLop = ? AND maMonHoc = ? AND namHoc = ?
                    ");
                    $stmtDelPC->execute([$maLop, $monHoc['maMonHoc'], $namHoc]);
                    
                    // Thêm phân công mới cho cả 2 học kỳ
                    foreach (['1', '2'] as $hocKy) {
                        $maPhanCong = 'PC_' . $maLop . '_' . $monHoc['maMonHoc'] . '_HK' . $hocKy . '_' . time();
                        $stmtAddPC = $this->db->prepare("
                            INSERT INTO phanconggiangday (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmtAddPC->execute([$maPhanCong, $maLop, $monHoc['maMonHoc'], $maGV, $namHoc, $hocKy]);
                    }
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
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
            $stmtYear = $this->db->prepare("SELECT namHoc FROM lophoc WHERE maLop = ?");
            $stmtYear->execute([$maLop]);
            $result = $stmtYear->fetch();
            $namHoc = $result['namHoc'] ?? '2024-2025';
            
            // Xóa phân công phòng cũ của lớp trong năm này (nếu có)
            $stmtDelete = $this->db->prepare("
                DELETE FROM phancongphonghoc 
                WHERE maLop = ? AND namHoc = ?
            ");
            $stmtDelete->execute([$maLop, $namHoc]);

            // Tạo mã phân công
            $maPhanCong = $maPhong . '_' . $maLop . '_' . str_replace('-', '', $namHoc);
            
            // Thêm phân công mới
            $stmtInsert = $this->db->prepare("
                INSERT INTO phancongphonghoc (maPhanCong, maPhong, maLop, namHoc, ngayPhanCong)
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
            $stmt = $this->db->prepare("DELETE FROM giaovienchunhiem WHERE lop = ?");
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
            $stmtYear = $this->db->prepare("SELECT namHoc FROM lophoc WHERE maLop = ?");
            $stmtYear->execute([$maLop]);
            $result = $stmtYear->fetch();
            $namHoc = $result['namHoc'] ?? '2024-2025';
            
            // Xóa phân công trong bảng PhanCongPhongHoc
            $stmt = $this->db->prepare("
                DELETE FROM phancongphonghoc 
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
            FROM lophoc l
            LEFT JOIN giaovienchunhiem gvcn ON gvcn.lop = l.maLop
            LEFT JOIN giaovienbomon gv ON gv.maGV = gvcn.maGV
            LEFT JOIN phancongphonghoc pc ON pc.maLop = l.maLop AND pc.namHoc = l.namHoc
            LEFT JOIN phonghoc p ON p.maPhong = pc.maPhong
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
                FROM monhoc
                WHERE namHoc = ?
                ORDER BY tenMon
            ");
            $stmt->execute([$namHoc]);
        } else {
            $stmt = $this->db->prepare("
                SELECT maMonHoc, tenMon, soTietTuan, loaiMonHoc, hocKy, namHoc
                FROM monhoc
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
    // SỬA LẠI: Map tên hiển thị (Tiếng Việt) sang MÃ MÔN trong Database (In hoa, không dấu)
    $monHocMap = [
        // Nhóm tự nhiên
        'Toán' => 'TOAN',
        'Toan' => 'TOAN',
        'Vật lý' => 'LY',
        'Vat ly' => 'LY',
        'Lý' => 'LY',
        'Hóa học' => 'HOA',
        'Hoa hoc' => 'HOA',
        'Hóa' => 'HOA',
        'Sinh học' => 'SINH',
        'Sinh hoc' => 'SINH',
        'Sinh' => 'SINH',
        
        // Nhóm xã hội
        'Ngữ văn' => 'NGUVAN',
        'Ngu van' => 'NGUVAN',
        'Văn' => 'NGUVAN',
        'Lịch sử' => 'SU',
        'Lich su' => 'SU',
        'Sử' => 'SU',
        'Địa lý' => 'DIA',
        'Dia ly' => 'DIA',
        'Địa' => 'DIA',
        'Giáo dục công dân' => 'GDCD',
        'GDCD' => 'GDCD',
        
        // Nhóm ngoại ngữ & khác
        'Tiếng Anh' => 'ANH',
        'Tieng Anh' => 'ANH',
        'Anh' => 'ANH',
        'Tin học' => 'TIN',
        'Tin hoc' => 'TIN',
        'Tin' => 'TIN',
        'Công nghệ' => 'CN',
        'Cong nghe' => 'CN',
        'Thể dục' => 'TD',
        'The duc' => 'TD',
        'Quốc phòng' => 'QPAN', // Lưu ý: DB của bạn là QPAN chứ không phải QP
        'GDQP' => 'QPAN',
        'GDQP-AN' => 'QPAN'
    ];
    
    // Nếu tìm thấy trong map thì lấy mã, không thì lấy chính nó (và viết hoa lên cho chắc)
    $searchKeyword = $monHocMap[$monHoc] ?? strtoupper($monHoc);
    
    // Query tìm kiếm
    $stmt = $this->db->prepare("
        SELECT 
            maGV,
            hoTen,
            monHocPhuTrach,
            email,
            soDienThoai
        FROM giaovienbomon
        WHERE tinhTrangTaiKhoan = 'ACTIVE'
          AND monHocPhuTrach LIKE ?
        ORDER BY hoTen
    ");
    
    // Thêm % để tìm kiếm tương đối (Dù map đúng rồi nhưng giữ % vẫn an toàn)
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
            FROM phanconggiangday pc
            JOIN monhoc mh ON mh.maMonHoc = pc.maMonHoc
            JOIN giaovienbomon gv ON gv.maGV = pc.maGV
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
            FROM phanconggiangday pc
            JOIN giaovienbomon gv ON gv.maGV = pc.maGV
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
            // Ràng buộc 1: Kiểm tra GV bộ môn chỉ được phụ trách tối đa 5 lớp
            $stmtCheck = $this->db->prepare("
                SELECT COUNT(DISTINCT maLop) as soLop
                FROM phanconggiangday
                WHERE maGV = ? AND namHoc = ? AND hocKy = ?
            ");
            $stmtCheck->execute([$maGV, $namHoc, $hocKy]);
            $result = $stmtCheck->fetch();
            
            if ($result['soLop'] >= 5) {
                throw new Exception('Giáo viên này đã phụ trách đủ 5 lớp. Không thể phân công thêm.');
            }
            
            // Ràng buộc 2: Kiểm tra nếu GV là GVCN của lớp này thì tự động được phân môn của mình
            $stmtGVCN = $this->db->prepare("
                SELECT gv.maGV, gv.monHocPhuTrach
                FROM giaovienchunhiem gvcn
                JOIN giaovienbomon gv ON gv.maGV = gvcn.maGV
                WHERE gvcn.lop = ?
            ");
            $stmtGVCN->execute([$maLop]);
            $gvcnInfo = $stmtGVCN->fetch();
            
            if ($gvcnInfo) {
                // Lấy thông tin môn học để so sánh
                $stmtMon = $this->db->prepare("SELECT tenMon FROM monhoc WHERE maMonHoc = ?");
                $stmtMon->execute([$maMonHoc]);
                $monInfo = $stmtMon->fetch();
                
                // Nếu là GVCN và môn học trùng với môn phụ trách
                if ($gvcnInfo['maGV'] === $maGV && $monInfo) {
                    // Kiểm tra xem môn này có khớp với môn phụ trách không
                    $monPhuTrach = $gvcnInfo['monHocPhuTrach'];
                    $tenMon = $monInfo['tenMon'];
                    
                    // Map để so sánh (có thể mở rộng)
                    $isMatchingSubject = (
                        strpos($monPhuTrach, $tenMon) !== false || 
                        strpos($tenMon, $monPhuTrach) !== false
                    );
                    
                    if (!$isMatchingSubject) {
                        throw new Exception("GVCN phải dạy môn {$monPhuTrach} cho lớp mình chủ nhiệm.");
                    }
                }
            }
            
            
            foreach (['1', '2'] as $hocKy) {
                        $maPhanCong = 'PC_' . $maLop . '_' . $maMonHoc . '_HK' . $hocKy . '_' . time();
                        $stmtAddPC = $this->db->prepare("
                            INSERT INTO phanconggiangday (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmtAddPC->execute([$maPhanCong, $maLop, $maMonHoc, $maGV, $namHoc, $hocKy]);
                    }
            $stmt = $this->db->prepare("
                INSERT INTO phanconggiangday 
                (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy, ghiChu)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$maPhanCong, $maLop, $maMonHoc, $maGV, $namHoc, $hocKy, $ghiChu]);
        } catch (Exception $e) {
            // Ném lại exception để controller bắt được
            throw $e;
        } catch (PDOException $e) {
            error_log("Lỗi thêm phân công giảng dạy: " . $e->getMessage());
            throw new Exception("Lỗi hệ thống khi thêm phân công");
        }
    }

    /**
     * Cập nhật phân công giảng dạy - FIX: INSERT nếu chưa tồn tại
     */
    public function capNhatPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy) {
        try {
            // Ràng buộc 1: Kiểm tra GV bộ môn chỉ được phụ trách tối đa 5 lớp
            $stmtCheck = $this->db->prepare("
                SELECT COUNT(DISTINCT maLop) as soLop
                FROM phanconggiangday
                WHERE maGV = ? AND namHoc = ? AND hocKy = ?
                  AND NOT (maLop = ? AND maMonHoc = ?)
            ");
            $stmtCheck->execute([$maGV, $namHoc, $hocKy, $maLop, $maMonHoc]);
            $result = $stmtCheck->fetch();
            
            if ($result['soLop'] >= 5) {
                throw new Exception('Giáo viên này đã phụ trách đủ 5 lớp. Không thể phân công thêm.');
            }
            
            // Ràng buộc 2: Kiểm tra GVCN phải dạy môn của mình
            $stmtGVCN = $this->db->prepare("
                SELECT gv.maGV, gv.monHocPhuTrach
                FROM giaovienchunhiem gvcn
                JOIN giaovienbomon gv ON gv.maGV = gvcn.maGV
                WHERE gvcn.lop = ?
            ");
            $stmtGVCN->execute([$maLop]);
            $gvcnInfo = $stmtGVCN->fetch();
            
            if ($gvcnInfo && $gvcnInfo['maGV'] === $maGV) {
                $stmtMon = $this->db->prepare("SELECT tenMon FROM monhoc WHERE maMonHoc = ?");
                $stmtMon->execute([$maMonHoc]);
                $monInfo = $stmtMon->fetch();
                
                if ($monInfo) {
                    $monPhuTrach = $gvcnInfo['monHocPhuTrach'];
                    $tenMon = $monInfo['tenMon'];
                    
                    $isMatchingSubject = (
                        strpos($monPhuTrach, $tenMon) !== false || 
                        strpos($tenMon, $monPhuTrach) !== false
                    );
                    
                    if (!$isMatchingSubject) {
                        throw new Exception("GVCN phải dạy môn {$monPhuTrach} cho lớp mình chủ nhiệm.");
                    }
                }
            }
            
            // ⚠️ FIX QUAN TRỌNG: Kiểm tra phân công đã tồn tại chưa
            $stmtExist = $this->db->prepare("
                SELECT maPhanCong 
                FROM phanconggiangday
                WHERE maLop = ? AND maMonHoc = ? AND namHoc = ? AND hocKy = ?
            ");
            $stmtExist->execute([$maLop, $maMonHoc, $namHoc, $hocKy]);
            $existing = $stmtExist->fetch();
            
            if ($existing) {
                // ✅ CÓ RỒI → UPDATE
                $stmt = $this->db->prepare("
                    UPDATE phanconggiangday
                    SET maGV = ?
                    WHERE maLop = ? AND maMonHoc = ? AND namHoc = ? AND hocKy = ?
                ");
                return $stmt->execute([$maGV, $maLop, $maMonHoc, $namHoc, $hocKy]);
            } else {
                // ✅ CHƯA CÓ → INSERT (giống logic ganGVCN)
                        foreach (['1', '2'] as $hocKy) {
                        $maPhanCong = 'PC_' . $maLop . '_' . $maMonHoc . '_HK' . $hocKy . '_' . time();
                        $stmtAddPC = $this->db->prepare("
                            INSERT INTO phanconggiangday (maPhanCong, maLop, maMonHoc, maGV, namHoc, hocKy)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmtAddPC->execute([$maPhanCong, $maLop, $maMonHoc, $maGV, $namHoc, $hocKy]);
                    }
            }
            
        } catch (Exception $e) {
            throw $e;
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật phân công giảng dạy: " . $e->getMessage());
            throw new Exception("Lỗi hệ thống khi cập nhật phân công");
        }
    }

    /**
     * Xóa phân công giảng dạy
     * @param string $maPhanCong
     * @return bool
     */
    public function xoaPhanCongGiangDay($maPhanCong) {
        try {
            $stmt = $this->db->prepare("DELETE FROM phanconggiangday WHERE maPhanCong = ?");
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
            FROM phanconggiangday
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
    /**
     * Đếm số lớp GV đang phụ trách (trả về PDOStatement để dùng ở controller)
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return PDOStatement
     */
    public function countClassesByTeacher($maGV, $namHoc, $hocKy) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT maLop) as soLop
            FROM phanconggiangday
            WHERE maGV = ? AND namHoc = ? AND hocKy = ?
        ");
        $stmt->execute([$maGV, $namHoc, $hocKy]);
        return $stmt;
    }

    /**
     * Đếm số lớp GV đang phụ trách (trả về số nguyên)
     * @param string $maGV
     * @param string $namHoc
     * @param string $hocKy
     * @return int
     */
    public function demSoLopGVDang($maGV, $namHoc, $hocKy) {
        $stmt = $this->countClassesByTeacher($maGV, $namHoc, $hocKy);
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
            FROM phanconggiangday pc
            JOIN lophoc l ON l.maLop = pc.maLop
            JOIN monhoc mh ON mh.maMonHoc = pc.maMonHoc
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

    /**
     * Phân công GVCN mới - FIX: Sửa lỗi cú pháp
     */
public function phanCongGVCN($maGV, $maLop) {
    try {
        $this->db->beginTransaction();

        // BƯỚC 1: Kiểm tra xem giáo viên đã là GVCN của lớp khác chưa
        $stmtCheck = $this->db->prepare("SELECT lop FROM giaovienchunhiem WHERE maGV = ?");
        $stmtCheck->execute([$maGV]);
        $existingClass = $stmtCheck->fetchColumn();

        if ($existingClass) {
            throw new Exception("Giáo viên đã là chủ nhiệm của lớp $existingClass");
        }

        // BƯỚC 2: Kiểm tra lớp đã có GVCN chưa
        // BƯỚC 2: Kiểm tra lớp đã có GVCN chưa
        $stmtCheckClass = $this->db->prepare("SELECT maGV FROM giaovienchunhiem WHERE lop = ?");
        $stmtCheckClass->execute([$maLop]);
        $existingGV = $stmtCheckClass->fetchColumn();

        if ($existingGV) {
            // Xóa GVCN cũ khỏi lớp này
            $stmtDelete = $this->db->prepare("DELETE FROM giaovienchunhiem WHERE lop = ?");
            $stmtDelete->execute([$maLop]);
            
            // LOGIC QUAN TRỌNG: Kiểm tra xem GV cũ còn chủ nhiệm lớp nào KHÁC không?
            $stmtCheckOldGV = $this->db->prepare("SELECT COUNT(*) FROM giaovienchunhiem WHERE maGV = ?");
            $stmtCheckOldGV->execute([$existingGV]);
            $countLopOldGV = $stmtCheckOldGV->fetchColumn();

            // Chỉ hạ quyền về 'gvbm' nếu họ KHÔNG còn chủ nhiệm lớp nào nữa
            if ($countLopOldGV == 0) {
                $stmtGetOldTK = $this->db->prepare("SELECT maTaiKhoan FROM giaovienbomon WHERE maGV = ?");
                $stmtGetOldTK->execute([$existingGV]);
                $oldMaTK = $stmtGetOldTK->fetchColumn();
                
                if ($oldMaTK) {
                    // Update đúng bảng taikhoan_vaitro
                    $stmtDowngradeOld = $this->db->prepare("UPDATE taikhoan_vaitro SET maVaiTro = 'gvbm' WHERE maTaiKhoan = ?");
                    $stmtDowngradeOld->execute([$oldMaTK]);
                }
            }
        }

        // BƯỚC 3: Thêm GVCN mới vào bảng giaovienchunhiem
        $stmtInsert = $this->db->prepare("INSERT INTO giaovienchunhiem (maGV, lop) VALUES (?, ?)");
        $stmtInsert->execute([$maGV, $maLop]);

// BƯỚC 4: Cập nhật role trong bảng taikhoan_vaitro
        // Lấy mã tài khoản từ bảng giáo viên
        $stmtGetMaTK = $this->db->prepare("SELECT maTaiKhoan FROM giaovienbomon WHERE maGV = ?");
        $stmtGetMaTK->execute([$maGV]);
        $maTaiKhoan = $stmtGetMaTK->fetchColumn();

        // [DEBUG] In ra để xem PHP thực sự lấy được gì
        error_log("DEBUG FIX: Mã GV [$maGV] có Mã TK là: [" . ($maTaiKhoan ?? 'NULL') . "]");

        if ($maTaiKhoan) {
            // SỬA LỖI QUAN TRỌNG: Dùng TRIM() để bỏ qua lỗi khoảng trắng (nếu có)
            // Và kiểm tra luôn: Nếu chưa có thì INSERT, có rồi thì UPDATE
            
            // 1. Kiểm tra chính xác xem tài khoản này đã nằm trong bảng phân quyền chưa
            $stmtCheck = $this->db->prepare("SELECT count(*) FROM taikhoan_vaitro WHERE TRIM(maTaiKhoan) = TRIM(?)");
            $stmtCheck->execute([$maTaiKhoan]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                // Có rồi -> UPDATE
                error_log("DEBUG FIX: Tìm thấy tài khoản -> Đang chạy lệnh UPDATE...");
                $stmtUpdate = $this->db->prepare("UPDATE taikhoan_vaitro SET maVaiTro = 'gvcn' WHERE TRIM(maTaiKhoan) = TRIM(?)");
                $stmtUpdate->execute([$maTaiKhoan]);
                error_log("DEBUG FIX: Đã UPDATE thành công " . $stmtUpdate->rowCount() . " dòng.");
            } else {
                // Chưa có -> INSERT
                error_log("DEBUG FIX: Không tìm thấy trong bảng role -> Đang chạy lệnh INSERT...");
                $stmtInsertRole = $this->db->prepare("INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) VALUES (TRIM(?), 'gvcn')");
                $stmtInsertRole->execute([$maTaiKhoan]);
            }
        } else {
            error_log("ERROR: Không tìm thấy maTaiKhoan cho giáo viên này. Kiểm tra lại bảng giaovienbomon.");
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("ERROR phanCongGVCN: " . $e->getMessage());
        error_log("ERROR Stack trace: " . $e->getTraceAsString());
        throw $e;
    }
}

/**
 * Hủy phân công GVCN và chuyển về gvbm
 */
public function huyPhanCongGVCN($maGV, $maLop) {
    try {
        $this->db->beginTransaction();

        // 1. Xóa khỏi bảng giaovienchunhiem
        $stmtDelete = $this->db->prepare("DELETE FROM giaovienchunhiem WHERE maGV = ? AND lop = ?");
        $stmtDelete->execute([$maGV, $maLop]);

        // 2. Kiểm tra xem GV còn là GVCN của lớp nào khác không
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM giaovienchunhiem WHERE maGV = ?");
        $stmtCheck->execute([$maGV]);
        $stillGVCN = $stmtCheck->fetchColumn();

        // 3. Nếu KHÔNG còn là GVCN nữa -> Chuyển về gvbm trong bảng TAIKHOAN_VAITRO
        if ($stillGVCN == 0) {
            $stmtGetMaTK = $this->db->prepare("SELECT maTaiKhoan FROM giaovienbomon WHERE maGV = ?");
            $stmtGetMaTK->execute([$maGV]);
            $maTaiKhoan = $stmtGetMaTK->fetchColumn();

            if ($maTaiKhoan) {
                // SỬA LẠI DÒNG NÀY: Update đúng bảng taikhoan_vaitro
                $stmtUpdateRole = $this->db->prepare("UPDATE taikhoan_vaitro SET maVaiTro = 'gvbm' WHERE maTaiKhoan = ?");
                $stmtUpdateRole->execute([$maTaiKhoan]);
                
                // Nếu hệ thống cũ còn dùng cột role trong bảng taikhoan, update luôn cho chắc (nếu không dùng thì bỏ dòng dưới)
                // $stmtSync = $this->db->prepare("UPDATE taikhoan SET role = 'gvbm' WHERE maTaiKhoan = ?");
                // $stmtSync->execute([$maTaiKhoan]);
            }
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("ERROR huyPhanCongGVCN: " . $e->getMessage());
        return false;
    }
}

    /**
     * Lấy danh sách GVCN hiện tại
     */
    public function getDanhSachGVCN() {
        try {
            $sql = "SELECT 
                        gvcn.maGV,
                        gv.hoTen,
                        gvcn.lop as maLop,
                        lh.tenLop,
                        lh.siSo
                    FROM giaovienchunhiem gvcn
                    INNER JOIN giaovienbomon gv ON gvcn.maGV = gv.maGV
                    INNER JOIN lophoc lh ON gvcn.lop = lh.maLop
                    ORDER BY lh.tenLop";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachGVCN: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách giáo viên chưa là GVCN
     */
    public function getDanhSachGVChuaLaGVCN() {
        try {
            $sql = "SELECT 
                        gv.maGV,
                        gv.hoTen,
                        gv.monHocPhuTrach
                    FROM giaovienbomon gv
                    LEFT JOIN giaovienchunhiem gvcn ON gv.maGV = gvcn.maGV
                    WHERE gvcn.maGV IS NULL
                      AND gv.tinhTrangTaiKhoan = 'ACTIVE'
                    ORDER BY gv.hoTen";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachGVChuaLaGVCN: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách lớp chưa có GVCN
     */
    public function getDanhSachLopChuaCoGVCN() {
        try {
            $sql = "SELECT 
                        lh.maLop,
                        lh.tenLop,
                        lh.siSo,
                        lh.khoi
                    FROM lophoc lh
                    LEFT JOIN giaovienchunhiem gvcn ON lh.maLop = gvcn.lop
                    WHERE gvcn.maGV IS NULL
                    ORDER BY lh.khoi, lh.tenLop";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachLopChuaCoGVCN: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy mã giáo viên phân công theo maLop + maMonHoc.
     * Tham số hocKy, namHoc là tùy chọn để khớp chính xác hơn.
     * Trả về array ['maGV'=>..., 'hoTen'=>...] hoặc null nếu không tìm.
     */
    public function getGiaoVienPhanCongChoMon($maLop, $maMonHoc) {
        try {
            $maMonHocClean = strtoupper(trim($maMonHoc));

            // 1) Kiểm tra bảng phanconggiangday trước
            $sql = "SELECT maGV FROM phanconggiangday
                    WHERE maLop = :maLop AND maMonHoc = :maMonHoc
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['maLop' => $maLop, 'maMonHoc' => $maMonHocClean]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['maGV'])) {
                // Lấy thông tin GV chi tiết
                $gv = $this->getGiaoVienByMaGV($row['maGV']);
                if ($gv) return $gv;
                // nếu maGV tồn nhưng thông tin GV không có -> tiếp fallback
                error_log("PhanCongModel: maGV tìm thấy nhưng không có thông tin chi tiết: " . $row['maGV']);
            }

            // Fallback: tìm giáo viên theo giaovienbomon.monHocPhuTrach (case-insensitive)
            $sql2 = "SELECT maGV, hoTen FROM giaovienbomon
                     WHERE TRIM(UPPER(monHocPhuTrach)) = :maMonHoc
                     LIMIT 1";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute(['maMonHoc' => $maMonHocClean]);
            $gv2 = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($gv2) {
                return $gv2;
            }

            // Nếu vẫn không tìm thấy => log chi tiết để debug
            error_log("PhanCongModel::getGiaoVienPhanCongChoMon - Not found. maLop={$maLop} maMonHoc={$maMonHocClean} hocKy=" . ($hocKyClean ?? 'NULL') . " namHoc=" . ($namHoc ?? 'NULL'));
            return null;

        } catch (PDOException $e) {
            error_log("Error getGiaoVienPhanCongChoMon: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy thông tin giáo viên theo maGV
     */
    private function getGiaoVienByMaGV($maGV) {
        try {
            $stmt = $this->db->prepare("SELECT maGV, hoTen, monHocPhuTrach FROM giaovienbomon WHERE maGV = :maGV LIMIT 1");
            $stmt->execute(['maGV' => $maGV]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error getGiaoVienByMaGV: " . $e->getMessage());
            return null;
        }
    }
}




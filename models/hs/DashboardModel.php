<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy thông tin học sinh từ username
     */
    public function getThongTinHocSinh($username) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        lh.tenLop,
                        lh.namHoc,
                        lh.maLop
                    FROM taikhoan tk
                    INNER JOIN hocsinh hs ON tk.maTaiKhoan = hs.maTaiKhoan
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy điểm trung bình học kỳ
     */
    public function getDiemTrungBinhHocKy($maHS, $hocKy, $namHoc) {
        try {
            $sql = "SELECT AVG((diemThuongXuyen + diemGiuaKy + diemCuoiKy * 2) / 4) as diemTB
                    FROM bangdiem
                    WHERE maHS = ? AND hocKy = ? AND namHoc = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS, $hocKy, $namHoc]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // FIX: Xử lý NULL trước khi round
            return $result && $result['diemTB'] !== null ? round($result['diemTB'], 2) : 0;
            
        } catch (PDOException $e) {
            error_log("Error getDiemTrungBinhHocKy: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * [DEPRECATED] Lấy hạnh kiểm - Chức năng tạm ngưng
     * Hiện tại return giá trị mặc định vì chưa có bảng hanhkiem
     */
    public function getHanhKiem($maHS, $hocKy, $namHoc) {
        return 'Tốt'; // Giá trị mặc định
        
        /* 
        // Code này sẽ dùng khi có bảng hanhkiem
        try {
            $sql = "SELECT xepLoai
                    FROM hanhkiem
                    WHERE maHS = ? AND hocKy = ? AND namHoc = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS, $hocKy, $namHoc]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['xepLoai'] : 'Tốt';
            
        } catch (PDOException $e) {
            error_log("Error getHanhKiem: " . $e->getMessage());
            return 'Tốt';
        }
        */
    }

    /**
     * Đếm đơn chờ phê duyệt
     */
    public function demDonChoPheDuyet($maHS) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM donxinphep
                    WHERE maHS = ? AND trangThai = 'Cho duyet'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['total'] : 0;
            
        } catch (PDOException $e) {
            error_log("Error demDonChoPheDuyet: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy điểm gần đây - FIX: Lấy từ bảng bangdiem thực tế
     */
    public function getDiemGanDay($maHS, $limit = 3) {
        try {
            $sql = "SELECT 
                        mh.tenMon as subject,
                        bd.diemCuoiKy as score,
                        'Cuối kỳ' as type,
                        CONCAT(bd.hocKy, ' - ', bd.namHoc) as date
                    FROM bangdiem bd
                    INNER JOIN monhoc mh ON bd.maMonHoc = mh.maMonHoc
                    WHERE bd.maHS = ? AND bd.diemCuoiKy IS NOT NULL
                    ORDER BY bd.namHoc DESC, 
                             CASE bd.hocKy 
                                 WHEN 'HK2' THEN 2
                                 WHEN 'HK1' THEN 1
                                 ELSE 0
                             END DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $maHS, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG
            error_log("=== getDiemGanDay ===");
            error_log("maHS: $maHS | limit: $limit");
            error_log("Rows: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getDiemGanDay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy lịch học hôm nay - FIX: Đơn giản hóa query, lấy theo thứ trong tuần
     */
    public function getLichHocHomNay($maLop) {
        try {
            $today = date('Y-m-d');
            $dayOfWeek = date('N'); // 1=T2, 2=T3,..., 7=CN
            
            // ⚠️ FIX: Đơn giản hóa - Lấy TKB theo thứ trong tuần hiện tại
            // Tìm thứ 2 của tuần này
            $monday = date('Y-m-d', strtotime('monday this week'));
            $sunday = date('Y-m-d', strtotime('sunday this week'));
            
            // Chuyển đổi: MySQL DAYOFWEEK: 1=CN, 2=T2,..., 7=T7
            $mysqlDayOfWeek = ($dayOfWeek == 7) ? 1 : $dayOfWeek + 1;
            
            // DEBUG LOG
            error_log("=== getLichHocHomNay DEBUG ===");
            error_log("maLop: $maLop");
            error_log("today: $today");
            error_log("PHP dayOfWeek: $dayOfWeek (1=T2, 7=CN)");
            error_log("MySQL dayOfWeek: $mysqlDayOfWeek (1=CN, 2=T2, 7=T7)");
            error_log("monday: $monday | sunday: $sunday");
            
            $sql = "SELECT 
                        tkb.tiet as period,
                        mh.tenMon as subject,
                        gv.hoTen as teacher,
                        ph.tenPhong as room,
                        tkb.ngayHoc,
                        DAYOFWEEK(tkb.ngayHoc) as ngayTrongTuan
                    FROM thoikhoabieu tkb
                    INNER JOIN monhoc mh ON tkb.maMonHoc = mh.maMonHoc
                    LEFT JOIN phonghoc ph ON tkb.maPhong = ph.maPhong
                    LEFT JOIN phanconggiangday pc ON pc.maLop = tkb.maLop 
                        AND pc.maMonHoc = tkb.maMonHoc
                    LEFT JOIN giaovienbomon gv ON pc.maGV = gv.maGV
                    WHERE tkb.maLop = ? 
                      AND DAYOFWEEK(tkb.ngayHoc) = ?
                      AND tkb.ngayHoc BETWEEN ? AND ?
                    ORDER BY tkb.tiet
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $mysqlDayOfWeek, $monday, $sunday]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Rows trả về: " . count($result));
            
            // ⚠️ DEBUG: Nếu không có dữ liệu, kiểm tra xem có TKB nào trong tuần này không
            if (count($result) == 0) {
                $checkStmt = $this->db->prepare("
                    SELECT 
                        COUNT(*) as total,
                        GROUP_CONCAT(DISTINCT DAYOFWEEK(ngayHoc)) as days
                    FROM thoikhoabieu 
                    WHERE maLop = ? 
                      AND ngayHoc BETWEEN ? AND ?
                ");
                $checkStmt->execute([$maLop, $monday, $sunday]);
                $check = $checkStmt->fetch();
                error_log("Tổng TKB tuần này: " . $check['total']);
                error_log("Các ngày có TKB: " . ($check['days'] ?? 'NONE'));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getLichHocHomNay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy điểm gần đây cho phụ huynh - Giống getDiemGanDay nhưng có thể dùng chung
     */
    public function getDiemGanDayPhuHuynh($maHS, $limit = 3) {
        return $this->getDiemGanDay($maHS, $limit);
    }
}

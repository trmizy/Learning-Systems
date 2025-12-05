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
     * Lấy hạnh kiểm - FIX: Không có bảng hanhkiem trong schema
     */
    public function getHanhKiem($maHS, $hocKy, $namHoc) {
        // Tạm thời return giá trị mặc định vì chưa có bảng hanhkiem
        return 'Tốt';
        
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
     * Lấy điểm gần đây - FIX: Sai cú pháp LIMIT với PDO
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
                    WHERE bd.maHS = ?
                    ORDER BY bd.namHoc DESC, bd.hocKy DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            // FIX: LIMIT phải bind như kiểu INT
            $stmt->bindValue(1, $maHS, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDiemGanDay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy lịch học hôm nay - FIX: Bỏ JOIN với giaovienbomon vì không có maGV
     */
    public function getLichHocHomNay($maLop) {
        try {
            $today = date('Y-m-d');
            
            $sql = "SELECT 
                        tkb.tiet as period,
                        mh.tenMon as subject,
                        NULL as teacher,
                        ph.tenPhong as room
                    FROM thoikhoabieu tkb
                    INNER JOIN monhoc mh ON tkb.maMonHoc = mh.maMonHoc
                    LEFT JOIN phonghoc ph ON tkb.maPhong = ph.maPhong
                    WHERE tkb.maLop = ? AND tkb.ngayHoc = ?
                    ORDER BY tkb.tiet";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop, $today]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getLichHocHomNay: " . $e->getMessage());
            return [];
        }
    }
}

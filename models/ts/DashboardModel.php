<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy thông tin thí sinh từ username
     * @param string $username - Tên đăng nhập
     * @return array|null - Thông tin thí sinh hoặc null
     */
    public function getThongTinThiSinh($username) {
        try {
            $sql = "SELECT 
                        ts.maThiSinh,
                        ts.hoTen,
                        ts.ngaySinh,
                        ts.gioiTinh,
                        ts.soDienThoai,
                        ts.soCCCD
                    FROM thisinh ts
                    INNER JOIN taikhoan tk ON tk.maTaiKhoan = ts.maTaiKhoan
                    WHERE tk.tenDangNhap = ? 
                      AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // LOG DEBUG
            error_log("=== getThongTinThiSinh ===");
            error_log("Username: $username");
            error_log("Result: " . ($result ? 'FOUND' : 'NOT FOUND'));
            
            return $result ?: null;
            
        } catch (PDOException $e) {
            error_log("ERROR getThongTinThiSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy điểm thi trung bình (THPT Quốc gia)
     * @param string $maThiSinh
     * @return float - Điểm TB (0 nếu chưa có điểm)
     */
    public function getDiemThiTongKet($maThiSinh) {
        try {
            $sql = "SELECT diem as diemTB
                    FROM thisinh
                    WHERE maThiSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $diemTB = $result && $result['diemTB'] !== null ? round($result['diemTB'], 2) : 0;
            
            // LOG DEBUG
            error_log("=== getDiemThiTongKet ===");
            error_log("maThiSinh: $maThiSinh | diemTB: $diemTB");
            
            return $diemTB;
            
        } catch (PDOException $e) {
            error_log("ERROR getDiemThiTongKet: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Đếm số nguyện vọng đã đăng ký
     * @param string $maThiSinh
     * @return int - Số nguyện vọng (tối đa 3)
     */
    public function demNguyenVongDaDangKy($maThiSinh) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM nguyenvong
                    WHERE maThiSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = $result ? (int)$result['total'] : 0;
            
            // LOG DEBUG
            error_log("=== demNguyenVongDaDangKy ===");
            error_log("maThiSinh: $maThiSinh | total: $total");
            
            return $total;
            
        } catch (PDOException $e) {
            error_log("ERROR demNguyenVongDaDangKy: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Đếm hồ sơ đã nộp
     * @param string $maThiSinh
     * @return int - Số hồ sơ đã nộp
     */
    public function demHoSoDaNop($maThiSinh) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM hosothisinh
                    WHERE maThiSinh = ? 
                      AND trangThai IN ('DA_NOP', 'DUYET')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = $result ? (int)$result['total'] : 0;
            
            // LOG DEBUG
            error_log("=== demHoSoDaNop ===");
            error_log("maThiSinh: $maThiSinh | total: $total");
            
            return $total;
            
        } catch (PDOException $e) {
            error_log("ERROR demHoSoDaNop: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy danh sách điểm thi theo môn (chi tiết)
     * @param string $maThiSinh
     * @return array - Danh sách điểm [['tenMon', 'diem', 'namThi'], ...]
     */
    public function getDiemThiTheoMon($maThiSinh) {
        try {
            $sql = "SELECT 
                ts.maThiSinh,
                ts.hoTen,
                ts.diemVan,
                ts.diemToan,
                ts.diemAnh,
                ts.namTuyenSinh
            FROM thi_inh ts
            WHERE ts.maThiSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("ERROR getDiemThiTheoMon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách nguyện vọng đã đăng ký
     * @param string $maThiSinh
     * @return array - Danh sách nguyện vọng
     */
    public function getDanhSachNguyenVong($maThiSinh) {
        try {
            $sql = "SELECT 
                        nv.maNguyenVong,
                        nv.maTruong,
                        nv.thuTuUuTien,
                        nv.trangThai,
                        nv.ngayDangKy
                    FROM nguyenvong nv
                    WHERE nv.maThiSinh = ?
                    ORDER BY nv.thuTuUuTien ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("ERROR getDanhSachNguyenVong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra xem thí sinh đã đăng ký đủ 3 nguyện vọng chưa
     * @param string $maThiSinh
     * @return bool
     */
    public function daDangKyDu3NguyenVong($maThiSinh) {
        return $this->demNguyenVongDaDangKy($maThiSinh) >= 3;
    }

    /**
     * Lấy trạng thái hồ sơ (tổng quan)
     * @param string $maThiSinh
     * @return array - ['total', 'da_nop', 'chua_nop', 'duyet', 'tu_choi']
     */
    public function getTrangThaiHoSo($maThiSinh) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN trangThai IN ('DA_NOP', 'DUYET') THEN 1 ELSE 0 END) as da_nop,
                        SUM(CASE WHEN trangThai = 'CHUA_NOP' THEN 1 ELSE 0 END) as chua_nop,
                        SUM(CASE WHEN trangThai = 'DUYET' THEN 1 ELSE 0 END) as duyet,
                        SUM(CASE WHEN trangThai = 'TU_CHOI' THEN 1 ELSE 0 END) as tu_choi
                    FROM hosothisinh
                    WHERE maThiSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThiSinh]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'da_nop' => 0,
                'chua_nop' => 0,
                'duyet' => 0,
                'tu_choi' => 0
            ];
            
        } catch (PDOException $e) {
            error_log("ERROR getTrangThaiHoSo: " . $e->getMessage());
            return [
                'total' => 0,
                'da_nop' => 0,
                'chua_nop' => 0,
                'duyet' => 0,
                'tu_choi' => 0
            ];
        }
    }
}

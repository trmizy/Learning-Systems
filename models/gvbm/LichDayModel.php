<?php
require_once __DIR__ . '/../../config/database.php';

class LichDayModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy mã GV từ username
     */
    public function getMaGVByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT gv.maGV
                FROM taikhoan tk
                INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaGVByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy lịch dạy theo tuần
     */
    public function getLichDayTheoTuan($maGV, $startDate, $endDate) {
        try {
            $sql = "SELECT 
                        tkb.tiet as tietHoc,
                        tkb.ngayHoc,
                        mh.tenMon,
                        lh.tenLop,
                        ph.tenPhong,
                        DAYOFWEEK(tkb.ngayHoc) AS thuTrongTuan
                    FROM thoikhoabieu tkb
                    INNER JOIN phanconggiangday pc ON tkb.maLop = pc.maLop 
                        AND tkb.maMonHoc = pc.maMonHoc
                    INNER JOIN monhoc mh ON tkb.maMonHoc = mh.maMonHoc
                    INNER JOIN lophoc lh ON tkb.maLop = lh.maLop
                    LEFT JOIN phonghoc ph ON tkb.maPhong = ph.maPhong
                    WHERE pc.maGV = ?
                      AND tkb.ngayHoc BETWEEN ? AND ?
                    ORDER BY tkb.ngayHoc, tkb.tiet";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV, $startDate, $endDate]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getLichDayTheoTuan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin giáo viên
     */
    public function getThongTinGiaoVien($maGV) {
        try {
            $stmt = $this->db->prepare("
                SELECT hoTen, monHocPhuTrach, email, soDienThoai
                FROM giaovienbomon
                WHERE maGV = ?
                LIMIT 1
            ");
            $stmt->execute([$maGV]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinGiaoVien: " . $e->getMessage());
            return null;
        }
    }
}

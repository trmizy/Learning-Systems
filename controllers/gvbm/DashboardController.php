<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        require_role(['gvbm', 'gvcn', 'ttbm']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Phiên đăng nhập hết hạn.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy mã giáo viên
        $maGV = $this->getMaGVByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php?action=login');
            exit;
        }

        // Lấy thông tin giáo viên
        $thongTinGV = $this->getThongTinGiaoVien($maGV);
        
        // Lấy lịch dạy hôm nay
        $todaySchedule = $this->getLichDayHomNay($maGV);
        
        // Đảm bảo $todaySchedule luôn là array
        if (!is_array($todaySchedule)) {
            $todaySchedule = [];
        }

        // Render view
        require_once __DIR__ . '/../../views/gvbm/dashboard.php';
    }

    private function getMaGVByUsername($username) {
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

    private function getThongTinGiaoVien($maGV) {
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

    private function getLichDayHomNay($maGV) {
        try {
            $today = date('Y-m-d');
            $dayOfWeek = date('N');
            $mysqlDayOfWeek = ($dayOfWeek == 7) ? 1 : $dayOfWeek + 1;
            
            $sql = "SELECT 
                        tkb.tiet as period,
                        mh.tenMon as subject,
                        lh.tenLop as class,
                        ph.tenPhong as room
                    FROM thoikhoabieu tkb
                    INNER JOIN phanconggiangday pc ON tkb.maLop = pc.maLop 
                        AND tkb.maMonHoc = pc.maMonHoc
                    INNER JOIN monhoc mh ON tkb.maMonHoc = mh.maMonHoc
                    INNER JOIN lophoc lh ON tkb.maLop = lh.maLop
                    LEFT JOIN phonghoc ph ON tkb.maPhong = ph.maPhong
                    WHERE pc.maGV = ?
                      AND DAYOFWEEK(tkb.ngayHoc) = ?
                      AND tkb.ngayHoc BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                                          AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY tkb.tiet
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV, $mysqlDayOfWeek]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return is_array($result) ? $result : [];
            
        } catch (PDOException $e) {
            error_log("Error getLichDayHomNay: " . $e->getMessage());
            return [];
        }
    }
}

<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../config/database.php';

class ViewAssignController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        // ✅ CHO PHÉP CẢ GVBM VÀ GVCN
        require_role(['gvbm', 'gvcn']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được giáo viên đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã giáo viên từ username
        $maGV = $this->getMaGiaoVienByUsername($user['username']);

        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách phân công
        $phanCong = $this->getDanhSachPhanCong($maGV);

        // ⚠️ QUAN TRỌNG: Load view (view này ĐÃ CÓ header và footer)
        require_once __DIR__ . '/../../views/gvbm/view_assign.php';
        // ⚠️ KHÔNG RETURN, KHÔNG ECHO gì thêm sau dòng này
    }

    private function getMaGiaoVienByUsername($username) {
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
            error_log("Error getMaGiaoVienByUsername: " . $e->getMessage());
            return null;
        }
    }

    private function getDanhSachPhanCong($maGV) {
        try {
            $sql = "SELECT 
                        hocKy,
                        kyThi,
                        soLuongDe,
                        thoiHan,
                        ghiChu
                    FROM bangphancongrade
                    WHERE maGV = ?
                    ORDER BY thoiHan DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachPhanCong: " . $e->getMessage());
            return [];
        }
    }
}

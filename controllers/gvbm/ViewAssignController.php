<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ttbm/AssignExamModel.php';

class ViewAssignController {
    private AssignExamModel $model;

    public function __construct() {
        // Kiểm tra quyền giáo viên bộ môn
        require_role(['gvbm']);
        
        $this->model = new AssignExamModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // Lấy thông tin user từ session
        $user = current_user();
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được giáo viên đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã giáo viên từ username
        $maGV = $this->getGiaoVienByUsername($user['username']);
        
        if (!$maGV) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin giáo viên.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách phân công
        $phanCong = $this->model->getPhanCongTheoGiaoVien($maGV);
        
        include __DIR__ . '/../../views/gvbm/view_assign.php';
    }

    /**
     * Helper: Lấy mã giáo viên từ username
     */
    private function getGiaoVienByUsername(string $username): ?string {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Lấy maTaiKhoan
            $stmt = $conn->prepare("
                SELECT maTaiKhoan 
                FROM TaiKhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $taiKhoan = $stmt->fetch();
            
            if (!$taiKhoan) {
                return null;
            }
            
            // Lấy maGV từ GiaoVienBoMon
            $stmt2 = $conn->prepare("
                SELECT maGV 
                FROM GiaoVienBoMon 
                WHERE maTaiKhoan = ?
                LIMIT 1
            ");
            $stmt2->execute([$taiKhoan['maTaiKhoan']]);
            $gv = $stmt2->fetch();
            
            return $gv ? $gv['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getGiaoVienByUsername: " . $e->getMessage());
            return null;
        }
    }
}

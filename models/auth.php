<?php
// File: models/auth.php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($username, $password) {
        try {
            // 1. Lấy tài khoản
            $stmt = $this->db->prepare("SELECT * FROM taikhoan WHERE (tenDangNhap = ? OR email = ?) AND trangThai = 'ACTIVE' LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if (!$user || $password !== $user['matKhau']) return false;

            // 2. Lấy vai trò
            $roleStmt = $this->db->prepare("SELECT maVaiTro FROM taikhoan_vaitro WHERE maTaiKhoan = ?");
            $roleStmt->execute([$user['maTaiKhoan']]);
            $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            // 3. Xác định vai trò chính
            $currentRole = 'guest';
            $priority = ['admin','bgh','ttbm','gvcn','gvbm','nhanvienso','ph','hs'];
            foreach ($priority as $p) {
                if (in_array($p, $roles)) { $currentRole = $p; break; }
            }

            // 4. Lấy thông tin chi tiết (Họ tên & ID)
            $profile = $this->getProfile($user['maTaiKhoan'], $currentRole);

            // 5. Trả về dữ liệu session
            return [
                'user_id'    => $user['maTaiKhoan'],
                'username'   => $user['tenDangNhap'],
                'full_name'  => $profile['hoTen'],
                'role'       => $currentRole,
                'roles'      => $roles,
                'teacher_id' => $profile['teacher_id'] ?? null, // Quan trọng cho GV
                'student_id' => $profile['student_id'] ?? null
            ];
        } catch (Exception $e) { return false; }
    }

    private function getProfile($userId, $role) {
        $table = ''; $colID = '';
        switch ($role) {
            case 'gvbm': case 'gvcn': case 'ttbm': 
                $table = 'giaovienbomon'; $colID = 'maGV AS teacher_id'; break;
            case 'hs': 
                $table = 'hocsinh'; $colID = 'maHS AS student_id'; break;
            // ... thêm các case khác nếu cần
            default: return ['hoTen' => 'Người dùng'];
        }
        
        if ($table) {
            $stmt = $this->db->prepare("SELECT hoTen, $colID FROM $table WHERE maTaiKhoan = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: ['hoTen' => 'Người dùng'];
        }
        return ['hoTen' => 'Người dùng'];
    }
}
?>
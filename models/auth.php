<?php 
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Đăng nhập: trả về mảng thông tin đầy đủ
     * gồm maGV, maTaiKhoan, role, hoTen, môn,...
     */
    public function login($username, $password) {
        try {
            // 1) Lấy tài khoản (KHÔNG group join vai trò để tránh nhân bản dòng)
                // Use lowercase table names to match schema and fetch the active account
                $stmt = $this->db->prepare(
                    "SELECT t.*
                    FROM taikhoan t
                    WHERE (t.tenDangNhap = ? OR t.email = ?) AND UPPER(TRIM(COALESCE(t.trangThai,''))) = 'ACTIVE'
                    LIMIT 1"
                );
            $stmt->execute([$username, $username]);
            $tk = $stmt->fetch();

                // Kiểm tra tồn tại + mật khẩu (hệ thống dùng password_hash)
                if (!$user || !password_verify($password, $user['matKhau'])) {
                    return false;
                }

            // 2) Lấy TẤT CẢ vai trò của tài khoản -> mảng thô từ DB (vd: ['admin','gvbm', ...])
                // Read roles from the taikhoan_vaitro table
                $roleStmt = $this->db->prepare(
                    "SELECT maVaiTro
                    FROM taikhoan_vaitro
                    WHERE maTaiKhoan = ?
                    ORDER BY maVaiTro"
                );
            $roleStmt->execute([$user['maTaiKhoan']]);
            $dbRoles = $roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            // 4) Chuẩn hoá vai trò
            $mappedRoles = [];
            foreach ($dbRoles as $r) {
                $mappedRoles[] = $this->mapRole($r);
            }
            $mappedRoles = array_values(array_unique($mappedRoles));

            // 5) Ưu tiên role
            $priority = ['admin','bgh','ttbm','gvcn','gvbm','nhanvienso','ph','hs'];
            $currentRole = 'guest';

            foreach ($priority as $p) {
                if (in_array($p, $mappedRoles, true)) {
                    $currentRole = $p;
                    break;
                }
            }

            // 6) Trả về FULL SESSION
            return [
                'username'      => $tk['tenDangNhap'],
                'email'         => $tk['email'],
                'maTaiKhoan'    => $tk['maTaiKhoan'],       // quan trọng
                'maGV'          => $gv['maGV'],              // quan trọng
                'full_name'     => $gv['hoTen'],
                'monHoc'        => $gv['monHocPhuTrach'],
                'chucVu'        => $gv['chucVu'],
                'role'          => $currentRole,
                'roles'         => $mappedRoles
            ];

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Map mã vai trò DB → app role
     */
    private function mapRole($dbRole) {
        $dbRole = strtolower((string)$dbRole);

        return match($dbRole) {
            'admin'       => 'admin',
            'bgh'         => 'bgh',
            'gvbm'        => 'gvbm',
            'gvcn'        => 'gvcn',
            'ttbm'        => 'ttbm',
            'hs'          => 'hs',
            'ph'          => 'ph',
            'nhanvienso'  => 'nhanvienso',
            default       => 'guest'
        };
    }
}
?>
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
            // 1) Lấy tài khoản
            $stmt = $this->db->prepare("
                SELECT *
                FROM TaiKhoan
                WHERE (tenDangNhap = ? OR email = ?)
                  AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username, $username]);
            $tk = $stmt->fetch();

            if (!$tk || $password !== $tk['matKhau']) {
                return false;
            }

            // 2) Lấy thông tin giáo viên (Tổ trưởng hoặc GVBM)
            $gvStmt = $this->db->prepare("
                SELECT maGV, hoTen, monHocPhuTrach, chucVu, maTaiKhoan
                FROM GiaoVienBoMon
                WHERE maTaiKhoan = ?
                LIMIT 1
            ");
            $gvStmt->execute([$tk['maTaiKhoan']]);
            $gv = $gvStmt->fetch() ?: [
                'maGV'            => null,
                'hoTen'           => "Người dùng",
                'monHocPhuTrach'  => null,
                'chucVu'          => null,
                'maTaiKhoan'      => $tk['maTaiKhoan']
            ];

            // 3) Lấy tất cả vai trò
            $roleStmt = $this->db->prepare("
                SELECT maVaiTro
                FROM TaiKhoan_VaiTro
                WHERE maTaiKhoan = ?
            ");
            $roleStmt->execute([$tk['maTaiKhoan']]);
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

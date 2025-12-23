<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // Bảo đảm mode fetch mặc định là ASSOC
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Đăng nhập và trả về thông tin người dùng:
     * [
     *   'username'   => string,
     *   'email'      => string,
     *   'full_name'  => string,
     *   'role'       => string,       // role hiện tại (đã map)
     *   'roles'      => string[],     // mảng tất cả role (đã map)
     * ]
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
            $user = $stmt->fetch();

                // Kiểm tra tồn tại + mật khẩu (hệ thống dùng password_hash)
                if (!$user) {
                    return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác'];
                }

            // ⚠️ FIX: Kiểm tra cả plain text VÀ hashed password
            $passwordMatch = false;
            
            // Trường hợp 1: Mật khẩu plain text (từ DB cũ)
            if ($user['matKhau'] === $password) {
                $passwordMatch = true;
            }
            // Trường hợp 2: Mật khẩu đã hash
            elseif (password_verify($password, $user['matKhau'])) {
                $passwordMatch = true;
            }

            if (!$passwordMatch) {
                return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác'];
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

            // 3) Map vai trò DB -> app role; lọc rỗng và unique
            $mappedRoles = [];
            foreach ($dbRoles as $r) {
                $m = $this->mapRole($r);
                if ($m && $m !== 'guest') {
                    $mappedRoles[] = $m;
                }
            }
            $mappedRoles = array_values(array_unique($mappedRoles));

            // 4) Chọn role hiện tại theo thứ tự ưu tiên
            // Ưu tiên: admin > bgh > ttbm > gvcn > gvbm > nhanvienso > ph > hs
            $priority = ['admin','bgh','ttbm','gvcn','gvbm','nhanvienso','ph','hs','ts'];
            $currentRole = 'guest';
            if (!empty($mappedRoles)) {
                foreach ($priority as $p) {
                    if (in_array($p, $mappedRoles, true)) {
                        $currentRole = $p;
                        break;
                    }
                }
                if ($currentRole === 'guest') {
                    // Nếu không khớp ưu tiên thì lấy role đầu tiên trong danh sách
                    $currentRole = $mappedRoles[0];
                }
            }

            // 5) Lấy họ tên theo role hiện tại
            $fullName = $this->getFullName($user['maTaiKhoan'], $currentRole);

            // 6) Trả về gói thông tin đầy đủ
            return [
                'username'  => $user['tenDangNhap'],
                'email'     => $user['email'],
                'full_name' => $fullName,
                'role'      => $currentRole,   // role đang sử dụng
                'roles'     => $mappedRoles,   // tất cả vai trò
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Trả về họ tên theo role hiện tại
     */
    private function getFullName($userId, $role) {
        $table = match($role) {
            'bgh'   => 'BanGiamHieu',
            'admin' => 'NhanVienPhongGiaoVu',
            'gvbm', 'gvcn', 'ttbm' => 'GiaoVienBoMon',
            'hs'    => 'HocSinh',
            'ph'    => 'PhuHuynh',
            'nhanvienso' => 'NhanVienSo',
            'ts' => 'ThiSinh',
            default => null
        };

        if (!$table) return "Người dùng";

        try {
            $stmt = $this->db->prepare("SELECT hoTen FROM $table WHERE maTaiKhoan = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result && !empty($result['hoTen']) ? $result['hoTen'] : "Người dùng";
        } catch (PDOException $e) {
            return "Người dùng";
        }
    }

    /**
     * Chuẩn hoá mã vai trò từ DB về key dùng trong app
     */
    private function mapRole($dbRole) {
        // Chuẩn hoá lower-case để tránh sai khác kiểu 'Admin'/'ADMIN'
        $dbRole = strtolower((string)$dbRole);

        return match($dbRole) {
            'admin'       => 'admin',        // Nhân viên phòng giáo vụ
            'bgh'         => 'bgh',          // Ban giám hiệu
            'gvbm'        => 'gvbm',         // Giáo viên bộ môn
            'gvcn'        => 'gvcn',         // Giáo viên chủ nhiệm
            'ttbm'        => 'ttbm',         // Tổ trưởng bộ môn
            'hs'          => 'hs',           // Học sinh
            'ph'          => 'ph',           // Phụ huynh
            'nhanvienso'  => 'nhanvienso',   // Nhân viên Sở
            'ts'          => 'ts',           // Thí sinh
            default       => 'guest'
        };
    }
}
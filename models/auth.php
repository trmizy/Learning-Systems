<?php
// File: models/auth.php
// (ĐÃ SỬA LỖI LOGIC)

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Đăng nhập và trả về thông tin người dùng đầy đủ
     */
    public function login($username, $password) {
        try {
            // 1) Lấy tài khoản
            $stmt = $this->db->prepare("
                SELECT t.*
                FROM TaiKhoan t
                WHERE (t.tenDangNhap = ? OR t.email = ?) AND t.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            // Kiểm tra tồn tại + mật khẩu
            if (!$user || $password !== $user['matKhau']) {
                return false;
            }

            // 2) Lấy TẤT CẢ vai trò của tài khoản
            $roleStmt = $this->db->prepare("
                SELECT maVaiTro
                FROM TaiKhoan_VaiTro
                WHERE maTaiKhoan = ?
                ORDER BY maVaiTro
            ");
            $roleStmt->execute([$user['maTaiKhoan']]);
            $dbRoles = $roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            // 3) Map vai trò DB -> app role
            $mappedRoles = [];
            foreach ($dbRoles as $r) {
                $m = $this->mapRole($r);
                if ($m && $m !== 'guest') {
                    $mappedRoles[] = $m;
                }
            }
            $mappedRoles = array_values(array_unique($mappedRoles));

            // 4) Chọn role hiện tại theo thứ tự ưu tiên
            $priority = ['admin','bgh','ttbm','gvcn','gvbm','nhanvienso','ph','hs'];
            $currentRole = 'guest';
            if (!empty($mappedRoles)) {
                foreach ($priority as $p) {
                    if (in_array($p, $mappedRoles, true)) {
                        $currentRole = $p;
                        break;
                    }
                }
                if ($currentRole === 'guest') {
                    $currentRole = $mappedRoles[0];
                }
            }

            // 5) === SỬA ĐỔI QUAN TRỌNG ===
            // Lấy thông tin chi tiết (Họ tên VÀ ID)
            $profileInfo = $this->getProfileInfo($user['maTaiKhoan'], $currentRole);

            // 6) === SỬA ĐỔI QUAN TRỌNG ===
            // Trả về gói thông tin đầy đủ (ĐÃ BAO GỒM teacher_id)
            return [
                'user_id'    => $user['maTaiKhoan'], // user_id chính là maTaiKhoan
                'username'   => $user['tenDangNhap'],
                'email'      => $user['email'],
                'full_name'  => $profileInfo['hoTen'],
                'role'       => $currentRole,
                'roles'      => $mappedRoles,
                'teacher_id' => $profileInfo['teacher_id'] ?? null, // <-- ĐÂY LÀ KHÓA CHÍNH!
                'student_id' => $profileInfo['student_id'] ?? null,
                'bgh_id'     => $profileInfo['bgh_id'] ?? null,
                'admin_id'   => $profileInfo['admin_id'] ?? null,
                'ph_id'      => $profileInfo['ph_id'] ?? null,
                'nvs_id'     => $profileInfo['nvs_id'] ?? null
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * === SỬA ĐỔI QUAN TRỌNG ===
     * Đổi tên hàm từ getFullName -> getProfileInfo
     * Trả về mảng (hoTen + ID) thay vì chỉ chuỗi hoTen
     */
    private function getProfileInfo($userId, $role) {
        $table = '';
        $idColumn = ''; // Cột ID đặc trưng (maGV, maHS, ...)
        $defaultName = 'Người dùng';

        // Xác định bảng và cột ID dựa trên vai trò
        switch($role) {
            case 'bgh': 
                $table = 'BanGiamHieu'; $idColumn = 'maBGH AS bgh_id'; $defaultName = 'Ban Giám Hiệu'; break;
            case 'admin': 
                $table = 'NhanVienPhongGiaoVu'; $idColumn = 'maNVGiaoVu AS admin_id'; $defaultName = 'Admin'; break;
            case 'gvbm':
            case 'gvcn':
            case 'ttbm':
                $table = 'GiaoVienBoMon'; $idColumn = 'maGV AS teacher_id'; $defaultName = 'Giáo viên'; break;
            case 'hs':
                $table = 'HocSinh'; $idColumn = 'maHS AS student_id'; $defaultName = 'Học sinh'; break;
            case 'ph':
                $table = 'PhuHuynh'; $idColumn = 'maPH AS ph_id'; $defaultName = 'Phụ huynh'; break;
            case 'nhanvienso':
                $table = 'NhanVienSo'; $idColumn = 'maNhanVienSo AS nvs_id'; $defaultName = 'Nhân viên Sở'; break;
            default:
                return ['hoTen' => $defaultName]; // Trả về mảng mặc định
        }

        try {
            // Lấy cả hoTen và ID đặc trưng (ví dụ: maGV)
            $stmt = $this->db->prepare("SELECT hoTen, $idColumn FROM $table WHERE maTaiKhoan = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            if (!$result) {
                 return ['hoTen' => $defaultName];
            }
            
            // Trả về một mảng chứa tất cả thông tin
            return $result; // $result đã chứa ['hoTen' => '...', 'teacher_id' => '...']
            
        } catch (PDOException $e) {
            error_log("Lỗi getProfileInfo: " . $e->getMessage());
            return ['hoTen' => $defaultName];
        }
    }

    /**
     * Chuẩn hoá mã vai trò từ DB về key dùng trong app
     * (Hàm này đã đúng, giữ nguyên)
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
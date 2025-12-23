<?php
require_once __DIR__ . '/../../config/database.php';

class SchoolAccountModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Lấy danh sách tất cả các trường kèm trạng thái tài khoản
     * @return array Danh sách trường
     */
    public function getDanhSachTruongVaTrangThaiTaiKhoan() {
        try {
            $sql = "SELECT * FROM viewTrangThaiTaiKhoanTruong ORDER BY maTruong";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachTruongVaTrangThaiTaiKhoan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin chi tiết một trường
     * @param string $maTruong Mã trường
     * @return array|null Thông tin trường
     */
    public function getThongTinTruong($maTruong) {
        try {
            $sql = "SELECT * FROM viewTrangThaiTaiKhoanTruong WHERE maTruong = :maTruong";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':maTruong', $maTruong);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in getThongTinTruong: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra trường đã có tài khoản chưa
     * LƯU Ý: Chỉ kiểm tra tài khoản có vai trò 'admin' (tài khoản trường), không tính tài khoản giáo viên
     * @param string $maTruong Mã trường
     * @return bool True nếu đã có tài khoản trường
     */
    public function kiemTraDaCoTaiKhoan($maTruong) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM taikhoan tk
                    INNER JOIN taikhoan_vaitro tkv ON tk.maTaiKhoan = tkv.maTaiKhoan
                    WHERE tk.maTruong = :maTruong AND tkv.maVaiTro = 'admin'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':maTruong', $maTruong);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error in kiemTraDaCoTaiKhoan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hàm chung để tạo mã tự động
     * @param string $table Tên bảng
     * @param string $column Tên cột chứa mã
     * @param string $prefix Tiền tố mã
     * @param int $prefixLength Độ dài tiền tố
     * @param int $numberLength Độ dài phần số
     * @param string $condition Điều kiện WHERE (optional)
     * @return string Mã mới
     */
    private function taoMaTuDong($table, $column, $prefix, $prefixLength, $numberLength, $condition = '') {
        try {
            $where = $condition ? "WHERE $condition AND" : "WHERE";
            $sql = "SELECT $column FROM $table $where $column LIKE :pattern ORDER BY $column DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['pattern' => $prefix . '%']);
            $result = $stmt->fetch();
            
            $newNumber = $result ? intval(substr($result[$column], $prefixLength)) + 1 : 1;
            return $prefix . str_pad($newNumber, $numberLength, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error in taoMaTuDong: " . $e->getMessage());
            return $prefix . str_pad(rand(1, pow(10, $numberLength) - 1), $numberLength, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Tạo mã tài khoản cho nhân viên sở theo format TKGVUXXX
     */
    private function taoMaTaiKhoanSo() {
        return $this->taoMaTuDong('nhanvienphonggiaovu', 'maTaiKhoan', 'TKGVU', 5, 3);
    }

    /**
     * Tạo mã nhân viên giáo vụ theo format TRXXXNVYYZZZZ
     */
    private function taoMaNhanVienGiaoVu($maTruong) {
        $prefix = $maTruong . 'NV' . date('y');
        return $this->taoMaTuDong('nhanvienphonggiaovu', 'maNVGiaoVu', $prefix, strlen($prefix), 4);
    }

    /**
     * Tạo mã trường tự động theo format TRXXX
     */
    private function taoMaTruong() {
        return $this->taoMaTuDong('truong', 'maTruong', 'TR', 2, 3);
    }

    /**
     * Validate email
     */
    private function validateEmail($email) {
        if (empty($email)) {
            throw new Exception('Email không được để trống');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
    }

    /**
     * Kiểm tra email đã tồn tại chưa
     */
    private function kiemTraEmailTonTai($email, $excludeMaTruong = null) {
        $sql = "SELECT COUNT(*) as count FROM truong WHERE email = :email";
        if ($excludeMaTruong) {
            $sql .= " AND maTruong != :maTruong";
        }
        $stmt = $this->conn->prepare($sql);
        $params = ['email' => $email];
        if ($excludeMaTruong) {
            $params['maTruong'] = $excludeMaTruong;
        }
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }

    /**
     * Tạo tài khoản cho trường
     */
    public function taoTaiKhoanChoTruong($maTruong, $maNhanVienSo) {
        try {
            $this->conn->beginTransaction();

            // Lấy và validate thông tin trường
            $truongInfo = $this->getThongTinTruong($maTruong);
            if (!$truongInfo) {
                throw new Exception("Không tìm thấy thông tin trường");
            }
            if (empty($truongInfo['email'])) {
                throw new Exception("Không có email của trường. Vui lòng bổ sung email trước.");
            }
            if ($truongInfo['trangThaiCapTaiKhoan'] === 'Đã cấp') {
                throw new Exception("Trường này đã được cấp tài khoản");
            }

            // Tạo thông tin tài khoản
            $accountData = [
                'maTaiKhoan' => $this->taoMaTaiKhoanSo(),
                'maNVGiaoVu' => $this->taoMaNhanVienGiaoVu($maTruong),
                'tenDangNhap' => $truongInfo['email'],
                'matKhau' => '1111',
                'email' => $truongInfo['email'],
                'soDienThoai' => $truongInfo['soDienThoai'],
                'maTruong' => $maTruong
            ];

            // Insert tài khoản (chỉ truyền các field cần thiết)
            $this->insertTaiKhoan([
                'maTaiKhoan' => $accountData['maTaiKhoan'],
                'tenDangNhap' => $accountData['tenDangNhap'],
                'matKhau' => $accountData['matKhau'],
                'email' => $accountData['email'],
                'soDienThoai' => $accountData['soDienThoai'],
                'maTruong' => $accountData['maTruong']
            ]);

            // Insert nhân viên phòng giáo vụ
            $this->insertNhanVienGiaoVu([
                'maNVGiaoVu' => $accountData['maNVGiaoVu'],
                'hoTen' => $truongInfo['hoTenNV'],
                'email' => $accountData['email'],
                'soDienThoai' => $accountData['soDienThoai'],
                'maTruong' => $accountData['maTruong'],
                'maTaiKhoan' => $accountData['maTaiKhoan']
            ]);

            // Gán vai trò admin
            $this->ganVaiTro($accountData['maTaiKhoan'], 'admin');

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Tạo tài khoản thành công',
                'data' => array_merge($accountData, [
                    'tenTruong' => $truongInfo['tenTruong']
                ])
            ];

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Insert tài khoản vào database
     */
    private function insertTaiKhoan($data) {
        $sql = "INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                VALUES (:maTaiKhoan, :tenDangNhap, :matKhau, :email, :soDienThoai, 'ACTIVE', :maTruong)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt->execute($data)) {
            throw new Exception("Lỗi khi tạo tài khoản: " . implode(', ', $stmt->errorInfo()));
        }
    }

    /**
     * Insert nhân viên phòng giáo vụ
     */
    private function insertNhanVienGiaoVu($data) {
        $sql = "INSERT INTO nhanvienphonggiaovu (maNVGiaoVu, hoTen, chucDanh, email, soDienThoai, trangThai, maTruong, maTaiKhoan, ngayTao)
                VALUES (:maNVGiaoVu, :hoTen, 'Nhan vien phong giao vu', :email, :soDienThoai, 'ACTIVE', :maTruong, :maTaiKhoan, NOW())";
        $this->conn->prepare($sql)->execute($data);
    }

    /**
     * Gán vai trò cho tài khoản
     */
    private function ganVaiTro($maTaiKhoan, $maVaiTro) {
        // Đảm bảo vai trò tồn tại
        $check = $this->conn->prepare("SELECT maVaiTro FROM vaitro WHERE maVaiTro = ?");
        $check->execute([$maVaiTro]);
        if (!$check->fetch()) {
            $this->conn->prepare("INSERT INTO vaitro (maVaiTro, tenVaiTro) VALUES (?, 'Quản trị viên trường')")
                       ->execute([$maVaiTro]);
        }
        // Gán vai trò
        $this->conn->prepare("INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) VALUES (?, ?)")
                   ->execute([$maTaiKhoan, $maVaiTro]);
    }

    /**
     * Thêm trường mới vào hệ thống
     */
    public function themTruongMoi($data) {
        try {
            // Validate
            if (empty($data['tenTruong'])) {
                throw new Exception('Tên trường không được để trống');
            }
            $this->validateEmail($data['email']);
            
            if ($this->kiemTraEmailTonTai($data['email'])) {
                throw new Exception('Email này đã được sử dụng bởi trường khác');
            }
            
            // Insert
            $maTruong = $this->taoMaTruong();
            $sql = "INSERT INTO truong (maTruong, tenTruong, diaChi, email, soDienThoai) 
                    VALUES (:maTruong, :tenTruong, :diaChi, :email, :soDienThoai)";
            
            $this->conn->prepare($sql)->execute([
                'maTruong' => $maTruong,
                'tenTruong' => $data['tenTruong'],
                'diaChi' => $data['diaChi'] ?? '',
                'email' => $data['email'],
                'soDienThoai' => $data['soDienThoai'] ?? ''
            ]);
            
            return [
                'success' => true,
                'message' => 'Thêm trường mới thành công',
                'data' => ['maTruong' => $maTruong, 'tenTruong' => $data['tenTruong']]
            ];
        } catch (Exception $e) {
            error_log("Error in themTruongMoi: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Gửi email thông tin tài khoản
     * @param array $accountData Dữ liệu tài khoản
     * @return bool Kết quả gửi email
     */
    public function guiEmailThongTinTaiKhoan($accountData) {
        try {
            $to = $accountData['email'];
            $subject = "Thông tin tài khoản đăng nhập - Hệ thống quản lý giáo dục";
            
            $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background: #f9f9f9; }
                        .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Thông tin tài khoản đăng nhập</h2>
                        </div>
                        <div class='content'>
                            <p>Kính gửi: <strong>{$accountData['tenTruong']}</strong></p>
                            <p>Hệ thống quản lý giáo dục đã cấp tài khoản đăng nhập cho trường. Dưới đây là thông tin chi tiết:</p>
                            
                            <div class='info-box'>
                                <strong>Tên đăng nhập:</strong> {$accountData['tenDangNhap']}<br>
                                <strong>Mật khẩu:</strong> {$accountData['matKhau']}<br>
                                <strong>Mã trường:</strong> {$accountData['maTruong']}
                            </div>
                            
                            <p><strong>Lưu ý:</strong></p>
                            <ul>
                                <li>Vui lòng đổi mật khẩu ngay sau lần đăng nhập đầu tiên</li>
                                <li>Không chia sẻ thông tin tài khoản với người khác</li>
                                <li>Liên hệ với Sở Giáo dục nếu gặp vấn đề đăng nhập</li>
                            </ul>
                        </div>
                        <div class='footer'>
                            <p>Email tự động - Vui lòng không trả lời</p>
                            <p>&copy; 2025 Hệ thống quản lý giáo dục</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: noreply@edu.vn" . "\r\n";
            
            // Trong môi trường development, log thay vì gửi thật
            if (getenv('APP_ENV') === 'development') {
                error_log("Email would be sent to: " . $to);
                error_log("Subject: " . $subject);
                error_log("Content: " . strip_tags($message));
                return true;
            }
            
            return mail($to, $subject, $message, $headers);
            
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật thông tin trường (không cập nhật email)
     */
    public function capNhatThongTinTruong($maTruong, $data) {
        try {
            $this->conn->beginTransaction();
            
            // Validate
            if (!$this->conn->prepare("SELECT 1 FROM truong WHERE maTruong = ?")->execute([$maTruong])) {
                throw new Exception('Không tìm thấy trường trong hệ thống');
            }
            if (empty($data['tenTruong'])) {
                throw new Exception('Tên trường không được để trống');
            }
            
            // Update
            $sql = "UPDATE truong SET tenTruong = ?, diaChi = ?, soDienThoai = ? WHERE maTruong = ?";
            $this->conn->prepare($sql)->execute([
                $data['tenTruong'],
                $data['diaChi'],
                $data['soDienThoai'],
                $maTruong
            ]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật thông tin trường thành công'];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
}
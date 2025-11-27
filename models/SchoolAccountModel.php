<?php
require_once __DIR__ . '/../config/database.php';

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
     * Tạo mã tài khoản cho nhân viên sở theo format TKGVUXXX
     * @return string Mã tài khoản mới
     */
    private function taoMaTaiKhoanSo() {
        try {
            $sql = "SELECT maTaiKhoan FROM nhanvienphonggiaovu 
                    WHERE maTaiKhoan LIKE 'TKGVU%' 
                    ORDER BY maTaiKhoan DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                // Lấy số cuối và tăng lên 1
                $lastNumber = intval(substr($result['maTaiKhoan'], 5)); // Bỏ "TKGVU"
                $newNumber = $lastNumber + 1;
            } else {
                // Chưa có tài khoản nào
                $newNumber = 1;
            }
            
            return 'TKGVU' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error in taoMaTaiKhoanSo: " . $e->getMessage());
            return 'TKGVU' . rand(100, 999);
        }
    }

    /**
     * Tạo mã nhân viên giáo vụ theo format TRXXXNVYYZZZZ
     * XXX: Mã trường (3 ký tự)
     * YY: Năm cấp tài khoản (2 chữ số cuối)
     * ZZZZ: Số random thứ tự (4 chữ số)
     * @param string $maTruong Mã trường (ví dụ: TR001)
     * @return string Mã nhân viên mới
     */
    private function taoMaNhanVienGiaoVu($maTruong) {
        try {
            // Lấy 3 số cuối của mã trường (TR001 -> 001)
            $soTruong = substr($maTruong, 2, 3);
            
            // Lấy 2 số cuối của năm hiện tại (2025 -> 25)
            $namCap = date('y');
            
            // Tìm số thứ tự lớn nhất cho trường này trong năm hiện tại
            $pattern = $maTruong . 'NV' . $namCap . '%';
            $sql = "SELECT maNVGiaoVu FROM nhanvienphonggiaovu 
                    WHERE maNVGiaoVu LIKE :pattern 
                    ORDER BY maNVGiaoVu DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['pattern' => $pattern]);
            $result = $stmt->fetch();
            
            if ($result) {
                // Lấy 4 số cuối và tăng lên 1
                $lastNumber = intval(substr($result['maNVGiaoVu'], -4));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $maTruong . 'NV' . $namCap . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error in taoMaNhanVienGiaoVu: " . $e->getMessage());
            return $maTruong . 'NV' . date('y') . rand(1000, 9999);
        }
    }

    /**
     * Tạo tài khoản cho trường
     * @param string $maTruong Mã trường
     * @param string $maNhanVienSo Mã nhân viên sở thực hiện
     * @return array Kết quả: ['success' => bool, 'message' => string, 'data' => array]
     */
    public function taoTaiKhoanChoTruong($maTruong, $maNhanVienSo) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Lấy thông tin trường
            $truongInfo = $this->getThongTinTruong($maTruong);
            
            if (!$truongInfo) {
                throw new Exception("Không tìm thấy thông tin trường");
            }

            // 2. Kiểm tra email
            if (empty($truongInfo['email'])) {
                throw new Exception("Không có email của trường trong hệ thống. Vui lòng bổ sung email trước khi cấp tài khoản.");
            }

            // 3. Kiểm tra đã có tài khoản chưa
            if ($truongInfo['trangThaiCapTaiKhoan'] === 'Đã cấp') {
                throw new Exception("Trường này đã được cấp tài khoản");
            }

            // 4. Tạo thông tin tài khoản
            // Tạo mã tài khoản theo format TKGVUXXX
            $maTaiKhoan = $this->taoMaTaiKhoanSo();
            
            // Tạo mã nhân viên giáo vụ theo format TRXXXNVYYZZZZ
            $maNVGiaoVu = $this->taoMaNhanVienGiaoVu($maTruong);
            
            // Email từ thông tin trường
            $email = $truongInfo['email'];
            $soDienThoai = $truongInfo['soDienThoai'];
            
            // Tên đăng nhập = email của nhân viên phòng giáo vụ
            $tenDangNhap = $email;
            
            // Mật khẩu mặc định = 1111
            $matKhauMacDinh = "1111";

            // 5. Thêm vào bảng taikhoan (để đăng nhập)
            $sqlInsertTaiKhoan = "
                INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                VALUES (:maTaiKhoan, :tenDangNhap, :matKhau, :email, :soDienThoai, 'ACTIVE', :maTruong)
            ";
            
            $stmt = $this->conn->prepare($sqlInsertTaiKhoan);
            $stmt->execute([
                'maTaiKhoan' => $maTaiKhoan,
                'tenDangNhap' => $tenDangNhap,
                'matKhau' => $matKhauMacDinh,
                'email' => $email,
                'soDienThoai' => $soDienThoai,
                'maTruong' => $maTruong
            ]);

            // 6. Thêm vào bảng nhanvienphonggiaovu (admin là nhân viên phòng giáo vụ)
            $sqlInsertNVGV = "
                INSERT INTO nhanvienphonggiaovu (
                    maNVGiaoVu, 
                    hoTen, 
                    chucDanh, 
                    email, 
                    soDienThoai, 
                    trangThai, 
                    maTruong, 
                    maTaiKhoan,
                    ngayTao
                ) VALUES (
                    :maNVGiaoVu,
                    :hoTen,
                    'Nhan vien phong giao vu',
                    :email,
                    :soDienThoai,
                    'ACTIVE',
                    :maTruong,
                    :maTaiKhoan,
                    NOW()
                )
            ";
            
            $stmt = $this->conn->prepare($sqlInsertNVGV);
            $stmt->execute([
                'maNVGiaoVu' => $maNVGiaoVu,
                'hoTen' => $truongInfo['hoTenNV'],
                'email' => $email,
                'soDienThoai' => $soDienThoai,
                'maTruong' => $maTruong,
                'maTaiKhoan' => $maTaiKhoan
            ]);

            // 7. Thêm vai trò cho tài khoản (vai trò admin - tài khoản trường)
            // Kiểm tra vai trò 'admin' có tồn tại chưa
            $sqlCheckVaiTro = "SELECT maVaiTro FROM vaitro WHERE maVaiTro = 'admin'";
            $stmtCheck = $this->conn->prepare($sqlCheckVaiTro);
            $stmtCheck->execute();
            
            if (!$stmtCheck->fetch()) {
                // Tạo vai trò mới nếu chưa có
                $sqlInsertVaiTro = "INSERT INTO vaitro (maVaiTro, tenVaiTro) VALUES ('admin', 'Quản trị viên trường')";
                $this->conn->prepare($sqlInsertVaiTro)->execute();
            }

            // Gán vai trò cho tài khoản
            $sqlInsertTaiKhoanVaiTro = "
                INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro)
                VALUES (:maTaiKhoan, 'admin')
            ";
            $stmt = $this->conn->prepare($sqlInsertTaiKhoanVaiTro);
            $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);

            // 8. Commit transaction
            $this->conn->commit();

            // 9. Gửi email (sẽ được xử lý ở controller)
            return [
                'success' => true,
                'message' => 'Tạo tài khoản thành công',
                'data' => [
                    'maTaiKhoan' => $maTaiKhoan,
                    'maNVGiaoVu' => $maNVGiaoVu,
                    'maTruong' => $maTruong,
                    'tenTruong' => $truongInfo['tenTruong'],
                    'tenDangNhap' => $tenDangNhap,
                    'matKhau' => $matKhauMacDinh,
                    'email' => $email
                ]
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
     * Xóa tài khoản của trường (hủy cấp tài khoản)
     * LƯU Ý: Chỉ xóa tài khoản có vai trò 'admin' (tài khoản trường), không ảnh hưởng tài khoản giáo viên
     * @param string $maTruong Mã trường
     * @return array Kết quả
     */
    public function xoaTaiKhoanTruong($maTruong) {
        try {
            $this->conn->beginTransaction();

            // Lấy mã tài khoản trường (có vai trò 'admin')
            $sqlGetAccount = "SELECT tk.maTaiKhoan 
                             FROM taikhoan tk
                             INNER JOIN taikhoan_vaitro tkv ON tk.maTaiKhoan = tkv.maTaiKhoan
                             WHERE tk.maTruong = :maTruong AND tkv.maVaiTro = 'admin'
                             LIMIT 1";
            $stmt = $this->conn->prepare($sqlGetAccount);
            $stmt->execute(['maTruong' => $maTruong]);
            $account = $stmt->fetch();

            if (!$account) {
                throw new Exception('Không tìm thấy tài khoản trường');
            }

            $maTaiKhoan = $account['maTaiKhoan'];

            // Xóa vai trò
            $sqlDeleteVaiTro = "DELETE FROM taikhoan_vaitro WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->conn->prepare($sqlDeleteVaiTro);
            $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);
            
            // Xóa từ bảng nhanvienphonggiaovu
            $sqlDeleteNVGV = "DELETE FROM nhanvienphonggiaovu WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->conn->prepare($sqlDeleteNVGV);
            $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);

            // Xóa tài khoản
            $sqlDeleteTaiKhoan = "DELETE FROM taikhoan WHERE maTaiKhoan = :maTaiKhoan";
            $stmt = $this->conn->prepare($sqlDeleteTaiKhoan);
            $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Xóa tài khoản trường thành công'
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
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
}

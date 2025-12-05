<?php
require_once __DIR__ . '/../config/database.php';

class SchoolAccountModel {
    /** @var PDO */
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /* ==================== HELPER DB ==================== */

    private function fetchAll(string $sql, array $params = []): array {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOne(string $sql, array $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }

    private function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }

    /* ==================== QUERY ĐƠN GIẢN ==================== */

    public function getDanhSachTruongVaTrangThaiTaiKhoan(): array {
        $sql = "SELECT * FROM viewTrangThaiTaiKhoanTruong ORDER BY maTruong";
        return $this->fetchAll($sql);
    }

    public function getThongTinTruong(string $maTruong): ?array {
        $sql = "SELECT * FROM viewTrangThaiTaiKhoanTruong WHERE maTruong = :maTruong";
        $result = $this->fetchOne($sql, ['maTruong' => $maTruong]);
        return $result ?: null;
    }

    /**
     * True nếu đã có tài khoản trường (vai trò admin)
     */
    public function kiemTraDaCoTaiKhoan(string $maTruong): bool {
        $sql = "SELECT COUNT(*) AS total
                FROM taikhoan tk
                INNER JOIN taikhoan_vaitro tkv ON tk.maTaiKhoan = tkv.maTaiKhoan
                WHERE tk.maTruong = :maTruong AND tkv.maVaiTro = 'admin'";
        $result = $this->fetchOne($sql, ['maTruong' => $maTruong]);

        return $result && (int)$result['total'] > 0;
    }

    /* ==================== TẠO MÃ ==================== */

    private function taoMaTaiKhoanSo(): string {
        try {
            $sql = "SELECT maTaiKhoan FROM nhanvienphonggiaovu
                    WHERE maTaiKhoan LIKE 'TKGVU%'
                    ORDER BY maTaiKhoan DESC LIMIT 1";
            $result = $this->fetchOne($sql);

            if ($result) {
                $lastNumber = (int)substr($result['maTaiKhoan'], 5); // sau TKGVU
                $newNumber  = $lastNumber + 1;
            } else {
                $newNumber  = 1;
            }

            return 'TKGVU' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return 'TKGVU' . rand(100, 999);
        }
    }

    /**
     * TRXXXNVYYZZZZ
     */
    private function taoMaNhanVienGiaoVu(string $maTruong): string {
        try {
            $namCap  = date('y');
            $pattern = $maTruong . 'NV' . $namCap . '%';

            $sql = "SELECT maNVGiaoVu FROM nhanvienphonggiaovu
                    WHERE maNVGiaoVu LIKE :pattern
                    ORDER BY maNVGiaoVu DESC LIMIT 1";
            $result = $this->fetchOne($sql, ['pattern' => $pattern]);

            if ($result) {
                $lastNumber = (int)substr($result['maNVGiaoVu'], -4);
                $newNumber  = $lastNumber + 1;
            } else {
                $newNumber  = 1;
            }

            return $maTruong . 'NV' . $namCap . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return $maTruong . 'NV' . date('y') . rand(1000, 9999);
        }
    }

    /* ==================== BUSINESS LOGIC ==================== */

    public function taoTaiKhoanChoTruong(string $maTruong, string $maNhanVienSo): array {
        try {
            $this->conn->beginTransaction();

            // 1. Thông tin trường
            $truongInfo = $this->getThongTinTruong($maTruong);
            if (!$truongInfo) {
                throw new Exception("Không tìm thấy thông tin trường");
            }

            if (empty($truongInfo['email'])) {
                throw new Exception("Thiếu email trường. Vui lòng bổ sung trước khi cấp tài khoản.");
            }

            if ($truongInfo['trangThaiCapTaiKhoan'] === 'Đã cấp') {
                throw new Exception("Trường này đã được cấp tài khoản");
            }

            // 2. Sinh mã & thông tin đăng nhập
            $maTaiKhoan    = $this->taoMaTaiKhoanSo();
            $maNVGiaoVu    = $this->taoMaNhanVienGiaoVu($maTruong);
            $email         = $truongInfo['email'];
            $soDienThoai   = $truongInfo['soDienThoai'];
            $tenDangNhap   = $email;
            $matKhauMacDinh = '1111';

            // 3. Insert taikhoan
            $sql = "INSERT INTO taikhoan
                        (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                    VALUES
                        (:maTaiKhoan, :tenDangNhap, :matKhau, :email, :soDienThoai, 'ACTIVE', :maTruong)";
            $this->execute($sql, [
                'maTaiKhoan'  => $maTaiKhoan,
                'tenDangNhap' => $tenDangNhap,
                'matKhau'     => $matKhauMacDinh,
                'email'       => $email,
                'soDienThoai' => $soDienThoai,
                'maTruong'    => $maTruong,
            ]);

            // 4. Insert nhanvienphonggiaovu
            $sql = "INSERT INTO nhanvienphonggiaovu (
                        maNVGiaoVu, hoTen, chucDanh, email, soDienThoai,
                        trangThai, maTruong, maTaiKhoan, ngayTao
                    ) VALUES (
                        :maNVGiaoVu, :hoTen, 'Nhan vien phong giao vu', :email, :soDienThoai,
                        'ACTIVE', :maTruong, :maTaiKhoan, NOW()
                    )";
            $this->execute($sql, [
                'maNVGiaoVu' => $maNVGiaoVu,
                'hoTen'      => $truongInfo['hoTenNV'],
                'email'      => $email,
                'soDienThoai'=> $soDienThoai,
                'maTruong'   => $maTruong,
                'maTaiKhoan' => $maTaiKhoan,
            ]);

            // 5. Đảm bảo có vai trò admin
            $checkAdmin = $this->fetchOne("SELECT maVaiTro FROM vaitro WHERE maVaiTro = 'admin'");
            if (!$checkAdmin) {
                $this->execute(
                    "INSERT INTO vaitro (maVaiTro, tenVaiTro) VALUES ('admin', 'Quản trị viên trường')"
                );
            }

            // 6. Gán vai trò
            $this->execute(
                "INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) VALUES (:maTaiKhoan, 'admin')",
                ['maTaiKhoan' => $maTaiKhoan]
            );

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Tạo tài khoản thành công',
                'data'    => [
                    'maTaiKhoan'  => $maTaiKhoan,
                    'maNVGiaoVu'  => $maNVGiaoVu,
                    'maTruong'    => $maTruong,
                    'tenTruong'   => $truongInfo['tenTruong'],
                    'tenDangNhap' => $tenDangNhap,
                    'matKhau'     => $matKhauMacDinh,
                    'email'       => $email,
                ],
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function xoaTaiKhoanTruong(string $maTruong): array {
        try {
            $this->conn->beginTransaction();

            // Tìm tài khoản admin của trường
            $sql = "SELECT tk.maTaiKhoan
                    FROM taikhoan tk
                    INNER JOIN taikhoan_vaitro tkv ON tk.maTaiKhoan = tkv.maTaiKhoan
                    WHERE tk.maTruong = :maTruong AND tkv.maVaiTro = 'admin'
                    LIMIT 1";
            $account = $this->fetchOne($sql, ['maTruong' => $maTruong]);

            if (!$account) {
                throw new Exception('Không tìm thấy tài khoản trường');
            }

            $maTaiKhoan = $account['maTaiKhoan'];

            // Xóa vai trò, nhân viên, tài khoản
            $this->execute(
                "DELETE FROM taikhoan_vaitro WHERE maTaiKhoan = :maTaiKhoan",
                ['maTaiKhoan' => $maTaiKhoan]
            );
            $this->execute(
                "DELETE FROM nhanvienphonggiaovu WHERE maTaiKhoan = :maTaiKhoan",
                ['maTaiKhoan' => $maTaiKhoan]
            );
            $this->execute(
                "DELETE FROM taikhoan WHERE maTaiKhoan = :maTaiKhoan",
                ['maTaiKhoan' => $maTaiKhoan]
            );

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Xóa tài khoản trường thành công',
            ];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }

    public function guiEmailThongTinTaiKhoan(array $accountData): bool {
        try {
            $to       = $accountData['email'];
            $subject  = "Thông tin tài khoản đăng nhập - Hệ thống quản lý giáo dục";

            $message = <<<HTML
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
<div class="container">
    <div class="header">
        <h2>Thông tin tài khoản đăng nhập</h2>
    </div>
    <div class="content">
        <p>Kính gửi: <strong>{$accountData['tenTruong']}</strong></p>
        <p>Hệ thống quản lý giáo dục đã cấp tài khoản đăng nhập cho trường. Dưới đây là thông tin chi tiết:</p>

        <div class="info-box">
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
    <div class="footer">
        <p>Email tự động - Vui lòng không trả lời</p>
        <p>&copy; 2025 Hệ thống quản lý giáo dục</p>
    </div>
</div>
</body>
</html>
HTML;

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@edu.vn\r\n";

            if (getenv('APP_ENV') === 'development') {
                error_log("Email would be sent to: $to");
                error_log("Subject: $subject");
                error_log("Content: " . strip_tags($message));
                return true;
            }

            return mail($to, $subject, $message, $headers);
        } catch (Exception $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }
}

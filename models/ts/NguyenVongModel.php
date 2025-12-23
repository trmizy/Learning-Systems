<?php
require_once __DIR__ . '/../../config/database.php';

class NguyenVongModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /* ================== Helpers ================== */

    private function fetchAll(string $sql, array $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchOne(string $sql, array $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function exec(string $sql, array $params = []): bool {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /* ================== Lấy thông tin thí sinh ================== */

    /**
     * Lấy thông tin thí sinh từ username
     */
    public function getThongTinThiSinhByUsername(string $username) {
        try {
            $sql = "SELECT 
                        ts.maThiSinh,
                        ts.hoTen,
                        ts.ngaySinh,
                        ts.gioiTinh,
                        ts.soCCCD,
                        ts.soDienThoai
                    FROM taikhoan tk
                    INNER JOIN thisinh ts ON tk.maTaiKhoan = ts.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            return $this->fetchOne($sql, [$username]);
        } catch (PDOException $e) {
            error_log("Error getThongTinThiSinhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /* ================== Trường THPT ================== */

    /**
     * Lấy thông tin chi tiết 1 trường
     */
    public function getThongTinTruong(string $maTruong) {
        try {
            $sql = "SELECT * FROM truong WHERE maTruong = ? LIMIT 1";
            return $this->fetchOne($sql, [$maTruong]);
        } catch (PDOException $e) {
            error_log("Error getThongTinTruong: " . $e->getMessage());
            return null;
        }
    }

    /* ================== Validate ================== */

    /**
     * Kiểm tra thông tin thí sinh hợp lệ
     */
    public function validateThiSinh(array $data): array {
        if (empty($data['hoTen']) || mb_strlen($data['hoTen']) < 3) {
            return ['valid' => false, 'message' => 'Họ tên phải có ít nhất 3 ký tự'];
        }

        if (empty($data['soCCCD']) || !preg_match('/^\d{12}$/', $data['soCCCD'])) {
            return ['valid' => false, 'message' => 'Số CCCD phải có 12 chữ số'];
        }

        if (empty($data['soDienThoai']) || !preg_match('/^0\d{9}$/', $data['soDienThoai'])) {
            return ['valid' => false, 'message' => 'Số điện thoại không hợp lệ (10 số, bắt đầu bằng 0)'];
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email không hợp lệ'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Kiểm tra nguyện vọng hợp lệ
     */
    public function validateNguyenVong(array $nguyenVong): array {
        $soLuong = count($nguyenVong);

        if ($soLuong === 0) {
            return ['valid' => false, 'message' => 'Vui lòng chọn trường'];
        }

        if ($soLuong > 1) {
            return ['valid' => false, 'message' => 'Mỗi lần chỉ đăng ký 1 nguyện vọng'];
        }

        $nv = $nguyenVong[0];

        // Kiểm tra mã trường
        if (empty($nv['maTruong'])) {
            return ['valid' => false, 'message' => 'Vui lòng chọn trường'];
        }

        // Kiểm tra thứ tự ưu tiên
        $thuTu = (int)($nv['thuTuUuTien'] ?? 0);
        if ($thuTu < 1 || $thuTu > 3) {
            return ['valid' => false, 'message' => 'Thứ tự ưu tiên phải từ 1 đến 3'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /* ================== Mã tự động ================== */

    /**
     * Tạo mã nguyện vọng tự động
     */
    private function taoMaNguyenVong(): string {
        try {
            $sql = "SELECT maNguyenVong FROM nguyenvong ORDER BY maNguyenVong DESC LIMIT 1";
            $result = $this->fetchOne($sql);

            if ($result) {
                $lastNumber = (int)substr($result['maNguyenVong'], 2);
                $newNumber = $lastNumber + 1;
                return 'NV' . str_pad((string)$newNumber, 8, '0', STR_PAD_LEFT);
            }

            return 'NV00000001';
        } catch (PDOException $e) {
            error_log("Error taoMaNguyenVong: " . $e->getMessage());
            return 'NV' . time();
        }
    }

    /* ================== CRUD Nguyện vọng ================== */

    /**
     * Kiểm tra thí sinh đã đăng ký nguyện vọng chưa
     */
    public function kiemTraDaDangKy(string $maThiSinh): bool {
        try {
            $sql = "SELECT COUNT(*) as total FROM nguyenvong WHERE maThiSinh = ?";
            $result = $this->fetchOne($sql, [$maThiSinh]);
            return (int)($result['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error kiemTraDaDangKy: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra nguyện vọng được phép đăng ký tiếp theo - BỎ LOGIC DUYỆT
     * @param string $maThiSinh
     * @return array ['allowed' => bool, 'nextPriority' => int, 'message' => string]
     */
    public function kiemTraNguyenVongTiepTheo(string $maThiSinh): array {
        try {
            // Lấy tất cả nguyện vọng hiện có
            $sql = "SELECT thuTuUuTien, trangThai 
                    FROM nguyenvong 
                    WHERE maThiSinh = ? 
                    ORDER BY thuTuUuTien ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $danhSach = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Chưa có nguyện vọng nào → Được đăng ký NV1
            if (empty($danhSach)) {
                return [
                    'allowed' => true,
                    'nextPriority' => 1,
                    'message' => 'Bạn có thể đăng ký nguyện vọng 1'
                ];
            }
            
            // Tìm nguyện vọng cao nhất
            $maxPriority = 0;
            foreach ($danhSach as $nv) {
                $priority = (int)$nv['thuTuUuTien'];
                if ($priority > $maxPriority) {
                    $maxPriority = $priority;
                }
            }
            
            // Đã đủ 3 nguyện vọng
            if ($maxPriority >= 3) {
                return [
                    'allowed' => false,
                    'nextPriority' => 0,
                    'message' => 'Bạn đã đăng ký đủ 3 nguyện vọng'
                ];
            }
            
            // ⚠️ BỎ KIỂM TRA TRẠNG THÁI DUYỆT - Cho phép đăng ký tiếp ngay
            $nextPriority = $maxPriority + 1;
            return [
                'allowed' => true,
                'nextPriority' => $nextPriority,
                'message' => "Bạn có thể đăng ký nguyện vọng $nextPriority"
            ];
            
        } catch (PDOException $e) {
            error_log("Error kiemTraNguyenVongTiepTheo: " . $e->getMessage());
            return [
                'allowed' => false,
                'nextPriority' => 0,
                'message' => 'Lỗi hệ thống khi kiểm tra nguyện vọng'
            ];
        }
    }

    /**
     * Lấy danh sách nguyện vọng của thí sinh - BỔ SUNG TRẠNG THÁI
     */
    public function getDanhSachNguyenVong(string $maThiSinh): array {
        try {
            $sql = "SELECT 
                        nv.maNguyenVong,
                        nv.thuTuUuTien,
                        nv.maTruong,
                        nv.trangThai,
                        nv.ngayDangKy,
                        t.tenTruong,
                        t.diaChi as diaChiTruong,
                        t.email as emailTruong,
                        t.soDienThoai as sdtTruong
                    FROM nguyenvong nv
                    INNER JOIN truong t ON nv.maTruong = t.maTruong
                    WHERE nv.maThiSinh = ?
                    ORDER BY nv.thuTuUuTien";
            
            return $this->fetchAll($sql, [$maThiSinh]);
        } catch (PDOException $e) {
            error_log("Error getDanhSachNguyenVong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đăng ký nguyện vọng mới - ⚠️ ĐÃ XÓA CODE TẠO HỒ SƠ
     */
    public function dangKyNguyenVong(string $maThiSinh, array $danhSachNguyenVong): array {
        try {
            $this->conn->beginTransaction();

            // 1. Kiểm tra quyền đăng ký nguyện vọng tiếp theo
            $checkNext = $this->kiemTraNguyenVongTiepTheo($maThiSinh);
            
            if (!$checkNext['allowed']) {
                throw new Exception($checkNext['message']);
            }
            
            $nextPriority = $checkNext['nextPriority'];

            // 2. CHỈ CHO PHÉP ĐĂNG KÝ 1 NGUYỆN VỌNG
            if (count($danhSachNguyenVong) !== 1) {
                throw new Exception("Mỗi lần chỉ được đăng ký 1 nguyện vọng. Hiện tại bạn đang ở nguyện vọng $nextPriority");
            }
            
            $nguyenVong = $danhSachNguyenVong[0];

            // 3. Validate thứ tự ưu tiên khớp
            if ((int)$nguyenVong['thuTuUuTien'] !== $nextPriority) {
                throw new Exception("Bạn chỉ có thể đăng ký nguyện vọng $nextPriority. Vui lòng chọn đúng thứ tự.");
            }

            // ⚠️ 4. KIỂM TRA TRÙNG TRƯỜNG
            $kiemTraTrung = $this->kiemTraTrungTruong($maThiSinh, $nguyenVong['maTruong']);
            if (!$kiemTraTrung['valid']) {
                throw new Exception($kiemTraTrung['message']);
            }

            // 5. Validate dữ liệu nguyện vọng
            $validateNV = $this->validateNguyenVong([$nguyenVong]);
            if (!$validateNV['valid']) {
                throw new Exception($validateNV['message']);
            }

            // 6. Thêm nguyện vọng MỚI với trạng thái CHO_DUYET
            $sqlInsert = "INSERT INTO nguyenvong (
                            maNguyenVong, 
                            maThiSinh, 
                            maTruong, 
                            thuTuUuTien, 
                            trangThai, 
                            ngayDangKy
                          ) VALUES (?, ?, ?, ?, 'CHO_DUYET', NOW())";
            
            $this->exec($sqlInsert, [
                $this->taoMaNguyenVong(),
                $maThiSinh,
                $nguyenVong['maTruong'],
                $nextPriority
            ]);

            $this->conn->commit();

            // Thông báo
            $message = "Đăng ký nguyện vọng $nextPriority thành công!";
            if ($nextPriority < 3) {
                $message .= " Bạn có thể tiếp tục đăng ký nguyện vọng " . ($nextPriority + 1) . ".";
            } else {
                $message .= " Bạn đã hoàn tất đăng ký 3 nguyện vọng.";
            }

            return [
                'success' => true,
                'message' => $message,
                'data' => $this->getDanhSachNguyenVong($maThiSinh)
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * ⚠️ MỚI: Kiểm tra trùng trường
     */
    private function kiemTraTrungTruong(string $maThiSinh, string $maTruong): array {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM nguyenvong
                    WHERE maThiSinh = ? AND maTruong = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maThiSinh, $maTruong]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return [
                    'valid' => false,
                    'message' => 'Bạn đã chọn trường này ở nguyện vọng trước. Vui lòng chọn trường khác.'
                ];
            }
            
            return ['valid' => true, 'message' => ''];
            
        } catch (PDOException $e) {
            error_log("Error kiemTraTrungTruong: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Lỗi hệ thống khi kiểm tra trùng trường'
            ];
        }
    }

    /**
     * ⚠️ MỚI: Lấy danh sách mã trường đã chọn
     */
    public function getDanhSachTruongDaChon(string $maThiSinh): array {
        try {
            $sql = "SELECT maTruong 
                    FROM nguyenvong 
                    WHERE maThiSinh = ?
                    ORDER BY thuTuUuTien";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $result ?: [];
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachTruongDaChon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách trường - ⚠️ PHIÊN BẢN MỚI (Có loại trừ)
     */
    public function getDanhSachTruong(string $maThiSinh = null, array $excludeMaTruong = []): array {
        try {
            $sql = "SELECT maTruong, tenTruong, diaChi 
                    FROM truong 
                    WHERE trangThai = 'ACTIVE'";
            
            // ⚠️ Loại trừ trường đã chọn
            if (!empty($excludeMaTruong)) {
                $placeholders = str_repeat('?,', count($excludeMaTruong) - 1) . '?';
                $sql .= " AND maTruong NOT IN ($placeholders)";
            }
            
            $sql .= " ORDER BY tenTruong";
            
            $stmt = $this->conn->prepare($sql);
            
            if (!empty($excludeMaTruong)) {
                $stmt->execute($excludeMaTruong);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HỦY nguyện vọng - CHỈ CHO PHÉP HỦY NGUYỆN VỌNG CUỐI CÙNG
     */
    public function huyDangKyNguyenVong(string $maThiSinh): array {
        try {
            $this->conn->beginTransaction();

            // Lấy nguyện vọng có thứ tự cao nhất
            $sql = "SELECT maNguyenVong, thuTuUuTien, trangThai 
                    FROM nguyenvong 
                    WHERE maThiSinh = ? 
                    ORDER BY thuTuUuTien DESC 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$maThiSinh]);
            $lastNV = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastNV) {
                throw new Exception('Không tìm thấy nguyện vọng nào để hủy');
            }

            // ⚠️ BỎ KIỂM TRA TRẠNG THÁI - Cho phép hủy bất kỳ nguyện vọng nào
            
            // Xóa nguyện vọng cuối
            $sqlDelete = "DELETE FROM nguyenvong WHERE maNguyenVong = ?";
            $this->exec($sqlDelete, [$lastNV['maNguyenVong']]);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => "Đã hủy nguyện vọng {$lastNV['thuTuUuTien']} thành công"
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật thông tin thí sinh
     */
    public function capNhatThongTinThiSinh(string $maThiSinh, array $data): bool {
        try {
            $sql = "UPDATE thisinh 
                    SET hoTen = ?, 
                        soDienThoai = ?, 
                    WHERE maThiSinh = ?";
            
            return $this->exec($sql, [
                $data['hoTen'],
                $data['soDienThoai'],
                $maThiSinh
            ]);
        } catch (PDOException $e) {
            error_log("Error capNhatThongTinThiSinh: " . $e->getMessage());
            return false;
        }
    }
}

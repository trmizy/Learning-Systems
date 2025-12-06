<?php
require_once __DIR__ . '/../../config/database.php';

class WishRegistrationModel {
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
        return $stmt->fetchAll();
    }

    private function fetchOne(string $sql, array $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    private function exec(string $sql, array $params = []): bool {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /* ================== Trường ================== */

    /**
     * Lấy danh sách tất cả các trường
     * @return array
     */
    public function getDanhSachTruong(): array {
        try {
            $sql = "SELECT maTruong, tenTruong, diaChi, email, soDienThoai 
                    FROM truong 
                    ORDER BY tenTruong";
            return $this->fetchAll($sql);
        } catch (PDOException $e) {
            error_log("Error in getDanhSachTruong: " . $e->getMessage());
            return [];
        }
    }

    /* ================== Validate ================== */

    /**
     * Kiểm tra thông tin thí sinh hợp lệ
     * @param array $data
     * @return array ['valid' => bool, 'message' => string]
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

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Kiểm tra nguyện vọng hợp lệ
     * @param array $nguyenVong
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateNguyenVong(array $nguyenVong): array {
        $soLuong = count($nguyenVong);

        if ($soLuong === 0) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất 1 nguyện vọng'];
        }

        if ($soLuong > 3) {
            return ['valid' => false, 'message' => 'Số lượng nguyện vọng đã vượt quá tối đa 3 nguyện vọng'];
        }

        $danhSachMaTruong = [];
        $danhSachThuTu    = [];

        foreach ($nguyenVong as $nv) {
            // Trường
            if (in_array($nv['maTruong'], $danhSachMaTruong, true)) {
                return ['valid' => false, 'message' => 'Nguyện vọng đã được đăng ký (trùng trường)'];
            }
            $danhSachMaTruong[] = $nv['maTruong'];

            // Thứ tự ưu tiên
            $thuTu = (int)($nv['thuTuUuTien'] ?? 0);
            if ($thuTu < 1 || $thuTu > 3) {
                return ['valid' => false, 'message' => 'Thứ tự ưu tiên phải từ 1 đến 3'];
            }

            if (in_array($thuTu, $danhSachThuTu, true)) {
                return ['valid' => false, 'message' => 'Thứ tự ưu tiên bị trùng'];
            }
            $danhSachThuTu[] = $thuTu;
        }

        return ['valid' => true, 'message' => ''];
    }

    /* ================== Thí sinh & Mã ================== */

    /**
     * Kiểm tra thí sinh đã tồn tại chưa (theo CCCD)
     */
    public function kiemTraThiSinhTonTai(string $soCCCD) {
        try {
            $sql = "SELECT * FROM thisinh WHERE soCCCD = :soCCCD LIMIT 1";
            return $this->fetchOne($sql, ['soCCCD' => $soCCCD]);
        } catch (PDOException $e) {
            error_log("Error in kiemTraThiSinhTonTai: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo mã thí sinh tự động theo quy ước TSYYZZZZZZXX
     */
    private function taoMaThiSinh(int $maVung = null): string {
        try {
            $namThi = date('y'); // 2 chữ số cuối
            if ($maVung === null) {
                $maVung = random_int(11, 99);
            }

            do {
                $soNgauNhien = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $maThiSinh   = 'TS' . $namThi . $soNgauNhien . $maVung;

                $sqlCheck  = "SELECT COUNT(*) AS count FROM thisinh WHERE maThiSinh = :maThiSinh";
                $result    = $this->fetchOne($sqlCheck, ['maThiSinh' => $maThiSinh]);
                $exists    = (int)($result['count'] ?? 0);
            } while ($exists > 0);

            return $maThiSinh;
        } catch (PDOException $e) {
            error_log("Error in taoMaThiSinh: " . $e->getMessage());
            return 'TS' . date('y') . time() . random_int(11, 99);
        }
    }

    /**
     * Tạo mã nguyện vọng tự động
     */
    private function taoMaNguyenVong(): string {
        try {
            $sql    = "SELECT maNguyenVong FROM nguyenvong ORDER BY maNguyenVong DESC LIMIT 1";
            $result = $this->fetchOne($sql);

            if ($result) {
                $lastNumber = (int)substr($result['maNguyenVong'], 2);
                $newNumber  = $lastNumber + 1;
                return 'NV' . str_pad((string)$newNumber, 6, '0', STR_PAD_LEFT);
            }

            return 'NV000001';
        } catch (PDOException $e) {
            error_log("Error in taoMaNguyenVong: " . $e->getMessage());
            return 'NV' . time();
        }
    }

    /* ================== Đăng ký nguyện vọng ================== */

    /**
     * Đăng ký nguyện vọng
     */
    public function dangKyNguyenVong(array $dataThiSinh, array $danhSachNguyenVong): array {
        try {
            $this->conn->beginTransaction();

            // 1. Validate
            $validateTS = $this->validateThiSinh($dataThiSinh);
            if (!$validateTS['valid']) {
                throw new Exception($validateTS['message']);
            }

            $validateNV = $this->validateNguyenVong($danhSachNguyenVong);
            if (!$validateNV['valid']) {
                throw new Exception($validateNV['message']);
            }

            // 2. Thí sinh
            $thiSinhTonTai = $this->kiemTraThiSinhTonTai($dataThiSinh['soCCCD']);

            if ($thiSinhTonTai) {
                $maThiSinh = $thiSinhTonTai['maThiSinh'];

                $sqlUpdate = "UPDATE thisinh 
                              SET hoTen = :hoTen, soDienThoai = :soDienThoai
                              WHERE maThiSinh = :maThiSinh";
                $this->exec($sqlUpdate, [
                    'hoTen'      => $dataThiSinh['hoTen'],
                    'soDienThoai'=> $dataThiSinh['soDienThoai'],
                    'maThiSinh'  => $maThiSinh
                ]);

                $sqlDeleteOld = "DELETE FROM nguyenvong WHERE maThiSinh = :maThiSinh";
                $this->exec($sqlDeleteOld, ['maThiSinh' => $maThiSinh]);
            } else {
                $maThiSinh = $this->taoMaThiSinh();

                $sqlInsert = "INSERT INTO thisinh (maThiSinh, hoTen, soCCCD, diem, soDienThoai)
                              VALUES (:maThiSinh, :hoTen, :soCCCD, NULL, :soDienThoai)";
                $this->exec($sqlInsert, [
                    'maThiSinh'   => $maThiSinh,
                    'hoTen'       => $dataThiSinh['hoTen'],
                    'soCCCD'      => $dataThiSinh['soCCCD'],
                    'soDienThoai' => $dataThiSinh['soDienThoai']
                ]);
            }

            // 3. Thêm nguyện vọng
            $sqlInsertNV = "INSERT INTO nguyenvong (maNguyenVong, thuTuUuTien, soLuong, maThiSinh, maTruong)
                            VALUES (:maNguyenVong, :thuTuUuTien, 1, :maThiSinh, :maTruong)";
            
            foreach ($danhSachNguyenVong as $nv) {
                $this->exec($sqlInsertNV, [
                    'maNguyenVong' => $this->taoMaNguyenVong(),
                    'thuTuUuTien'  => $nv['thuTuUuTien'],
                    'maThiSinh'    => $maThiSinh,
                    'maTruong'     => $nv['maTruong']
                ]);
            }

            $this->conn->commit();

            $nguyenVongDaDangKy = $this->getDanhSachNguyenVong($maThiSinh);

            return [
                'success' => true,
                'message' => 'Đăng ký nguyện vọng thành công',
                'data' => [
                    'maThiSinh'    => $maThiSinh,
                    'hoTen'        => $dataThiSinh['hoTen'],
                    'soCCCD'       => $dataThiSinh['soCCCD'],
                    'soDienThoai'  => $dataThiSinh['soDienThoai'],
                    'soNguyenVong' => count($danhSachNguyenVong),
                    'nguyenVong'   => $nguyenVongDaDangKy
                ]
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        }
    }

    /**
     * Lấy danh sách nguyện vọng của thí sinh
     */
    public function getDanhSachNguyenVong(string $maThiSinh): array {
        try {
            $sql = "SELECT nv.*, t.tenTruong, t.diaChi
                    FROM nguyenvong nv
                    INNER JOIN truong t ON nv.maTruong = t.maTruong
                    WHERE nv.maThiSinh = :maThiSinh
                    ORDER BY nv.thuTuUuTien";
            return $this->fetchAll($sql, ['maThiSinh' => $maThiSinh]);
        } catch (PDOException $e) {
            error_log("Error in getDanhSachNguyenVong: " . $e->getMessage());
            return [];
        }
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class WishRegistrationModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Lấy danh sách tất cả các trường
     * @return array Danh sách trường
     */
    public function getDanhSachTruong() {
        try {
            $sql = "SELECT maTruong, tenTruong, diaChi, email, soDienThoai 
                    FROM truong 
                    ORDER BY tenTruong";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra thông tin thí sinh hợp lệ
     * @param array $data Dữ liệu thí sinh
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateThiSinh($data) {
        // Kiểm tra họ tên
        if (empty($data['hoTen']) || strlen($data['hoTen']) < 3) {
            return ['valid' => false, 'message' => 'Họ tên phải có ít nhất 3 ký tự'];
        }

        // Kiểm tra số CCCD (12 số)
        if (empty($data['soCCCD']) || !preg_match('/^\d{12}$/', $data['soCCCD'])) {
            return ['valid' => false, 'message' => 'Số CCCD phải có 12 chữ số'];
        }

        // Kiểm tra số điện thoại (10 số, bắt đầu bằng 0)
        if (empty($data['soDienThoai']) || !preg_match('/^0\d{9}$/', $data['soDienThoai'])) {
            return ['valid' => false, 'message' => 'Số điện thoại không hợp lệ (10 số, bắt đầu bằng 0)'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Kiểm tra nguyện vọng hợp lệ
     * @param array $nguyenVong Danh sách nguyện vọng
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateNguyenVong($nguyenVong) {
        // Kiểm tra số lượng nguyện vọng (tối đa 3)
        if (count($nguyenVong) > 3) {
            return ['valid' => false, 'message' => 'Số lượng nguyện vọng đã vượt quá tối đa 3 nguyện vọng'];
        }

        if (count($nguyenVong) == 0) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất 1 nguyện vọng'];
        }

        // Kiểm tra trùng nguyện vọng
        $danhSachMaTruong = [];
        foreach ($nguyenVong as $nv) {
            if (in_array($nv['maTruong'], $danhSachMaTruong)) {
                return ['valid' => false, 'message' => 'Nguyện vọng đã được đăng ký (trùng trường)'];
            }
            $danhSachMaTruong[] = $nv['maTruong'];
        }

        // Kiểm tra thứ tự ưu tiên (1, 2, 3)
        $danhSachThuTu = [];
        foreach ($nguyenVong as $nv) {
            if (empty($nv['thuTuUuTien']) || $nv['thuTuUuTien'] < 1 || $nv['thuTuUuTien'] > 3) {
                return ['valid' => false, 'message' => 'Thứ tự ưu tiên phải từ 1 đến 3'];
            }
            if (in_array($nv['thuTuUuTien'], $danhSachThuTu)) {
                return ['valid' => false, 'message' => 'Thứ tự ưu tiên bị trùng'];
            }
            $danhSachThuTu[] = $nv['thuTuUuTien'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Kiểm tra thí sinh đã tồn tại chưa (theo CCCD)
     * @param string $soCCCD Số CCCD
     * @return array|null Thông tin thí sinh nếu có
     */
    public function kiemTraThiSinhTonTai($soCCCD) {
        try {
            $sql = "SELECT * FROM thisinh WHERE soCCCD = :soCCCD LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['soCCCD' => $soCCCD]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in kiemTraThiSinhTonTai: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo mã thí sinh tự động theo quy ước TSYYZZZZZZXX
     * YY: Năm thi (2 chữ số cuối)
     * ZZZZZZ: Số ngẫu nhiên (6 chữ số)
     * XX: Mã vùng cấp 2 (11-99)
     * @param int $maVung Mã vùng (mặc định 11-99 ngẫu nhiên)
     * @return string Mã thí sinh mới
     */
    private function taoMaThiSinh($maVung = null) {
        try {
            // YY: 2 chữ số cuối của năm hiện tại
            $namThi = date('y'); // Ví dụ: 2025 -> 25
            
            // ZZZZZZ: Số ngẫu nhiên 6 chữ số (000000-999999)
            $soNgauNhien = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // XX: Mã vùng cấp 2 (11-99)
            if ($maVung === null) {
                $maVung = rand(11, 99);
            }
            
            // Kiểm tra trùng mã (rất hiếm nhưng cần kiểm tra)
            $maThiSinh = 'TS' . $namThi . $soNgauNhien . $maVung;
            
            // Kiểm tra mã đã tồn tại chưa
            $sqlCheck = "SELECT COUNT(*) as count FROM thisinh WHERE maThiSinh = :maThiSinh";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->execute(['maThiSinh' => $maThiSinh]);
            $exists = $stmtCheck->fetch()['count'];
            
            // Nếu trùng, tạo lại với số ngẫu nhiên khác
            if ($exists > 0) {
                return $this->taoMaThiSinh($maVung);
            }
            
            return $maThiSinh;
            
        } catch (PDOException $e) {
            error_log("Error in taoMaThiSinh: " . $e->getMessage());
            // Fallback với timestamp
            return 'TS' . date('y') . time() . rand(11, 99);
        }
    }

    /**
     * Tạo mã nguyện vọng tự động
     * @return string Mã nguyện vọng mới
     */
    private function taoMaNguyenVong() {
        try {
            $sql = "SELECT maNguyenVong FROM nguyenvong ORDER BY maNguyenVong DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result) {
                $lastNumber = intval(substr($result['maNguyenVong'], 2));
                $newNumber = $lastNumber + 1;
                return 'NV' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            } else {
                return 'NV000001';
            }
        } catch (PDOException $e) {
            error_log("Error in taoMaNguyenVong: " . $e->getMessage());
            return 'NV' . time();
        }
    }

    /**
     * Đăng ký nguyện vọng
     * @param array $dataThiSinh Dữ liệu thí sinh
     * @param array $danhSachNguyenVong Danh sách nguyện vọng
     * @return array Kết quả
     */
    public function dangKyNguyenVong($dataThiSinh, $danhSachNguyenVong) {
        try {
            $this->conn->beginTransaction();

            // 1. Validate thí sinh
            $validateTS = $this->validateThiSinh($dataThiSinh);
            if (!$validateTS['valid']) {
                throw new Exception($validateTS['message']);
            }

            // 2. Validate nguyện vọng
            $validateNV = $this->validateNguyenVong($danhSachNguyenVong);
            if (!$validateNV['valid']) {
                throw new Exception($validateNV['message']);
            }

            // 3. Kiểm tra thí sinh đã tồn tại chưa
            $thiSinhTonTai = $this->kiemTraThiSinhTonTai($dataThiSinh['soCCCD']);
            
            if ($thiSinhTonTai) {
                $maThiSinh = $thiSinhTonTai['maThiSinh'];
                
                // Cập nhật thông tin thí sinh (không cập nhật điểm vì chưa thi)
                $sqlUpdate = "UPDATE thisinh 
                             SET hoTen = :hoTen, 
                                 soDienThoai = :soDienThoai
                             WHERE maThiSinh = :maThiSinh";
                $stmt = $this->conn->prepare($sqlUpdate);
                $stmt->execute([
                    'hoTen' => $dataThiSinh['hoTen'],
                    'soDienThoai' => $dataThiSinh['soDienThoai'],
                    'maThiSinh' => $maThiSinh
                ]);

                // Xóa các nguyện vọng cũ
                $sqlDeleteOld = "DELETE FROM nguyenvong WHERE maThiSinh = :maThiSinh";
                $stmt = $this->conn->prepare($sqlDeleteOld);
                $stmt->execute(['maThiSinh' => $maThiSinh]);
            } else {
                // Tạo mới thí sinh (điểm sẽ được cập nhật sau khi thi)
                $maThiSinh = $this->taoMaThiSinh();
                
                $sqlInsert = "INSERT INTO thisinh (maThiSinh, hoTen, soCCCD, diem, soDienThoai)
                             VALUES (:maThiSinh, :hoTen, :soCCCD, NULL, :soDienThoai)";
                $stmt = $this->conn->prepare($sqlInsert);
                $stmt->execute([
                    'maThiSinh' => $maThiSinh,
                    'hoTen' => $dataThiSinh['hoTen'],
                    'soCCCD' => $dataThiSinh['soCCCD'],
                    'soDienThoai' => $dataThiSinh['soDienThoai']
                ]);
            }

            // 4. Thêm các nguyện vọng mới
            $sqlInsertNV = "INSERT INTO nguyenvong (maNguyenVong, thuTuUuTien, soLuong, maThiSinh, maTruong)
                           VALUES (:maNguyenVong, :thuTuUuTien, 1, :maThiSinh, :maTruong)";
            
            foreach ($danhSachNguyenVong as $nv) {
                $maNguyenVong = $this->taoMaNguyenVong();
                $stmt = $this->conn->prepare($sqlInsertNV);
                $stmt->execute([
                    'maNguyenVong' => $maNguyenVong,
                    'thuTuUuTien' => $nv['thuTuUuTien'],
                    'maThiSinh' => $maThiSinh,
                    'maTruong' => $nv['maTruong']
                ]);
            }

            $this->conn->commit();

            // Lấy danh sách nguyện vọng đã đăng ký với thông tin trường
            $nguyenVongDaDangKy = $this->getDanhSachNguyenVong($maThiSinh);

            return [
                'success' => true,
                'message' => 'Đăng ký nguyện vọng thành công',
                'data' => [
                    'maThiSinh' => $maThiSinh,
                    'hoTen' => $dataThiSinh['hoTen'],
                    'soCCCD' => $dataThiSinh['soCCCD'],
                    'soDienThoai' => $dataThiSinh['soDienThoai'],
                    'soNguyenVong' => count($danhSachNguyenVong),
                    'nguyenVong' => $nguyenVongDaDangKy
                ]
            ];

        } catch (Exception $e) {
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
     * Lấy danh sách nguyện vọng của thí sinh
     * @param string $maThiSinh Mã thí sinh
     * @return array Danh sách nguyện vọng
     */
    public function getDanhSachNguyenVong($maThiSinh) {
        try {
            $sql = "SELECT nv.*, t.tenTruong, t.diaChi
                    FROM nguyenvong nv
                    INNER JOIN truong t ON nv.maTruong = t.maTruong
                    WHERE nv.maThiSinh = :maThiSinh
                    ORDER BY nv.thuTuUuTien";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['maThiSinh' => $maThiSinh]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachNguyenVong: " . $e->getMessage());
            return [];
        }
    }
}

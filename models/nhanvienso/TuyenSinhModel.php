<?php
require_once __DIR__ . '/../../config/database.php';

class TuyenSinhModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Thêm thí sinh - FIX: Bỏ cột ngayTao
     */
    public function themThiSinh($data) {
        try {
            // BẮT ĐẦU TRANSACTION
            $this->db->beginTransaction();
            
            $maThiSinh = $data['maThiSinh'];
            
            // ====== BƯỚC 1: TẠO TÀI KHOẢN ======
            // Tạo mã tài khoản duy nhất: TK + 11 ký tự cuối của mã thí sinh
            $maTaiKhoan = 'TK' . substr($data['maThiSinh'], -11);
            
            // ⚠️ FIX: Bỏ cột ngayTao vì không có trong schema
            $sqlTaiKhoan = "INSERT INTO taikhoan (
                                maTaiKhoan,
                                tenDangNhap,
                                matKhau,
                                email,
                                soDienThoai,
                                trangThai
                            ) VALUES (
                                :maTaiKhoan,
                                :tenDangNhap,
                                :matKhau,
                                :email,
                                :soDienThoai,
                                'ACTIVE'
                            )";
            
            $stmtTaiKhoan = $this->db->prepare($sqlTaiKhoan);
            $resultTaiKhoan = $stmtTaiKhoan->execute([
                ':maTaiKhoan' => $maTaiKhoan,
                ':tenDangNhap' => $data['soCCCD'], // Username = CCCD
                ':matKhau' => '1111', // ⚠️ Mật khẩu mặc định không mã hóa
                ':email' => '',
                ':soDienThoai' => $data['soDienThoai']
            ]);
            
            if (!$resultTaiKhoan) {
                throw new Exception("Không thể tạo tài khoản");
            }
            
            // ====== BƯỚC 2: GÁN VAI TRÒ THÍ SINH ======
            $sqlVaiTro = "INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) 
                          VALUES (:maTaiKhoan, 'ts')";
            
            $stmtVaiTro = $this->db->prepare($sqlVaiTro);
            $resultVaiTro = $stmtVaiTro->execute([':maTaiKhoan' => $maTaiKhoan]);
            
            if (!$resultVaiTro) {
                throw new Exception("Không thể gán vai trò");
            }
            
            // ====== BƯỚC 3: TẠO THÍ SINH ======
            $sqlThiSinh = "INSERT INTO thisinh (
                                maThiSinh,
                                maTaiKhoan,
                                hoTen, 
                                soCCCD, 
                                ngaySinh,
                                gioiTinh,
                                diemVan,
                                diemToan,
                                diemAnh,
                                diem,
                                soDienThoai, 
                                noiSinh,
                                namTuyenSinh
                            ) VALUES (
                                :maThiSinh,
                                :maTaiKhoan,
                                :hoTen, 
                                :soCCCD, 
                                :ngaySinh,
                                :gioiTinh,
                                :diemVan,
                                :diemToan,
                                :diemAnh,
                                :diem,
                                :soDienThoai, 
                                :noiSinh,
                                :namTuyenSinh
                            )";
            
            $stmtThiSinh = $this->db->prepare($sqlThiSinh);
            $resultThiSinh = $stmtThiSinh->execute([
                ':maThiSinh' => $maThiSinh,
                ':maTaiKhoan' => $maTaiKhoan,
                ':hoTen' => $data['hoTen'],
                ':soCCCD' => $data['soCCCD'],
                ':ngaySinh' => $data['ngaySinh'],
                ':gioiTinh' => $data['gioiTinh'],
                ':diemVan' => $data['diemVan'],
                ':diemToan' => $data['diemToan'],
                ':diemAnh' => $data['diemAnh'],
                ':diem' => $data['diem'],
                ':soDienThoai' => $data['soDienThoai'],
                ':noiSinh' => $data['noiSinh'],
                ':namTuyenSinh' => $data['namTuyenSinh']
            ]);
            
            if (!$resultThiSinh) {
                throw new Exception("Không thể tạo thông tin thí sinh");
            }
            
            // ====== COMMIT TRANSACTION ======
            $this->db->commit();
            
            // DEBUG LOG
            error_log("✅ Tạo thành công:");
            error_log("  - Thí sinh: $maThiSinh");
            error_log("  - Tài khoản: $maTaiKhoan (Username: {$data['soCCCD']}, Pass: 123456)");
            error_log("  - Điểm: Văn={$data['diemVan']}, Toán={$data['diemToan']}, Anh={$data['diemAnh']}");
            
            return [
                'success' => true,
                'message' => "Thêm thí sinh thành công! Mã: $maThiSinh | Tài khoản: {$data['soCCCD']} | Mật khẩu: 123456",
                'maTaiKhoan' => $maTaiKhoan,
                'maThiSinh' => $maThiSinh
            ];
            
        } catch (PDOException $e) {
            // ROLLBACK nếu có lỗi
            $this->db->rollBack();
            
            $errorMsg = $e->getMessage();
            error_log("❌ Error themThiSinh (PDO): " . $errorMsg);
            
            // Xử lý lỗi duplicate key
            if ($e->getCode() == '23000') {
                if (strpos($errorMsg, 'soCCCD') !== false) {
                    return ['success' => false, 'message' => 'Số CCCD đã tồn tại trong hệ thống'];
                } elseif (strpos($errorMsg, 'maThiSinh') !== false) {
                    return ['success' => false, 'message' => 'Mã thí sinh đã tồn tại'];
                } elseif (strpos($errorMsg, 'maTaiKhoan') !== false) {
                    return ['success' => false, 'message' => 'Mã tài khoản đã tồn tại'];
                }
                return ['success' => false, 'message' => 'Dữ liệu đã tồn tại trong hệ thống'];
            }
            
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $errorMsg];
            
        } catch (Exception $e) {
            // ROLLBACK nếu có lỗi
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log("❌ Error themThiSinh (Exception): " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Lấy danh sách trường - FIX: BỎ trangThai vì không có trong schema
     */
    public function getDanhSachTruong() {
        try {
            // BỎ điều kiện WHERE trangThai vì bảng truong không có cột này
            $sql = "SELECT maTruong, tenTruong FROM truong ORDER BY tenTruong";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getDanhSachTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy mã trường theo tên - GIỮ LẠI ĐỂ TƯƠNG LAI MỞ RỘNG
     */
    public function getMaTruongByTen($tenTruong) {
        try {
            $sql = "SELECT maTruong FROM truong WHERE tenTruong LIKE :ten LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ten' => "%$tenTruong%"]);
            $result = $stmt->fetch();
            return $result ? $result['maTruong'] : null;
        } catch (PDOException $e) {
            error_log("Error getMaTruongByTen: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách thí sinh - FIX: BỎ JOIN truong vì không có maTruong
     */
    public function getDanhSachThiSinh($namTuyenSinh, $search = '') {
        try {
            // BỎ maTruong vì không có trong bảng thisinh
            $sql = "SELECT 
                        ts.maThiSinh,
                        ts.soCCCD,
                        ts.hoTen,
                        ts.ngaySinh,
                        ts.diem,
                        ts.soDienThoai,
                        ts.noiSinh
                    FROM thisinh ts
                    WHERE 1=1"; // Giữ WHERE để filter sau
            
            if (!empty($search)) {
                $sql .= " AND (ts.soCCCD LIKE :search OR ts.hoTen LIKE :search)";
            }
            
            $sql .= " ORDER BY ts.diem DESC, ts.soCCCD";
            
            $stmt = $this->db->prepare($sql);
            $params = [];
            
            if (!empty($search)) {
                $params['search'] = "%$search%";
            }
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachThiSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Thống kê tuyển sinh - FIX: Xử lý NULL
     */
    public function getThongKeTuyenSinh($namTuyenSinh = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as tongThiSinh,
                        AVG(diem) as diemTrungBinh,
                        MAX(diem) as diemCaoNhat,
                        MIN(diem) as diemThapNhat
                    FROM thisinh";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // FIX: Đảm bảo không trả về NULL
            return [
                'tongThiSinh' => (int)($result['tongThiSinh'] ?? 0),
                'diemTrungBinh' => (float)($result['diemTrungBinh'] ?? 0),
                'diemCaoNhat' => (float)($result['diemCaoNhat'] ?? 0),
                'diemThapNhat' => (float)($result['diemThapNhat'] ?? 0)
            ];
            
        } catch (PDOException $e) {
            error_log("Error getThongKeTuyenSinh: " . $e->getMessage());
            return [
                'tongThiSinh' => 0,
                'diemTrungBinh' => 0,
                'diemCaoNhat' => 0,
                'diemThapNhat' => 0
            ];
        }
    }
}

<?php
require_once __DIR__ . '/../../config/database.php';

class DiemChuanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách điểm chuẩn theo trường
     */
    public function getDanhSachDiemChuan($maTruong) {
        try {
            $sql = "SELECT 
                        dc.maDiemChuan,
                        dc.soDiem,
                        dc.namTuyenSinh,
                        tr.tenTruong
                    FROM diemchuan dc
                    INNER JOIN truong tr ON dc.maTruong = tr.maTruong
                    WHERE dc.maTruong = ?
                    ORDER BY dc.namTuyenSinh DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachDiemChuan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy mã trường từ BGH username
     */
    public function getMaTruongByBGH($username) {
        try {
            $sql = "SELECT bgh.maTruong
                    FROM taikhoan tk
                    INNER JOIN bangiamhieu bgh ON tk.maTaiKhoan = bgh.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['maTruong'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaTruongByBGH: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Thêm điểm chuẩn mới
     */
    public function themDiemChuan($data) {
        try {
            // Tạo mã điểm chuẩn tự động: DC + maTruong + namTuyenSinh
            $maDiemChuan = 'DC' . $data['maTruong'] . $data['namTuyenSinh'];
            
            $sql = "INSERT INTO diemchuan (maDiemChuan, soDiem, maTruong, namTuyenSinh)
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $maDiemChuan,
                $data['soDiem'],
                $data['maTruong'],
                $data['namTuyenSinh']
            ]);
            
            return ['success' => true, 'message' => 'Thêm điểm chuẩn thành công'];
            
        } catch (PDOException $e) {
            error_log("Error themDiemChuan: " . $e->getMessage());
            
            // Kiểm tra lỗi trùng khóa
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Điểm chuẩn năm ' . $data['namTuyenSinh'] . ' đã tồn tại'];
            }
            
            return ['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()];
        }
    }

    /**
     * Cập nhật điểm chuẩn
     */
    public function capNhatDiemChuan($maDiemChuan, $soDiem) {
        try {
            $sql = "UPDATE diemchuan 
                    SET soDiem = ?
                    WHERE maDiemChuan = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$soDiem, $maDiemChuan]);
            
            return ['success' => true, 'message' => 'Cập nhật điểm chuẩn thành công'];
            
        } catch (PDOException $e) {
            error_log("Error capNhatDiemChuan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()];
        }
    }

    /**
     * Xóa điểm chuẩn
     */
    public function xoaDiemChuan($maDiemChuan) {
        try {
            $sql = "DELETE FROM diemchuan WHERE maDiemChuan = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDiemChuan]);
            
            return ['success' => true, 'message' => 'Xóa điểm chuẩn thành công'];
            
        } catch (PDOException $e) {
            error_log("Error xoaDiemChuan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()];
        }
    }

    /**
     * Kiểm tra điểm chuẩn đã tồn tại chưa
     */
    public function kiemTraTonTai($maTruong, $namTuyenSinh) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM diemchuan 
                    WHERE maTruong = ? AND namTuyenSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong, $namTuyenSinh]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error kiemTraTonTai: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy chi tiết điểm chuẩn
     */
    public function getChiTietDiemChuan($maDiemChuan) {
        try {
            $sql = "SELECT 
                        dc.maDiemChuan,
                        dc.soDiem,
                        dc.namTuyenSinh,
                        dc.maTruong,
                        tr.tenTruong
                    FROM diemchuan dc
                    INNER JOIN truong tr ON dc.maTruong = tr.maTruong
                    WHERE dc.maDiemChuan = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDiemChuan]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getChiTietDiemChuan: " . $e->getMessage());
            return null;
        }
    }
}

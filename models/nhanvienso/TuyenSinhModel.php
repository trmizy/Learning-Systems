<?php
require_once __DIR__ . '/../../config/database.php';

class TuyenSinhModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Thêm thí sinh - FIX: Thêm cột gioiTinh
     */
    public function themThiSinh($data) {
        try {
            $maThiSinh = $data['maThiSinh'];
            
            $sql = "INSERT INTO thisinh (
                        maThiSinh,
                        hoTen, 
                        soCCCD, 
                        ngaySinh,
                        gioiTinh,
                        diem, 
                        soDienThoai, 
                        noiSinh,
                        namTuyenSinh
                    ) VALUES (
                        :maThiSinh,
                        :hoTen, 
                        :soCCCD, 
                        :ngaySinh,
                        :gioiTinh,
                        :diem, 
                        :soDienThoai, 
                        :noiSinh,
                        :namTuyenSinh
                    )";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':maThiSinh' => $maThiSinh,
                ':hoTen' => $data['hoTen'],
                ':soCCCD' => $data['soCCCD'],
                ':ngaySinh' => $data['ngaySinh'],
                ':gioiTinh' => $data['gioiTinh'], // ⚠️ THÊM MỚI
                ':diem' => $data['diem'],
                ':soDienThoai' => $data['soDienThoai'],
                ':noiSinh' => $data['noiSinh'],
                ':namTuyenSinh' => $data['namTuyenSinh']
            ]);
            
            // DEBUG LOG
            if ($result) {
                error_log("✅ Insert thành công: maThiSinh=$maThiSinh, hoTen={$data['hoTen']}, gioiTinh={$data['gioiTinh']}, ngaySinh={$data['ngaySinh']}");
            } else {
                error_log("❌ Insert thất bại: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error themThiSinh: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                error_log("❌ Mã thí sinh {$data['maThiSinh']} đã tồn tại!");
            }
            
            return false;
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

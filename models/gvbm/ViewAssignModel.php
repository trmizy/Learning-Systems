<?php
require_once __DIR__ . '/../../config/database.php';

class ViewAssignModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy mã GV từ username
     */
    public function getMaGVByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT gv.maGV
                FROM taikhoan tk
                INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaGVByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách phân công ra đề của giáo viên
     */
    public function getPhanCongRaDe($maGV) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    hocKy,
                    kyThi,
                    soLuongDe,
                    thoiHan,
                    ghiChu
                FROM bangphancongrade
                WHERE maGV = ?
                ORDER BY thoiHan DESC
            ");
            $stmt->execute([$maGV]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getPhanCongRaDe: " . $e->getMessage());
            return [];
        }
    }
}

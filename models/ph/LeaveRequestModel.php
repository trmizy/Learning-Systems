<?php
require_once __DIR__ . '/../../config/database.php';

class LeaveRequestModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy mã phụ huynh từ username
     */
    public function getMaPhuHuynhByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT ph.maPH
                FROM taikhoan tk
                INNER JOIN phuhuynh ph ON tk.maTaiKhoan = ph.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maPH'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaPhuHuynhByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách con của phụ huynh
     */
    public function getDanhSachConCuaPhuHuynh($maPH) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        lh.tenLop
                    FROM phuhuynh_hocsinh ph_hs
                    INNER JOIN hocsinh hs ON ph_hs.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE ph_hs.maPH = ?
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPH]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachConCuaPhuHuynh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tạo đơn xin nghỉ mới
     */
    public function createLeaveRequest($data) {
        try {
            // Tạo mã đơn xin phép tự động
            $maDonXinPhep = $this->generateLeaveRequestId();

            $sql = "INSERT INTO donxinphep (maDonXinPhep, ngay, soBuoi, lyDo, minhChungKemTheo, trangThai, maHS, maPH)
                    VALUES (?, ?, ?, ?, ?, 'Cho duyet', ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $maDonXinPhep,
                $data['ngay'],
                $data['soBuoi'],
                $data['lyDo'],
                $data['minhChungKemTheo'],
                $data['maHS'],
                $data['maPH']
            ]);

            return $result;
            
        } catch (PDOException $e) {
            error_log("Error createLeaveRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo mã đơn xin phép tự động
     */
    private function generateLeaveRequestId() {
        try {
            $stmt = $this->db->query("SELECT maDonXinPhep FROM donxinphep ORDER BY maDonXinPhep DESC LIMIT 1");
            $result = $stmt->fetch();
            
            if ($result) {
                // Lấy số cuối cùng và tăng lên 1
                $lastId = $result['maDonXinPhep'];
                $number = intval(substr($lastId, 3)) + 1;
                return 'DXP' . str_pad($number, 3, '0', STR_PAD_LEFT);
            } else {
                return 'DXP001';
            }
            
        } catch (PDOException $e) {
            error_log("Error generateLeaveRequestId: " . $e->getMessage());
            return 'DXP' . uniqid();
        }
    }

    /**
     * Lấy danh sách đơn xin nghỉ theo học sinh
     */
    public function getLeaveRequestsByStudent($maHS) {
        try {
            // ⚠️ FIX: Khớp với schema - bảng donxinphep có cột ngay (không có chữ H)
            $sql = "SELECT 
                        dxp.maDonXinPhep,
                        dxp.ngay,
                        dxp.soBuoi,
                        dxp.lyDo,
                        dxp.minhChungKemTheo,
                        dxp.trangThai,
                        dxp.maHS,
                        hs.hoTen as tenHocSinh,
                        lh.tenLop
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE dxp.maHS = ?
                    ORDER BY dxp.ngay DESC";
            
            // DEBUG LOG
            error_log("=== getLeaveRequestsByStudent ===");
            error_log("SQL: $sql");
            error_log("maHS: $maHS");
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Rows returned: " . count($result));
            if (count($result) > 0) {
                error_log("First row: " . print_r($result[0], true));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getLeaveRequestsByStudent: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return [];
        }
    }

    /**
     * Lấy thông tin đơn xin nghỉ theo ID
     */
    public function getLeaveRequestById($maDonXinPhep) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM donxinphep WHERE maDonXinPhep = ? LIMIT 1");
            $stmt->execute([$maDonXinPhep]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getLeaveRequestById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hủy đơn xin nghỉ
     */
    public function cancelLeaveRequest($maDonXinPhep) {
        try {
            $stmt = $this->db->prepare("UPDATE donxinphep SET trangThai = 'Da huy' WHERE maDonXinPhep = ?");
            return $stmt->execute([$maDonXinPhep]);
            
        } catch (PDOException $e) {
            error_log("Error cancelLeaveRequest: " . $e->getMessage());
            return false;
        }
    }
}

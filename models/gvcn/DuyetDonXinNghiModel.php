<?php
require_once __DIR__ . '/../../config/database.php';

class DuyetDonXinNghiModel {
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
     * Lấy lớp chủ nhiệm
     */
    public function getLopChuNhiem($maGV) {
        try {
            $sql = "SELECT lh.maLop, lh.tenLop, lh.namHoc
                    FROM giaovienchunhiem gvcn
                    INNER JOIN lophoc lh ON gvcn.lop = lh.maLop
                    WHERE gvcn.maGV = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getLopChuNhiem: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách đơn chờ phê duyệt
     */
    public function getDonChoPheDuyet($maLop) {
        try {
            $sql = "SELECT 
                        dxp.maDonXinPhep,
                        dxp.ngay,
                        dxp.soBuoi,
                        dxp.lyDo,
                        dxp.minhChungKemTheo,
                        dxp.trangThai,
                        hs.maHS,
                        hs.hoTen as tenHocSinh,
                        ph.hoTen as tenPhuHuynh,
                        ph.soDienThoai as sdtPhuHuynh
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    INNER JOIN phuhuynh ph ON dxp.maPH = ph.maPH
                    WHERE hs.maLop = ? AND dxp.trangThai = 'Cho duyet'
                    ORDER BY dxp.ngay DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDonChoPheDuyet: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy đơn theo trạng thái
     */
    public function getDonTheoTrangThai($maLop, $trangThai = 'all') {
        try {
            $sql = "SELECT 
                        dxp.maDonXinPhep,
                        dxp.ngay,
                        dxp.soBuoi,
                        dxp.lyDo,
                        dxp.minhChungKemTheo,
                        dxp.trangThai,
                        hs.maHS,
                        hs.hoTen as tenHocSinh,
                        ph.hoTen as tenPhuHuynh
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    INNER JOIN phuhuynh ph ON dxp.maPH = ph.maPH
                    WHERE hs.maLop = ?";
            
            if ($trangThai !== 'all') {
                $sql .= " AND dxp.trangThai = ?";
            }
            
            $sql .= " ORDER BY dxp.ngay DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($trangThai !== 'all') {
                $stmt->execute([$maLop, $trangThai]);
            } else {
                $stmt->execute([$maLop]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDonTheoTrangThai: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin đơn
     */
    public function getThongTinDon($maDonXinPhep) {
        try {
            $sql = "SELECT 
                        dxp.*,
                        hs.hoTen as tenHocSinh,
                        hs.maLop,
                        ph.hoTen as tenPhuHuynh,
                        ph.soDienThoai as sdtPhuHuynh,
                        lh.tenLop
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    INNER JOIN phuhuynh ph ON dxp.maPH = ph.maPH
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE dxp.maDonXinPhep = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDonXinPhep]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinDon: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy thông tin đơn chi tiết (có thêm hạnh kiểm)
     */
    public function getThongTinDonChiTiet($maDonXinPhep) {
        $don = $this->getThongTinDon($maDonXinPhep);
        
        if ($don) {
            // Lấy thông tin hạnh kiểm hiện tại
            $hanhKiem = $this->getHanhKiemHienTai($don['maHS'], '2024-2025', 'HK1');
            $don['hanhKiem'] = $hanhKiem;
        }
        
        return $don;
    }

    /**
     * Cập nhật trạng thái đơn
     */
    public function capNhatTrangThaiDon($maDonXinPhep, $trangThai, $ghiChu = '') {
        try {
            // Cập nhật bảng donxinphep - thêm cột ghiChu nếu cần
            $sql = "UPDATE donxinphep 
                    SET trangThai = ?
                    WHERE maDonXinPhep = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$trangThai, $maDonXinPhep]);
            
        } catch (PDOException $e) {
            error_log("Error capNhatTrangThaiDon: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật hạnh kiểm - Tăng số buổi nghỉ có phép
     */
    public function capNhatHanhKiem($maHS, $namHoc, $hocKy, $soBuoiNghi) {
        try {
            // Kiểm tra xem đã có bản ghi hạnh kiểm chưa
            $checkSql = "SELECT maHanhKiem, soBuoiNghiCoPhep 
                        FROM hanhkiem 
                        WHERE maHS = ? AND namHoc = ? AND hocKy = ?
                        LIMIT 1";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$maHS, $namHoc, $hocKy]);
            $hanhKiem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($hanhKiem) {
                // Cập nhật số buổi nghỉ có phép
                $soNghiMoi = ($hanhKiem['soBuoiNghiCoPhep'] ?? 0) + $soBuoiNghi;
                
                $updateSql = "UPDATE hanhkiem 
                             SET soBuoiNghiCoPhep = ? 
                             WHERE maHanhKiem = ?";
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$soNghiMoi, $hanhKiem['maHanhKiem']]);
                
            } else {
                // Tạo mới bản ghi hạnh kiểm
                $maHanhKiem = $this->generateMaHanhKiem();
                
                $insertSql = "INSERT INTO hanhkiem 
                             (maHanhKiem, maHS, namHoc, hocKy, soBuoiNghiCoPhep, loaiHanhKiem)
                             VALUES (?, ?, ?, ?, ?, 'Tốt')";
                
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute([$maHanhKiem, $maHS, $namHoc, $hocKy, $soBuoiNghi]);
            }

            return true;
            
        } catch (PDOException $e) {
            error_log("Error capNhatHanhKiem: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy hạnh kiểm hiện tại
     */
    public function getHanhKiemHienTai($maHS, $namHoc, $hocKy) {
        try {
            $stmt = $this->db->prepare("
                SELECT soBuoiNghiCoPhep, soBuoiNghiKhongCoPhep, loaiHanhKiem
                FROM hanhkiem
                WHERE maHS = ? AND namHoc = ? AND hocKy = ?
                LIMIT 1
            ");
            $stmt->execute([$maHS, $namHoc, $hocKy]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getHanhKiemHienTai: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate mã hạnh kiểm
     */
    private function generateMaHanhKiem() {
        try {
            $stmt = $this->db->query("SELECT maHanhKiem FROM hanhkiem ORDER BY maHanhKiem DESC LIMIT 1");
            $result = $stmt->fetch();
            
            if ($result) {
                $lastId = $result['maHanhKiem'];
                // Lấy phần số từ HK_220330 -> 220330
                preg_match('/\d+/', $lastId, $matches);
                if (!empty($matches)) {
                    $number = intval($matches[0]) + 1;
                    return 'HK_' . $number;
                }
            }
            
            return 'HK_' . time();
            
        } catch (PDOException $e) {
            error_log("Error generateMaHanhKiem: " . $e->getMessage());
            return 'HK_' . uniqid();
        }
    }

    /**
     * Transaction methods
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollBack();
    }

    /**
     * Đếm số đơn chờ duyệt
     */
    public function demDonChoPheDuyet($maLop) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    WHERE hs.maLop = ? AND dxp.trangThai = 'Cho duyet'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['total'] : 0;
            
        } catch (PDOException $e) {
            error_log("Error demDonChoPheDuyet: " . $e->getMessage());
            return 0;
        }
    }
}

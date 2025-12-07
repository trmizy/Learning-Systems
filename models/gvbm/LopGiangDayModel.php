<?php
require_once __DIR__ . '/../../config/database.php';

class LopGiangDayModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy mã giáo viên từ username
     */
    public function getMaGiaoVienByUsername($username) {
        try {
            $sql = "SELECT gv.maGV
                    FROM taikhoan tk
                    INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
                    WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maGV'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaGiaoVienByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách lớp mà giáo viên đang giảng dạy (GVBM/TTBM)
     */
    public function getDanhSachLopGiangDay($maGV, $namHoc = '2024-2025', $hocKy = 'HK1') {
        try {
            $sql = "SELECT DISTINCT
                        lh.maLop,
                        lh.tenLop,
                        lh.khoi,
                        lh.siSo,
                        mh.tenMon,
                        mh.maMonHoc,
                        gvcn.hoTen as tenGVCN
                    FROM phanconggiangday pc
                    INNER JOIN lophoc lh ON pc.maLop = lh.maLop
                    INNER JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                    LEFT JOIN giaovienchunhiem gvcn_lop ON lh.maLop = gvcn_lop.lop
                    LEFT JOIN giaovienbomon gvcn ON gvcn_lop.maGV = gvcn.maGV
                    WHERE pc.maGV = ? 
                      AND pc.namHoc = ?
                      AND pc.hocKy = ?
                    ORDER BY lh.khoi, lh.tenLop, mh.tenMon";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maGV, $namHoc, $hocKy]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachLopGiangDay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách lớp chủ nhiệm (GVCN)
     */
    public function getLopChuNhiem($maGV) {
        try {
            $sql = "SELECT 
                        lh.maLop,
                        lh.tenLop,
                        lh.khoi,
                        lh.siSo,
                        lh.namHoc
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
     * Lấy danh sách học sinh trong lớp
     */
    public function getDanhSachHocSinhTheoLop($maLop) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        hs.gioiTinh,
                        hs.ngaySinh,
                        hs.email,
                        hs.sdt,
                        hs.trangThai
                    FROM hocsinh hs
                    WHERE hs.maLop = ? AND hs.trangThai = 'DANGHOC'
                    ORDER BY hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachHocSinhTheoLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin chi tiết học sinh
     */
    public function getChiTietHocSinh($maHS) {
        try {
            $sql = "SELECT 
                        hs.*,
                        lh.tenLop,
                        lh.khoi,
                        ph.hoTen as tenPhuHuynh,
                        ph.soDienThoai as sdtPhuHuynh,
                        ph.email as emailPhuHuynh
                    FROM hocsinh hs
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    LEFT JOIN phuhuynh_hocsinh ph_hs ON hs.maHS = ph_hs.maHS
                    LEFT JOIN phuhuynh ph ON ph_hs.maPH = ph.maPH
                    WHERE hs.maHS = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getChiTietHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy thống kê điểm trung bình của học sinh
     */
    public function getDiemTrungBinhHocSinh($maHS, $maMonHoc = null, $hocKy = 'HK1', $namHoc = '2024-2025') {
        try {
            $sql = "SELECT 
                        AVG((bd.diemThuongXuyen + bd.diemGiuaKy + bd.diemCuoiKy * 2) / 4) as diemTB
                    FROM bangdiem bd
                    WHERE bd.maHS = ? 
                      AND bd.hocKy = ?
                      AND bd.namHoc = ?";
            
            $params = [$maHS, $hocKy, $namHoc];
            
            if ($maMonHoc) {
                $sql .= " AND bd.maMonHoc = ?";
                $params[] = $maMonHoc;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result && $result['diemTB'] !== null ? round($result['diemTB'], 2) : null;
            
        } catch (PDOException $e) {
            error_log("Error getDiemTrungBinhHocSinh: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Đếm số buổi vắng của học sinh (TODO: Khi có bảng diemdanh)
     */
    public function demSoBuoiVang($maHS, $maMonHoc = null) {
        // Tạm thời return 0 vì chưa có bảng diemdanh
        return 0;
    }
}

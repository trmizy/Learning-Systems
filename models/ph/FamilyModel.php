<?php
require_once __DIR__ . '/../../config/database.php';

class FamilyModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * ⚠️ CRITICAL FIX: Lấy danh sách con UNIQUE
     */
    public function getDanhSachConChiTiet($maPH) {
        try {
            // ⚠️ FIX 1: Thêm DISTINCT và GROUP BY maHS
            $sql = "SELECT DISTINCT
                        hs.maHS,
                        hs.hoTen,
                        hs.ngaySinh,
                        hs.gioiTinh,
                        hs.email,
                        hs.sdt,
                        hs.diaChi,
                        hs.trangThai,
                        lh.maLop,
                        lh.tenLop,
                        lh.khoi,
                        lh.namHoc
                    FROM phuhuynh_hocsinh ph_hs
                    INNER JOIN hocsinh hs ON ph_hs.maHS = hs.maHS
                    LEFT JOIN lophoc lh ON hs.maLop = lh.maLop
                    WHERE ph_hs.maPH = ?
                    GROUP BY hs.maHS
                    ORDER BY lh.khoi DESC, hs.hoTen";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPH]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // ⚠️ FIX 2: Lấy thông tin GVCN riêng để tránh duplicate do JOIN
            foreach ($result as &$con) {
                $thongTinGVCN = $this->getThongTinGVCN($con['maLop']);
                $con['tenGVCN'] = $thongTinGVCN['hoTen'] ?? 'Chưa có';
                $con['sdtGVCN'] = $thongTinGVCN['soDienThoai'] ?? 'Chưa có';
                $con['emailGVCN'] = $thongTinGVCN['email'] ?? 'Chưa có';
            }
            
            // DEBUG
            error_log("=== getDanhSachConChiTiet ===");
            error_log("maPH: $maPH | Rows: " . count($result));
            
            // ⚠️ FIX 3: Kiểm tra duplicate trong result
            $uniqueCheck = [];
            foreach ($result as $r) {
                $maHS = $r['maHS'];
                if (isset($uniqueCheck[$maHS])) {
                    error_log("⚠️ CẢNH BÁO: Query trả về DUPLICATE maHS=$maHS");
                }
                $uniqueCheck[$maHS] = true;
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachConChiTiet: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin GVCN riêng - Tránh duplicate
     */
    private function getThongTinGVCN($maLop) {
        if (!$maLop) return null;
        
        try {
            $sql = "SELECT 
                        gv.hoTen,
                        gv.soDienThoai,
                        gv.email
                    FROM giaovienchunhiem gvcn
                    INNER JOIN giaovienbomon gv ON gvcn.maGV = gv.maGV
                    WHERE gvcn.lop = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maLop]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongTinGVCN: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy thống kê con
     */
    public function getThongKeCon($maHS) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT bd.maMonHoc) as soMonHoc,
                        ROUND(AVG((bd.diemThuongXuyen + bd.diemGiuaKy + bd.diemCuoiKy * 2) / 4), 2) as diemTB
                    FROM bangdiem bd
                    WHERE bd.maHS = ? 
                      AND bd.namHoc = '2024-2025' 
                      AND bd.hocKy = 'HK1'
                    GROUP BY bd.maHS";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maHS]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Đếm đơn xin phép
            $stmtDon = $this->db->prepare("SELECT COUNT(*) as total FROM donxinphep WHERE maHS = ?");
            $stmtDon->execute([$maHS]);
            $soDonXinPhep = $stmtDon->fetch()['total'] ?? 0;
            
            return [
                'soMonHoc' => $result['soMonHoc'] ?? 0,
                'diemTB' => $result['diemTB'] ?? 0,
                'soDonXinPhep' => $soDonXinPhep
            ];
            
        } catch (PDOException $e) {
            error_log("Error getThongKeCon: " . $e->getMessage());
            return ['soMonHoc' => 0, 'diemTB' => 0, 'soDonXinPhep' => 0];
        }
    }
}

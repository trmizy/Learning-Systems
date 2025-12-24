<?php
require_once __DIR__ . '/../../config/database.php';

class XetTuyenModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kiểm tra điều kiện sẵn sàng xét tuyển
     */
    public function kiemTraSanSang() {
        $errors = [];
        
        try {
            // 1. Kiểm tra có điểm chuẩn không
            $stmtDC = $this->db->query("SELECT COUNT(*) FROM diemchuan");
            if ($stmtDC->fetchColumn() == 0) {
                $errors[] = 'Chưa nhập điểm chuẩn';
            }

            // 2. Kiểm tra có thí sinh nào có điểm thi không
            $stmtTS = $this->db->query("SELECT COUNT(*) FROM thisinh WHERE diem IS NOT NULL");
            if ($stmtTS->fetchColumn() == 0) {
                $errors[] = 'Chưa có thí sinh nào có điểm thi';
            }

            // 3. Kiểm tra có nguyện vọng nào không
            $stmtNV = $this->db->query("SELECT COUNT(*) FROM nguyenvong");
            if ($stmtNV->fetchColumn() == 0) {
                $errors[] = 'Chưa có nguyện vọng nào được đăng ký';
            }

            return [
                'ready' => empty($errors),
                'errors' => $errors
            ];
            
        } catch (PDOException $e) {
            error_log("Error kiemTraSanSang: " . $e->getMessage());
            return ['ready' => false, 'errors' => ['Lỗi kiểm tra: ' . $e->getMessage()]];
        }
    }

    /**
     * THUẬT TOÁN XÉT TUYỂN TOÀN BỘ
     */
    public function chayXetTuyenToanBo() {
        try {
            $this->db->beginTransaction();
            
            // BƯỚC 1: Reset trạng thái tất cả nguyện vọng về CHO_DUYET
            $this->db->exec("UPDATE nguyenvong SET trangThai = 'CHO_DUYET'");
            
            // BƯỚC 2: Lấy danh sách tất cả thí sinh có điểm
            $stmtTS = $this->db->query("
                SELECT maThiSinh, diem 
                FROM thisinh 
                WHERE diem IS NOT NULL
                ORDER BY diem DESC
            ");
            $danhSachThiSinh = $stmtTS->fetchAll(PDO::FETCH_ASSOC);
            
            $tongTrungTuyen = 0;
            $tongTruot = 0;
            
            // BƯỚC 3: Xét từng thí sinh
            foreach ($danhSachThiSinh as $thiSinh) {
                $ketQua = $this->xetTuyenMotThiSinh($thiSinh['maThiSinh'], $thiSinh['diem']);
                
                if ($ketQua == 'TRUNG_TUYEN') {
                    $tongTrungTuyen++;
                } else {
                    $tongTruot++;
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'tong_trung_tuyen' => $tongTrungTuyen,
                'tong_truot' => $tongTruot
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error chayXetTuyenToanBo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * XÉT TUYỂN 1 THÍ SINH (NV1 → NV2 → NV3)
     */
    private function xetTuyenMotThiSinh($maThiSinh, $diemThi) {
        // Lấy 3 nguyện vọng theo thứ tự ưu tiên
        $stmtNV = $this->db->prepare("
            SELECT nv.maNguyenVong, nv.maTruong, dc.soDiem
            FROM nguyenvong nv
            INNER JOIN diemchuan dc ON nv.maTruong = dc.maTruong
            WHERE nv.maThiSinh = ?
            ORDER BY nv.thuTuUuTien
            LIMIT 3
        ");
        $stmtNV->execute([$maThiSinh]);
        $nguyenVongList = $stmtNV->fetchAll(PDO::FETCH_ASSOC);
        
        // LOGIC XÉT TUYỂN
        foreach ($nguyenVongList as $nv) {
            $diemChuan = floatval($nv['soDiem']);
            
            if ($diemThi >= $diemChuan) {
                // ĐẬU NV này → Update ĐẬU và DỪNG
                $this->capNhatTrangThaiNguyenVong($nv['maNguyenVong'], 'DAU');
                return 'TRUNG_TUYEN';
            } else {
                // TRƯỢT NV này → Update TRƯỢT và XÉT NV TIẾP THEO
                $this->capNhatTrangThaiNguyenVong($nv['maNguyenVong'], 'TRUOT');
            }
        }
        
        // Hết 3 NV đều trượt
        return 'TRUOT';
    }

    /**
     * Cập nhật trạng thái nguyện vọng
     */
    private function capNhatTrangThaiNguyenVong($maNguyenVong, $trangThai) {
        $stmt = $this->db->prepare("UPDATE nguyenvong SET trangThai = ? WHERE maNguyenVong = ?");
        $stmt->execute([$trangThai, $maNguyenVong]);
    }

    /**
     * Lấy thống kê xét tuyển
     */
    public function getThongKeXetTuyen() {
        try {
            $stats = [
                'tong_thi_sinh' => 0,
                'tong_trung_tuyen' => 0,
                'tong_truot' => 0,
                'ty_le_trung_tuyen' => 0
            ];
            
            // Tổng thí sinh
            $stmt = $this->db->query("SELECT COUNT(*) FROM thisinh WHERE diem IS NOT NULL");
            $stats['tong_thi_sinh'] = (int)$stmt->fetchColumn();
            
            // Tổng trúng tuyển (nguyện vọng ĐẬU)
            $stmt = $this->db->query("SELECT COUNT(DISTINCT maThiSinh) FROM nguyenvong WHERE trangThai = 'DAU'");
            $stats['tong_trung_tuyen'] = (int)$stmt->fetchColumn();
            
            // Tổng trượt
            $stats['tong_truot'] = $stats['tong_thi_sinh'] - $stats['tong_trung_tuyen'];
            
            // Tỷ lệ
            if ($stats['tong_thi_sinh'] > 0) {
                $stats['ty_le_trung_tuyen'] = round(($stats['tong_trung_tuyen'] / $stats['tong_thi_sinh']) * 100, 1);
            }
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error getThongKeXetTuyen: " . $e->getMessage());
            return [
                'tong_thi_sinh' => 0,
                'tong_trung_tuyen' => 0,
                'tong_truot' => 0,
                'ty_le_trung_tuyen' => 0
            ];
        }
    }

    /**
     * Lấy kết quả theo từng trường
     */
    public function getKetQuaTheoTruong() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    t.maTruong,
                    t.tenTruong,
                    dc.soDiem as soDiem,
                    COUNT(DISTINCT CASE WHEN nv.trangThai = 'DAU' THEN nv.maThiSinh END) as soTrungTuyen
                FROM truong t
                LEFT JOIN diemchuan dc ON t.maTruong = dc.maTruong
                LEFT JOIN nguyenvong nv ON t.maTruong = nv.maTruong
                GROUP BY t.maTruong, t.tenTruong, dc.soDiem
                ORDER BY t.tenTruong
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getKetQuaTheoTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy kết quả chi tiết từng thí sinh
     */
    public function getKetQuaChiTiet() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    ts.maThiSinh,
                    ts.hoTen,
                    ts.diem,
                    nv.thuTuUuTien,
                    t.tenTruong,
                    nv.trangThai
                FROM thisinh ts
                INNER JOIN nguyenvong nv ON ts.maThiSinh = nv.maThiSinh
                INNER JOIN truong t ON nv.maTruong = t.maTruong
                WHERE nv.trangThai IN ('DAU', 'TRUOT')
                ORDER BY ts.diem DESC, ts.maThiSinh, nv.thuTuUuTien
                LIMIT 100
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getKetQuaChiTiet: " . $e->getMessage());
            return [];
        }
    }
}

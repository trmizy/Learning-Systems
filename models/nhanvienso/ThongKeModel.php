<?php
require_once __DIR__ . '/../../config/database.php';

class ThongKeModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Thống kê chung: Tổng số thí sinh, điểm trung bình,...
     */
    public function getThongKeChung($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        COUNT(*) as tongThiSinh,
                        AVG(diem) as diemTrungBinh,
                        MAX(diem) as diemCaoNhat,
                        MIN(diem) as diemThapNhat,
                        COUNT(CASE WHEN diem >= 22.5 THEN 1 END) as soThiSinhDat
                    FROM thisinh
                    WHERE namTuyenSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namTuyenSinh]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongKeChung: " . $e->getMessage());
            return [
                'tongThiSinh' => 0,
                'diemTrungBinh' => 0,
                'diemCaoNhat' => 0,
                'diemThapNhat' => 0,
                'soThiSinhDat' => 0
            ];
        }
    }

    /**
     * Thống kê theo môn: Toán, Văn, Anh
     */
    public function getThongKeTheoMon($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        'Toán' as monHoc,
                        AVG(diemToan) as diemTB,
                        MAX(diemToan) as diemMax,
                        MIN(diemToan) as diemMin,
                        COUNT(CASE WHEN diemToan >= 8 THEN 1 END) as soThiSinhGioi
                    FROM thisinh
                    WHERE namTuyenSinh = ?
                    
                    UNION ALL
                    
                    SELECT 
                        'Văn' as monHoc,
                        AVG(diemVan) as diemTB,
                        MAX(diemVan) as diemMax,
                        MIN(diemVan) as diemMin,
                        COUNT(CASE WHEN diemVan >= 8 THEN 1 END) as soThiSinhGioi
                    FROM thisinh
                    WHERE namTuyenSinh = ?
                    
                    UNION ALL
                    
                    SELECT 
                        'Anh' as monHoc,
                        AVG(diemAnh) as diemTB,
                        MAX(diemAnh) as diemMax,
                        MIN(diemAnh) as diemMin,
                        COUNT(CASE WHEN diemAnh >= 8 THEN 1 END) as soThiSinhGioi
                    FROM thisinh
                    WHERE namTuyenSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namTuyenSinh, $namTuyenSinh, $namTuyenSinh]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongKeTheoMon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Thống kê theo trường: Điểm TB, số thí sinh đỗ
     */
    public function getThongKeTheoTruong($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        nv.maChiTieu,
                        COUNT(ts.maThiSinh) as soThiSinh,
                        AVG(ts.diem) as diemTB,
                        COUNT(CASE WHEN ts.diem >= 22.5 THEN 1 END) as soThiSinhDat
                    FROM chitieutuyensinh ct
                    LEFT JOIN nguyenvong nv ON ct.maChiTieu = nv.maChiTieu
                    LEFT JOIN thisinh ts ON nv.maThiSinh = ts.maThiSinh
                    WHERE ct.namHoc LIKE ?
                    GROUP BY nv.maChiTieu
                    ORDER BY diemTB DESC
                    LIMIT 10";
            
            $namHoc = $namTuyenSinh . '-%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namHoc]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongKeTheoTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Phân phối điểm: Chia thành các khoảng điểm
     */
    public function getPhanPoiDiem($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        CASE 
                            WHEN diem < 10 THEN 'Dưới 10'
                            WHEN diem >= 10 AND diem < 15 THEN '10-15'
                            WHEN diem >= 15 AND diem < 20 THEN '15-20'
                            WHEN diem >= 20 AND diem < 25 THEN '20-25'
                            ELSE 'Trên 25'
                        END as khoangDiem,
                        COUNT(*) as soLuong
                    FROM thisinh
                    WHERE namTuyenSinh = ?
                    GROUP BY khoangDiem
                    ORDER BY MIN(diem)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namTuyenSinh]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getPhanPoiDiem: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Top thí sinh có điểm cao nhất
     */
    public function getTopThiSinh($namTuyenSinh, $limit = 10) {
        try {
            $sql = "SELECT 
                        maThiSinh,
                        hoTen,
                        diem,
                        diemToan,
                        diemVan,
                        diemAnh,
                        noiSinh
                    FROM thisinh
                    WHERE namTuyenSinh = ?
                    ORDER BY diem DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $namTuyenSinh, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getTopThiSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách năm tuyển sinh có dữ liệu
     */
    public function getDanhSachNamTuyenSinh() {
        try {
            $sql = "SELECT DISTINCT namTuyenSinh 
                    FROM thisinh 
                    ORDER BY namTuyenSinh DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachNamTuyenSinh: " . $e->getMessage());
            return [date('Y')];
        }
    }
}

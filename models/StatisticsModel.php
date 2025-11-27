<?php
require_once __DIR__ . '/../config/database.php';

class StatisticsModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Lấy danh sách năm học có dữ liệu
     * @return array
     */
    public function getDanhSachNamHoc() {
        try {
            $sql = "SELECT DISTINCT namHoc 
                    FROM viewThongKeDiemHanhKiem 
                    ORDER BY namHoc DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachNamHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách học kỳ
     * @return array
     */
    public function getDanhSachHocKy() {
        return [
            ['hocKy' => 'HK1', 'tenHocKy' => 'Học kỳ 1'],
            ['hocKy' => 'HK2', 'tenHocKy' => 'Học kỳ 2']
        ];
    }

    /**
     * Lấy danh sách khối
     * @return array
     */
    public function getDanhSachKhoi() {
        try {
            $sql = "SELECT DISTINCT khoi 
                    FROM viewThongKeDiemHanhKiem 
                    ORDER BY khoi";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachKhoi: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách lớp theo khối
     * @param string|null $khoi
     * @return array
     */
    public function getDanhSachLop($khoi = null) {
        try {
            $sql = "SELECT DISTINCT maLop, tenLop, khoi 
                    FROM viewThongKeDiemHanhKiem";
            
            if ($khoi) {
                $sql .= " WHERE khoi = :khoi";
            }
            
            $sql .= " ORDER BY maLop";
            
            $stmt = $this->conn->prepare($sql);
            
            if ($khoi) {
                $stmt->bindParam(':khoi', $khoi);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy năm học và học kỳ gần nhất
     * @return array
     */
    public function getNamHocHocKyGanNhat() {
        try {
            $sql = "SELECT namHoc, hocKy 
                    FROM viewThongKeDiemHanhKiem 
                    ORDER BY namHoc DESC, hocKy DESC 
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result ?: ['namHoc' => date('Y') . '-' . (date('Y') + 1), 'hocKy' => 'HK1'];
        } catch (PDOException $e) {
            error_log("Error in getNamHocHocKyGanNhat: " . $e->getMessage());
            return ['namHoc' => date('Y') . '-' . (date('Y') + 1), 'hocKy' => 'HK1'];
        }
    }

    /**
     * Thống kê điểm số và hạnh kiểm
     * @param array $filters ['namHoc', 'hocKy', 'khoi', 'maLop']
     * @return array
     */
    public function thongKeDiemHanhKiem($filters) {
        try {
            // Validate bắt buộc
            if (empty($filters['namHoc']) || empty($filters['hocKy'])) {
                throw new Exception("Năm học và học kỳ là bắt buộc");
            }

            // Query cơ bản
            $sql = "SELECT 
                        maHS,
                        hoTen,
                        maLop,
                        tenLop,
                        khoi,
                        diemTrungBinhChung,
                        loaiHanhKiem,
                        xepLoaiHocLuc
                    FROM viewThongKeDiemHanhKiem
                    WHERE namHoc = :namHoc AND hocKy = :hocKy";

            // Thêm filter tùy chọn
            if (!empty($filters['khoi'])) {
                $sql .= " AND khoi = :khoi";
            }
            
            if (!empty($filters['maLop'])) {
                $sql .= " AND maLop = :maLop";
            }

            $sql .= " ORDER BY khoi, maLop, hoTen";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':namHoc', $filters['namHoc']);
            $stmt->bindParam(':hocKy', $filters['hocKy']);
            
            if (!empty($filters['khoi'])) {
                $stmt->bindParam(':khoi', $filters['khoi']);
            }
            
            if (!empty($filters['maLop'])) {
                $stmt->bindParam(':maLop', $filters['maLop']);
            }

            $stmt->execute();
            $data = $stmt->fetchAll();

            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy dữ liệu',
                    'data' => null
                ];
            }

            // Tính toán thống kê
            $thongKe = $this->tinhToanThongKe($data);

            return [
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => [
                    'danhSach' => $data,
                    'thongKe' => $thongKe,
                    'filters' => $filters
                ]
            ];

        } catch (Exception $e) {
            error_log("Error in thongKeDiemHanhKiem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Tính toán thống kê từ dữ liệu
     * @param array $data
     * @return array
     */
    private function tinhToanThongKe($data) {
        $tongSoHS = count($data);
        
        // Thống kê học lực
        $hocLuc = [
            'Gioi' => 0,
            'Kha' => 0,
            'Trung Binh' => 0,
            'Yeu' => 0
        ];
        
        // Thống kê hạnh kiểm
        $hanhKiem = [
            'Tot' => 0,
            'Kha' => 0,
            'Trung Binh' => 0,
            'Yeu' => 0
        ];
        
        $tongDiemTB = 0;
        
        foreach ($data as $hs) {
            // Đếm học lực
            if (isset($hs['xepLoaiHocLuc']) && isset($hocLuc[$hs['xepLoaiHocLuc']])) {
                $hocLuc[$hs['xepLoaiHocLuc']]++;
            }
            
            // Đếm hạnh kiểm
            if (isset($hs['loaiHanhKiem']) && isset($hanhKiem[$hs['loaiHanhKiem']])) {
                $hanhKiem[$hs['loaiHanhKiem']]++;
            }
            
            // Tổng điểm TB
            $tongDiemTB += $hs['diemTrungBinhChung'] ?? 0;
        }
        
        // Tính tỉ lệ %
        $tiLeHocLuc = [];
        foreach ($hocLuc as $loai => $soLuong) {
            $tiLeHocLuc[$loai] = [
                'soLuong' => $soLuong,
                'tiLe' => $tongSoHS > 0 ? round(($soLuong / $tongSoHS) * 100, 2) : 0
            ];
        }
        
        $tiLeHanhKiem = [];
        foreach ($hanhKiem as $loai => $soLuong) {
            $tiLeHanhKiem[$loai] = [
                'soLuong' => $soLuong,
                'tiLe' => $tongSoHS > 0 ? round(($soLuong / $tongSoHS) * 100, 2) : 0
            ];
        }
        
        return [
            'tongSoHocSinh' => $tongSoHS,
            'diemTrungBinhChung' => $tongSoHS > 0 ? round($tongDiemTB / $tongSoHS, 2) : 0,
            'hocLuc' => $tiLeHocLuc,
            'hanhKiem' => $tiLeHanhKiem
        ];
    }
}

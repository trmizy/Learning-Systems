<?php
require_once __DIR__ . '/../../config/database.php';

class StatisticsModel {
    /** @var PDO */
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Helper chung cho SELECT nhiều dòng
    private function fetchAll(string $sql, array $params = []): array {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return [];
        }
    }

    // Helper cho SELECT 1 dòng
    private function fetchOne(string $sql, array $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }

    public function getDanhSachNamHoc(): array {
        $sql = "SELECT DISTINCT namHoc 
                FROM viewThongKeDiemHanhKiem 
                ORDER BY namHoc DESC";
        return $this->fetchAll($sql);
    }

    public function getDanhSachHocKy(): array {
        return [
            ['hocKy' => 'HK1', 'tenHocKy' => 'Học kỳ 1'],
            ['hocKy' => 'HK2', 'tenHocKy' => 'Học kỳ 2']
        ];
    }

    public function getDanhSachKhoi(): array {
        $sql = "SELECT DISTINCT khoi 
                FROM viewThongKeDiemHanhKiem 
                ORDER BY khoi";
        return $this->fetchAll($sql);
    }

    public function getDanhSachLop(?string $khoi = null): array {
        $sql = "SELECT DISTINCT maLop, tenLop, khoi 
                FROM viewThongKeDiemHanhKiem";
        $params = [];

        if ($khoi) {
            $sql .= " WHERE khoi = :khoi";
            $params[':khoi'] = $khoi;
        }

        $sql .= " ORDER BY maLop";

        return $this->fetchAll($sql, $params);
    }

    public function getNamHocHocKyGanNhat(): array {
        $sql = "SELECT namHoc, hocKy 
                FROM viewThongKeDiemHanhKiem 
                ORDER BY namHoc DESC, hocKy DESC 
                LIMIT 1";

        $result = $this->fetchOne($sql);

        if (!$result) {
            return [
                'namHoc' => date('Y') . '-' . (date('Y') + 1),
                'hocKy'  => 'HK1'
            ];
        }

        return $result;
    }

    /**
     * @param array $filters ['namHoc','hocKy','khoi','maLop']
     */
    public function thongKeDiemHanhKiem(array $filters): array {
        if (empty($filters['namHoc']) || empty($filters['hocKy'])) {
            return [
                'success' => false,
                'message' => 'Năm học và học kỳ là bắt buộc',
                'data'    => null
            ];
        }

        $sql = "SELECT 
                    maHS,
                    hoTen,
                    maLop,
                    tenLop,
                    khoi,
                    diemTrungBinhChung,
                    loaiHanhKiem,
                    xepLoaiHocLuc,
                    namHoc,
                    hocKy
                FROM viewThongKeDiemHanhKiem
                WHERE namHoc = :namHoc AND hocKy = :hocKy";

        $params = [
            ':namHoc' => $filters['namHoc'],
            ':hocKy'  => $filters['hocKy']
        ];

        if (!empty($filters['khoi'])) {
            $sql .= " AND khoi = :khoi";
            $params[':khoi'] = $filters['khoi'];
        }

        if (!empty($filters['maLop'])) {
            $sql .= " AND maLop = :maLop";
            $params[':maLop'] = $filters['maLop'];
        }

        $sql .= " ORDER BY khoi, maLop, hoTen";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in thongKeDiemHanhKiem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi truy vấn dữ liệu',
                'data'    => null
            ];
        }

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy dữ liệu',
                'data'    => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Lấy dữ liệu thành công',
            'data'    => [
                'danhSach' => $data,
                'thongKe'  => $this->tinhToanThongKe($data),
                'filters'  => $filters
            ]
        ];
    }

    private function tinhToanThongKe(array $data): array {
        $tongSoHS   = count($data);
        $tongDiemTB = 0;
        $soHSCoDiem = 0; // Đếm số học sinh thực sự có điểm

        $hocLuc = [
            'Gioi'       => 0,
            'Kha'        => 0,
            'Trung Binh' => 0,
            'Yeu'        => 0
        ];

        $hanhKiem = [
            'Tot'        => 0,
            'Kha'        => 0,
            'Trung Binh' => 0,
            'Yeu'        => 0
        ];

        foreach ($data as $hs) {
            // Chỉ tính học sinh có xếp loại học lực (tức là đã có điểm)
            if (!empty($hs['xepLoaiHocLuc']) && isset($hocLuc[$hs['xepLoaiHocLuc']])) {
                $hocLuc[$hs['xepLoaiHocLuc']]++;
            }

            if (!empty($hs['loaiHanhKiem']) && isset($hanhKiem[$hs['loaiHanhKiem']])) {
                $hanhKiem[$hs['loaiHanhKiem']]++;
            }

            // Chỉ tính điểm trung bình cho học sinh có điểm thực sự
            // Kiểm tra: điểm không null, lớn hơn 0, và có xếp loại học lực
            if ($hs['diemTrungBinhChung'] !== null 
                && $hs['diemTrungBinhChung'] > 0 
                && !empty($hs['xepLoaiHocLuc'])) {
                $tongDiemTB += $hs['diemTrungBinhChung'];
                $soHSCoDiem++;
            }
        }

        $buildTiLe = function (array $src) use ($tongSoHS): array {
            $res = [];
            foreach ($src as $loai => $soLuong) {
                $res[$loai] = [
                    'soLuong' => $soLuong,
                    'tiLe'    => $tongSoHS > 0 ? round($soLuong / $tongSoHS * 100, 2) : 0
                ];
            }
            return $res;
        };

        return [
            'tongSoHocSinh'      => $tongSoHS,
            'soHocSinhCoDiem'    => $soHSCoDiem,
            'diemTrungBinhChung' => $soHSCoDiem > 0 ? round($tongDiemTB / $soHSCoDiem, 2) : null,
            'hocLuc'             => $buildTiLe($hocLuc),
            'hanhKiem'           => $buildTiLe($hanhKiem)
        ];
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class EnterPointsModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db   = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Lấy danh sách các lớp mà giáo viên được phân công giảng dạy
     */
    public function getDanhSachLopPhanCong($maGV, $namHoc = null, $hocKy = null) {
        try {
            $sql = "SELECT DISTINCT 
                        pc.maLop,
                        l.tenLop,
                        l.siSo,
                        l.khoi,
                        mh.maMonHoc,
                        mh.tenMon,
                        pc.namHoc,
                        pc.hocKy
                    FROM phanconggiangday pc
                    INNER JOIN lophoc l ON pc.maLop = l.maLop
                    INNER JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc";

            // Xây WHERE động cho gọn
            $conditions = ["pc.maGV = :maGV"];
            $params     = [':maGV' => $maGV];

            if ($namHoc) {
                $conditions[]     = "pc.namHoc = :namHoc";
                $params[':namHoc'] = $namHoc;
            }
            if ($hocKy) {
                $conditions[]    = "pc.hocKy = :hocKy";
                $params[':hocKy'] = $hocKy;
            }

            $sql .= " WHERE " . implode(" AND ", $conditions) . " ORDER BY l.tenLop, mh.tenMon";

            $stmt = $this->conn->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getDanhSachLopPhanCong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy bảng điểm của một lớp học cho môn học cụ thể
     */
    public function getBangDiem($maLop, $maMonHoc, $maGV, $namHoc, $hocKy) {
        try {
            $sql = "SELECT 
                        hs.maHS,
                        hs.hoTen,
                        hs.ngaySinh,
                        bd.maBangDiem,
                        bd.diemThuongXuyen,
                        bd.diemGiuaKy,
                        bd.diemCuoiKy,
                        CASE 
                            WHEN bd.diemThuongXuyen IS NOT NULL 
                             AND bd.diemGiuaKy IS NOT NULL 
                             AND bd.diemCuoiKy IS NOT NULL 
                            THEN ROUND((bd.diemThuongXuyen + bd.diemGiuaKy * 2 + bd.diemCuoiKy * 3) / 6, 1)
                            ELSE NULL
                        END as diemTrungBinh
                    FROM hocsinh hs
                    LEFT JOIN bangdiem bd ON hs.maHS = bd.maHS 
                        AND bd.maMonHoc = :maMonHoc 
                        AND bd.maGV = :maGV
                        AND bd.namHoc = :namHoc
                        AND bd.hocKy = :hocKy
                    WHERE hs.maLop = :maLop 
                        AND hs.trangThai = 'DANGHOC'
                    ORDER BY hs.hoTen";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':maLop',    $maLop);
            $stmt->bindParam(':maMonHoc', $maMonHoc);
            $stmt->bindParam(':maGV',     $maGV);
            $stmt->bindParam(':namHoc',   $namHoc);
            $stmt->bindParam(':hocKy',    $hocKy);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getBangDiem: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra tính hợp lệ của 1 điểm
     */
    public function validateDiem($diem) {
        if ($diem === null || $diem === '') {
            return ['valid' => true, 'message' => ''];
        }

        if (!is_numeric($diem)) {
            return ['valid' => false, 'message' => 'Điểm không hợp lệ. Vui lòng nhập số.'];
        }

        $diemFloat = floatval($diem);
        if ($diemFloat < 0 || $diemFloat > 10) {
            return ['valid' => false, 'message' => 'Điểm không hợp lệ. Điểm phải từ 0 đến 10.'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate nhiều điểm cùng lúc (rút gọn lặp)
     */
    private function validateAllDiem(array $diemFields) {
        foreach ($diemFields as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $validate = $this->validateDiem($value);
            if (!$validate['valid']) {
                return [
                    'valid'   => false,
                    'message' => $label . ': ' . $validate['message']
                ];
            }
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Chuẩn hóa điểm (null hoặc float)
     */
    private function normalizeScore($diem) {
        return ($diem !== null && $diem !== '') ? floatval($diem) : null;
    }

    /**
     * Lưu hoặc cập nhật điểm cho học sinh
     */
    public function luuDiem($data) {
        try {
            $this->conn->beginTransaction();

            $maHS     = $data['maHS'];
            $maMonHoc = $data['maMonHoc'];
            $maGV     = $data['maGV'];
            $namHoc   = $data['namHoc'];
            $hocKy    = $data['hocKy'];

            $diemThuongXuyen = $data['diemThuongXuyen'] ?? null;
            $diemGiuaKy      = $data['diemGiuaKy']      ?? null;
            $diemCuoiKy      = $data['diemCuoiKy']      ?? null;

            // Gom validate 3 loại điểm
            $validateAll = $this->validateAllDiem([
                'Điểm thường xuyên' => $diemThuongXuyen,
                'Điểm giữa kỳ'      => $diemGiuaKy,
                'Điểm cuối kỳ'      => $diemCuoiKy,
            ]);

            if (!$validateAll['valid']) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => $validateAll['message']];
            }

            // Kiểm tra đã có bảng điểm chưa
            $sqlCheck = "SELECT maBangDiem FROM bangdiem 
                        WHERE maHS = :maHS 
                          AND maMonHoc = :maMonHoc 
                          AND maGV = :maGV
                          AND namHoc = :namHoc
                          AND hocKy = :hocKy";
            
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->execute([
                ':maHS'     => $maHS,
                ':maMonHoc' => $maMonHoc,
                ':maGV'     => $maGV,
                ':namHoc'   => $namHoc,
                ':hocKy'    => $hocKy,
            ]);
            
            $existing = $stmtCheck->fetch();

            if ($existing) {
                // Cập nhật điểm (build động)
                $fields = [
                    'diemThuongXuyen' => $diemThuongXuyen,
                    'diemGiuaKy'      => $diemGiuaKy,
                    'diemCuoiKy'      => $diemCuoiKy,
                ];

                $updates = [];
                $params  = [':maBangDiem' => $existing['maBangDiem']];

                foreach ($fields as $col => $value) {
                    if ($value !== null && $value !== '') {
                        $updates[]                = "$col = :$col";
                        $params[":$col"] = $this->normalizeScore($value);
                    }
                }

                if (empty($updates)) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Không có điểm nào được cập nhật'];
                }

                $sqlUpdate = "UPDATE bangdiem SET " . implode(', ', $updates) . " WHERE maBangDiem = :maBangDiem";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute($params);
            } else {
                // Thêm mới
                $maBangDiem = $this->generateMaBangDiem($maHS, $maMonHoc, $namHoc, $hocKy);

                $sqlInsert = "INSERT INTO bangdiem 
                                (maBangDiem, diemThuongXuyen, diemGiuaKy, diemCuoiKy, 
                                 namHoc, hocKy, maHS, maMonHoc, maGV) 
                              VALUES 
                                (:maBangDiem, :diemThuongXuyen, :diemGiuaKy, :diemCuoiKy, 
                                 :namHoc, :hocKy, :maHS, :maMonHoc, :maGV)";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':maBangDiem'      => $maBangDiem,
                    ':diemThuongXuyen' => $this->normalizeScore($diemThuongXuyen),
                    ':diemGiuaKy'      => $this->normalizeScore($diemGiuaKy),
                    ':diemCuoiKy'      => $this->normalizeScore($diemCuoiKy),
                    ':namHoc'          => $namHoc,
                    ':hocKy'           => $hocKy,
                    ':maHS'            => $maHS,
                    ':maMonHoc'        => $maMonHoc,
                    ':maGV'            => $maGV,
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Lưu điểm thành công'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error in luuDiem: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi khi lưu điểm: ' . $e->getMessage()];
        }
    }

    /**
     * Lưu nhiều điểm cùng lúc (cả lớp)
     */
    public function luuNhieuDiem($danhSachDiem) {
        $errors       = [];
        $successCount = 0;

        foreach ($danhSachDiem as $diemData) {
            $result = $this->luuDiem($diemData);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = [
                    'maHS'    => $diemData['maHS'],
                    'hoTen'   => $diemData['hoTen'] ?? 'Không xác định',
                    'message' => $result['message']
                ];
            }
        }

        if (empty($errors)) {
            return [
                'success' => true, 
                'message' => "Lưu thành công {$successCount} bản ghi điểm",
                'errors'  => []
            ];
        }

        return [
            'success' => false,
            'message' => "Lưu thành công {$successCount}/" . count($danhSachDiem) . " bản ghi. Có " . count($errors) . " lỗi.",
            'errors'  => $errors
        ];
    }

    /**
     * Tạo mã bảng điểm tự động
     */
    private function generateMaBangDiem($maHS, $maMonHoc, $namHoc, $hocKy) {
        $namHocShort = str_replace('-', '', $namHoc); // 2024-2025 -> 20242025
        return "BD_{$maHS}_{$maMonHoc}_{$namHocShort}_{$hocKy}";
    }

    /**
     * Lấy thông tin năm học và học kỳ hiện tại
     */
    public function getNamHocHocKyHienTai() {
        $currentYear  = date('Y');
        $currentMonth = date('n');

        if ($currentMonth >= 9) {
            $namHoc = $currentYear . '-' . ($currentYear + 1);
            $hocKy  = 'HK1';
        } elseif ($currentMonth >= 1 && $currentMonth <= 5) {
            $namHoc = ($currentYear - 1) . '-' . $currentYear;
            $hocKy  = 'HK2';
        } else { // 6-8: hè, vẫn tính là HK2 của năm trước
            $namHoc = ($currentYear - 1) . '-' . $currentYear;
            $hocKy  = 'HK2';
        }

        return compact('namHoc', 'hocKy');
    }
}

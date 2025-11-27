<?php
require_once __DIR__ . '/../config/database.php';

class EnterPointsModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Lấy danh sách các lớp mà giáo viên được phân công giảng dạy
     * @param string $maGV - Mã giáo viên
     * @param string $namHoc - Năm học hiện tại
     * @param string $hocKy - Học kỳ hiện tại
     * @return array Danh sách các lớp
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
                    INNER JOIN monhoc mh ON pc.maMonHoc = mh.maMonHoc
                    WHERE pc.maGV = :maGV";
            
            if ($namHoc) {
                $sql .= " AND pc.namHoc = :namHoc";
            }
            if ($hocKy) {
                $sql .= " AND pc.hocKy = :hocKy";
            }
            
            $sql .= " ORDER BY l.tenLop, mh.tenMon";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':maGV', $maGV);
            if ($namHoc) {
                $stmt->bindParam(':namHoc', $namHoc);
            }
            if ($hocKy) {
                $stmt->bindParam(':hocKy', $hocKy);
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
     * @param string $maLop - Mã lớp
     * @param string $maMonHoc - Mã môn học
     * @param string $maGV - Mã giáo viên
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return array Danh sách học sinh và điểm
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
            $stmt->bindParam(':maLop', $maLop);
            $stmt->bindParam(':maMonHoc', $maMonHoc);
            $stmt->bindParam(':maGV', $maGV);
            $stmt->bindParam(':namHoc', $namHoc);
            $stmt->bindParam(':hocKy', $hocKy);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getBangDiem: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra tính hợp lệ của điểm số
     * @param mixed $diem - Điểm cần kiểm tra
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateDiem($diem) {
        // Cho phép null hoặc empty (không nhập điểm)
        if ($diem === null || $diem === '') {
            return ['valid' => true, 'message' => ''];
        }

        // Kiểm tra có phải là số không
        if (!is_numeric($diem)) {
            return ['valid' => false, 'message' => 'Điểm không hợp lệ. Vui lòng nhập số.'];
        }

        $diemFloat = floatval($diem);

        // Kiểm tra khoảng giá trị 0-10
        if ($diemFloat < 0 || $diemFloat > 10) {
            return ['valid' => false, 'message' => 'Điểm không hợp lệ. Điểm phải từ 0 đến 10.'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Lưu hoặc cập nhật điểm cho học sinh
     * @param array $data - Dữ liệu điểm cần lưu
     * @return array ['success' => bool, 'message' => string]
     */
    public function luuDiem($data) {
        try {
            $this->conn->beginTransaction();

            $maHS = $data['maHS'];
            $maMonHoc = $data['maMonHoc'];
            $maGV = $data['maGV'];
            $namHoc = $data['namHoc'];
            $hocKy = $data['hocKy'];
            
            // Validate tất cả các điểm
            $diemThuongXuyen = $data['diemThuongXuyen'] ?? null;
            $diemGiuaKy = $data['diemGiuaKy'] ?? null;
            $diemCuoiKy = $data['diemCuoiKy'] ?? null;

            // Kiểm tra điểm thường xuyên
            if ($diemThuongXuyen !== null && $diemThuongXuyen !== '') {
                $validate = $this->validateDiem($diemThuongXuyen);
                if (!$validate['valid']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Điểm thường xuyên: ' . $validate['message']];
                }
            }

            // Kiểm tra điểm giữa kỳ
            if ($diemGiuaKy !== null && $diemGiuaKy !== '') {
                $validate = $this->validateDiem($diemGiuaKy);
                if (!$validate['valid']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Điểm giữa kỳ: ' . $validate['message']];
                }
            }

            // Kiểm tra điểm cuối kỳ
            if ($diemCuoiKy !== null && $diemCuoiKy !== '') {
                $validate = $this->validateDiem($diemCuoiKy);
                if (!$validate['valid']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Điểm cuối kỳ: ' . $validate['message']];
                }
            }

            // Kiểm tra xem đã có bảng điểm chưa
            $sqlCheck = "SELECT maBangDiem FROM bangdiem 
                        WHERE maHS = :maHS 
                        AND maMonHoc = :maMonHoc 
                        AND maGV = :maGV
                        AND namHoc = :namHoc
                        AND hocKy = :hocKy";
            
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':maHS', $maHS);
            $stmtCheck->bindParam(':maMonHoc', $maMonHoc);
            $stmtCheck->bindParam(':maGV', $maGV);
            $stmtCheck->bindParam(':namHoc', $namHoc);
            $stmtCheck->bindParam(':hocKy', $hocKy);
            $stmtCheck->execute();
            
            $existing = $stmtCheck->fetch();

            if ($existing) {
                // Cập nhật điểm
                $sqlUpdate = "UPDATE bangdiem SET ";
                $updates = [];
                $params = [':maBangDiem' => $existing['maBangDiem']];

                if ($diemThuongXuyen !== null && $diemThuongXuyen !== '') {
                    $updates[] = "diemThuongXuyen = :diemThuongXuyen";
                    $params[':diemThuongXuyen'] = floatval($diemThuongXuyen);
                }

                if ($diemGiuaKy !== null && $diemGiuaKy !== '') {
                    $updates[] = "diemGiuaKy = :diemGiuaKy";
                    $params[':diemGiuaKy'] = floatval($diemGiuaKy);
                }

                if ($diemCuoiKy !== null && $diemCuoiKy !== '') {
                    $updates[] = "diemCuoiKy = :diemCuoiKy";
                    $params[':diemCuoiKy'] = floatval($diemCuoiKy);
                }

                if (empty($updates)) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Không có điểm nào được cập nhật'];
                }

                $sqlUpdate .= implode(", ", $updates);
                $sqlUpdate .= " WHERE maBangDiem = :maBangDiem";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                foreach ($params as $key => $value) {
                    $stmtUpdate->bindValue($key, $value);
                }
                $stmtUpdate->execute();
            } else {
                // Tạo mã bảng điểm mới
                $maBangDiem = $this->generateMaBangDiem($maHS, $maMonHoc, $namHoc, $hocKy);

                // Thêm mới điểm
                $sqlInsert = "INSERT INTO bangdiem 
                            (maBangDiem, diemThuongXuyen, diemGiuaKy, diemCuoiKy, 
                             namHoc, hocKy, maHS, maMonHoc, maGV) 
                            VALUES 
                            (:maBangDiem, :diemThuongXuyen, :diemGiuaKy, :diemCuoiKy, 
                             :namHoc, :hocKy, :maHS, :maMonHoc, :maGV)";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bindParam(':maBangDiem', $maBangDiem);
                $stmtInsert->bindValue(':diemThuongXuyen', 
                    ($diemThuongXuyen !== null && $diemThuongXuyen !== '') ? floatval($diemThuongXuyen) : null);
                $stmtInsert->bindValue(':diemGiuaKy', 
                    ($diemGiuaKy !== null && $diemGiuaKy !== '') ? floatval($diemGiuaKy) : null);
                $stmtInsert->bindValue(':diemCuoiKy', 
                    ($diemCuoiKy !== null && $diemCuoiKy !== '') ? floatval($diemCuoiKy) : null);
                $stmtInsert->bindParam(':namHoc', $namHoc);
                $stmtInsert->bindParam(':hocKy', $hocKy);
                $stmtInsert->bindParam(':maHS', $maHS);
                $stmtInsert->bindParam(':maMonHoc', $maMonHoc);
                $stmtInsert->bindParam(':maGV', $maGV);
                $stmtInsert->execute();
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
     * @param array $danhSachDiem - Mảng các dữ liệu điểm
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function luuNhieuDiem($danhSachDiem) {
        $errors = [];
        $successCount = 0;

        foreach ($danhSachDiem as $index => $diemData) {
            $result = $this->luuDiem($diemData);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = [
                    'maHS' => $diemData['maHS'],
                    'hoTen' => $diemData['hoTen'] ?? 'Không xác định',
                    'message' => $result['message']
                ];
            }
        }

        if (empty($errors)) {
            return [
                'success' => true, 
                'message' => "Lưu thành công {$successCount} bản ghi điểm",
                'errors' => []
            ];
        } else {
            return [
                'success' => false,
                'message' => "Lưu thành công {$successCount}/" . count($danhSachDiem) . " bản ghi. Có " . count($errors) . " lỗi.",
                'errors' => $errors
            ];
        }
    }

    /**
     * Tạo mã bảng điểm tự động
     * @param string $maHS - Mã học sinh
     * @param string $maMonHoc - Mã môn học
     * @param string $namHoc - Năm học
     * @param string $hocKy - Học kỳ
     * @return string Mã bảng điểm
     */
    private function generateMaBangDiem($maHS, $maMonHoc, $namHoc, $hocKy) {
        // Format: BD_<maHS>_<maMonHoc>_<namHoc>_<hocKy>
        $namHocShort = str_replace('-', '', $namHoc); // 2024-2025 -> 20242025
        return "BD_{$maHS}_{$maMonHoc}_{$namHocShort}_{$hocKy}";
    }

    /**
     * Lấy thông tin năm học và học kỳ hiện tại
     * @return array ['namHoc' => string, 'hocKy' => string]
     */
    public function getNamHocHocKyHienTai() {
        // Logic để xác định năm học và học kỳ hiện tại
        // Có thể lấy từ bảng cấu hình hoặc tính toán dựa vào ngày hiện tại
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Giả định: Học kỳ 1 từ tháng 9-12, Học kỳ 2 từ tháng 1-5
        if ($currentMonth >= 9) {
            $namHoc = $currentYear . '-' . ($currentYear + 1);
            $hocKy = 'HK1';
        } elseif ($currentMonth >= 1 && $currentMonth <= 5) {
            $namHoc = ($currentYear - 1) . '-' . $currentYear;
            $hocKy = 'HK2';
        } else {
            // Tháng 6-8: Hè
            $namHoc = ($currentYear - 1) . '-' . $currentYear;
            $hocKy = 'HK2';
        }

        return [
            'namHoc' => $namHoc,
            'hocKy' => $hocKy
        ];
    }
}

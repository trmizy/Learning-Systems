<?php
require_once __DIR__ . '/../../config/database.php';

class XetTuyenModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách thí sinh xét tuyển theo trường và năm
     */
    public function getDanhSachThiSinhXetTuyen($maTruong, $namTuyenSinh) {
        try {
            $sql = "SELECT 
                        ts.maThiSinh,
                        ts.hoTen,
                        ts.soCCCD,
                        ts.ngaySinh,
                        ts.gioiTinh,
                        ts.soDienThoai,
                        ts.diemVan,
                        ts.diemToan,
                        ts.diemAnh,
                        (ts.diemVan * 2 + ts.diemToan * 2 + ts.diemAnh) as tongDiem,
                        nv.thuTuUuTien,
                        nv.trangThai,
                        nv.maNguyenVong,
                        dc.soDiem as diemChuan
                    FROM thisinh ts
                    INNER JOIN nguyenvong nv ON ts.maThiSinh = nv.maThiSinh
                    LEFT JOIN diemchuan dc ON dc.maTruong = nv.maTruong 
                        AND dc.namTuyenSinh = ts.namTuyenSinh
                    WHERE nv.maTruong = ?
                      AND ts.namTuyenSinh = ?
                      AND nv.trangThai = 'CHO_DUYET'
                    ORDER BY nv.thuTuUuTien ASC, 
                             (ts.diemVan * 2 + ts.diemToan * 2 + ts.diemAnh) DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong, $namTuyenSinh]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachThiSinhXetTuyen: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy điểm chuẩn của trường
     */
    public function getDiemChuan($maTruong, $namTuyenSinh) {
        try {
            $sql = "SELECT soDiem FROM diemchuan 
                    WHERE maTruong = ? AND namTuyenSinh = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong, $namTuyenSinh]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['soDiem'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getDiemChuan: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Xét tuyển một thí sinh - Tạo học sinh + tài khoản
     */
    public function xetTuyenThiSinh($maThiSinh, $maLop) {
        try {
            $this->db->beginTransaction();

            // 1. Lấy thông tin thí sinh
            $sqlThiSinh = "SELECT * FROM thisinh WHERE maThiSinh = ?";
            $stmtThiSinh = $this->db->prepare($sqlThiSinh);
            $stmtThiSinh->execute([$maThiSinh]);
            $thiSinh = $stmtThiSinh->fetch(PDO::FETCH_ASSOC);

            if (!$thiSinh) {
                throw new Exception("Không tìm thấy thông tin thí sinh");
            }

            // 2. Tạo mã học sinh mới
            $maHS = $this->taoMaHocSinhMoi($maLop, $thiSinh['namTuyenSinh']);

            // 3. Tạo tài khoản
            $maTaiKhoan = $this->taoTaiKhoanHocSinh($maHS, $thiSinh);

            // 4. Tạo học sinh
            $this->taoHocSinh($maHS, $maTaiKhoan, $thiSinh, $maLop);

            // 5. Cập nhật trạng thái nguyện vọng
            $this->capNhatTrangThaiNguyenVong($maThiSinh, 'TRUNG_TUYEN');

            $this->db->commit();
            return ['success' => true, 'message' => 'Xét tuyển thành công', 'maHS' => $maHS];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error xetTuyenThiSinh: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Xét tuyển hàng loạt
     */
    public function xetTuyenHangLoat($maTruong, $namTuyenSinh, $danhSachMaThiSinh) {
        $ketQua = ['thanh_cong' => 0, 'that_bai' => 0, 'chi_tiet' => []];

        // Lấy danh sách lớp 10 của trường
        $dsLop = $this->getDanhSachLop10($maTruong);
        if (empty($dsLop)) {
            return ['success' => false, 'message' => 'Trường chưa có lớp 10'];
        }

        foreach ($danhSachMaThiSinh as $maThiSinh) {
            // Chọn lớp có sĩ số ít nhất
            $maLop = $this->chonLopItSiSoNhat($dsLop);

            $result = $this->xetTuyenThiSinh($maThiSinh, $maLop);

            if ($result['success']) {
                $ketQua['thanh_cong']++;
                // Cập nhật sĩ số lớp trong danh sách tạm
                foreach ($dsLop as &$lop) {
                    if ($lop['maLop'] == $maLop) {
                        $lop['siSoHienTai']++;
                    }
                }
            } else {
                $ketQua['that_bai']++;
            }

            $ketQua['chi_tiet'][] = [
                'maThiSinh' => $maThiSinh,
                'result' => $result
            ];
        }

        return $ketQua;
    }

    /**
     * Tạo mã học sinh mới - Format: TR001HS240001
     */
    private function taoMaHocSinhMoi($maLop, $namTuyenSinh) {
        // Lấy mã trường từ mã lớp (giả sử: 10A1 → TR001)
        $maTruong = 'TR001'; // TODO: Lấy từ bảng lophoc

        // Lấy 2 chữ số cuối của năm
        $namCuoi2So = substr($namTuyenSinh, -2);

        // Đếm số học sinh hiện tại
        $sql = "SELECT COUNT(*) as total FROM hocsinh WHERE maHS LIKE ?";
        $stmt = $this->db->prepare($sql);
        $pattern = $maTruong . 'HS' . $namCuoi2So . '%';
        $stmt->execute([$pattern]);
        $count = $stmt->fetch()['total'];

        $stt = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return $maTruong . 'HS' . $namCuoi2So . $stt;
    }

    /**
     * Tạo tài khoản cho học sinh
     */
    private function taoTaiKhoanHocSinh($maHS, $thiSinh) {
        $maTaiKhoan = 'TKHS' . substr($maHS, -4); // TKHS0001
        $tenDangNhap = strtolower($maHS); // tr001hs240001
        $matKhau = password_hash($thiSinh['soCCCD'] ?? '123456', PASSWORD_DEFAULT);

        $sql = "INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, ngayTao)
                VALUES (?, ?, ?, ?, ?, 'ACTIVE', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $maTaiKhoan,
            $tenDangNhap,
            $matKhau,
            strtolower($maHS) . '@student.edu.vn',
            $thiSinh['soDienThoai'],
        ]);

        // Gán vai trò học sinh
        $sqlRole = "INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro) VALUES (?, 'hs')";
        $stmtRole = $this->db->prepare($sqlRole);
        $stmtRole->execute([$maTaiKhoan]);

        return $maTaiKhoan;
    }

    /**
     * Tạo bản ghi học sinh
     */
    private function taoHocSinh($maHS, $maTaiKhoan, $thiSinh, $maLop) {
        $sql = "INSERT INTO hocsinh (maHS, hoTen, ngaySinh, soCCCD, gioiTinh, sdt, email, maLop, trangThai, maTaiKhoan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'DANGHOC', ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $maHS,
            $thiSinh['hoTen'],
            $thiSinh['ngaySinh'],
            $thiSinh['soCCCD'],
            $thiSinh['gioiTinh'],
            $thiSinh['soDienThoai'],
            strtolower($maHS) . '@student.edu.vn',
            $maLop,
            $maTaiKhoan
        ]);
    }

    /**
     * Cập nhật trạng thái nguyện vọng
     */
    private function capNhatTrangThaiNguyenVong($maThiSinh, $trangThai) {
        $sql = "UPDATE nguyenvong SET trangThai = ? WHERE maThiSinh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$trangThai, $maThiSinh]);
    }

    /**
     * Lấy danh sách lớp 10 của trường
     */
    private function getDanhSachLop10($maTruong) {
        $sql = "SELECT 
                    lh.maLop,
                    lh.tenLop,
                    lh.siSo as siSoToiDa,
                    COUNT(hs.maHS) as siSoHienTai
                FROM lophoc lh
                LEFT JOIN hocsinh hs ON lh.maLop = hs.maLop
                WHERE lh.khoi = '10' 
                  AND lh.namHoc = ?
                GROUP BY lh.maLop
                HAVING siSoHienTai < lh.siSo
                ORDER BY siSoHienTai ASC";

        $namHoc = date('Y') . '-' . (date('Y') + 1);
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$namHoc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chọn lớp có sĩ số ít nhất
     */
    private function chonLopItSiSoNhat($dsLop) {
        if (empty($dsLop)) return null;

        usort($dsLop, function($a, $b) {
            return $a['siSoHienTai'] - $b['siSoHienTai'];
        });

        return $dsLop[0]['maLop'];
    }

    /**
     * Lấy thống kê xét tuyển
     */
    public function getThongKeXetTuyen($maTruong, $namTuyenSinh) {
        try {
            $sql = "SELECT 
                        COUNT(*) as tongSoThiSinh,
                        SUM(CASE WHEN nv.trangThai = 'TRUNG_TUYEN' THEN 1 ELSE 0 END) as soTrungTuyen,
                        SUM(CASE WHEN nv.trangThai = 'CHO_DUYET' THEN 1 ELSE 0 END) as soChuaXet,
                        SUM(CASE WHEN nv.trangThai = 'TRUOT' THEN 1 ELSE 0 END) as soTruot
                    FROM nguyenvong nv
                    INNER JOIN thisinh ts ON nv.maThiSinh = ts.maThiSinh
                    WHERE nv.maTruong = ? AND ts.namTuyenSinh = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maTruong, $namTuyenSinh]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getThongKeXetTuyen: " . $e->getMessage());
            return null;
        }
    }
}

<?php
require_once __DIR__ . '/../../config/database.php';

class DiemChuanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách điểm chuẩn theo năm
     */
    public function getDanhSachDiemChuan($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        dc.maDiemChuan,
                        dc.soDiem,
                        dc.namTuyenSinh,
                        dc.trangThai,
                        dc.ngayTao,
                        t.tenTruong,
                        t.maTruong,
                        COUNT(nv.maNguyenVong) as soNguyenVong
                    FROM diemchuan dc
                    INNER JOIN truong t ON dc.maTruong = t.maTruong
                    LEFT JOIN nguyenvong nv ON nv.maTruong = dc.maTruong 
                        AND YEAR(nv.ngayDangKy) = dc.namTuyenSinh
                    WHERE dc.namTuyenSinh = ?
                    GROUP BY dc.maDiemChuan
                    ORDER BY dc.trangThai, dc.ngayTao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namTuyenSinh]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getDanhSachDiemChuan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Công bố điểm chuẩn (Chuyển trạng thái)
     */
    public function congBoDiemChuan($maDiemChuan) {
        try {
            $this->db->beginTransaction();

            // Cập nhật trạng thái
            $sql = "UPDATE diemchuan 
                    SET trangThai = 'DA_CONG_BO', 
                        ngayCongBo = NOW() 
                    WHERE maDiemChuan = ? 
                      AND trangThai = 'CHUA_CONG_BO'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDiemChuan]);

            if ($stmt->rowCount() == 0) {
                throw new Exception('Điểm chuẩn không tồn tại hoặc đã được công bố');
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Công bố điểm chuẩn thành công'];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error congBoDiemChuan: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ✅ XÉT TUYỂN TỰ ĐỘNG THEO TRƯỜNG (Sau khi công bố)
     */
    public function xetTuyenTuDongTheoTruong($maDiemChuan) {
        try {
            $this->db->beginTransaction();

            // 1. Lấy thông tin điểm chuẩn
            $dcStmt = $this->db->prepare("
                SELECT maTruong, soDiem, namTuyenSinh 
                FROM diemchuan 
                WHERE maDiemChuan = ? AND trangThai = 'DA_CONG_BO'
            ");
            $dcStmt->execute([$maDiemChuan]);
            $diemChuan = $dcStmt->fetch(PDO::FETCH_ASSOC);

            if (!$diemChuan) {
                throw new Exception('Điểm chuẩn chưa được công bố');
            }

            // 2. Lấy danh sách thí sinh có nguyện vọng theo thứ tự ưu tiên
            $nvStmt = $this->db->prepare("
                SELECT 
                    nv.maNguyenVong,
                    nv.maThiSinh,
                    nv.thuTuUuTien,
                    ts.diemVan,
                    ts.diemToan,
                    ts.diemAnh,
                    (ts.diemVan * 2 + ts.diemToan * 2 + ts.diemAnh) as tongDiem
                FROM nguyenvong nv
                INNER JOIN thisinh ts ON nv.maThiSinh = ts.maThiSinh
                WHERE nv.maTruong = ?
                  AND ts.namTuyenSinh = ?
                  AND nv.trangThai = 'CHO_DUYET'
                ORDER BY nv.thuTuUuTien ASC, tongDiem DESC
            ");
            $nvStmt->execute([$diemChuan['maTruong'], $diemChuan['namTuyenSinh']]);
            $danhSachNV = $nvStmt->fetchAll(PDO::FETCH_ASSOC);

            $thongKe = ['dau' => 0, 'rot' => 0, 'khong_xet' => 0];

            foreach ($danhSachNV as $nv) {
                // 3. Kiểm tra xem thí sinh đã trúng tuyển NV trước chưa
                $checkStmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM nguyenvong
                    WHERE maThiSinh = ?
                      AND thuTuUuTien < ?
                      AND trangThai = 'TRUNG_TUYEN'
                ");
                $checkStmt->execute([$nv['maThiSinh'], $nv['thuTuUuTien']]);
                $daTrungTuyen = $checkStmt->fetch()['total'] > 0;

                // 4. Xét trạng thái
                if ($daTrungTuyen) {
                    // Đã trúng tuyển NV trước → Không xét
                    $trangThaiMoi = 'KHONG_XET';
                    $thongKe['khong_xet']++;
                } else if ($nv['tongDiem'] >= $diemChuan['soDiem']) {
                    // Đạt điểm chuẩn → Trúng tuyển
                    $trangThaiMoi = 'TRUNG_TUYEN';
                    $thongKe['dau']++;
                } else {
                    // Không đạt → Trượt
                    $trangThaiMoi = 'TRUOT';
                    $thongKe['rot']++;
                }

                // 5. Cập nhật trạng thái nguyện vọng
                $updateStmt = $this->db->prepare("
                    UPDATE nguyenvong 
                    SET trangThai = ?, ngayXetTuyen = NOW()
                    WHERE maNguyenVong = ?
                ");
                $updateStmt->execute([$trangThaiMoi, $nv['maNguyenVong']]);
            }

            $this->db->commit();

            $message = sprintf(
                'Xét tuyển hoàn tất: Đậu %d - Rớt %d - Không xét %d',
                $thongKe['dau'],
                $thongKe['rot'],
                $thongKe['khong_xet']
            );

            return ['success' => true, 'message' => $message, 'thongKe' => $thongKe];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error xetTuyenTuDongTheoTruong: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi xét tuyển: ' . $e->getMessage()];
        }
    }

    /**
     * Từ chối điểm chuẩn
     */
    public function tuChoiDiemChuan($maDiemChuan, $lyDo) {
        try {
            $sql = "UPDATE diemchuan 
                    SET trangThai = 'TU_CHOI', 
                        lyDoTuChoi = ?,
                        ngayTuChoi = NOW()
                    WHERE maDiemChuan = ? 
                      AND trangThai = 'CHUA_CONG_BO'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$lyDo, $maDiemChuan]);

            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'message' => 'Không thể từ chối điểm chuẩn'];
            }

            return ['success' => true, 'message' => 'Từ chối điểm chuẩn thành công'];

        } catch (PDOException $e) {
            error_log("Error tuChoiDiemChuan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * Thống kê điểm chuẩn
     */
    public function getThongKeDiemChuan($namTuyenSinh) {
        try {
            $sql = "SELECT 
                        COUNT(*) as tongSo,
                        SUM(CASE WHEN trangThai = 'CHUA_CONG_BO' THEN 1 ELSE 0 END) as chuaCongBo,
                        SUM(CASE WHEN trangThai = 'DA_CONG_BO' THEN 1 ELSE 0 END) as daCongBo,
                        SUM(CASE WHEN trangThai = 'TU_CHOI' THEN 1 ELSE 0 END) as tuChoi
                    FROM diemchuan
                    WHERE namTuyenSinh = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$namTuyenSinh]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error getThongKeDiemChuan: " . $e->getMessage());
            return null;
        }
    }
}

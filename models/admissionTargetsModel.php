<?php
require_once __DIR__ . '/../config/database.php';

class AdmissionTargetsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /* ================== Helpers ================== */

    private function fetchAll($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    private function exec($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /* ================== Năm học & Trường ================== */

    // Lấy danh sách các năm học
    public function loadNamHoc() {
        $defaults = ['2026-2027','2025-2026','2024-2025','2023-2024'];

        try {
            $rows = $this->fetchAll("
                SELECT DISTINCT namHoc 
                FROM LopHoc 
                WHERE namHoc IS NOT NULL 
                ORDER BY namHoc DESC
            ");
            $namHocList = array_column($rows, 'namHoc');
            $namHocList = array_values(array_unique(array_merge($namHocList, $defaults)));
            rsort($namHocList);
            return $namHocList;
        } catch (PDOException $e) {
            return $defaults;
        }
    }

    // Lấy danh sách tất cả các trường THPT
    public function getDanhSachTruong() {
        try {
            return $this->fetchAll("
                SELECT maTruong, tenTruong, diaChi 
                FROM Truong 
                ORDER BY tenTruong ASC
            ");
        } catch (PDOException $e) {
            return [];
        }
    }

    /* ================== Nhân viên Sở ================== */

    // Lấy mã nhân viên sở từ username
    public function getMaNhanVienSoByUsername($username) {
        try {
            $tk = $this->fetchOne("
                SELECT maTaiKhoan 
                FROM TaiKhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ", [$username]);

            if (!$tk) return null;

            $nv = $this->fetchOne("
                SELECT maNhanVienSo 
                FROM NhanVienSo 
                WHERE maTaiKhoan = ?
                LIMIT 1
            ", [$tk['maTaiKhoan']]);

            return $nv['maNhanVienSo'] ?? null;
        } catch (PDOException $e) {
            error_log("Error getMaNhanVienSoByUsername: " . $e->getMessage());
            return null;
        }
    }

    /* ================== Chỉ tiêu theo năm ================== */

    // Lấy chỉ tiêu tuyển sinh đã phân bổ cho các trường theo năm học
    public function getChiTieuTheoNamHoc($namHoc) {
        try {
            return $this->fetchAll("
                SELECT 
                    maChiTieu,
                    maTruong,
                    tenTruong,
                    chiTieuPhanBo,
                    ngayBanHanh,
                    phanTramPhanBo,
                    tongChiTieu
                FROM viewChiTieu
                WHERE namHoc = ?
                ORDER BY tenTruong ASC
            ", [$namHoc]);
        } catch (PDOException $e) {
            error_log("Error getChiTieuTheoNamHoc: " . $e->getMessage());
            return [];
        }
    }

    // Lấy tổng chỉ tiêu phê duyệt của năm học
    // 2023-2024: cố định 2000, năm khác: lấy DB / 5% tăng / fallback 180 × số trường
    public function getTongPheDuyet($namHoc) {
        if ($namHoc === '2023-2024') return 2000;

        // Lấy nếu có trong DB
        $row = $this->fetchOne("
            SELECT tongChiTieu 
            FROM ChiTieuTuyenSinh 
            WHERE namHoc = ? 
            LIMIT 1
        ", [$namHoc]);

        if (!empty($row['tongChiTieu'])) {
            return (int)$row['tongChiTieu'];
        }

        // Lấy năm trước gần nhất và tăng 5%
        $prev = $this->fetchOne("
            SELECT tongChiTieu 
            FROM ChiTieuTuyenSinh 
            WHERE namHoc < ?
            ORDER BY namHoc DESC 
            LIMIT 1
        ", [$namHoc]);

        if (!empty($prev['tongChiTieu'])) {
            return (int) round($prev['tongChiTieu'] * 1.05);
        }

        // Fallback: 180 × số trường
        return count($this->getDanhSachTruong()) * 180;
    }

    // Lấy tổng chỉ tiêu đã phân bổ cho năm học
    public function getTongChiTieuDaPhanBo($namHoc) {
        try {
            $row = $this->fetchOne("
                SELECT SUM(chiTieuPhanBo) as tongDaPhanBo
                FROM ChiTieuTuyenSinh
                WHERE namHoc = ?
            ", [$namHoc]);
            return (int)($row['tongDaPhanBo'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getTongChiTieuDaPhanBo: " . $e->getMessage());
            return 0;
        }
    }

    /* ================== Gợi ý & năm trước ================== */

    // Tính gợi ý chỉ tiêu cho trường
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);

        if ($chiTieuNamTruoc == 0) {
            $tongChiTieu   = $this->getTongPheDuyet($namHoc);
            $soTruong      = count($this->getDanhSachTruong());
            return ($tongChiTieu > 0 && $soTruong > 0)
                ? (int) round($tongChiTieu / $soTruong)
                : 150;
        }

        return (int) round($chiTieuNamTruoc * 1.05);
    }

    // Lấy chỉ tiêu của trường trong năm trước
    private function getChiTieuNamTruoc($maTruong, $namHocHienTai) {
        try {
            $row = $this->fetchOne("
                SELECT chiTieuPhanBo 
                FROM ChiTieuTuyenSinh
                WHERE maTruong = ? AND namHoc < ?
                ORDER BY namHoc DESC 
                LIMIT 1
            ", [$maTruong, $namHocHienTai]);

            return (int)($row['chiTieuPhanBo'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getChiTieuNamTruoc: " . $e->getMessage());
            return 0;
        }
    }

    // Public wrapper cho controller
    public function getChiTieuNamTruocThucTe($maTruong, $namHocHienTai) {
        return $this->getChiTieuNamTruoc($maTruong, $namHocHienTai);
    }

    /* ================== Lưu / Xóa phân bổ ================== */

    // Lưu phân bổ chỉ tiêu tuyển sinh
    public function luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo) {
        try {
            $this->db->beginTransaction();

            $tongPheDuyet = $this->getTongPheDuyet($namHoc);
            $tongPhanBo   = array_sum($chiTieuData);

            if ($tongPhanBo > $tongPheDuyet && $tongPheDuyet > 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => "Tổng chỉ tiêu ($tongPhanBo) vượt quá phê duyệt ($tongPheDuyet)!"
                ];
            }

            // Xóa dữ liệu cũ của năm học
            $this->exec("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?", [$namHoc]);

            $stmtInsert = $this->db->prepare("
                INSERT INTO ChiTieuTuyenSinh 
                (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            // Kiểm tra mã NVS 1 lần
            $maNVS = null;
            if (!empty($maNhanVienSo) && $maNhanVienSo !== 'NVS_DEFAULT') {
                $check = $this->fetchOne("
                    SELECT maNhanVienSo 
                    FROM NhanVienSo 
                    WHERE maNhanVienSo = ?
                ", [$maNhanVienSo]);
                if (!empty($check['maNhanVienSo'])) {
                    $maNVS = $maNhanVienSo;
                }
            }

            list($namBD, $namKT) = explode('-', $namHoc);
            $prefix = 'CT' . substr($namBD, -2) . substr($namKT, -2);

            foreach ($chiTieuData as $maTruong => $soLuong) {
                if ($soLuong <= 0) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => "Chỉ tiêu cho trường $maTruong phải > 0!"
                    ];
                }

                $maChiTieu = $prefix . $maTruong;
                $stmtInsert->execute([
                    $maChiTieu,
                    $namHoc,
                    $tongPheDuyet,
                    $soLuong,
                    $maTruong,
                    $maNVS
                ]);
            }

            $this->db->commit();
            $this->guiThongBaoPhanBo($namHoc, $chiTieuData);

            return [
                'success' => true,
                'message' => "Phân bổ chỉ tiêu thành công cho năm học $namHoc!"
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error luuPhanBo: " . $e->getMessage());
            return ['success' => false, 'message' => "Lỗi: " . $e->getMessage()];
        }
    }

    // Gửi thông báo về chỉ tiêu cho các trường
    private function guiThongBaoPhanBo($namHoc, $chiTieuData) {
        // TODO: gửi email / tạo notification
        error_log("Đã phân bổ chỉ tiêu năm $namHoc cho " . count($chiTieuData) . " trường.");
    }

    // Xóa phân bổ chỉ tiêu theo năm học
    public function xoaPhanBoTheoNamHoc($namHoc) {
        try {
            $stmtDelete = $this->db->prepare("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?");
            $stmtDelete->execute([$namHoc]);
            $soXoa = $stmtDelete->rowCount();

            if ($soXoa == 0) {
                return ['success' => false, 'message' => "Không tìm thấy chỉ tiêu năm $namHoc!"];
            }

            return ['success' => true, 'message' => "✅ Đã xóa $soXoa chỉ tiêu năm học $namHoc!"];
        } catch (PDOException $e) {
            error_log("Error xoaPhanBoTheoNamHoc: " . $e->getMessage());
            return ['success' => false, 'message' => "Lỗi: " . $e->getMessage()];
        }
    }

    /* ================== Validate & Thống kê ================== */

    // Kiểm tra dữ liệu nhập có hợp lệ không
    public function kiemTraTongChiTieu($chiTieuData, $namHoc) {
        $errors       = [];
        $tongNhap     = array_sum($chiTieuData);
        $tongPheDuyet = $this->getTongPheDuyet($namHoc);

        if ($tongPheDuyet > 0 && $tongNhap != $tongPheDuyet) {
            $errors[] = "⚠️ Tổng nhập ($tongNhap) khác tổng phê duyệt ($tongPheDuyet)!";
        }

        foreach ($chiTieuData as $maTruong => $soLuong) {
            if (!is_numeric($soLuong)) {
                $errors[] = "❌ Trường $maTruong: phải là số!";
            } elseif ($soLuong < 0) {
                $errors[] = "❌ Trường $maTruong: không được âm ($soLuong)!";
            } elseif ($soLuong == 0) {
                $errors[] = "⚠️ Trường $maTruong: không được = 0!";
            } elseif (floor($soLuong) != $soLuong) {
                $errors[] = "⚠️ Trường $maTruong: phải là số nguyên!";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    // Tính tổng chỉ tiêu từ danh sách đã nhập
    public function tinhTongChiTieuDaNhap($chiTieuData) {
        return array_sum($chiTieuData);
    }

    // Lấy lịch sử phân bổ chỉ tiêu
    public function getLichSuPhanBo($limit = 10) {
        try {
            return $this->fetchAll("
                SELECT namHoc, ngayBanHanh,
                       COUNT(DISTINCT maTruong) as soTruong,
                       SUM(chiTieuPhanBo) as tongPhanBo,
                       MAX(tongChiTieu) as tongChiTieu
                FROM viewChiTieu
                WHERE maTruong IS NOT NULL
                GROUP BY namHoc, ngayBanHanh
                ORDER BY ngayBanHanh DESC
                LIMIT ?
            ", [$limit]);
        } catch (PDOException $e) {
            error_log("Error getLichSuPhanBo: " . $e->getMessage());
            return [];
        }
    }
}

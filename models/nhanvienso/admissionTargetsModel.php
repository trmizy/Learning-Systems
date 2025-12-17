<?php
require_once __DIR__ . '/../../config/database.php';

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
            // Nếu là năm cố định: LUÔN tự động tính phân bổ đều
            // Năm cố định tự động cập nhật
            if ($this->laNamCodinh($namHoc)) {
                return $this->sinhChiTieuTuDongChoNamCodinh($namHoc);
            }
            // Năm khác: lấy từ DB như bình thường
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
    // Kiểm tra xem có phải năm cố định không
    private function laNamCodinh($namHoc) {
        return $namHoc === '2023-2024';
    }
    // Tự động sinh chỉ tiêu phân bổ đều cho năm cố định
    private function sinhChiTieuTuDongChoNamCodinh($namHoc) {
        $danhSachTruong = $this->getDanhSachTruong();
        $tongChiTieu = 2000; // Tổng chỉ tiêu năm cố định
        $soTruong = count($danhSachTruong);
        
        if ($soTruong == 0) return [];
        
        $chiTieuMoiTruong = (int) floor($tongChiTieu / $soTruong);
        $chiTieuDu = $tongChiTieu - ($chiTieuMoiTruong * $soTruong);
        
        $result = [];
        list($namBD, $namKT) = explode('-', $namHoc);
        $prefix = 'CT' . substr($namBD, -2) . substr($namKT, -2);
        
        foreach ($danhSachTruong as $index => $truong) {
            $chiTieu = $chiTieuMoiTruong;
            // Phân phối số dư cho các trường đầu tiên
            if ($index < $chiTieuDu) {
                $chiTieu++;
            }
            
            $result[] = [
                'maChiTieu' => $prefix . $truong['maTruong'],
                'maTruong' => $truong['maTruong'],
                'tenTruong' => $truong['tenTruong'],
                'chiTieuPhanBo' => $chiTieu,
                'ngayBanHanh' => null,
                'phanTramPhanBo' => round(($chiTieu / $tongChiTieu) * 100, 2),
                'tongChiTieu' => $tongChiTieu
            ];
        }
        
        return $result;
    }
    // Lấy tổng chỉ tiêu phê duyệt của năm học
    // 2023-2024: cố định 2000, năm khác: lấy DB / 5% tăng / fallback 180 × số trường
    public function getTongPheDuyet($namHoc) {
        if ($this->laNamCodinh($namHoc)) return 2000;
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
        // Tính tổng chỉ tiêu dựa trên năm trước (đệ quy hoặc tính dây chuyền)
        list($namBatDau, $namKetThuc) = explode('-', $namHoc);
        $namBatDauInt = (int)$namBatDau;
        // Tìm trong database trước
        $allYears = $this->fetchAll("
            SELECT DISTINCT tongChiTieu, namHoc
            FROM ChiTieuTuyenSinh 
            ORDER BY namHoc DESC
        ");
        // Tìm năm gần nhất trong DB
        foreach ($allYears as $year) {
            list($namBD, $namKT) = explode('-', $year['namHoc']);
            $namBDInt = (int)$namBD;
            if ($namBDInt < $namBatDauInt) {
                $tongChiTieuNamTruoc = (int)$year['tongChiTieu'];
                return (int) round($tongChiTieuNamTruoc * 1.05);
            }
        }
        // Nếu không tìm thấy trong DB, tính từ năm cố định 2023-2024
        if ($namBatDauInt > 2023) {
            // Tính dây chuyền từ năm 2023-2024 (= 2000)
            $chiTieuGoc = 2000; // Năm cố định 2023-2024
            $soNamCachBiet = $namBatDauInt - 2023;
            // Mỗi năm tăng 5%: 2000 * (1.05)^n
            $tongChiTieu = $chiTieuGoc * pow(1.05, $soNamCachBiet);
            return (int) round($tongChiTieu);
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
            
            $tongDB = (int)($row['tongDaPhanBo'] ?? 0);
            // Nếu là năm cố định và chưa có trong DB, trả về tổng phê duyệt
            if ($this->laNamCodinh($namHoc) && $tongDB == 0) {
                return $this->getTongPheDuyet($namHoc);
            }
            return $tongDB;
        } catch (PDOException $e) {
            error_log("Error getTongChiTieuDaPhanBo: " . $e->getMessage());
            return 0;
        }
    }
    /* ================== Gợi ý & năm trước ================== */
    // Tính gợi ý chỉ tiêu cho trường
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        $chiTieuNamTruoc = $this->getChiTieuNamTruocThucTe($maTruong, $namHoc);
        if ($chiTieuNamTruoc > 0) return (int) round($chiTieuNamTruoc * 1.05);

        $chiTieuTrungBinh = $this->getTrungBinhChiTieu($namHoc);
        if ($chiTieuTrungBinh > 0) return $chiTieuTrungBinh;

        $tongChiTieu = $this->getTongPheDuyet($namHoc);
        $soTruong = count($this->getDanhSachTruong());
        
        return ($tongChiTieu > 0 && $soTruong > 0) 
            ? (int) round($tongChiTieu / $soTruong) 
            : 150;
    }
    // Tính trung bình chỉ tiêu các trường
    private function getTrungBinhChiTieu($namHoc) {
        try {
            // Lấy từ năm cố định nếu >= 2024-2025
            if ($namHoc >= '2024-2025') {
                $chiTieuCodinh = $this->sinhChiTieuTuDongChoNamCodinh('2023-2024');
                if (!empty($chiTieuCodinh)) {
                    $tong = array_sum(array_column($chiTieuCodinh, 'chiTieuPhanBo'));
                    return (int) round($tong / count($chiTieuCodinh));
                }
            }    
            // Lấy từ DB
            $row = $this->fetchOne("
                SELECT AVG(chiTieuPhanBo) as tb
                FROM ChiTieuTuyenSinh
                WHERE namHoc < ? AND chiTieuPhanBo > 0
                ORDER BY namHoc DESC
                LIMIT 1
            ", [$namHoc]);

            return (int)($row['tb'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
    // Lấy chỉ tiêu năm trước của trường (public cho controller)
    public function getChiTieuNamTruocThucTe($maTruong, $namHoc) {
        try {
            // Lấy từ DB
            $row = $this->fetchOne("
                SELECT chiTieuPhanBo 
                FROM ChiTieuTuyenSinh
                WHERE maTruong = ? AND namHoc < ?
                ORDER BY namHoc DESC LIMIT 1
            ", [$maTruong, $namHoc]);
            
            $chiTieu = (int)($row['chiTieuPhanBo'] ?? 0);
            
            // Fallback: lấy từ năm cố định nếu >= 2024-2025
            if ($chiTieu === 0 && $namHoc >= '2024-2025') {
                $chiTieuCodinh = $this->sinhChiTieuTuDongChoNamCodinh('2023-2024');
                foreach ($chiTieuCodinh as $ct) {
                    if ($ct['maTruong'] === $maTruong) {
                        return (int)$ct['chiTieuPhanBo'];
                    }
                }
            }
            return $chiTieu;
        } catch (PDOException $e) {
            return 0;
        }
    }
    /* ================== Lưu / Xóa phân bổ ================== */
    public function luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo) {
        try {
            $this->db->beginTransaction();

            $tongPheDuyet = $this->getTongPheDuyet($namHoc);
            $tongPhanBo = array_sum($chiTieuData);

            if ($tongPhanBo > $tongPheDuyet && $tongPheDuyet > 0) {
                throw new Exception("Tổng chỉ tiêu ($tongPhanBo) vượt quá phê duyệt ($tongPheDuyet)!");
            }

            // Xóa dữ liệu cũ
            $this->exec("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?", [$namHoc]);

            // Validate mã nhân viên sở
            $maNVS = (!empty($maNhanVienSo) && $maNhanVienSo !== 'NVS_DEFAULT' 
                && $this->fetchOne("SELECT 1 FROM NhanVienSo WHERE maNhanVienSo = ?", [$maNhanVienSo]))
                ? $maNhanVienSo : null;

            list($namBD, $namKT) = explode('-', $namHoc);
            $prefix = 'CT' . substr($namBD, -2) . substr($namKT, -2);

            $stmt = $this->db->prepare("
                INSERT INTO ChiTieuTuyenSinh 
                (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($chiTieuData as $maTruong => $soLuong) {
                if ($soLuong <= 0) throw new Exception("Chỉ tiêu trường $maTruong phải > 0!");
                
                $stmt->execute([
                    $prefix . $maTruong, $namHoc, $tongPheDuyet, 
                    $soLuong, $maTruong, $maNVS
                ]);
            }

            $this->db->commit();
            error_log("Đã phân bổ chỉ tiêu $namHoc cho " . count($chiTieuData) . " trường.");

            return ['success' => true, 'message' => "Phân bổ chỉ tiêu thành công cho năm học $namHoc!"];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function xoaPhanBoTheoNamHoc($namHoc) {
        try {
            $stmt = $this->db->prepare("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?");
            $stmt->execute([$namHoc]);
            $soXoa = $stmt->rowCount();

            return $soXoa > 0
                ? ['success' => true, 'message' => "Đã xóa $soXoa chỉ tiêu năm học $namHoc!"]
                : ['success' => false, 'message' => "Không tìm thấy chỉ tiêu năm $namHoc!"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Lỗi: " . $e->getMessage()];
        }
    }
    /* ================== Validate & Thống kê ================== */
    public function kiemTraTongChiTieu($chiTieuData, $namHoc) {
        $errors = [];
        $tongNhap = array_sum($chiTieuData);
        $tongPheDuyet = $this->getTongPheDuyet($namHoc);
        if ($tongPheDuyet > 0 && $tongNhap != $tongPheDuyet) {
            $errors[] = 'Tổng nhập (' . $tongNhap . ') khác tổng phê duyệt (' . $tongPheDuyet . ')!';
        }
        foreach ($chiTieuData as $maTruong => $soLuong) {
            if (!is_numeric($soLuong)) {
                $errors[] = 'Trường ' . $maTruong . ': phải là số!';
            } elseif ($soLuong < 0) {
                $errors[] = 'Trường ' . $maTruong . ': không được âm!';
            } elseif ($soLuong == 0) {
                $errors[] = 'Trường ' . $maTruong . ': không được = 0!';
            } elseif (floor($soLuong) != $soLuong) {
                $errors[] = 'Trường ' . $maTruong . ': phải là số nguyên!';
            }
        }
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    public function tinhTongChiTieuDaNhap($chiTieuData) {
        return array_sum($chiTieuData);
    }
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
            return [];
        }
    }
}
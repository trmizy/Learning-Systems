<?php
require_once __DIR__ . '/../config/database.php';

class AdmissionTargetsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các năm học có trong hệ thống
     */
    public function loadNamHoc() {
        try {
            $sql = "SELECT DISTINCT namHoc FROM LopHoc WHERE namHoc IS NOT NULL ORDER BY namHoc DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $namHocList = $stmt->fetchAll(PDO::FETCH_COLUMN);
            // Bổ sung các năm dự phòng nếu cần
            $defaults = ['2026-2027','2025-2026','2024-2025','2023-2024'];
            foreach ($defaults as $nh) {
                if (!in_array($nh, $namHocList)) $namHocList[] = $nh;
            }
            rsort($namHocList);
            return $namHocList;
        } catch (PDOException $e) {
            return ['2026-2027','2025-2026','2024-2025','2023-2024'];
        }
    }

    /**
     * Lấy danh sách tất cả các trường THPT
     */
    public function getDanhSachTruong() {
        try {
            $sql = "SELECT maTruong, tenTruong, diaChi FROM Truong ORDER BY tenTruong ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy mã nhân viên sở từ username (tenDangNhap)
     * @param string $username - Tên đăng nhập từ session
     * @return string|null - maNhanVienSo hoặc NULL nếu không tìm thấy
     */
    public function getMaNhanVienSoByUsername($username) {
        try {
            // Bước 1: Lấy maTaiKhoan từ TaiKhoan
            $stmt = $this->db->prepare("
                SELECT maTaiKhoan 
                FROM TaiKhoan 
                WHERE tenDangNhap = ? AND trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $taiKhoan = $stmt->fetch();
            
            if (!$taiKhoan) {
                return null;
            }
            
            // Bước 2: Lấy maNhanVienSo từ bảng NhanVienSo
            $stmt2 = $this->db->prepare("
                SELECT maNhanVienSo 
                FROM NhanVienSo 
                WHERE maTaiKhoan = ?
                LIMIT 1
            ");
            $stmt2->execute([$taiKhoan['maTaiKhoan']]);
            $nhanVien = $stmt2->fetch();
            
            return $nhanVien ? $nhanVien['maNhanVienSo'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaNhanVienSoByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy chỉ tiêu tuyển sinh đã phân bổ cho các trường theo năm học
     * ✅ TỐI ƯU: Sử dụng viewChiTieu thay vì JOIN thủ công
     */
    public function getChiTieuTheoNamHoc($namHoc) {
        try {
            $stmt = $this->db->prepare("
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
            ");
            $stmt->execute([$namHoc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getChiTieuTheoNamHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tổng chỉ tiêu tuyển sinh cho năm học (được phê duyệt)
     * - Năm 2023-2024: Lấy từ database (cố định = 2000)
     * - Các năm sau: Tự động tính bằng thuật toán
     */
    /**
     * Lấy tổng chỉ tiêu phê duyệt của năm học
     * Đơn giản: 
     * - 2023-2024: Cố định 2000 (năm đã khóa)
     * - Năm khác: Lấy từ DB, nếu chưa có thì 180 × số trường
     */
    public function getTongPheDuyet($namHoc) {
        // Năm cơ sở
        if ($namHoc === '2023-2024') return 2000;

        // Lấy nếu có trong DB
        $stmt = $this->db->prepare("SELECT tongChiTieu FROM ChiTieuTuyenSinh WHERE namHoc = ? LIMIT 1");
        $stmt->execute([$namHoc]);
        $row = $stmt->fetch();
        if ($row && $row['tongChiTieu'] > 0) return (int)$row['tongChiTieu'];

        // Nếu chưa có dữ liệu cho năm này -> thử lấy tổng năm trước gần nhất và tăng 5%
        $stmt2 = $this->db->prepare("SELECT tongChiTieu FROM ChiTieuTuyenSinh WHERE namHoc < ? ORDER BY namHoc DESC LIMIT 1");
        $stmt2->execute([$namHoc]);
        $prev = $stmt2->fetch();
        if ($prev && $prev['tongChiTieu'] > 0) return (int) round($prev['tongChiTieu'] * 1.05);

        // Không có năm trước -> fallback: 180 × số trường
        return count($this->getDanhSachTruong()) * 180;
    }

    /**
     * Lấy tổng chỉ tiêu đã phân bổ cho năm học
     */
    public function getTongChiTieuDaPhanBo($namHoc) {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(chiTieuPhanBo) as tongDaPhanBo
                FROM ChiTieuTuyenSinh
                WHERE namHoc = ?
            ");
            $stmt->execute([$namHoc]);
            $result = $stmt->fetch();
            return $result ? (int)$result['tongDaPhanBo'] : 0;
        } catch (PDOException $e) {
            error_log("Error getTongChiTieuDaPhanBo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tính gợi ý chỉ tiêu cho trường
     * 
     * Thuật toán đơn giản: Chỉ tiêu năm trước × 1.05 (tăng 5%)
     * Nếu không có dữ liệu năm trước → Chia đều
     */
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        // Lấy chỉ tiêu năm trước thực tế
        $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);
        if ($chiTieuNamTruoc == 0) {
            $tongChiTieu = $this->getTongPheDuyet($namHoc);
            $danhSachTruong = $this->getDanhSachTruong();
            return ($tongChiTieu > 0 && count($danhSachTruong) > 0) ? (int) round($tongChiTieu / count($danhSachTruong)) : 150;
        }

        // Có dữ liệu -> tăng 5% (không làm các bước làm tròn theo 50 hay giới hạn)
        return (int) round($chiTieuNamTruoc * 1.05);
    }

    
    /**
     * Lấy chỉ tiêu của trường trong năm trước (ưu tiên 2023-2024)
     */
    /**
     * Lấy chỉ tiêu của trường trong năm trước
     */
    private function getChiTieuNamTruoc($maTruong, $namHocHienTai) {
        try {
            $stmt = $this->db->prepare("
                SELECT chiTieuPhanBo FROM ChiTieuTuyenSinh
                WHERE maTruong = ? AND namHoc < ?
                ORDER BY namHoc DESC LIMIT 1
            ");
            $stmt->execute([$maTruong, $namHocHienTai]);
            $result = $stmt->fetch();
            return $result ? (int)$result['chiTieuPhanBo'] : 0;
        } catch (PDOException $e) {
            error_log("Error getChiTieuNamTruoc: " . $e->getMessage());
            return 0;
        }
    }

    // Public wrapper: lấy chỉ tiêu năm trước (dùng cho controller)
    public function getChiTieuNamTruocThucTe($maTruong, $namHocHienTai) {
        return $this->getChiTieuNamTruoc($maTruong, $namHocHienTai);
    }


    /**
     * Lưu phân bổ chỉ tiêu tuyển sinh vào database
     */
    public function luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo) {
        try {
            $this->db->beginTransaction();

            // Kiểm tra tổng không vượt quá phê duyệt
            $tongPheDuyet = $this->getTongPheDuyet($namHoc);
            $tongPhanBo = array_sum($chiTieuData);
            
            if ($tongPhanBo > $tongPheDuyet && $tongPheDuyet > 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => "Tổng chỉ tiêu ($tongPhanBo) vượt quá phê duyệt ($tongPheDuyet)!"
                ];
            }

            // Xóa dữ liệu cũ của năm học
            $this->db->prepare("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?")->execute([$namHoc]);

            // Thêm dữ liệu mới
            $stmtInsert = $this->db->prepare("
                INSERT INTO ChiTieuTuyenSinh 
                (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($chiTieuData as $maTruong => $soLuong) {
                if ($soLuong <= 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => "Chỉ tiêu cho trường $maTruong phải > 0!"];
                }

                // Tạo mã: CT + năm (2 số) + mã trường. VD: CT2324TR001
                list($namBD, $namKT) = explode('-', $namHoc);
                $maChiTieu = 'CT' . substr($namBD, -2) . substr($namKT, -2) . $maTruong;
                
                // Kiểm tra mã nhân viên sở có tồn tại
                $maNVS = null;
                if (!empty($maNhanVienSo) && $maNhanVienSo !== 'NVS_DEFAULT') {
                    $check = $this->db->prepare("SELECT maNhanVienSo FROM NhanVienSo WHERE maNhanVienSo = ?");
                    $check->execute([$maNhanVienSo]);
                    if ($check->fetch()) {
                        $maNVS = $maNhanVienSo;
                    }
                }
                
                $stmtInsert->execute([$maChiTieu, $namHoc, $tongPheDuyet, $soLuong, $maTruong, $maNVS]);
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

    /**
     * Gửi thông báo về chỉ tiêu cho các trường
     */
    private function guiThongBaoPhanBo($namHoc, $chiTieuData) {
        // TODO: Có thể gửi email, tạo thông báo trong hệ thống
        error_log("Đã phân bổ chỉ tiêu năm $namHoc cho " . count($chiTieuData) . " trường.");
    }

    /**
     * Kiểm tra dữ liệu nhập vào có hợp lệ không
     */
    public function kiemTraTongChiTieu($chiTieuData, $namHoc) {
        $errors = [];

        // Kiểm tra tổng khớp với phê duyệt
        $tongNhap = array_sum($chiTieuData);
        $tongPheDuyet = $this->getTongPheDuyet($namHoc);
        
        if ($tongNhap != $tongPheDuyet && $tongPheDuyet > 0) {
            $errors[] = "⚠️ Tổng nhập ($tongNhap) khác tổng phê duyệt ($tongPheDuyet)!";
        }

        foreach ($chiTieuData as $maTruong => $soLuong) {
            if (!is_numeric($soLuong)) {
                $errors[] = "❌ Trường $maTruong: phải là số!";
            }
            elseif ($soLuong < 0) {
                $errors[] = "❌ Trường $maTruong: không được âm ($soLuong)!";
            }
            elseif ($soLuong == 0) {
                $errors[] = "⚠️ Trường $maTruong: không được = 0!";
            }
            elseif (floor($soLuong) != $soLuong) {
                $errors[] = "⚠️ Trường $maTruong: phải là số nguyên!";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Tính tổng chỉ tiêu từ danh sách đã nhập
     */
    public function tinhTongChiTieuDaNhap($chiTieuData) {
        return array_sum($chiTieuData);
    }

    /**
     * Lấy lịch sử phân bổ chỉ tiêu (sử dụng viewChiTieu)
     */
    public function getLichSuPhanBo($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT namHoc, ngayBanHanh,
                       COUNT(DISTINCT maTruong) as soTruong,
                       SUM(chiTieuPhanBo) as tongPhanBo,
                       MAX(tongChiTieu) as tongChiTieu
                FROM viewChiTieu
                WHERE maTruong IS NOT NULL
                GROUP BY namHoc, ngayBanHanh
                ORDER BY ngayBanHanh DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getLichSuPhanBo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Xóa phân bổ chỉ tiêu theo năm học
     */
    public function xoaPhanBoTheoNamHoc($namHoc) {
        try {
            // Kiểm tra năm học có dữ liệu không
            $stmt = $this->db->prepare("SELECT COUNT(*) as soLuong FROM ChiTieuTuyenSinh WHERE namHoc = ?");
            $stmt->execute([$namHoc]);
            $result = $stmt->fetch();
            
            if ($result['soLuong'] == 0) {
                return ['success' => false, 'message' => "Không tìm thấy chỉ tiêu năm $namHoc!"];
            }

            // Xóa tất cả chỉ tiêu của năm học
            $stmtDelete = $this->db->prepare("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?");
            $stmtDelete->execute([$namHoc]);
            $soXoa = $stmtDelete->rowCount();

            return ['success' => true, 'message' => "✅ Đã xóa $soXoa chỉ tiêu năm học $namHoc!"];

        } catch (PDOException $e) {
            error_log("Error xoaPhanBoTheoNamHoc: " . $e->getMessage());
            return ['success' => false, 'message' => "Lỗi: " . $e->getMessage()];
        }
    }
}
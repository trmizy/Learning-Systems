<?php
require_once __DIR__ . '/../config/database.php';

class AdmissionTargetsModel {
    private $db;
    private $cache = []; // Thêm cache để giảm query

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các năm học có trong hệ thống
     */
    public function loadNamHoc() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT namHoc 
                FROM LopHoc 
                WHERE namHoc IS NOT NULL 
                ORDER BY namHoc DESC
            ");
            $stmt->execute();
            $namHocList = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Thêm các năm học bổ sung nếu chưa có
            $namHocBoSung = ['2026-2027', '2025-2026', '2024-2025', '2023-2024'];
            foreach ($namHocBoSung as $nh) {
                if (!in_array($nh, $namHocList)) {
                    $namHocList[] = $nh;
                }
            }
            
            // Sắp xếp giảm dần
            rsort($namHocList);
            
            return $namHocList;
        } catch (PDOException $e) {
            error_log("Error loadNamHoc: " . $e->getMessage());
            // Trả về danh sách mặc định nếu lỗi
            return ['2026-2027', '2025-2026', '2024-2025', '2023-2024'];
        }
    }

    /**
     * Lấy danh sách tất cả các trường THPT - CACHE
     */
    public function getDanhSachTruong() {
        if (isset($this->cache['danhSachTruong'])) {
            return $this->cache['danhSachTruong'];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT maTruong, tenTruong, diaChi, email, soDienThoai
                FROM Truong
                ORDER BY tenTruong ASC
            ");
            $stmt->execute();
            $this->cache['danhSachTruong'] = $stmt->fetchAll();
            return $this->cache['danhSachTruong'];
        } catch (PDOException $e) {
            error_log("Error getDanhSachTruong: " . $e->getMessage());
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
     */
    public function getChiTieuTheoNamHoc($namHoc) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.maChiTieu,
                    c.maTruong,
                    t.tenTruong,
                    c.chiTieuPhanBo,
                    c.ngayBanHanh
                FROM ChiTieuTuyenSinh c
                INNER JOIN Truong t ON c.maTruong = t.maTruong
                WHERE c.namHoc = ?
                ORDER BY t.tenTruong ASC
            ");
            $stmt->execute([$namHoc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getChiTieuTheoNamHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tổng chỉ tiêu tuyển sinh cho năm học - TỐI ƯU
     */
    public function getTongPheDuyet($namHoc) {
        // Cache key
        $cacheKey = 'tongPheDuyet_' . $namHoc;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT tongChiTieu
                FROM ChiTieuTuyenSinh
                WHERE namHoc = ?
                LIMIT 1
            ");
            $stmt->execute([$namHoc]);
            $result = $stmt->fetch();
            $tongChiTieu = $result ? (int)$result['tongChiTieu'] : 0;
            
            // Năm 2023-2024 CỐ ĐỊNH
            if ($namHoc === '2023-2024') {
                $tongChiTieu = $tongChiTieu > 0 ? $tongChiTieu : 8000;
                $this->cache[$cacheKey] = $tongChiTieu;
                return $tongChiTieu;
            }
            
            // Các năm khác: tự động tính nếu chưa có
            if ($tongChiTieu <= 0) {
                $tongChiTieu = $this->tinhTongChiTieuTuDong($namHoc);
            }
            
            $this->cache[$cacheKey] = $tongChiTieu;
            return $tongChiTieu;
        } catch (PDOException $e) {
            error_log("Error getTongPheDuyet: " . $e->getMessage());
            return $namHoc === '2023-2024' ? 8000 : 2000;
        }
    }
    
    /**
     * Thuật toán tự động tính TỔNG chỉ tiêu - ĐƠN GIẢN
     * 
     * Công thức: Tổng năm trước × (1 + Tỷ lệ thay đổi tổng học sinh)
     * 
     * Logic đơn giản:
     * - Nếu tổng HS tăng 10% → Tổng chỉ tiêu tăng 10%
     * - Nếu tổng HS giảm 5% → Tổng chỉ tiêu giảm 5%
     */
    private function tinhTongChiTieuTuDong($namHoc) {
        try {
            // Bước 1: Lấy tổng chỉ tiêu năm trước
            $tongNamTruoc = $this->getTongChiTieuNamTruocGanNhat($namHoc);
            $soTruong = count($this->getDanhSachTruong());
            
            // Nếu không có dữ liệu năm trước → Ước tính theo số trường
            if ($tongNamTruoc == 0) {
                // Mặc định mỗi trường 180 chỉ tiêu (phù hợp với 2,000 cho 11 trường)
                $tongChiTieu = $soTruong * 180;
            } else {
                // Bước 2: Lấy tổng học sinh hiện tại và năm trước
                $tongHocSinhHienTai = $this->getTongSoHocSinhHienTai();
                $tongHocSinhNamTruoc = $this->getTongHocSinhNamTruoc();
                
                // Bước 3: Tính tỷ lệ thay đổi tổng học sinh
                $tyLeThayDoi = 0.05; // Mặc định tăng 5%
                
                if ($tongHocSinhNamTruoc > 0) {
                    $tyLeThayDoi = ($tongHocSinhHienTai - $tongHocSinhNamTruoc) / $tongHocSinhNamTruoc;
                    
                    // Giới hạn thay đổi: -20% đến +30%
                    $tyLeThayDoi = max(-0.20, min(0.30, $tyLeThayDoi));
                }
                
                // Bước 4: Tính tổng chỉ tiêu mới
                $tongChiTieu = round($tongNamTruoc * (1 + $tyLeThayDoi));
            }
            
            // Bước 5: Làm tròn đến bội số 100 (dễ quản lý cấp sở)
            $tongChiTieu = round($tongChiTieu / 100) * 100;
            
            // Bước 6: Giới hạn hợp lý theo số trường
            $min = $soTruong * 100;   // Tối thiểu 100/trường
            $max = $soTruong * 500;   // Tối đa 500/trường
            $tongChiTieu = max($min, min($max, $tongChiTieu));
            
            return $tongChiTieu;
            
        } catch (PDOException $e) {
            error_log("Error tinhTongChiTieuTuDong: " . $e->getMessage());
            // Fallback: Trả về giá trị mặc định
            $soTruong = 11; // 11 trường Hà Nội
            return $soTruong * 180; // 1,980 ≈ 2,000
        }
    }
    
    /**
     * Ước tính tổng học sinh năm trước (giảm 10% so với hiện tại)
     */
    private function getTongHocSinhNamTruoc() {
        try {
            $tongHienTai = $this->getTongSoHocSinhHienTai();
            return round($tongHienTai * 0.9); // Năm trước ít hơn 10%
        } catch (PDOException $e) {
            error_log("Error getTongHocSinhNamTruoc: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lấy tổng số học sinh hiện tại trong toàn hệ thống
     */
    private function getTongSoHocSinhHienTai() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as tongHS
                FROM HocSinh
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? (int)$result['tongHS'] : 0;
        } catch (PDOException $e) {
            error_log("Error getTongSoHocSinhHienTai: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy tổng chỉ tiêu đã phân bổ - TỐI ƯU với CACHE
     */
    public function getTongChiTieuDaPhanBo($namHoc) {
        $cacheKey = 'tongDaPhanBo_' . $namHoc;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(chiTieuPhanBo), 0) as tongDaPhanBo
                FROM ChiTieuTuyenSinh
                WHERE namHoc = ?
            ");
            $stmt->execute([$namHoc]);
            $result = $stmt->fetch();
            $total = $result ? (int)$result['tongDaPhanBo'] : 0;
            
            $this->cache[$cacheKey] = $total;
            return $total;
        } catch (PDOException $e) {
            error_log("Error getTongChiTieuDaPhanBo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tính gợi ý chỉ tiêu - ĐƠN GIẢN HÓA
     */
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        try {
            // Lấy chỉ tiêu năm trước
            $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);
            
            if ($chiTieuNamTruoc > 0) {
                // Tăng nhẹ 5% so với năm trước
                $goiY = round($chiTieuNamTruoc * 1.05);
            } else {
                // Chia đều tổng chỉ tiêu
                $tongChiTieu = $this->getTongPheDuyet($namHoc);
                $soTruong = count($this->getDanhSachTruong());
                $goiY = $soTruong > 0 ? round($tongChiTieu / $soTruong) : 150;
            }
            
            // Làm tròn xuống bội số 10
            $goiY = round($goiY / 10) * 10;
            
            // Giới hạn 50-500
            return max(50, min(500, $goiY));
            
        } catch (PDOException $e) {
            error_log("Error tinhGoiYChiTieu: " . $e->getMessage());
            return 150;
        }
    }
    
    /**
     * Lấy số học sinh của trường trong năm trước
     * Ước tính: Số học sinh hiện tại - 10% (do tốt nghiệp)
     */
    private function getHocSinhNamTruoc($maTruong) {
        try {
            // Trong thực tế nên lưu lịch sử số HS vào bảng riêng
            // Hiện tại ước tính: năm trước ít hơn 10% (đã có học sinh tốt nghiệp)
            $hocSinhHienTai = $this->getSoLuongHocSinhHienTai($maTruong);
            return round($hocSinhHienTai * 0.9);
        } catch (PDOException $e) {
            error_log("Error getHocSinhNamTruoc: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lấy chỉ tiêu của trường trong năm trước (ưu tiên 2023-2024)
     */
    private function getChiTieuNamTruoc($maTruong, $namHocHienTai) {
        try {
            // Tìm năm học trước đó
            $stmt = $this->db->prepare("
                SELECT chiTieuPhanBo
                FROM ChiTieuTuyenSinh
                WHERE maTruong = ?
                AND namHoc < ?
                ORDER BY namHoc DESC
                LIMIT 1
            ");
            $stmt->execute([$maTruong, $namHocHienTai]);
            $result = $stmt->fetch();
            
            return $result ? (int)$result['chiTieuPhanBo'] : 0;
        } catch (PDOException $e) {
            error_log("Error getChiTieuNamTruoc: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Tính tỷ lệ tăng trưởng chỉ tiêu giữa năm mới và năm trước
     * Công thức: (TongChiTieuMoi - TongChiTieuTruoc) / TongChiTieuTruoc
     */
    private function getTyLeTangTruongChiTieu($namHocHienTai) {
        try {
            $tongChiTieuMoi = $this->getTongPheDuyet($namHocHienTai);
            $tongChiTieuTruoc = $this->getTongChiTieuNamTruocGanNhat($namHocHienTai);
            
            if ($tongChiTieuTruoc > 0 && $tongChiTieuMoi > 0) {
                $tyLe = ($tongChiTieuMoi - $tongChiTieuTruoc) / $tongChiTieuTruoc;
                // Giới hạn tỷ lệ tăng trưởng trong khoảng -20% đến +20%
                return max(min($tyLe, 0.20), -0.20);
            }
            
            return 0; // Không có tăng trưởng
        } catch (PDOException $e) {
            error_log("Error getTyLeTangTruongChiTieu: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lấy tổng chỉ tiêu của năm trước gần nhất
     */
    private function getTongChiTieuNamTruocGanNhat($namHocHienTai) {
        try {
            $stmt = $this->db->prepare("
                SELECT MAX(tongChiTieu) as tongChiTieu
                FROM ChiTieuTuyenSinh
                WHERE namHoc < ?
                LIMIT 1
            ");
            $stmt->execute([$namHocHienTai]);
            $result = $stmt->fetch();
            
            return $result ? (int)$result['tongChiTieu'] : 0;
        } catch (PDOException $e) {
            error_log("Error getTongChiTieuNamTruocGanNhat: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lấy số lượng học sinh hiện tại của trường
     */
    private function getSoLuongHocSinhHienTai($maTruong) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as soLuongHS
                FROM HocSinh hs
                INNER JOIN TaiKhoan tk ON hs.maTaiKhoan = tk.maTaiKhoan
                WHERE tk.maTruong = ?
            ");
            $stmt->execute([$maTruong]);
            $result = $stmt->fetch();
            
            return $result ? (int)$result['soLuongHS'] : 0;
        } catch (PDOException $e) {
            error_log("Error getSoLuongHocSinhHienTai: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Đánh giá năng lực trường dựa trên nhiều yếu tố
     * Trả về giá trị từ -0.2 đến +0.2 (điều chỉnh ±20%)
     * 
     * Các yếu tố:
     * - Tỷ lệ lớp/phòng học (quá tải hay dư thừa)
     * - Số lượng giáo viên
     * - Kết quả học tập năm trước
     */
    private function getNangLucTruong($maTruong) {
        try {
            // Đếm số lớp và số phòng (giả định)
            // Trong thực tế, cần có logic phức tạp hơn
            
            // Placeholder: Trả về giá trị trung bình (0)
            // Có thể mở rộng với logic phức tạp hơn
            
            return 0; // Giá trị trung bình
        } catch (PDOException $e) {
            error_log("Error getNangLucTruong: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lưu phân bổ chỉ tiêu tuyển sinh
     * @param string $namHoc
     * @param array $chiTieuData - Mảng ['maTruong' => soLuong]
     * @param string $maNhanVienSo
     * @return array ['success' => bool, 'message' => string]
     */
    public function luuPhanBo($namHoc, $chiTieuData, $maNhanVienSo) {
        try {
            $this->db->beginTransaction();

            // Tính tổng
            $tongPhanBo = array_sum($chiTieuData);
            $tongChiTieuPheDuyet = $this->getTongPheDuyet($namHoc);

            // Xóa dữ liệu cũ
            $stmtDelete = $this->db->prepare("DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?");
            $stmtDelete->execute([$namHoc]);

            // Insert hàng loạt
            $stmtInsert = $this->db->prepare("
                INSERT INTO ChiTieuTuyenSinh 
                (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($chiTieuData as $maTruong => $soLuong) {
                if ($soLuong <= 0) continue;
                
                $maChiTieu = 'CT_' . str_replace('-', '', $namHoc) . '_' . $maTruong;
                $stmtInsert->execute([
                    $maChiTieu,
                    $namHoc,
                    $tongChiTieuPheDuyet,
                    (int)$soLuong,
                    $maTruong,
                    $maNhanVienSo
                ]);
            }

            $this->db->commit();
            $this->cache = [];

            return [
                'success' => true,
                'message' => "✓ Phân bổ thành công $tongPhanBo chỉ tiêu cho năm $namHoc!"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error luuPhanBo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Lỗi: " . $e->getMessage()
            ];
        }
    }

    /**
     * Lưu phân bổ PARTIAL - CHỈ CẬP NHẬT CÁC TRƯỜNG ĐƯỢC CHỌN
     * Sử dụng UPDATE nếu tồn tại, INSERT nếu mới
     */
    public function luuPhanBoPartial($namHoc, $chiTieuMoi, $chiTieuHoanChinh, $maNhanVienSo) {
        try {
            $this->db->beginTransaction();

            $tongChiTieuPheDuyet = $this->getTongPheDuyet($namHoc);
            $tongPhanBoMoi = array_sum($chiTieuHoanChinh);

            $warningMessage = '';
            if ($tongChiTieuPheDuyet > 0 && $tongPhanBoMoi > $tongChiTieuPheDuyet) {
                $warningMessage = " (⚠️ Tổng vượt: " . number_format($tongPhanBoMoi - $tongChiTieuPheDuyet) . ")";
            }

            foreach ($chiTieuMoi as $maTruong => $soLuong) {
                if ($soLuong <= 0) continue;
                
                $maChiTieu = 'CT_' . str_replace('-', '', $namHoc) . '_' . $maTruong;
                
                // Kiểm tra record đã tồn tại chưa
                $stmtCheck = $this->db->prepare("
                    SELECT maChiTieu FROM ChiTieuTuyenSinh WHERE maChiTieu = ?
                ");
                $stmtCheck->execute([$maChiTieu]);
                $exists = $stmtCheck->fetch();
                
                if ($exists) {
                    // UPDATE record cũ (GHI ĐÈ giá trị mới)
                    $stmtUpdate = $this->db->prepare("
                        UPDATE ChiTieuTuyenSinh 
                        SET chiTieuPhanBo = ?,
                            maNhanVienSo = ?,
                            ngayBanHanh = NOW()
                        WHERE maChiTieu = ?
                    ");
                    $stmtUpdate->execute([
                        (int)$soLuong,
                        $maNhanVienSo,
                        $maChiTieu
                    ]);
                } else {
                    // INSERT record mới
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO ChiTieuTuyenSinh 
                        (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtInsert->execute([
                        $maChiTieu,
                        $namHoc,
                        $tongChiTieuPheDuyet,
                        (int)$soLuong,
                        $maTruong,
                        $maNhanVienSo
                    ]);
                }
            }

            $this->db->commit();
            $this->cache = [];

            $soTruongCapNhat = count($chiTieuMoi);
            $tongCapNhat = array_sum($chiTieuMoi);

            return [
                'success' => true,
                'message' => "✓ Đã cập nhật cho $soTruongCapNhat trường (Tổng: " . number_format($tongCapNhat) . " chỉ tiêu)$warningMessage"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error luuPhanBoPartial: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Lỗi: " . $e->getMessage()
            ];
        }
    }

    /**
     * Kiểm tra tính hợp lệ của dữ liệu đầu vào
     */
    public function kiemTraTongChiTieu($chiTieuData, $namHoc) {
        $errors = [];
        
        foreach ($chiTieuData as $maTruong => $soLuong) {
            $soLuong = (int)$soLuong;
            
            if ($soLuong < 0) {
                $errors[] = "❌ Trường $maTruong: Không được nhập số âm!";
            } elseif ($soLuong == 0) {
                $errors[] = "⚠️ Trường $maTruong: Chỉ tiêu phải lớn hơn 0!";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Tính tổng chỉ tiêu đã nhập từ danh sách
     * @param array $chiTieuData - Mảng ['maTruong' => soLuong]
     * @return int Tổng chỉ tiêu
     */
    public function tinhTongChiTieuDaNhap($chiTieuData) {
        return array_sum($chiTieuData);
    }

    /**
     * Lấy lịch sử phân bổ chỉ tiêu
     */
    public function getLichSuPhanBo($limit = 10) {
    try {
        // Ép kiểu & ràng buộc phạm vi an toàn
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 10;
        if ($limit > 500) $limit = 500; // tránh query quá lớn

        $sql = "
            SELECT 
                c.namHoc,
                c.ngayBanHanh,
                COUNT(DISTINCT c.maTruong) AS soTruong,
                SUM(c.chiTieuPhanBo)      AS tongPhanBo,
                MAX(c.tongChiTieu)        AS tongChiTieu
            FROM ChiTieuTuyenSinh c
            WHERE c.maTruong IS NOT NULL
            GROUP BY c.namHoc, c.ngayBanHanh
            ORDER BY c.ngayBanHanh DESC
            LIMIT $limit
        ";

        // Không dùng placeholder cho LIMIT để tránh lỗi quote
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getLichSuPhanBo: " . $e->getMessage());
        return [];
    }
}

/**
 * Xóa toàn bộ phân bổ của 1 năm học (RESET)
 */
public function resetPhanBoTheoNam($namHoc) {
    try {
        // KHÔNG cho phép reset năm 2023-2024 (năm cơ sở)
        if ($namHoc === '2023-2024') {
            return [
                'success' => false,
                'message' => '❌ Không thể reset năm 2023-2024! Đây là năm cơ sở.'
            ];
        }

        $this->db->beginTransaction();

        // Đếm số bản ghi sẽ xóa
        $stmtCount = $this->db->prepare("
            SELECT COUNT(*) as total FROM ChiTieuTuyenSinh WHERE namHoc = ?
        ");
        $stmtCount->execute([$namHoc]);
        $count = $stmtCount->fetch()['total'];

        if ($count == 0) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => '⚠️ Không có dữ liệu phân bổ cho năm học ' . $namHoc
            ];
        }

        // Xóa toàn bộ phân bổ của năm học
        $stmtDelete = $this->db->prepare("
            DELETE FROM ChiTieuTuyenSinh WHERE namHoc = ?
        ");
        $stmtDelete->execute([$namHoc]);

        $this->db->commit();
        $this->cache = [];

        return [
            'success' => true,
            'message' => "✓ Đã reset phân bổ năm $namHoc ($count bản ghi đã xóa)"
        ];

    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error resetPhanBoTheoNam: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}

/**
 * Xóa 1 bản ghi lịch sử cụ thể (theo namHoc + ngayBanHanh)
 */
public function xoaLichSu($namHoc, $ngayBanHanh) {
    try {
        // KHÔNG cho phép xóa năm 2023-2024
        if ($namHoc === '2023-2024') {
            return [
                'success' => false,
                'message' => '❌ Không thể xóa lịch sử năm 2023-2024!'
            ];
        }

        $this->db->beginTransaction();

        // Xóa tất cả record có namHoc và ngayBanHanh khớp
        $stmtDelete = $this->db->prepare("
            DELETE FROM ChiTieuTuyenSinh 
            WHERE namHoc = ? AND ngayBanHanh = ?
        ");
        $stmtDelete->execute([$namHoc, $ngayBanHanh]);

        $rowsDeleted = $stmtDelete->rowCount();

        if ($rowsDeleted == 0) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => '⚠️ Không tìm thấy lịch sử cần xóa'
            ];
        }

        $this->db->commit();
        $this->cache = [];

        return [
            'success' => true,
            'message' => "✓ Đã xóa lịch sử phân bổ ($rowsDeleted bản ghi)"
        ];

    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error xoaLichSu: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ];
    }
}

}

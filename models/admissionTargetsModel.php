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
     * Lấy danh sách tất cả các trường THPT
     */
    public function getDanhSachTruong() {
        try {
            $stmt = $this->db->prepare("
                SELECT maTruong, tenTruong, diaChi, email, soDienThoai
                FROM Truong
                ORDER BY tenTruong ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
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
     * Lấy tổng chỉ tiêu tuyển sinh cho năm học (được phê duyệt)
     * - Năm 2023-2024: Lấy từ database (cố định = 8000)
     * - Các năm sau: Tự động tính bằng thuật toán
     */
    public function getTongPheDuyet($namHoc) {
        try {
            // Lấy 1 giá trị tongChiTieu duy nhất (không phải SUM)
            $stmt = $this->db->prepare("
                SELECT tongChiTieu
                FROM ChiTieuTuyenSinh
                WHERE namHoc = ?
                LIMIT 1
            ");
            $stmt->execute([$namHoc]);
            $result = $stmt->fetch();
            $tongChiTieu = $result ? (int)$result['tongChiTieu'] : 0;
            
            // Đặc biệt: Năm 2023-2024 CỐ ĐỊNH, không dùng thuật toán
            if ($namHoc === '2023-2024') {
                // Nếu database có giá trị → dùng (cố định = 8000)
                if ($tongChiTieu > 0) {
                    return $tongChiTieu;
                }
                // Nếu chưa có → trả về 8000
                return 8000;
            }
            
            // Các năm khác: Kiểm tra tính hợp lý
            $soTruong = count($this->getDanhSachTruong());
            $minHopLy = $soTruong * 500;  // Tối thiểu 500/trường
            
            // Nếu chưa có HOẶC không hợp lý, tự động tính
            if ($tongChiTieu <= 0 || $tongChiTieu < $minHopLy) {
                $tongChiTieu = $this->tinhTongChiTieuTuDong($namHoc);
            }
            
            return $tongChiTieu;
        } catch (PDOException $e) {
            error_log("Error getTongPheDuyet: " . $e->getMessage());
            // Năm 2023-2024 trả về cố định
            if ($namHoc === '2023-2024') {
                return 8000;
            }
            return $this->tinhTongChiTieuTuDong($namHoc);
        }
    }
    
    /**
     * Thuật toán tự động tính tổng chỉ tiêu dựa trên:
     * 1. Tổng chỉ tiêu năm trước (60%)
     * 2. Tổng số học sinh hiện tại / 3 (30%)
     * 3. Năng lực tổng thể hệ thống (10%)
     */
    private function tinhTongChiTieuTuDong($namHoc) {
        try {
            // Yếu tố 1: Lấy tổng chỉ tiêu năm trước (60%)
            $tongNamTruoc = $this->getTongChiTieuNamTruocGanNhat($namHoc);
            
            // Yếu tố 2: Tính dựa trên số học sinh hiện tại (30%)
            $tongHocSinhHienTai = $this->getTongSoHocSinhHienTai();
            $goiYTheoHocSinh = round($tongHocSinhHienTai / 3);
            
            // Yếu tố 3: Tính dựa trên số trường và năng lực (10%)
            $soTruong = count($this->getDanhSachTruong());
            $chiTieuTrungBinh = 1200; // Mỗi trường trung bình 1,200 học sinh/năm
            $goiYTheoTruong = $soTruong * $chiTieuTrungBinh;
            
            if ($tongNamTruoc > 0) {
                // Có dữ liệu năm trước: Kết hợp 3 yếu tố
                // Tăng trưởng tự nhiên 4% mỗi năm
                $tangTruongTuNhien = 1.04;
                
                $tongChiTieu = round(
                    ($tongNamTruoc * $tangTruongTuNhien) * 0.60 +  // 60% từ năm trước với tăng trưởng
                    $goiYTheoHocSinh * 0.30 +                       // 30% từ số HS hiện tại
                    $goiYTheoTruong * 0.10                          // 10% từ quy mô hệ thống
                );
            } else {
                // Không có dữ liệu năm trước: Dựa vào số HS và số trường
                $tongChiTieu = round(
                    $goiYTheoHocSinh * 0.70 +   // 70% từ số HS
                    $goiYTheoTruong * 0.30       // 30% từ quy mô hệ thống
                );
            }
            
            // Làm tròn đến bội số của 500 để dễ quản lý
            $tongChiTieu = round($tongChiTieu / 500) * 500;
            
            // Giới hạn hợp lý: 100 * số trường đến 2000 * số trường
            $min = $soTruong * 100;
            $max = $soTruong * 2000;
            $tongChiTieu = max(min($tongChiTieu, $max), $min);
            
            return $tongChiTieu;
            
        } catch (PDOException $e) {
            error_log("Error tinhTongChiTieuTuDong: " . $e->getMessage());
            // Fallback: Trả về giá trị mặc định
            $soTruong = 10; // Giá trị mặc định
            return $soTruong * 1200; // 12,000
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
     * Tính số gợi ý chỉ tiêu cho một trường dựa trên thuật toán phân bổ thông minh
     * 
     * Thuật toán: Weighted Average với nhiều yếu tố
     * 1. Chỉ tiêu năm trước (40% trọng số)
     * 2. Tỷ lệ tăng trưởng tổng chỉ tiêu (30% trọng số)
     * 3. Số lượng học sinh hiện tại (20% trọng số)
     * 4. Tỷ lệ phân bổ theo năng lực trường (10% trọng số)
     * 
     * @param string $maTruong Mã trường cần tính gợi ý
     * @param string $namHoc Năm học cần phân bổ
     * @return int Số chỉ tiêu gợi ý
     */
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        try {
            // === YẾU TỐ 1: Chỉ tiêu năm trước (2023-2024 hoặc năm gần nhất) ===
            $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);
            
            // === YẾU TỐ 2: Tỷ lệ tăng trưởng tổng chỉ tiêu ===
            $tyLeTangTruong = $this->getTyLeTangTruongChiTieu($namHoc);
            
            // === YẾU TỐ 3: Số lượng học sinh hiện tại ===
            $soLuongHS = $this->getSoLuongHocSinhHienTai($maTruong);
            
            // === YẾU TỐ 4: Năng lực trường (cơ sở vật chất, quy mô) ===
            $nangLucTruong = $this->getNangLucTruong($maTruong);
            
            // === TÍNH TOÁN GỢI Ý ===
            $goiY = 0;
            
            if ($chiTieuNamTruoc > 0) {
                // Trường hợp có dữ liệu năm trước
                // Công thức: ChiTieuNamTruoc * (1 + TyLeTangTruong)
                $goiY = round($chiTieuNamTruoc * (1 + $tyLeTangTruong));
                
                // Điều chỉnh dựa trên số lượng học sinh (20% trọng số)
                $tyLeSoLuongHS = $soLuongHS / 3; // Giả định 1/3 học sinh hiện tại
                $goiY = round($goiY * 0.8 + $tyLeSoLuongHS * 0.2);
                
                // Điều chỉnh dựa trên năng lực trường (10% trọng số)
                $goiY = round($goiY * (1 + $nangLucTruong * 0.1));
                
            } else {
                // Trường hợp không có dữ liệu năm trước
                // Sử dụng phương pháp phân bổ theo tỷ lệ
                $tongChiTieuMoi = $this->getTongPheDuyet($namHoc);
                $tongChiTieuTruoc = $this->getTongChiTieuNamTruocGanNhat($namHoc);
                $danhSachTruong = $this->getDanhSachTruong();
                
                if ($tongChiTieuMoi > 0 && count($danhSachTruong) > 0) {
                    // Phân bổ đều cho các trường
                    $goiYTrungBinh = round($tongChiTieuMoi / count($danhSachTruong));
                    
                    // Điều chỉnh dựa trên năng lực trường
                    $goiY = round($goiYTrungBinh * (1 + $nangLucTruong * 0.2));
                    
                    // Điều chỉnh dựa trên số lượng học sinh
                    if ($soLuongHS > 0) {
                        $tyLeSoLuongHS = $soLuongHS / 3;
                        $goiY = round($goiY * 0.7 + $tyLeSoLuongHS * 0.3);
                    }
                } else {
                    // Fallback: Sử dụng số lượng học sinh
                    $goiY = round($soLuongHS / 3);
                }
            }
            
            // Đảm bảo giá trị hợp lý: tối thiểu 100, tối đa 2000
            $goiY = max($goiY, 100);
            $goiY = min($goiY, 2000);
            
            // Làm tròn đến bội số của 50 để dễ quản lý
            $goiY = round($goiY / 50) * 50;
            
            return $goiY;
            
        } catch (PDOException $e) {
            error_log("Error tinhGoiYChiTieu: " . $e->getMessage());
            return 100;
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

            // 1. Kiểm tra tổng chỉ tiêu được phê duyệt
            $tongChiTieuPheDuyet = $this->getTongPheDuyet($namHoc);
            
            // 2. Tính tổng chỉ tiêu đang phân bổ
            $tongPhanBo = array_sum($chiTieuData);

            // 3. Kiểm tra vượt quá tổng chỉ tiêu
            if ($tongPhanBo > $tongChiTieuPheDuyet && $tongChiTieuPheDuyet > 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => "Tổng chỉ tiêu phân bổ ($tongPhanBo) vượt quá tổng chỉ tiêu được phê duyệt ($tongChiTieuPheDuyet)!"
                ];
            }

            // 4. Xóa dữ liệu cũ (nếu có) cho năm học này
            $stmtDelete = $this->db->prepare("
                DELETE FROM ChiTieuTuyenSinh 
                WHERE namHoc = ?
            ");
            $stmtDelete->execute([$namHoc]);

            // 5. Thêm dữ liệu phân bổ mới
            $stmtInsert = $this->db->prepare("
                INSERT INTO ChiTieuTuyenSinh 
                (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong, maNhanVienSo, ngayBanHanh)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($chiTieuData as $maTruong => $soLuong) {
                // Kiểm tra số lượng hợp lệ
                if ($soLuong <= 0) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => "Chỉ tiêu cho trường $maTruong phải lớn hơn 0!"
                    ];
                }

                $maChiTieu = 'CT_' . $namHoc . '_' . $maTruong;
                
                // Kiểm tra maNhanVienSo có tồn tại không, nếu không thì set NULL
                $maNVS = null;
                if (!empty($maNhanVienSo) && $maNhanVienSo !== 'NVS_DEFAULT') {
                    $checkNVS = $this->db->prepare("SELECT maNhanVienSo FROM NhanVienSo WHERE maNhanVienSo = ?");
                    $checkNVS->execute([$maNhanVienSo]);
                    if ($checkNVS->fetch()) {
                        $maNVS = $maNhanVienSo;
                    }
                }
                
                $stmtInsert->execute([
                    $maChiTieu,
                    $namHoc,
                    $tongChiTieuPheDuyet,
                    $soLuong,
                    $maTruong,
                    $maNVS,  // Có thể NULL
                ]);
            }

            $this->db->commit();

            // 6. Gửi thông báo cho các trường (có thể implement sau)
            $this->guiThongBaoPhanBo($namHoc, $chiTieuData);

            return [
                'success' => true,
                'message' => "Phân bổ chỉ tiêu tuyển sinh thành công cho năm học $namHoc!"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error luuPhanBo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Lỗi hệ thống: " . $e->getMessage()
            ];
        }
    }

    /**
     * Gửi thông báo về chỉ tiêu cho các trường
     * (Placeholder - có thể implement email/notification system sau)
     */
    private function guiThongBaoPhanBo($namHoc, $chiTieuData) {
        // TODO: Implement notification system
        // Có thể gửi email, lưu vào bảng thông báo, etc.
        error_log("Thông báo chỉ tiêu năm $namHoc đã được gửi đến " . count($chiTieuData) . " trường.");
    }

    /**
     * Kiểm tra tính hợp lệ của dữ liệu đầu vào
     */
    public function kiemTraTongChiTieu($chiTieuData, $namHoc) {
        $errors = [];

        // Kiểm tra tổng chỉ tiêu nhập vào
        $tongNhap = $this->tinhTongChiTieuDaNhap($chiTieuData);
        $tongPheDuyet = $this->getTongPheDuyet($namHoc);
        
        if ($tongNhap != $tongPheDuyet && $tongPheDuyet > 0) {
            $errors[] = "⚠️ Tổng chỉ tiêu đã nhập ($tongNhap) không khớp với tổng được phê duyệt ($tongPheDuyet)!";
        }

        foreach ($chiTieuData as $maTruong => $soLuong) {
            // Kiểm tra số lượng là số
            if (!is_numeric($soLuong)) {
                $errors[] = "❌ Chỉ tiêu cho trường $maTruong phải là số!";
            }
            // Kiểm tra số âm
            elseif ($soLuong < 0) {
                $errors[] = "❌ KHÔNG ĐƯỢC NHẬP SỐ ÂM! Chỉ tiêu cho trường $maTruong là $soLuong (số âm không hợp lệ)";
            }
            // Kiểm tra số 0
            elseif ($soLuong == 0) {
                $errors[] = "⚠️ Chỉ tiêu cho trường $maTruong không được bằng 0!";
            }
            // Kiểm tra không phải số thập phân
            elseif (floor($soLuong) != $soLuong) {
                $errors[] = "⚠️ Chỉ tiêu cho trường $maTruong phải là số nguyên (không được có số thập phân)!";
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
            $stmt = $this->db->prepare("
                SELECT 
                    c.namHoc,
                    c.ngayBanHanh,
                    COUNT(DISTINCT c.maTruong) as soTruong,
                    SUM(c.chiTieuPhanBo) as tongPhanBo,
                    MAX(c.tongChiTieu) as tongChiTieu
                FROM ChiTieuTuyenSinh c
                WHERE c.maTruong IS NOT NULL
                GROUP BY c.namHoc, c.ngayBanHanh
                ORDER BY c.ngayBanHanh DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getLichSuPhanBo: " . $e->getMessage());
            return [];
        }
    }
}

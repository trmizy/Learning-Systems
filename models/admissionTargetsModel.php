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
     * - Năm 2023-2024: Lấy từ database (cố định = 2000)
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
     * Tính gợi ý chỉ tiêu tuyển sinh cho trường - THUẬT TOÁN ĐơN GIẢN
     * 
     * Công thức: Chỉ tiêu năm trước × (1 + Tỷ lệ thay đổi học sinh)
     * 
     * Logic:
     * - Nếu học sinh tăng 20% → Chỉ tiêu tăng 20%
     * - Nếu học sinh giảm 10% → Chỉ tiêu giảm 10%
     * - Nếu không có dữ liệu → Chia đều
     * 
     * @param string $maTruong Mã trường cần tính gợi ý
     * @param string $namHoc Năm học cần phân bổ
     * @return int Số chỉ tiêu gợi ý
     */
    public function tinhGoiYChiTieu($maTruong, $namHoc) {
        try {
            // Bước 1: Lấy chỉ tiêu năm trước
            $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);
            
            // Nếu không có dữ liệu năm trước → Chia đều
            if ($chiTieuNamTruoc == 0) {
                $tongChiTieu = $this->getTongPheDuyet($namHoc);
                $danhSachTruong = $this->getDanhSachTruong();
                if ($tongChiTieu > 0 && count($danhSachTruong) > 0) {
                    $goiY = round($tongChiTieu / count($danhSachTruong));
                } else {
                    $goiY = 150; // Mặc định
                }
            } else {
                // Bước 2: Lấy số học sinh hiện tại và năm trước
                $hocSinhHienTai = $this->getSoLuongHocSinhHienTai($maTruong);
                $hocSinhNamTruoc = $this->getHocSinhNamTruoc($maTruong);
                
                // Bước 3: Tính tỷ lệ thay đổi học sinh
                $tyLeThayDoi = 0.05; // Mặc định tăng 5%
                
                if ($hocSinhNamTruoc > 0) {
                    $tyLeThayDoi = ($hocSinhHienTai - $hocSinhNamTruoc) / $hocSinhNamTruoc;
                    
                    // Giới hạn thay đổi: -20% đến +30%
                    $tyLeThayDoi = max(-0.20, min(0.30, $tyLeThayDoi));
                }
                
                // Bước 4: Tính chỉ tiêu mới dựa trên tỷ lệ thay đổi
                $goiY = round($chiTieuNamTruoc * (1 + $tyLeThayDoi));
            }
            
            // Bước 5: Làm tròn đến bội số 50
            $goiY = round($goiY / 50) * 50;
            
            // Bước 6: Giới hạn hợp lý (100-500)
            $goiY = max(100, min(500, $goiY));
            
            return $goiY;
            
        } catch (PDOException $e) {
            error_log("Error tinhGoiYChiTieu: " . $e->getMessage());
            return 150; // Giá trị mặc định
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

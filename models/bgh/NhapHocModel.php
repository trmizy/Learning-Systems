<?php
require_once __DIR__ . '/../../config/database.php';

class NhapHocModel {
    private $db;
    private $SI_SO_TOI_DA = 30; // Sĩ số tối đa mỗi lớp khối 10

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kiểm tra điều kiện nhập học
     */
    public function kiemTraDieuKienNhapHoc() {
        $errors = [];
        
        try {
            // 1. Kiểm tra có thí sinh đậu chưa nhập học không
            $stmt = $this->db->query("
                SELECT COUNT(*) 
                FROM thisinh ts
                INNER JOIN nguyenvong nv ON ts.maThiSinh = nv.maThiSinh
                WHERE nv.trangThai = 'DAU' 
                  AND (ts.daChuyenHocSinh = 0 OR ts.daChuyenHocSinh IS NULL)
            ");
            $soThiSinhDau = $stmt->fetchColumn();
            
            if ($soThiSinhDau == 0) {
                $errors[] = 'Không có thí sinh đậu nào chưa nhập học';
            }

            // 2. Kiểm tra có lớp khối 10 không - FIX: lop → lophoc
            $stmt = $this->db->query("SELECT COUNT(*) FROM lophoc WHERE khoi = '10'");
            $soLopKhoi10 = $stmt->fetchColumn();
            
            if ($soLopKhoi10 == 0) {
                $errors[] = 'Chưa tạo lớp khối 10 trong hệ thống';
            }

            return [
                'ready' => empty($errors),
                'errors' => $errors,
                'soThiSinhDau' => $soThiSinhDau ?? 0,
                'soLopKhoi10' => $soLopKhoi10 ?? 0
            ];
            
        } catch (PDOException $e) {
            error_log("Error kiemTraDieuKienNhapHoc: " . $e->getMessage());
            return ['ready' => false, 'errors' => ['Lỗi kiểm tra: ' . $e->getMessage()]];
        }
    }

    /**
     * ⚠️ THUẬT TOÁN CHÍNH: Nhập học tự động
     * 1. Lấy danh sách thí sinh đậu (ORDER BY diem DESC)
     * 2. Phân lớp tuần tự: Lớp 10A1 → 10A2 → ... → 10A5
     * 3. Mỗi lớp tối đa 30 học sinh
     * 4. Tạo tài khoản tự động (username = maHS, password mặc định)
     */
    public function chayNhapHocTuDong() {
        try {
            $this->db->beginTransaction();
            
            // BƯỚC 1: Lấy danh sách thí sinh đậu theo điểm GIẢM DẦN
            $stmt = $this->db->query("
                SELECT DISTINCT
                    ts.maThiSinh,
                    ts.hoTen,
                    ts.ngaySinh,
                    ts.gioiTinh,
                    ts.soCCCD,
                    ts.soDienThoai,
                    ts.diem,
                    nv.maTruong
                FROM thisinh ts
                INNER JOIN nguyenvong nv ON ts.maThiSinh = nv.maThiSinh
                WHERE nv.trangThai = 'DAU'
                  AND (ts.daChuyenHocSinh = 0 OR ts.daChuyenHocSinh IS NULL)
                ORDER BY ts.diem DESC
            ");
            $danhSachThiSinh = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // BƯỚC 2: Lấy danh sách lớp khối 10 - FIX: lop → lophoc
            $stmt = $this->db->query("
                SELECT maLop, tenLop, siSo
                FROM lophoc
                WHERE khoi = '10'
                ORDER BY maLop
            ");
            $danhSachLop = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($danhSachLop)) {
                throw new Exception("Không có lớp khối 10 để phân bổ");
            }
            
            // BƯỚC 3: Phân lớp thông minh
            $tongNhapHoc = 0;
            $lopIndex = 0;
            $siSoHienTai = [];
            
            foreach ($danhSachLop as $lop) {
                $siSoHienTai[$lop['maLop']] = (int)$lop['siSo'];
            }
            
            foreach ($danhSachThiSinh as $thiSinh) {
                $maLopPhanBo = null;
                $soLanThu = 0;
                
                while ($soLanThu < count($danhSachLop)) {
                    $lop = $danhSachLop[$lopIndex];
                    
                    if ($siSoHienTai[$lop['maLop']] < $this->SI_SO_TOI_DA) {
                        $maLopPhanBo = $lop['maLop'];
                        $siSoHienTai[$lop['maLop']]++;
                        break;
                    } else {
                        $lopIndex = ($lopIndex + 1) % count($danhSachLop);
                        $soLanThu++;
                    }
                }
                
                if (!$maLopPhanBo) {
                    // Tất cả lớp đều đầy
                    error_log("WARNING: Không thể phân lớp cho thí sinh " . $thiSinh['maThiSinh']);
                    continue;
                }
                
                // BƯỚC 4: Tạo mã học sinh (format: TR001HS24xxxx)
                $maHS = $this->taoMaHocSinh($thiSinh['maTruong']);
                
                // BƯỚC 5: Insert vào bảng hocsinh - BỎ diaChi và email
                $stmtHS = $this->db->prepare("
                    INSERT INTO hocsinh (
                        maHS, hoTen, ngaySinh, gioiTinh, soCCCD, 
                        sdt, maLop, trangThai
                    ) VALUES (
                        :maHS, :hoTen, :ngaySinh, :gioiTinh, :soCCCD,
                        :sdt, :maLop, 'DANGHOC'
                    )
                ");
                
                $stmtHS->execute([
                    'maHS' => $maHS,
                    'hoTen' => $thiSinh['hoTen'],
                    'ngaySinh' => $thiSinh['ngaySinh'],
                    'gioiTinh' => $thiSinh['gioiTinh'],
                    'soCCCD' => $thiSinh['soCCCD'],
                    'sdt' => $thiSinh['soDienThoai'],
                    'maLop' => $maLopPhanBo
                ]);
                
                // BƯỚC 6: Tạo tài khoản (username = maHS, password mặc định)
                $maTaiKhoan = $this->taoTaiKhoan($maHS, $thiSinh['hoTen']);
                
                // BƯỚC 7: Link tài khoản với học sinh
                $stmtUpdate = $this->db->prepare("
                    UPDATE hocsinh 
                    SET maTaiKhoan = :maTaiKhoan 
                    WHERE maHS = :maHS
                ");
                $stmtUpdate->execute([
                    'maTaiKhoan' => $maTaiKhoan,
                    'maHS' => $maHS
                ]);
                
                // BƯỚC 8: Đánh dấu thí sinh đã chuyển
                $stmtMark = $this->db->prepare("
                    UPDATE thisinh 
                    SET daChuyenHocSinh = 1 
                    WHERE maThiSinh = :maThiSinh
                ");
                $stmtMark->execute(['maThiSinh' => $thiSinh['maThiSinh']]);
                
                $lopIndex = ($lopIndex + 1) % count($danhSachLop);
                $tongNhapHoc++;
            }
            
            // BƯỚC 10: Cập nhật sĩ số lớp - FIX: lop → lophoc
            foreach ($siSoHienTai as $maLop => $siSo) {
                $stmtUpdateSiSo = $this->db->prepare("
                    UPDATE lophoc 
                    SET siSo = :siSo 
                    WHERE maLop = :maLop
                ");
                $stmtUpdateSiSo->execute([
                    'siSo' => $siSo,
                    'maLop' => $maLop
                ]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'tong_nhap_hoc' => $tongNhapHoc
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error chayNhapHocTuDong: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo mã học sinh tự động (format: TR001HS24xxxx)
     */
    private function taoMaHocSinh($maTruong) {
        // Lấy số thứ tự lớn nhất
        $stmt = $this->db->query("
            SELECT MAX(CAST(SUBSTRING(maHS, 11) AS UNSIGNED)) as max_stt
            FROM hocsinh
            WHERE maHS LIKE 'TR001HS24%'
        ");
        $result = $stmt->fetch();
        $nextStt = ($result['max_stt'] ?? 0) + 1;
        
        return sprintf('%sHS24%04d', $maTruong, $nextStt);
    }

    /**
     * Tạo tài khoản cho học sinh mới
     */
    private function taoTaiKhoan($maHS, $hoTen) {
        // Tạo mã tài khoản
        $maTaiKhoan = 'TK' . $maHS;
        
        // Username = maHS, Password mặc định = 123456
        $username = $maHS;
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
        
        // Insert vào bảng taikhoan
        $stmt = $this->db->prepare("
            INSERT INTO taikhoan (
                maTaiKhoan, tenDangNhap, matKhau, trangThai
            ) VALUES (
                :maTaiKhoan, :username, :password, 'ACTIVE'
            )
        ");
        
        $stmt->execute([
            'maTaiKhoan' => $maTaiKhoan,
            'username' => $username,
            'password' => $passwordHash
        ]);
        
        // Insert vào bảng taikhoan_vaitro (role = 'hs')
        $stmt = $this->db->prepare("
            INSERT INTO taikhoan_vaitro (maTaiKhoan, maVaiTro)
            VALUES (:maTaiKhoan, 'hs')
        ");
        
        $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);
        
        return $maTaiKhoan;
    }

    /**
     * Lấy danh sách thí sinh đậu (chưa nhập học)
     */
    public function getDanhSachThiSinhDau() {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT
                    ts.maThiSinh,
                    ts.hoTen,
                    ts.diem,
                    ts.soDienThoai,
                    t.tenTruong,
                    nv.thuTuUuTien
                FROM thisinh ts
                INNER JOIN nguyenvong nv ON ts.maThiSinh = nv.maThiSinh
                INNER JOIN truong t ON nv.maTruong = t.maTruong
                WHERE nv.trangThai = 'DAU'
                  AND (ts.daChuyenHocSinh = 0 OR ts.daChuyenHocSinh IS NULL)
                ORDER BY ts.diem DESC
                LIMIT 100
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachThiSinhDau: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thống kê lớp khối 10
     */
    public function getThongKeLopKhoi10() {
        try {
            // FIX: lop → lophoc
            $stmt = $this->db->query("
                SELECT 
                    maLop,
                    tenLop,
                    siSo,
                    (30 - siSo) as choTrong
                FROM lophoc
                WHERE khoi = '10'
                ORDER BY maLop
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getThongKeLopKhoi10: " . $e->getMessage());
            return [];
        }
    }
}

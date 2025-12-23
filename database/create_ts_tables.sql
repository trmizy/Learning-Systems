-- ============================================
-- TẠO BẢNG CHO MODULE THÍ SINH (ts)
-- ============================================

-- Bảng thí sinh (nếu chưa có)
CREATE TABLE IF NOT EXISTS `thisinh` (
  `maThiSinh` VARCHAR(30) PRIMARY KEY,
  `hoTen` VARCHAR(100) NOT NULL,
  `ngaySinh` DATE NOT NULL,
  `gioiTinh` ENUM('Nam', 'Nu') NOT NULL,
  `soCCCD` VARCHAR(20) UNIQUE NOT NULL,
  `email` VARCHAR(100),
  `soDienThoai` VARCHAR(15),
  `diaChi` TEXT,
  `diaDiemThi` VARCHAR(200),
  `phongThi` VARCHAR(50),
  `maTaiKhoan` VARCHAR(50),
  `trangThai` ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
  `ngayTao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan`(`maTaiKhoan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng điểm thi THPT Quốc gia
CREATE TABLE IF NOT EXISTS `diemthi` (
  `maDiemThi` VARCHAR(30) PRIMARY KEY,
  `maThiSinh` VARCHAR(30) NOT NULL,
  `maMonHoc` VARCHAR(20) NOT NULL,
  `diem` DECIMAL(4,2) CHECK (`diem` BETWEEN 0 AND 10),
  `namThi` VARCHAR(10) NOT NULL,
  `ghiChu` TEXT,
  `ngayCapNhat` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`maThiSinh`) REFERENCES `thisinh`(`maThiSinh`) ON DELETE CASCADE,
  FOREIGN KEY (`maMonHoc`) REFERENCES `monhoc`(`maMonHoc`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng nguyện vọng
CREATE TABLE IF NOT EXISTS `nguyenvong` (
  `maNguyenVong` VARCHAR(30) PRIMARY KEY,
  `maThiSinh` VARCHAR(30) NOT NULL,
  `maTruong` VARCHAR(30) NOT NULL,
  `maNganh` VARCHAR(30),
  `thuTuUuTien` INT NOT NULL CHECK (`thuTuUuTien` BETWEEN 1 AND 3),
  `trangThai` ENUM('CHO_DUYET', 'DA_DUYET', 'TU_CHOI') DEFAULT 'CHO_DUYET',
  `ngayDangKy` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`maThiSinh`) REFERENCES `thisinh`(`maThiSinh`) ON DELETE CASCADE,
  UNIQUE KEY `unique_thisinh_thutu` (`maThiSinh`, `thuTuUuTien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng hồ sơ thí sinh
CREATE TABLE IF NOT EXISTS `hosothisinh` (
  `maHoSo` VARCHAR(30) PRIMARY KEY,
  `maThiSinh` VARCHAR(30) NOT NULL,
  `loaiHoSo` VARCHAR(100) NOT NULL,
  `duongDanFile` VARCHAR(255),
  `trangThai` ENUM('CHUA_NOP', 'DA_NOP', 'DUYET', 'TU_CHOI') DEFAULT 'CHUA_NOP',
  `ngayNop` DATETIME,
  `ngayCapNhat` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`maThiSinh`) REFERENCES `thisinh`(`maThiSinh`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dữ liệu mẫu (nếu cần test)
INSERT IGNORE INTO `thisinh` (`maThiSinh`, `hoTen`, `ngaySinh`, `gioiTinh`, `soCCCD`, `email`, `diaDiemThi`, `phongThi`, `maTaiKhoan`) 
VALUES 
('TS2025001', 'Nguyễn Văn A', '2007-01-15', 'Nam', '001207000001', 'nva@gmail.com', 'THPT Nguyễn Huệ', 'P101', NULL);

INSERT IGNORE INTO `diemthi` (`maDiemThi`, `maThiSinh`, `maMonHoc`, `diem`, `namThi`) 
VALUES 
('DT001', 'TS2025001', 'TOAN', 8.5, '2025'),
('DT002', 'TS2025001', 'NGUVAN', 7.0, '2025'),
('DT003', 'TS2025001', 'ANH', 9.0, '2025');

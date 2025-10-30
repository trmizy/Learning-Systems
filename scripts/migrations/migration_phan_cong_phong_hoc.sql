-- Tạo bảng phân công phòng học theo năm học
CREATE TABLE IF NOT EXISTS PhanCongPhongHoc (
    maPhanCong VARCHAR(50) PRIMARY KEY,
    maPhong VARCHAR(20) NOT NULL,
    maLop VARCHAR(20) NOT NULL,
    namHoc VARCHAR(15) NOT NULL,
    ngayPhanCong DATETIME DEFAULT CURRENT_TIMESTAMP,
    ghiChu VARCHAR(255),
    UNIQUE KEY unique_phong_nam (maPhong, namHoc),
    UNIQUE KEY unique_lop_nam (maLop, namHoc),
    INDEX idx_phong (maPhong),
    INDEX idx_lop (maLop),
    INDEX idx_nam_hoc (namHoc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

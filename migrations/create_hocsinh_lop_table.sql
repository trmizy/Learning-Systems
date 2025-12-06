-- =========================================================
-- Migration: Tạo bảng HocSinh_Lop (Liên kết Học sinh - Lớp học)
-- Mục đích: Lưu lịch sử học sinh học ở lớp nào theo từng năm học
-- File: migrations/create_hocsinh_lop_table.sql
-- =========================================================

USE HeThongQuanLyHocSinh1;

-- Tạo bảng HocSinh_Lop
CREATE TABLE IF NOT EXISTS HocSinh_Lop (
    maHS VARCHAR(20) NOT NULL,
    maLop VARCHAR(20) NOT NULL,
    namHoc VARCHAR(15) NOT NULL,
    ngayVaoLop DATE,
    trangThai VARCHAR(20) DEFAULT 'ACTIVE',
    PRIMARY KEY (maHS, maLop, namHoc),
    FOREIGN KEY (maHS) REFERENCES HocSinh(maHS) ON DELETE CASCADE,
    FOREIGN KEY (maLop) REFERENCES LopHoc(maLop) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index để tăng tốc query
CREATE INDEX idx_hocsinh_lop_namhoc ON HocSinh_Lop(namHoc);
CREATE INDEX idx_hocsinh_lop_trangthai ON HocSinh_Lop(trangThai);

-- Comment
ALTER TABLE HocSinh_Lop COMMENT = 'Bảng liên kết học sinh với lớp học theo năm học';

-- =========================================================
-- Dữ liệu mẫu (tùy chọn - chỉ để test)
-- =========================================================
-- INSERT INTO HocSinh_Lop (maHS, maLop, namHoc, ngayVaoLop, trangThai) VALUES
-- ('HS0000', '12A1', '2024-2025', '2024-09-05', 'ACTIVE');

SELECT 'Tạo bảng HocSinh_Lop thành công!' as message;

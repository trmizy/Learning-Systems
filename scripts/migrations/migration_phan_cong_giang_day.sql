-- =========================================================
-- Bảng phân công giảng dạy (GV bộ môn cho lớp - môn học)
-- =========================================================

CREATE TABLE IF NOT EXISTS PhanCongGiangDay (
  maPhanCong VARCHAR(50) PRIMARY KEY,
  maLop VARCHAR(20) NOT NULL,
  maMonHoc VARCHAR(20) NOT NULL,
  maGV VARCHAR(20) NOT NULL,
  namHoc VARCHAR(15),
  hocKy VARCHAR(10),
  ghiChu VARCHAR(255),
  ngayPhanCong DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (maLop) REFERENCES LopHoc(maLop) ON DELETE CASCADE,
  FOREIGN KEY (maMonHoc) REFERENCES MonHoc(maMonHoc) ON DELETE CASCADE,
  FOREIGN KEY (maGV) REFERENCES GiaoVienBoMon(maGV) ON DELETE CASCADE,
  UNIQUE KEY UK_PhanCong (maLop, maMonHoc, namHoc, hocKy)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index để tăng tốc query
CREATE INDEX IX_PhanCongGiangDay_maLop ON PhanCongGiangDay(maLop);
CREATE INDEX IX_PhanCongGiangDay_maGV ON PhanCongGiangDay(maGV);
CREATE INDEX IX_PhanCongGiangDay_maMonHoc ON PhanCongGiangDay(maMonHoc);

-- =========================================================
-- SEED DATA: Môn học mẫu cho THPT
-- =========================================================

INSERT INTO MonHoc (maMonHoc, tenMon, soTietTuan, loaiMonHoc, hocKy, namHoc, moTa) VALUES
  ('TOAN', 'Toán', 5, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Toán học'),
  ('VAN', 'Ngữ văn', 4, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Ngữ văn'),
  ('ANH', 'Tiếng Anh', 3, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Tiếng Anh'),
  ('LY', 'Vật lý', 3, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Vật lý'),
  ('HOA', 'Hóa học', 3, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Hóa học'),
  ('SINH', 'Sinh học', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Sinh học'),
  ('SU', 'Lịch sử', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Lịch sử'),
  ('DIA', 'Địa lý', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Địa lý'),
  ('GDCD', 'Giáo dục công dân', 1, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn GDCD'),
  ('TD', 'Thể dục', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Thể dục'),
  ('QP', 'Quốc phòng', 1, 'Chính khóa', 'Cả năm', '2024-2025', 'Giáo dục quốc phòng'),
  ('TIN', 'Tin học', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Tin học')
ON DUPLICATE KEY UPDATE 
  tenMon=VALUES(tenMon), 
  soTietTuan=VALUES(soTietTuan),
  loaiMonHoc=VALUES(loaiMonHoc),
  hocKy=VALUES(hocKy),
  namHoc=VALUES(namHoc),
  moTa=VALUES(moTa);

-- Cập nhật monHocPhuTrach cho các giáo viên
UPDATE GiaoVienBoMon SET monHocPhuTrach = 'Toán' WHERE maGV IN ('GV_TR001_01', 'GV_TR001_03');
UPDATE GiaoVienBoMon SET monHocPhuTrach = 'Văn' WHERE maGV = 'GV_TR001_02';
UPDATE GiaoVienBoMon SET monHocPhuTrach = 'Lý' WHERE maGV = 'GV_TR001_04';
UPDATE GiaoVienBoMon SET monHocPhuTrach = 'Hóa' WHERE maGV = 'GV_TR001_05';
UPDATE GiaoVienBoMon SET monHocPhuTrach = 'Sinh' WHERE maGV = 'GV_TR001_06';

-- Thêm giáo viên cho các môn còn lại
INSERT INTO GiaoVienBoMon (maGV, hoTen, ngaySinh, gioiTinh, email, soDienThoai, diaChi, monHocPhuTrach, trinhDoHocVan, chucVu, soCCCD, tinhTrangTaiKhoan, maTaiKhoan) VALUES
  ('GV_TR001_07', 'Trần Văn G', '1985-07-10', 'Nam', 'tranvang@test.edu.vn', '0900000011', 'Hà Nội', 'Anh', 'ThS', 'GVBM', '014455667788', 'ACTIVE', NULL),
  ('GV_TR001_08', 'Phạm Thị H', '1986-08-15', 'Nữ', 'phamthih@test.edu.vn', '0900000012', 'Hà Nội', 'Sử', 'ThS', 'GVBM', '015566778899', 'ACTIVE', NULL),
  ('GV_TR001_09', 'Lê Văn I', '1987-09-20', 'Nam', 'levani@test.edu.vn', '0900000013', 'Hà Nội', 'Địa', 'ThS', 'GVBM', '016677889900', 'ACTIVE', NULL),
  ('GV_TR001_10', 'Nguyễn Thị K', '1988-10-25', 'Nữ', 'nguyenthik@test.edu.vn', '0900000014', 'Hà Nội', 'GDCD', 'ThS', 'GVBM', '017788990011', 'ACTIVE', NULL),
  ('GV_TR001_11', 'Hoàng Văn L', '1989-11-30', 'Nam', 'hoangvanl@test.edu.vn', '0900000015', 'Hà Nội', 'TD', 'CN', 'GVBM', '018899001122', 'ACTIVE', NULL),
  ('GV_TR001_12', 'Trần Thị M', '1990-12-05', 'Nữ', 'tranthim@test.edu.vn', '0900000016', 'Hà Nội', 'Tin', 'ThS', 'GVBM', '019900112233', 'ACTIVE', NULL)
ON DUPLICATE KEY UPDATE 
  hoTen=VALUES(hoTen), 
  email=VALUES(email), 
  monHocPhuTrach=VALUES(monHocPhuTrach),
  tinhTrangTaiKhoan=VALUES(tinhTrangTaiKhoan);

-- =========================================================
-- END
-- =========================================================

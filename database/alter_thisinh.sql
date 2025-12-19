-- Thêm cột gioiTinh nếu chưa có
ALTER TABLE thisinh 
ADD COLUMN IF NOT EXISTS gioiTinh ENUM('M', 'F') DEFAULT NULL 
AFTER ngaySinh;

-- Kiểm tra cấu trúc bảng
DESC thisinh;

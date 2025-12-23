-- Thêm cột soDienThoai và maTruong vào bảng taikhoan
ALTER TABLE `taikhoan` 
ADD COLUMN `soDienThoai` VARCHAR(15) NULL AFTER `email`,
ADD COLUMN `maTruong` VARCHAR(30) NULL AFTER `soDienThoai`;

-- Thêm index cho tìm kiếm nhanh
ALTER TABLE `taikhoan` 
ADD INDEX `idx_soDienThoai` (`soDienThoai`),
ADD INDEX `idx_maTruong` (`maTruong`);

-- Thêm foreign key nếu bảng truong tồn tại
-- ALTER TABLE `taikhoan` 
-- ADD CONSTRAINT `fk_taikhoan_truong` 
-- FOREIGN KEY (`maTruong`) REFERENCES `truong`(`maTruong`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;

# Learning-Systems: Chức năng Tạo Tổ Hợp Môn & Quản Lý Học Sinh

## 1. Các file và thư mục đã thêm

### Quản lý học sinh
- **Model:**
  - `models/admin/QuanLyHocSinhModel.php`
- **Controller:**
  - `controllers/admin/QuanLyHocSinhController.php`
- **Views:**
  - `views/admin/quanLyHocSinh/quanLyHocSinhView.php` (trang tìm kiếm & danh sách)
  - `views/admin/quanLyHocSinh/view.php` (trang chi tiết hồ sơ học sinh)
  - `views/admin/quanLyHocSinh/edit.php` (trang chỉnh sửa hồ sơ học sinh)
- **Entrypoint modules:**
  - `modules/quanLyHocSinh/quanLyHocSinhView.php` (trang chính quản lý học sinh)
  - `modules/quanLyHocSinh/view.php` (xem chi tiết)
  - `modules/quanLyHocSinh/edit.php` (chỉnh sửa)

### Tạo tổ hợp môn
- **Model:**
  - `models/admin/taoCacToHopMonModel.php`
- **Controller:**
  - `controllers/admin/taoCacToHopMon_controller.php`
- **Views:**
  - `views/admin/toHopMon/taoCacToHopMon.php` (form tạo tổ hợp môn)
  - `views/admin/toHopMon/list.php` (danh sách tổ hợp môn)

### Cấu trúc bảng và view trong database

#### Các bảng liên quan tổ hợp môn
```sql
-- Bảng tổ hợp môn
CREATE TABLE `tohopmon` (
  `maToHop` varchar(20) NOT NULL,
  `tenToHop` varchar(100) DEFAULT NULL,
  `danhSachMon` varchar(500) DEFAULT NULL,
  `soLuongLop` int(11) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng liên kết tổ hợp môn với môn học
CREATE TABLE `tohopmon_monhoc` (
  `maToHop` varchar(20) NOT NULL,
  `maMonHoc` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### View quản lý hồ sơ học sinh
```sql
-- View tổng hợp hồ sơ học sinh (tạo thủ công trong DB)
CREATE OR REPLACE VIEW vw_hocsinh_ho_so AS
SELECT
  h.maHS,
  h.hoTen,
  h.ngaySinh,
  h.soCCCD,
  h.diaChi,
  h.email,
  h.gioiTinh,
  h.sdt,
  h.maLop,
  lo.khoi AS khoi,
  h.trangThai,
  h.maTaiKhoan,
  (SELECT hl.xepLoaiHocLuc FROM hocluc hl WHERE hl.maHS = h.maHS LIMIT 1) AS xepLoaiHocLuc,
  (SELECT hk.loaiHanhKiem FROM hanhkiem hk WHERE hk.maHS = h.maHS LIMIT 1) AS loaiHanhKiem,
  ROUND(AVG((bd.diemThuongXuyen + bd.diemGiuaKy + bd.diemCuoiKy) / 3), 2) AS diemTrungBinhMon,
  GROUP_CONCAT(DISTINCT CONCAT(ph.hoTen, ' | ', ph.soDienThoai, ' | ', IFNULL(ph.email, '')) SEPARATOR '; ') AS phuHuynh_info
FROM hocsinh h
LEFT JOIN lophoc lo ON lo.maLop = h.maLop
LEFT JOIN bangdiem bd ON bd.maHS = h.maHS
LEFT JOIN phuhuynh_hocsinh phhs ON phhs.maHS = h.maHS
LEFT JOIN phuhuynh ph ON ph.maPH = phhs.maPH
GROUP BY
  h.maHS,
  h.hoTen,
  h.ngaySinh,
  h.soCCCD,
  h.diaChi,
  h.email,
  h.gioiTinh,
  h.sdt,
  h.maLop,
  lo.khoi,
  h.trangThai,
  h.maTaiKhoan;
```

## 2. Hướng dẫn sử dụng
- Đảm bảo đã tạo các bảng và view như trên trong database.
- Truy cập các chức năng qua các entrypoint module tương ứng.
- Quản lý học sinh: tìm kiếm, xem chi tiết, chỉnh sửa hồ sơ.
- Tạo tổ hợp môn: thêm mới, xem danh sách tổ hợp môn.

## 3. Ghi chú
- Các file trên được thêm mới hoặc chỉnh sửa để phục vụ hai chức năng chính: quản lý học sinh và tạo tổ hợp môn.
- Nếu cần bổ sung hoặc chỉnh sửa, cập nhật lại README.md để đảm bảo đồng bộ.

# Learning-Systems: Quản Lý Học Sinh & Tạo/Duyệt Tổ Hợp Môn

## Tổng Quan Hệ Thống

Hệ thống này hỗ trợ các chức năng:
1. **Quản lý hồ sơ học sinh** — Admin (Phòng Giáo Vụ) tìm kiếm, xem, chỉnh sửa thông tin học sinh
2. **Tạo tổ hợp môn** — Admin (Phòng Giáo Vụ) tạo các tổ hợp môn (mặc định trạng thái = PENDING)
3. **Duyệt tổ hợp môn** — Ban Giám Hiệu (BGH) xem danh sách, chi tiết, phê duyệt hoặc từ chối tổ hợp môn
4. **Dashboard hiển thị yêu cầu chờ duyệt** — BGH xem các yêu cầu cần xử lý, sắp xếp theo mới nhất

---

## 1. Quản Lý Hồ Sơ Học Sinh

### Files Liên Quan

**Model:**
- `models/admin/quanLyHoSoHocSinhModel.php`

**Controller:**
- `controllers/admin/quanLyHoSoHocSinhController.php`

**Views:**
- `views/admin/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php` — Trang tìm kiếm & danh sách
- `views/admin/quanLyHoSoHocSinh/view.php` — Trang chi tiết hồ sơ học sinh
- `views/admin/quanLyHoSoHocSinh/edit.php` — Trang chỉnh sửa hồ sơ học sinh

**Module Entry Points:**
- `modules/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php` — Trang chính quản lý học sinh
- `modules/quanLyHoSoHocSinh/view.php` — Xem chi tiết
- `modules/quanLyHoSoHocSinh/edit.php` — Chỉnh sửa

### Database Schema

```sql
-- View tổng hợp hồ sơ học sinh
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
  h.maHS, h.hoTen, h.ngaySinh, h.soCCCD, h.diaChi, h.email, h.gioiTinh, h.sdt,
  h.maLop, lo.khoi, h.trangThai, h.maTaiKhoan;
```

---

## 2. Tạo Tổ Hợp Môn (Admin/Phòng Giáo Vụ)

### Files Liên Quan

**Model:**
- `models/admin/taoCacToHopMonModel.php`

**Controller:**
- `controllers/admin/taoCacToHopMon_controller.php`

**Views:**
- `views/admin/toHopMon/taoCacToHopMon.php` — Form tạo tổ hợp môn
- `views/admin/toHopMon/list.php` — Danh sách tổ hợp môn (không có form chỉnh sửa/xóa)

### Quy Trình Tạo Tổ Hợp Môn

1. Admin truy cập `/controllers/admin/taoCacToHopMon_controller.php?action=create`
2. Điền thông tin: Mã tổ hợp, Tên tổ hợp, Danh sách môn (tối đa 3 môn), Số lượng lớp
3. **Không có trường trạng thái** — Admin không thể chọn/thay đổi trạng thái
4. Khi tạo: `trangThai = 'PENDING'` (chờ duyệt) tự động được ghi vào DB
5. Yêu cầu được hiển thị trên Dashboard BGH ngay lập tức

### Database: Thêm cột mới vào bảng `tohopmon`

Bảng `tohopmon` và `tohopmon_monhoc` đã tồn tại trong hệ thống. Cần thêm các cột sau để hỗ trợ chức năng duyệt:

```sql
ALTER TABLE tohopmon 
ADD COLUMN IF NOT EXISTS ngayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS nguoiTao VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS ngayDuyet TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS nguoiDuyet VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS lyDoDuyet TEXT NULL;
```

**Giải thích các cột mới:**
- `ngayTao` — Ngày tạo tổ hợp môn (tự động ghi khi tạo)
- `nguoiTao` — Tên/ID người tạo (admin)
- `ngayDuyet` — Ngày xử lý (phê duyệt hoặc từ chối)
- `nguoiDuyet` — Tên/ID người xử lý (BGH)
- `lyDoDuyet` — Lý do phê duyệt hoặc từ chối

---

## 3. Duyệt Tổ Hợp Môn (Ban Giám Hiệu)

### Files Liên Quan

**Model:**
- `models/bgh/chonToHopMonModel.php` — Đọc từ VIEW `vw_bgh_chon_to_hop_mon` cho các truy vấn, ghi vào bảng `tohopmon` cho phê duyệt/từ chối

**Controller:**
- `controllers/bgh/chonToHopMonController.php` — Xử lý POST phê duyệt/từ chối

**Views:**
- `views/bgh/chonToHopMon/list.php` — Danh sách tổ hợp môn theo trạng thái (chờ duyệt, đã chọn, từ chối)
- `views/bgh/chonToHopMon/detail.php` — Chi tiết một tổ hợp môn + form phê duyệt/từ chối

**Module Entry Points:**
- `modules/chonToHopMon/quanLyChonList.php` — Trang danh sách
- `modules/chonToHopMon/quanLyChonDetail.php` — Trang chi tiết

### Quy Trình Duyệt Tổ Hợp Môn

1. BGH truy cập `/modules/chonToHopMon/quanLyChonList.php`
2. Xem danh sách tổ hợp môn, lọc theo trạng thái: "Tất Cả", "Chờ Duyệt", "Đã Chọn", "Từ Chối"
3. Nhấn "Chi Tiết" để xem thông tin chi tiết (môn học, học sinh đã đăng ký, ngày tạo, người tạo)
4. Tùy trạng thái hiện tại:
   - **PENDING**: Hiển thị 2 form (Phê Duyệt + Từ Chối Tổ Hợp) — BGH chọn một hành động
   - **APPROVED/REJECTED**: Hiển thị thông tin xử lý (ngày, người, lý do)

### Trạng Thái Tổ Hợp Môn

| Trạng Thái | Mã DB | Ý Nghĩa |
|-----------|-------|---------|
| Chờ Duyệt | `PENDING` | Vừa tạo, chưa được BGH xử lý |
| Đã Chọn | `APPROVED` | BGH đã phê duyệt, tổ hợp được sử dụng |
| Từ Chối | `REJECTED` | BGH đã từ chối, không sử dụng được |

### Database: View tổng hợp

```sql
CREATE OR REPLACE VIEW vw_bgh_chon_to_hop_mon AS
SELECT
  t.maToHop,
  t.tenToHop,
  t.danhSachMon,
  t.soLuongLop,
  t.trangThai,
  t.ngayTao,
  t.nguoiTao,
  t.ngayDuyet,
  t.nguoiDuyet,
  t.lyDoDuyet,
  GROUP_CONCAT(DISTINCT m.tenMon SEPARATOR ', ') AS tenCacMon,
  COUNT(DISTINCT phd.maHS) AS soHocSinhDaDangKy
FROM tohopmon t
LEFT JOIN tohopmon_monhoc tm ON t.maToHop = tm.maToHop
LEFT JOIN monhoc m ON tm.maMonHoc = m.maMonHoc
LEFT JOIN phieudangkytohopmon phd ON t.maToHop = phd.maToHop
GROUP BY
  t.maToHop, t.tenToHop, t.danhSachMon, t.soLuongLop, t.trangThai,
  t.ngayTao, t.nguoiTao, t.ngayDuyet, t.nguoiDuyet, t.lyDoDuyet;
```

---

## 4. Dashboard BGH

### Files Liên Quan

**View:**
- `views/bgh/dashboard.php` — Dashboard chính của BGH

### Chức Năng

**Phần "Yêu Cầu Chờ Duyệt":**
- Hiển thị danh sách các yêu cầu chờ xử lý (từ nhiều nguồn: sửa điểm, hạnh kiểm, đề thi, phân công, **tổ hợp môn**)
- **Sắp xếp theo ngày giảm dần** — Mục mới nhất (vừa tạo) luôn hiển thị ở vị trí đầu tiên
- Mỗi mục tổ hợp môn có nút "Chi Tiết" dẫn tới `/modules/chonToHopMon/quanLyChonDetail.php?maToHop=...`

### Code Logic Tích Hợp

Trong `views/bgh/dashboard.php`, các mục tổ hợp môn PENDING được thêm vào mảng `$pendingApprovals` tự động:

```php
// Thêm các yêu cầu từ "Chọn Tổ Hợp Môn" do admin tạo (trạng thái PENDING)
require_once __DIR__ . '/../../models/bgh/chonToHopMonModel.php';
try {
    $chonModel = new chonToHopMonModel();
    $pendingToHop = $chonModel->getDanhSachToHopMon('PENDING');
    foreach ($pendingToHop as $t) {
        $pendingApprovals[] = [
            'type' => 'tohopmon',
            'title' => 'Yêu cầu duyệt tổ hợp: ' . ($t['tenToHop'] ?? $t['maToHop']),
            'submitter' => $t['nguoiTao'] ?? ($t['nguoiDuyet'] ?? 'Phòng Giáo Vụ'),
            'date' => $t['ngayTao'] ?? date('Y-m-d'),
            'priority' => 'high',
            'maToHop' => $t['maToHop'] ?? null
        ];
    }
} catch (Exception $e) {
    // Nếu có lỗi kết nối DB, giữ nguyên các mục tĩnh
}

// Sắp xếp danh sách yêu cầu chờ duyệt theo ngày giảm dần (mới nhất lên đầu)
usort($pendingApprovals, function($a, $b) {
    $dateA = strtotime($a['date'] ?? '1970-01-01');
    $dateB = strtotime($b['date'] ?? '1970-01-01');
    return $dateB - $dateA; // Giảm dần: ngày mới nhất trước
});
```

---

## 5. Hướng Dẫn Setup & Chạy

### Bước 1: Thêm cột mới vào Database

Chạy các lệnh SQL sau trong MySQL để thêm các cột hỗ trợ chức năng duyệt tổ hợp môn:

```sql
-- 1. Thêm cột vào bảng tohopmon
ALTER TABLE tohopmon 
ADD COLUMN IF NOT EXISTS ngayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS nguoiTao VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS ngayDuyet TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS nguoiDuyet VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS lyDoDuyet TEXT NULL;

-- 2. Tạo view vw_bgh_chon_to_hop_mon
CREATE OR REPLACE VIEW vw_bgh_chon_to_hop_mon AS
SELECT
  t.maToHop,
  t.tenToHop,
  t.danhSachMon,
  t.soLuongLop,
  t.trangThai,
  t.ngayTao,
  t.nguoiTao,
  t.ngayDuyet,
  t.nguoiDuyet,
  t.lyDoDuyet,
  GROUP_CONCAT(DISTINCT m.tenMon SEPARATOR ', ') AS tenCacMon,
  COUNT(DISTINCT phd.maHS) AS soHocSinhDaDangKy
FROM tohopmon t
LEFT JOIN tohopmon_monhoc tm ON t.maToHop = tm.maToHop
LEFT JOIN monhoc m ON tm.maMonHoc = m.maMonHoc
LEFT JOIN phieudangkytohopmon phd ON t.maToHop = phd.maToHop
GROUP BY
  t.maToHop, t.tenToHop, t.danhSachMon, t.soLuongLop, t.trangThai,
  t.ngayTao, t.nguoiTao, t.ngayDuyet, t.nguoiDuyet, t.lyDoDuyet;
```

### Bước 2: Đăng Nhập & Truy Cập

- **Admin (Phòng Giáo Vụ):**
  - Tạo tổ hợp môn: `/controllers/admin/taoCacToHopMon_controller.php?action=create`
  - Xem danh sách: `/controllers/admin/taoCacToHopMon_controller.php`

- **Ban Giám Hiệu (BGH):**
  - Dashboard: `/views/bgh/dashboard.php` (hoặc xem yêu cầu từ module entry point)
  - Danh sách tổ hợp môn: `/modules/chonToHopMon/quanLyChonList.php`
  - Chi tiết tổ hợp: `/modules/chonToHopMon/quanLyChonDetail.php?maToHop=...`

- **Học Sinh:**
  - Quản lý hồ sơ: `/modules/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php`

---

## 6. Ghi Chú Kỹ Thuật

### Model Layer
- **Admin Model** (`models/admin/taoCacToHopMonModel.php`):
  - `create()` — Insert vào `tohopmon` (trạng thái = 'PENDING') và `tohopmon_monhoc`
  - `validateData()` — Kiểm tra tối đa 3 môn, các môn phải tồn tại
  - `checkExist()` — Kiểm tra mã tổ hợp trùng

- **BGH Model** (`models/bgh/chonToHopMonModel.php`):
  - `getDanhSachToHopMon($trangThai)` — Lấy từ VIEW để tối ưu
  - `getChiTietToHopMon($maToHop)` — Lấy chi tiết từ VIEW
  - `getDanhSachMonTrongToHop($maToHop)` — Lấy danh sách môn, dùng cột `tenMon` từ bảng `monhoc`
  - `pheDuyetToHopMon()` — Update `trangThai = 'APPROVED'`
  - `tuChoiDuyetToHopMon()` — Update `trangThai = 'REJECTED'`

### View Layer
- Views sử dụng Bootstrap 5 & Font Awesome 6 cho giao diện
- Các table responsive cho dữ liệu nhiều cột
- Form validation cả client-side (JS) và server-side (PHP)

### Middleware
- `AuthGuard.php` — Kiểm tra quyền (role: admin, bgh, hs, v.v.)
- Tất cả module entry point require đúng role trước khi hiển thị

---

## 7. Changelog

### v1.0 (Ngày 26/11/2025)
- ✅ Tạo chức năng Quản Lý Hồ Sơ Học Sinh
- ✅ Tạo chức năng Tạo Tổ Hợp Môn (Admin)
  - Form tạo với 3 field: Mã tổ hợp, Tên tổ hợp, Danh sách môn (max 3), Số lượng lớp
  - Bỏ trường Trạng Thái khỏi form
  - Mặc định `trangThai = 'PENDING'` khi tạo
- ✅ Tạo chức năng Duyệt Tổ Hợp Môn (BGH)
  - Danh sách với filter theo trạng thái (Tất Cả, Chờ Duyệt, Đã Chọn, Từ Chối)
  - Chi tiết: hiển thị thông tin tổng quát, danh sách môn, danh sách học sinh đã đăng ký
  - Phê duyệt/Từ chối: form update status, lý do, ngày xử lý, người xử lý
- ✅ Thêm cột `ngayTao`, `nguoiTao`, `ngayDuyet`, `nguoiDuyet`, `lyDoDuyet` vào bảng `tohopmon`
- ✅ Tạo VIEW `vw_bgh_chon_to_hop_mon` để tổng hợp dữ liệu
- ✅ Tích hợp yêu cầu tổ hợp môn vào Dashboard BGH (phần "Yêu Cầu Chờ Duyệt")
- ✅ Sắp xếp yêu cầu chờ duyệt theo ngày giảm dần (mới nhất lên đầu)
- ✅ Sửa tên cột `tenMonHoc` → `tenMon` trong `getDanhSachMonTrongToHop()`
- ✅ Sửa reference `tenNguoiTao` → `nguoiTao`, `tenNguoiDuyet` → `nguoiDuyet` trong view chi tiết

---

## 8. Liên Hệ & Hỗ Trợ

Nếu gặp lỗi hoặc cần tính năng mới, vui lòng cập nhật README.md hoặc liên hệ quản trị viên hệ thống.

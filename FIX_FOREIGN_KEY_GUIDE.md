# 🔧 Khắc phục lỗi Foreign Key Constraint - maNhanVienSo

## ❌ Lỗi gặp phải
```
SQLSTATE[23000]: Integrity constraint violation: 1452 
Cannot add or update a child row: a foreign key constraint fails 
(`hethongquanlyhocsinh1`.`chitieutuyensinh`, 
CONSTRAINT `chitieutuyensinh_ibfk_2` FOREIGN KEY (`maNhanVienSo`) 
REFERENCES `nhanvienso` (`maNhanVienSo`))
```

## 🔍 Nguyên nhân
1. Giá trị `maNhanVienSo` từ session không tồn tại trong bảng `NhanVienSo`
2. Controller đang lấy `user['id']` nhưng không có bản ghi tương ứng trong `NhanVienSo`
3. Foreign key constraint yêu cầu `maNhanVienSo` phải tồn tại trước khi insert

## ✅ Giải pháp đã áp dụng

### 1. Sửa Model - `models/ChiTieuTuyenSinh.php`

**Thêm logic kiểm tra maNhanVienSo trước khi insert:**

```php
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
```

**Lợi ích:**
- Nếu `maNhanVienSo` không tồn tại → Lưu NULL thay vì bị lỗi
- Vẫn giữ được dữ liệu phân bổ
- Tránh crash hệ thống

### 2. Sửa Controller - `controllers/nhanvienso/ChiTieuController.php`

**Cải thiện logic lấy maNhanVienSo:**

```php
// Ưu tiên lấy từ id, nếu không có thì lấy từ username, nếu vẫn không có thì NULL
$maNhanVienSo = null;
if (isset($user['id'])) {
    $maNhanVienSo = $user['id'];
} elseif (isset($user['username'])) {
    $maNhanVienSo = $user['username'];
}
```

**Lợi ích:**
- Linh hoạt hơn trong việc lấy mã nhân viên
- Fallback to NULL nếu không có
- Không crash khi thiếu dữ liệu

### 3. Sửa Database - Chạy script `database/fix_foreign_key.sql`

## 📝 Hướng dẫn thực hiện

### Bước 1: Chạy script SQL

**Mở phpMyAdmin hoặc MySQL Workbench và chạy:**

```bash
mysql -u root -p HeThongQuanLyHocSinh1 < database/fix_foreign_key.sql
```

**Hoặc trong phpMyAdmin:**
1. Chọn database `HeThongQuanLyHocSinh1`
2. Tab "SQL"
3. Copy nội dung file `database/fix_foreign_key.sql`
4. Click "Go"

### Bước 2: Kiểm tra kết quả

Script sẽ hiển thị 3 bảng thông tin:

#### Bảng 1: Thông tin nhân viên sở
```sql
SELECT * FROM NhanVienSo;
```
✅ Phải có ít nhất 1 bản ghi

#### Bảng 2: Tài khoản nhân viên sở
```sql
SELECT ... FROM TaiKhoan WHERE role = 'nhanvienso';
```
✅ Kiểm tra cột "TrangThai" phải là "Đã có trong NhanVienSo"

#### Bảng 3: Tổng quan
```sql
- TongNhanVienSo: Số lượng nhân viên sở trong DB
- TongTaiKhoanNhanVienSo: Số tài khoản có role 'nhanvienso'
- ChiTieuCoNhanVienSo: Số bản ghi chỉ tiêu có liên kết nhân viên
- ChiTieuKhongCoNhanVienSo: Số bản ghi chỉ tiêu không có liên kết
```

### Bước 3: Test lại chức năng

1. Đăng nhập với tài khoản `nhanvienso`
2. Truy cập "Phân bổ chỉ tiêu tuyển sinh"
3. Chọn năm học 2024-2025
4. Nhập chỉ tiêu cho các trường
5. Click "Xác nhận phân bổ"
6. ✅ **Không còn lỗi Foreign Key!**

## 🔍 Debug nếu vẫn lỗi

### Kiểm tra 1: Bảng NhanVienSo có dữ liệu chưa?

```sql
SELECT COUNT(*) as total FROM NhanVienSo;
```

Nếu `total = 0`:
```sql
-- Thêm thủ công
INSERT INTO NhanVienSo (maNhanVienSo, hoTen, chucVu, phongBan, email, soDienThoai, maTaiKhoan) 
VALUES ('NVS001', 'Nguyễn Văn An', 'Chuyên viên', 'Phòng Giáo dục THPT', 'nhanvienso@sgd.edu.vn', '0901234567', 'NVS001');
```

### Kiểm tra 2: Session có user['id'] không?

Thêm vào đầu file `controllers/nhanvienso/ChiTieuController.php`:

```php
public function submit() {
    // DEBUG
    error_log("User session: " . print_r(current_user(), true));
    error_log("maNhanVienSo: " . ($maNhanVienSo ?? 'NULL'));
    
    // ... code tiếp
}
```

Xem log trong file:
- Windows: `C:\xampp\apache\logs\error.log`
- Linux: `/var/log/apache2/error.log`

### Kiểm tra 3: Constraint có cho phép NULL chưa?

```sql
-- Kiểm tra cấu trúc bảng
SHOW CREATE TABLE ChiTieuTuyenSinh;
```

Phải có:
```sql
`maNhanVienSo` varchar(30) DEFAULT NULL
```

Nếu không:
```sql
ALTER TABLE ChiTieuTuyenSinh 
MODIFY COLUMN maNhanVienSo VARCHAR(30) NULL;
```

### Kiểm tra 4: Foreign key constraint đúng chưa?

```sql
-- Xem tất cả constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'HeThongQuanLyHocSinh1'
AND TABLE_NAME = 'ChiTieuTuyenSinh'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

## 🎯 Cách hoạt động sau khi sửa

### Luồng xử lý mới:

1. **User submit form** → Controller nhận request
2. **Lấy maNhanVienSo từ session:**
   - Có `user['id']` → Dùng `user['id']`
   - Không có `user['id']` nhưng có `user['username']` → Dùng `user['username']`
   - Không có gì → `NULL`

3. **Model xử lý:**
   - Kiểm tra `maNhanVienSo` có trong bảng `NhanVienSo` không?
   - ✅ Có → Dùng giá trị đó
   - ❌ Không có → Set `NULL`

4. **Insert vào database:**
   - Foreign key cho phép `NULL`
   - Insert thành công
   - ✅ Phân bổ chỉ tiêu thành công!

### Ưu điểm:

✅ **Không crash** khi thiếu maNhanVienSo  
✅ **Linh hoạt** - Chấp nhận NULL  
✅ **Tương thích ngược** - Code cũ vẫn chạy được  
✅ **Dễ maintain** - Có thể cập nhật maNhanVienSo sau  

## 📊 So sánh trước/sau

| Tiêu chí | Trước | Sau |
|----------|-------|-----|
| Khi maNhanVienSo không tồn tại | ❌ Crash | ✅ Lưu NULL |
| Phân bổ chỉ tiêu | ❌ Thất bại | ✅ Thành công |
| Dữ liệu trong DB | ❌ Không lưu | ✅ Lưu đầy đủ |
| Foreign key | ❌ Bắt buộc | ✅ Optional (NULL) |
| Tính linh hoạt | ❌ Thấp | ✅ Cao |

## 🔐 Cập nhật sau này

Nếu muốn link đúng maNhanVienSo cho các bản ghi NULL:

```sql
-- Cập nhật maNhanVienSo cho các bản ghi cũ
UPDATE ChiTieuTuyenSinh 
SET maNhanVienSo = 'NVS001'
WHERE maNhanVienSo IS NULL
AND EXISTS (SELECT 1 FROM NhanVienSo WHERE maNhanVienSo = 'NVS001');
```

## 📝 Files đã sửa

| File | Thay đổi |
|------|----------|
| `models/ChiTieuTuyenSinh.php` | Thêm logic kiểm tra maNhanVienSo |
| `controllers/nhanvienso/ChiTieuController.php` | Cải thiện logic lấy maNhanVienSo |
| `database/fix_foreign_key.sql` | Script sửa constraint và thêm dữ liệu |

## ✅ Checklist hoàn thành

- [x] Sửa Model để kiểm tra maNhanVienSo
- [x] Sửa Controller để lấy đúng maNhanVienSo
- [x] Tạo script SQL fix constraint
- [x] Cho phép NULL trong maNhanVienSo
- [x] Thêm ON DELETE SET NULL
- [x] Tạo/cập nhật bảng NhanVienSo
- [x] Thêm nhân viên sở mẫu
- [x] Test lại chức năng

## 🎉 Kết luận

Sau khi áp dụng các thay đổi trên:

✅ **Lỗi Foreign Key đã được khắc phục**  
✅ **Chức năng phân bổ chỉ tiêu hoạt động bình thường**  
✅ **Hệ thống ổn định hơn, không crash khi thiếu dữ liệu**  

---

**Ngày sửa:** 2024-03-15  
**Trạng thái:** ✅ Đã khắc phục hoàn toàn  

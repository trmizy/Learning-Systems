# 🚀 Hướng dẫn cài đặt nhanh - Chức năng Phân bổ chỉ tiêu tuyển sinh

## ✅ Checklist cài đặt

### 1. Database Setup
```bash
# Mở phpMyAdmin hoặc MySQL Workbench
# Chạy file SQL:
mysql -u root -p HeThongQuanLyHocSinh1 < Learning-Systems/database/setup_chitieu.sql
```

**Hoặc trong phpMyAdmin:**
1. Chọn database `HeThongQuanLyHocSinh1`
2. Tab "SQL"
3. Copy nội dung file `database/setup_chitieu.sql`
4. Click "Go"

### 2. Kiểm tra cấu trúc file
```
Learning-Systems/
├── models/
│   └── ChiTieuTuyenSinh.php          ✓ Created
├── controllers/
│   └── nhanvienso/
│       └── ChiTieuController.php     ✓ Created
├── views/
│   └── nhanvienso/
│       └── chitieu/
│           └── phanbo.php            ✓ Created
├── assets/
│   └── css/
│       └── chitieu.css               ✓ Created
├── database/
│   └── setup_chitieu.sql             ✓ Created
├── docs/
│   └── CHITIEU_README.md             ✓ Created
└── demo/
    └── chitieu-demo.html             ✓ Created
```

### 3. Cập nhật file cấu hình

**File: `config/roles.php`**
- ✓ Đã thêm vai trò `nhanvienso`
- ✓ Đã cập nhật permissions

**File: `public/index.php`**
- ✓ Đã thêm routing cho controller `chitieu`

**File: `views/nhanvienso/dashboard.php`**
- ✓ Đã thêm link "Phân bổ chỉ tiêu"

### 4. Test đăng nhập
```
URL: http://localhost/Learning-Systems/public/index.php
Username: nhanvienso
Password: 123456
```

### 5. Truy cập chức năng
```
URL: http://localhost/Learning-Systems/public/index.php?controller=chitieu&action=index
```

## 🧪 Test Cases

### Test 1: Xem trang phân bổ
- [ ] Đăng nhập với tài khoản `nhanvienso`
- [ ] Click vào card "Chỉ tiêu" trong dashboard
- [ ] Kiểm tra hiển thị đầy đủ:
  - [ ] Header và statistics
  - [ ] Dropdown năm học
  - [ ] Bảng danh sách trường
  - [ ] Form nhập chỉ tiêu

### Test 2: Cập nhật tổng chỉ tiêu
- [ ] Chọn năm học (vd: 2024-2025)
- [ ] Nhập tổng chỉ tiêu: 12000
- [ ] Click "Cập nhật"
- [ ] Kiểm tra thông báo thành công

### Test 3: Phân bổ chỉ tiêu
- [ ] Chọn năm học
- [ ] Nhập chỉ tiêu cho mỗi trường
- [ ] Kiểm tra tổng chỉ tiêu tự động cập nhật
- [ ] Click "Xác nhận phân bổ"
- [ ] Kiểm tra thông báo thành công
- [ ] Kiểm tra dữ liệu trong database

### Test 4: Áp dụng gợi ý
- [ ] Click "Áp dụng gợi ý" cho một trường
- [ ] Kiểm tra giá trị được điền tự động
- [ ] Click "Áp dụng tất cả gợi ý"
- [ ] Kiểm tra tất cả trường được điền

### Test 5: Validation
- [ ] Để trống chỉ tiêu -> Kiểm tra báo lỗi
- [ ] Nhập số âm -> Kiểm tra báo lỗi
- [ ] Nhập số 0 -> Kiểm tra báo lỗi
- [ ] Vượt quá tổng chỉ tiêu -> Kiểm tra cảnh báo

### Test 6: Hủy phân bổ
- [ ] Nhập một số chỉ tiêu
- [ ] Click "Hủy phân bổ"
- [ ] Xác nhận modal
- [ ] Kiểm tra redirect về trang index

## 🐛 Debug Checklist

### Nếu không thấy menu "Phân bổ chỉ tiêu"
```php
// Kiểm tra vai trò trong session
<?php
session_start();
print_r($_SESSION['auth']);
?>
```
- Đảm bảo `role` = 'nhanvienso'

### Nếu lỗi 404
1. Kiểm tra routing trong `public/index.php`
2. Đảm bảo file `controllers/nhanvienso/ChiTieuController.php` tồn tại
3. Check path trong URL

### Nếu lỗi database
1. Kiểm tra kết nối trong `.env`
2. Chạy lại script SQL
3. Kiểm tra bảng `ChiTieuTuyenSinh` đã tồn tại

### Nếu CSS không load
1. Kiểm tra path: `/Learning-Systems/assets/css/chitieu.css`
2. Clear cache browser
3. Kiểm tra file tồn tại

## 📊 Kiểm tra dữ liệu trong database

```sql
-- Kiểm tra vai trò
SELECT * FROM VaiTro WHERE maVaiTro = 'nhanvienso';

-- Kiểm tra tài khoản
SELECT * FROM TaiKhoan WHERE tenDangNhap = 'nhanvienso';

-- Kiểm tra trường
SELECT COUNT(*) as total FROM Truong;

-- Kiểm tra chỉ tiêu
SELECT 
    namHoc,
    COUNT(*) as soTruong,
    SUM(chiTieuPhanBo) as tongPhanBo
FROM ChiTieuTuyenSinh
WHERE maTruong IS NOT NULL
GROUP BY namHoc;
```

## 🎨 Demo giao diện

File demo không cần database:
```
file:///[path]/Learning-Systems/demo/chitieu-demo.html
```

## 📱 Responsive Test
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

## ✨ Các tính năng đã implement

### Backend (PHP)
- [x] Model với các phương thức CRUD
- [x] Controller xử lý routing và logic
- [x] Validation dữ liệu đầu vào
- [x] Transaction database
- [x] Error handling

### Frontend (HTML/CSS/JS)
- [x] Responsive design
- [x] Real-time calculation
- [x] Form validation
- [x] Modal confirmations
- [x] Toast notifications
- [x] Smooth animations

### Database
- [x] Schema đầy đủ
- [x] Foreign keys
- [x] Sample data
- [x] Indexes (nếu cần)

### Security
- [x] Authentication check
- [x] Role-based access control
- [x] SQL injection prevention (PDO)
- [x] XSS prevention (htmlspecialchars)
- [x] CSRF protection (sessions)

## 📝 Notes

1. **Môi trường development:**
   - PHP >= 7.4
   - MySQL >= 5.7
   - Apache/Nginx web server

2. **Dependencies:**
   - PDO extension
   - vlucas/phpdotenv (đã có)

3. **Browser support:**
   - Chrome (latest)
   - Firefox (latest)
   - Safari (latest)
   - Edge (latest)

## 🎯 Next Steps

Sau khi cài đặt xong:
1. Test tất cả các chức năng
2. Kiểm tra responsive trên mobile
3. Test với nhiều user khác nhau
4. Backup database trước khi deploy
5. Monitor logs để catch bugs

## 💡 Tips

- Sử dụng Chrome DevTools để debug JavaScript
- Check Network tab để xem API calls
- Sử dụng `error_log()` trong PHP để debug
- Backup database thường xuyên khi test

---

**Happy Coding! 🚀**

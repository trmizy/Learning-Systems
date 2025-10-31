# 🔧 Khắc phục lỗi 404 - Phân bổ chỉ tiêu tuyển sinh

## ❌ Vấn đề
Khi truy cập từ URL `http://localhost/public/index.php` và click vào "Phân bổ chỉ tiêu", gặp lỗi 404.

## ✅ Nguyên nhân
Các URL trong code đang sử dụng đường dẫn tuyệt đối `/Learning-Systems/public/index.php` nhưng ứng dụng đang chạy ở `/public/index.php`.

## 🔨 Giải pháp đã thực hiện

### 1. Sửa file `views/nhanvienso/dashboard.php`
**Dòng 479:** Thay đổi link "Phân bổ chỉ tiêu"
```php
// Cũ:
<a href="/Learning-Systems/public/index.php?controller=chitieu&action=index" class="btn btn-info btn-sm">

// Mới:
<a href="/public/index.php?controller=chitieu&action=index" class="btn btn-info btn-sm">
```

### 2. Sửa file `views/nhanvienso/chitieu/phanbo.php`
Thay đổi tất cả 5 URL:

**Dòng 9:** CSS link
```php
// Cũ:
<link rel="stylesheet" href="/Learning-Systems/assets/css/chitieu.css">

// Mới:
<link rel="stylesheet" href="/assets/css/chitieu.css">
```

**Dòng 88:** Form cập nhật tổng chỉ tiêu
```php
// Cũ:
<form method="POST" action="/Learning-Systems/public/index.php?controller=chitieu&action=updateTongChiTieu">

// Mới:
<form method="POST" action="/public/index.php?controller=chitieu&action=updateTongChiTieu">
```

**Dòng 127:** Form chọn năm học
```php
// Cũ:
<form method="GET" action="/Learning-Systems/public/index.php">

// Mới:
<form method="GET" action="/public/index.php">
```

**Dòng 148:** Form submit phân bổ
```php
// Cũ:
<form method="POST" action="/Learning-Systems/public/index.php?controller=chitieu&action=submit">

// Mới:
<form method="POST" action="/public/index.php?controller=chitieu&action=submit">
```

**Dòng 301:** Link hủy phân bổ
```php
// Cũ:
<a href="/Learning-Systems/public/index.php?controller=chitieu&action=cancel&namHoc=...">

// Mới:
<a href="/public/index.php?controller=chitieu&action=cancel&namHoc=...">
```

### 3. Sửa file `controllers/nhanvienso/ChiTieuController.php`
Thay đổi tất cả 8 redirect URL:

**Phương thức `submit()`:**
```php
// Cũ:
header('Location: /Learning-Systems/public/index.php?controller=chitieu&action=index');
header('Location: /Learning-Systems/public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));

// Mới:
header('Location: /public/index.php?controller=chitieu&action=index');
header('Location: /public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));
```

**Phương thức `updateTongChiTieu()`:**
```php
// Cũ:
header('Location: /Learning-Systems/public/index.php?controller=chitieu&action=index');
header('Location: /Learning-Systems/public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));

// Mới:
header('Location: /public/index.php?controller=chitieu&action=index');
header('Location: /public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));
```

**Phương thức `cancel()`:**
```php
// Cũ:
header('Location: /Learning-Systems/public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));

// Mới:
header('Location: /public/index.php?controller=chitieu&action=index&namHoc=' . urlencode($namHoc));
```

## 📊 Tổng kết thay đổi

| File | Số chỗ sửa | Loại thay đổi |
|------|-----------|---------------|
| `views/nhanvienso/dashboard.php` | 1 | Link button |
| `views/nhanvienso/chitieu/phanbo.php` | 5 | CSS link + Form actions |
| `controllers/nhanvienso/ChiTieuController.php` | 8 | Redirect headers |
| **TỔNG** | **14** | |

## ✅ Kiểm tra sau khi sửa

1. **Truy cập trang chủ:**
   ```
   http://localhost/public/index.php
   ```

2. **Đăng nhập với tài khoản nhân viên sở:**
   - Username: `nhanvienso`
   - Password: `123456`

3. **Click vào card "Chỉ tiêu" - "Phân bổ chỉ tiêu"**
   - Phải chuyển đến: `http://localhost/public/index.php?controller=chitieu&action=index`
   - Không còn lỗi 404

4. **Kiểm tra CSS:**
   - Giao diện hiển thị đúng (có màu sắc, gradient)
   - File CSS load thành công từ `/assets/css/chitieu.css`

5. **Kiểm tra các form:**
   - Form cập nhật tổng chỉ tiêu
   - Form chọn năm học
   - Form phân bổ chỉ tiêu
   - Tất cả phải submit về đúng URL

## 🚨 Lưu ý quan trọng

### Nếu ứng dụng chạy ở đường dẫn khác:

**Trường hợp 1:** Ứng dụng ở thư mục con
```
URL: http://localhost/myapp/public/index.php
Cần thay: /public/index.php
Thành: /myapp/public/index.php
```

**Trường hợp 2:** Ứng dụng ở domain gốc
```
URL: http://localhost/index.php
Cần thay: /public/index.php
Thành: /index.php
```

### Sử dụng URL động (Khuyến nghị):

Thay vì hardcode URL, nên tạo helper function:

```php
// Trong config/helpers.php
function base_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/' . ltrim($path, '/');
}

// Sử dụng:
<a href="<?php echo base_url('index.php?controller=chitieu&action=index'); ?>">
```

## 🎯 Cấu trúc URL đúng

Sau khi sửa, cấu trúc URL sẽ là:

```
Trang chủ:           /public/index.php
Dashboard:           /public/index.php
Phân bổ chỉ tiêu:    /public/index.php?controller=chitieu&action=index
Submit form:         /public/index.php?controller=chitieu&action=submit
Cập nhật tổng:       /public/index.php?controller=chitieu&action=updateTongChiTieu
Hủy phân bổ:         /public/index.php?controller=chitieu&action=cancel
CSS:                 /assets/css/chitieu.css
```

## 🔍 Debug nếu vẫn lỗi

### 1. Kiểm tra Apache/Nginx
```apache
# File .htaccess trong thư mục public (nếu dùng Apache)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 2. Kiểm tra quyền file
```bash
# Windows PowerShell
icacls "d:\BÉ GIANG\Năm_3\HK1\PTUD\codeCacChucNang\Learning-Systems" /grant Users:F /T
```

### 3. Kiểm tra PHP errors
```php
// Thêm vào đầu file public/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 4. Clear cache browser
- Nhấn `Ctrl + Shift + Delete`
- Xóa cache và cookies
- Reload lại trang

## ✨ Hoàn tất

Sau khi thực hiện các thay đổi trên, chức năng "Phân bổ chỉ tiêu tuyển sinh" sẽ hoạt động bình thường với URL base là `/public/index.php`.

---

**Ngày sửa:** 2024-03-15  
**Trạng thái:** ✅ Đã khắc phục  

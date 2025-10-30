# 🛠️ Scripts Directory# 🛠️ Scripts Directory



Thư mục này chứa các **migration scripts** để quản lý cấu trúc database.Thư mục này chứa các **utility scripts** dùng cho **development, testing và database management**. 



⚠️ **CẢNH BÁO**: Các scripts này **KHÔNG được truy cập qua web browser**.⚠️ **CẢNH BÁO**: Các scripts này **KHÔNG được sử dụng trong production** và không được truy cập qua web browser.



---## 📂 Cấu trúc thư mục



## 📂 Cấu trúc thư mục```

scripts/

```├── database/      # Database schema files

scripts/├── migrations/    # Database migration scripts

├── .htaccess          # Bảo vệ truy cập từ web├── seeds/         # Sample data seeding

├── migrations/        # Database migration scripts└── utilities/     # Development helper scripts

└── README.md          # Documentation này```

```

---

---

## 📦 database/ - Database Schema

## 🔄 migrations/ - Database Migrations

Chứa file SQL dump đầy đủ của database.

Chứa các file migration để thay đổi cấu trúc database.

### Files:

### Files:- **`hethongquanlyhocsinh1.sql`** - Full database schema và structure



| File | Mô tả | Khi nào dùng |### Cách dùng:

|------|-------|--------------|```bash

| `fix_collation.php` | **FIX LỖI COLLATION** - Sửa utf8mb4_unicode_ci → utf8mb4_general_ci | Khi gặp lỗi "Illegal mix of collations" |# Import database

| `migration_phan_cong_giang_day.sql` | Tạo bảng PhanCongGiangDay | Lần đầu setup database |mysql -u root -p < scripts/database/hethongquanlyhocsinh1.sql

| `migration_phan_cong_phong_hoc.sql` | Tạo bảng PhanCongPhongHoc | Lần đầu setup database |

| `run_migration.php` | Runner cho migration giảng dạy | Tự động chạy migration |# Hoặc qua phpMyAdmin:

| `run_migration_phong_hoc.php` | Runner cho migration phòng học | Tự động chạy migration |# Import file này để tạo database mới

```

### Cách dùng:

---

#### 1. Chạy migration tự động:

```bash## 🔄 migrations/ - Database Migrations

cd scripts/migrations

Chứa các file migration để thay đổi cấu trúc database.

# Chạy migration giảng dạy

php run_migration.php### Files:



# Chạy migration phòng học| File | Mô tả | Chạy khi nào |

php run_migration_phong_hoc.php|------|-------|--------------|

```| `migration_phan_cong_giang_day.sql` | Tạo bảng PhanCongGiangDay | Khi cần phân công GV theo môn |

| `migration_phan_cong_phong_hoc.sql` | Tạo bảng PhanCongPhongHoc | Khi cần phân công phòng multi-year |

#### 2. Fix lỗi collation:| `run_migration.php` | Runner cho migration giảng dạy | Chạy migration tự động |

```bash| `run_migration_phong_hoc.php` | Runner cho migration phòng học | Chạy migration tự động |

cd scripts/migrations

### Cách dùng:

# Chạy fix collation (nếu gặp lỗi phân công phòng học)```bash

php fix_collation.php# Di chuyển vào thư mục migrations

```cd scripts/migrations



#### 3. Import SQL trực tiếp:# Chạy migration

```bashphp run_migration.php

# Từ command linephp run_migration_phong_hoc.php

mysql -u root -p HeThongQuanLyHocSinh1 < migration_phan_cong_giang_day.sql

mysql -u root -p HeThongQuanLyHocSinh1 < migration_phan_cong_phong_hoc.sql# Hoặc import trực tiếp SQL

```mysql -u root -p HeThongQuanLyHocSinh1 < migration_phan_cong_giang_day.sql

```

### Lưu ý quan trọng:

- ⚠️ **Chỉ chạy migration MỘT LẦN** khi khởi tạo database### Lưu ý:

- ✅ **Backup database** trước khi chạy migration- ⚠️ Chỉ chạy migration MỘT LẦN

- 📝 Kiểm tra migration đã chạy chưa trước khi chạy lại- ✅ Backup database trước khi chạy migration

- 🔧 `fix_collation.php` có thể chạy nhiều lần nếu cần- 📝 Kiểm tra xem migration đã chạy chưa trước khi chạy lại



------



## 🐛 Troubleshooting## 🌱 seeds/ - Data Seeding



### Lỗi "Illegal mix of collations"Chứa dữ liệu mẫu để test.

**Nguyên nhân:** Bảng có collation khác nhau (utf8mb4_unicode_ci vs utf8mb4_general_ci)

### Files:

**Giải pháp:**- **`seed_phong_hoc.sql`** - Dữ liệu mẫu 10 phòng học

```bash

cd scripts/migrations### Cách dùng:

php fix_collation.php```bash

```mysql -u root -p HeThongQuanLyHocSinh1 < scripts/seeds/seed_phong_hoc.sql

```

### Lỗi "Table already exists"

**Nguyên nhân:** Migration đã được chạy trước đó### Mục đích:

- Tạo dữ liệu test

**Giải pháp:** Không cần chạy lại, bỏ qua lỗi này- Development và demo

- Không dùng trong production

### Lỗi "Connection refused"

**Nguyên nhân:** Sai thông tin database trong `config/database.php`---



**Giải pháp:** Kiểm tra và cập nhật:## 🔧 utilities/ - Helper Scripts

- Database host

- Database nameChứa các script PHP để setup, test và maintain database.

- Username/password

### 📋 Setup Scripts

---

| Script | Mô tả | Khi nào dùng |

## 🔒 Security|--------|-------|--------------|

| `add_missing_teachers.php` | Thêm 28 giáo viên với tên tự nhiên | Setup ban đầu |

### .htaccess Protection| `add_years.php` | Tạo 4 năm học (2023-2027) | Setup ban đầu |

File `.htaccess` trong thư mục này **chặn tất cả truy cập từ web**:| `update_teacher_names.php` | Cập nhật tên GV thành tên VN | Sửa dữ liệu cũ |



```apache**Cách dùng:**

# Deny access to this directory```bash

Require all deniedcd scripts/utilities

```php add_years.php

php add_missing_teachers.php

### Best Practices:```

- ❌ KHÔNG deploy scripts/ lên production nếu không cần

- ❌ KHÔNG chạy scripts trực tiếp từ browser---

- ✅ Chỉ chạy scripts từ command line (terminal)

- ✅ Kiểm tra `.htaccess` có hoạt động đúng không### 📊 Assignment Scripts



---| Script | Mô tả | Khi nào dùng |

|--------|-------|--------------|

## 📦 Backup Files| `assign_gvcn_random.php` | Gán GVCN ngẫu nhiên cho tất cả lớp | Tạo dữ liệu mẫu |

| `copy_assignments_to_years.php` | Copy phân công GD sang các năm khác | Tạo multi-year data |

Các file quan trọng đã được backup tại: `backup_scripts/`| `copy_gvcn_to_years.php` | Copy phân công GVCN sang các năm | Tạo multi-year data |

| `create_full_assignments.php` | Tạo đầy đủ phân công (GVCN + GD) | Setup toàn bộ |

Bao gồm:| `create_table_phan_cong.php` | Tạo bảng PhanCongGiangDay | Alternative migration |

- ✅ `fix_collation.php` - Script fix collation

- ✅ `hethongquanlyhocsinh1.sql` - Full database dump**Cách dùng:**

- ✅ `seed_phong_hoc.sql` - Seed data 10 phòng học```bash

cd scripts/utilities

---

# 1. Gán GVCN cho tất cả lớp

## 📋 Setup Database từ đầuphp assign_gvcn_random.php



### Bước 1: Restore Database# 2. Tạo phân công giảng dạy đầy đủ

```bashphp create_full_assignments.php

# Import database backup

mysql -u root -p < backup_scripts/hethongquanlyhocsinh1.sql# 3. Copy sang các năm khác

```php copy_gvcn_to_years.php

php copy_assignments_to_years.php

### Bước 2: Chạy Migrations```

```bash

cd scripts/migrations---



# Chạy các migration### ✅ Check Scripts

php run_migration.php

php run_migration_phong_hoc.php| Script | Mô tả | Output |

```|--------|-------|--------|

| `check_all_assignments.php` | Kiểm tra tất cả phân công | Thống kê tổng quan |

### Bước 3: Seed Data (Optional)| `check_gvcn.php` | Kiểm tra phân công GVCN | Danh sách GVCN theo lớp |

```bash| `check_lop_structure.php` | Kiểm tra cấu trúc bảng LopHoc | Schema và data |

# Import seed data phòng học| `check_monhoc.php` | Kiểm tra danh sách môn học | 12 môn học |

mysql -u root -p HeThongQuanLyHocSinh1 < backup_scripts/seed_phong_hoc.sql| `check_phan_cong_structure.php` | Kiểm tra bảng phân công | Schema validation |

```| `check_users.php` | Kiểm tra users và roles | Danh sách tài khoản |



### Bước 4: Fix Collation (Nếu cần)**Cách dùng:**

```bash```bash

cd scripts/migrationscd scripts/utilities

php fix_collation.php

```# Kiểm tra phân công

php check_all_assignments.php

---php check_gvcn.php



## 📞 Support# Kiểm tra structure

php check_lop_structure.php

Nếu gặp lỗi khi chạy migration:php check_phan_cong_structure.php



1. **Kiểm tra database connection**: `config/database.php`# Kiểm tra dữ liệu

2. **Kiểm tra permissions**: Scripts cần quyền đọc databasephp check_monhoc.php

3. **Check PHP errors**: Bật `display_errors` trong developmentphp check_users.php

4. **Xem logs**: Check error logs của PHP và MySQL```



------



## 📝 Change Log### 🧹 Cleanup Scripts



**Version 2.0 (October 30, 2025)**| Script | Mô tả | ⚠️ Cảnh báo |

- ✅ Xóa toàn bộ test/demo scripts (26 files)|--------|-------|-------------|

- ✅ Chỉ giữ lại migrations cần thiết| `clear_old_assignments.php` | Xóa phân công cũ | XÓA DỮ LIỆU! Backup trước |

- ✅ Backup files quan trọng vào `backup_scripts/`| `free_rooms_for_other_years.php` | Giải phóng phòng cho năm khác | Cẩn thận với năm học |

- ✅ Tối ưu cấu trúc theo production standards

**Cách dùng:**

**Version 1.0 (Initial)**```bash

- Database management scriptscd scripts/utilities

- Setup utilities

- Test/check scripts# ⚠️ BACKUP DATABASE TRƯỚC!

mysqldump -u root -p HeThongQuanLyHocSinh1 > backup_before_cleanup.sql

---

# Xóa phân công cũ

**Lưu ý:** Scripts đều có comments chi tiết trong code. Đọc kỹ trước khi chạy!php clear_old_assignments.php


# Giải phóng phòng
php free_rooms_for_other_years.php
```

---

### 🔧 Fix Scripts

| Script | Mô tả | Khi nào dùng |
|--------|-------|--------------|
| `fix_usernames.php` | Sửa username theo format chuẩn | Fix lỗi username |

**Cách dùng:**
```bash
cd scripts/utilities
php fix_usernames.php
```

---

### 🧪 Test Scripts

| Script | Mô tả | Output |
|--------|-------|--------|
| `test_find_teacher.php` | Test tìm GV theo môn | Debug search function |

**Cách dùng:**
```bash
cd scripts/utilities
php test_find_teacher.php
```

---

## 📋 Workflow Setup Database từ đầu

### Bước 1: Import Database
```bash
mysql -u root -p < scripts/database/hethongquanlyhocsinh1.sql
```

### Bước 2: Chạy Migrations
```bash
cd scripts/migrations
php run_migration.php
php run_migration_phong_hoc.php
```

### Bước 3: Seed Dữ liệu cơ bản
```bash
cd scripts/seeds
mysql -u root -p HeThongQuanLyHocSinh1 < seed_phong_hoc.sql
```

### Bước 4: Setup Dữ liệu
```bash
cd scripts/utilities
php add_years.php                # Tạo 4 năm học
php add_missing_teachers.php     # Tạo 28 giáo viên
```

### Bước 5: Tạo Phân công
```bash
php create_full_assignments.php  # Tạo phân công cho năm 2024-2025
php copy_gvcn_to_years.php       # Copy GVCN sang các năm
php copy_assignments_to_years.php # Copy GD sang các năm
```

### Bước 6: Kiểm tra
```bash
php check_all_assignments.php    # Xem tổng quan
php check_gvcn.php               # Xem GVCN
```

---

## ⚠️ Lưu ý quan trọng

### ❌ KHÔNG được:
- ❌ Chạy scripts trực tiếp từ browser
- ❌ Deploy thư mục `scripts/` lên production server
- ❌ Chạy cleanup scripts mà không backup
- ❌ Chạy migration nhiều lần

### ✅ NÊN:
- ✅ Backup database trước khi chạy scripts
- ✅ Test trên local trước khi chạy trên server
- ✅ Đọc code script để hiểu nó làm gì
- ✅ Chạy check scripts sau khi thay đổi dữ liệu

### 🔒 Security:
- Scripts này chỉ dùng cho **development**
- Không có authentication/authorization
- Truy cập trực tiếp database
- **PHẢI xóa trên production server**

---

## 🗑️ Xóa Scripts trước khi Deploy

Trước khi deploy lên production:

```bash
# Xóa thư mục scripts
rm -rf scripts/

# Hoặc thêm vào .gitignore
echo "scripts/" >> .gitignore
```

Hoặc cấu hình `.htaccess`:
```apache
# Chặn truy cập scripts/
<Directory "scripts">
    Require all denied
</Directory>
```

---

## 📞 Support

Nếu gặp lỗi khi chạy scripts:

1. **Kiểm tra database connection**: `config/database.php`
2. **Kiểm tra permissions**: Scripts cần quyền đọc/ghi
3. **Check PHP errors**: Bật `display_errors` trong development
4. **Xem logs**: Check error logs của PHP và MySQL

---

**Lưu ý:** Tất cả scripts đều có comments giải thích trong code. Đọc kỹ trước khi chạy!

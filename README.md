# 👥 Module Phân công Giảng dạy và Phòng học

Module quản lý phân công giáo viên và phòng học cho trường THPT, được xây dựng theo mô hình **MVC chuẩn**.

## 📋 Tính năng chính

### �‍🏫 Phân công Giáo viên Chủ nhiệm (GVCN)
- ✅ Gán/hủy giáo viên chủ nhiệm cho lớp
- ✅ Kiểm tra giáo viên đã làm chủ nhiệm lớp nào chưa
- ✅ Tự động lọc giáo viên đã có lớp chủ nhiệm
- ✅ Hiển thị thông tin GVCN theo lớp

### � Phân công Phòng học
- ✅ Gán/hủy phòng học cho lớp theo năm học
- ✅ Hỗ trợ multi-year (1 phòng có thể gán cho nhiều năm học khác nhau)
- ✅ Kiểm tra phòng đã được gán trong năm học hiện tại
- ✅ Tự động lọc phòng khả dụng cho năm học
- ✅ Hiển thị sức chứa và trạng thái phòng

### 📚 Phân công Giáo viên Bộ môn
- ✅ Gán giáo viên dạy từng môn học cho lớp
- ✅ **Giới hạn tối đa 5 lớp/giáo viên/học kỳ** (cấu hình được)
- ✅ Tự động ẩn giáo viên đã đạt giới hạn khỏi danh sách chọn
- ✅ Lọc giáo viên theo môn phụ trách
- ✅ Quản lý theo học kỳ (HK1, HK2, Cả năm)
- ✅ Thêm/sửa/xóa phân công linh hoạt
- ✅ Hiển thị số lớp đang dạy của mỗi giáo viên

### 📊 Quản lý theo Năm học
- ✅ Filter theo năm học
- ✅ Hỗ trợ nhiều năm học độc lập
- ✅ Dữ liệu phân công tách biệt theo năm


## 🏗️ Kiến trúc MVC

### Cấu trúc theo MVC Pattern
```
📂 models/bgh/
   └── PhanCongModel.php           → Data Access Layer (622 dòng)
   
📂 controllers/bgh/
   ├── assignment_controller.php          → Logic GVCN & Phòng học (234 dòng)
   └── teacher_assignment_controller.php  → Logic GV bộ môn (261 dòng)

📂 views/bgh/assignments/
   ├── manage.php                  → UI quản lý GVCN & Phòng (486 dòng)
   └── assign_teachers.php         → UI phân công GV bộ môn (386 dòng)

📂 public/bgh/assignments/
   ├── manage.php                  → Entry point GVCN & Phòng
   ├── assign_teachers.php         → Entry point GV bộ môn
   ├── index.php                   → Redirect
   └── api/
       ├── get_giao_vien.php              → API lấy danh sách GVCN
       ├── get_giao_vien_theo_mon.php     → API lấy GV theo môn (có lọc limit)
       └── get_phong_hoc.php              → API lấy phòng khả dụng
```

### Luồng xử lý (MVC Flow)
```
1. User truy cập: /public/bgh/assignments/manage.php
2. Entry Point:   Kiểm tra authentication & role
3. Controller:    Xử lý business logic, gọi Model
4. Model:         Truy vấn database, trả về PDOStatement
5. Controller:    Xử lý data, load View
6. View:          Render HTML + JavaScript
7. JavaScript:    Gọi API để lấy dữ liệu động (AJAX)
8. API:           Controller trả về JSON
```


### Tech Stack
- **Backend**: PHP 8.x với PDO
- **Database**: MySQL 5.7+ (utf8mb4_general_ci)
- **Frontend**: Bootstrap 5.3, Vanilla JavaScript (ES6+)
- **UI Libraries**: DataTables, FontAwesome 6
- **Architecture**: MVC chuẩn, RESTful APIs

### Đặc điểm kỹ thuật
- ✅ **Prepared Statements** - Ngăn chặn SQL Injection
- ✅ **RESTful API** - AJAX endpoints trả về JSON
- ✅ **Role-based Access Control** - Middleware kiểm tra quyền
- ✅ **Multi-year Support** - Quản lý nhiều năm học độc lập
- ✅ **Collation nhất quán** - utf8mb4_general_ci cho tất cả bảng
- ✅ **Foreign Keys** - Đảm bảo referential integrity


## 🚀 Installation

### Prerequisites
- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx với mod_rewrite
- Web browser hiện đại (Chrome, Firefox, Edge)

### Setup Database

1. **Import database schema**
```bash
mysql -u root -p < scripts/database/hethongquanlyhocsinh1.sql
```

2. **Chạy migrations phân công**
```bash
cd scripts/migrations

# Migration phân công giảng dạy (bảng PhanCongGiangDay)
php run_migration.php

# Migration phân công phòng học (bảng PhanCongPhongHoc)
php run_migration_phong_hoc.php

# Fix collation mismatch (nếu gặp lỗi)
php fix_collation.php
```

3. **Seed dữ liệu mẫu (Optional - Development only)**
```bash
cd scripts/utilities

# Tạo 4 năm học (2023-2027)
php add_years.php

# Tạo 28 giáo viên với môn phụ trách
php add_missing_teachers.php

# Tạo phân công đầy đủ cho năm 2024-2025
php create_full_assignments.php

# Import 10 phòng học mẫu
mysql -u root -p HeThongQuanLyHocSinh1 < scripts/seeds/seed_phong_hoc.sql
```

### Configure Application

1. **Cấu hình database**
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'HeThongQuanLyHocSinh1');
define('DB_USER', 'root');
define('DB_PASS', '');
```

2. **Cấu hình giới hạn phân công**
```php
// config/config.php
define('MAX_CLASSES_PER_TEACHER', 5); // Số lớp tối đa mỗi GV
```

### Start Application

**Cách 1: PHP Built-in Server (Development)**
```bash
php -S localhost:8000
```

**Cách 2: Apache/Nginx (Production)**
- Trỏ document root đến thư mục project
- Đảm bảo mod_rewrite được bật

**Access:**
```
http://localhost:8000/public/bgh/assignments/manage.php
```

### Default Login
```
Role: BGH (Ban Giám Hiệu)
Username: bgh01
Password: bgh123
```


## 📂 Cấu trúc Files

### Phân công GVCN & Phòng học
```
models/bgh/PhanCongModel.php
├── getAllLopHoc($namHoc)              → Lấy danh sách lớp
├── getGiaoVienChuaChuNhiem($maLop)    → Lấy GV chưa làm GVCN
├── getPhongHocChuaGan($maLop, $namHoc)→ Lấy phòng khả dụng
├── ganGVCN($maLop, $maGV)             → Gán GVCN
├── ganPhongHoc($maLop, $maPhong)      → Gán phòng
├── xoaGVCN($maLop)                    → Xóa GVCN
└── xoaPhongHoc($maLop)                → Xóa phòng

controllers/bgh/assignment_controller.php
├── index()                            → Hiển thị trang chính
├── ganGVCN()                          → Xử lý gán GVCN
├── ganPhongHoc()                      → Xử lý gán phòng
├── getGiaoVien()                      → API lấy danh sách GV
└── getPhongHoc()                      → API lấy danh sách phòng

views/bgh/assignments/manage.php
└── UI quản lý GVCN & Phòng học
    ├── DataTable danh sách lớp
    ├── Modal gán GVCN
    ├── Modal gán phòng
    └── JavaScript AJAX calls
```

### Phân công Giáo viên Bộ môn
```
models/bgh/PhanCongModel.php
├── getAllMonHoc($namHoc)                          → Lấy 12 môn học
├── getGiaoVienTheoMon($monHoc)                    → Lấy GV theo môn
├── getPhanCongGiangDay($maLop, $namHoc, $hocKy)   → Lấy phân công lớp
├── themPhanCongGiangDay(...)                      → Thêm phân công
├── capNhatPhanCongGiangDay(...)                   → Sửa phân công
├── xoaPhanCongGiangDay($maPhanCong)               → Xóa phân công
├── demSoLopGVDang($maGV, $namHoc, $hocKy)         → Đếm số lớp GV dạy
└── kiemTraGioiHanLop($maGV, $namHoc, $hocKy)      → Kiểm tra giới hạn 5 lớp

controllers/bgh/teacher_assignment_controller.php
├── index()                            → Hiển thị trang phân công
├── themPhanCong()                     → Thêm phân công mới
├── suaPhanCong()                      → Đổi giáo viên
├── xoaPhanCong()                      → Xóa phân công
└── getGiaoVienTheoMon()               → API lấy GV theo môn (có lọc limit)

views/bgh/assignments/assign_teachers.php
└── UI phân công GV bộ môn
    ├── 12 cards môn học
    ├── Modal thêm/sửa phân công
    ├── Filter theo học kỳ
    └── JavaScript AJAX với lọc GV đạt giới hạn
```


## �️ Database Schema

### Bảng chính
```sql
-- Lớp học
LopHoc (maLop, tenLop, khoi, siSo, namHoc)

-- Giáo viên
GiaoVienBoMon (maGV, hoTen, email, soDienThoai, monHocPhuTrach)

-- Phòng học
PhongHoc (maPhong, tenPhong, loaiPhong, sucChua, trangThai)

-- Môn học (12 môn THPT)
MonHoc (maMonHoc, tenMon, soTietTuan, loaiMonHoc, hocKy, namHoc)
```

### Bảng phân công
```sql
-- Phân công GVCN
GiaoVienChuNhiem (
    maGV VARCHAR(20),           -- FK → GiaoVienBoMon
    lop VARCHAR(20),            -- FK → LopHoc
    namHoc VARCHAR(15),
    PRIMARY KEY (lop),          -- 1 lớp chỉ có 1 GVCN
    UNIQUE KEY (maGV)           -- 1 GV chỉ làm GVCN 1 lớp
)

-- Phân công Phòng học (Multi-year)
PhanCongPhongHoc (
    maPhanCong VARCHAR(50),     -- PK: format {maPhong}_{maLop}_{namHoc}
    maPhong VARCHAR(20),        -- FK → PhongHoc
    maLop VARCHAR(20),          -- FK → LopHoc
    namHoc VARCHAR(15),
    ngayPhanCong DATETIME,
    UNIQUE KEY (maPhong, namHoc),  -- 1 phòng cho 1 lớp/năm
    UNIQUE KEY (maLop, namHoc)     -- 1 lớp có 1 phòng/năm
)

-- Phân công Giảng dạy (GV bộ môn)
PhanCongGiangDay (
    maPhanCong VARCHAR(50),     -- PK: format PC_{maLop}_{maMonHoc}_{timestamp}
    maLop VARCHAR(20),          -- FK → LopHoc
    maMonHoc VARCHAR(20),       -- FK → MonHoc
    maGV VARCHAR(20),           -- FK → GiaoVienBoMon
    namHoc VARCHAR(15),
    hocKy VARCHAR(10),          -- 'HK1', 'HK2', 'Cả năm'
    ghiChu VARCHAR(255),
    ngayPhanCong DATETIME,
    UNIQUE KEY (maLop, maMonHoc, namHoc, hocKy)  -- 1 môn 1 GV/học kỳ
)
```

### Ràng buộc nghiệp vụ
- ✅ 1 lớp chỉ có 1 GVCN
- ✅ 1 giáo viên chỉ làm GVCN 1 lớp
- ✅ 1 phòng chỉ gán cho 1 lớp trong 1 năm học
- ✅ 1 lớp chỉ có 1 phòng trong 1 năm học
- ✅ 1 môn trong 1 lớp chỉ có 1 giáo viên dạy trong 1 học kỳ
- ✅ 1 giáo viên bộ môn tối đa dạy 5 lớp trong 1 học kỳ (configurable)

### Collation
Tất cả bảng và cột sử dụng `utf8mb4_general_ci` để tránh lỗi "Illegal mix of collations".


## 🎯 Luồng sử dụng

### Phân công GVCN
1. Truy cập `/public/bgh/assignments/manage.php`
2. Chọn năm học từ dropdown filter
3. Click nút "Gán GVCN" ở hàng lớp muốn phân công
4. Modal hiện ra với danh sách giáo viên chưa làm GVCN
5. Chọn giáo viên → Click "Xác nhận"
6. Hệ thống kiểm tra: GV đã làm GVCN lớp khác chưa?
7. Nếu hợp lệ → Lưu vào bảng `GiaoVienChuNhiem`
8. Refresh trang → Hiển thị tên GVCN vừa gán

**Hủy GVCN:** Click nút đỏ "Xóa GVCN" → Confirm → Xóa khỏi DB

### Phân công Phòng học
1. Truy cập `/public/bgh/assignments/manage.php`
2. Chọn năm học từ dropdown filter
3. Click nút "Gán Phòng" ở hàng lớp muốn phân công
4. Modal hiện ra với danh sách phòng khả dụng (chưa gán trong năm đó)
5. Chọn phòng → Click "Xác nhận"
6. Hệ thống kiểm tra: Phòng đã gán cho lớp khác trong năm này chưa?
7. Nếu hợp lệ → Lưu vào bảng `PhanCongPhongHoc`
8. Refresh trang → Hiển thị tên phòng vừa gán

**Hủy phòng:** Click nút vàng "Xóa Phòng" → Confirm → Xóa khỏi DB

### Phân công GV Bộ môn
1. Từ trang manage.php, click "Phân công GV bộ môn" ở hàng lớp
2. Chuyển đến `/public/bgh/assignments/assign_teachers.php?maLop=...`
3. Chọn học kỳ (HK1/HK2/Cả năm) từ dropdown
4. Thấy 12 cards môn học với trạng thái:
   - 🟢 Đã phân công → Hiện tên GV, email, SĐT
   - 🔴 Chưa phân công → Nút "Phân công"
5. Click "Phân công" trên môn chưa có GV
6. Modal hiện ra với dropdown giáo viên:
   - **Chỉ hiện GV phụ trách môn đó**
   - **Tự động ẨN GV đã dạy đủ 5 lớp**
   - Hiển thị số lớp đang dạy (ví dụ: "Nguyễn Văn A - Toán (4/5 lớp)")
7. Chọn GV → Click "Xác nhận"
8. Hệ thống kiểm tra:
   - GV có dạy môn này không?
   - GV đã dạy đủ 5 lớp chưa?
   - Môn này trong lớp đã có GV chưa?
9. Nếu hợp lệ → Lưu vào `PhanCongGiangDay`
10. Card môn chuyển sang 🟢 và hiện thông tin GV

**Đổi GV:** Click "Đổi GV" → Chọn GV khác → Confirm  
**Xóa phân công:** Click nút đỏ "Xóa" → Confirm → Card về 🔴


## 🔐 Security Features

### Authentication & Authorization
- ✅ **Middleware AuthGuard**: Kiểm tra đăng nhập trước khi truy cập
- ✅ **Role-based Access**: `require_role(['bgh', 'admin'])` chỉ cho BGH/Admin
- ✅ **Session Management**: Lưu thông tin user trong $_SESSION

### SQL Injection Prevention
```php
// ❌ KHÔNG làm thế này
$sql = "SELECT * FROM GiaoVienBoMon WHERE maGV = '$maGV'";

// ✅ Sử dụng Prepared Statements
$stmt = $db->prepare("SELECT * FROM GiaoVienBoMon WHERE maGV = ?");
$stmt->execute([$maGV]);
```

### XSS Prevention
```php
// ✅ Escape output trong views
echo htmlspecialchars($giaoVien['hoTen'], ENT_QUOTES, 'UTF-8');
```

### CSRF Protection
- Form submissions kiểm tra HTTP_REFERER
- AJAX requests có proper headers

### Input Validation
- Server-side validation trong Controllers
- Client-side validation với HTML5 + JavaScript
- Kiểm tra ràng buộc nghiệp vụ trước khi insert/update


## 🧪 Testing & Debugging

### Manual Testing Checklist
```
□ Login với tài khoản BGH
□ Chọn năm học từ dropdown filter
□ Gán GVCN cho lớp chưa có GVCN
□ Thử gán GVCN cho lớp đã có GVCN (test update)
□ Xóa GVCN
□ Gán phòng học cho lớp
□ Thử gán phòng đã được gán cho lớp khác trong năm (test validation)
□ Xóa phòng học
□ Vào trang phân công GV bộ môn
□ Chọn học kỳ HK1
□ Phân công GV cho môn chưa có
□ Kiểm tra dropdown chỉ hiện GV dạy đúng môn
□ Kiểm tra GV đã dạy 5 lớp KHÔNG hiện trong dropdown
□ Đổi GV dạy môn đã phân công
□ Xóa phân công
□ Chuyển sang HK2, kiểm tra dữ liệu độc lập
```

### Debug với PHP Error Log
```php
// Trong controller hoặc model
error_log("Debug - maLop: " . $maLop);
error_log("Debug - Số phòng tìm thấy: " . count($phongHocs));
```

**Xem logs:**
- XAMPP: `xampp/apache/logs/error.log`
- WAMP: `wamp/logs/php_error.log`

### Debug AJAX Calls
```javascript
// Trong JavaScript console (F12)
fetch('api/get_giao_vien_theo_mon.php?tenMon=Toán')
    .then(res => res.json())
    .then(data => console.log('Response:', data));
```

### Check Scripts
```bash
cd scripts/utilities

# Kiểm tra tất cả phân công
php check_all_assignments.php

# Kiểm tra GVCN
php check_gvcn.php

# Kiểm tra cấu trúc bảng
php check_phan_cong_structure.php
```


## ⚠️ Troubleshooting

### Lỗi "Illegal mix of collations"
**Nguyên nhân:** Bảng `PhanCongPhongHoc` có collation khác với `PhongHoc` và `LopHoc`

**Giải pháp:**
```bash
cd scripts/migrations
php fix_collation.php
```

### Dropdown không hiện danh sách
**Nguyên nhân:** Đường dẫn require sai trong API files

**Kiểm tra:**
- File trong `public/bgh/assignments/` cần `../../../` (3 cấp)
- File trong `public/bgh/assignments/api/` cần `../../../../` (4 cấp)

**Xem logs:** Mở F12 → Console → Xem lỗi JavaScript

### Database connection failed
**Kiểm tra:**
```php
// config/database.php
- Host đúng chưa? (localhost)
- Database name đúng chưa? (HeThongQuanLyHocSinh1)
- Username/password đúng chưa?
- MySQL service đã chạy chưa?
```

### Không gán được phòng/GVCN
**Nguyên nhân:** Ràng buộc UNIQUE KEY vi phạm

**Kiểm tra:**
```sql
-- Xem phòng đã gán chưa
SELECT * FROM PhanCongPhongHoc WHERE maPhong = 'P101' AND namHoc = '2024-2025';

-- Xem GV đã làm GVCN chưa
SELECT * FROM GiaoVienChuNhiem WHERE maGV = 'GV001';
```

### Giáo viên không hiện trong dropdown phân công môn
**Nguyên nhân:** 
1. GV không phụ trách môn đó
2. GV đã dạy đủ 5 lớp trong học kỳ

**Kiểm tra:**
```sql
-- Xem GV phụ trách môn gì
SELECT maGV, hoTen, monHocPhuTrach FROM GiaoVienBoMon;

-- Đếm số lớp GV đang dạy
SELECT COUNT(DISTINCT maLop) 
FROM PhanCongGiangDay 
WHERE maGV = 'GV001' AND namHoc = '2024-2025' AND hocKy = 'HK1';
```


## 🎨 UI/UX Features

### Design System
- **Bootstrap 5.3** - Responsive grid & components
- **Gradient backgrounds** - Modern card designs
- **Smooth animations** - fadeIn, hover effects
- **Icons** - FontAwesome 6 cho tất cả actions
- **Color coding:**
  - 🟢 Xanh lá: Đã phân công, actions thành công
  - 🔴 Đỏ: Chưa phân công, actions xóa
  - 🟡 Vàng: Actions cảnh báo
  - 🔵 Xanh dương: Actions thông tin

### Interactive Elements
- **DataTables**: Tìm kiếm, sort, pagination tự động
- **Modals**: Bootstrap modals cho forms
- **AJAX**: Load data không reload trang
- **Real-time filtering**: Dropdown năm học, học kỳ
- **Responsive**: Hoạt động tốt trên mobile, tablet, desktop

### User Feedback
- ✅ **Success alerts**: "Phân công thành công!"
- ❌ **Error alerts**: "Giáo viên đã làm GVCN lớp khác"
- ⚠️ **Confirm dialogs**: "Bạn có chắc muốn xóa?"
- 📊 **Status badges**: Hiển thị trạng thái rõ ràng

## 🔧 Configuration

### Giới hạn phân công
```php
// config/config.php
define('MAX_CLASSES_PER_TEACHER', 5);    // Tối đa 5 lớp/GV/học kỳ
define('MAX_SUBJECTS_PER_TEACHER', 3);   // Tối đa 3 môn/GV (tùy chọn)
```

### Database charset
```php
// config/database.php
$pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
```

### Session timeout
```php
// config/config.php
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```


## 📝 Notes & Best Practices

### Khi thêm năm học mới
```bash
cd scripts/utilities
php add_years.php  # Thêm năm học vào bảng LopHoc
```
- Dữ liệu phân công của mỗi năm độc lập
- Phòng học có thể tái sử dụng cho nhiều năm
- GVCN và GV bộ môn cần phân công lại cho năm mới

### Khi backup database
```bash
mysqldump -u root -p HeThongQuanLyHocSinh1 > backup_$(date +%Y%m%d).sql
```

### Khi gặp lỗi collation
- Chạy `scripts/migrations/fix_collation.php`
- Hoặc manual: Đổi tất cả về `utf8mb4_general_ci`

### Code style
- **PSR-12** coding standards
- **camelCase** cho methods: `getAllLopHoc()`, `ganGVCN()`
- **snake_case** cho database: `ma_lop`, `ten_lop`
- **PascalCase** cho classes: `PhanCongModel`, `AssignmentController`

### Comments
```php
/**
 * Gán giáo viên chủ nhiệm cho lớp
 * @param string $maLop - Mã lớp
 * @param string $maGV - Mã giáo viên
 * @return bool - True nếu thành công
 */
public function ganGVCN($maLop, $maGV) {
    // Implementation
}
```

## 🚀 Future Enhancements

### Planned Features
- [ ] Xuất báo cáo phân công ra Excel
- [ ] Import danh sách giáo viên từ Excel
- [ ] Dashboard thống kê tải công việc GV
- [ ] Lịch sử thay đổi phân công (audit log)
- [ ] Thông báo email khi phân công mới
- [ ] API REST cho mobile app
- [ ] Giới hạn số tiết/tuần thay vì số lớp
- [ ] Tự động phân công thông minh (AI)

### Performance Optimization
- [ ] Cache danh sách giáo viên, phòng học
- [ ] Lazy loading cho DataTables
- [ ] CDN cho Bootstrap, FontAwesome
- [ ] Minify CSS/JS

## � Support & Contact

### Documentation
- 📖 **README.md** - Hướng dẫn này
- 📋 **scripts/README.md** - Hướng dẫn sử dụng scripts
- � **Inline comments** - Chi tiết trong code

### Issues & Bugs
Nếu gặp lỗi, vui lòng cung cấp:
1. Mô tả lỗi chi tiết
2. Steps to reproduce
3. Screenshots (nếu có)
4. PHP error logs
5. Browser console logs (F12)

### Tech Stack References
- [PHP Documentation](https://www.php.net/docs.php)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [DataTables](https://datatables.net/)
- [FontAwesome 6](https://fontawesome.com/icons)

---

## 📄 License

This project is for **educational purposes** - PTUD (Phát triển ứng dụng) course project.

## 👥 Team

**Project:** Hệ thống Quản lý Học sinh  
**Module:** Phân công Giảng dạy và Phòng học  
**Team:** HK1 Năm 4 - PTUD  
**Repository:** [GitHub - Learning-Systems](https://github.com/trmizy/Learning-Systems)

---

**Version:** 2.0 (MVC Refactored)  
**Last Updated:** October 30, 2025  
**Status:** ✅ Production Ready  
**Branch:** `phanCongGiangDayVaPhongHoc`

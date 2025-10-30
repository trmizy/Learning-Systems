# 🎓 Hệ thống Quản lý Học sinh - Learning Systems

Hệ thống quản lý toàn diện cho trường trung học phổ thông, được xây dựng theo mô hình **MVC chuẩn**.

## 📋 Tính năng chính

### 🔐 Authentication & Authorization
- Multi-role system: Admin, BGH, GVBM, GVCN, Học sinh, Phụ huynh, Nhân viên sổ
- Session-based authentication
- Role-based access control

### 👥 Quản lý Phân công (BGH)
- ✅ **Phân công GVCN** - Gán giáo viên chủ nhiệm cho lớp
- ✅ **Phân công Phòng học** - Gán phòng học cho lớp theo năm
- ✅ **Phân công Giảng dạy** - Gán giáo viên bộ môn dạy từng môn cho lớp
- ✅ **Multi-year support** - Hỗ trợ nhiều năm học
- ✅ **Real-time validation** - Kiểm tra ràng buộc khi phân công

### 📊 Dashboard
- Thống kê tổng quan theo role
- Quản lý phân công nhanh
- Notifications và alerts

## 🏗️ Kiến trúc

### MVC Pattern
```
📂 models/          → Data Access Layer
📂 controllers/     → Business Logic Layer  
📂 views/           → Presentation Layer
📂 modules/         → Entry Points (Routing)
📂 middlewares/     → Authentication Guards
```

### Tech Stack
- **Backend**: PHP 8.x, PDO, MySQL
- **Frontend**: Bootstrap 5, jQuery, DataTables
- **Icons**: FontAwesome 6
- **Architecture**: MVC, RESTful APIs

## 🚀 Installation

### Prerequisites
- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx web server
- Composer (optional)

### Setup

1. **Clone repository**
```bash
git clone https://github.com/trmizy/Learning-Systems.git
cd Learning-Systems
```

2. **Import database**
```bash
mysql -u root -p < scripts/database/hethongquanlyhocsinh1.sql
```

3. **Run migrations**
```bash
cd scripts/migrations
php run_migration.php
php run_migration_phong_hoc.php
```

4. **Configure database**
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'HeThongQuanLyHocSinh1');
define('DB_USER', 'root');
define('DB_PASS', '');
```

5. **Seed data (Development only)**
```bash
cd scripts/utilities
php add_years.php
php add_missing_teachers.php
php create_full_assignments.php
```

6. **Start server**
```bash
php -S localhost:8000
```

7. **Access application**
```
http://localhost:8000
```

### Default accounts
```
Admin:
- Username: admin
- Password: admin123

BGH:
- Username: bgh01
- Password: bgh123

GVBM:
- Username: gv01
- Password: gv123
```

## 📂 Cấu trúc Project

```
Learning-Systems/
├── 📂 config/              # Configuration files
├── 📂 controllers/         # Business logic (MVC Controller)
├── 📂 models/             # Data access (MVC Model)
├── 📂 views/              # UI templates (MVC View)
├── 📂 modules/            # Entry points & routing
├── 📂 middlewares/        # Auth guards
├── 📂 assets/             # CSS, JS, images
├── 📂 scripts/            # Development utilities
│   ├── database/          # DB schema
│   ├── migrations/        # DB migrations
│   ├── seeds/             # Sample data
│   └── utilities/         # Helper scripts
└── 📂 docs/               # Documentation
```

Xem chi tiết: [📖 PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md)

## 📖 Documentation

- [📋 MVC Structure](docs/MVC_STRUCTURE.md) - Giải thích mô hình MVC
- [🔄 MVC Refactoring](docs/MVC_REFACTORING_COMPLETE.md) - Chi tiết refactoring
- [📂 Project Structure](docs/PROJECT_STRUCTURE.md) - Cấu trúc project đầy đủ
- [🛠️ Scripts README](scripts/README.md) - Hướng dẫn sử dụng scripts

## 🎯 Modules

### Ban Giám Hiệu (BGH)
- Dashboard tổng quan
- **Phân công giảng dạy và phòng học** (MVC complete)
  - Quản lý GVCN
  - Quản lý phòng học
  - Phân công GV bộ môn theo môn học
- Phê duyệt và quản lý

### Giáo viên bộ môn (GVBM)
- Xem phân công
- Quản lý điểm
- Thời khóa biểu

### Học sinh (HS)
- Xem điểm
- Xem lịch học
- Thông tin lớp

### Phụ huynh (PH)
- Xem điểm con
- Liên hệ giáo viên

## 🔧 Development

### Running scripts
```bash
cd scripts/utilities

# Check data
php check_all_assignments.php
php check_gvcn.php

# Setup data
php add_years.php
php create_full_assignments.php
```

### Adding new feature
1. Create Model in `models/`
2. Create Controller in `controllers/{role}/`
3. Create View in `views/{role}/`
4. Create Entry point in `modules/{role}/`

Example:
```php
// 1. Model
models/student.php

// 2. Controller  
controllers/gvbm/student_controller.php

// 3. View
views/gvbm/students/list.php

// 4. Entry
modules/gvbm/students/list.php
```

## 🔐 Security

- ✅ Authentication middleware on all protected routes
- ✅ Prepared statements (PDO) - SQL injection prevention
- ✅ XSS protection with `htmlspecialchars()`
- ✅ Role-based access control
- ✅ Session management
- ✅ Input validation

## 🧪 Testing

### Manual testing checklist
- [ ] Login with all roles
- [ ] GVCN assignment (add/remove)
- [ ] Room assignment (add/remove)
- [ ] Teacher-subject assignment (add/edit/delete)
- [ ] Year filtering
- [ ] Search and filters
- [ ] Multi-year data consistency

### Check scripts
```bash
cd scripts/utilities
php check_all_assignments.php  # Verify all assignments
php check_gvcn.php             # Verify GVCN assignments
```

## 🚀 Deployment

### Before deploying to production:

1. **Remove development scripts**
```bash
rm -rf scripts/
```

2. **Update database config**
```php
// config/database.php
define('DB_HOST', 'production_host');
define('DB_NAME', 'production_db');
define('DB_USER', 'production_user');
define('DB_PASS', 'strong_password');
```

3. **Disable error display**
```php
// config/config.php
error_reporting(0);
ini_set('display_errors', 0);
```

4. **Setup .htaccess**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]
```

5. **Set proper permissions**
```bash
chmod 755 -R .
chmod 644 config/*.php
```

## 📊 Database

### Tables
- `LopHoc` - Classes
- `GiaoVienBoMon` - Teachers
- `GiaoVienChuNhiem` - Homeroom teachers
- `PhongHoc` - Classrooms
- `PhanCongPhongHoc` - Room assignments (multi-year)
- `MonHoc` - Subjects
- `PhanCongGiangDay` - Teaching assignments
- `HocSinh` - Students
- `PhuHuynh` - Parents
- `NguoiDung` - Users

### Multi-year support
Các bảng phân công hỗ trợ `namHoc` để quản lý nhiều năm học độc lập.

## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 👥 Authors

- **Trmizy** - [GitHub](https://github.com/trmizy)

## 📞 Support

- 📧 Email: support@learning-systems.com
- 📖 Documentation: [docs/](docs/)
- 🐛 Issues: [GitHub Issues](https://github.com/trmizy/Learning-Systems/issues)

## 🎉 Acknowledgments

- Bootstrap 5 for UI components
- DataTables for table management
- FontAwesome for icons
- PHP community

---

**Version:** 2.0 (Post MVC Refactoring)  
**Last Updated:** October 30, 2025  
**Status:** ✅ Production Ready

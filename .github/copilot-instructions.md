# Learning System - AI Coding Agent Instructions

## Project Overview
Vietnamese high school management system (THPT) managing students, teachers, classes, grades, schedules, and subject combinations. Uses vanilla PHP (no framework) with MySQL database.

## Architecture Fundamentals

### Routing System
- Entry point: `/public/index.php` handles all routing via query parameters
- URL pattern: `?action=<action_name>` or `?controller=<controller_name>`
- Examples:
  - `?action=enterPoints_gvbm` → `/controllers/gvbm/enterPointsController.php`
  - `?action=statistics_bgh` → `/controllers/bgh/statisticsController.php`
- Direct controller access also supported: `/controllers/admin/taoCacToHopMon_controller.php`

### Module vs MVC Structure
Project uses **hybrid approach**:
- **Modules** (`/modules/*`): Entry points that delegate to controllers, e.g., `/modules/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php` instantiates controller
- **MVC Pattern**: 
  - Controllers in `/controllers/{role}/` process logic
  - Models in `/models/{role}/` handle database operations  
  - Views in `/views/{role}/` render UI (include `layouts/header.php` and `layouts/footer.php`)

### Database Pattern
```php
// Standard singleton pattern used throughout
$db = Database::getInstance();
$conn = $db->getConnection();
// Use PDO prepared statements exclusively
$stmt = $conn->prepare("SELECT ... WHERE ... = :param");
$stmt->execute(['param' => $value]);
```

## Role-Based Access Control

### 8 User Roles (Vietnamese names required)
```php
'nhanvienso' => 'Nhân viên Sở Giáo dục'     // Education bureau staff
'admin' => 'Admin Nhân viên phòng giáo vụ'   // School admin
'bgh' => 'Ban giám hiệu'                     // Principal board
'ttbm' => 'Tổ trưởng bộ môn'                 // Department head
'gvcn' => 'Giáo viên chủ nhiệm'              // Homeroom teacher
'gvbm' => 'Giáo viên bộ môn'                 // Subject teacher
'phuhuynh' => 'Phụ huynh'                    // Parent
'hocsinh' => 'Học sinh'                       // Student
```

### Authorization Middleware
Every controller/view must use:
```php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['gvbm', 'gvcn']); // Allow multiple roles
```

Helper functions:
- `require_login()` - Enforce authentication
- `require_role(['role1', 'role2'])` - Enforce specific roles
- `current_user()` - Get current session user array
- `has_role('role')` - Check if user has role

### Permission Hierarchy
Defined in `/config/roles.php` - `ROLE_PERMISSIONS` array shows cascading permissions (e.g., nhanvienso has full access).

## Critical Conventions

### File Naming
- Controllers: PascalCase with 'Controller' suffix, e.g., `EnterPointsController.php` 
- Models: PascalCase with 'Model' suffix, e.g., `EnterPointsModel.php`
- Views: snake_case, e.g., `enter_points_view.php`, `duyet-don/list.php`
- Config files: camelCase, e.g., `database.php`, `roles.php`

### Flash Messages
Session-based flash messaging for user feedback:
```php
// Set in controller
$_SESSION['flash_success'] = 'Thao tác thành công';
$_SESSION['flash_error'] = 'Có lỗi xảy ra';
$_SESSION['flash_info'] = 'Thông tin';

// Consumed automatically in /public/index.php or views
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
```

### View Layout Pattern
All views must include header/footer:
```php
$pageTitle = 'Tên trang';
require_once __DIR__ . '/../layouts/header.php';
// ... view content ...
require_once __DIR__ . '/../layouts/footer.php';
```

### Database Naming
- Tables: lowercase, e.g., `hocsinh`, `giaovienbomon`, `bangdiem`
- Columns: camelCase with Vietnamese abbreviations:
  - `maHS` (mã học sinh - student ID)
  - `hoTen` (họ tên - full name)  
  - `maGV` (mã giáo viên - teacher ID)
  - `maLop` (mã lớp - class ID)
  - `diemThuongXuyen` (regular grade)
  - `diemGiuaKy` (midterm grade)
  - `diemCuoiKy` (final grade)

### Status Workflow Pattern
Many entities use approval workflows with statuses:
```php
'PENDING'  => 'Chờ duyệt'
'APPROVED' => 'Đã duyệt' 
'REJECTED' => 'Từ chối'
```
Examples: `tohopmon.trangThai`, grade correction requests.

## Key Workflows

### Creating New Feature for Role
1. Add route in `/public/index.php` under appropriate switch case
2. Create controller in `/controllers/{role}/YourController.php` with `require_role()`
3. Create model in `/models/{role}/YourModel.php` with PDO queries
4. Create view in `/views/{role}/your_view.php` with header/footer includes
5. Update menu in `/config/config.php` `$MENU_ITEMS` array if needed

### Debugging
Development error display enabled in `/public/index.php`:
```php
ini_set('display_errors', '1');
error_reporting(E_ALL);
```

### Teacher Data Retrieval Pattern
Common pattern to get teacher info from logged-in user:
```php
$maGV = null;
$stmt = $conn->prepare("
    SELECT gv.maGV FROM taikhoan tk
    INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
    WHERE tk.tenDangNhap = :username
");
$stmt->execute(['username' => $currentUser['username']]);
$result = $stmt->fetch();
if ($result) $maGV = $result['maGV'];
```

## Important Files

- `/config/config.php` - Menu items, constants, max assignments per teacher
- `/config/roles.php` - All role definitions and permission hierarchies  
- `/config/database.php` - Database singleton with .env support via vlucas/phpdotenv
- `/middlewares/AuthGuard.php` - All authentication/authorization helpers
- `/public/index.php` - Main router and entry point
- `/views/layouts/header.php` - Global navigation, includes role-based menu
- `/README.md` - Detailed Vietnamese documentation of major features

## Common Pitfalls

- Never bypass `require_role()` checks - security critical
- Always use prepared statements - no string concatenation in SQL
- Flash messages must be unset after reading to prevent re-display
- View paths are relative from view file location, use `__DIR__`
- Controllers must output buffer (`ob_start()`) if later redirecting
- Menu links must match actual routes defined in `/public/index.php`
- Vietnamese language strings should remain in Vietnamese throughout

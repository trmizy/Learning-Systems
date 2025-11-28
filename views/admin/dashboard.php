<?php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['admin']);

// Tiêu đề trang và header chung
$pageTitle = 'Bảng điều khiển - THPT';
require_once __DIR__ . '/../layouts/header.php';

// Lấy user hiện tại và chuẩn hoá dữ liệu hiển thị
$user = current_user() ?: [];
$fullName = isset($user['full_name']) ? $user['full_name'] : 'Người dùng';

// BẢO ĐẢM roles luôn là MẢNG
$roles = [];
if (isset($user['roles']) && is_array($user['roles'])) {
    $roles = $user['roles'];
}

// Số liệu demo (thay bằng truy vấn thật nếu cần)
$stats = [
    'students' => 1287,
    'classes'  => 42,
    'teachers' => 87,
    'subjects' => 18,
];
?>

<style>
    .admin-dashboard {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stat-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .feature-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
        position: relative;
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-banner h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .role-badge-large {
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 1rem;
        background: white;
        color: #667eea;
        display: inline-block;
        margin-top: 0.5rem;
    }
</style>

<div class="admin-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-hand-wave me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($fullName); ?>!
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-user-shield me-2"></i>Quản trị viên hệ thống
                </p>
                <span class="role-badge-large">
                    <i class="fa-solid fa-crown me-2"></i>Administrator
                </span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if (count($roles) > 1): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-tag me-2"></i>Đổi vai trò
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach ($roles as $role): ?>
                            <li>
                                <a class="dropdown-item" href="/public/index.php?action=switch&role=<?php echo htmlspecialchars($role); ?>">
                                    <i class="fa-solid fa-arrow-right me-2"></i><?php echo htmlspecialchars(getRoleName($role)); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['students']); ?></h3>
                        <p><i class="fa-solid fa-user-graduate me-2"></i>Tổng học sinh</p>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['classes']); ?></h3>
                        <p><i class="fa-solid fa-school me-2"></i>Tổng lớp</p>
                    </div>
                    <i class="fa-solid fa-door-open fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['teachers']); ?></h3>
                        <p><i class="fa-solid fa-chalkboard-teacher me-2"></i>Giáo viên</p>
                    </div>
                    <i class="fa-solid fa-user-tie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['subjects']); ?></h3>
                        <p><i class="fa-solid fa-book-open me-2"></i>Môn học</p>
                    </div>
                    <i class="fa-solid fa-book fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Student Management -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý học sinh</h5>
                    <p class="text-muted small">Hồ sơ, tiếp nhận, cập nhật thông tin</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/students/create.php" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus me-1"></i>Thêm mới
                        </a>
                        <a href="/modules/students/list.php" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Management -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h5 class="card-title fw-bold">Tài khoản</h5>
                    <p class="text-muted small">Tạo tài khoản học sinh và phụ huynh</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/accounts/create.php" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-user-plus me-1"></i>Tạo tài khoản
                        </a>
                        <a href="/modules/accounts/import.php" class="btn btn-outline-success btn-sm">
                            <i class="fa-solid fa-file-import me-1"></i>Import Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class & Teacher Assignment -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công</h5>
                    <p class="text-muted small">Phân công lớp, giáo viên, thời khóa biểu</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/assignments/teachers.php" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-person-chalkboard me-1"></i>Phân công GV
                        </a>
                        <a href="/controllers/admin/ArrangeTKBController.php?module=tkb" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-calendar me-1"></i>Thời khóa biểu
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Records -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h5 class="card-title fw-bold">Học vụ</h5>
                    <p class="text-muted small">Đơn sửa điểm, hồ sơ học vụ</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/academic/requests.php" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-clipboard-check me-1"></i>Duyệt đơn
                        </a>
                        <a href="/modules/academic/records.php" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-archive me-1"></i>Hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Admin Tools -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i>Quản lý người dùng
                    </h6>
                    <p class="text-muted small mb-3">Tài khoản, phân quyền hệ thống</p>
                    <a href="/modules/users/list.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-list me-2"></i>Danh sách tài khoản
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-sliders text-success me-2"></i>Cấu hình hệ thống
                    </h6>
                    <p class="text-muted small mb-3">Thiết lập tham số, cấu hình</p>
                    <a href="/modules/settings/index.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-cog me-2"></i>Cài đặt
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-database text-warning me-2"></i>Sao lưu & Phục hồi
                    </h6>
                    <p class="text-muted small mb-3">Backup dữ liệu hệ thống</p>
                    <a href="/modules/backup/index.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-download me-2"></i>Sao lưu ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

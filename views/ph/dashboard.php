<?php
// filepath: d:\Hk1_2025\PTUD_Nhom4\Đồ Án Nhóm\Learning_System\views\ph\dashboard.php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['ph']);

// Tiêu đề trang và header chung
$pageTitle = 'Trang phụ huynh - THPT';
require_once __DIR__ . '/../layouts/header.php';

// Lấy user hiện tại
$user = current_user() ?: [];
$fullName = isset($user['full_name']) ? $user['full_name'] : 'Phụ huynh';
$parentId = isset($user['parent_id']) ? $user['parent_id'] : 'PH0000';

// Thông tin con (demo - thay bằng query thật)
$childInfo = [
    'name' => 'Nguyễn Văn A',
    'student_id' => 'HS2024001',
    'class' => '12A1',
    'homeroom_teacher' => 'Trần Thị B'
];

// Số liệu demo
$stats = [
    'attendance_rate' => 96.5,
    'gpa_semester' => 8.7,
    'conduct_rating' => 'Tốt',
    'pending_requests' => 1,
    'unread_notifications' => 3,
    'violations' => 0,
    'rewards' => 2,
];

// Dữ liệu mẫu - Kết quả học tập gần đây
$recentGrades = [
    ['subject' => 'Toán', 'score' => 8.5, 'type' => 'Giữa kỳ', 'date' => '2024-03-15'],
    ['subject' => 'Văn', 'score' => 9.0, 'type' => 'Cuối kỳ', 'date' => '2024-03-14'],
    ['subject' => 'Anh', 'score' => 8.0, 'type' => '15 phút', 'date' => '2024-03-13'],
];

// Thông báo quan trọng
$notifications = [
    ['title' => 'Thông báo họp phụ huynh cuối học kỳ 2', 'type' => 'GVCN', 'date' => '2024-03-15', 'unread' => true, 'priority' => 'high'],
    ['title' => 'Con em bạn được khen thưởng học sinh giỏi', 'type' => 'BGH', 'date' => '2024-03-14', 'unread' => true, 'priority' => 'high'],
    ['title' => 'Nhắc nhở nộp học phí tháng 3', 'type' => 'Kế toán', 'date' => '2024-03-10', 'unread' => false, 'priority' => 'normal'],
];

// Khen thưởng & Vi phạm
$rewards = [
    ['title' => 'Học sinh giỏi học kỳ I', 'date' => '2024-01-15', 'type' => 'Khen thưởng'],
    ['title' => 'Giải nhất Olympic Toán cấp trường', 'date' => '2024-02-20', 'type' => 'Khen thưởng'],
];

$violations = [];
?>

<style>
    .parent-dashboard {
        animation: fadeIn 0.5s ease;
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
    
    .stat-card.danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
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
    
    .child-info-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-left: 4px solid #667eea;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .grade-item {
        padding: 1rem;
        border-radius: 12px;
        background: white;
        border: 1px solid #e9ecef;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .grade-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateX(5px);
    }
    
    .grade-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1.2rem;
    }
    
    .grade-excellent {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .grade-good {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .grade-average {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .notification-item {
        padding: 1rem;
        border-radius: 12px;
        background: white;
        border: 1px solid #e9ecef;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .notification-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #667eea, #764ba2);
    }
    
    .notification-item.unread {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, white 100%);
        border-color: #667eea;
        font-weight: 500;
    }
    
    .notification-item.priority-high::before {
        background: linear-gradient(180deg, #f5576c, #f093fb);
    }
    
    .notification-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .badge-type {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
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
    
    .quick-action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid white;
        color: white;
        background: transparent;
    }
    
    .quick-action-btn:hover {
        background: white;
        color: #667eea;
        transform: translateY(-2px);
    }
    
    .reward-item {
        padding: 1rem;
        background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
        border-left: 4px solid #11998e;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }
    
    .violation-item {
        padding: 1rem;
        background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
        border-left: 4px solid #f5576c;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }
</style>

<div class="parent-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-hand-wave me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($fullName); ?>!
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-child me-2"></i>Con em: <strong><?php echo htmlspecialchars($childInfo['name']); ?></strong>
                    <span class="mx-2">|</span>
                    <i class="fa-solid fa-id-card me-2"></i>Mã HS: <strong><?php echo htmlspecialchars($childInfo['student_id']); ?></strong>
                    <span class="mx-2">|</span>
                    <i class="fa-solid fa-users me-2"></i>Lớp: <strong><?php echo htmlspecialchars($childInfo['class']); ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/modules/parents/child-profile.php" class="quick-action-btn btn">
                    <i class="fa-solid fa-user me-2"></i>Hồ sơ con em
                </a>
            </div>
        </div>
    </div>

    <!-- GVCN Info Card -->
    <div class="child-info-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2">
                    <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                    Giáo viên chủ nhiệm: <strong><?php echo htmlspecialchars($childInfo['homeroom_teacher']); ?></strong>
                </h5>
                <p class="mb-0 text-muted">
                    <i class="fa-solid fa-phone me-2"></i>(028) 3456-7890
                    <span class="mx-2">|</span>
                    <i class="fa-solid fa-envelope me-2"></i>gvcn.12a1@thpt.edu.vn
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/modules/parents/contact-teacher.php" class="btn btn-primary">
                    <i class="fa-solid fa-comments me-2"></i>Liên hệ GVCN
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['attendance_rate']; ?>%</h3>
                        <p><i class="fa-solid fa-calendar-check me-2"></i>Chuyên cần</p>
                    </div>
                    <i class="fa-solid fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['gpa_semester']; ?></h3>
                        <p><i class="fa-solid fa-star me-2"></i>Điểm TB HK</p>
                    </div>
                    <i class="fa-solid fa-trophy fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['rewards']; ?></h3>
                        <p><i class="fa-solid fa-award me-2"></i>Khen thưởng</p>
                    </div>
                    <i class="fa-solid fa-medal fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card <?php echo $stats['violations'] > 0 ? 'danger' : 'success'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['violations']; ?></h3>
                        <p><i class="fa-solid fa-triangle-exclamation me-2"></i>Vi phạm</p>
                    </div>
                    <i class="fa-solid fa-shield-halved fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Kết quả học tập -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h5 class="card-title fw-bold">Kết quả học tập</h5>
                    <p class="text-muted small">Điểm số, xếp loại học lực chi tiết</p>
                    <a href="/modules/parents/grades.php" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-eye me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Đơn xin nghỉ -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <h5 class="card-title fw-bold">Đơn xin nghỉ</h5>
                    <p class="text-muted small">Gửi đơn, upload minh chứng</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/parents/leave-requests/create.php" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-plus me-1"></i>Tạo đơn mới
                        </a>
                        <a href="/modules/parents/leave-requests/list.php" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Lịch sử đơn
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hạnh kiểm & Vi phạm -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <h5 class="card-title fw-bold">Hạnh kiểm</h5>
                    <p class="text-muted small">Vi phạm, khen thưởng</p>
                    <a href="/modules/parents/conduct.php" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-clipboard-check me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Thông báo -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thông báo</h5>
                    <p class="text-muted small">Tin từ GVCN, BGH</p>
                    <a href="/modules/parents/notifications.php" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-envelope me-2"></i>Xem tất cả
                        <?php if ($stats['unread_notifications'] > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Grades -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-star text-warning me-2"></i>
                        Điểm số mới nhất
                    </h5>
                    <?php foreach ($recentGrades as $grade): ?>
                    <div class="grade-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($grade['subject']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-calendar me-1"></i><?php echo $grade['date']; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="grade-badge <?php 
                                    echo $grade['score'] >= 8 ? 'grade-excellent' : 
                                        ($grade['score'] >= 6.5 ? 'grade-good' : 'grade-average'); 
                                ?>">
                                    <?php echo $grade['score']; ?>
                                </div>
                                <div class="small text-muted mt-1"><?php echo $grade['type']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/parents/grades.php" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-chart-line me-2"></i>Xem tất cả điểm
                    </a>
                </div>
            </div>
        </div>

        <!-- Rewards & Violations -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-award text-success me-2"></i>
                        Khen thưởng & Vi phạm
                    </h5>
                    
                    <?php if (count($rewards) > 0): ?>
                        <h6 class="text-success mb-3">
                            <i class="fa-solid fa-trophy me-2"></i>Khen thưởng (<?php echo count($rewards); ?>)
                        </h6>
                        <?php foreach ($rewards as $reward): ?>
                        <div class="reward-item">
                            <div class="fw-bold text-success"><?php echo htmlspecialchars($reward['title']); ?></div>
                            <div class="small text-muted">
                                <i class="fa-solid fa-calendar me-1"></i><?php echo $reward['date']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (count($violations) > 0): ?>
                        <h6 class="text-danger mb-3 mt-3">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Vi phạm (<?php echo count($violations); ?>)
                        </h6>
                        <?php foreach ($violations as $violation): ?>
                        <div class="violation-item">
                            <div class="fw-bold text-danger"><?php echo htmlspecialchars($violation['title']); ?></div>
                            <div class="small text-muted">
                                <i class="fa-solid fa-calendar me-1"></i><?php echo $violation['date']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-success mt-3">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            Con em không có vi phạm nào!
                        </div>
                    <?php endif; ?>
                    
                    <a href="/modules/parents/conduct.php" class="btn btn-outline-success w-100 mt-3">
                        <i class="fa-solid fa-clipboard-list me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-bell text-info me-2"></i>
                        Thông báo quan trọng
                        <?php if ($stats['unread_notifications'] > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </h5>
                    <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['unread'] ? 'unread' : ''; ?> priority-<?php echo $notif['priority']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1">
                                    <?php if ($notif['priority'] === 'high'): ?>
                                    <i class="fa-solid fa-circle-exclamation text-danger me-1"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </div>
                                <div class="small text-muted">
                                    <span class="badge-type bg-primary text-white me-2">
                                        <?php echo htmlspecialchars($notif['type']); ?>
                                    </span>
                                    <i class="fa-solid fa-clock me-1"></i><?php echo $notif['date']; ?>
                                </div>
                            </div>
                            <?php if ($notif['unread']): ?>
                            <span class="badge bg-danger rounded-circle" style="width: 10px; height: 10px; padding: 0;"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/parents/notifications.php" class="btn btn-outline-info w-100 mt-3">
                        <i class="fa-solid fa-envelope-open me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-calendar-days text-primary me-2"></i>Thời khóa biểu con
                    </h6>
                    <p class="text-muted small mb-3">Xem lịch học trong tuần của con em</p>
                    <a href="?action=xem_tkb" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-eye me-2"></i>Xem TKB
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-messages text-success me-2"></i>Tin nhắn với GVCN
                    </h6>
                    <p class="text-muted small mb-3">Trao đổi trực tiếp với giáo viên chủ nhiệm</p>
                    <a href="/modules/parents/messages.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-envelope me-2"></i>Tin nhắn
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-money-bill text-warning me-2"></i>Học phí
                    </h6>
                    <p class="text-muted small mb-3">Tra cứu và thanh toán học phí</p>
                    <a href="/modules/parents/tuition.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-receipt me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
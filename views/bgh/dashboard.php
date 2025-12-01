<?php
// filepath: d:\Hk1_2025\PTUD_Nhom4\Đồ Án Nhóm\Learning_System\views\bgh\dashboard.php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../config/database.php';
require_role(['bgh']);

// Tiêu đề trang và header chung
$pageTitle = 'Ban Giám Hiệu - THPT';
require_once __DIR__ . '/../layouts/header.php';

// Lấy user hiện tại
$user = current_user() ?: [];
$fullName = isset($user['full_name']) ? $user['full_name'] : 'Ban Giám Hiệu';
$position = isset($user['position']) ? $user['position'] : 'Hiệu trưởng';

// Số liệu thống kê từ database
$db = Database::getInstance()->getConnection();
$stats = [
    'total_students' => 0,
    'total_teachers' => 0,
    'total_classes' => 0,
    'pending_approvals' => 0,
    'score_edit_requests' => 0,
    'conduct_approvals' => 0,
    'exam_approvals' => 0,
    'teaching_assignments' => 0,
];

try {
    // Tổng học sinh
    $stmtStudents = $db->prepare("SELECT COUNT(*) as total FROM hocsinh");
    $stmtStudents->execute();
    $stats['total_students'] = $stmtStudents->fetchColumn();

    // Tổng giáo viên
    $stmtTeachers = $db->prepare("SELECT COUNT(*) as total FROM giaovienbomon");
    $stmtTeachers->execute();
    $stats['total_teachers'] = $stmtTeachers->fetchColumn();

    // Tổng lớp học
    $stmtClasses = $db->prepare("SELECT COUNT(*) as total FROM lophoc");
    $stmtClasses->execute();
    $stats['total_classes'] = $stmtClasses->fetchColumn();

    // Tổng yêu cầu chờ duyệt (các bảng cần duyệt)
    // Giả định có các bảng: phieussuadiem, hanhkiem, dethi, phanconggiangday với cột trangThai
    $stmtApprovals = $db->prepare("
        SELECT COUNT(*) as total FROM tohopmon WHERE trangThai = 'PENDING'
    ");
    $stmtApprovals->execute();
    $stats['pending_approvals'] = $stmtApprovals->fetchColumn();

    // Nếu có bảng phieussuadiem, conduct, exam, assignment - cập nhật tương ứng
    // Tạm thời gán 0 hoặc truy vấn từ các bảng nếu chúng tồn tại
    $stats['score_edit_requests'] = 0;
    $stats['conduct_approvals'] = 0;
    $stats['exam_approvals'] = 0;
    $stats['teaching_assignments'] = 0;

} catch (Exception $e) {
    // Nếu có lỗi, giữ nguyên giá trị mặc định
}

// Yêu cầu chờ duyệt
// Khởi tạo mảng chứa các mục chờ duyệt
$pendingApprovals = [];
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

// Thống kê theo khối
$gradeStats = [
    ['grade' => 'Khối 12', 'students' => 850, 'avg_score' => 8.2, 'excellent' => 45, 'good' => 60],
    ['grade' => 'Khối 11', 'students' => 820, 'avg_score' => 7.8, 'excellent' => 38, 'good' => 55],
    ['grade' => 'Khối 10', 'students' => 817, 'avg_score' => 7.5, 'excellent' => 35, 'good' => 52],
];

// Báo cáo nhanh
$quickReports = [
    ['title' => 'Tỷ lệ học sinh giỏi', 'value' => '12.5%', 'change' => '+2.3%', 'trend' => 'up'],
    ['title' => 'Tỷ lệ chuyên cần', 'value' => '96.8%', 'change' => '+1.2%', 'trend' => 'up'],
    ['title' => 'Tỷ lệ hoàn thành chương trình', 'value' => '94.5%', 'change' => '-0.5%', 'trend' => 'down'],
];

// Thông báo quan trọng
$notifications = [
    ['title' => 'Họp BGH về kế hoạch tổ chức thi THPT Quốc gia', 'date' => '2024-03-20 09:00', 'type' => 'meeting'],
    ['title' => 'Báo cáo kết quả học tập học kỳ II cần hoàn thành', 'date' => '2024-03-18', 'type' => 'deadline'],
    ['title' => 'Kiểm tra cơ sở vật chất trường học', 'date' => '2024-03-17', 'type' => 'inspection'],
];
?>

<style>
    .bgh-dashboard {
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
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.5s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card:hover::before {
        top: -20%;
        right: -20%;
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
        position: relative;
        z-index: 1;
    }
    
    .stat-card p {
        margin: 0;
        opacity: 0.9;
        position: relative;
        z-index: 1;
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
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(30, 60, 114, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .welcome-banner h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .position-badge {
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        display: inline-block;
        margin-top: 0.5rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .approval-item {
        padding: 1.25rem;
        border-radius: 12px;
        background: white;
        border: 2px solid #e9ecef;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .approval-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
    }
    
    .approval-item.priority-high::before {
        background: linear-gradient(180deg, #f5576c, #f093fb);
    }
    
    .approval-item.priority-medium::before {
        background: linear-gradient(180deg, #ffc107, #ff9800);
    }
    
    .approval-item:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateX(5px);
    }
    
    .type-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .type-badge.score {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    
    .type-badge.conduct {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
    }
    
    .type-badge.exam {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }
    
    .type-badge.assignment {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
    }
    
    .grade-stats-card {
        padding: 1.25rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-left: 4px solid #667eea;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .grade-stats-card:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        transform: translateX(5px);
    }
    
    .report-card {
        padding: 1.5rem;
        border-radius: 12px;
        background: white;
        border: 2px solid #e9ecef;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .report-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    .report-value {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0.5rem 0;
    }
    
    .report-change {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .report-change.up {
        color: #11998e;
    }
    
    .report-change.down {
        color: #f5576c;
    }
    
    .notification-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .notification-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #667eea, #764ba2);
    }
    
    .timeline-item {
        position: relative;
        padding: 1rem 0;
        padding-left: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -0.25rem;
        top: 1.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
    }
    
    .timeline-item.meeting::before {
        border-color: #667eea;
    }
    
    .timeline-item.deadline::before {
        border-color: #f5576c;
    }
    
    .timeline-item.inspection::before {
        border-color: #ffc107;
    }
</style>

<div class="bgh-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-user-tie me-2"></i>
                    Chào mừng, <?php echo htmlspecialchars($fullName); ?>
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-building me-2"></i>Ban Giám Hiệu - Trường THPT
                </p>
                <span class="position-badge">
                    <i class="fa-solid fa-star me-2"></i><?php echo htmlspecialchars($position); ?>
                </span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-column gap-2">
                    <span class="text-white">
                        <i class="fa-solid fa-calendar me-2"></i>
                        <?php echo date('l, d/m/Y'); ?>
                    </span>
                    <span class="text-white">
                        <i class="fa-solid fa-clock me-2"></i>
                        <span id="currentTime"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['total_students']); ?></h3>
                        <p><i class="fa-solid fa-user-graduate me-2"></i>Tổng học sinh</p>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['total_teachers']; ?></h3>
                        <p><i class="fa-solid fa-chalkboard-user me-2"></i>Giáo viên</p>
                    </div>
                    <i class="fa-solid fa-user-tie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['total_classes']; ?></h3>
                        <p><i class="fa-solid fa-school me-2"></i>Lớp học</p>
                    </div>
                    <i class="fa-solid fa-door-open fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['pending_approvals']; ?></h3>
                        <p><i class="fa-solid fa-clock me-2"></i>Chờ duyệt</p>
                    </div>
                    <i class="fa-solid fa-clipboard-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Duyệt phiếu sửa điểm -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <h5 class="card-title fw-bold">Sửa điểm</h5>
                    <p class="text-muted small">Duyệt đơn xin sửa điểm</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/bgh/score-edits/pending.php" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['score_edit_requests']; ?>)
                        </a>
                        <a href="/modules/bgh/score-edits/list.php" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Lịch sử
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duyệt hạnh kiểm -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <h5 class="card-title fw-bold">Hạnh kiểm</h5>
                    <p class="text-muted small">Duyệt xếp loại hạnh kiểm</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/bgh/conduct/pending.php" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['conduct_approvals']; ?>)
                        </a>
                        <a href="/modules/bgh/conduct/list.php" class="btn btn-outline-success btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Đã duyệt
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duyệt đề thi -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h5 class="card-title fw-bold">Đề thi</h5>
                    <p class="text-muted small">Duyệt đề thi, kiểm tra</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/bgh/exams/pending.php" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['exam_approvals']; ?>)
                        </a>
                        <a href="/modules/bgh/exams/list.php" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Đã duyệt
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phân công giảng dạy -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công</h5>
                    <p class="text-muted small">Duyệt phân công giảng dạy</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/bgh/assignments/pending.php" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['teaching_assignments']; ?>)
                        </a>
                        <a href="/modules/bgh/assignments/manage.php" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-cog me-1"></i>Quản lý
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Approvals -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-hourglass-half text-warning me-2"></i>
                        Yêu cầu chờ duyệt (<?php echo count($pendingApprovals); ?>)
                    </h5>
                    <?php foreach ($pendingApprovals as $approval): ?>
                    <div class="approval-item priority-<?php echo $approval['priority']; ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1"><?php echo htmlspecialchars($approval['title']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($approval['submitter']); ?>
                                    <span class="mx-2">|</span>
                                    <i class="fa-solid fa-calendar me-1"></i><?php echo $approval['date']; ?>
                                </div>
                            </div>
                            <span class="type-badge <?php echo $approval['type']; ?>">
                                <?php 
                                    $typeNames = [
                                        'score_edit' => 'Sửa điểm',
                                        'conduct' => 'Hạnh kiểm',
                                        'exam' => 'Đề thi',
                                        'assignment' => 'Phân công',
                                        'tohopmon' => 'Tổ hợp môn'
                                    ];
                                    echo $typeNames[$approval['type']];
                                ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <?php if (isset($approval['type']) && $approval['type'] === 'tohopmon' && !empty($approval['maToHop'])): ?>
                                <a href="/modules/chonToHopMon/quanLyChonDetail.php?maToHop=<?php echo urlencode($approval['maToHop']); ?>" class="btn btn-sm btn-primary flex-1">
                                    <i class="fa-solid fa-eye me-1"></i>Chi Tiết
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success flex-1">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                                <button class="btn btn-sm btn-danger flex-1">
                                    <i class="fa-solid fa-times me-1"></i>Từ chối
                                </button>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/bgh/approvals/allPending.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics by Grade -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-chart-bar text-primary me-2"></i>
                        Thống kê theo khối
                    </h5>
                    <?php foreach ($gradeStats as $grade): ?>
                    <div class="grade-stats-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($grade['grade']); ?></h6>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-users me-1"></i><?php echo number_format($grade['students']); ?> học sinh
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success mb-1">ĐTB: <?php echo $grade['avg_score']; ?></div>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <div class="small">
                                    <i class="fa-solid fa-star text-warning me-1"></i>
                                    Giỏi: <strong><?php echo $grade['excellent']; ?>%</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small">
                                    <i class="fa-solid fa-medal text-info me-1"></i>
                                    Khá: <strong><?php echo $grade['good']; ?>%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/bgh/reports/by-grade.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-chart-line me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports & Notifications -->
    <div class="row g-4 mt-2">
        <!-- Quick Reports -->
        <div class="col-lg-8">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-chart-pie text-success me-2"></i>
                        Báo cáo nhanh
                    </h5>
                    <div class="row g-3">
                        <?php foreach ($quickReports as $report): ?>
                        <div class="col-md-4">
                            <div class="report-card">
                                <div class="small text-muted"><?php echo htmlspecialchars($report['title']); ?></div>
                                <div class="report-value"><?php echo htmlspecialchars($report['value']); ?></div>
                                <div class="report-change <?php echo $report['trend']; ?>">
                                    <i class="fa-solid fa-arrow-<?php echo $report['trend'] === 'up' ? 'up' : 'down'; ?> me-1"></i>
                                    <?php echo htmlspecialchars($report['change']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-md-6">
                            <a href="/modules/bgh/reports/academic.php" class="btn btn-outline-primary w-100">
                                <i class="fa-solid fa-file-chart me-2"></i>Báo cáo học vụ
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="/modules/bgh/reports/export.php" class="btn btn-outline-success w-100">
                                <i class="fa-solid fa-file-export me-2"></i>Xuất báo cáo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Timeline -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-bell text-info me-2"></i>
                        Lịch trình quan trọng
                    </h5>
                    <div class="notification-timeline">
                        <?php foreach ($notifications as $notif): ?>
                        <div class="timeline-item <?php echo $notif['type']; ?>">
                            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="small text-muted">
                                <i class="fa-solid fa-clock me-1"></i><?php echo $notif['date']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/modules/bgh/calendar.php" class="btn btn-outline-info w-100 mt-3">
                        <i class="fa-solid fa-calendar me-2"></i>Xem lịch đầy đủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Management Tools -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i>Quản lý nhân sự
                    </h6>
                    <a href="/modules/bgh/staff/index.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-user-tie me-2"></i>Xem danh sách
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-clipboard-list text-success me-2"></i>Kế hoạch giảng dạy
                    </h6>
                    <a href="/modules/bgh/curriculum/index.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-book me-2"></i>Xem kế hoạch
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-building text-warning me-2"></i>Cơ sở vật chất
                    </h6>
                    <a href="/modules/bgh/facilities/index.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-warehouse me-2"></i>Quản lý CSVC
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-file-contract text-danger me-2"></i>Văn bản - Quyết định
                    </h6>
                    <a href="/modules/bgh/documents/index.php" class="btn btn-outline-danger w-100">
                        <i class="fa-solid fa-folder-open me-2"></i>Xem văn bản
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-layer-group text-primary me-2"></i>Chọn Tổ Hợp Môn
                    </h6>
                    <a href="/modules/chonToHopMon/quanLyChonList.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-list me-2"></i>Quản lý Tổ Hợp Môn
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN');
        document.getElementById('currentTime').textContent = timeString;
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
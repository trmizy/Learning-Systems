<?php
// filepath: d:\Hk1_2025\PTUD_Nhom4\Đồ Án Nhóm\Learning_System\views\gvbm\dashboard.php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['gvbm', 'gvcn', 'ttbm']);

// Tiêu đề trang và header chung
$pageTitle = 'Trang giáo viên - THPT';
require_once __DIR__ . '/../layouts/header.php';

// Lấy user hiện tại
$user        = current_user() ?: [];
$fullName    = $user['full_name'] ?? 'Giáo viên';
$currentRole = $user['role']      ?? 'gvbm';
$username    = $user['username']  ?? null;   // trong debug bạn có key này

// Kết nối DB
require_once __DIR__ . '/../../config/database.php';
$db   = Database::getInstance();
$conn = $db->getConnection();

// ===== 1. LẤY THÔNG TIN GIÁO VIÊN TỪ USERNAME =====
$teacherId = null;
$teacherInfo = [
    'subject'        => 'Chưa cập nhật',
    'department'     => 'Tổ bộ môn',
    'homeroom_class' => $currentRole === 'gvcn' ? '12A1' : null,
];

if (!empty($username)) {
    try {
        $stmt = $conn->prepare("
            SELECT gv.maGV, gv.hoTen, gv.monHocPhuTrach
            FROM taikhoan tk
            INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
            WHERE tk.tenDangNhap = :username
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $gvRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($gvRow) {
            $teacherId = $gvRow['maGV'];
            if (!empty($gvRow['hoTen'])) {
                $fullName = $gvRow['hoTen'];
            }
            if (!empty($gvRow['monHocPhuTrach'])) {
                $teacherInfo['subject'] = $gvRow['monHocPhuTrach'];
            }
        }
    } catch (Exception $e) {
        // tạm bỏ qua, phía dưới sẽ debug nếu cần
    }
}

// ===== DEBUG NHỎ: nếu không lấy được maGV thì in ra cho chắc =====
if (empty($teacherId)) {
    echo '<pre>';
    echo "DEBUG: Không tìm được giáo viên từ username\n";
    echo "username (session): " . htmlspecialchars((string)$username) . "\n\n";

    echo "Thử query tay trong phpMyAdmin (nên ra 1 dòng):\n";
    echo "SELECT gv.maGV, gv.hoTen, gv.monHocPhuTrach\n";
    echo "FROM taikhoan tk\n";
    echo "JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan\n";
    echo "WHERE tk.tenDangNhap = '" . addslashes($username) . "';\n";
    echo "</pre>";
}

// ===== 2. FIX CỨNG NĂM HỌC / HỌC KỲ THEO FILE SQL =====
$namHoc = '2024-2025';
$hocKy  = 'HK1';

// ===== 3. KHỞI TẠO THỐNG KÊ =====
$teachingClasses = [];
$stats = [
    'total_classes'        => 0,
    'total_students'       => 0,
    'pending_scores'       => 0,
    'pending_requests'     => 0,
    'unread_notifications' => 0,
];

// Chỉ tính thống kê khi đã có maGV
if (!empty($teacherId)) {
    // 3.1 Tổng số lớp giảng dạy
    $sqlClasses = "
        SELECT COUNT(DISTINCT pc.maLop) AS total_classes
        FROM phanconggiangday pc
        WHERE pc.maGV   = :maGV
          AND pc.namHoc = :namHoc
          AND pc.hocKy  = :hocKy
    ";
    $stmt = $conn->prepare($sqlClasses);
    $stmt->execute([
        'maGV'   => $teacherId,
        'namHoc' => $namHoc,
        'hocKy'  => $hocKy,
    ]);
    $stats['total_classes'] = (int) ($stmt->fetchColumn() ?? 0);

    // 3.2 Tổng số học sinh duy nhất trong các lớp dạy
    $sqlStudents = "
        SELECT COUNT(DISTINCT hs.maHS) AS total_students
        FROM hocsinh hs
        INNER JOIN phanconggiangday pc ON hs.maLop = pc.maLop
        WHERE pc.maGV   = :maGV
          AND pc.namHoc = :namHoc
          AND pc.hocKy  = :hocKy
          AND hs.trangThai = 'DANGHOC'
    ";
    $stmt = $conn->prepare($sqlStudents);
    $stmt->execute([
        'maGV'   => $teacherId,
        'namHoc' => $namHoc,
        'hocKy'  => $hocKy,
    ]);
    $stats['total_students'] = (int) ($stmt->fetchColumn() ?? 0);

    // 3.3 Số học sinh chưa có / chưa nhập đủ điểm ở môn mình dạy
    $sqlPending = "
        SELECT COUNT(DISTINCT hs.maHS) AS pending_students
        FROM phanconggiangday pc
        INNER JOIN hocsinh hs ON hs.maLop = pc.maLop
        LEFT JOIN bangdiem bd
            ON bd.maHS     = hs.maHS
           AND bd.maGV     = pc.maGV
           AND bd.maMonHoc = pc.maMonHoc
           AND bd.namHoc   = pc.namHoc
           AND bd.hocKy    = pc.hocKy
        WHERE pc.maGV   = :maGV
          AND pc.namHoc = :namHoc
          AND pc.hocKy  = :hocKy
          AND hs.trangThai = 'DANGHOC'
          AND (
                bd.maBangDiem IS NULL
                OR bd.diemThuongXuyen IS NULL
                OR bd.diemGiuaKy IS NULL
                OR bd.diemCuoiKy IS NULL
          )
    ";
    $stmt = $conn->prepare($sqlPending);
    $stmt->execute([
        'maGV'   => $teacherId,
        'namHoc' => $namHoc,
        'hocKy'  => $hocKy,
    ]);
    $stats['pending_scores'] = (int) ($stmt->fetchColumn() ?? 0);

    // 3.4 Thông báo – CSDL mẫu chưa có bảng thongbao → tạm để 0
    $notifications = [];
} else {
    $notifications = [];
}

// Lịch dạy & đơn xin nghỉ mock
$todaySchedule = [];
$leaveRequests = [];
if ($currentRole === 'gvcn') {
    $leaveRequests = [
        ['student' => 'Nguyễn Văn A', 'reason' => 'Ốm đau',       'date' => '2024-03-16', 'status' => 'pending'],
        ['student' => 'Trần Thị B',   'reason' => 'Việc gia đình', 'date' => '2024-03-17', 'status' => 'pending'],
        ['student' => 'Lê Văn C',     'reason' => 'Khám bệnh',     'date' => '2024-03-15', 'status' => 'pending'],
    ];
    $stats['pending_requests'] = count($leaveRequests);
}
?>

<style>
    .teacher-dashboard {
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
    
    .class-card {
        padding: 1.25rem;
        border-radius: 12px;
        background: white;
        border: 2px solid #e9ecef;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .class-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateX(5px);
    }
    
    .schedule-item {
        padding: 1rem;
        border-left: 4px solid #667eea;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .schedule-item:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        transform: translateX(5px);
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
    
    .notification-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .request-item {
        padding: 1rem;
        background: linear-gradient(135deg, rgba(240, 147, 251, 0.1) 0%, rgba(245, 87, 108, 0.1) 100%);
        border-left: 4px solid #f5576c;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }
    
    .badge-status {
        padding: 0.35rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="teacher-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-hand-wave me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($fullName); ?>!
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-id-card me-2"></i>Mã GV: <?php echo htmlspecialchars($teacherId); ?>
                    <span class="mx-2">|</span>
                    <i class="fa-solid fa-book me-2"></i>Bộ môn: <?php echo htmlspecialchars($teacherInfo['subject']); ?>
                    <?php if ($teacherInfo['homeroom_class']): ?>
                    <span class="mx-2">|</span>
                    <i class="fa-solid fa-users me-2"></i>Lớp CN: <?php echo htmlspecialchars($teacherInfo['homeroom_class']); ?>
                    <?php endif; ?>
                </p>
                <div>
                    <?php
                    $roleName = $currentRole === 'gvcn' ? 'Giáo viên Chủ nhiệm' : 
                               ($currentRole === 'ttbm' ? 'Tổ trưởng Bộ môn' : 'Giáo viên Bộ môn');
                    ?>
                    <span class="role-badge-large">
                        <i class="fa-solid fa-star me-2"></i><?php echo $roleName; ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/modules/teachers/profile.php" class="btn btn-light btn-lg">
                    <i class="fa-solid fa-user me-2"></i>Hồ sơ cá nhân
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['total_classes']; ?></h3>
                        <p><i class="fa-solid fa-chalkboard me-2"></i>Lớp giảng dạy</p>
                    </div>
                    <i class="fa-solid fa-school fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['total_students']; ?></h3>
                        <p><i class="fa-solid fa-users me-2"></i>Học sinh</p>
                    </div>
                    <i class="fa-solid fa-user-graduate fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['pending_scores']; ?></h3>
                        <p><i class="fa-solid fa-clipboard-list me-2"></i>Điểm chưa nhập</p>
                    </div>
                    <i class="fa-solid fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $currentRole === 'gvcn' ? $stats['pending_requests'] : $stats['unread_notifications']; ?></h3>
                        <p><i class="fa-solid <?php echo $currentRole === 'gvcn' ? 'fa-file-lines' : 'fa-bell'; ?> me-2"></i>
                            <?php echo $currentRole === 'gvcn' ? 'Đơn chờ duyệt' : 'Thông báo'; ?>
                        </p>
                    </div>
                    <i class="fa-solid fa-envelope fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Nhập điểm -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h5 class="card-title fw-bold">Nhập điểm</h5>
                    <p class="text-muted small">Nhập, sửa điểm kiểm tra, thi</p>
                    <a href="/public/index.php?action=enterPoints_gvbm" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Nhập điểm
                    </a>
                </div>
            </div>
        </div>

        <!-- Lớp giảng dạy -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h5 class="card-title fw-bold">Lớp giảng dạy</h5>
                    <p class="text-muted small">Danh sách lớp và học sinh</p>
                    <a href="/modules/teachers/classes.php" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Xem danh sách
                    </a>
                </div>
            </div>
        </div>

        <!-- Thời khóa biểu -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thời khóa biểu</h5>
                    <p class="text-muted small">Lịch dạy trong tuần</p>
                    <a href="/modules/teachers/schedule.php" class="btn btn-danger w-100 mt-3">
                        <i class="fa-solid fa-calendar me-2"></i>Xem TKB
                    </a>
                </div>
            </div>
        </div>

        <!-- GVCN/TTBM Features -->
        <?php if ($currentRole === 'gvcn'): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <h5 class="card-title fw-bold">Duyệt đơn</h5>
                    <p class="text-muted small">Đơn xin nghỉ học sinh</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/teachers/requests/pending.php" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['pending_requests']; ?>)
                        </a>
                        <a href="/modules/teachers/requests/list.php" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Tất cả
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($currentRole === 'ttbm'): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-tasks"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công</h5>
                    <p class="text-muted small">Ra đề thi, giảng dạy</p>
                    <a href="/modules/teachers/department/assignments.php" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-user-gear me-2"></i>Quản lý
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thông báo</h5>
                    <p class="text-muted small">Tin từ BGH, tổ bộ môn</p>
                    <a href="/modules/teachers/notifications.php" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-envelope me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Teaching Classes -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-school text-primary me-2"></i>
                        Lớp giảng dạy (<?php echo count($teachingClasses); ?>)
                    </h5>
                    <?php foreach ($teachingClasses as $class): ?>
                    <div class="class-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-primary fs-5">Lớp <?php echo htmlspecialchars($class['class']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-users me-1"></i><?php echo $class['students']; ?> học sinh
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success">ĐTB: <?php echo $class['avg_score']; ?></div>
                            </div>
                        </div>
                        <div class="small text-muted">
                            <i class="fa-solid fa-clock me-1"></i><?php echo htmlspecialchars($class['period']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/teachers/classes.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-clock text-warning me-2"></i>
                        Lịch dạy hôm nay
                    </h5>
                    <?php foreach ($todaySchedule as $lesson): ?>
                    <div class="schedule-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold text-primary">Tiết <?php echo $lesson['period']; ?>: Lớp <?php echo htmlspecialchars($lesson['class']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-door-open me-1"></i>Phòng: <?php echo htmlspecialchars($lesson['room']); ?>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">
                                <?php echo $lesson['time']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/teachers/schedule.php" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-calendar-week me-2"></i>Xem lịch tuần
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications / Leave Requests -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <?php if ($currentRole === 'gvcn' && count($leaveRequests) > 0): ?>
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fa-solid fa-file-lines text-danger me-2"></i>
                            Đơn xin nghỉ chờ duyệt
                            <span class="badge bg-danger rounded-pill"><?php echo count($leaveRequests); ?></span>
                        </h5>
                        <?php foreach ($leaveRequests as $request): ?>
                        <div class="request-item">
                            <div class="fw-bold"><?php echo htmlspecialchars($request['student']); ?></div>
                            <div class="small text-muted mb-2">
                                <i class="fa-solid fa-calendar me-1"></i><?php echo $request['date']; ?>
                            </div>
                            <div class="small mb-2">
                                Lý do: <?php echo htmlspecialchars($request['reason']); ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-success flex-1">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </button>
                                <button class="btn btn-sm btn-danger flex-1">
                                    <i class="fa-solid fa-times me-1"></i>Từ chối
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a href="/modules/teachers/requests/pending.php" class="btn btn-outline-danger w-100 mt-3">
                            <i class="fa-solid fa-list me-2"></i>Xem tất cả
                        </a>
                    <?php else: ?>
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fa-solid fa-bell text-info me-2"></i>
                            Thông báo mới
                            <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $stats['unread_notifications']; ?></span>
                            <?php endif; ?>
                        </h5>
                        <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?php echo $notif['unread'] ? 'unread' : ''; ?>">
                            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="small text-muted">
                                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($notif['type']); ?></span>
                                <i class="fa-solid fa-clock me-1"></i><?php echo $notif['date']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a href="/modules/teachers/notifications.php" class="btn btn-outline-info w-100 mt-3">
                            <i class="fa-solid fa-envelope-open me-2"></i>Xem tất cả
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Features based on Role -->
    <div class="row g-3 mt-4">
        <?php if ($currentRole === 'gvcn'): ?>
        <!-- GVCN specific features -->
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-clipboard-check text-success me-2"></i>Hạnh kiểm & Học lực
                    </h6>
                    <p class="text-muted small mb-3">Xếp loại học sinh lớp chủ nhiệm</p>
                    <a href="/modules/teachers/homeroom/conduct.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-star me-2"></i>Xếp loại
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-award text-warning me-2"></i>Khen thưởng & Vi phạm
                    </h6>
                    <p class="text-muted small mb-3">Ghi nhận khen thưởng, vi phạm</p>
                    <a href="/modules/teachers/homeroom/rewards.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-pen me-2"></i>Ghi nhận
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-file-export text-primary me-2"></i>Báo cáo lớp
                    </h6>
                    <p class="text-muted small mb-3">Xuất báo cáo Excel, PDF</p>
                    <a href="/modules/teachers/homeroom/reports.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-download me-2"></i>Xuất báo cáo
                    </a>
                </div>
            </div>
        </div>

        <?php elseif ($currentRole === 'ttbm'): ?>
        <!-- TTBM specific features -->
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i>Phân công ra đề
                    </h6>
                    <p class="text-muted small mb-3">Phân công giáo viên ra đề thi</p>
                    <a href="/modules/teachers/department/exam-assignments.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-tasks me-2"></i>Phân công
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-chart-line text-success me-2"></i>Báo cáo bộ môn
                    </h6>
                    <p class="text-muted small mb-3">Tổng hợp báo cáo chuyên môn</p>
                    <a href="/modules/teachers/department/reports.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-file-chart me-2"></i>Xem báo cáo
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-users-gear text-warning me-2"></i>Quản lý tổ
                    </h6>
                    <p class="text-muted small mb-3">Giáo viên và phân công giảng dạy</p>
                    <a href="/modules/teachers/department/teachers.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-list me-2"></i>Danh sách
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- GVBM basic features -->
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-chart-simple text-primary me-2"></i>Thống kê điểm
                    </h6>
                    <p class="text-muted small mb-3">Xem thống kê kết quả học tập</p>
                    <a href="/modules/teachers/statistics.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-chart-bar me-2"></i>Xem thống kê
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-book text-success me-2"></i>Tài liệu giảng dạy
                    </h6>
                    <p class="text-muted small mb-3">Quản lý tài liệu, bài giảng</p>
                    <a href="/modules/teachers/materials.php" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-folder me-2"></i>Tài liệu
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-headset text-warning me-2"></i>Hỗ trợ
                    </h6>
                    <p class="text-muted small mb-3">Hướng dẫn sử dụng hệ thống</p>
                    <a href="/modules/teachers/support.php" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-circle-question me-2"></i>Trợ giúp
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
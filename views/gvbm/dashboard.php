<?php
ob_start(); // bắt đầu buffer output
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

    // 3.4 Đếm số đơn chờ duyệt THỰC TẾ (chỉ cho GVCN)
    if ($currentRole === 'gvcn') {
        try {
            // Lấy mã lớp chủ nhiệm
            $stmtLopCN = $conn->prepare("
                SELECT lop 
                FROM giaovienchunhiem 
                WHERE maGV = :maGV
                LIMIT 1
            ");
            $stmtLopCN->execute(['maGV' => $teacherId]);
            $lopChuNhiem = $stmtLopCN->fetch(PDO::FETCH_ASSOC);
            
            if ($lopChuNhiem) {
                $maLopCN = $lopChuNhiem['lop'];
                
                // Đếm đơn chờ duyệt
                $stmtDon = $conn->prepare("
                    SELECT COUNT(*) as total
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    WHERE hs.maLop = :maLop 
                      AND dxp.trangThai = 'Cho duyet'
                ");
                $stmtDon->execute(['maLop' => $maLopCN]);
                $stats['pending_requests'] = (int) ($stmtDon->fetchColumn() ?? 0);
                
                // Lấy danh sách đơn chờ duyệt THỰC TẾ
                $stmtDanhSach = $conn->prepare("
                    SELECT 
                        dxp.maDonXinPhep,
                        hs.hoTen as student,
                        dxp.lyDo as reason,
                        DATE_FORMAT(dxp.ngay, '%Y-%m-%d') as date,
                        'pending' as status
                    FROM donxinphep dxp
                    INNER JOIN hocsinh hs ON dxp.maHS = hs.maHS
                    WHERE hs.maLop = :maLop 
                      AND dxp.trangThai = 'Cho duyet'
                    ORDER BY dxp.ngay DESC
                    LIMIT 5
                ");
                $stmtDanhSach->execute(['maLop' => $maLopCN]);
                $leaveRequests = $stmtDanhSach->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $leaveRequests = [];
            }
        } catch (Exception $e) {
            error_log("Error load leave requests: " . $e->getMessage());
            $leaveRequests = [];
        }
    } else {
        $leaveRequests = [];
    }

    // 3.5 Thông báo – CSDL mẫu chưa có bảng thongbao → tạm để 0
    $notifications = [];
} else {
    $notifications = [];
    $leaveRequests = [];
}
?>

<style>
    .teacher-dashboard {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        height: 100%;
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
    
    .section-header {
        margin: 3rem 0 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid #667eea;
    }
    
    .section-header h4 {
        color: #667eea;
        font-weight: 700;
        margin: 0;
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
    
    @media (max-width: 767.98px) {
        .stat-card h3 {
            font-size: 2rem;
        }
        .welcome-banner h2 {
            font-size: 1.5rem;
        }
    }
</style>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$currentRole = $_SESSION['auth']['role'] ?? '';

if ($currentRole === 'ttbm' && ($_GET['action'] ?? '') === 'assign_exam') {
    require_once __DIR__ . '/../../controllers/ttbm/AssignExamController.php';
    $controller = new AssignExamController();
    $controller->index();
    exit; // Dừng toàn bộ dashboard, chỉ hiển thị trang phân công
}

if ($currentRole === 'ttbm' && ($_GET['action'] ?? '') === 'store_assign_exam') {
    require_once __DIR__ . '/../../controllers/ttbm/AssignExamController.php';
    $controller = new AssignExamController();
    $controller->store();
    exit;
}
?>


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
                <a href="/public/index.php?action=gvbm-profile" class="btn btn-light btn-lg">
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

    <!-- ===== PHẦN 1: CHỨC NĂNG CHUNG (ALL ROLES) ===== -->
    <div class="section-header">
        <h4><i class="fa-solid fa-briefcase me-2"></i>Công việc hàng ngày</h4>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Nhập điểm -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h5 class="card-title fw-bold">Nhập điểm</h5>
                    <p class="text-muted small">Nhập, sửa điểm kiểm tra, thi</p>
                    <a href="/public/index.php?action=enterPoints_gvbm" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-keyboard me-2"></i>Nhập điểm
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Yêu cầu sửa điểm -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-edit"></i>
                    </div>
                    <h5 class="card-title fw-bold">Yêu cầu sửa điểm</h5>
                    <p class="text-muted small">Gửi yêu cầu chỉnh sửa điểm</p>
                    <a href="index.php?action=yeu_cau_sua_diem" class="btn btn-danger w-100 mt-3">
                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi yêu cầu
                    </a>
                </div>
            </div>
        </div>

        <!-- Lớp giảng dạy -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h5 class="card-title fw-bold">Lớp giảng dạy</h5>
                    <p class="text-muted small">Danh sách lớp và học sinh</p>
                    <a href="index.php?action=lop_giang_day" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Xem danh sách
                    </a>
                </div>
            </div>
        </div>

        <!-- Lịch giảng dạy -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h5 class="card-title fw-bold">Lịch giảng dạy</h5>
                    <p class="text-muted small">Xem thời khóa biểu tuần</p>
                    <a href="/public/index.php?action=gvbm-lich-day" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-calendar-check me-2"></i>Xem lịch
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== PHẦN 2: CHỨC NĂNG THEO ROLE ===== -->
    <?php if ($currentRole === 'gvcn'): ?>
    <!-- GVCN Features -->
    <div class="section-header">
        <h4><i class="fa-solid fa-user-tie me-2"></i>Công việc Chủ nhiệm</h4>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Lớp chủ nhiệm -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fa-solid fa-house-user"></i>
                    </div>
                    <h5 class="card-title fw-bold">Lớp chủ nhiệm</h5>
                    <p class="text-muted small">Quản lý lớp phụ trách</p>
                    <a href="index.php?action=xem_lop_cn" class="btn btn-warning w-100 mt-3">
                        <i class="fa-solid fa-door-open me-2"></i>Vào lớp
                    </a>
                </div>
            </div>
        </div>

        <!-- Duyệt đơn -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <h5 class="card-title fw-bold">Duyệt đơn xin nghỉ</h5>
                    <p class="text-muted small">Phê duyệt đơn học sinh</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/public/index.php?action=gvcn-duyet-don-pending" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-clock me-1"></i>Chờ duyệt (<?php echo $stats['pending_requests']; ?>)
                        </a>
                        <a href="/public/index.php?action=gvcn-duyet-don-list" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Tất cả
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Xếp loại -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h5 class="card-title fw-bold">Xếp loại học lực</h5>
                    <p class="text-muted small">Xếp loại học sinh lớp CN</p>
                    <a href="index.php?action=xep_loai" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-star me-2"></i>Xếp loại
                    </a>
                </div>
            </div>
        </div>

        <!-- Phân công của tôi -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công ra đề</h5>
                    <p class="text-muted small">Đề thi được giao</p>
                    <a href="index.php?action=view_assign" class="btn btn-warning w-100 mt-3">
                        <i class="fa-solid fa-eye me-2"></i>Xem ngay
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($currentRole === 'ttbm'): ?>
    <!-- TTBM Features -->
    <div class="section-header">
        <h4><i class="fa-solid fa-users-gear me-2"></i>Công việc Tổ trưởng</h4>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Phân công ra đề -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công ra đề</h5>
                    <p class="text-muted small">Giao đề thi cho giáo viên</p>
                    <a href="index.php?action=assign_exam" class="btn btn-warning w-100 mt-3">
                        <i class="fa-solid fa-tasks me-2"></i>Quản lý
                    </a>
                </div>
            </div>
        </div>

        <!-- Quản lý giáo viên -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fa-solid fa-users-cog"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý tổ</h5>
                    <p class="text-muted small">Giáo viên và phân công</p>
                    <a href="/public/index.php?action=ttbm-quan-ly-to" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Danh sách
                    </a>
                </div>
            </div>
        </div>

        <!-- Báo cáo bộ môn -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h5 class="card-title fw-bold">Báo cáo bộ môn</h5>
                    <p class="text-muted small">Tổng hợp chuyên môn</p>
                    <a href="/public/index.php?action=ttbm-bao-cao" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-file-chart me-2"></i>Xem báo cáo
                    </a>
                </div>
            </div>
        </div>

        <!-- Thông báo -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thông báo</h5>
                    <p class="text-muted small">Gửi thông báo tổ BM</p>
                    <a href="/public/index.php?action=ttbm-thong-bao" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi TB
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- GVBM Features -->
    <div class="section-header">
        <h4><i class="fa-solid fa-chalkboard-user me-2"></i>Tiện ích giảng dạy</h4>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Phân công của tôi -->
        <div class="col-md-6 col-lg-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <h5 class="card-title fw-bold">Phân công ra đề</h5>
                    <p class="text-muted small">Đề thi được giao</p>
                    <a href="index.php?action=view_assign" class="btn btn-warning w-100 mt-3">
                        <i class="fa-solid fa-eye me-2"></i>Xem ngay
                    </a>
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="col-md-6 col-lg-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thống kê điểm</h5>
                    <p class="text-muted small">Xem kết quả học tập</p>
                    <a href="/public/index.php?action=gvbm-thong-ke" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-chart-bar me-2"></i>Xem thống kê
                    </a>
                </div>
            </div>
        </div>

        <!-- Tài liệu -->
        <div class="col-md-6 col-lg-4">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <h5 class="card-title fw-bold">Tài liệu giảng dạy</h5>
                    <p class="text-muted small">Quản lý bài giảng</p>
                    <a href="/public/index.php?action=gvbm-tai-lieu" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-folder me-2"></i>Tài liệu
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== PHẦN 3: THÔNG TIN NHANH ===== -->
    <div class="section-header">
        <h4><i class="fa-solid fa-dashboard me-2"></i>Tổng quan hôm nay</h4>
    </div>

    <div class="row g-4">
        <!-- Today's Schedule -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-clock text-primary me-2"></i>
                        Lịch dạy hôm nay
                    </h5>
                    <?php if (empty($todaySchedule)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-calendar-xmark fa-3x mb-3 d-block"></i>
                            <p>Hôm nay không có lịch dạy</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todaySchedule as $lesson): ?>
                        <div class="schedule-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-primary">
                                        Tiết <?php echo htmlspecialchars($lesson['period']); ?>: 
                                        <?php echo htmlspecialchars($lesson['subject']); ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <?php echo htmlspecialchars($lesson['class'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    <i class="fa-solid fa-door-open me-1"></i>
                                    <?php echo htmlspecialchars($lesson['room'] ?? 'N/A'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="/public/index.php?action=gvbm-lich-day" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-calendar-week me-2"></i>Xem lịch tuần
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications / Leave Requests -->
        <div class="col-lg-6">
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
                                <i class="fa-solid fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($request['date'])); ?>
                            </div>
                            <div class="small mb-2">
                                Lý do: <?php echo htmlspecialchars($request['reason']); ?>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="/public/index.php?action=gvcn-duyet-don-approve" method="POST" style="flex: 1;" onsubmit="return confirm('Xác nhận phê duyệt đơn này?')">
                                    <input type="hidden" name="maDonXinPhep" value="<?php echo htmlspecialchars($request['maDonXinPhep'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fa-solid fa-check me-1"></i>Duyệt
                                    </button>
                                </form>
                                
                                <button type="button" 
                                        class="btn btn-sm btn-danger"
                                        style="flex: 1;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModalQuick<?php echo htmlspecialchars($request['maDonXinPhep'] ?? ''); ?>">
                                    <i class="fa-solid fa-times me-1"></i>Từ chối
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal từ chối -->
                        <div class="modal fade" id="rejectModalQuick<?php echo htmlspecialchars($request['maDonXinPhep'] ?? ''); ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form action="/public/index.php?action=gvcn-duyet-don-reject" method="POST">
                                        <input type="hidden" name="maDonXinPhep" value="<?php echo htmlspecialchars($request['maDonXinPhep'] ?? ''); ?>">
                                        <div class="modal-header bg-danger text-white">
                                            <h6 class="modal-title">Từ chối đơn</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label small">Lý do từ chối <span class="text-danger">*</span></label>
                                            <textarea class="form-control form-control-sm" 
                                                      name="lyDoTuChoi" 
                                                      rows="3" 
                                                      placeholder="Nhập lý do..."
                                                      required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-ban me-1"></i>Từ chối
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a href="/public/index.php?action=gvcn-duyet-don-pending" class="btn btn-outline-danger w-100 mt-3">
                            <i class="fa-solid fa-list me-2"></i>Xem tất cả
                        </a>
                    <?php elseif ($currentRole === 'gvcn'): ?>
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fa-solid fa-file-lines text-success me-2"></i>
                            Đơn xin nghỉ
                        </h5>
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-check-double fa-3x mb-3 d-block"></i>
                            <p>Không có đơn chờ duyệt</p>
                        </div>
                        <a href="/public/index.php?action=gvcn-duyet-don-list" class="btn btn-outline-primary w-100 mt-3">
                            <i class="fa-solid fa-list me-2"></i>Xem tất cả đơn
                        </a>
                    <?php else: ?>
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fa-solid fa-bell text-info me-2"></i>
                            Thông báo
                        </h5>
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                            <p>Chưa có thông báo mới</p>
                        </div>
                        <a href="/public/index.php?action=gvbm-thong-bao" class="btn btn-outline-info w-100 mt-3">
                            <i class="fa-solid fa-envelope me-2"></i>Xem tất cả
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
ob_end_flush();
?>

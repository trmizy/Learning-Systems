<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../middlewares/AuthGuard.php';

// Bật hiển thị lỗi để gỡ lỗi (bạn có thể xóa 3 dòng này khi deploy)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Get and clear flash messages
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
$flash_info = $_SESSION['flash_info'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);

// Role to view path mapping with absolute paths
const ROLE_VIEWS = [
    'nhanvienso' => __DIR__ . '/../views/nhanvienso/dashboard.php',
    'bgh' => __DIR__ . '/../views/bgh/dashboard.php', 
    'admin' => __DIR__ . '/../views/admin/dashboard.php',
    'gvbm' => __DIR__ . '/../views/gvbm/dashboard.php',
    'gvcn' => __DIR__ . '/../views/gvbm/dashboard.php', // GVCN dùng chung dashboard GVBM
    'ttbm' => __DIR__ . '/../views/gvbm/dashboard.php', // TTBM dùng chung dashboard GVBM
    'hs' => __DIR__ . '/../views/hs/dashboard.php',
    'ph' => __DIR__ . '/../views/ph/dashboard.php'
];

// Handle controller routing
if (isset($_GET['controller'])) {
    $controller = $_GET['controller'];
    
    // Route to specific controllers
    switch ($controller) {
        case 'chitieu':
            // Legacy route name 'chitieu' -> use new targets controller
            require_once __DIR__ . '/../controllers/nhanvienso/targetsController.php';
            exit;
        
        case 'targets':
            require_once __DIR__ . '/../controllers/nhanvienso/targetsController.php';
            exit;
            
        // Add more controllers here as needed
    }
}

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'statistics_bgh':
            require_once __DIR__ . '/../controllers/bgh/statisticsController.php';
            exit;
            
        case 'enterPoints_gvbm':
            require_once __DIR__ . '/../controllers/gvbm/enterPointsController.php';
            exit;
            
        case 'schoolAccount_nhanvienso':
            require_once __DIR__ . '/../controllers/nhanvienso/schoolAccountController.php';
            exit;
            
        case 'targets_nhanvienso':
            require_once __DIR__ . '/../controllers/nhanvienso/targetsController.php';
            exit;
            
        case 'wishRegistration_ph':
            require_once __DIR__ . '/../controllers/ph/wishRegistrationController.php';
            exit;
        
    case 'logout':
    // 1. Xóa tất cả các biến trong session
    session_unset();

    // 2. Hủy hoàn toàn session
    session_destroy();

    // 3. Chuyển hướng về trang chủ
    header('Location: /public/index.php');
    exit;
            
        case 'switch':
            if (isset($_GET['role']) && 
                isset($_SESSION['auth']['roles']) &&
                in_array($_GET['role'], $_SESSION['auth']['roles'])) {
                $_SESSION['auth']['role'] = $_GET['role'];
                session_regenerate_id(true);
            }
            header('Location: index.php'); // Về trang chủ (tải lại dashboard)
            exit;

        // ========================================================
        // === 🚀 ĐÂY LÀ CODE MỚI ĐỂ XỬ LÝ USECASE 1 ===
        // ========================================================
        case 'xem_lop_cn':
            // 1. Gọi file Controller
            require_once __DIR__ . '/../controllers/gvbm/lop.controller.php';
            // 2. Khởi tạo Controller
            $controller = new LopController();
            // 3. Gọi hàm (action) tương ứng
            $controller->xemLopChuNhiem();
            exit; // Dừng lại sau khi Controller đã xử lý xong
        // ========================================================
        case 'xem_chi_tiet_hs':
            require_once __DIR__ . '/../controllers/gvbm/lop.controller.php';
            $controller = new LopController();
            $controller->xemChiTietHocSinh();
            exit;
        // ========================================================
        // --- Usecase 2: Xếp loại học lực, hạnh kiểm (GVCN) ---
        case 'xep_loai':
            require_once __DIR__ . '/../controllers/gvbm/XepLoaiController.php';
            $controller = new XepLoaiController();
            $controller->showXepLoaiPage();
            exit;

        case 'luu_xep_loai':
            require_once __DIR__ . '/../controllers/gvbm/XepLoaiController.php';
            $controller = new XepLoaiController();
            $controller->saveXepLoai();
            exit;

        // --- Usecase 3: Yêu cầu sửa điểm (GVBM) ---
        // --- Usecase 4: Yêu cầu sửa điểm (GVBM) ---
        case 'yeu_cau_sua_diem':
            require_once __DIR__ . '/../controllers/gvbm/DiemController.php';
            $controller = new DiemController();
            $controller->showYeuCauForm();
            exit;

        case 'gui_yeu_cau_sua_diem':
            require_once __DIR__ . '/../controllers/gvbm/DiemController.php';
            $controller = new DiemController();
            $controller->submitYeuCau();
            exit;
        // --- Usecase: Xem báo cáo thống kê (Nhân viên Sở) ---
        case 'xem_bao_cao':
            require_once __DIR__ . '/../controllers/nhanvienso/BaoCaoController.php';
            $controller = new BaoCaoController();
            $controller->showBaoCaoPage();
            exit;

    // === HỌC SINH ROUTES ===
    case 'hs-dashboard':
        require_role(['hs']);
        require_once __DIR__ . '/../views/hs/dashboard.php';
        break;

    case 'hs-xem-diem':
        require_role(['hs']);
        require_once __DIR__ . '/../controllers/hs/DiemController.php';
        $controller = new DiemController();
        $controller->xemDiem();
        break;
    
    case 'hs-xem-tkb':
        require_role(['hs']);
        require_once __DIR__ . '/../controllers/hs/ThoiKhoaBieuController.php';
        $controller = new ThoiKhoaBieuController();
        $controller->indexHocSinh();
        break;

    // === PHỤ HUYNH ROUTES ===
    case 'ph-dashboard':
        require_role(['ph']);
        require_once __DIR__ . '/../views/ph/dashboard.php';
        break;

    case 'ph-xem-diem':
        require_role(['ph']);
        require_once __DIR__ . '/../controllers/ph/DiemController.php';
        $controller = new DiemController();
        $controller->xemDiemPhuHuynh();
        break;
    
    case 'ph-xem-tkb':
        require_role(['ph']);
        require_once __DIR__ . '/../controllers/ph/ThoiKhoaBieuController.php';
        $controller = new ThoiKhoaBieuController();
        $controller->indexPhuHuynh();
        break;

    // ⚠️ THÊM ROUTE MỚI
    case 'ph-family-profile':
        require_once __DIR__ . '/../controllers/ph/FamilyController.php';
        break;

    // ⚠️ THÊM ROUTE MỚI - Quản lý hồ sơ giáo viên
    case 'bgh-quan-ly-giao-vien':
        require_once __DIR__ . '/../controllers/bgh/quanLyHoSoGiaoVien/QuanLyGiaoVienController.php';
        break;

    // ⚠️ THÊM ROUTE MỚI - Lớp giảng dạy (GVBM, GVCN, TTBM)
    case 'lop_giang_day':
        require_once __DIR__ . '/../controllers/gvbm/LopGiangDayController.php';
        $controller = new LopGiangDayController();
        $controller->index();
        break;

    case 'chi_tiet_lop':
        require_once __DIR__ . '/../controllers/gvbm/LopGiangDayController.php';
        $controller = new LopGiangDayController();
        $controller->chiTietLop();
        break;

    case 'chi_tiet_hoc_sinh':
        require_once __DIR__ . '/../controllers/gvbm/LopGiangDayController.php';
        $controller = new LopGiangDayController();
        $controller->chiTietHocSinh();
        break;

    // Thêm route mới cho phân công giảng dạy
    case 'assign_exam':
        require_once __DIR__ . '/../controllers/ttbm/AssignExamController.php';
        $controller = new AssignExamController();
        $controller->index();
        break;

    case 'store_assign_exam':
        require_once __DIR__ . '/../controllers/ttbm/AssignExamController.php';
        $controller = new AssignExamController();
        $controller->store();
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        break;
    }
}

// Handle page routing
if (isset($_GET['page']) && isset($_SESSION['auth'])) {
    $page = $_GET['page'];
    
    // BGH pages
    if ($_SESSION['auth']['role'] === 'bgh') {
        switch ($page) {
            case 'bgh-phan-cong':
                require_once __DIR__ . '/../controllers/bgh/phanCongGiangDayVaPhongHoc/QuanLyPhanCongController.php';
                exit;
                
            case 'bgh-phan-cong-mon-hoc':
                require_once __DIR__ . '/../controllers/bgh/phanCongGiangDayVaPhongHoc/PhanCongMonHocController.php';
                exit;
                
            case 'bgh-quan-ly-giao-vien':
                require_once __DIR__ . '/../controllers/bgh/quanLyHoSoGiaoVien/QuanLyGiaoVienController.php';
                exit;
        }
    }
    
    // HS pages
    if ($_SESSION['auth']['role'] === 'hs') {
        switch ($page) {
            case 'hs-xem-diem':
                require_once __DIR__ . '/../controllers/hs/XemDiemController.php';
                exit;
        }
    }
    
    // PH pages
    if ($_SESSION['auth']['role'] === 'ph') {
        switch ($page) {
            case 'ph-xem-diem':
                require_once __DIR__ . '/../controllers/ph/XemDiemController.php';
                exit;
        }
    }
}

// Display flash messages if they exist
if ($flash_success || $flash_error || $flash_info): ?>
    <div style="position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px;">
        <?php if ($flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-lg">
                <strong>Thành công!</strong> <?php echo htmlspecialchars($flash_success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-lg">
                <strong>Lỗi!</strong> <?php echo htmlspecialchars($flash_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_info): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-lg">
                <strong>Thông báo:</strong> <?php echo htmlspecialchars($flash_info); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
<?php endif;

// Check authentication & redirect (Logic tải Dashboard mặc định)
if (isset($_SESSION['auth']) && isset($_SESSION['auth']['role'])) {
    $role = $_SESSION['auth']['role'];
    if (isset(ROLE_VIEWS[$role])) {
        if (file_exists(ROLE_VIEWS[$role])) {
            require_once ROLE_VIEWS[$role];
            exit;
        }
    }
    http_response_code(403);
    exit('Invalid role configuration or missing dashboard file');
}

// Not logged in - show welcome page
require_once __DIR__ . '/../views/guest/welcome.php';
?>
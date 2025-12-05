<?php
declare(strict_types=1);

session_start();

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
        
        case 'logout':
            session_unset();
            session_destroy();
            header('Location: index.php'); // Chuyển hướng về trang chủ
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
    }
}

// Display flash messages if they exist (Cải tiến giao diện)
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
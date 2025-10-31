<?php
declare(strict_types=1);
session_start();

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
    'gvcn' => __DIR__ . '/../views/gvbm/dashboard.php',
    'ttbm' => __DIR__ . '/../views/gvbm/dashboard.php',
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
        // THAY THẾ BẰNG ĐOẠN NÀY
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
            header('Location: /public/index.php');
            exit;
    }
}

// Display flash messages if they exist
if ($flash_success || $flash_error || $flash_info): ?>
    <div class="container mt-3">
        <?php if ($flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($flash_success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($flash_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_info): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?php echo htmlspecialchars($flash_info); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
<?php endif;

// Check authentication & redirect
if (isset($_SESSION['auth']) && isset($_SESSION['auth']['role'])) {
    $role = $_SESSION['auth']['role'];
    if (isset(ROLE_VIEWS[$role])) {
        // Instead of redirecting, include the dashboard directly
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

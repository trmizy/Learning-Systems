<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../controllers/admin/quanLyTaiKhoanController.php';

// Kiểm tra quyền admin
require_role(['admin']);

// Khởi tạo database connection
$db = Database::getInstance()->getConnection();

// Khởi tạo controller
$controller = new QuanLyTaiKhoanController($db);

// Lấy action từ query string hoặc POST
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Xử lý các hành động
switch ($action) {
    case 'list':
        $search = $_GET['search'] ?? '';
        $trangThai = $_GET['trangThai'] ?? '';
        $taiKhoanList = $controller->getDanhSachTaiKhoan($search, $trangThai);
        // Debug mode: print raw result if requested
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "DEBUG taiKhoanList:\n" . print_r($taiKhoanList, true);
            exit;
        }

        require_once __DIR__ . '/../../views/admin/quanLyTaiKhoan/list.php';
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        error_log('Nhận POST tạo tài khoản, action=' . $_POST['action']);
                        error_log('POST data: ' . json_encode($_POST));
            $result = $controller->taoTaiKhoan();
                        error_log('Kết quả tạo TK: ' . json_encode($result));
            // If debug query param present, print result instead of redirecting
            if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                header('Content-Type: text/plain; charset=utf-8');
                echo "DEBUG create result:\n" . print_r($result, true);
                // Also print current POST for reference
                echo "\nPOST:\n" . print_r($_POST, true);
                exit;
            }

            $_SESSION['message'] = $result['message'];
            $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
            header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
            exit;
        }
        $action = 'create';
        require_once __DIR__ . '/../../views/admin/quanLyTaiKhoan/form.php';
        break;

    case 'edit':
        if (!isset($_GET['maTaiKhoan'])) {
            $_SESSION['message'] = 'Mã tài khoản không được cung cấp';
            $_SESSION['messageType'] = 'danger';
            header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->capNhatTaiKhoan();
            $_SESSION['message'] = $result['message'];
            $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
            
            if ($result['success']) {
                header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
            } else {
                header('Location: /models/admin/quanLyTaiKhoan.php?action=edit&maTaiKhoan=' . urlencode($_POST['maTaiKhoan']));
            }
            exit;
        }

        $action = 'edit';
        require_once __DIR__ . '/../../views/admin/quanLyTaiKhoan/form.php';
        break;

    case 'permissions':
        if (!isset($_GET['maTaiKhoan'])) {
            $_SESSION['message'] = 'Mã tài khoản không được cung cấp';
            $_SESSION['messageType'] = 'danger';
            header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->ganVaiTro();
            $_SESSION['message'] = $result['message'];
            $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
            header('Location: /models/admin/quanLyTaiKhoan.php?action=permissions&maTaiKhoan=' . urlencode($_POST['maTaiKhoan']));
            exit;
        }

        require_once __DIR__ . '/../../views/admin/quanLyTaiKhoan/permissions.php';
        break;

    case 'updatePermissions':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->ganVaiTro();
            $_SESSION['message'] = $result['message'];
            $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
            header('Location: /models/admin/quanLyTaiKhoan.php?action=permissions&maTaiKhoan=' . urlencode($_POST['maTaiKhoan']));
            exit;
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->xoaTaiKhoan();
            $_SESSION['message'] = $result['message'];
            $_SESSION['messageType'] = $result['success'] ? 'success' : 'danger';
            // If debug flag set, print result + current DB row for the account
            if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                header('Content-Type: text/plain; charset=utf-8');
                echo "DEBUG delete result:\n" . print_r($result, true) . "\n\n";
                $ma = $_POST['maTaiKhoan'] ?? '';
                if (!empty($ma)) {
                    $row = $controller->getChiTietTaiKhoan($ma);
                    echo "DB row after delete attempt:\n" . print_r($row, true);
                }
                exit;
            }

            header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
            exit;
        }
        break;

    default:
        $taiKhoanList = $controller->getDanhSachTaiKhoan();
        require_once __DIR__ . '/../../views/admin/quanLyTaiKhoan/list.php';
}
?>

<?php
session_start();
require_once __DIR__ . '/../../models/admin/taoCacToHopMonModel.php';

$model = new TaoCacToHopMonModel();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    // Xử lý POST tạo mới
    // Trang thái sẽ do BGH duyệt; admin không nhập trạng thái khi tạo.
    // Mặc định trạng thái sẽ được thiết lập là 'PENDING' trong model khi tạo.
    $data = [
        'maToHop' => trim($_POST['maToHop'] ?? ''),
        'tenToHop' => trim($_POST['tenToHop'] ?? ''),
        'danhSachMon' => isset($_POST['danhSachMon']) ? implode(',', $_POST['danhSachMon']) : '',
        'soLuongLop' => intval($_POST['soLuongLop'] ?? 0)
    ];

    // Validate
    $errors = $model->validateData($data);
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        header('Location: /controllers/admin/taoCacToHopMon_controller.php?action=create');
        exit;
    }

    // Kiểm tra trùng
    if ($model->checkExist($data['maToHop'])) {
        $_SESSION['error_messages'] = ["Mã tổ hợp đã tồn tại"];
        header('Location: /controllers/admin/taoCacToHopMon_controller.php?action=create');
        exit;
    }

    // Tạo
    try {
        $model->create($data);
        $_SESSION['success_message'] = 'Tạo tổ hợp môn thành công';
        header('Location: /controllers/admin/taoCacToHopMon_controller.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_messages'] = ["Có lỗi xảy ra: " . $e->getMessage()];
        header('Location: /controllers/admin/taoCacToHopMon_controller.php?action=create');
        exit;
    }
}

// GET handlers
if ($action === 'create') {
    // Show create form
    $danhSachMonHoc = $model->getDanhSachMonHoc();
    require_once __DIR__ . '/../../views/admin/toHopMon/taoCacToHopMon.php';
    exit;
} else {
    // Default: list
    $tohopList = [];
    try {
        $tohopList = $model->getDanhSachToHopMon();
    } catch (Exception $e) {
        $_SESSION['error_messages'] = ["Không thể tải danh sách: " . $e->getMessage()];
    }
    require_once __DIR__ . '/../../views/admin/toHopMon/list.php';
    exit;
}

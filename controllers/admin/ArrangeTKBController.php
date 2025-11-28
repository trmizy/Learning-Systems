<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../models/admin/ArrangeTKBModel.php';

$model = new ArrangeTKBModel();

// ------- Check quyền -------
if (!isset($_SESSION['auth']) || !in_array($_SESSION['auth']['role'], ['admin', 'nhanvienso'], true)) {
    $_SESSION['flash_error'] = 'Bạn không có quyền truy cập chức năng này.';
    header('Location: /public/index.php');
    exit;
}

// ------- Flash message -------
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ------- Input filter -------
$namHoc = $_GET['namHoc'] ?? ($_POST['namHoc'] ?? '');
$hocKy  = $_GET['hocKy']  ?? ($_POST['hocKy']  ?? '');
$maLop  = $_GET['maLop']  ?? ($_POST['maLop']  ?? '');

// ------- Data cho form -------
$dsNamHoc = $model->getDanhSachNamHoc();
$dsHocKy  = $model->getDanhSachHocKy();

$dsLop    = [];
$dsMonHoc = [];
$dsPhong  = $model->getDanhSachPhongHoc();

$previewAll = [];
$tkbLopDangXem = [];
$conflicts = [];

// Nếu đã chọn năm học & học kỳ thì load lớp + môn
if ($namHoc !== '' && $hocKy !== '') {
    $dsLop    = $model->getDanhSachLopTheoNamHoc($namHoc);
    $dsMonHoc = $model->getDanhSachMonHocTheoNamHoc($namHoc, $hocKy);
    $previewAll = $_SESSION['tkb_preview'][$namHoc][$hocKy] ?? [];
}

// ------- Handle actions -------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* 1. Tự động sắp xếp tất cả lớp (PREVIEW) */
    if ($action === 'auto_all') {
        if ($namHoc === '' || $hocKy === '') {
            $flash_error = 'Vui lòng chọn Năm học và Học kỳ trước khi tự động sắp xếp.';
        } else {
            $previewAll = $model->generateAutoTKBForAll($namHoc, $hocKy);
            $_SESSION['tkb_preview'][$namHoc][$hocKy] = $previewAll;
            $flash_success = 'Đã tạo thời khóa biểu đề xuất cho tất cả các lớp. Chọn lớp để xem chi tiết và xác nhận lưu.';
        }

    /* 2. Lưu TKB cho một lớp từ bản preview */
    } elseif ($action === 'save_class') {
        if ($namHoc === '' || $hocKy === '' || $maLop === '') {
            $flash_error = 'Thiếu thông tin Năm học / Học kỳ / Lớp cần lưu.';
        } else {
            $allPreview = $_SESSION['tkb_preview'][$namHoc][$hocKy] ?? [];
            if (empty($allPreview[$maLop])) {
                $flash_error = 'Không tìm thấy dữ liệu TKB đề xuất cho lớp này. Hãy tự động sắp xếp lại.';
            } else {
                $plan = $allPreview[$maLop];
                $conflicts = $model->findConflicts($plan, $namHoc, $hocKy, $maLop);

                // Nếu có trùng mà chưa tick "force_save" -> chỉ hiển thị, chưa lưu
                if (!empty($conflicts) && empty($_POST['force_save'])) {
                    $flash_error = 'Phát hiện trùng lịch. Kiểm tra danh sách bên dưới. Nếu vẫn muốn lưu hãy đánh dấu "Chấp nhận ghi đè" và bấm lưu lại.';
                    $tkbLopDangXem = $plan;
                } else {
                    $model->saveTKBForClass($maLop, $namHoc, $hocKy, $plan);
                    $flash_success = "Đã lưu thời khóa biểu cho lớp $maLop.";
                    // Sau khi lưu, nên lấy lại từ DB để xem
                    $tkbLopDangXem = $model->getTKBFromDb($maLop, $namHoc, $hocKy);
                }
            }
        }

    /* 3. Thêm 1 slot thủ công */
    } elseif ($action === 'manual_add') {
        $data = [
            'namHoc'   => $_POST['namHoc']   ?? '',
            'hocKy'    => $_POST['hocKy']    ?? '',
            'maLop'    => $_POST['maLop']    ?? '',
            'maMonHoc' => $_POST['maMonHoc'] ?? '',
            'tiet'     => (int)($_POST['tiet'] ?? 0),
            'ngayHoc'  => $_POST['ngayHoc']  ?? '',
            'maPhong'  => $_POST['maPhong']  ?? ''
        ];

        if (in_array('', [$data['namHoc'], $data['hocKy'], $data['maLop'], $data['maMonHoc'], $data['ngayHoc']], true) || $data['tiet'] <= 0) {
            $flash_error = 'Vui lòng nhập đầy đủ thông tin khi xếp thủ công.';
        } else {
            if ($model->addManualSlot($data)) {
                $flash_success = 'Đã thêm tiết học thủ công vào thời khóa biểu.';
            } else {
                $flash_error = 'Slot này đã có thời khóa biểu, không thể ghi đè.';
            }
            // Sau khi thêm thủ công, load lại TKB lớp
            $maLop  = $data['maLop'];
            $namHoc = $data['namHoc'];
            $hocKy  = $data['hocKy'];
            $tkbLopDangXem = $model->getTKBFromDb($maLop, $namHoc, $hocKy);
        }
    }
}

// Sau xử lý POST, nếu có maLop đang xem mà chưa có dữ liệu thì lấy từ preview hoặc DB
if ($maLop !== '' && empty($tkbLopDangXem)) {
    if (!empty($previewAll[$maLop])) {
        $tkbLopDangXem = $previewAll[$maLop];
    } elseif ($namHoc !== '' && $hocKy !== '') {
        $tkbLopDangXem = $model->getTKBFromDb($maLop, $namHoc, $hocKy);
    }
}

$BASE = dirname(__DIR__, 2); // Dẫn tới thư mục gốc (Learning-Systems)

require_once $BASE . '/views/layouts/header.php';
require_once $BASE . '/views/admin/arrange_tkb.php';
require_once $BASE . '/views/layouts/footer.php';


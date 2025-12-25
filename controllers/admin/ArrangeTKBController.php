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
$flash_error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ------- Input filter -------
$namHoc   = $_GET['namHoc'] ?? ($_POST['namHoc'] ?? '');
$hocKy    = $_GET['hocKy'] ?? ($_POST['hocKy'] ?? '');      // nhận 'HK1/HK2' hoặc '1/2'
$maLop    = $_GET['maLop'] ?? ($_POST['maLop'] ?? '');
$maMonHoc = $_GET['maMonHoc'] ?? ($_POST['maMonHoc'] ?? '');

// Filter xem TKB (Tuần / Tháng / Kỳ)
$viewMode = $_GET['viewMode'] ?? 'hocKy'; // hocKy | tuan | thang
$tuan     = (int)($_GET['tuan'] ?? 1);
$thang    = $_GET['thang'] ?? ''; // YYYY-MM

// Mặc định năm học hiện tại theo Usecase
if ($namHoc === '') {
    $namHoc = $model->getNamHocHienTai();
}

// ------- Data cho form -------
$dsNamHoc = $model->getDanhSachNamHoc();
$dsHocKy  = $model->getDanhSachHocKy(); // ['1','2']

$dsLop   = [];
$dsPhong = $model->getDanhSachPhongHoc();

$previewAll    = [];
$tkbLopDangXem = [];
$conflicts     = [];

// Theo Usecase: danh sách môn của lớp + slot trống
$dsMonCuaLop = [];
$slotTrong   = [];

// Thông tin tuần HK
$tongTuanHocKy = 0;
$thongTinHocKy = null; // ['start'=>..., 'end'=>..., 'week_start'=>1, 'week_end'=>N]
$dsTuanHocKy   = [];
$dsThangHocKy  = [];

$displayNgayThu = []; // ngày thứ 2..6 của tuần đang xem
$displayTuan    = 1;

if ($namHoc !== '' && $hocKy !== '') {
    $dsLop = $model->getDanhSachLopTheoNamHoc($namHoc);

    $previewAll = $_SESSION['tkb_preview'][$namHoc][$hocKy] ?? [];

    $tongTuanHocKy = $model->getTongSoTuanHocKy($namHoc, $hocKy);
    $thongTinHocKy = $model->getThongTinHocKy($namHoc, $hocKy);
    $dsTuanHocKy   = $model->getDanhSachTuanHocKy($namHoc, $hocKy);
    $dsThangHocKy  = $model->getDanhSachThangHocKy($namHoc, $hocKy);

    // Xác định tuần đang xem
    if ($viewMode === 'thang' && $thang !== '') {
        $tuanTheoThang = $model->getTuanDauTienTrongThang($namHoc, $hocKy, $thang);
        $displayTuan = $tuanTheoThang ?: 1;
    } elseif ($viewMode === 'tuan') {
        $displayTuan = max(1, min($tuan, max(1, $tongTuanHocKy)));
    } else {
        // hocKy => mặc định hiển thị tuần 1
        $displayTuan = 1;
    }

    $displayNgayThu = $model->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, $displayTuan);
}

if ($namHoc !== '' && $hocKy !== '' && $maLop !== '') {
    $dsMonCuaLop = $model->getMonCuaLop($maLop, $namHoc, $hocKy);
}

if ($namHoc !== '' && $hocKy !== '' && $maLop !== '' && $maMonHoc !== '') {
    // slot trống theo pattern (thứ/tiết), có check trùng GV/phòng
    $slotTrong = $model->getSlotTrongCuaLop($maLop, $namHoc, $hocKy, $maMonHoc);
}

// ------- Handle actions -------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* 1) Tự động sắp xếp tất cả lớp (PREVIEW) - tránh trùng GV/Phòng ngay khi xếp */
    if ($action === 'auto_all') {
        if ($namHoc === '' || $hocKy === '') {
            $flash_error = 'Vui lòng chọn Năm học và Học kỳ trước khi tự động sắp xếp.';
        } else {
            $previewAll = $model->generateAutoTKBForAll($namHoc, $hocKy);
            $_SESSION['tkb_preview'][$namHoc][$hocKy] = $previewAll;
            $flash_success = 'Đã tạo thời khóa biểu đề xuất cho tất cả các lớp. Chọn lớp để xem chi tiết và xác nhận lưu.';
        }

    /* 2) Lưu TKB cho một lớp từ bản preview */
    } elseif ($action === 'save_class') {
        if ($namHoc === '' || $hocKy === '' || $maLop === '') {
            $flash_error = 'Thiếu thông tin Năm học / Học kỳ / Lớp cần lưu.';
        } else {
            $allPreview = $_SESSION['tkb_preview'][$namHoc][$hocKy] ?? [];
            if (empty($allPreview[$maLop])) {
                $flash_error = 'Không tìm thấy dữ liệu TKB đề xuất cho lớp này. Hãy tự động sắp xếp lại.';
            } else {
                $plan = $allPreview[$maLop];
                $conflicts = $model->findConflictsPattern($plan, $namHoc, $hocKy, $maLop);

                if (!empty($conflicts) && empty($_POST['force_save'])) {
                    $flash_error = 'Phát hiện trùng lịch. Kiểm tra danh sách bên dưới. Nếu vẫn muốn lưu hãy đánh dấu "Chấp nhận ghi đè" và bấm lưu lại.';
                    $tkbLopDangXem = $plan; // show preview
                } else {
                    $model->saveTKBForClassFromPattern($maLop, $namHoc, $hocKy, $plan);
                    $flash_success = "Thời khóa biểu được sắp xếp thành công và đã lưu cho lớp $maLop.";
                    $tkbLopDangXem = $model->getTKBByWeek($maLop, $namHoc, $hocKy, $displayTuan);
                }
            }
        }

    /* 3) Thủ công: chọn môn -> slot trống -> xác nhận */
    } elseif ($action === 'manual_add') {
        $namHoc   = $_POST['namHoc'] ?? '';
        $hocKy    = $_POST['hocKy'] ?? '';
        $maLop    = $_POST['maLop'] ?? '';
        $maMonHoc = $_POST['maMonHoc'] ?? '';
        $slot     = $_POST['slot'] ?? ''; // "thu|tiet"
        $maPhong  = $_POST['maPhong'] ?? '';

        if ($namHoc === '' || $hocKy === '' || $maLop === '' || $maMonHoc === '' || $slot === '') {
            $flash_error = 'Vui lòng chọn đầy đủ: Năm học, Học kỳ, Lớp, Môn và Slot trống.';
        } else {
            [$thuStr, $tietStr] = array_pad(explode('|', $slot), 2, '');
            $thu  = (int)$thuStr;  // thứ VN: 2..6
            $tiet = (int)$tietStr; // 1..8

            if ($thu < 2 || $thu > 6 || $tiet < 1 || $tiet > 8) {
                $flash_error = 'Slot không hợp lệ.';
            } else {
                // Lấy phòng theo lớp nếu bỏ trống
                if ($maPhong === '') {
                    $maPhong = $model->getPhongCuaLop($maLop, $namHoc) ?? '';
                }

                // Lấy đúng GV dạy môn này của lớp
                $gvInfo = $model->getGiaoVienDayMonCuaLop($maLop, $maMonHoc, $namHoc, $hocKy);
                $maGV   = $gvInfo['maGV'] ?? null;

                $data = [
                    'namHoc'   => $namHoc,
                    'hocKy'    => $hocKy,
                    'maLop'    => $maLop,
                    'maMonHoc' => $maMonHoc,
                    'thu'      => $thu,
                    'tiet'     => $tiet,
                    'maPhong'  => $maPhong,
                    'maGV'     => $maGV,
                    'loaiTiet' => 'Chinh_khoa',
                ];

                // Check trùng GV/Phòng theo pattern
                $conf = $model->findConflictsPattern([$data], $namHoc, $hocKy, $maLop);
                if (!empty($conf)) {
                    $flash_error = 'Slot bạn chọn đang trùng phòng/giáo viên với lớp khác. Vui lòng chọn slot khác.';
                    $conflicts = $conf;
                } else {
                    if ($model->addManualSlotToAllWeeks($data)) {
                        $flash_success = 'Thời khóa biểu được sắp xếp thành công.';
                    } else {
                        $flash_error = 'Slot này đã có thời khóa biểu (không còn trống).';
                    }
                }

                $tkbLopDangXem = $model->getTKBByWeek($maLop, $namHoc, $hocKy, $displayTuan);
            }
        }
    }
}

// Nếu có maLop đang xem mà chưa có dữ liệu thì lấy từ preview hoặc DB
if ($maLop !== '' && empty($tkbLopDangXem) && $namHoc !== '' && $hocKy !== '') {
    if (!empty($previewAll[$maLop])) {
        $tkbLopDangXem = $previewAll[$maLop]; // preview pattern
    } else {
        $tkbLopDangXem = $model->getTKBByWeek($maLop, $namHoc, $hocKy, $displayTuan);
    }
}

$BASE = dirname(__DIR__, 2);

require_once $BASE . '/views/layouts/header.php';
require_once $BASE . '/views/admin/arrange_tkb.php';
require_once $BASE . '/views/layouts/footer.php';

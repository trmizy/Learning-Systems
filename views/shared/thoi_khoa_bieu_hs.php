<?php
$pageTitle = 'Thời khóa biểu';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/assign_exam.css">

<div class="assign-container">
    <!-- Header -->
    <div class="assign-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3><i class="fa-solid fa-calendar-days me-2"></i>Thời khóa biểu</h3>
                <?php if ($thongTinLop): ?>
                    <p>
                        <i class="fa-solid fa-school me-2"></i>Lớp: <strong><?= htmlspecialchars($thongTinLop['tenLop']) ?></strong>
                        <span class="mx-2">|</span>
                        <i class="fa-solid fa-calendar-alt me-2"></i>Năm học: <strong><?= htmlspecialchars($thongTinLop['namHoc']) ?></strong>
                        <?php if (!empty($thongTinLop['tenGVCN'])): ?>
                            <span class="mx-2">|</span>
                            <i class="fa-solid fa-chalkboard-user me-2"></i>GVCN: <strong><?= htmlspecialchars($thongTinLop['tenGVCN']) ?></strong>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <a href="index.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Chọn con (chỉ hiển thị cho phụ huynh) -->
    <?php if (isset($danhSachCon) && count($danhSachCon) > 1): ?>
    <div class="card-modern mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="<?= $_GET['action'] ?? 'xem_tkb' ?>">
                <div class="col-auto">
                    <label class="form-label fw-bold mb-0">
                        <i class="fa-solid fa-user-graduate me-2"></i>Chọn con:
                    </label>
                </div>
                <div class="col-md-4">
                    <select name="maHocSinh" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($danhSachCon as $con): ?>
                            <option value="<?= $con['maHocSinh'] ?>" <?= ($con['maHocSinh'] == $maHocSinh) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($con['hoTen']) ?> - <?= htmlspecialchars($con['tenLop']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form chọn tuần - GIỐNG GVCN -->
    <div class="card-modern">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="<?= $_GET['action'] ?? 'xem_tkb' ?>">
                <?php if (isset($maHocSinh)): ?>
                    <input type="hidden" name="maHocSinh" value="<?= htmlspecialchars($maHocSinh) ?>">
                <?php endif; ?>
                
                <div class="col-auto">
                    <label for="week" class="form-label fw-bold mb-0">
                        <i class="fa-solid fa-calendar-week me-2"></i>Chọn tuần:
                    </label>
                </div>
                
                <div class="col-auto">
                    <a href="?action=<?= $_GET['action'] ?? 'xem_tkb' ?><?= isset($maHocSinh) ? '&maHocSinh=' . $maHocSinh : '' ?>&week=<?= $prevWeekDate ?>" 
                       class="btn btn-outline-secondary" title="Tuần trước">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                </div>
                
                <div class="col-md-3">
                    <input type="date" class="form-control" id="week" name="week" 
                           value="<?= htmlspecialchars($selected_date) ?>">
                </div>

                <div class="col-auto">
                    <a href="?action=<?= $_GET['action'] ?? 'xem_tkb' ?><?= isset($maHocSinh) ? '&maHocSinh=' . $maHocSinh : '' ?>&week=<?= $nextWeekDate ?>" 
                       class="btn btn-outline-secondary" title="Tuần sau">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="col-auto">
                    <button type="submit" class="btn btn-gradient-primary">
                        <i class="fa-solid fa-search me-1"></i>Xem ngày đã chọn
                    </button>
                </div>
                
                <div class="col-auto">
                    <a href="?action=<?= $_GET['action'] ?? 'xem_tkb' ?><?= isset($maHocSinh) ? '&maHocSinh=' . $maHocSinh : '' ?>" 
                       class="btn btn-gradient-success">
                        <i class="fa-solid fa-calendar-day me-1"></i>Tuần hiện tại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng thời khóa biểu - GIỐNG GVCN -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h5>
                <i class="fa-solid fa-table"></i>
                Thời khóa biểu tuần (<?= htmlspecialchars($tuanHienTai) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tkbGrid)): ?>
                <div class="p-5 text-center">
                    <i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có lịch học trong tuần này</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th style="width: 8%; vertical-align: middle;">Tiết</th>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <th style="width: 13%;">
                                        <div><?= $day['name'] ?></div>
                                        <small class="fw-normal"><?= $day['date'] ?></small>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($tiet = 1; $tiet <= 10; $tiet++): ?>
                                <tr>
                                    <td class="fw-bold text-center">Tiết <?= $tiet ?></td>
                                    <?php for ($ngay = 1; $ngay <= 7; $ngay++): 
                                        $tietHoc = $tkbGrid[$ngay][$tiet] ?? null;
                                    ?>
                                        <td style="height: 60px; vertical-align: middle;">
                                            <?php if ($tietHoc): ?>
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold text-primary" style="font-size: 14px;">
                                                        <?= htmlspecialchars($tietHoc['tenMon']) ?>
                                                    </div>
                                                    <small class="text-muted d-block" style="font-size: 11px;">
                                                        📍 <?= htmlspecialchars($tietHoc['tenPhong']) ?>
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center text-muted">-</div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ghi chú -->
    <div class="card-modern">
        <div class="card-body">
            <h6 class="fw-bold mb-3">
                <i class="fa-solid fa-circle-info me-2"></i>Ghi chú
            </h6>
            <ul class="mb-0">
                <li>Thời gian mỗi tiết học: <strong>45 phút</strong></li>
                <li>Giờ ra chơi: Sau tiết 2 (10 phút) và sau tiết 5 (15 phút)</li>
                <li>Lưu ý: Học sinh cần mang đầy đủ sách vở, dụng cụ học tập theo thời khóa biểu</li>
                <li>Trong trường hợp có thay đổi, giáo viên sẽ thông báo trước</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

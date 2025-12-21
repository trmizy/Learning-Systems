<?php
// View: Trang quản lý phân công giảng dạy và phòng học
// Path: views/bgh/phanCongGiangDayVaPhongHoc/quan_ly_phan_cong.php
$pageTitle = 'Phân công giảng dạy và phòng học - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/phan_cong.css">

<div class="assignment-container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-user-gear text-primary me-2"></i>
                Phân công giảng dạy và phòng học
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-info-circle me-1"></i>
                Quản lý phân công giáo viên chủ nhiệm và phòng học cho các lớp
            </p>
        </div>
        <a href="/views/bgh/dashboard.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($message) && $message): ?>
    <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-<?php echo (isset($messageType) && $messageType === 'success') ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Danh sách lớp học -->
    <div class="card card-assignment">
        <div class="card-body">
            <!-- Header with Year Filter -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-list-check text-success me-2"></i>
                    Danh sách lớp học
                </h5>
                
                <!-- Year Filter -->
                <div class="d-flex align-items-center gap-2">
                    <label for="namHocFilter" class="mb-0 text-muted">
                        <i class="fa-solid fa-calendar-alt me-1"></i>
                        Năm học:
                    </label>
                    <form method="GET" style="display:inline;">
                        <input type="hidden" name="action" value="bgh-phan-cong">
                        <select name="namHoc" class="form-select form-select-sm" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                            <?php while($namHoc = $danhSachNamHoc->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo htmlspecialchars($namHoc['namHoc']); ?>" 
                                        <?php echo $namHoc['namHoc'] === $namHocFilter ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($namHoc['namHoc']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-assignment table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lớp</th>
                            <th>Khối</th>
                            <th>Sĩ số</th>
                            <th>GVCN</th>
                            <th>Phòng học</th>
                            <th>Năm học</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($lop = $danhSachLop->fetch()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($lop['tenLop']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($lop['maLop']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary">Khối <?php echo htmlspecialchars($lop['khoi']); ?></span>
                            </td>
                            <td>
                                <i class="fa-solid fa-users me-1"></i>
                                <?php echo $lop['siSo']; ?> HS
                            </td>
                            <td>
                                <?php if ($lop['maGVCN']): ?>
                                    <div class="info-badge">
                                        <div>
                                            <strong><?php echo htmlspecialchars($lop['tenGVCN']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($lop['maGVCN']); ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">
                                        <i class="fa-solid fa-exclamation-circle me-1"></i>Chưa gán
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lop['maPhong']): ?>
                                    <div class="info-badge">
                                        <i class="fa-solid fa-door-open text-success"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($lop['tenPhong']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($lop['maPhong']); ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">
                                        <i class="fa-solid fa-exclamation-circle me-1"></i>Chưa gán
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-calendar me-1"></i>
                                <?php echo htmlspecialchars($lop['namHoc']); ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1">
                                    <div class="btn-group" role="group">
                                        <a href="?action=bgh-phan-cong&action_modal=gan_gvcn&maLop=<?php echo urlencode($lop['maLop']); ?>&tenLop=<?php echo urlencode($lop['tenLop']); ?>&maGVCN=<?php echo urlencode($lop['maGVCN'] ?? ''); ?>&namHoc=<?php echo urlencode($namHocFilter); ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Gán GVCN">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </a>
                                        <a href="?action=bgh-phan-cong&action_modal=gan_phong&maLop=<?php echo urlencode($lop['maLop']); ?>&tenLop=<?php echo urlencode($lop['tenLop']); ?>&maPhong=<?php echo urlencode($lop['maPhong'] ?? ''); ?>&namHoc=<?php echo urlencode($namHocFilter); ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Gán phòng học">
                                            <i class="fa-solid fa-door-open"></i>
                                        </a>
                                        <?php if ($lop['maGVCN']): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa GVCN của lớp <?php echo htmlspecialchars($lop['tenLop']); ?>?');">
                                            <input type="hidden" name="action" value="xoa_gvcn">
                                            <input type="hidden" name="maLop" value="<?php echo $lop['maLop']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa GVCN">
                                                <i class="fa-solid fa-user-xmark"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($lop['maPhong']): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa phòng học của lớp <?php echo htmlspecialchars($lop['tenLop']); ?>?');">
                                            <input type="hidden" name="action" value="xoa_phong">
                                            <input type="hidden" name="maLop" value="<?php echo $lop['maLop']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa phòng">
                                                <i class="fa-solid fa-door-closed"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?action=bgh-phan-cong-mon-hoc&maLop=<?php echo urlencode($lop['maLop']); ?>" 
                                       class="btn btn-sm btn-primary"
                                       title="Phân công GV bộ môn">
                                        <i class="fa-solid fa-chalkboard-user me-1"></i>
                                        Phân công GV bộ môn
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gán GVCN -->
<?php if (isset($_GET['action_modal']) && $_GET['action_modal'] === 'gan_gvcn'): ?>
<div class="modal-backdrop fade show" style="z-index: 1040;"></div>
<div class="modal fade show" id="modalGanGVCN" tabindex="-1" style="display:block; z-index: 1050;" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Gán giáo viên chủ nhiệm
                </h5>
                <a href="?action=bgh-phan-cong&namHoc=<?php echo urlencode($namHocFilter); ?>" class="btn-close"></a>
            </div>
            <form method="POST" action="/public/index.php?action=bgh-phan-cong">
                <div class="modal-body">
                    <input type="hidden" name="action" value="gan_gvcn">
                    <input type="hidden" name="maLop" value="<?php echo htmlspecialchars($_GET['maLop'] ?? ''); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lớp học</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_GET['tenLop'] ?? ''); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn giáo viên chủ nhiệm</label>
                        <select name="maGV" class="form-select" required>
                            <option value="">-- Chọn giáo viên --</option>
                            <?php foreach ($danhSachGiaoVien as $gv): ?>
                                <option value="<?php echo $gv['maGV']; ?>" <?php echo (isset($_GET['maGVCN']) && $_GET['maGVCN'] === $gv['maGV']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($gv['hoTen']); ?> 
                                    (<?php echo htmlspecialchars($gv['monHocPhuTrach'] ?? 'N/A'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/public/index.php?php echo urlencode($namHocFilter); ?>" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: Gán phòng học -->
<?php if (isset($_GET['action_modal']) && $_GET['action_modal'] === 'gan_phong'): 
    $maLopHienTai = $_GET['maLop'] ?? '';
    $soPhongKhaDung = 0;
?>
<div class="modal-backdrop fade show" style="z-index: 1040;"></div>
<div class="modal fade show" id="modalGanPhong" tabindex="-1" style="display:block; z-index: 1050;" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-door-open me-2"></i>
                    Gán phòng học
                </h5>
                <a href="?action=bgh-phan-cong&namHoc=<?php echo urlencode($namHocFilter); ?>" class="btn-close"></a>
            </div>
            <form method="POST" action="/public/index.php?action=bgh-phan-cong">
                <div class="modal-body">
                    <input type="hidden" name="action" value="gan_phong">
                    <input type="hidden" name="maLop" value="<?php echo htmlspecialchars($_GET['maLop'] ?? ''); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lớp học</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_GET['tenLop'] ?? ''); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn phòng học</label>
                        <select name="maPhong" class="form-select" required>
                            <option value="">-- Chọn phòng học --</option>
                            <?php if (empty($danhSachPhongHoc)): ?>
                                <option value="" disabled>Không có phòng học khả dụng</option>
                            <?php else: ?>
                                <?php foreach ($danhSachPhongHoc as $phong): 
                                    // Chỉ hiển thị phòng chưa gán hoặc đang gán cho lớp hiện tại
                                    $lopDangGan = $phong['lopDangGan'] ?? '';
                                    if (empty($lopDangGan) || $lopDangGan === $maLopHienTai):
                                        $soPhongKhaDung++;
                                ?>
                                    <option value="<?php echo $phong['maPhong']; ?>" <?php echo (isset($_GET['maPhong']) && $_GET['maPhong'] === $phong['maPhong']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($phong['tenPhong']); ?> 
                                        (Sức chứa: <?php echo $phong['sucChua'] ?? 'N/A'; ?>)
                                    </option>
                                <?php endif; endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted"><?php echo $soPhongKhaDung; ?> phòng khả dụng</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/public/index.php?action=bgh-phan-cong&namHoc=<?php echo urlencode($namHocFilter); ?>" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

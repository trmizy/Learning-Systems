<?php
$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<link rel="stylesheet" href="/assets/css/assign_exam.css">

<div class="assign-container">
    <!-- Header -->
    <div class="assign-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3><i class="fa-solid fa-tasks me-2"></i>Phân công giáo viên ra đề thi</h3>
                <p><i class="fa-solid fa-info-circle me-2"></i>Quản lý phân công ra đề thi cho giáo viên bộ môn</p>
            </div>
            <a href="index.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-modern">
            <i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-modern">
            <i class="fa-solid fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Form phân công -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h5><i class="fa-solid fa-user-plus"></i>Tạo phân công mới</h5>
        </div>
        <div class="card-body p-4">
            <form action="index.php?action=store_assign_exam" method="POST" class="form-modern">
                <div class="row g-4">
                    <!-- Khối -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fa-solid fa-layer-group me-2"></i>Khối
                        </label>
                        <select name="khoi" class="form-select" required>
                            <option value="">-- Chọn khối --</option>
                            <?php foreach ($khoi as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>">Khối <?= htmlspecialchars($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Học kỳ -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar-alt me-2"></i>Học kỳ
                        </label>
                        <select name="hocKy" class="form-select" required>
                            <option value="">-- Chọn học kỳ --</option>
                            <option value="I">Học kỳ I</option>
                            <option value="II">Học kỳ II</option>
                        </select>
                    </div>

                    <!-- Kỳ thi -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fa-solid fa-file-lines me-2"></i>Kỳ thi
                        </label>
                        <select name="kyThi" class="form-select" required>
                            <option value="">-- Chọn kỳ thi --</option>
                            <option value="Giữa kỳ">Giữa kỳ</option>
                            <option value="Cuối kỳ">Cuối kỳ</option>
                        </select>
                    </div>

                    <!-- Số lượng đề -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fa-solid fa-list-ol me-2"></i>Số lượng đề
                        </label>
                        <input type="number" name="soLuongDe" class="form-control" min="1" placeholder="VD: 2" required>
                    </div>

                    <!-- Giáo viên -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-users me-2"></i>Giáo viên bộ môn
                        </label>
                        <select name="listGV[]" multiple class="form-select" size="6" required>
                            <?php foreach ($listGV as $gv): ?>
                                <option value="<?= $gv['maGV'] ?>">
                                    <?= $gv['hoTen'] ?> - <?= $gv['monHocPhuTrach'] ?> (<?= $gv['gioiTinh'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small><i class="fa-solid fa-circle-info me-1"></i>Giữ Ctrl để chọn nhiều giáo viên</small>
                    </div>

                    <!-- Thời hạn -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fa-solid fa-clock me-2"></i>Thời hạn nộp đề
                        </label>
                        <input type="datetime-local" name="thoiHan" class="form-control" required>
                        
                        <label class="form-label mt-3">
                            <i class="fa-solid fa-comment-dots me-2"></i>Ghi chú
                        </label>
                        <textarea name="ghiChu" rows="3" class="form-control" placeholder="Nhập ghi chú (nếu có)..."></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-gradient-primary">
                            <i class="fa-solid fa-paper-plane me-2"></i>Xác nhận phân công
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách phân công -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h5><i class="fa-solid fa-list-check"></i>Danh sách phân công hiện có (<?= count($phanCong) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($phanCong)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                    <p>Chưa có phân công nào</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Học kỳ</th>
                                <th>Kỳ thi</th>
                                <th>Số đề</th>
                                <th>Giáo viên</th>
                                <th>Thời hạn</th>
                                <th>Ghi chú</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phanCong as $index => $row): 
                                $thoiHan = strtotime($row['thoiHan']);
                                $now = time();
                                $isOverdue = $thoiHan < $now;
                            ?>
                                <tr>
                                    <td><strong><?= $index + 1 ?></strong></td>
                                    <td><?= htmlspecialchars($row['hocKy']) ?></td>
                                    <td><?= htmlspecialchars($row['kyThi']) ?></td>
                                    <td><span class="badge badge-info-modern"><?= htmlspecialchars($row['soLuongDe']) ?> đề</span></td>
                                    <td><i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($row['giaoVien']) ?></td>
                                    <td>
                                        <i class="fa-solid fa-calendar-check me-2"></i><?= date('d/m/Y H:i', $thoiHan) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['ghiChu'] ?: '---') ?></td>
                                    <td>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge badge-danger-modern">
                                                <i class="fa-solid fa-exclamation-triangle me-1"></i>Quá hạn
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success-modern">
                                                <i class="fa-solid fa-clock me-1"></i>Còn hạn
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
                <h3><i class="fa-solid fa-list-check me-2"></i>Phân công ra đề của tôi</h3>
                <p><i class="fa-solid fa-info-circle me-2"></i>Danh sách đề thi được tổ trưởng phân công</p>
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

    <!-- Thống kê -->
    <?php if (!empty($phanCong)): 
        $totalOverdue = 0;
        $totalPending = 0;
        foreach ($phanCong as $row) {
            strtotime($row['thoiHan']) < time() ? $totalOverdue++ : $totalPending++;
        }
    ?>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-item">
                <div class="stat-value"><?= count($phanCong) ?></div>
                <div class="stat-label"><i class="fa-solid fa-file-lines me-2"></i>Tổng phân công</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-item" style="border-left-color: #38ef7d;">
                <div class="stat-value" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $totalPending ?></div>
                <div class="stat-label"><i class="fa-solid fa-clock me-2"></i>Còn hạn</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-item" style="border-left-color: #f5576c;">
                <div class="stat-value" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $totalOverdue ?></div>
                <div class="stat-label"><i class="fa-solid fa-exclamation-triangle me-2"></i>Quá hạn</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Danh sách phân công -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h5><i class="fa-solid fa-clipboard-list"></i>Danh sách đề thi được phân công</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($phanCong)): ?>
                <div class="p-5 text-center">
                    <i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có phân công nào</h5>
                    <p class="text-muted">Bạn chưa được tổ trưởng phân công ra đề thi.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Học kỳ</th>
                                <th>Kỳ thi</th>
                                <th>Số lượng đề</th>
                                <th>Thời hạn nộp</th>
                                <th>Ghi chú</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phanCong as $index => $row): 
                                $thoiHan = strtotime($row['thoiHan']);
                                $now = time();
                                $isOverdue = $thoiHan < $now;
                                $timeLeft = $thoiHan - $now;
                                $daysLeft = floor($timeLeft / 86400);
                            ?>
                                <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                    <td><strong><?= $index + 1 ?></strong></td>
                                    <td><?= htmlspecialchars($row['hocKy']) ?></td>
                                    <td><?= htmlspecialchars($row['kyThi']) ?></td>
                                    <td>
                                        <span class="badge badge-info-modern">
                                            <?= htmlspecialchars($row['soLuongDe']) ?> đề
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fa-solid fa-calendar-check me-2"></i><?= date('d/m/Y H:i', $thoiHan) ?>
                                        <?php if (!$isOverdue && $daysLeft <= 3): ?>
                                            <br><small class="text-warning"><i class="fa-solid fa-bell me-1"></i>Còn <?= $daysLeft ?> ngày</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['ghiChu'] ?? '---') ?></td>
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
                                    <td>
                                        <button class="btn btn-sm btn-gradient-success" title="Nộp đề">
                                            <i class="fa-solid fa-upload"></i>
                                        </button>
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

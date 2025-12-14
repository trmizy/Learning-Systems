<?php
// ⚠️ QUAN TRỌNG: Thêm header để tạo trang độc lập
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['gvbm', 'gvcn']);

// Tiêu đề trang
$pageTitle = 'Phân công ra đề - THPT';
require_once __DIR__ . '/../layouts/header.php';

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<link rel="stylesheet" href="/assets/css/assign_exam.css">

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=gvbm-dashboard">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
            </li>
            <li class="breadcrumb-item active">Phân công ra đề</li>
        </ol>
    </nav>

    <div class="assign-container">
        <!-- Header -->
        <div class="assign-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="fa-solid fa-list-check me-2"></i>Phân công ra đề của tôi</h3>
                    <p class="text-muted mb-0">
                        <i class="fa-solid fa-info-circle me-2"></i>Danh sách đề thi được tổ trưởng phân công
                    </p>
                </div>
                <a href="/public/index.php?action=gvbm-dashboard" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0 fw-bold"><?= count($phanCong) ?></h2>
                                <p class="text-muted mb-0">
                                    <i class="fa-solid fa-file-lines me-2"></i>Tổng phân công
                                </p>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fa-solid fa-clipboard-list fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0 fw-bold text-success"><?= $totalPending ?></h2>
                                <p class="text-muted mb-0">
                                    <i class="fa-solid fa-clock me-2"></i>Còn hạn
                                </p>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fa-solid fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0 fw-bold text-danger"><?= $totalOverdue ?></h2>
                                <p class="text-muted mb-0">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>Quá hạn
                                </p>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fa-solid fa-bell fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Danh sách phân công -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fa-solid fa-clipboard-list me-2"></i>Danh sách đề thi được phân công
                </h5>
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
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">STT</th>
                                    <th>Học kỳ</th>
                                    <th>Kỳ thi</th>
                                    <th class="text-center">Số lượng đề</th>
                                    <th>Thời hạn nộp</th>
                                    <th>Ghi chú</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center" style="width: 120px;">Thao tác</th>
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
                                        <td class="text-center"><strong><?= $index + 1 ?></strong></td>
                                        <td><?= htmlspecialchars($row['hocKy']) ?></td>
                                        <td><?= htmlspecialchars($row['kyThi']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($row['soLuongDe']) ?> đề
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa-solid fa-calendar-check me-2"></i>
                                            <?= date('d/m/Y H:i', $thoiHan) ?>
                                            <?php if (!$isOverdue && $daysLeft <= 3): ?>
                                                <br><small class="text-warning">
                                                    <i class="fa-solid fa-bell me-1"></i>Còn <?= $daysLeft ?> ngày
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['ghiChu'] ?? '---') ?></td>
                                        <td class="text-center">
                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fa-solid fa-exclamation-triangle me-1"></i>Quá hạn
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="fa-solid fa-clock me-1"></i>Còn hạn
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary" title="Nộp đề">
                                                <i class="fa-solid fa-upload me-1"></i>Nộp đề
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
</div>

<style>
    .assign-container {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
</style>

<?php
// ⚠️ QUAN TRỌNG: Thêm footer
require_once __DIR__ . '/../layouts/footer.php';
?>

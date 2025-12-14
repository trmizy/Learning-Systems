<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['ph']);

$pageTitle = 'Danh sách đơn xin nghỉ - THPT';
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .request-list-container {
        animation: fadeIn 0.5s ease;
    }
    
    .request-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .request-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .status-pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .status-approved {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .status-rejected {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
    }
    
    .status-cancelled {
        background: #6c757d;
        color: white;
    }
    
    .child-selector-tabs {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .child-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .child-tab:hover {
        background: rgba(245, 87, 108, 0.1);
    }
    
    .child-tab.active {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-color: #f5576c;
    }
</style>

<div class="container-fluid request-list-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
            </li>
            <li class="breadcrumb-item active">Danh sách đơn xin nghỉ</li>
        </ol>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="fa-solid fa-list-check text-danger me-2"></i>
            Danh sách đơn xin nghỉ
        </h3>
        <a href="/public/index.php?action=ph-leave-create&maHS=<?php echo htmlspecialchars($maHS ?? ''); ?>" class="btn btn-danger">
            <i class="fa-solid fa-plus me-2"></i>Tạo đơn mới
        </a>
    </div>

    <!-- Child Selector Tabs -->
    <?php if (count($danhSachCon) > 1): ?>
    <div class="child-selector-tabs">
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($danhSachCon as $con): ?>
            <div class="child-tab <?php echo ($con['maHS'] == $maHS) ? 'active' : ''; ?>"
                 onclick="window.location.href='?action=ph-leave-list&maHS=<?php echo htmlspecialchars($con['maHS']); ?>'">
                <i class="fa-solid fa-user me-2"></i>
                <?php echo htmlspecialchars($con['hoTen']); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Request List -->
    <?php 
    // === DEBUG INFO ===
    if (isset($_GET['debug'])) {
        echo '<div class="alert alert-info">';
        echo '<strong>DEBUG INFO:</strong><br>';
        echo 'maHS: ' . htmlspecialchars($maHS ?? 'NULL') . '<br>';
        echo 'danhSachCon count: ' . count($danhSachCon ?? []) . '<br>';
        echo 'danhSachDon count: ' . count($danhSachDon ?? []) . '<br>';
        echo 'danhSachDon isset: ' . (isset($danhSachDon) ? 'YES' : 'NO') . '<br>';
        if (isset($danhSachDon) && !empty($danhSachDon)) {
            echo '<pre>' . print_r($danhSachDon[0], true) . '</pre>';
        }
        echo '</div>';
    }
    // === END DEBUG ===
    
    if (empty($danhSachDon)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-inbox fa-4x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">Chưa có đơn xin nghỉ nào</h5>
                <p class="text-muted">Bấm nút "Tạo đơn mới" để gửi đơn xin nghỉ cho con em</p>
                <p class="text-muted small">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Đang tìm đơn cho học sinh: <?php echo htmlspecialchars($maHS ?? 'N/A'); ?>
                </p>
                <a href="/public/index.php?action=ph-leave-create&maHS=<?php echo htmlspecialchars($maHS ?? ''); ?>" class="btn btn-danger mt-3">
                    <i class="fa-solid fa-plus me-2"></i>Tạo đơn đầu tiên
                </a>
                <a href="?action=ph-leave-list&maHS=<?php echo htmlspecialchars($maHS ?? ''); ?>&debug=1" class="btn btn-outline-secondary mt-3 ms-2">
                    <i class="fa-solid fa-bug me-2"></i>Debug
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($danhSachDon as $don): ?>
        <div class="request-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fa-solid fa-calendar-xmark text-danger me-2"></i>
                                    Đơn xin nghỉ ngày <?php echo date('d/m/Y', strtotime($don['ngay'])); ?>
                                </h6>
                                <p class="text-muted mb-2">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    Số buổi: <strong><?php echo htmlspecialchars($don['soBuoi']); ?> buổi</strong>
                                    <span class="mx-2">|</span>
                                    <i class="fa-solid fa-user me-1"></i>
                                    <?php echo htmlspecialchars($don['tenHocSinh']); ?> - <?php echo htmlspecialchars($don['tenLop']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Lý do:</strong> <?php echo htmlspecialchars($don['lyDo']); ?>
                                </p>
                                <?php if (!empty($don['minhChungKemTheo'])): ?>
                                <p class="mb-0">
                                    <i class="fa-solid fa-paperclip text-primary me-1"></i>
                                    <a href="<?php echo htmlspecialchars($don['minhChungKemTheo']); ?>" target="_blank">
                                        Xem minh chứng
                                    </a>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <?php
                        $trangThai = $don['trangThai'];
                        $statusClass = 'status-pending';
                        $statusIcon = 'fa-clock';
                        $statusText = 'Chờ duyệt';
                        
                        if ($trangThai === 'Da duoc duyet') {
                            $statusClass = 'status-approved';
                            $statusIcon = 'fa-check-circle';
                            $statusText = 'Đã duyệt';
                        } elseif ($trangThai === 'Bi tu choi') {
                            $statusClass = 'status-rejected';
                            $statusIcon = 'fa-times-circle';
                            $statusText = 'Bị từ chối';
                        } elseif ($trangThai === 'Da huy') {
                            $statusClass = 'status-cancelled';
                            $statusIcon = 'fa-ban';
                            $statusText = 'Đã hủy';
                        }
                        ?>
                        <div class="status-badge <?php echo $statusClass; ?> mb-3">
                            <i class="fa-solid <?php echo $statusIcon; ?> me-1"></i>
                            <?php echo $statusText; ?>
                        </div>
                        
                        <?php if ($trangThai === 'Cho duyet'): ?>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="if(confirm('Bạn có chắc muốn hủy đơn này?')) { window.location.href='/public/index.php?action=ph-leave-cancel&id=<?php echo urlencode($don['maDonXinPhep']); ?>'; }">
                            <i class="fa-solid fa-times me-1"></i>Hủy đơn
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['gvcn']);

$pageTitle = 'Tất cả đơn xin nghỉ - GVCN';
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
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
    
    .filter-tabs {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .filter-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        text-decoration: none;
        color: #495057;
    }
    
    .filter-tab:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .filter-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    
    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
    }
</style>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=gvcn-duyet-don-pending">Duyệt đơn xin nghỉ</a>
            </li>
            <li class="breadcrumb-item active">Tất cả đơn</li>
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
            <i class="fa-solid fa-list text-primary me-2"></i>
            Tất cả đơn xin nghỉ
        </h3>
        <a href="/public/index.php?action=gvcn-duyet-don-pending" class="btn btn-primary">
            <i class="fa-solid fa-clock me-2"></i>Đơn chờ duyệt
        </a>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info mb-4">
        <i class="fa-solid fa-info-circle me-2"></i>
        <strong>Lớp chủ nhiệm:</strong> <?php echo htmlspecialchars($lopChuNhiem['tenLop']); ?> - 
        Năm học <?php echo htmlspecialchars($lopChuNhiem['namHoc']); ?>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <div class="d-flex gap-2 flex-wrap">
            <a href="?action=gvcn-duyet-don-list&status=all" 
               class="filter-tab <?php echo ($trangThai === 'all') ? 'active' : ''; ?>">
                <i class="fa-solid fa-list me-2"></i>Tất cả
            </a>
            <a href="?action=gvcn-duyet-don-list&status=Cho duyet" 
               class="filter-tab <?php echo ($trangThai === 'Cho duyet') ? 'active' : ''; ?>">
                <i class="fa-solid fa-clock me-2"></i>Chờ duyệt
            </a>
            <a href="?action=gvcn-duyet-don-list&status=Da duoc duyet" 
               class="filter-tab <?php echo ($trangThai === 'Da duoc duyet') ? 'active' : ''; ?>">
                <i class="fa-solid fa-check-circle me-2"></i>Đã duyệt
            </a>
            <a href="?action=gvcn-duyet-don-list&status=Bi tu choi" 
               class="filter-tab <?php echo ($trangThai === 'Bi tu choi') ? 'active' : ''; ?>">
                <i class="fa-solid fa-times-circle me-2"></i>Bị từ chối
            </a>
            <a href="?action=gvcn-duyet-don-list&status=Da huy" 
               class="filter-tab <?php echo ($trangThai === 'Da huy') ? 'active' : ''; ?>">
                <i class="fa-solid fa-ban me-2"></i>Đã hủy
            </a>
        </div>
    </div>

    <!-- Request List -->
    <?php if (empty($danhSachDon)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-inbox fa-4x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">Không tìm thấy đơn nào</h5>
                <p class="text-muted">
                    <?php if ($trangThai !== 'all'): ?>
                    Không có đơn với trạng thái này
                    <?php else: ?>
                    Chưa có học sinh nào gửi đơn xin nghỉ
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($danhSachDon as $don): 
            $ngayNghi = strtotime($don['ngay']);
        ?>
        <div class="request-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Student Info -->
                    <div class="col-md-7">
                        <div class="d-flex align-items-start">
                            <div class="student-avatar me-3">
                                <?php echo strtoupper(substr($don['tenHocSinh'], 0, 2)); ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">
                                    <?php echo htmlspecialchars($don['tenHocSinh']); ?>
                                </h6>
                                <div class="text-muted small mb-2">
                                    <i class="fa-solid fa-id-card me-1"></i>
                                    <?php echo htmlspecialchars($don['maHS']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Ngày nghỉ:</strong> 
                                    <span class="text-primary">
                                        <?php echo date('d/m/Y', $ngayNghi); ?>
                                    </span>
                                    <span class="mx-2">|</span>
                                    <strong>Số buổi:</strong> 
                                    <span class="text-danger"><?php echo htmlspecialchars($don['soBuoi']); ?> buổi</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Lý do:</strong> 
                                    <?php echo htmlspecialchars($don['lyDo']); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fa-solid fa-user me-1"></i>
                                    PH: <?php echo htmlspecialchars($don['tenPhuHuynh']); ?>
                                </div>
                                <?php if (!empty($don['minhChungKemTheo'])): ?>
                                <div class="mt-2">
                                    <a href="<?php echo htmlspecialchars($don['minhChungKemTheo']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fa-solid fa-paperclip me-1"></i>Xem minh chứng
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Actions -->
                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                        <?php
                        $trangThaiDon = $don['trangThai'];
                        $statusClass = 'status-pending';
                        $statusIcon = 'fa-clock';
                        $statusText = 'Chờ duyệt';
                        
                        if ($trangThaiDon === 'Da duoc duyet') {
                            $statusClass = 'status-approved';
                            $statusIcon = 'fa-check-circle';
                            $statusText = 'Đã duyệt';
                        } elseif ($trangThaiDon === 'Bi tu choi') {
                            $statusClass = 'status-rejected';
                            $statusIcon = 'fa-times-circle';
                            $statusText = 'Bị từ chối';
                        } elseif ($trangThaiDon === 'Da huy') {
                            $statusClass = 'status-cancelled';
                            $statusIcon = 'fa-ban';
                            $statusText = 'Đã hủy';
                        }
                        ?>
                        <div class="status-badge <?php echo $statusClass; ?> mb-3">
                            <i class="fa-solid <?php echo $statusIcon; ?> me-1"></i>
                            <?php echo $statusText; ?>
                        </div>
                        
                        <a href="/public/index.php?action=gvcn-duyet-don-detail&id=<?php echo urlencode($don['maDonXinPhep']); ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye me-1"></i>Xem chi tiết
                        </a>
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

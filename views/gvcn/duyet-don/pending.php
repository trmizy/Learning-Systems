<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['gvcn']);

$pageTitle = 'Duyệt đơn xin nghỉ - GVCN';
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
        border-left: 4px solid #f093fb;
    }
    
    .request-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .request-card.urgent {
        border-left-color: #f5576c;
        background: linear-gradient(90deg, rgba(245, 87, 108, 0.05) 0%, white 100%);
    }
    
    .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .btn-approve {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
        color: white;
    }
    
    .btn-reject {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        color: white;
    }
</style>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
            </li>
            <li class="breadcrumb-item active">Duyệt đơn xin nghỉ</li>
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
            <i class="fa-solid fa-clipboard-check text-primary me-2"></i>
            Đơn xin nghỉ chờ duyệt
            <?php if (count($danhSachDon) > 0): ?>
            <span class="badge bg-danger rounded-pill"><?php echo count($danhSachDon); ?></span>
            <?php endif; ?>
        </h3>
        <a href="/public/index.php?action=gvcn-duyet-don-list" class="btn btn-outline-primary">
            <i class="fa-solid fa-list me-2"></i>Tất cả đơn
        </a>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info mb-4">
        <i class="fa-solid fa-info-circle me-2"></i>
        <strong>Lớp chủ nhiệm:</strong> <?php echo htmlspecialchars($lopChuNhiem['tenLop']); ?> - 
        Năm học <?php echo htmlspecialchars($lopChuNhiem['namHoc']); ?>
    </div>

    <!-- Request List -->
    <?php if (empty($danhSachDon)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-check-double fa-4x text-success mb-3 d-block"></i>
                <h5 class="text-success">Không có đơn nào chờ duyệt</h5>
                <p class="text-muted">Tất cả đơn đã được xử lý</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($danhSachDon as $don): 
            $ngayNghi = strtotime($don['ngay']);
            $today = strtotime(date('Y-m-d'));
            $isUrgent = $ngayNghi <= strtotime('+2 days', $today);
        ?>
        <div class="request-card <?php echo $isUrgent ? 'urgent' : ''; ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Student Info -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="student-avatar me-3">
                                <?php echo strtoupper(substr($don['tenHocSinh'], 0, 2)); ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">
                                    <?php echo htmlspecialchars($don['tenHocSinh']); ?>
                                    <?php if ($isUrgent): ?>
                                    <span class="badge bg-danger ms-2">
                                        <i class="fa-solid fa-exclamation me-1"></i>Khẩn cấp
                                    </span>
                                    <?php endif; ?>
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
                                    <span class="mx-2">|</span>
                                    <i class="fa-solid fa-phone me-1"></i>
                                    <?php echo htmlspecialchars($don['sdtPhuHuynh']); ?>
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

                    <!-- Actions -->
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="d-flex flex-column gap-2">
                            <!-- Approve Button -->
                            <button type="button" 
                                    class="btn btn-approve"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#approveModal<?php echo $don['maDonXinPhep']; ?>">
                                <i class="fa-solid fa-check me-2"></i>Phê duyệt
                            </button>

                            <!-- Reject Button -->
                            <button type="button" 
                                    class="btn btn-reject"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal<?php echo $don['maDonXinPhep']; ?>">
                                <i class="fa-solid fa-times me-2"></i>Từ chối
                            </button>

                            <!-- Detail Link -->
                            <a href="/public/index.php?action=gvcn-duyet-don-detail&id=<?php echo urlencode($don['maDonXinPhep']); ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-eye me-1"></i>Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal<?php echo $don['maDonXinPhep']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/public/index.php?action=gvcn-duyet-don-approve" method="POST">
                        <input type="hidden" name="maDonXinPhep" value="<?php echo htmlspecialchars($don['maDonXinPhep']); ?>">
                        
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-check-circle me-2"></i>Xác nhận phê duyệt
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc chắn muốn <strong class="text-success">PHÊ DUYỆT</strong> đơn xin nghỉ của:</p>
                            <div class="alert alert-info">
                                <strong><?php echo htmlspecialchars($don['tenHocSinh']); ?></strong><br>
                                Nghỉ ngày: <?php echo date('d/m/Y', $ngayNghi); ?> (<?php echo $don['soBuoi']; ?> buổi)<br>
                                Lý do: <?php echo htmlspecialchars($don['lyDo']); ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea class="form-control" name="ghiChu" rows="2" 
                                          placeholder="Ghi chú của giáo viên..."></textarea>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fa-solid fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Sau khi phê duyệt, số buổi nghỉ có phép sẽ được cập nhật vào hạnh kiểm của học sinh.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-times me-1"></i>Hủy
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-check me-1"></i>Xác nhận phê duyệt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal<?php echo $don['maDonXinPhep']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/public/index.php?action=gvcn-duyet-don-reject" method="POST" onsubmit="return validateReject(this)">
                        <input type="hidden" name="maDonXinPhep" value="<?php echo htmlspecialchars($don['maDonXinPhep']); ?>">
                        
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fa-solid fa-times-circle me-2"></i>Xác nhận từ chối
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc chắn muốn <strong class="text-danger">TỪ CHỐI</strong> đơn xin nghỉ của:</p>
                            <div class="alert alert-info">
                                <strong><?php echo htmlspecialchars($don['tenHocSinh']); ?></strong><br>
                                Nghỉ ngày: <?php echo date('d/m/Y', $ngayNghi); ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="lyDoTuChoi" rows="3" 
                                          placeholder="Vui lòng nêu rõ lý do từ chối..."
                                          required></textarea>
                                <div class="form-text">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    Lý do từ chối sẽ được gửi đến phụ huynh
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-times me-1"></i>Hủy
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fa-solid fa-ban me-1"></i>Xác nhận từ chối
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function validateReject(form) {
    const lyDo = form.lyDoTuChoi.value.trim();
    if (lyDo.length < 10) {
        alert('Vui lòng nhập lý do từ chối ít nhất 10 ký tự!');
        return false;
    }
    return true;
}
</script>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

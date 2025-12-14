<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['gvcn']);

$pageTitle = 'Chi tiết đơn xin nghỉ - GVCN';
require_once __DIR__ . '/../../layouts/header.php';

$ngayNghi = strtotime($don['ngay']);
?>

<style>
    .detail-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
    }
    
    .info-row {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 150px;
    }
    
    .status-badge {
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
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
            <li class="breadcrumb-item active">Chi tiết đơn</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="detail-card">
                <!-- Header -->
                <div class="detail-header">
                    <h3 class="mb-2">
                        <i class="fa-solid fa-file-lines me-2"></i>
                        Chi tiết đơn xin nghỉ
                    </h3>
                    <p class="mb-0 opacity-75">
                        Mã đơn: <?php echo htmlspecialchars($don['maDonXinPhep']); ?>
                    </p>
                </div>

                <!-- Content -->
                <div class="card-body p-4">
                    <!-- Trạng thái -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Trạng thái:</div>
                            <div class="col-md-9">
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
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <i class="fa-solid <?php echo $statusIcon; ?> me-2"></i>
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin học sinh -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Học sinh:</div>
                            <div class="col-md-9">
                                <strong><?php echo htmlspecialchars($don['tenHocSinh']); ?></strong><br>
                                <span class="text-muted">
                                    Mã HS: <?php echo htmlspecialchars($don['maHS']); ?> | 
                                    Lớp: <?php echo htmlspecialchars($don['tenLop']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Phụ huynh -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Phụ huynh:</div>
                            <div class="col-md-9">
                                <?php echo htmlspecialchars($don['tenPhuHuynh']); ?><br>
                                <span class="text-muted">
                                    <i class="fa-solid fa-phone me-1"></i>
                                    <?php echo htmlspecialchars($don['sdtPhuHuynh']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thời gian nghỉ -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Ngày nghỉ:</div>
                            <div class="col-md-9">
                                <span class="text-primary fw-bold">
                                    <?php echo date('d/m/Y', $ngayNghi); ?>
                                </span>
                                (<?php echo date('l', $ngayNghi); ?>)
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Số buổi nghỉ:</div>
                            <div class="col-md-9">
                                <span class="badge bg-danger px-3 py-2">
                                    <?php echo htmlspecialchars($don['soBuoi']); ?> buổi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Lý do -->
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Lý do nghỉ:</div>
                            <div class="col-md-9">
                                <?php echo nl2br(htmlspecialchars($don['lyDo'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Minh chứng -->
                    <?php if (!empty($don['minhChungKemTheo'])): ?>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-3 info-label">Minh chứng:</div>
                            <div class="col-md-9">
                                <a href="<?php echo htmlspecialchars($don['minhChungKemTheo']); ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-primary">
                                    <i class="fa-solid fa-paperclip me-2"></i>Xem file đính kèm
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hạnh kiểm hiện tại -->
                    <?php if (isset($don['hanhKiem']) && $don['hanhKiem']): ?>
                    <div class="info-row bg-light">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">
                                    <i class="fa-solid fa-chart-simple me-2"></i>
                                    Thông tin hạnh kiểm hiện tại
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Nghỉ có phép:</small>
                                        <div class="fw-bold"><?php echo $don['hanhKiem']['soBuoiNghiCoPhep'] ?? 0; ?> buổi</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Nghỉ không phép:</small>
                                        <div class="fw-bold text-danger"><?php echo $don['hanhKiem']['soBuoiNghiKhongCoPhep'] ?? 0; ?> buổi</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Xếp loại:</small>
                                        <div class="fw-bold text-success"><?php echo $don['hanhKiem']['loaiHanhKiem'] ?? 'Tốt'; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="mt-4 text-center">
                        <a href="/public/index.php?action=gvcn-duyet-don-list" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                        
                        <?php if ($trangThai === 'Cho duyet'): ?>
                        <button type="button" 
                                class="btn btn-success"
                                data-bs-toggle="modal" 
                                data-bs-target="#approveModal">
                            <i class="fa-solid fa-check me-2"></i>Phê duyệt
                        </button>
                        
                        <button type="button" 
                                class="btn btn-danger"
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="fa-solid fa-times me-2"></i>Từ chối
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($trangThai === 'Cho duyet'): ?>
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
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
                    <p>Bạn có chắc chắn muốn <strong class="text-success">PHÊ DUYỆT</strong> đơn này?</p>
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        Sau khi phê duyệt, số buổi nghỉ có phép sẽ được cập nhật vào hạnh kiểm.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check me-1"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/public/index.php?action=gvcn-duyet-don-reject" method="POST">
                <input type="hidden" name="maDonXinPhep" value="<?php echo htmlspecialchars($don['maDonXinPhep']); ?>">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-times-circle me-2"></i>Xác nhận từ chối
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="lyDoTuChoi" rows="4" 
                                  placeholder="Vui lòng nêu rõ lý do từ chối..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-ban me-1"></i>Xác nhận từ chối
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

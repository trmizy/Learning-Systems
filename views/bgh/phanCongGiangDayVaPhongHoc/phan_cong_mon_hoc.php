<?php
/**
 * View: Phân công giảng dạy (GV bộ môn)
 * Path: views/bgh/phanCongGiangDayVaPhongHoc/phan_cong_mon_hoc.php
 */
$pageTitle = 'Phân công giảng dạy - ' . htmlspecialchars($lopInfo['tenLop']);
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .assignment-container {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card-assignment {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
        margin-bottom: 1.5rem;
    }
    
    .card-assignment:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .table-assignment {
        margin-bottom: 0;
    }
    
    .table-assignment thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-assignment thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
    }
    
    .table-assignment tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-assignment tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }
    
    .badge-status {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .badge-assigned {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
    }
    
    .badge-unassigned {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }
    
    .btn-action {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        border: none;
        padding: 1.5rem;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-select, .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.6rem 1rem;
        transition: all 0.2s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
</style>

<div class="assignment-container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                Phân công giảng dạy - GV bộ môn
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-info-circle me-1"></i>
                Lớp: <strong><?php echo htmlspecialchars($lopInfo['tenLop']); ?></strong> | 
                Năm học: <strong><?php echo htmlspecialchars($namHoc); ?></strong>
            </p>
        </div>
        <a href="/public/index.php?action=bgh-phan-cong" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Danh sách môn học -->
    <div class="card card-assignment">
        <div class="card-body">
            <!-- Header with Semester Filter -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-list-check text-success me-2"></i>
                    Danh sách môn học
                </h5>
                
                <!-- Semester Filter -->
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="action" value="bgh-phan-cong-mon-hoc">
                    <input type="hidden" name="maLop" value="<?php echo htmlspecialchars($maLop); ?>">
                    <label class="mb-0 text-muted">
                        <i class="fa-solid fa-calendar me-1"></i>
                        Học kỳ:
                    </label>
                    <select name="hocKy" class="form-select form-select-sm" style="width: auto; min-width: 100px;" onchange="this.form.submit()">
                        <option value="1" <?php echo $hocKy == '1' ? 'selected' : ''; ?>>Học kỳ I</option>
                        <option value="2" <?php echo $hocKy == '2' ? 'selected' : ''; ?>>Học kỳ II</option>
                    </select>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table table-assignment table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Môn học</th>
                            <th>Mã môn</th>
                            <th>Số tiết/tuần</th>
                            <th>Giáo viên phụ trách</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monHocsWithAssignment as $mon): ?>
                        <tr>
                            <td>
                                <strong><i class="fa-solid fa-book me-2 text-primary"></i><?php echo htmlspecialchars($mon['tenMon']); ?></strong>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo htmlspecialchars($mon['maMonHoc']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    <?php echo $mon['soTietTuan']; ?> tiết
                                </span>
                            </td>
                            <td>
                                <?php if ($mon['daPhanCong']): ?>
                                    <div class="info-badge">
                                        <i class="fa-solid fa-user-tie text-primary"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($mon['tenGV']); ?></strong>
                                            <br>
                                            <?php if ($mon['emailGV']): ?>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($mon['emailGV']); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fa-solid fa-user-slash me-1"></i>
                                        Chưa phân công
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($mon['daPhanCong']): ?>
                                    <span class="badge badge-assigned">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã phân công
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">
                                        <i class="fa-solid fa-exclamation-circle me-1"></i>Chưa gán
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($mon['daPhanCong']): ?>
                                <div class="btn-group" role="group">
                                    <a href="/public/index.php?action=bgh-phan-cong-mon-hoc&maLop=<?php echo urlencode($maLop); ?>&hocKy=<?php echo urlencode($hocKy); ?>&action_modal=edit&maMonHoc=<?php echo urlencode($mon['maMonHoc']); ?>&tenMon=<?php echo urlencode($mon['tenMon']); ?>&maGV=<?php echo urlencode($mon['maGV']); ?>"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Đổi GV">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa phân công môn <?php echo htmlspecialchars($mon['tenMon']); ?>?');">
                                        <input type="hidden" name="action" value="xoa_phan_cong">
                                        <input type="hidden" name="maPhanCong" value="<?php echo $mon['maPhanCong']; ?>">
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger"
                                                title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <a href="/public/index.php?action=bgh-phan-cong-mon-hoc&maLop=<?php echo urlencode($maLop); ?>&hocKy=<?php echo urlencode($hocKy); ?>&action_modal=add&maMonHoc=<?php echo urlencode($mon['maMonHoc']); ?>&tenMon=<?php echo urlencode($mon['tenMon']); ?>"
                                   class="btn btn-sm btn-primary"
                                   title="Phân công GV">
                                    <i class="fa-solid fa-user-plus me-1"></i>Phân GV
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($monHocsWithAssignment)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                Không có môn học nào cho học kỳ này
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Phân công giáo viên -->
<?php if (isset($_GET['action_modal']) && ($_GET['action_modal'] === 'add' || $_GET['action_modal'] === 'edit')): 
    $maMonHocModal = $_GET['maMonHoc'] ?? '';
    $tenMonModal = $_GET['tenMon'] ?? '';
    $maGVModal = $_GET['maGV'] ?? '';
    
    // Tìm danh sách GV cho môn này
    $danhSachGVModal = [];
    foreach ($monHocsWithAssignment as $mon) {
        if ($mon['maMonHoc'] === $maMonHocModal) {
            $danhSachGVModal = $mon['danhSachGV'];
            break;
        }
    }
?>
<div class="modal-backdrop fade show" style="z-index: 1040;"></div>
<div class="modal fade show" id="modalPhanCongGV" tabindex="-1" style="display:block; z-index: 1050;" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Phân công giáo viên
                </h5>
                <a href="/public/index.php?action=bgh-phan-cong-mon-hoc&maLop=<?php echo urlencode($maLop); ?>&hocKy=<?php echo urlencode($hocKy); ?>" class="btn-close"></a>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="them_phan_cong">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Môn học</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($tenMonModal); ?>" readonly>
                        <input type="hidden" name="maMonHoc" value="<?php echo htmlspecialchars($maMonHocModal); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn giáo viên</label>
                        <select name="maGV" class="form-select" required <?php echo empty($danhSachGVModal) ? 'disabled' : ''; ?>>
                            <option value="">-- Chọn giáo viên --</option>
                            <?php if (empty($danhSachGVModal)): ?>
                                <option value="" disabled>⚠️ Không có GV phù hợp</option>
                            <?php else: ?>
                                <?php foreach ($danhSachGVModal as $gv): ?>
                                    <option value="<?php echo $gv['maGV']; ?>" <?php echo ($maGVModal === $gv['maGV']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($gv['hoTen']); ?> - <?php echo htmlspecialchars($gv['monHocPhuTrach'] ?? 'N/A'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Danh sách giáo viên phụ trách môn này</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="/public/index.php?action=bgh-phan-cong-mon-hoc&maLop=<?php echo urlencode($maLop); ?>&hocKy=<?php echo urlencode($hocKy); ?>" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

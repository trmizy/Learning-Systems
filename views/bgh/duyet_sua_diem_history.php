<?php
$pageTitle = 'Lịch sử duyệt sửa điểm - BGH';
require_once __DIR__ . '/../layouts/header.php';

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<style>
    .history-badge-approved {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .history-badge-rejected {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .filter-btn {
        border-radius: 20px;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }
</style>

<div class="container py-4">
    <a href="index.php?action=bgh-duyet-sua-diem" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách chờ duyệt
    </a>

    <h2 class="mb-4 fw-bold">
        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
        Lịch sử duyệt yêu cầu sửa điểm
    </h2>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Buttons -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=ALL" 
                   class="btn filter-btn <?= ($trangThai === 'ALL') ? 'active' : 'btn-outline-secondary' ?>">
                    <i class="fa-solid fa-list me-1"></i>Tất cả (<?= $tongSo ?>)
                </a>
                <a href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=DA_DUYET" 
                   class="btn filter-btn <?= ($trangThai === 'DA_DUYET') ? 'active' : 'btn-outline-success' ?>">
                    <i class="fa-solid fa-check me-1"></i>Đã duyệt
                </a>
                <a href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=TU_CHOI" 
                   class="btn filter-btn <?= ($trangThai === 'TU_CHOI') ? 'active' : 'btn-outline-danger' ?>">
                    <i class="fa-solid fa-times me-1"></i>Đã từ chối
                </a>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fa-solid fa-history me-2"></i>
                Danh sách lịch sử (<?= count($lichSuYeuCau) ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($lichSuYeuCau)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
                    <p>Chưa có lịch sử nào</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Thời gian yêu cầu</th>
                                <th>Học sinh</th>
                                <th>Lớp</th>
                                <th>Môn học</th>
                                <th>Loại điểm</th>
                                <th>Điểm cũ → Mới</th>
                                <th>Trạng thái</th>
                                <th>Người duyệt</th>
                                <th>Thời gian duyệt</th>
                                <th class="text-center">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lichSuYeuCau as $yc): 
                                // Map tên loại điểm
                                $tenLoai = '';
                                if($yc['loaiDiem'] == 'diemThuongXuyen') $tenLoai = 'TX';
                                elseif($yc['loaiDiem'] == 'diemGiuaKy') $tenLoai = 'GK';
                                elseif($yc['loaiDiem'] == 'diemCuoiKy') $tenLoai = 'CK';
                                
                                $isDuyet = ($yc['trangThai'] === 'DA_DUYET');
                            ?>
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($yc['ngayYeuCau'])) ?>
                                    </small>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($yc['tenHocSinh']) ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($yc['tenLop']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($yc['tenMon']) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= $tenLoai ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= $yc['diemCu'] ?></span>
                                    <i class="fa-solid fa-arrow-right mx-1"></i>
                                    <span class="fw-bold <?= $isDuyet ? 'text-success' : 'text-danger' ?>">
                                        <?= $yc['diemMoi'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $isDuyet ? 'history-badge-approved' : 'history-badge-rejected' ?>">
                                        <i class="fa-solid fa-<?= $isDuyet ? 'check' : 'times' ?> me-1"></i>
                                        <?= $isDuyet ? 'Đã duyệt' : 'Đã từ chối' ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($yc['tenBGH'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $yc['ngayDuyet'] ? date('d/m/Y H:i', strtotime($yc['ngayDuyet'])) : 'N/A' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= $yc['maYeuCau'] ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal chi tiết -->
                            <div class="modal fade" id="detailModal<?= $yc['maYeuCau'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header <?= $isDuyet ? 'bg-success' : 'bg-danger' ?> text-white">
                                            <h5 class="modal-title">
                                                <i class="fa-solid fa-info-circle me-2"></i>
                                                Chi tiết yêu cầu - Mã: <?= $yc['maYeuCau'] ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Học sinh:</strong> <?= htmlspecialchars($yc['tenHocSinh']) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Lớp:</strong> <?= htmlspecialchars($yc['tenLop']) ?>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Môn học:</strong> <?= htmlspecialchars($yc['tenMon']) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Loại điểm:</strong> <?= $tenLoai ?>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Điểm cũ:</strong> <span class="text-danger"><?= $yc['diemCu'] ?></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Điểm mới:</strong> <span class="text-primary"><?= $yc['diemMoi'] ?></span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Lý do giáo viên:</strong>
                                                <div class="alert alert-warning mt-2">
                                                    <?= htmlspecialchars($yc['lyDo']) ?>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Giáo viên yêu cầu:</strong> <?= htmlspecialchars($yc['tenGiaoVien']) ?>
                                            </div>
                                            <hr>
                                            <div class="mb-2">
                                                <strong>Người duyệt:</strong> <?= htmlspecialchars($yc['tenBGH'] ?? 'N/A') ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Thời gian duyệt:</strong> 
                                                <?= $yc['ngayDuyet'] ? date('d/m/Y H:i:s', strtotime($yc['ngayDuyet'])) : 'N/A' ?>
                                            </div>
                                            <?php if (!empty($yc['ghiChuBGH'])): ?>
                                            <div class="mb-2">
                                                <strong>Ghi chú BGH:</strong>
                                                <div class="alert alert-info mt-2">
                                                    <?= htmlspecialchars($yc['ghiChuBGH']) ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($tongTrang > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=<?= $trangThai ?>&page=<?= $page - 1 ?>">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php for($i = 1; $i <= $tongTrang; $i++): ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=<?= $trangThai ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $tongTrang) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=bgh-duyet-sua-diem-lich-su&trangThai=<?= $trangThai ?>&page=<?= $page + 1 ?>">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

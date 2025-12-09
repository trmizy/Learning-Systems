<?php
$pageTitle = 'Duyệt yêu cầu sửa điểm - BGH';
require_once __DIR__ . '/../layouts/header.php';

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Dashboard
    </a>

    <h2 class="mb-4 fw-bold">
        <i class="fa-solid fa-clipboard-check text-primary me-2"></i>
        Duyệt yêu cầu sửa điểm
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

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fa-solid fa-list me-2"></i>
                Danh sách yêu cầu chờ duyệt (<?= count($danhSachYeuCau) ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($danhSachYeuCau)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                    <p>Không có yêu cầu nào chờ duyệt</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Thời gian</th>
                                <th>Học sinh</th>
                                <th>Lớp</th>
                                <th>Môn học</th>
                                <th>Loại điểm</th>
                                <th>Điểm cũ</th>
                                <th>Điểm mới</th>
                                <th>Giáo viên</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachYeuCau as $yc): 
                                // Map tên loại điểm
                                $tenLoai = '';
                                if($yc['loaiDiem'] == 'diemThuongXuyen') $tenLoai = 'TX';
                                elseif($yc['loaiDiem'] == 'diemGiuaKy') $tenLoai = 'GK';
                                elseif($yc['loaiDiem'] == 'diemCuoiKy') $tenLoai = 'CK';
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
                                <td class="text-danger fw-bold"><?= $yc['diemCu'] ?></td>
                                <td class="text-primary fw-bold"><?= $yc['diemMoi'] ?></td>
                                <td>
                                    <small><?= htmlspecialchars($yc['tenGiaoVien']) ?></small>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?action=bgh-duyet-sua-diem-chi-tiet&id=<?= $yc['maYeuCau'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                    </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

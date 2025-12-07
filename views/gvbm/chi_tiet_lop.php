<?php
$pageTitle = 'Danh sách học sinh - ' . ($thongTinLop['tenLop'] ?? 'Lớp');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fa-solid fa-users text-primary me-2"></i>
                <?php echo htmlspecialchars($thongTinLop['tenLop'] ?? 'Lớp'); ?>
            </h2>
            <p class="text-muted mb-0">
                Khối <?php echo htmlspecialchars($thongTinLop['khoi'] ?? ''); ?> - 
                Sĩ số: <?php echo count($danhSachHocSinh); ?> học sinh
            </p>
        </div>
        <a href="/public/index.php?action=lop_giang_day" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Danh sách học sinh -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($danhSachHocSinh)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-user-slash fa-3x mb-3 d-block"></i>
                    <p>Lớp chưa có học sinh</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Mã HS</th>
                                <th>Họ và tên</th>
                                <th>Giới tính</th>
                                <th>Ngày sinh</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($danhSachHocSinh as $hs): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><code><?php echo htmlspecialchars($hs['maHS']); ?></code></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($hs['hoTen']); ?></td>
                                <td>
                                    <i class="fa-solid fa-<?php echo $hs['gioiTinh'] == 'Nam' ? 'mars text-primary' : 'venus text-danger'; ?> me-1"></i>
                                    <?php echo htmlspecialchars($hs['gioiTinh']); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($hs['ngaySinh'])); ?></td>
                                <td><?php echo htmlspecialchars($hs['sdt']); ?></td>
                                <td><?php echo htmlspecialchars($hs['email']); ?></td>
                                <td class="text-center">
                                    <a href="/public/index.php?action=chi_tiet_hoc_sinh&maHS=<?php echo urlencode($hs['maHS']); ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
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

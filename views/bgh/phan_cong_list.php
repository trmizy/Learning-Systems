<?php
$pageTitle = 'Danh sách phân công giảng dạy';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-chalkboard-user me-2"></i>
                        Danh sách phân công giảng dạy
                    </h5>
                    <a href="/public/index.php?action=bgh-phan-cong-mon-hoc" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i>Phân công mới
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($danhSachPhanCong)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                            <p>Chưa có phân công nào</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã PC</th>
                                        <th>Lớp</th>
                                        <th>Môn học</th>
                                        <th>Giáo viên</th>
                                        <th>Năm học</th>
                                        <th>Học kỳ</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachPhanCong as $pc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pc['maPhanCong']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($pc['tenLop']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pc['tenMon']); ?></td>
                                        <td><?php echo htmlspecialchars($pc['tenGiaoVien'] ?? 'Chưa phân công'); ?></td>
                                        <td><?php echo htmlspecialchars($pc['namHoc']); ?></td>
                                        <td><?php echo htmlspecialchars($pc['ghiChu']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
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
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

<?php
$pageTitle = 'Kết quả xét tuyển';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-chart-bar me-2 text-success"></i>Kết quả xét tuyển</h2>
                <div>
                    <a href="/public/index.php?action=nhanvienso-xet-tuyen" class="btn btn-primary me-2">
                        <i class="fa-solid fa-repeat me-1"></i>Chạy lại
                    </a>
                    <a href="/public/index.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Dashboard
                    </a>
                </div>
            </div>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    <?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tổng quan -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?php echo number_format($thongKeTongQuat['tong_thi_sinh']); ?></h3>
                            <p class="mb-0">Tổng thí sinh</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?php echo number_format($thongKeTongQuat['tong_trung_tuyen']); ?></h3>
                            <p class="mb-0">Trúng tuyển</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3><?php echo number_format($thongKeTongQuat['tong_truot']); ?></h3>
                            <p class="mb-0">Trượt</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3><?php echo $thongKeTongQuat['ty_le_trung_tuyen']; ?>%</h3>
                            <p class="mb-0">Tỷ lệ đỗ</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kết quả theo trường -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-school me-2"></i>Kết quả theo trường</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã trường</th>
                                    <th>Tên trường</th>
                                    <th>Điểm chuẩn</th>
                                    <th>Số trúng tuyển</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ketQuaTheoTruong as $truong): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($truong['maTruong']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($truong['tenTruong']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($truong['soDiem'])): ?>
                                            <span class="badge bg-primary">
                                                <?php echo number_format($truong['soDiem'], 1); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Chưa công bố</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-success fs-5"><?php echo number_format($truong['soTrungTuyen']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kết quả chi tiết thí sinh -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i>Kết quả chi tiết (Top 100)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã TS</th>
                                    <th>Họ tên</th>
                                    <th>Điểm</th>
                                    <th>NV</th>
                                    <th>Trường</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ketQuaChiTiet as $kq): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kq['maThiSinh']); ?></td>
                                    <td><?php echo htmlspecialchars($kq['hoTen']); ?></td>
                                    <td><strong><?php echo number_format($kq['diem'], 2); ?></strong></td>
                                    <td>NV<?php echo $kq['thuTuUuTien']; ?></td>
                                    <td><?php echo htmlspecialchars($kq['tenTruong']); ?></td>
                                    <td>
                                        <?php if ($kq['trangThai'] == 'DAU'): ?>
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Đậu</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Trượt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<?php
$pageTitle = 'Duyệt điểm chuẩn tuyển sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <h2 class="fw-bold mb-4">
        <i class="fa-solid fa-chart-line text-primary me-2"></i>
        Duyệt điểm chuẩn tuyển sinh
    </h2>

    <!-- Bộ lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/public/index.php" class="row g-3">
                <input type="hidden" name="action" value="nhanvienso-duyet-diem-chuan">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Năm tuyển sinh</label>
                    <select name="namTuyenSinh" class="form-select">
                        <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($namTuyenSinh ?? '') == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê -->
    <?php if ($thongKe): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['tongSo']; ?></h3>
                    <p class="mb-0">Tổng số trường</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['chuaCongBo']; ?></h3>
                    <p class="mb-0">Chờ duyệt</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['daCongBo']; ?></h3>
                    <p class="mb-0">Đã công bố</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['tuChoi']; ?></h3>
                    <p class="mb-0">Từ chối</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Danh sách điểm chuẩn -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>STT</th>
                            <th>Trường</th>
                            <th class="text-center">Điểm chuẩn</th>
                            <th class="text-center">Số NV</th>
                            <th class="text-center">Ngày tạo</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachDiemChuan)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Không có dữ liệu
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($danhSachDiemChuan as $index => $dc): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($dc['tenTruong']); ?></strong></td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-5"><?php echo number_format($dc['soDiem'], 2); ?></span>
                                </td>
                                <td class="text-center"><?php echo $dc['soNguyenVong']; ?></td>
                                <td class="text-center"><?php echo date('d/m/Y', strtotime($dc['ngayTao'])); ?></td>
                                <td class="text-center">
                                    <?php if ($dc['trangThai'] == 'DA_CONG_BO'): ?>
                                        <span class="badge bg-success">Đã công bố</span>
                                    <?php elseif ($dc['trangThai'] == 'TU_CHOI'): ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($dc['trangThai'] == 'CHUA_CONG_BO'): ?>
                                        <form method="POST" action="/public/index.php?action=nhanvienso-duyet-diem-chuan-post" class="d-inline"
                                              onsubmit="return confirm('Công bố điểm chuẩn và xét tuyển tự động?');">
                                            <input type="hidden" name="maDiemChuan" value="<?php echo $dc['maDiemChuan']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success me-2">
                                                <i class="fa-solid fa-check me-1"></i>Duyệt
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#tuChoiModal<?php echo $dc['maDiemChuan']; ?>">
                                            <i class="fa-solid fa-times me-1"></i>Từ chối
                                        </button>

                                        <!-- Modal Từ chối -->
                                        <div class="modal fade" id="tuChoiModal<?php echo $dc['maDiemChuan']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="/public/index.php?action=nhanvienso-tu-choi-diem-chuan">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Từ chối điểm chuẩn</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="maDiemChuan" value="<?php echo $dc['maDiemChuan']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Lý do từ chối</label>
                                                                <textarea name="lyDoTuChoi" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

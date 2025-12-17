<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['admin', 'bgh']);

$pageTitle = 'Danh sách học sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col">
            <h3><i class="fa-solid fa-users me-2"></i>Danh sách học sinh</h3>
        </div>
    </div>

    <!-- Filter: Chọn khối và lớp -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="action" value="admin-danh-sach-hoc-sinh" />
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Chọn khối</label>
                    <select name="khoi" class="form-select" onchange="this.form.submit()">
                        <option value="10" <?php echo $khoiChon == '10' ? 'selected' : ''; ?>>Khối 10</option>
                        <option value="11" <?php echo $khoiChon == '11' ? 'selected' : ''; ?>>Khối 11</option>
                        <option value="12" <?php echo $khoiChon == '12' ? 'selected' : ''; ?>>Khối 12</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Chọn lớp</label>
                    <select name="lop" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($danhSachLop as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['maLop']); ?>" 
                                    <?php echo $lopChon == $lop['maLop'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['tenLop']); ?> 
                                (<?php echo $lop['siSo']; ?> HS)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-search me-1"></i>Tìm
                    </button>
                </div>

                <?php if ($lopChon): ?>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="/public/index.php?action=admin-xuat-excel&lop=<?php echo urlencode($lopChon); ?>" 
                       class="btn btn-success w-100">
                        <i class="fa-solid fa-file-excel me-1"></i>Xuất Excel
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($lopChon && !empty($danhSachHocSinh)): ?>
        <?php 
        // Lấy thống kê
        $thongKe = $this->model->getThongKeTheoLop($lopChon);
        ?>
        
        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h4 class="text-primary"><?php echo $thongKe['tongSo'] ?? 0; ?></h4>
                        <p class="mb-0">Tổng số</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h4 class="text-info"><?php echo $thongKe['soNam'] ?? 0; ?></h4>
                        <p class="mb-0">Nam</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <h4 class="text-danger"><?php echo $thongKe['soNu'] ?? 0; ?></h4>
                        <p class="mb-0">Nữ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h4 class="text-success"><?php echo $thongKe['dangHoc'] ?? 0; ?></h4>
                        <p class="mb-0">Đang học</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>STT</th>
                                <th>Mã HS</th>
                                <th>Họ và tên</th>
                                <th>Ngày sinh</th>
                                <th>Giới tính</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($danhSachHocSinh as $hs): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo htmlspecialchars($hs['maHS']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($hs['hoTen']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($hs['ngaySinh'])); ?></td>
                                <td>
                                    <?php if ($hs['gioiTinh'] == 'Nam'): ?>
                                        <span class="badge bg-info">Nam</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nữ</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($hs['email']); ?></td>
                                <td><?php echo htmlspecialchars($hs['sdt']); ?></td>
                                <td>
                                    <?php if ($hs['trangThai'] == 'DANGHOC'): ?>
                                        <span class="badge bg-success">Đang học</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($hs['trangThai']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/public/index.php?action=admin-xem-hs&maHS=<?php echo urlencode($hs['maHS']); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="/public/index.php?action=admin-sua-hs&maHS=<?php echo urlencode($hs['maHS']); ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($lopChon): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info me-2"></i>Không có học sinh nào trong lớp này.
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fa-solid fa-hand-point-up me-2"></i>Vui lòng chọn khối và lớp để xem danh sách học sinh.
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

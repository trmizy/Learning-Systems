<?php
$pageTitle = 'Xét tuyển học sinh lớp 10';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .student-row.pass {
        background: linear-gradient(90deg, rgba(17, 153, 142, 0.1) 0%, transparent 100%);
        border-left: 4px solid #11998e;
    }
    
    .student-row.fail {
        background: linear-gradient(90deg, rgba(245, 87, 108, 0.1) 0%, transparent 100%);
        border-left: 4px solid #f5576c;
    }
    
    .score-badge {
        font-size: 1.2rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 12px;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header & Thống kê -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="fa-solid fa-user-graduate text-primary me-2"></i>
                Xét tuyển học sinh lớp 10
            </h2>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/public/index.php" class="row g-3">
                <input type="hidden" name="action" value="nhanvienso-xet-tuyen">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trường</label>
                    <select name="maTruong" class="form-select">
                        <option value="TR001" <?php echo ($maTruong ?? '') == 'TR001' ? 'selected' : ''; ?>>THPT Lê Quý Đôn</option>
                    </select>
                </div>
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
                    <h3><?php echo $thongKe['tongSoThiSinh']; ?></h3>
                    <p class="mb-0">Tổng thí sinh</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['soTrungTuyen']; ?></h3>
                    <p class="mb-0">Đã trúng tuyển</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['soChuaXet']; ?></h3>
                    <p class="mb-0">Chưa xét</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3><?php echo $thongKe['soTruot']; ?></h3>
                    <p class="mb-0">Trượt</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Điểm chuẩn -->
    <?php if ($diemChuan): ?>
    <div class="alert alert-info mb-4">
        <h5 class="fw-bold mb-0">
            <i class="fa-solid fa-chart-line me-2"></i>
            Điểm chuẩn năm <?php echo $namTuyenSinh; ?>: 
            <span class="badge bg-primary fs-5"><?php echo number_format($diemChuan, 2); ?> điểm</span>
        </h5>
    </div>
    <?php else: ?>
    <div class="alert alert-warning mb-4">
        ⚠️ Chưa có điểm chuẩn cho năm <?php echo $namTuyenSinh; ?>
    </div>
    <?php endif; ?>

    <!-- Nút xét tuyển tự động -->
    <?php if ($thongKe && $thongKe['soChuaXet'] > 0 && $diemChuan): ?>
    <form method="POST" action="/public/index.php?action=nhanvienso-xet-tuyen-auto" class="mb-4"
          onsubmit="return confirm('Xét tuyển tự động cho <?php echo $thongKe['soChuaXet']; ?> thí sinh?');">
        <input type="hidden" name="maTruong" value="<?php echo htmlspecialchars($maTruong); ?>">
        <input type="hidden" name="namTuyenSinh" value="<?php echo htmlspecialchars($namTuyenSinh); ?>">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>
            Xét tuyển tự động (<?php echo $thongKe['soChuaXet']; ?> thí sinh)
        </button>
    </form>
    <?php endif; ?>

    <!-- Danh sách thí sinh -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>STT</th>
                            <th>Mã TS</th>
                            <th>Họ tên</th>
                            <th>Ngày sinh</th>
                            <th class="text-center">Văn</th>
                            <th class="text-center">Toán</th>
                            <th class="text-center">Anh</th>
                            <th class="text-center">Tổng điểm</th>
                            <th class="text-center">NV</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachThiSinh)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                Không có thí sinh nào
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($danhSachThiSinh as $index => $ts): 
                                $datDiemChuan = $diemChuan && $ts['tongDiem'] >= $diemChuan;
                                $rowClass = $datDiemChuan ? 'pass' : 'fail';
                            ?>
                            <tr class="student-row <?php echo $rowClass; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($ts['maThiSinh']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ts['hoTen']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($ts['ngaySinh'])); ?></td>
                                <td class="text-center"><?php echo number_format($ts['diemVan'], 2); ?></td>
                                <td class="text-center"><?php echo number_format($ts['diemToan'], 2); ?></td>
                                <td class="text-center"><?php echo number_format($ts['diemAnh'], 2); ?></td>
                                <td class="text-center">
                                    <span class="score-badge <?php echo $datDiemChuan ? 'bg-success' : 'bg-danger'; ?> text-white">
                                        <?php echo number_format($ts['tongDiem'], 2); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">NV<?php echo $ts['thuTuUuTien']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($ts['trangThai'] == 'TRUNG_TUYEN'): ?>
                                        <span class="badge bg-success">Trúng tuyển</span>
                                    <?php elseif ($ts['trangThai'] == 'TRUOT'): ?>
                                        <span class="badge bg-danger">Trượt</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chờ xét</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($ts['trangThai'] == 'CHO_DUYET' && $datDiemChuan): ?>
                                    <form method="POST" action="/public/index.php?action=nhanvienso-xet-tuyen-one" class="d-inline"
                                          onsubmit="return confirm('Xét tuyển thí sinh này?');">
                                        <input type="hidden" name="maThiSinh" value="<?php echo $ts['maThiSinh']; ?>">
                                        <input type="hidden" name="maLop" value="10A1">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-check me-1"></i>Xét tuyển
                                        </button>
                                    </form>
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

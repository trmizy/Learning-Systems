<?php
$pageTitle = 'Danh sách thí sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fa-solid fa-list text-primary me-2"></i>
            Danh sách thí sinh tuyển sinh
        </h2>
        <a href="/public/index.php?action=nhanvienso-tuyen-sinh-upload" class="btn btn-primary">
            <i class="fa-solid fa-upload me-2"></i>Upload điểm
        </a>
    </div>

    <!-- Thống kê -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Tổng thí sinh</h6>
                    <h3 class="text-primary"><?php echo number_format($thongKe['tongThiSinh'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Điểm TB</h6>
                    <h3 class="text-success"><?php echo number_format($thongKe['diemTrungBinh'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Điểm cao nhất</h6>
                    <h3 class="text-warning"><?php echo number_format($thongKe['diemCaoNhat'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Điểm thấp nhất</h6>
                    <h3 class="text-danger"><?php echo number_format($thongKe['diemThapNhat'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="nhanvienso-tuyen-sinh-list">
                <div class="col-md-4">
                    <select name="namTuyenSinh" class="form-select">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $namTuyenSinh ? 'selected' : ''; ?>>
                                Năm <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm theo SBD, họ tên..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-search me-1"></i>Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>STT</th>
                            <th>CCCD</th>
                            <th>Họ và tên</th>
                            <th>Ngày sinh</th>
                            <th>Số điện thoại</th>
                            <th>Nơi sinh</th>
                            <th>Tổng điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachThiSinh)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    Chưa có dữ liệu thí sinh
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; foreach ($danhSachThiSinh as $ts): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($ts['soCCCD'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ts['hoTen'] ?? 'N/A'); ?></td>
                                <td><?php echo $ts['ngaySinh'] ? date('d/m/Y', strtotime($ts['ngaySinh'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($ts['soDienThoai'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ts['noiSinh'] ?? 'N/A'); ?></td>
                                <td class="fw-bold text-primary"><?php echo number_format($ts['diem'] ?? 0, 2); ?></td>
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

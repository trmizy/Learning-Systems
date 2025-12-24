<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh']);

$pageTitle = 'Chi Tiết Tổ Hợp Môn';
require_once __DIR__ . '/../../layouts/header.php';

// $chiTiet, $danhSachMon, $danhSachHocSinh are expected from controller
$chiTiet = $chiTiet ?? [];
$danhSachMon = $danhSachMon ?? [];
$danhSachHocSinh = $danhSachHocSinh ?? [];
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<div class="container mt-4">
    <!-- Breadcrumb và Back button -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/models/bgh/chonToHopMon/quanLyChonList.php">Danh Sách Tổ Hợp Môn</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($chiTiet['tenToHop'] ?? 'Chi Tiết'); ?></li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Thông tin tổng quát -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-info-circle me-2"></i>Thông Tin Tổng Quát</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Mã Tổ Hợp:</label>
                            <p class="form-control-plaintext"><code><?php echo htmlspecialchars($chiTiet['maToHop'] ?? ''); ?></code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tên Tổ Hợp:</label>
                            <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($chiTiet['tenToHop'] ?? ''); ?></strong></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Số Lượng Lớp:</label>
                            <p class="form-control-plaintext"><?php echo $chiTiet['soLuongLop'] ?? 0; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Trạng Thái:</label>
                            <p class="form-control-plaintext">
                                <?php 
                                $status = $chiTiet['trangThai'] ?? 'ACTIVE';
                                if ($status === 'APPROVED') {
                                    echo '<span class="badge bg-success">Đã Chọn</span>';
                                } else if ($status === 'REJECTED') {
                                    echo '<span class="badge bg-danger">Từ Chối</span>';
                                } else if ($status === 'PENDING' || $status === 'ACTIVE') {
                                    echo '<span class="badge bg-warning">Chờ Duyệt</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Ngày Tạo:</label>
                            <p class="form-control-plaintext"><?php echo date('d/m/Y H:i', strtotime($chiTiet['ngayTao'] ?? 'now')); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Người Tạo:</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($chiTiet['nguoiTao'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách môn học -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-book me-2"></i>Danh Sách Môn Học</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($danhSachMon)): ?>
                        <p class="text-muted text-center py-3"><i class="fa-solid fa-inbox me-2"></i>Không có môn học nào</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                            <th><i class="fa-solid fa-hashtag me-1"></i>STT</th>
                                            <th><i class="fa-solid fa-code me-1"></i>Mã Môn</th>
                                            <th><i class="fa-solid fa-graduation-cap me-1"></i>Tên Môn</th>
                                            <th><i class="fa-solid fa-layer-group me-1"></i>Số Tiết/tuần</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($danhSachMon as $mon): ?>
                                        <tr>
                                            <td><?php echo $stt++; ?></td>
                                            <td><code><?php echo htmlspecialchars($mon['maMon'] ?? ''); ?></code></td>
                                            <td><?php echo htmlspecialchars($mon['tenMon'] ?? ''); ?></td>
                                            <td><?php echo $mon['soTiet'] ?? 0; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danh sách học sinh đăng ký -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i>Danh Sách Học Sinh Đăng Ký 
                        <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachHocSinh); ?> HS</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($danhSachHocSinh)): ?>
                        <p class="text-muted text-center py-3"><i class="fa-solid fa-inbox me-2"></i>Chưa có học sinh nào đăng ký</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fa-solid fa-hashtag me-1"></i>STT</th>
                                        <th><i class="fa-solid fa-code me-1"></i>Mã HS</th>
                                        <th><i class="fa-solid fa-user me-1"></i>Tên Học Sinh</th>
                                        <th><i class="fa-solid fa-chalkboard me-1"></i>Lớp</th>
                                        <th><i class="fa-solid fa-calendar me-1"></i>Ngày Đăng Ký</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($danhSachHocSinh as $hs): ?>
                                        <tr>
                                            <td><?php echo $stt++; ?></td>
                                            <td><code><?php echo htmlspecialchars($hs['maHocSinh'] ?? ''); ?></code></td>
                                            <td><?php echo htmlspecialchars($hs['tenHocSinh'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($hs['lop'] ?? ''); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($hs['ngayTao'] ?? 'now')); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar: Phê duyệt / Từ chối -->
        <div class="col-md-4">
            <?php 
            $status = $chiTiet['trangThai'] ?? 'ACTIVE';
            $coThePheDuyet = ($status === 'PENDING' || $status === 'ACTIVE');
            ?>

            <?php if ($coThePheDuyet): ?>
                <!-- Form Phê Duyệt -->
                <div class="card mb-3 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-thumbs-up me-2"></i>Phê Duyệt Tổ Hợp</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/controllers/bgh/chonToHopMonController.php">
                            <input type="hidden" name="action" value="pheDuyet">
                            <input type="hidden" name="maToHop" value="<?php echo htmlspecialchars($chiTiet['maToHop'] ?? ''); ?>">
                            
                            <div class="mb-3">
                                <label for="lyDo" class="form-label">Ghi Chú (Tùy Chọn):</label>
                                <textarea class="form-control" id="lyDo" name="lyDo" rows="3" placeholder="Nhập ghi chú nếu cần..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa-solid fa-check me-2"></i>Phê Duyệt
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Form Từ Chối -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fa-solid fa-times-circle me-2"></i>Từ Chối Tổ Hợp</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/controllers/bgh/chonToHopMonController.php">
                            <input type="hidden" name="action" value="tuChoi">
                            <input type="hidden" name="maToHop" value="<?php echo htmlspecialchars($chiTiet['maToHop'] ?? ''); ?>">
                            
                            <div class="mb-3">
                                <label for="lyDoTuChoi" class="form-label">Lý Do Từ Chối: <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="lyDoTuChoi" name="lyDo" rows="3" placeholder="Nhập lý do từ chối..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa-solid fa-ban me-2"></i>Từ Chối
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Đã xử lý -->
                <div class="card">
                    <div class="card-header <?php echo $status === 'APPROVED' ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <h6 class="mb-0">
                            <i class="fa-solid <?php echo $status === 'APPROVED' ? 'fa-check-circle' : 'fa-times-circle'; ?> me-2"></i>
                            <?php echo $status === 'APPROVED' ? 'Đã Phê Duyệt' : 'Đã Từ Chối'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Ngày Xử Lý:</label>
                            <p class="form-control-plaintext"><?php echo date('d/m/Y H:i', strtotime($chiTiet['ngayDuyet'] ?? 'now')); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Người Xử Lý:</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($chiTiet['nguoiDuyet'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Ghi Chú:</label>
                            <p class="form-control-plaintext">
                                <?php echo htmlspecialchars($chiTiet['lyDoDuyet'] ?? 'Không có ghi chú'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="/models/bgh/chonToHopMon/quanLyChonList.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay Lại
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

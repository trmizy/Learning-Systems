<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh']);

$pageTitle = 'Chọn Tổ Hợp Môn';
require_once __DIR__ . '/../../layouts/header.php';

// $danhSachToHop, $thongKe are expected from controller
$danhSachToHop = $danhSachToHop ?? [];
$thongKe = $thongKe ?? [];
$trangThai = $_GET['trangThai'] ?? null;
$success = $_GET['success'] ?? '';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fa-solid fa-check-circle me-2"></i>Chọn Tổ Hợp Môn</h2>
            <p class="text-muted">Duyệt và chọn các tổ hợp môn do phòng giáo vụ tạo để đưa vào chương trình giảng dạy</p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Thống kê theo trạng thái -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Chờ Duyệt</h6>
                    <h3 class="text-warning">
                        <?php 
                        $count = 0;
                        foreach ($thongKe as $stat) {
                            if ($stat['trangThai'] === 'PENDING' || $stat['trangThai'] === 'ACTIVE') {
                                $count = $stat['soLuong'];
                            }
                        }
                        echo $count;
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Đã Chọn</h6>
                    <h3 class="text-success">
                        <?php 
                        $count = 0;
                        foreach ($thongKe as $stat) {
                            if ($stat['trangThai'] === 'APPROVED') {
                                $count = $stat['soLuong'];
                            }
                        }
                        echo $count;
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Từ Chối</h6>
                    <h3 class="text-danger">
                        <?php 
                        $count = 0;
                        foreach ($thongKe as $stat) {
                            if ($stat['trangThai'] === 'REJECTED') {
                                $count = $stat['soLuong'];
                            }
                        }
                        echo $count;
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng Cộng</h6>
                    <h3><?php echo count($danhSachToHop); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Lọc theo trạng thái -->
    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="/modules/chonToHopMon/quanLyChonList.php" class="btn btn-outline-primary<?php echo is_null($trangThai) ? ' active' : ''; ?>">
                <i class="fa-solid fa-list me-1"></i>Tất Cả
            </a>
            <a href="/modules/chonToHopMon/quanLyChonList.php?trangThai=PENDING" class="btn btn-outline-warning<?php echo $trangThai === 'PENDING' ? ' active' : ''; ?>">
                <i class="fa-solid fa-hourglass-half me-1"></i>Chờ Duyệt
            </a>
            <a href="/modules/chonToHopMon/quanLyChonList.php?trangThai=APPROVED" class="btn btn-outline-success<?php echo $trangThai === 'APPROVED' ? ' active' : ''; ?>">
                <i class="fa-solid fa-check me-1"></i>Đã Chọn
            </a>
            <a href="/modules/chonToHopMon/quanLyChonList.php?trangThai=REJECTED" class="btn btn-outline-danger<?php echo $trangThai === 'REJECTED' ? ' active' : ''; ?>">
                <i class="fa-solid fa-times me-1"></i>Từ Chối
            </a>
        </div>
    </div>

    <!-- Bảng danh sách tổ hợp môn -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-light">
                <tr>
                    <th><i class="fa-solid fa-hashtag me-1"></i>STT</th>
                    <th><i class="fa-solid fa-key me-1"></i>Mã Tổ Hợp</th>
                    <th><i class="fa-solid fa-graduation-cap me-1"></i>Tên Tổ Hợp</th>
                    <th><i class="fa-solid fa-book me-1"></i>Các Môn Học</th>
                    <th><i class="fa-solid fa-users me-1"></i>Học Sinh Đăng Ký</th>
                    <th><i class="fa-solid fa-calendar me-1"></i>Ngày Tạo</th>
                    <th><i class="fa-solid fa-flag me-1"></i>Trạng Thái</th>
                    <th><i class="fa-solid fa-cogs me-1"></i>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($danhSachToHop)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox me-2"></i>Không có tổ hợp môn nào
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $stt = 1; foreach ($danhSachToHop as $item): ?>
                        <tr>
                            <td><strong><?php echo $stt++; ?></strong></td>
                            <td><code><?php echo htmlspecialchars($item['maToHop']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($item['tenToHop']); ?></strong></td>
                            <td>
                                <small><?php echo htmlspecialchars($item['tenCacMon'] ?? $item['danhSachMon']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $item['soHocSinhDaDangKy'] ?? 0; ?> HS</span>
                            </td>
                            <td>
                                <small><?php echo date('d/m/Y', strtotime($item['ngayTao'] ?? 'now')); ?></small>
                            </td>
                            <td>
                                <?php 
                                $status = $item['trangThai'] ?? 'ACTIVE';
                                if ($status === 'APPROVED') {
                                    echo '<span class="badge bg-success">Đã Chọn</span>';
                                } else if ($status === 'REJECTED') {
                                    echo '<span class="badge bg-danger">Từ Chối</span>';
                                } else if ($status === 'PENDING') {
                                    echo '<span class="badge bg-warning">Chờ Duyệt</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="/modules/chonToHopMon/quanLyChonDetail.php?maToHop=<?php echo urlencode($item['maToHop']); ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-eye me-1"></i>Chi Tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

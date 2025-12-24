<?php
// Kiểm tra dữ liệu từ controller
if (empty($student)) {
    echo "<p>Học sinh không tồn tại.</p>";
    return;
}

// Thiết lập tiêu đề trang
$pageTitle = 'Hồ sơ học sinh - ' . ($student['hoTen'] ?? 'Chi tiết');

// Include header ĐÚNG 1 LẦN
require __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/public/index.php?action=admin-dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/public/index.php?action=admin-quan-ly-hoc-sinh">Quản lý học sinh</a></li>
                    <li class="breadcrumb-item active">Hồ sơ chi tiết</li>
                </ol>
            </nav>

            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-user-graduate me-2"></i>Hồ sơ học sinh: <?php echo htmlspecialchars($student['hoTen'] ?? ''); ?></h2>
                <div>
                    <a href="/public/index.php?action=admin-sua-hs&maHS=<?php echo urlencode($student['maHS'] ?? ''); ?>" class="btn btn-primary">
                        <i class="fa-solid fa-edit me-1"></i>Chỉnh sửa
                    </a>
                    <a href="/public/index.php?action=admin-quan-ly-hoc-sinh" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>

            <?php if (!empty($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle me-2"></i>Cập nhật thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Thông tin cơ bản -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-info-circle me-2"></i>Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Mã học sinh</th>
                                <td><?php echo htmlspecialchars($student['maHS'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Họ tên</th>
                                <td><?php echo htmlspecialchars($student['hoTen'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Ngày sinh</th>
                                <td><?php echo !empty($student['ngaySinh']) ? date('d/m/Y', strtotime($student['ngaySinh'])) : 'Chưa cập nhật'; ?></td>
                            </tr>
                            <tr>
                                <th>Giới tính</th>
                                <td><?php echo htmlspecialchars($student['gioiTinh'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Số CCCD</th>
                                <td><?php echo htmlspecialchars($student['soCCCD'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Địa chỉ</th>
                                <td><?php echo htmlspecialchars($student['diaChi'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($student['email'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <th>Số điện thoại</th>
                                <td><?php echo htmlspecialchars($student['sdt'] ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kết quả học tập -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-graduation-cap me-2"></i>Kết quả học tập</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted">Xếp loại học lực</h6>
                                <h4 class="text-success"><?php echo htmlspecialchars($student['xepLoaiHocLuc'] ?? 'Chưa xếp loại'); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted">Hạnh kiểm</h6>
                                <h4 class="text-info"><?php echo htmlspecialchars($student['loaiHanhKiem'] ?? 'Chưa đánh giá'); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted">Điểm trung bình</h6>
                                <h4 class="text-primary"><?php echo htmlspecialchars($student['diemTrungBinhMon'] ?? '0.0'); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin phụ huynh -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i>Thông tin phụ huynh / Người giám hộ</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['parents'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Họ tên</th>
                                        <th>Mối quan hệ</th>
                                        <th>Số điện thoại</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student['parents'] as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['hoTen'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($p['moiQuanHe'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($p['soDienThoai'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            <i class="fa-solid fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($student['phuHuynh_info'] ?? 'Chưa có thông tin phụ huynh'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lịch sử hạnh kiểm -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Lịch sử hạnh kiểm</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['hanhkiem_history'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Năm học</th>
                                        <th>Học kỳ</th>
                                        <th>Xếp loại</th>
                                        <th>Nghỉ có phép</th>
                                        <th>Nghỉ không phép</th>
                                        <th>Số lần vi phạm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student['hanhkiem_history'] as $hk): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hk['namHoc'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($hk['hocKy'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo htmlspecialchars($hk['loaiHanhKiem'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($hk['soBuoiNghiCoPhep'] ?? '0'); ?></td>
                                        <td><?php echo htmlspecialchars($hk['soBuoiNghiKhongCoPhep'] ?? '0'); ?></td>
                                        <td><?php echo htmlspecialchars($hk['soLanViPham'] ?? '0'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            <i class="fa-solid fa-info-circle me-2"></i>Chưa có bản ghi hạnh kiểm.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex gap-2 mb-4">
                <a href="/public/index.php?action=admin-sua-hs&maHS=<?php echo urlencode($student['maHS'] ?? ''); ?>" class="btn btn-primary">
                    <i class="fa-solid fa-edit me-1"></i>Chỉnh sửa thông tin
                </a>
                <a href="/public/index.php?action=admin-quan-ly-hoc-sinh" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer ĐÚNG 1 LẦN
require __DIR__ . '/../../layouts/footer.php'; 
?>
<?php
// View: Danh sách quản lý giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php
$pageTitle = 'Quản lý hồ sơ giáo viên - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/quan_ly_phan_cong.css">

<div class="assignment-container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-users text-primary me-2"></i>
                Quản lý hồ sơ giáo viên
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-info-circle me-1"></i>
                Xem, tạo, sửa, xóa hồ sơ giáo viên bộ môn
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/public/index.php?page=bgh-quan-ly-giao-vien&action=create" class="btn btn-success">
                <i class="fa-solid fa-plus me-2"></i>Tạo mới giáo viên
            </a>
            <a href="/views/bgh/dashboard.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($message) && $message): ?>
    <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-<?php echo (isset($messageType) && $messageType === 'success') ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Search Box -->
    <div class="card card-assignment mb-4">
        <div class="card-body">
            <form method="GET" action="/public/index.php" class="row g-3">
                <input type="hidden" name="page" value="bgh-quan-ly-giao-vien">
                <div class="col-md-10">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Tìm kiếm theo mã GV, họ tên, email, SĐT, môn học..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-search me-2"></i>Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách giáo viên -->
    <div class="card card-assignment">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="fa-solid fa-list text-success me-2"></i>
                Danh sách giáo viên
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <span class="badge bg-info">Kết quả tìm kiếm: "<?php echo htmlspecialchars($_GET['search']); ?>"</span>
                <?php endif; ?>
            </h5>

            <div class="table-responsive">
                <table class="table table-assignment table-hover">
                    <thead>
                        <tr>
                            <th>Mã GV</th>
                            <th>Họ và tên</th>
                            <th>Giới tính</th>
                            <th>Ngày sinh</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Môn phụ trách</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        while ($gv = $danhSachGiaoVien->fetch()): 
                            $count++;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($gv['maGV']); ?></strong></td>
                            <td><?php echo htmlspecialchars($gv['hoTen']); ?></td>
                            <td>
                                <?php if ($gv['gioiTinh'] === 'Nam'): ?>
                                    <i class="fa-solid fa-mars text-primary"></i> Nam
                                <?php else: ?>
                                    <i class="fa-solid fa-venus text-danger"></i> Nữ
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($gv['ngaySinh'])); ?></td>
                            <td><?php echo htmlspecialchars($gv['soDienThoai']); ?></td>
                            <td><?php echo htmlspecialchars($gv['email']); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($gv['monHocPhuTrach']); ?></span>
                            </td>
                            <td>
                                <?php if (isset($gv['tinhTrangTaiKhoan']) && $gv['tinhTrangTaiKhoan'] === 'ACTIVE'): ?>
                                    <span class="badge badge-assigned">Đang làm</span>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">Nghỉ việc</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/public/index.php?page=bgh-quan-ly-giao-vien&action=edit&maGV=<?php echo urlencode($gv['maGV']); ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Cập nhật">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="?page=bgh-quan-ly-giao-vien&action_modal=delete&maGV=<?php echo urlencode($gv['maGV']); ?>&tenGV=<?php echo urlencode($gv['hoTen']); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($count === 0): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                                    Không tìm thấy giáo viên nào phù hợp
                                <?php else: ?>
                                    Chưa có giáo viên nào trong hệ thống
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<?php if (isset($_GET['action_modal']) && $_GET['action_modal'] === 'delete' && isset($_GET['maGV'])): ?>
<div class="modal fade show" id="deleteModal" tabindex="-1" style="display: block;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-exclamation-triangle text-warning me-2"></i>
                    Xác nhận xóa
                </h5>
                <a href="?page=bgh-quan-ly-giao-vien" class="btn-close"></a>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giáo viên <strong><?php echo htmlspecialchars($_GET['tenGV'] ?? ''); ?></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fa-solid fa-warning me-1"></i>
                    Lưu ý: Không thể xóa nếu giáo viên đang làm chủ nhiệm hoặc có phân công giảng dạy.
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="/public/index.php?page=bgh-quan-ly-giao-vien&action=delete" style="display: inline;">
                    <input type="hidden" name="maGV" value="<?php echo htmlspecialchars($_GET['maGV']); ?>">
                    <a href="?page=bgh-quan-ly-giao-vien" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-2"></i>Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show" style="z-index: 1040;"></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

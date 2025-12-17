<?php
// View: Danh sách quản lý giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php
$pageTitle = 'Quản lý giáo viên';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-chalkboard-user me-2"></i>Quản lý giáo viên</h2>
        <a href="/public/index.php?action=bgh-giao-vien-create" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Thêm giáo viên
        </a>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="action" value="bgh-giao-vien-list">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm theo tên, mã, email..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa-solid fa-search me-1"></i>Tìm kiếm
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
                            <th>Mã GV</th>
                            <th>Họ tên</th>
                            <th>Giới tính</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Môn dạy</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachGiaoVien)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    Không có giáo viên nào
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; foreach ($danhSachGiaoVien as $gv): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo htmlspecialchars($gv['maGV']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($gv['hoTen']); ?></td>
                                <td><?php echo htmlspecialchars($gv['gioiTinh']); ?></td>
                                <td><?php echo htmlspecialchars($gv['email']); ?></td>
                                <td><?php echo htmlspecialchars($gv['soDienThoai']); ?></td>
                                <td><?php echo htmlspecialchars($gv['monHocPhuTrach']); ?></td>
                                <td>
                                    <?php if ($gv['tinhTrangTaiKhoan'] == 'ACTIVE'): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Không hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- ❌ BỎ NÚT XEM CHI TIẾT (nếu không cần) -->
                                    <!-- <a href="/public/index.php?action=bgh-giao-vien-view&maGV=<?php echo urlencode($gv['maGV']); ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a> -->
                                    
                                    <!-- GIỮ NÚT SỬA -->
                                    <a href="/public/index.php?action=bgh-giao-vien-edit&maGV=<?php echo urlencode($gv['maGV']); ?>" 
                                       class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    
                                    <!-- ❌ XÓA HOÀN TOÀN NÚT XÓA VÀ FORM -->
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

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

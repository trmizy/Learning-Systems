<?php
// View: Danh sách quản lý giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/quan_ly_giao_vien.php
$pageTitle = 'Quản lý hồ sơ giáo viên - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .teacher-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }
    
    .teacher-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                Quản lý hồ sơ giáo viên
            </h2>
            <p class="text-muted mb-0">Xem, thêm, sửa, xóa thông tin giáo viên</p>
        </div>
        <a href="/public/index.php?action=bgh-tao-giao-vien" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Thêm giáo viên mới
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/public/index.php">
                <input type="hidden" name="page" value="bgh-quan-ly-giao-vien">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm kiếm theo mã, họ tên, email, SĐT, môn học..."
                               value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách giáo viên -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mã GV</th>
                            <th>Họ tên</th>
                            <th>Giới tính</th>
                            <th>Ngày sinh</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>Môn phụ trách</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($danhSachGiaoVien->rowCount() > 0): ?>
                            <?php while ($gv = $danhSachGiaoVien->fetch()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($gv['maGV']); ?></td>
                                    <td><?php echo htmlspecialchars($gv['hoTen']); ?></td>
                                    <td>
                                        <i class="fa-solid fa-<?php echo $gv['gioiTinh'] == 'Nam' ? 'mars text-primary' : 'venus text-danger'; ?>"></i>
                                        <?php echo htmlspecialchars($gv['gioiTinh']); ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($gv['ngaySinh'])); ?></td>
                                    <td><?php echo htmlspecialchars($gv['soDienThoai']); ?></td>
                                    <td><?php echo htmlspecialchars($gv['email']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($gv['monHocPhuTrach']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $gv['tinhTrangTaiKhoan'] == 'ACTIVE' ? 'active' : 'inactive'; ?>">
                                            <?php echo $gv['tinhTrangTaiKhoan'] == 'ACTIVE' ? 'Hoạt động' : 'Khóa'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/public/index.php?action=bgh-sua-giao-vien&maGV=<?php echo urlencode($gv['maGV']); ?>" 
                                           class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('<?php echo htmlspecialchars($gv['maGV']); ?>', '<?php echo htmlspecialchars($gv['hoTen']); ?>')"
                                                title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-users-slash fa-3x mb-3 d-block"></i>
                                    Không tìm thấy giáo viên nào
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
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Xác nhận xóa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giáo viên <strong id="deleteTeacherName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fa-solid fa-exclamation-circle me-2"></i>
                    Hành động này không thể hoàn tác!
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="/public/index.php?action=bgh-xoa-giao-vien">
                    <input type="hidden" name="maGV" id="deleteMaGV">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-2"></i>Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(maGV, hoTen) {
    document.getElementById('deleteMaGV').value = maGV;
    document.getElementById('deleteTeacherName').textContent = hoTen;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

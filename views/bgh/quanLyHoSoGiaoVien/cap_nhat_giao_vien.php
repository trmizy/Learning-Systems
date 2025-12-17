<?php
// View: Form cập nhật giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php
$pageTitle = 'Cập nhật giáo viên - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i>
                    Cập nhật giáo viên
                </h2>
                <a href="/public/index.php?page=bgh-quan-ly-giao-vien" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/public/index.php?action=bgh-sua-giao-vien&maGV=<?php echo urlencode($giaoVien['maGV']); ?>">
                        <!-- Mã giáo viên (chỉ hiển thị) -->
                        <div class="alert alert-info mb-3">
                            <strong>Mã giáo viên:</strong> <?php echo htmlspecialchars($giaoVien['maGV']); ?>
                        </div>

                        <!-- Thông tin cá nhân -->
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-user me-2"></i>Thông tin cá nhân
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="hoTen" class="form-control" 
                                   value="<?php echo htmlspecialchars($giaoVien['hoTen']); ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Giới tính <span class="text-danger">*</span>
                                </label>
                                <select name="gioiTinh" class="form-select" required>
                                    <option value="Nam" <?php echo $giaoVien['gioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $giaoVien['gioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Ngày sinh <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="ngaySinh" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['ngaySinh']); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" name="soDienThoai" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['soDienThoai']); ?>" 
                                       pattern="0[0-9]{9}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['email']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea name="diaChi" class="form-control" rows="2"><?php echo htmlspecialchars($giaoVien['diaChi'] ?? ''); ?></textarea>
                        </div>

                        <!-- Thông tin công tác -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="fa-solid fa-briefcase me-2"></i>Thông tin công tác
                        </h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Môn học phụ trách <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="monHocPhuTrach" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['monHocPhuTrach']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trình độ học vấn</label>
                                <input type="text" name="trinhDoHocVan" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['trinhDoHocVan'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <input type="text" name="chucVu" class="form-control" 
                                       value="<?php echo htmlspecialchars($giaoVien['chucVu'] ?? 'Giáo viên'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <select name="tinhTrangTaiKhoan" class="form-select">
                                    <option value="ACTIVE" <?php echo $giaoVien['tinhTrangTaiKhoan'] == 'ACTIVE' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="INACTIVE" <?php echo $giaoVien['tinhTrangTaiKhoan'] == 'INACTIVE' ? 'selected' : ''; ?>>Khóa</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/public/index.php?page=bgh-quan-ly-giao-vien" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fa-solid fa-save me-2"></i>Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

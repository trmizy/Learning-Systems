<?php
// View: Form cập nhật giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/cap_nhat_giao_vien.php
$pageTitle = 'Cập nhật giáo viên - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/quan_ly_phan_cong.css">

<div class="assignment-container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-user-edit text-info me-2"></i>
                Cập nhật hồ sơ giáo viên
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-info-circle me-1"></i>
                Mã GV: <strong><?php echo htmlspecialchars($giaoVien['maGV']); ?></strong>
            </p>
        </div>
        <a href="/public/index.php?page=bgh-quan-ly-giao-vien" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($message) && $message): ?>
    <div class="alert alert-<?php echo isset($messageType) ? $messageType : 'info'; ?> alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-<?php echo (isset($messageType) && $messageType === 'success') ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card card-assignment">
        <div class="card-body">
            <form method="POST" action="/public/index.php?page=bgh-quan-ly-giao-vien&action=edit&maGV=<?php echo urlencode($giaoVien['maGV']); ?>">
                <div class="row">
                    <!-- Mã giáo viên (disabled) -->
                    <div class="col-md-6 mb-3">
                        <label for="maGV" class="form-label">
                            Mã giáo viên
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="maGV" 
                               value="<?php echo htmlspecialchars($giaoVien['maGV']); ?>"
                               disabled>
                        <small class="text-muted">Không thể thay đổi mã giáo viên</small>
                    </div>

                    <!-- Họ và tên -->
                    <div class="col-md-6 mb-3">
                        <label for="hoTen" class="form-label">
                            Họ và tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="hoTen" 
                               name="hoTen" 
                               required
                               minlength="3"
                               value="<?php echo htmlspecialchars($giaoVien['hoTen']); ?>">
                    </div>

                    <!-- Giới tính -->
                    <div class="col-md-4 mb-3">
                        <label for="gioiTinh" class="form-label">
                            Giới tính <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="gioiTinh" name="gioiTinh" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" <?php echo ($giaoVien['gioiTinh'] === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nữ" <?php echo ($giaoVien['gioiTinh'] === 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                        </select>
                    </div>

                    <!-- Ngày sinh -->
                    <div class="col-md-6 mb-3">
                        <label for="ngaySinh" class="form-label">
                            Ngày sinh <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               class="form-control" 
                               id="ngaySinh" 
                               name="ngaySinh" 
                               required
                               max="<?php echo date('Y-m-d', strtotime('-22 years')); ?>"
                               min="<?php echo date('Y-m-d', strtotime('-65 years')); ?>"
                               value="<?php echo htmlspecialchars($giaoVien['ngaySinh']); ?>">
                        <small class="text-muted">Tuổi từ 22-65</small>
                    </div>

                    <!-- Chức vụ -->
                    <div class="col-md-6 mb-3">
                        <label for="chucVu" class="form-label">
                            Chức vụ
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="chucVu" 
                               name="chucVu" 
                               value="<?php echo htmlspecialchars($giaoVien['chucVu'] ?? 'Giáo viên'); ?>"
                               placeholder="VD: Giáo viên, Tổ trưởng...">
                    </div>

                    <!-- Số điện thoại -->
                    <div class="col-md-6 mb-3">
                        <label for="soDienThoai" class="form-label">
                            Số điện thoại <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="soDienThoai" 
                               name="soDienThoai" 
                               required
                               pattern="0[0-9]{9}"
                               value="<?php echo htmlspecialchars($giaoVien['soDienThoai']); ?>">
                        <small class="text-muted">10 số, bắt đầu bằng 0</small>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required
                               value="<?php echo htmlspecialchars($giaoVien['email']); ?>">
                    </div>

                    <!-- Môn học phụ trách -->
                    <div class="col-md-6 mb-3">
                        <label for="monHocPhuTrach" class="form-label">
                            Môn học phụ trách <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="monHocPhuTrach" 
                               name="monHocPhuTrach" 
                               required
                               value="<?php echo htmlspecialchars($giaoVien['monHocPhuTrach']); ?>">
                    </div>

                    <!-- Trình độ học vấn -->
                    <div class="col-md-6 mb-3">
                        <label for="trinhDoHocVan" class="form-label">
                            Trình độ học vấn
                        </label>
                        <select class="form-select" id="trinhDoHocVan" name="trinhDoHocVan">
                            <option value="">-- Chọn trình độ --</option>
                            <option value="Cử nhân" <?php echo (isset($giaoVien['trinhDoHocVan']) && $giaoVien['trinhDoHocVan'] === 'Cử nhân') ? 'selected' : ''; ?>>Cử nhân</option>
                            <option value="Thạc sĩ" <?php echo (isset($giaoVien['trinhDoHocVan']) && $giaoVien['trinhDoHocVan'] === 'Thạc sĩ') ? 'selected' : ''; ?>>Thạc sĩ</option>
                            <option value="Tiến sĩ" <?php echo (isset($giaoVien['trinhDoHocVan']) && $giaoVien['trinhDoHocVan'] === 'Tiến sĩ') ? 'selected' : ''; ?>>Tiến sĩ</option>
                        </select>
                    </div>

                    <!-- Tình trạng tài khoản -->
                    <div class="col-md-6 mb-3">
                        <label for="tinhTrangTaiKhoan" class="form-label">
                            Tình trạng tài khoản <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="tinhTrangTaiKhoan" name="tinhTrangTaiKhoan" required>
                            <option value="ACTIVE" <?php echo (isset($giaoVien['tinhTrangTaiKhoan']) && $giaoVien['tinhTrangTaiKhoan'] === 'ACTIVE') ? 'selected' : ''; ?>>Đang làm việc</option>
                            <option value="INACTIVE" <?php echo (isset($giaoVien['tinhTrangTaiKhoan']) && $giaoVien['tinhTrangTaiKhoan'] === 'INACTIVE') ? 'selected' : ''; ?>>Nghỉ việc</option>
                        </select>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="col-12 mb-3">
                        <label for="diaChi" class="form-label">
                            Địa chỉ
                        </label>
                        <textarea class="form-control" 
                                  id="diaChi" 
                                  name="diaChi" 
                                  rows="2"><?php echo htmlspecialchars($giaoVien['diaChi']); ?></textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="/public/index.php?page=bgh-quan-ly-giao-vien" class="btn btn-secondary">
                        <i class="fa-solid fa-times me-2"></i>Hủy
                    </a>
                    <button type="submit" class="btn btn-info">
                        <i class="fa-solid fa-save me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

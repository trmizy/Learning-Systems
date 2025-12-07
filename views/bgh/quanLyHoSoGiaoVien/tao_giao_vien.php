<?php
// View: Form tạo mới giáo viên
// Path: views/bgh/quanLyHoSoGiaoVien/tao_giao_vien.php
$pageTitle = 'Thêm giáo viên mới - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/quan_ly_phan_cong.css">

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i>
                    Thêm giáo viên mới
                </h2>
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
            <form method="POST" action="/public/index.php?page=bgh-quan-ly-giao-vien&action=create">
                <div class="row">
                    <!-- Mã trường -->
                    <div class="col-md-4 mb-3">
                        <label for="maTruong" class="form-label">
                            Mã trường <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="maTruong" 
                               name="maTruong" 
                               required
                               pattern="[A-Z0-9]{3,10}"
                               value="<?php echo htmlspecialchars($data['maTruong'] ?? 'TR001'); ?>"
                               placeholder="VD: TR001">
                        <small class="text-muted">TRXXX</small>
                    </div>

                    <!-- Năm vào trường -->
                    <div class="col-md-4 mb-3">
                        <label for="namVao" class="form-label">
                            Năm vào trường <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="namVao" 
                               name="namVao" 
                               required
                    
                               value="<?php echo htmlspecialchars($data['namVao'] ?? date('Y')); ?>"
                               placeholder="VD: 2022">
                        <small class="text-muted"></small>
                    </div>

                    <!-- Họ và tên -->
                    <div class="col-md-4 mb-3">
                        <label for="hoTen" class="form-label">
                            Họ và tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="hoTen" 
                               name="hoTen" 
                               required
                               minlength="3"
                               value="<?php echo htmlspecialchars($data['hoTen'] ?? ''); ?>"
                               placeholder="VD: Nguyen Van A">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/public/index.php?page=bgh-quan-ly-giao-vien&action=create">
                        <!-- Mã trường & Năm vào -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Mã trường <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="maTruong" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['maTruong'] ?? 'TR001'); ?>" 
                                       placeholder="VD: TR001" required>
                                <small class="text-muted">3-10 ký tự viết hoa, số</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Năm vào trường <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="namVao" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['namVao'] ?? date('Y')); ?>" 
                                       min="2000" max="2100" required>
                            </div>
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
                                   value="<?php echo htmlspecialchars($data['hoTen'] ?? ''); ?>" 
                                   placeholder="VD: Nguyễn Văn A" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Giới tính <span class="text-danger">*</span>
                                </label>
                                <select name="gioiTinh" class="form-select" required>
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="Nam" <?php echo ($data['gioiTinh'] ?? '') == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo ($data['gioiTinh'] ?? '') == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Ngày sinh <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="ngaySinh" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['ngaySinh'] ?? ''); ?>" 
                                       max="<?php echo date('Y-m-d', strtotime('-22 years')); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-65 years')); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" name="soDienThoai" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['soDienThoai'] ?? ''); ?>" 
                                       placeholder="0xxxxxxxxx" pattern="0[0-9]{9}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" 
                                       placeholder="example@gmail.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea name="diaChi" class="form-control" rows="2" 
                                      placeholder="Số nhà, đường, phường, quận, TP"><?php echo htmlspecialchars($data['diaChi'] ?? ''); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($data['monHocPhuTrach'] ?? ''); ?>" 
                                       placeholder="VD: Toán" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trình độ học vấn</label>
                                <input type="text" name="trinhDoHocVan" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['trinhDoHocVan'] ?? ''); ?>" 
                                       placeholder="VD: Đại học, Thạc sĩ">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <input type="text" name="chucVu" class="form-control" 
                                       value="<?php echo htmlspecialchars($data['chucVu'] ?? 'Giáo viên'); ?>" 
                                       placeholder="Giáo viên, Tổ trưởng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <select name="tinhTrangTaiKhoan" class="form-select">
                                    <option value="ACTIVE" selected>Hoạt động</option>
                                    <option value="INACTIVE">Khóa</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/public/index.php?page=bgh-quan-ly-giao-vien" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-plus me-2"></i>Thêm giáo viên
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

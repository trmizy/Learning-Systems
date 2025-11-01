<?php
// File: views/gvbm/xem_chi_tiet_hs.php
// Header và Footer đã được Controller gọi.
// Biến $hocSinh được Controller cung cấp
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Chi tiết Học sinh</h2>
        <a href="index.php?action=xem_lop_cn" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách lớp
        </a>
    </div>
    <hr>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fa-solid fa-user-graduate me-2"></i>
                <?php echo htmlspecialchars($hocSinh['hoTen']); ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Mã học sinh:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['maHS']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Ngày sinh:</strong>
                        <span class="text-muted"><?php echo date("d/m/Y", strtotime($hocSinh['ngaySinh'])); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Giới tính:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['gioiTinh']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Số CCCD:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['soCCCD'] ?? 'Chưa cập nhật'); ?></span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Lớp:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['tenLop'] ?? 'Chưa xếp lớp'); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['email'] ?? 'Chưa cập nhật'); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Số điện thoại:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['sdt'] ?? 'Chưa cập nhật'); ?></span>
                    </p>
                     <p class="mb-2">
                        <strong>Trạng thái:</strong>
                        <span class="badge bg-success"><?php echo htmlspecialchars($hocSinh['trangThai']); ?></span>
                    </p>
                </div>
                <div class="col-12">
                    <p class="mb-2">
                        <strong>Địa chỉ:</strong>
                        <span class="text-muted"><?php echo htmlspecialchars($hocSinh['diaChi'] ?? 'Chưa cập nhật'); ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
</div>
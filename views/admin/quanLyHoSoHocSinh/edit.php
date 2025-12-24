<?php
// Expecting $student and optional $error from controller
require __DIR__ . '/../../layouts/header.php';

if (empty($student)) {
    echo "<p>Học sinh không tồn tại.</p>";
    return;
}

if (!empty($error)) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
}

?>
<div class="container mt-4">
    <h2>Chỉnh sửa hồ sơ: <?php echo htmlspecialchars($student['hoTen']); ?></h2>
    
    <!-- FIX: Action gửi đến controller thay vì file model -->
    <form method="post" action="/public/index.php?action=admin-sua-hs">
        <input type="hidden" name="maHS" value="<?php echo htmlspecialchars($student['maHS']); ?>">
        
        <div class="form-group mb-3">
            <label>Họ và tên</label>
            <input name="hoTen" class="form-control" value="<?php echo htmlspecialchars($student['hoTen']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Ngày sinh</label>
            <input name="ngaySinh" type="date" class="form-control" value="<?php echo htmlspecialchars($student['ngaySinh']); ?>" required>
        </div>
        
        <div class="form-group mb-3">
            <label>Địa chỉ</label>
            <input name="diaChi" class="form-control" value="<?php echo htmlspecialchars($student['diaChi'] ?? ''); ?>">
        </div>
        
        <div class="form-group mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group mb-3">
            <label>SĐT học sinh</label>
            <input name="sdt" class="form-control" value="<?php echo htmlspecialchars($student['sdt'] ?? ''); ?>">
        </div>
        
        <div class="form-group mb-3">
            <label>SĐT phụ huynh (chính)</label>
            <?php
            // Lấy SĐT phụ huynh đầu tiên nếu có
            $phSdt = '';
            if (!empty($student['parents'])) {
                $phSdt = $student['parents'][0]['soDienThoai'] ?? '';
            }
            ?>
            <input name="ph_sdt" class="form-control" value="<?php echo htmlspecialchars($phSdt); ?>">
        </div>
        

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save me-1"></i>Lưu thay đổi
            </button>
            
            <!-- FIX: Link Hủy đúng routing -->
            <a href="/public/index.php?action=admin-xem-hs&maHS=<?php echo urlencode($student['maHS']); ?>" class="btn btn-secondary">
                <i class="fa-solid fa-times me-1"></i>Hủy
            </a>
            
            <a href="/public/index.php?action=admin-quan-ly-hoc-sinh" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>


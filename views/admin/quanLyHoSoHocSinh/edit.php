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
    <form method="post" action="/modules/quanLyHoSoHocSinh/edit.php">
        <input type="hidden" name="maHS" value="<?php echo htmlspecialchars($student['maHS']); ?>">
        <div class="form-group">
            <label>Họ và tên</label>
            <input name="hoTen" class="form-control" value="<?php echo htmlspecialchars($student['hoTen']); ?>">
        </div>
        <div class="form-group">
            <label>Ngày sinh</label>
            <input name="ngaySinh" type="date" class="form-control" value="<?php echo htmlspecialchars($student['ngaySinh']); ?>">
        </div>
        <div class="form-group">
            <label>Địa chỉ</label>
            <input name="diaChi" class="form-control" value="<?php echo htmlspecialchars($student['diaChi']); ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>">
        </div>
        <div class="form-group">
            <label>SĐT học sinh</label>
            <input name="sdt" class="form-control" value="<?php echo htmlspecialchars($student['sdt']); ?>">
        </div>
        <div class="form-group">
            <label>SĐT phụ huynh (chính)</label>
            <input name="ph_sdt" class="form-control" value="<?php echo htmlspecialchars($student['phuHuynh_info']); ?>">
        </div>
        <div class="form-group">
            <label>Hạnh kiểm</label>
            <select name="loaiHanhKiem" class="form-control">
                <option value="" <?php echo empty($student['loaiHanhKiem']) ? 'selected' : ''; ?>>-- chọn --</option>
                <option value="Tốt" <?php echo ($student['loaiHanhKiem'] === 'Tốt') ? 'selected' : ''; ?>>Tốt</option>
                <option value="Khá" <?php echo ($student['loaiHanhKiem'] === 'Khá') ? 'selected' : ''; ?>>Khá</option>
                <option value="Trung bình" <?php echo ($student['loaiHanhKiem'] === 'Trung bình') ? 'selected' : ''; ?>>Trung bình</option>
            </select>
        </div>

        <button class="btn btn-primary">Lưu</button>
        <a href="/modules/quanLyHoSoHocSinh/view.php?maHS=<?php echo urlencode($student['maHS']); ?>" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<?php require __DIR__ . '/../../layouts/footer.php';


<?php
$pageTitle = 'Phân công môn học';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Phân công môn học
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/public/index.php?action=bgh-phan-cong-mon-hoc-store">
                        <div class="mb-3">
                            <label class="form-label">Lớp học <span class="text-danger">*</span></label>
                            <select name="maLop" class="form-select" required>
                                <option value="">-- Chọn lớp --</option>
                                <?php foreach ($danhSachLop as $lop): ?>
                                <option value="<?php echo $lop['maLop']; ?>">
                                    <?php echo htmlspecialchars($lop['tenLop']); ?> (Khối <?php echo $lop['khoi']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select name="maMonHoc" class="form-select" required>
                                <option value="">-- Chọn môn --</option>
                                <?php foreach ($danhSachMon as $mon): ?>
                                <option value="<?php echo $mon['maMonHoc']; ?>">
                                    <?php echo htmlspecialchars($mon['tenMon']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giáo viên <span class="text-danger">*</span></label>
                            <select name="maGV" class="form-select" required>
                                <option value="">-- Chọn giáo viên --</option>
                                <?php foreach ($danhSachGiaoVien as $gv): ?>
                                <option value="<?php echo $gv['maGV']; ?>">
                                    <?php echo htmlspecialchars($gv['hoTen']); ?>
                                    (<?php echo htmlspecialchars($gv['monHocPhuTrach']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Năm học</label>
                                <input type="text" name="namHoc" class="form-control" value="2024-2025" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Học kỳ</label>
                                <select name="hocKy" class="form-select" required>
                                    <option value="HK1">Học kỳ 1</option>
                                    <option value="HK2">Học kỳ 2</option>
                                    <option value="CaNam">Cả năm</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghiChu" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/public/index.php?action=bgh-phan-cong" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-2"></i>Lưu phân công
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

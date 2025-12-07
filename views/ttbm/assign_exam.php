<?php
// ⚠️ THÊM: Require header ở ĐẦU FILE
$pageTitle = 'Phân công ra đề thi - TTBM';
require_once __DIR__ . '/../layouts/header.php';

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container py-4">

    <a href="index.php" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Dashboard
    </a>

    <h2 class="mb-4 fw-bold">Phân công giáo viên ra đề thi</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ⚠️ THÊM: Dropdown chọn môn học -->
    <?php if (!empty($monList)): ?>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fa-solid fa-book me-2"></i>Chọn môn học
            </h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="assign_exam">
                <div class="col-auto">
                    <select name="mon" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($monList as $mon): ?>
                            <option value="<?= htmlspecialchars($mon['maMonHoc']) ?>" 
                                    <?= ($selectedMon == $mon['maMonHoc']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mon['tenMon']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- FORM PHÂN CÔNG -->
    <form action="index.php?action=store_assign_exam" method="POST" class="row g-3 mb-4">

        <!-- KHỐI -->
        <div class="col-md-3">
            <label class="form-label fw-semibold">Khối</label>
            <select name="khoi" class="form-select" required>
                <option value="">-- Chọn khối --</option>
                <?php foreach ($khoi as $khoiItem): ?>
                    <option value="<?= htmlspecialchars($khoiItem['soKhoi']) ?>">
                        <?= htmlspecialchars($khoiItem['tenKhoi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- GIÁO VIÊN BỘ MÔN - FIX: Hiển thị thông báo nếu rỗng -->
        <div class="col-md-5">
            <label class="form-label fw-semibold">Giáo viên bộ môn</label>
            <?php if (empty($listGV)): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Không có giáo viên nào dạy môn này. Vui lòng chọn môn khác.
                </div>
            <?php else: ?>
                <select name="listGV[]" multiple class="form-select" size="5" required>
                    <?php foreach ($listGV as $gv): ?>
                        <option value="<?= htmlspecialchars($gv['maGV']) ?>">
                            <?= htmlspecialchars($gv['hoTen']) ?> (<?= htmlspecialchars($gv['monHocPhuTrach']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Giữ Ctrl để chọn nhiều giáo viên</small>
            <?php endif; ?>
        </div>

        <!-- HỌC KỲ -->
        <div class="col-md-2">
            <label class="form-label fw-semibold">Học kỳ</label>
            <select name="hocKy" class="form-select" required>
                <option value="">-- Chọn học kỳ --</option>
                <option value="HK1">Học kỳ I</option>
                <option value="HK2">Học kỳ II</option>
            </select>
        </div>

        <!-- KỲ THI -->
        <div class="col-md-2">
            <label class="form-label fw-semibold">Kỳ thi</label>
            <select name="kyThi" class="form-select" required>
                <option value="">-- Chọn kỳ thi --</option>
                <option value="Giữa kỳ">Giữa kỳ</option>
                <option value="Cuối kỳ">Cuối kỳ</option>
            </select>
        </div>

        <!-- SỐ LƯỢNG ĐỀ -->
        <div class="col-md-3">
            <label class="form-label fw-semibold">Số lượng đề</label>
            <input type="number" name="soLuongDe" class="form-control" min="1" required>
        </div>

        <!-- THỜI HẠN NỘP -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Thời hạn nộp</label>
            <input type="datetime-local" name="thoiHan" class="form-control" required>
        </div>

        <!-- GHI CHÚ -->
        <div class="col-md-5">
            <label class="form-label fw-semibold">Ghi chú</label>
            <textarea name="ghiChu" class="form-control" rows="2"></textarea>
        </div>

        <!-- BUTTON -->
        <div class="col-12 text-end">
            <button class="btn btn-primary px-4">
                <i class="fa-solid fa-save me-2"></i> Xác nhận phân công
            </button>
        </div>
    </form>


    <!-- DANH SÁCH PHÂN CÔNG -->
    <h4 class="fw-bold mt-4">Danh sách phân công hiện có</h4>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Khối</th>
                    <th>Học kỳ</th>
                    <th>Kỳ thi</th>
                    <th>Số lượng đề</th>
                    <th>Thời hạn</th>
                    <th>Ghi chú</th>
                    <th>Giáo viên phụ trách</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($phanCong)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            Chưa có phân công nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($phanCong as $row): ?>
                        <tr>
                            <td>Khối <?= htmlspecialchars($row['khoi']) ?></td>
                            <td><?= htmlspecialchars($row['hocKy']) ?></td>
                            <td><?= htmlspecialchars($row['kyThi']) ?></td>
                            <td><?= htmlspecialchars($row['soLuongDe']) ?></td>
                            <td><?= htmlspecialchars($row['thoiHan']) ?></td>
                            <td><?= htmlspecialchars($row['ghiChu']) ?></td>
                            <td><?= htmlspecialchars($row['giaoVien']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
// ⚠️ THÊM: Require footer ở CUỐI FILE
require_once __DIR__ . '/../layouts/footer.php';
?>

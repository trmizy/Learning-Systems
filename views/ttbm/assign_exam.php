<?php
$pageTitle = 'Phân công ra đề thi';
require_once __DIR__ . '/../layouts/header.php';

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container py-5">

    <!-- QUAY LẠI -->
    <a href="index.php"
        class="btn btn-primary mb-4 px-4 py-2 shadow-sm"
        style="background: linear-gradient(135deg,#2563eb,#06b6d4); border: none;">
            <i class="fa-solid fa-arrow-left me-2"></i>
            Quay lại Dashboard
    </a>

    <!-- TIÊU ĐỀ -->
    <div class="mb-4 p-4 rounded text-white shadow-sm"
         style="background: linear-gradient(135deg,#4f46e5,#06b6d4)">
        <h3 class="fw-bold mb-1">Phân công giáo viên ra đề thi</h3>
        <div class="mt-2">
            <span class="me-2 opacity-75">Môn phân công:</span>
            <span class="fw-bold fs-4 text-white">
                <?= htmlspecialchars($mon['tenMon']) ?>
            </span>
        </div>
    </div>

    <!-- FLASH -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM PHÂN CÔNG -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fa-solid fa-pen-to-square me-2"></i>
                Thông tin phân công
            </h5>
        </div>

        <div class="card-body pt-4">
            <form action="index.php?action=store_assign_exam" method="POST" class="row g-4">
                <input type="hidden"
                    name="maMonHoc"
                    value="<?= htmlspecialchars($monList[0]['maMonHoc']) ?>">
                
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

                <!-- GIÁO VIÊN -->
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Giáo viên bộ môn</label>
                    <select name="listGV[]" multiple class="form-select" size="6" required>
                        <?php foreach ($listGV as $gv): ?>
                            <option value="<?= htmlspecialchars($gv['maGV']) ?>">
                                <?= htmlspecialchars($gv['hoTen']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Giữ Ctrl để chọn nhiều giáo viên</small>
                </div>

                <!-- HỌC KỲ -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Học kỳ</label>
                    <select name="hocKy" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <option value="HK1">HK I</option>
                        <option value="HK2">HK II</option>
                    </select>
                </div>

                <!-- KỲ THI -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kỳ thi</label>
                    <select name="kyThi" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <option value="Giữa kỳ">Giữa kỳ</option>
                        <option value="Cuối kỳ">Cuối kỳ</option>
                    </select>
                </div>

                <!-- SỐ LƯỢNG -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Số lượng đề</label>
                    <input type="number" name="soLuongDe" class="form-control" min="1" required>
                </div>

                <!-- THỜI HẠN -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thời hạn nộp</label>
                    <input type="datetime-local" name="thoiHan" class="form-control" required>
                </div>

                <!-- GHI CHÚ -->
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="ghiChu" class="form-control" rows="2"
                              placeholder="Ghi chú ..."></textarea>
                </div>

                <!-- BUTTON -->
                <div class="col-12 text-end mt-3">
                    <button class="btn btn-primary px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-check me-2"></i>
                        Xác nhận phân công
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DANH SÁCH PHÂN CÔNG -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold text-success">
                <i class="fa-solid fa-list-check me-2"></i>
                Danh sách phân công hiện có
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Khối</th>
                            <th>Môn</th>
                            <th>Học kỳ</th>
                            <th>Kỳ thi</th>
                            <th>Số đề</th>
                            <th>Thời hạn</th>
                            <th>Giáo viên</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($phanCong)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox me-2"></i>
                                Chưa có phân công nào
                            </td>
                        </tr>
                    <?php else: foreach ($phanCong as $row): ?>
                        <tr>
                            <td class="fw-semibold"><?= $row['maPhanCongRaDe'] ?></td>
                            <td>Khối <?= htmlspecialchars($row['khoi']) ?></td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= htmlspecialchars($row['tenMon'] ?? '') ?>
                                </span>
                            </td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['hocKy']) ?></span></td>
                            <td><?= htmlspecialchars($row['kyThi']) ?></td>
                            <td><?= htmlspecialchars($row['soLuongDe']) ?></td>
                            <td><?= htmlspecialchars($row['thoiHan']) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['giaoVien'] ?? '') ?></td>
                            <td>
                                <a href="index.php?action=cancel_assign&maPhanCongRaDe=<?= $row['maPhanCongRaDe'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Xác nhận hủy phân công?')">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.table th { white-space: nowrap; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

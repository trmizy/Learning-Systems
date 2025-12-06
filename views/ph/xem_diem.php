<?php
/**
 * View: Xem điểm con (Phụ huynh)
 * Path: views/ph/xem_diem.php
 * Chỉ chứa HTML và hiển thị dữ liệu từ Controller
 */

// Kiểm tra dữ liệu từ Controller
if (!isset($maHS) || !isset($bangDiem)) {
    die('Lỗi: View được gọi trực tiếp mà không qua Controller');
}

// Header
$pageTitle = 'Xem điểm con - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
.grades-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.grades-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(240, 147, 251, 0.3);
}

.filter-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.grades-table-container {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.grades-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

.grades-table thead {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.grades-table th {
    padding: 15px;
    text-align: center;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.2);
}

.grades-table td {
    padding: 12px;
    text-align: center;
    border: 1px solid #e0e0e0;
}

.grades-table tbody tr:hover {
    background-color: #fff8f8;
}

.grades-table tbody tr.no-data {
    background-color: #fff8dc;
}

.subject-name {
    text-align: left !important;
    font-weight: 500;
}

.score-cell {
    font-weight: 600;
    font-size: 1.1em;
}

.score-excellent {
    color: #22c55e;
}

.score-good {
    color: #3b82f6;
}

.score-average {
    color: #f59e0b;
}

.score-weak {
    color: #ef4444;
}

.score-empty {
    color: #9ca3af;
    font-style: italic;
}

.summary-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}

.summary-card h3 {
    margin: 0;
    font-size: 2.5em;
    font-weight: bold;
}

.summary-card p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}

.alert-info-custom {
    background-color: #fce7f3;
    border-left: 4px solid #ec4899;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.child-selector {
    background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}
</style>

<div class="container my-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-chart-bar me-2"></i>
        Bảng điểm con em: <?php echo htmlspecialchars($thongTinHS['hoTen']); ?>
    </h2>

    <?php if (!empty($danhSachCon) && count($danhSachCon) > 1): ?>
    <!-- Chọn con -->
    <div class="mb-3">
        <label>Chọn con:</label>
        <select class="form-select" onchange="location.href='?action=ph-xem-diem&maHS=' + this.value">
            <?php foreach ($danhSachCon as $con): ?>
            <option value="<?= $con['maHS'] ?>" <?= $con['maHS'] == $maHS ? 'selected' : '' ?>>
                <?= htmlspecialchars($con['hoTen']) ?> - Lớp <?= htmlspecialchars($con['tenLop']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <!-- Chọn học kỳ -->
    <div class="row mb-3">
        <div class="col-md-6">
            <select class="form-select" onchange="location.href='?action=ph-xem-diem&maHS=<?= $maHS ?>&hocKy=' + this.value + '&namHoc=<?= $namHoc ?>'">
                <option value="HK1" <?= $hocKy == 'HK1' ? 'selected' : '' ?>>Học kỳ 1</option>
                <option value="HK2" <?= $hocKy == 'HK2' ? 'selected' : '' ?>>Học kỳ 2</option>
            </select>
        </div>
    </div>

    <!-- Bảng điểm -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Môn học</th>
                    <th>Thường xuyên</th>
                    <th>Giữa kỳ</th>
                    <th>Cuối kỳ</th>
                    <th>Trung bình</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bangDiem as $mon): ?>
                <tr>
                    <td><?= htmlspecialchars($mon['tenMon']) ?></td>
                    <td><?= $mon['diemThuongXuyen'] ?></td>
                    <td><?= $mon['diemGiuaKy'] ?></td>
                    <td><?= $mon['diemCuoiKy'] ?></td>
                    <td class="fw-bold">
                        <?= round(($mon['diemThuongXuyen'] + $mon['diemGiuaKy'] + $mon['diemCuoiKy'] * 2) / 4, 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-info">
                <tr>
                    <td colspan="4" class="text-end fw-bold">Điểm trung bình:</td>
                    <td class="fw-bold"><?= $diemTB ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
/**
 * View: Xem điểm con (Phụ huynh)
 * Path: views/ph/grades.php
 */

// Load controller
$data = require_once __DIR__ . '/../../controllers/ph/diem_controller.php';

// Extract data
$danhSachCon = $data['danhSachCon'];
$maHS = $data['maHS'];
$thongTinHS = $data['thongTinHS'];
$danhSachDiem = $data['danhSachDiem'];
$danhSachNamHoc = $data['danhSachNamHoc'];
$namHoc = $data['namHoc'];
$hocKy = $data['hocKy'];
$diemTBC = $data['diemTBC'];
$validHocKy = $data['validHocKy'];

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

<div class="grades-container">
    <!-- Header -->
    <div class="grades-header">
        <h2>
            <i class="fa-solid fa-chart-bar me-2"></i>
            Xem điểm con
        </h2>
        <p class="mb-0 mt-2">
            <i class="fa-solid fa-user-group me-2"></i>
            Phụ huynh - Quản lý kết quả học tập
        </p>
    </div>

    <!-- Alert thông tin -->
    <div class="alert-info-custom">
        <i class="fa-solid fa-info-circle me-2"></i>
        <strong>Lưu ý:</strong> Bạn chỉ có thể xem điểm của con em đã được liên kết trong hệ thống. 
        Nếu môn học chưa có điểm, cột điểm sẽ hiển thị "Chưa có". 
        Điểm trung bình được tính theo công thức: (ĐTX + ĐGK + ĐCK×2) / 4
    </div>

    <!-- Bộ lọc -->
    <div class="filter-card">
        <form method="GET" action="" class="row g-3 align-items-end">
            <!-- Chọn con -->
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fa-solid fa-child me-2"></i>Chọn con
                </label>
                <select name="maHS" class="form-select" required onchange="this.form.submit()">
                    <option value="">-- Chọn học sinh --</option>
                    <?php 
                    // Reset pointer
                    $danhSachCon->execute();
                    while ($con = $danhSachCon->fetch()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($con['maHS']); ?>"
                            <?php echo ($con['maHS'] == $maHS) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($con['hoTen']); ?> 
                            <?php if (!empty($con['tenLop'])): ?>
                                (Lớp <?php echo htmlspecialchars($con['tenLop']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Năm học -->
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fa-solid fa-calendar me-2"></i>Năm học
                </label>
                <select name="namHoc" class="form-select" required>
                    <?php 
                    // Reset pointer
                    $danhSachNamHoc->execute();
                    while ($nh = $danhSachNamHoc->fetch()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($nh['namHoc']); ?>"
                            <?php echo ($nh['namHoc'] == $namHoc) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nh['namHoc']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Học kỳ -->
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fa-solid fa-book me-2"></i>Học kỳ
                </label>
                <select name="hocKy" class="form-select" required>
                    <?php foreach ($validHocKy as $hk): ?>
                        <option value="<?php echo htmlspecialchars($hk); ?>"
                            <?php echo ($hk == $hocKy) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($hk); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Nút xem -->
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-filter me-2"></i>Xem điểm
                </button>
            </div>
        </form>
    </div>

    <?php if ($maHS && $thongTinHS): ?>
    <!-- Thông tin học sinh đang xem -->
    <div class="child-selector">
        <h6 class="mb-2">
            <i class="fa-solid fa-user-graduate me-2"></i>
            Đang xem điểm của: <strong><?php echo htmlspecialchars($thongTinHS['hoTen']); ?></strong>
        </h6>
        <p class="mb-0 small">
            <i class="fa-solid fa-id-card me-2"></i>Mã HS: <?php echo htmlspecialchars($thongTinHS['maHS']); ?>
            <?php if (!empty($thongTinHS['tenLop'])): ?>
            <span class="mx-2">|</span>
            <i class="fa-solid fa-users me-2"></i>Lớp: <?php echo htmlspecialchars($thongTinHS['tenLop']); ?>
            <?php endif; ?>
        </p>
    </div>

    <!-- Điểm trung bình chung -->
    <?php if ($diemTBC !== null): ?>
    <div class="row mb-4">
        <div class="col-md-4 mx-auto">
            <div class="summary-card">
                <p><i class="fa-solid fa-trophy me-2"></i>Điểm trung bình chung</p>
                <h3><?php echo number_format($diemTBC, 2); ?></h3>
                <p>
                    <?php 
                    if ($diemTBC >= 8.0) echo '<i class="fa-solid fa-star"></i> Giỏi';
                    elseif ($diemTBC >= 6.5) echo '<i class="fa-solid fa-circle-check"></i> Khá';
                    elseif ($diemTBC >= 5.0) echo '<i class="fa-solid fa-circle-minus"></i> Trung bình';
                    else echo '<i class="fa-solid fa-circle-xmark"></i> Yếu';
                    ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bảng điểm -->
    <div class="grades-table-container">
        <h5 class="mb-3">
            <i class="fa-solid fa-table me-2"></i>
            Chi tiết điểm - <?php echo htmlspecialchars($namHoc); ?> - <?php echo htmlspecialchars($hocKy); ?>
        </h5>
        
        <table class="grades-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Môn học</th>
                    <th>Điểm TX</th>
                    <th>Điểm giữa kỳ</th>
                    <th>Điểm cuối kỳ</th>
                    <th>Điểm TB</th>
                    <th>Giáo viên</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                $hasData = false;
                while ($diem = $danhSachDiem->fetch()): 
                    $hasData = true;
                    
                    // Xác định class màu điểm TB
                    $tbClass = 'score-empty';
                    if ($diem['diemTrungBinh'] !== null) {
                        if ($diem['diemTrungBinh'] >= 8.0) $tbClass = 'score-excellent';
                        elseif ($diem['diemTrungBinh'] >= 6.5) $tbClass = 'score-good';
                        elseif ($diem['diemTrungBinh'] >= 5.0) $tbClass = 'score-average';
                        else $tbClass = 'score-weak';
                    }
                    
                    // Kiểm tra có điểm nào không
                    $coDiem = ($diem['diemThuongXuyen'] !== null || 
                               $diem['diemGiuaKy'] !== null || 
                               $diem['diemCuoiKy'] !== null);
                ?>
                <tr <?php echo !$coDiem ? 'class="no-data"' : ''; ?>>
                    <td><?php echo $stt++; ?></td>
                    <td class="subject-name">
                        <i class="fa-solid fa-book-open me-2"></i>
                        <?php echo htmlspecialchars($diem['tenMon']); ?>
                    </td>
                    <td class="score-cell">
                        <?php echo $diem['diemThuongXuyen'] !== null 
                            ? number_format($diem['diemThuongXuyen'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell">
                        <?php echo $diem['diemGiuaKy'] !== null 
                            ? number_format($diem['diemGiuaKy'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell">
                        <?php echo $diem['diemCuoiKy'] !== null 
                            ? number_format($diem['diemCuoiKy'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell <?php echo $tbClass; ?>">
                        <?php echo $diem['diemTrungBinh'] !== null 
                            ? number_format($diem['diemTrungBinh'], 2) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td>
                        <?php if (!empty($diem['tenGiaoVien'])): ?>
                            <i class="fa-solid fa-chalkboard-user me-1"></i>
                            <?php echo htmlspecialchars($diem['tenGiaoVien']); ?>
                        <?php else: ?>
                            <span class="text-muted">Chưa phân công</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (!$hasData): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                        Chưa có dữ liệu điểm cho năm học và học kỳ này
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Chú thích -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #22c55e;"></div>
                <span>Giỏi (≥ 8.0)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #3b82f6;"></div>
                <span>Khá (6.5 - 7.9)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #f59e0b;"></div>
                <span>Trung bình (5.0 - 6.4)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ef4444;"></div>
                <span>Yếu (&lt; 5.0)</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Chưa chọn con -->
    <div class="alert alert-warning">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        Vui lòng chọn con em để xem điểm
    </div>
    <?php endif; ?>

    <!-- Nút quay lại -->
    <div class="text-center mt-4">
        <a href="/views/ph/dashboard.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

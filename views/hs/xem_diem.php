<?php
/**
 * View: Xem điểm (Học sinh)
 */

// Kiểm tra dữ liệu từ Controller - FIX: Kiểm tra đúng biến
if (!isset($thongTinHS) || !isset($danhSachDiem)) {
    die('Lỗi: View được gọi trực tiếp mà không qua Controller');
}

// Header
$pageTitle = 'Bảng điểm - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
.grades-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.grades-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background-color: #f8f9ff;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background-color: #e0f2fe;
    border-left: 4px solid #0284c7;
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

    .score-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .score-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
    .score-excellent { color: #28a745; font-weight: bold; }
    .score-good { color: #17a2b8; font-weight: bold; }
    .score-average { color: #ffc107; font-weight: bold; }
    .score-poor { color: #dc3545; font-weight: bold; }
</style>

<div class="grades-container">
    <!-- Header -->
    <div class="grades-header">
        <h2>
            <i class="fa-solid fa-chart-bar me-2"></i>
            Bảng điểm học tập
        </h2>
        <?php if ($thongTinHS): ?>
        <p class="mb-0 mt-2">
            <i class="fa-solid fa-user me-2"></i><?php echo htmlspecialchars($thongTinHS['hoTen'] ?? 'N/A'); ?>
            <span class="mx-3">|</span>
            <i class="fa-solid fa-id-card me-2"></i><?php echo htmlspecialchars($thongTinHS['maHS'] ?? 'N/A'); ?>
            <?php if (!empty($thongTinHS['tenLop'])): ?>
            <span class="mx-3">|</span>
            <i class="fa-solid fa-users me-2"></i>Lớp: <?php echo htmlspecialchars($thongTinHS['tenLop']); ?>
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- Alert thông tin -->
    <div class="alert-info-custom">
        <i class="fa-solid fa-info-circle me-2"></i>
        <strong>Lưu ý:</strong> Điểm trung bình được tính từ VIEW v_diem_hocsinh.
    </div>

    <!-- Bộ lọc -->
    <div class="filter-card">
        <form method="GET" action="/public/index.php" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="hs-xem-diem">
            
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="fa-solid fa-calendar me-2"></i>Năm học
                </label>
                <select name="namHoc" class="form-select" required>
                    <?php if (isset($danhSachNamHoc)): ?>
                        <?php while ($nh = $danhSachNamHoc->fetch()): ?>
                            <option value="<?php echo htmlspecialchars($nh['namHoc']); ?>"
                                <?php echo ($nh['namHoc'] == $namHoc) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nh['namHoc']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="2024-2025" selected>2024-2025</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="fa-solid fa-book me-2"></i>Học kỳ
                </label>
                <select name="hocKy" class="form-select" required>
                    <?php 
                    $validHocKy = $validHocKy ?? ['HK1', 'HK2', 'Cả năm'];
                    foreach ($validHocKy as $hk): 
                    ?>
                        <option value="<?php echo htmlspecialchars($hk); ?>"
                            <?php echo ($hk == $hocKy) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($hk); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-filter me-2"></i>Xem điểm
                </button>
            </div>
        </form>
    </div>

    <!-- Điểm trung bình chung -->
    <?php if (isset($diemTBC) && $diemTBC !== null): ?>
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
            Chi tiết điểm - <?php echo htmlspecialchars($namHoc ?? '2024-2025'); ?> - <?php echo htmlspecialchars($hocKy ?? 'HK1'); ?>
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
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                $hasData = false;
                
                // FIX: Kiểm tra $danhSachDiem là PDOStatement
                if ($danhSachDiem && $danhSachDiem instanceof PDOStatement):
                    while ($diem = $danhSachDiem->fetch()): 
                        $hasData = true;
                        
                        // Xác định class màu điểm TB
                        $tbClass = 'score-empty';
                        $diemTB = $diem['diemTrungBinh'] ?? $diem['diemTrungBinhMon'] ?? null;
                        
                        if ($diemTB !== null) {
                            if ($diemTB >= 8.0) $tbClass = 'score-excellent';
                            elseif ($diemTB >= 6.5) $tbClass = 'score-good';
                            elseif ($diemTB >= 5.0) $tbClass = 'score-average';
                            else $tbClass = 'score-weak';
                        }
                ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td class="subject-name">
                        <i class="fa-solid fa-book-open me-2"></i>
                        <?php echo htmlspecialchars($diem['tenMonHoc'] ?? $diem['tenMon'] ?? 'N/A'); ?>
                    </td>
                    <td class="score-cell">
                        <?php echo isset($diem['diemThuongXuyen']) && $diem['diemThuongXuyen'] !== null
                            ? number_format($diem['diemThuongXuyen'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell">
                        <?php echo isset($diem['diemGiuaKy']) && $diem['diemGiuaKy'] !== null
                            ? number_format($diem['diemGiuaKy'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell">
                        <?php echo isset($diem['diemCuoiKy']) && $diem['diemCuoiKy'] !== null
                            ? number_format($diem['diemCuoiKy'], 1) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                    <td class="score-cell <?php echo $tbClass; ?>">
                        <?php echo $diemTB !== null
                            ? number_format($diemTB, 2) 
                            : '<span class="score-empty">Chưa có</span>'; ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                endif;
                
                if (!$hasData): 
                ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
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

    <!-- Nút quay lại -->
    <div class="text-center mt-4">
        <a href="/public/index.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

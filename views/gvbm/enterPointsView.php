<?php 
require_once __DIR__ . '/../layouts/header.php'; 

// Hàm tính điểm trung bình
function tinhDiemTrungBinh($tx, $gk, $ck) {
    if ($tx === null && $gk === null && $ck === null) {
        return null;
    }
    
    $tx = $tx ?? 0;
    $gk = $gk ?? 0;
    $ck = $ck ?? 0;
    
    // Công thức: (ĐTX + ĐGK×2 + ĐCK×3) / 6
    $dtb = ($tx + $gk * 2 + $ck * 3) / 6;
    return round($dtb, 1);
}
?>

<link rel="stylesheet" href="/assets/css/nhapdiem.css">

<?php if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])): 
    $icons = ['success'=>'check-circle', 'danger'=>'exclamation-circle', 'info'=>'info-circle', 'warning'=>'exclamation-triangle']; ?>
    <div style="max-width: 1400px; margin: 20px auto; padding: 0 20px;">
        <?php foreach ($_SESSION['messages'] as $msg): ?>
            <div class="alert alert-<?= $msg['type'] ?>">
                <i class="fas fa-<?= $icons[$msg['type']] ?? 'info-circle' ?>"></i>
                <?= htmlspecialchars($msg['text']) ?>
            </div>
        <?php endforeach; unset($_SESSION['messages']); ?>
    </div>
<?php endif; ?>

<div class="nhapdiem-container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Nhập điểm</h1>
        <div class="header-info">
            <form method="GET" class="filter-form" style="display: flex; gap: 20px; align-items: center;">
                <?php $selectStyle = 'padding: 8px 12px; border-radius: 5px; border: 2px solid white; background: rgba(255,255,255,0.2); color: black; font-weight: bold; cursor: pointer;'; ?>
                
                <!-- Năm học -->
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <label for="namHoc" style="margin-right: 8px;">Năm học:</label>
                    <select name="namHoc" id="namHoc" onchange="this.form.submit()" style="<?= $selectStyle ?>">
                        <option value="2024-2025" <?= $namHoc === '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                        <option value="2023-2024" <?= $namHoc === '2023-2024' ? 'selected' : '' ?>>2023-2024</option>
                        <option value="2025-2026" <?= $namHoc === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                    </select>
                </div>
                
                <!-- Học kỳ -->
                <div class="info-item">
                    <i class="fas fa-book"></i>
                    <label for="hocKy" style="margin-right: 8px;">Học kỳ:</label>
                    <select name="hocKy" id="hocKy" onchange="this.form.submit()" style="<?= $selectStyle ?>">
                        <option value="HK1" <?= $hocKy === 'HK1' ? 'selected' : '' ?>>Học kỳ 1</option>
                        <option value="HK2" <?= $hocKy === 'HK2' ? 'selected' : '' ?>>Học kỳ 2</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Phần chọn lớp -->
    <div class="card selection-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách lớp được phân công</h3>
        </div>
        <div class="card-body">
            <?php if (empty($danhSachLop)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Bạn chưa được phân công giảng dạy lớp nào trong học kỳ này.
                </div>
            <?php else: ?>
                <div class="class-grid">
                    <?php foreach ($danhSachLop as $lop): ?>
                        <a href="?maLop=<?= urlencode($lop['maLop']) ?>&maMonHoc=<?= urlencode($lop['maMonHoc']) ?>&namHoc=<?= urlencode($namHoc) ?>&hocKy=<?= urlencode($hocKy) ?>" 
                           class="class-card <?= ($selectedMaLop == $lop['maLop'] && $selectedMaMonHoc == $lop['maMonHoc']) ? 'active' : '' ?>">
                            <div class="class-card-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="class-card-content">
                                <h4><?= htmlspecialchars($lop['tenLop']) ?></h4>
                                <p class="subject-name"><?= htmlspecialchars($lop['tenMon']) ?></p>
                                <div class="class-meta">
                                    <span><i class="fas fa-users"></i> Sĩ số: <?= $lop['siSo'] ?></span>
                                    <span><i class="fas fa-layer-group"></i> Khối: <?= htmlspecialchars($lop['khoi']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Phần bảng điểm -->
    <?php if ($selectedMaLop && $selectedMaMonHoc && $selectedLopInfo): ?>
        <div class="card grade-card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-table"></i> 
                    Bảng điểm - <?= htmlspecialchars($selectedLopInfo['tenLop']) ?> - <?= htmlspecialchars($selectedLopInfo['tenMon']) ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($bangDiem)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Lớp này chưa có học sinh nào.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Hướng dẫn:</strong> Nhập điểm từ 0 đến 10. Bỏ trống nếu chưa có điểm. 
                        Điểm trung bình sẽ tự động tính theo công thức: (ĐTX + ĐGK×2 + ĐCK×3) / 6
                    </div>

                    <form method="POST" class="grade-form">
                        <input type="hidden" name="maLop" value="<?= htmlspecialchars($selectedMaLop) ?>">
                        <input type="hidden" name="maMonHoc" value="<?= htmlspecialchars($selectedMaMonHoc) ?>">
                        <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                        <input type="hidden" name="hocKy" value="<?= htmlspecialchars($hocKy) ?>">
                        <input type="hidden" name="submit_grades" value="1">

                        <div class="table-responsive">
                            <table class="grade-table">
                                <thead>
                                    <tr>
                                        <th width="5%">STT</th>
                                        <th width="15%">Mã học sinh</th>
                                        <th width="25%">Họ và tên</th>
                                        <th width="12%">Điểm TX</th>
                                        <th width="12%">Điểm GK</th>
                                        <th width="12%">Điểm CK</th>
                                        <th width="12%">Điểm TB</th>
                                        <th width="7%">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bangDiem as $index => $hs): 
                                        $daLuuDayDu = ($hs['diemThuongXuyen'] !== null && $hs['diemGiuaKy'] !== null && $hs['diemCuoiKy'] !== null);
                                        $readonly = $daLuuDayDu ? 'readonly' : '';
                                        $rowClass = $daLuuDayDu ? 'disabled' : '';
                                    ?>
                                        <tr data-mahs="<?= htmlspecialchars($hs['maHS']) ?>" class="<?= $rowClass ?>">
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($hs['maHS']) ?></td>
                                            <td class="student-name">
                                                <?= htmlspecialchars($hs['hoTen']) ?>
                                                <?php if ($daLuuDayDu): ?>
                                                    <span class="badge bg-success ms-2">✓ Đã lưu</span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- Điểm thường xuyên -->
                                            <td>
                                                <input type="number" class="grade-input" name="diemThuongXuyen[<?= $hs['maHS'] ?>]" data-mahs="<?= $hs['maHS'] ?>" value="<?= $hs['diemThuongXuyen'] !== null ? number_format($hs['diemThuongXuyen'], 1) : '' ?>" min="0" max="10" step="0.1" placeholder="0-10" oninput="calculateAverage('<?= $hs['maHS'] ?>')" <?= $readonly ?>>
                                            </td>
                                            <!-- Điểm giữa kỳ -->
                                            <td>
                                                <input type="number" class="grade-input" name="diemGiuaKy[<?= $hs['maHS'] ?>]" data-mahs="<?= $hs['maHS'] ?>" value="<?= $hs['diemGiuaKy'] !== null ? number_format($hs['diemGiuaKy'], 1) : '' ?>" min="0" max="10" step="0.1" placeholder="0-10" oninput="calculateAverage('<?= $hs['maHS'] ?>')" <?= $readonly ?>>
                                            </td>
                                            <!-- Điểm cuối kỳ -->
                                            <td>
                                                <input type="number" class="grade-input" name="diemCuoiKy[<?= $hs['maHS'] ?>]" data-mahs="<?= $hs['maHS'] ?>" value="<?= $hs['diemCuoiKy'] !== null ? number_format($hs['diemCuoiKy'], 1) : '' ?>" min="0" max="10" step="0.1" placeholder="0-10" oninput="calculateAverage('<?= $hs['maHS'] ?>')" <?= $readonly ?>>
                                            </td>
                                            <td class="text-center">
                                                <span class="average-score" id="avg-<?= $hs['maHS'] ?>" style="color: #000">
                                                    <?= $hs['diemTrungBinh'] !== null ? number_format($hs['diemTrungBinh'], 1) : '-' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($daLuuDayDu): ?>
                                                    <i class="fas fa-lock text-success" title="Đã khóa"></i>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 12px;">Chờ lưu</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu tất cả
                            </button>
                            <a href="?namHoc=<?= urlencode($namHoc) ?>&hocKy=<?= urlencode($hocKy) ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function calculateAverage(maHS) {
    const tx = document.querySelector(`input[name="diemThuongXuyen[${maHS}]"]`);
    const gk = document.querySelector(`input[name="diemGiuaKy[${maHS}]"]`);
    const ck = document.querySelector(`input[name="diemCuoiKy[${maHS}]"]`);
    const avgSpan = document.getElementById(`avg-${maHS}`);
    
    const txVal = parseFloat(tx.value) || 0;
    const gkVal = parseFloat(gk.value) || 0;
    const ckVal = parseFloat(ck.value) || 0;
    
    if (tx.value || gk.value || ck.value) {
        avgSpan.textContent = ((txVal + gkVal * 2 + ckVal * 3) / 6).toFixed(1);
        avgSpan.style.fontWeight = 'bold';
    } else {
        avgSpan.textContent = '-';
        avgSpan.style.fontWeight = '';
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

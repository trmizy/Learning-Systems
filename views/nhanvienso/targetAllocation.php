<?php
$pageTitle = 'Phân bổ chỉ tiêu tuyển sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="/assets/css/chitieu.css">

<div class="chitieu-container">
    <!-- Header Section -->
    <div class="chitieu-header">
        <h1>📊 Phân bổ chỉ tiêu tuyển sinh</h1>
        <p>Quản lý và phân bổ chỉ tiêu tuyển sinh cho các trường THPT trong hệ thống</p>
    </div>

    <!-- Alert Messages -->
    <?php
    $alerts = ['success'=>'✓','error'=>'✗','info'=>'ℹ'];
    foreach ($alerts as $k=>$icon) {
        if (isset($_SESSION[$k])) {
            echo "<div class=\"alert alert-$k\"><span>$icon</span> <span>".htmlspecialchars(
                $_SESSION[$k])."</span></div>";
            unset($_SESSION[$k]);
        }
    }
    ?>

    <!-- Statistics Section -->
    <?php 
    $conLai = $tongChiTieuPheDuyet - $tongChiTieuDaPhanBo;
    $conLaiText = $conLai > 0 ? "Còn lại: " . number_format($conLai) : ($conLai < 0 ? "Vượt: " . number_format(abs($conLai)) : "Đã đủ");
    $tyLe = $tongChiTieuPheDuyet > 0 ? round(($tongChiTieuDaPhanBo / $tongChiTieuPheDuyet) * 100, 1) : 0;
    ?>
    <div class="stats-grid">
        <div class="stat-card primary">
            <h3>Tổng trường THPT</h3>
            <p class="value"><?= count($danhSachTruong) ?></p>
            <p class="subtext">Trường trong hệ thống</p>
        </div>
        <div class="stat-card success">
            <h3>Tổng chỉ tiêu phê duyệt</h3>
            <p class="value"><?= number_format($tongChiTieuPheDuyet) ?></p>
            <p class="subtext">Năm học <?= htmlspecialchars($namHocSelected) ?></p>
        </div>
        <div class="stat-card warning">
            <h3>Đã phân bổ</h3>
            <p class="value"><?= number_format($tongChiTieuDaPhanBo) ?></p>
            <p class="subtext"><?= $conLaiText ?></p>
        </div>
        <div class="stat-card danger">
            <h3>Tỷ lệ phân bổ</h3>
            <p class="value"><?= $tyLe ?>%</p>
            <p class="subtext">So với chỉ tiêu phê duyệt</p>
        </div>
    </div>

    <!-- Main Allocation Form -->
    <div class="content-section">
        <h2 class="section-title">Phân bổ chỉ tiêu cho các trường</h2>

        <!-- Select Academic Year -->
        <form method="GET" action="/public/index.php" class="form-inline">
            <input type="hidden" name="action" value="targets_nhanvienso">
            <div class="form-group" style="flex: 2;">
                <label for="namHoc">Chọn năm học</label>
                <select name="namHoc" id="namHoc" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Chọn năm học --</option>
                    <?php foreach ($danhSachNamHoc as $nh): ?>
                        <option value="<?= htmlspecialchars($nh) ?>" <?= $nh === $namHocSelected ? 'selected' : '' ?>><?= htmlspecialchars($nh) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($namHocSelected) && !empty($danhSachTruong)): ?>
        <!-- Allocation Form -->
        <form method="POST" 
              action="/public/index.php?action=targets_nhanvienso&param=submit" 
              id="phanBoForm"
              onsubmit="return validateForm()">
            
            <input type="hidden" name="namHoc" value="<?php echo htmlspecialchars($namHocSelected); ?>">

            <div class="table-responsive">
                <table class="chitieu-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">STT</th>
                            <th style="width: 30%;">Thông tin trường</th>
                            <th style="width: 12%;">Chỉ tiêu năm trước</th>
                            <th style="width: 12%;">Gợi ý năm nay</th>
                            <th style="width: 16%;">Chỉ tiêu phân bổ</th>
                            <th style="width: 13%;">Đã phân bổ</th>
                            <th style="width: 12%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                                        <?php foreach ($danhSachTruong as $index => $truong): 
                                            $maTruong = $truong['maTruong'];
                                            $goiY = $goiYChiTieu[$maTruong] ?? 0;
                                            $daPhanBo = $chiTieuDaPhanBo[$maTruong] ?? 0;
                                            $namTruoc = $chiTieuNamTruoc[$maTruong] ?? 0;
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="school-info">
                                                    <span class="school-name"><?= htmlspecialchars($truong['tenTruong']) ?></span>
                                                    <span class="school-code">Mã: <?= htmlspecialchars($maTruong) ?></span>
                                                    <?= !empty($truong['diaChi']) ? '<span class="school-address">📍 '.htmlspecialchars($truong['diaChi']).'</span>' : '' ?>
                                                </div>
                                            </td>
                                            <td><span style="color:<?= $namTruoc>0?'#6c757d':'#999' ?>;font-weight:<?= $namTruoc>0?'500':'normal' ?>;"><?= $namTruoc>0?number_format($namTruoc):'Chưa có' ?></span></td>
                                            <td><span class="goiy-badge" data-goiy="<?= $goiY ?>">💡 <?= number_format($goiY) ?></span></td>
                                            <td>
                                                <input type="number" name="chitieu_<?= htmlspecialchars($maTruong) ?>" class="form-control chitieu-input" data-truong="<?= htmlspecialchars($maTruong) ?>" min="1" step="1" value="<?= $daPhanBo>0?$daPhanBo:'' ?>" placeholder="<?= $namHocSelected==='2023-2024'?'Đã cố định':'Nhập chỉ tiêu (số dương)' ?>" <?= $namHocSelected==='2023-2024'?'readonly disabled':'required' ?> oninput="validateNegativeInput(this); updateTotal();" onpaste="validateNegativeInput(this);" style="<?= $namHocSelected==='2023-2024'?'background-color:#f0f0f0;cursor:not-allowed;':'' ?>">
                                            </td>
                                            <td><span style="color:<?= $daPhanBo>0?'#28a745':'#999' ?>;font-weight:<?= $daPhanBo>0?'600':'normal' ?>;"><?= $daPhanBo>0?'✓ '.number_format($daPhanBo):'Chưa phân bổ' ?></span></td>
                                            <td><?= $namHocSelected==='2023-2024'?'<span style="color:#999;font-size:12px;">Đã cố định</span>':'<button type="button" class="btn-apply-all" onclick="applyGoiY(\''.htmlspecialchars($maTruong).'\', '. $goiY .')">Áp dụng gợi ý</button>' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Total Summary -->
            <div class="total-summary">
                <div class="summary-row">
                    <span class="summary-label">Tổng chỉ tiêu được phê duyệt:</span>
                    <span class="summary-value" id="tongChiTieuPheDuyet"><?= number_format($tongChiTieuPheDuyet) ?></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tổng chỉ tiêu đang nhập:</span>
                    <span class="summary-value" id="tongChiTieuDangNhap">0</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Còn lại / Vượt quá:</span>
                    <span class="summary-value" id="chiTieuConLai"><?= number_format($tongChiTieuPheDuyet) ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if ($namHocSelected === '2023-2024'): ?>
                <div class="alert alert-warning" style="margin-top: 20px;">
                    <span>🔒</span>
                    <span><strong>Năm học 2023-2024 đã được cố định.</strong><br>Đây là năm cơ sở để tính toán các năm tiếp theo. Không thể chỉnh sửa.</span>
                </div>
            <?php else: ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="huyNhapLieu()"><span>✗</span> Hủy phân bổ</button>
                    <button type="button" class="btn btn-danger" onclick="resetDatabase()"><span>🗑️</span> Xóa phân bổ</button>
                    <button type="button" class="btn btn-warning" onclick="applyAllGoiY()"><span>💡</span> Áp dụng tất cả gợi ý</button>
                    <button type="submit" class="btn btn-success" id="submitBtn"><span>✓</span> Xác nhận phân bổ</button>
                </div>
            <?php endif; ?>
        </form>
        <?php else: ?>
            <div class="alert alert-info">
                <span>ℹ</span>
                <span>Vui lòng chọn năm học để bắt đầu phân bổ chỉ tiêu.</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Section -->
    <?php if (!empty($lichSuPhanBo)): ?>
    <div class="content-section">
        <h2 class="section-title">Lịch sử phân bổ gần đây</h2>
        <ul class="history-list">
            <?php foreach ($lichSuPhanBo as $ls): ?>
            <li class="history-item">
                <div class="date">
                    📅 <?php echo date('d/m/Y H:i', strtotime($ls['ngayBanHanh'])); ?>
                </div>
                <div class="namhoc">
                    Năm học: <?php echo htmlspecialchars($ls['namHoc']); ?>
                </div>
                <div class="details">
                    Phân bổ cho <strong><?php echo $ls['soTruong']; ?></strong> trường | 
                    Tổng: <strong><?php echo number_format($ls['tongPhanBo']); ?></strong> học sinh /
                    <strong><?php echo number_format($ls['tongChiTieu']); ?></strong> chỉ tiêu
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>⚠️ Xác nhận hủy phân bổ</h2>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn hủy phân bổ chỉ tiêu tuyển sinh?</p>
            <p>Tất cả dữ liệu đã nhập sẽ không được lưu.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                Không, tiếp tục
            </button>
            <a href="/public/index.php?action=targets_nhanvienso&param=cancel&namHoc=<?php echo urlencode($namHocSelected); ?>" 
               class="btn btn-danger">
                Có, hủy phân bổ
            </a>
        </div>
    </div>
</div>

<script>
let tongChiTieuPheDuyet = <?= $tongChiTieuPheDuyet ?>;

document.addEventListener('DOMContentLoaded', function() { updateTotal(); });

function validateNegativeInput(input) {
    const value = parseFloat(input.value);
    if (value < 0) {
        input.style.borderColor = '#dc3545';
        input.style.backgroundColor = '#fff5f5';
        input.title = '❌ KHÔNG ĐƯỢC NHẬP SỐ ÂM! Vui lòng nhập số dương.';
        setTimeout(() => {
            if (parseFloat(input.value) < 0) {
                input.value = '';
                alert('❌ KHÔNG ĐƯỢC NHẬP SỐ ÂM!\n\nChỉ tiêu tuyển sinh phải là số dương (lớn hơn 0).');
            }
        }, 500);
    } else if (value === 0) {
        input.style.borderColor = '#ffc107';
        input.style.backgroundColor = '#fffbf0';
        input.title = '⚠️ Chỉ tiêu phải lớn hơn 0';
    } else {
        input.style.borderColor = '#28a745';
        input.style.backgroundColor = '';
        input.title = '';
    }
}

function updateTotal() {
    const inputs = document.querySelectorAll('.chitieu-input');
    let total = 0;
    inputs.forEach(input => {
        const value = parseInt(input.value) || 0;
        if (value < 0) {
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
        } else {
            input.style.borderColor = '#ddd';
            input.style.backgroundColor = '';
        }
        total += value;
    });

    const dangNhap = document.getElementById('tongChiTieuDangNhap');
    if (dangNhap) dangNhap.textContent = total.toLocaleString('vi-VN');
    
    const conLai = tongChiTieuPheDuyet - total;
    const conLaiEl = document.getElementById('chiTieuConLai');
    if (conLaiEl) {
        conLaiEl.textContent = Math.abs(conLai).toLocaleString('vi-VN');
        conLaiEl.className = conLai < 0 ? 'summary-value over-limit' : conLai > 0 ? 'summary-value under-limit' : 'summary-value';
    }
}

function validateForm() {
    const inputs = document.querySelectorAll('.chitieu-input');
    let total = 0, hasEmpty = false, hasNegative = false, hasZero = false, negativeSchools = [];

    inputs.forEach(input => {
        const value = input.value.trim();
        const schoolName = input.closest('tr').querySelector('td:first-child').textContent.trim();
        
        if (value === '') {
            hasEmpty = true;
            input.style.borderColor = '#dc3545';
        } else if (parseInt(value) < 0) {
            hasNegative = true;
            negativeSchools.push(schoolName);
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
        } else if (parseInt(value) === 0) {
            hasZero = true;
            input.style.borderColor = '#dc3545';
        } else {
            total += parseInt(value);
            input.style.borderColor = '#ddd';
            input.style.backgroundColor = '';
        }
    });

    if (hasEmpty) { alert('⚠️ Vui lòng nhập chỉ tiêu cho tất cả các trường!'); return false; }
    if (hasNegative) { alert('❌ KHÔNG ĐƯỢC NHẬP SỐ ÂM!\n\nCác trường có chỉ tiêu âm:\n' + negativeSchools.join('\n') + '\n\nVui lòng nhập số dương lớn hơn 0!'); return false; }
    if (hasZero) { alert('⚠️ Chỉ tiêu phải là số dương lớn hơn 0!\n\nKhông được nhập số 0.'); return false; }

    if (tongChiTieuPheDuyet > 0 && total > tongChiTieuPheDuyet) {
        if (!confirm(`⚠️ Tổng chỉ tiêu phân bổ (${total.toLocaleString('vi-VN')}) vượt quá tổng chỉ tiêu được phê duyệt (${tongChiTieuPheDuyet.toLocaleString('vi-VN')})!\n\nBạn có chắc chắn muốn tiếp tục?`)) return false;
    }

    return confirm(`✓ Xác nhận phân bổ chỉ tiêu tuyển sinh cho ${inputs.length} trường?\n\nTổng chỉ tiêu: ${total.toLocaleString('vi-VN')} học sinh`);
}

function applyGoiY(maTruong, goiY) {
    const input = document.querySelector(`input[data-truong="${maTruong}"]`);
    if (input) {
        input.value = goiY;
        updateTotal();
        input.style.borderColor = '#28a745';
        setTimeout(() => { input.style.borderColor = '#ddd'; }, 1000);
    }
}

function applyAllGoiY() {
    if (!confirm('💡 Áp dụng gợi ý chỉ tiêu cho tất cả các trường?')) return;
    document.querySelectorAll('.goiy-badge').forEach(badge => {
        const goiY = badge.getAttribute('data-goiy');
        const input = badge.closest('tr').querySelector('.chitieu-input');
        if (input) input.value = goiY;
    });
    updateTotal();
    alert('✓ Đã áp dụng gợi ý cho tất cả các trường!');
}

function huyNhapLieu() {
    if (!confirm('✗ Hủy thao tác nhập liệu?\n\nDữ liệu bạn vừa nhập sẽ bị xoá (chưa lưu vào database).')) return;
    document.querySelectorAll('.chitieu-input').forEach(input => {
        if (!input.disabled && !input.readOnly) input.value = '';
    });
    updateTotal();
    window.location.href = '/public/index.php?action=targets_nhanvienso';
}

function resetDatabase() {
    const namHoc = document.querySelector('select[name="namHoc"]').value;
    if (!namHoc) { alert('⚠️ Vui lòng chọn năm học trước!'); return; }
    if (!confirm('🗑️ XÓA TẤT CẢ chỉ tiêu đã phân bổ cho năm học ' + namHoc + '?\n\n⚠️ CẢNH BÁO: Dữ liệu trong DATABASE sẽ bị xóa vĩnh viễn!\n\nBạn có chắc chắn?')) return;
    if (!confirm('⚠️ XÁC NHẬN LẦN CUỐI:\n\nBạn THỰC SỰ muốn xóa toàn bộ phân bổ chỉ tiêu năm ' + namHoc + '?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/public/index.php?action=targets_nhanvienso&param=reset';
    const inputNamHoc = document.createElement('input');
    inputNamHoc.type = 'hidden';
    inputNamHoc.name = 'namHoc';
    inputNamHoc.value = namHoc;
    form.appendChild(inputNamHoc);
    document.body.appendChild(form);
    form.submit();
}

function showCancelModal() { document.getElementById('cancelModal').style.display = 'block'; }
function closeCancelModal() { document.getElementById('cancelModal').style.display = 'none'; }

window.onclick = function(event) {
    const modal = document.getElementById('cancelModal');
    if (event.target === modal) closeCancelModal();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
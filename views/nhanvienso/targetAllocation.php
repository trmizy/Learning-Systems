<?php
// filepath: views/nhanvienso/targetAllocation.php
// Trang phân bổ chỉ tiêu tuyển sinh

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
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>✓</span>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <span>✗</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info">
            <span>ℹ</span>
            <span><?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Statistics Section -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <h3>Tổng trường THPT</h3>
            <p class="value"><?php echo count($danhSachTruong); ?></p>
            <p class="subtext">Trường trong hệ thống</p>
        </div>

        <div class="stat-card success">
            <h3>Tổng chỉ tiêu phê duyệt</h3>
            <p class="value"><?php echo number_format($tongChiTieuPheDuyet); ?></p>
            <p class="subtext">Năm học <?php echo htmlspecialchars($namHocSelected); ?></p>
        </div>

        <div class="stat-card warning">
            <h3>Đã phân bổ</h3>
            <p class="value"><?php echo number_format($tongChiTieuDaPhanBo); ?></p>
            <p class="subtext">
                <?php 
                $conLai = $tongChiTieuPheDuyet - $tongChiTieuDaPhanBo;
                if ($conLai > 0) {
                    echo "Còn lại: " . number_format($conLai);
                } else if ($conLai < 0) {
                    echo "Vượt: " . number_format(abs($conLai));
                } else {
                    echo "Đã đủ";
                }
                ?>
            </p>
        </div>

        <div class="stat-card danger">
            <h3>Tỷ lệ phân bổ</h3>
            <p class="value">
                <?php 
                $tyLe = $tongChiTieuPheDuyet > 0 
                    ? round(($tongChiTieuDaPhanBo / $tongChiTieuPheDuyet) * 100, 1) 
                    : 0;
                echo $tyLe . '%';
                ?>
            </p>
            <p class="subtext">So với chỉ tiêu phê duyệt</p>
        </div>
    </div>

    <!-- Main Allocation Form -->
    <div class="content-section">
        <h2 class="section-title">Phân bổ chỉ tiêu cho các trường</h2>

        <!-- Select Academic Year -->
        <form method="GET" action="/public/index.php" class="form-inline">
            <input type="hidden" name="controller" value="chitieu">
            <input type="hidden" name="action" value="index">
            
            <div class="form-group" style="flex: 2;">
                <label for="namHoc">Chọn năm học</label>
                <select name="namHoc" id="namHoc" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Chọn năm học --</option>
                    <?php foreach ($danhSachNamHoc as $nh): ?>
                        <option value="<?php echo htmlspecialchars($nh); ?>" 
                            <?php echo $nh === $namHocSelected ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nh); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($namHocSelected) && !empty($danhSachTruong)): ?>
        <!-- Allocation Form -->
        <form method="POST" 
              action="/public/index.php?controller=targets&action=submit" 
              id="phanBoForm"
              onsubmit="return validateForm()">
            
            <input type="hidden" name="namHoc" value="<?php echo htmlspecialchars($namHocSelected); ?>">

            <div class="table-responsive">
                <table class="chitieu-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">STT</th>
                            <th style="width: 35%;">Thông tin trường</th>
                            <th style="width: 15%;">Gợi ý</th>
                            <th style="width: 20%;">Chỉ tiêu phân bổ</th>
                            <th style="width: 15%;">Đã phân bổ</th>
                            <th style="width: 10%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachTruong as $index => $truong): 
                            $maTruong = $truong['maTruong'];
                            $goiY = $goiYChiTieu[$maTruong] ?? 0;
                            $daPhanBo = $chiTieuDaPhanBo[$maTruong] ?? 0;
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <div class="school-info">
                                    <span class="school-name"><?php echo htmlspecialchars($truong['tenTruong']); ?></span>
                                    <span class="school-code">Mã: <?php echo htmlspecialchars($maTruong); ?></span>
                                    <?php if (!empty($truong['diaChi'])): ?>
                                        <span class="school-address">📍 <?php echo htmlspecialchars($truong['diaChi']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="goiy-badge" data-goiy="<?php echo $goiY; ?>">
                                    💡 <?php echo number_format($goiY); ?> học sinh
                                </span>
                            </td>
                            <td>
                                <input type="number" 
                                       name="chitieu_<?php echo htmlspecialchars($maTruong); ?>"
                                       class="form-control chitieu-input"
                                       data-truong="<?php echo htmlspecialchars($maTruong); ?>"
                                       min="1"
                                       step="1"
                                       value="<?php echo $daPhanBo > 0 ? $daPhanBo : ''; ?>"
                                       placeholder="<?php echo $namHocSelected === '2023-2024' ? 'Đã cố định' : 'Nhập chỉ tiêu (để trống = bỏ qua)'; ?>"
                                       <?php echo $namHocSelected === '2023-2024' ? 'readonly disabled' : ''; ?>
                                       oninput="validateNegativeInput(this); updateTotal();"
                                       onpaste="validateNegativeInput(this);"
                                       style="<?php echo $namHocSelected === '2023-2024' ? 'background-color: #f0f0f0; cursor: not-allowed;' : ''; ?>">
                                <small style="color: #666; font-size: 11px;">💡 Để trống nếu không phân bổ cho trường này</small>
                            </td>
                            <td>
                                <?php if ($daPhanBo > 0): ?>
                                    <span style="color: #28a745; font-weight: 600;">
                                        ✓ <?php echo number_format($daPhanBo); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">Chưa phân bổ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($namHocSelected === '2023-2024'): ?>
                                    <!-- Năm 2023-2024: Không có nút áp dụng -->
                                    <span style="color: #999; font-size: 12px;">Đã cố định</span>
                                <?php else: ?>
                                    <!-- Các năm khác: Hiển thị nút -->
                                    <button type="button" 
                                            class="btn-apply-all"
                                            onclick="applyGoiY('<?php echo htmlspecialchars($maTruong); ?>', <?php echo $goiY; ?>)">
                                        Áp dụng gợi ý
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Total Summary -->
            <div class="total-summary">
                <div class="summary-row">
                    <span class="summary-label">Tổng chỉ tiêu được phê duyệt:</span>
                    <span class="summary-value" id="tongChiTieuPheDuyet">
                        <?php echo number_format($tongChiTieuPheDuyet); ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tổng chỉ tiêu đang nhập:</span>
                    <span class="summary-value" id="tongChiTieuDangNhap">0</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Còn lại / Vượt quá:</span>
                    <span class="summary-value" id="chiTieuConLai">
                        <?php echo number_format($tongChiTieuPheDuyet); ?>
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if ($namHocSelected === '2023-2024'): ?>
                <!-- Năm 2023-2024: CHỈ hiển thị thông tin -->
                <div class="alert alert-warning" style="margin-top: 20px;">
                    <span>🔒</span>
                    <span><strong>Năm học 2023-2024 đã được cố định.</strong><br>
                    Đây là năm cơ sở để tính toán các năm tiếp theo. Không thể chỉnh sửa.</span>
                </div>
            <?php else: ?>
                <!-- Các năm khác: Cho phép phân bổ -->
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" onclick="showResetModal()" title="Reset toàn bộ phân bổ năm <?php echo htmlspecialchars($namHocSelected); ?>">
                        <span>🔄</span> Reset phân bổ
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="showCancelModal()">
                        <span>✗</span> Hủy phân bổ
                    </button>
                    <button type="button" class="btn btn-warning" onclick="applyAllGoiY()">
                        <span>💡</span> Áp dụng tất cả gợi ý
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <span>✓</span> Xác nhận phân bổ
                    </button>
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
                <div class="history-content">
                    <div class="date">
                        📅 <?php echo date('d/m/Y H:i', strtotime($ls['ngayBanHanh'])); ?>
                    </div>
                    <div class="namhoc">
                        Năm học: <strong><?php echo htmlspecialchars($ls['namHoc']); ?></strong>
                    </div>
                    <div class="details">
                        Phân bổ cho <strong><?php echo $ls['soTruong']; ?></strong> trường | 
                        Tổng: <strong><?php echo number_format($ls['tongPhanBo']); ?></strong> học sinh /
                        <strong><?php echo number_format($ls['tongChiTieu']); ?></strong> chỉ tiêu
                    </div>
                </div>
                <?php if ($ls['namHoc'] !== '2023-2024'): ?>
                <div class="history-actions">
                    <button type="button" 
                            class="btn-delete-history" 
                            onclick="confirmDeleteHistory('<?php echo htmlspecialchars($ls['namHoc']); ?>', '<?php echo htmlspecialchars($ls['ngayBanHanh']); ?>')"
                            title="Xóa lịch sử này">
                        🗑️ Xóa
                    </button>
                </div>
                <?php else: ?>
                <div class="history-actions">
                    <span style="color: #999; font-size: 12px;">🔒 Đã cố định</span>
                </div>
                <?php endif; ?>
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
            <a href="/public/index.php?controller=targets&action=cancel&namHoc=<?php echo urlencode($namHocSelected); ?>" 
               class="btn btn-danger">
                Có, hủy phân bổ
            </a>
        </div>
    </div>
</div>

<!-- Reset Confirmation Modal -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>🔄 Xác nhận reset phân bổ</h2>
        </div>
        <div class="modal-body">
            <p><strong>⚠️ CẢNH BÁO: Hành động này không thể hoàn tác!</strong></p>
            <p>Bạn có chắc chắn muốn xóa TOÀN BỘ phân bổ chỉ tiêu của năm học <strong><?php echo htmlspecialchars($namHocSelected); ?></strong>?</p>
            <p>Tất cả dữ liệu phân bổ cho các trường sẽ bị xóa vĩnh viễn.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeResetModal()">
                Hủy
            </button>
            <form method="POST" action="/public/index.php?controller=targets&action=reset&namHoc=<?php echo urlencode($namHocSelected); ?>" style="display: inline;">
                <button type="submit" class="btn btn-danger">
                    Xác nhận reset
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background: #f9f9f9;
    border-left: 4px solid #007bff;
    border-radius: 4px;
}

.history-content {
    flex: 1;
}

.history-actions {
    margin-left: 15px;
}

.btn-delete-history {
    padding: 6px 12px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s;
}

.btn-delete-history:hover {
    background: #c82333;
    transform: scale(1.05);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.modal-footer button,
.modal-footer .btn {
    margin-left: 10px;
}
</style>

<script>
const tongChiTieuPheDuyet = <?php echo $tongChiTieuPheDuyet; ?>;

function validateNegativeInput(input) {
    const value = parseFloat(input.value);
    
    if (value < 0) {
        input.style.borderColor = '#dc3545';
        input.value = '';
        return;
    }
    
    input.style.borderColor = value > 0 ? '#28a745' : '#ddd';
    updateTotal();
}

function updateTotal() {
    const inputs = document.querySelectorAll('.chitieu-input:not([disabled])');
    let total = 0;
    let countFilled = 0;
    
    inputs.forEach(input => {
        const value = parseInt(input.value) || 0;
        if (value > 0) {
            total += value;
            countFilled++;
        }
    });

    document.getElementById('tongChiTieuDangNhap').textContent = total.toLocaleString('vi-VN') + 
        ' (' + countFilled + ' trường)';
    
    const conLai = tongChiTieuPheDuyet - total;
    const conLaiElement = document.getElementById('chiTieuConLai');
    
    conLaiElement.textContent = Math.abs(conLai).toLocaleString('vi-VN');
    conLaiElement.className = 'summary-value' + (conLai < 0 ? ' over-limit' : conLai > 0 ? ' under-limit' : '');
}

function validateForm() {
    const inputs = document.querySelectorAll('.chitieu-input:not([disabled])');
    let totalFilled = 0;
    let totalValue = 0;
    let hasInvalid = false;

    inputs.forEach(input => {
        const value = input.value.trim();
        
        if (value !== '') {
            const numValue = parseInt(value);
            
            if (numValue <= 0) {
                hasInvalid = true;
                input.style.borderColor = '#dc3545';
            } else {
                totalFilled++;
                totalValue += numValue;
                input.style.borderColor = '#ddd';
            }
        }
    });

    if (totalFilled === 0) {
        alert('⚠️ Vui lòng nhập chỉ tiêu cho ít nhất 1 trường!');
        return false;
    }

    if (hasInvalid) {
        alert('⚠️ Chỉ tiêu phải là số dương! Vui lòng kiểm tra lại các giá trị đã nhập.');
        return false;
    }

    return confirm(`✓ Xác nhận phân bổ cho ${totalFilled} trường?\n\nTổng chỉ tiêu: ${totalValue.toLocaleString('vi-VN')}`);
}

function applyGoiY(maTruong, goiY) {
    const input = document.querySelector(`input[data-truong="${maTruong}"]`);
    if (input && !input.disabled) {
        input.value = goiY;
        input.style.borderColor = '#28a745';
        updateTotal();
        setTimeout(() => { input.style.borderColor = '#ddd'; }, 1000);
    }
}

function applyAllGoiY() {
    if (!confirm('💡 Áp dụng gợi ý cho TẤT CẢ các trường?')) return;
    
    document.querySelectorAll('.goiy-badge').forEach(badge => {
        const goiY = badge.getAttribute('data-goiy');
        const input = badge.closest('tr').querySelector('.chitieu-input');
        if (input && !input.disabled) {
            input.value = goiY;
        }
    });
    
    updateTotal();
    alert('✓ Đã áp dụng gợi ý cho tất cả các trường!');
}

function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'block';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

function showResetModal() {
    document.getElementById('resetModal').style.display = 'block';
}

function closeResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}

function confirmDeleteHistory(namHoc, ngayBanHanh) {
    if (confirm(`⚠️ Xóa lịch sử phân bổ năm ${namHoc}?\n\nHành động này không thể hoàn tác!`)) {
        window.location.href = `/public/index.php?controller=targets&action=deleteHistory&namHoc=${encodeURIComponent(namHoc)}&ngayBanHanh=${encodeURIComponent(ngayBanHanh)}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const cancelModal = document.getElementById('cancelModal');
    const resetModal = document.getElementById('resetModal');
    
    if (event.target === cancelModal) {
        closeCancelModal();
    }
    if (event.target === resetModal) {
        closeResetModal();
    }
}

document.addEventListener('DOMContentLoaded', updateTotal);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

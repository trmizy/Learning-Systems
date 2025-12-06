<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['ph']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Nguyện Vọng</title>
    <link rel="stylesheet" href="../../assets/css/wishRegistration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="wish-registration-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap"></i>Đăng Ký Nguyện Vọng</h1>
            <p class="page-description">Vui lòng điền đầy đủ thông tin thí sinh và chọn tối đa 3 nguyện vọng theo thứ tự ưu tiên</p>
        </div>

        <?php if ($successData): ?>
            <div class="success-summary">
                <h4><i class="fas fa-check-circle"></i> Đăng Ký Nguyện Vọng Thành Công!</h4>
                <div class="summary-item"><strong>Mã thí sinh:</strong> <span><?= htmlspecialchars($successData['maThiSinh']) ?></span></div>
                <div class="summary-item"><strong>Họ tên:</strong> <span><?= htmlspecialchars($successData['hoTen']) ?></span></div>
                <div class="summary-item"><strong>Số CCCD:</strong> <span><?= htmlspecialchars($successData['soCCCD']) ?></span></div>
                <div class="summary-item"><strong>Số điện thoại:</strong> <span><?= htmlspecialchars($successData['soDienThoai']) ?></span></div>
                
                <div class="registered-wishes">
                    <h5><i class="fas fa-heart"></i> Các nguyện vọng đã đăng ký:</h5>
                    <?php foreach ($successData['nguyenVong'] as $index => $nv): ?>
                        <div class="registered-wish-item">
                            <div class="wish-content">
                                <div class="wish-info">
                                    <div class="wish-title">
                                        <span class="priority">NV<?= $index + 1 ?></span>
                                        <strong><?= htmlspecialchars($nv['tenTruong']) ?></strong>
                                    </div>
                                    <div class="wish-details">
                                        <div><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> <?= htmlspecialchars($nv['diaChi']) ?></div>
                                        <div><i class="fas fa-code"></i> <strong>Mã nguyện vọng:</strong> <?= htmlspecialchars($nv['maNguyenVong']) ?></div>
                                        <div><i class="fas fa-school"></i> <strong>Mã trường:</strong> <?= htmlspecialchars($nv['maTruong']) ?></div>
                                    </div>
                                </div>
                                <div class="wish-priority-badge">Ưu tiên #<?= htmlspecialchars($nv['thuTuUuTien']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="action-center">
                    <a href="/public/index.php?action=wishRegistration_ph&param=new" class="btn btn-primary"><i class="fas fa-plus"></i> Đăng Ký Thí Sinh Khác</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <div class="container my-2">
            <?php foreach ($messages as $message):
                $text = $message['text'] ?? '';
                $type = $message['type'] ?? 'info';

                if (mb_stripos($text, 'trùng trường') !== false || mb_stripos($text, 'trùng') !== false) {
                    $text = 'Lỗi: Bạn đã chọn cùng một trường ở nhiều nguyện vọng. Vui lòng chọn các trường khác nhau cho mỗi nguyện vọng.';
                    $type = 'danger';
                } elseif (mb_stripos($text, 'Thứ tự ưu tiên bị trùng') !== false || mb_stripos($text, 'ưu tiên') !== false) {
                    $text = 'Lỗi: Thứ tự ưu tiên bị trùng. Vui lòng đặt các thứ tự ưu tiên khác nhau (1, 2, 3).';
                    $type = 'danger';
                }

                $icon = $type === 'success' ? 'check-circle' : ($type === 'danger' ? 'exclamation-circle' : 'info-circle'); ?>
                <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $icon ?> me-2"></i>
                    <?= htmlspecialchars($text) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Inline form alerts (client-side) -->
        <div id="formAlerts" class="container my-2"></div>

        <!-- Registration Form -->
        <form id="wishRegistrationForm" method="POST" action="/public/index.php?action=wishRegistration_ph">
            <input type="hidden" name="action" value="register_wish">

            <!-- Student Information Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i>Thông Tin Thí Sinh</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="hoTen">Họ và Tên <span class="required">*</span></label>
                        <input type="text" class="form-control" id="hoTen" name="hoTen" required placeholder="Nhập họ và tên đầy đủ">
                        <small class="form-text">Vui lòng nhập họ tên đầy đủ của thí sinh</small>
                    </div>

                    <div class="form-group">
                        <label for="soCCCD">Số CCCD <span class="required">*</span></label>
                        <input type="text" class="form-control" id="soCCCD" name="soCCCD" required pattern="[0-9]{12}" maxlength="12" placeholder="Nhập 12 số CCCD">
                        <small class="form-text">Số CCCD phải có đúng 12 chữ số</small>
                    </div>

                    <div class="form-group">
                        <label for="soDienThoai">Số Điện Thoại <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="soDienThoai" name="soDienThoai" required pattern="0[0-9]{9}" maxlength="10" placeholder="Nhập số điện thoại (10 số)">
                        <small class="form-text">Số điện thoại phải có 10 chữ số và bắt đầu bằng số 0</small>
                    </div>
                </div>
            </div>

            <!-- Wishes Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-heart"></i>Danh Sách Nguyện Vọng (Tối đa 3)</h3>
                </div>
                <div class="card-body">
                    <div class="wish-list" id="wishList">
                        <!-- Initial wish item -->
                        <div class="wish-item" data-wish-index="0">
                            <div class="wish-item-header">
                                <span class="wish-number">1</span>
                            </div>
                            <div class="wish-item-body">
                                <div class="form-group">
                                    <label>Trường <span class="required">*</span></label>
                                    <select class="form-control wish-school" name="nguyenVong[0][maTruong]" required>
                                        <option value="">-- Chọn trường --</option>
                                        <?php foreach ($danhSachTruong as $truong): ?>
                                            <option value="<?= htmlspecialchars($truong['maTruong']) ?>"><?= htmlspecialchars($truong['tenTruong']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Thứ tự ưu tiên <span class="required">*</span></label>
                                    <select class="form-control wish-priority" name="nguyenVong[0][thuTuUuTien]" required>
                                        <option value="">-- Chọn --</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-center">
                        <button type="button" id="btnAddWish" class="btn btn-outline">
                            <i class="fas fa-plus"></i> Thêm Nguyện Vọng
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Đăng Ký Nguyện Vọng
                </button>
                <button type="button" class="btn btn-secondary" onclick="confirmCancel()">
                    <i class="fas fa-times"></i> Hủy Bỏ
                </button>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    
    <script>
    // Wish Registration Form
    let wishCount = 1;
    const MAX_WISHES = 3;
    const schools = <?= json_encode($danhSachTruong) ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnAddWish')?.addEventListener('click', () => {
            if (wishCount >= MAX_WISHES) {
                showInlineAlert('Bạn chỉ có thể đăng ký tối đa ' + MAX_WISHES + ' nguyện vọng!', 'danger');
                return;
            }
            addWishItem();
        });
        
        document.getElementById('wishRegistrationForm')?.addEventListener('submit', validateForm);
        updateAddButtonState();
    });
    
    function addWishItem() {
        const wishList = document.getElementById('wishList');
        const wishIndex = wishCount;
        const wishItem = document.createElement('div');
        wishItem.className = 'wish-item';
        wishItem.setAttribute('data-wish-index', wishIndex);
        
        wishItem.innerHTML = `
            <div class="wish-item-header">
                <span class="wish-number">${wishIndex + 1}</span>
                <button type="button" class="btn-remove-wish" onclick="removeWishItem(this)">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
            <div class="wish-item-body">
                <div class="form-group">
                    <label>Trường <span class="required">*</span></label>
                    <select class="form-control wish-school" name="nguyenVong[${wishIndex}][maTruong]" required>
                        <option value="">-- Chọn trường --</option>
                        ${schools.map(s => `<option value="${s.maTruong}">${s.tenTruong}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Thứ tự ưu tiên <span class="required">*</span></label>
                    <select class="form-control wish-priority" name="nguyenVong[${wishIndex}][thuTuUuTien]" required>
                        <option value="">-- Chọn --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>`;
        
        wishList.appendChild(wishItem);
        wishCount++;
        updateAddButtonState();
    }
    
    function removeWishItem(button) {
        button.closest('.wish-item').remove();
        wishCount--;
        
        document.querySelectorAll('.wish-item').forEach((item, index) => {
            item.setAttribute('data-wish-index', index);
            item.querySelector('.wish-number').textContent = index + 1;
            item.querySelector('.wish-school').name = `nguyenVong[${index}][maTruong]`;
            item.querySelector('.wish-priority').name = `nguyenVong[${index}][thuTuUuTien]`;
        });
        
        wishCount = document.querySelectorAll('.wish-item').length;
        updateAddButtonState();
    }
    
    function updateAddButtonState() {
        const btnAdd = document.getElementById('btnAddWish');
        if (btnAdd) {
            btnAdd.disabled = wishCount >= MAX_WISHES;
            btnAdd.style.opacity = wishCount >= MAX_WISHES ? '0.5' : '1';
            btnAdd.style.cursor = wishCount >= MAX_WISHES ? 'not-allowed' : 'pointer';
        }
    }
    
    function confirmCancel() {
        if (confirm('Bạn có chắc chắn muốn hủy bỏ đăng ký? Tất cả thông tin đã nhập sẽ bị mất.')) {
            window.location.href = '/public/index.php?action=ph-dashboard';
        }
    }
    
    function validateForm(e) {
        const selectedSchools = [];
        const selectedPriorities = [];
        
        // Check duplicate schools
        for (let select of document.querySelectorAll('.wish-school')) {
            const value = select.value;
            if (value) {
                if (selectedSchools.includes(value)) {
                    e.preventDefault();
                    showInlineAlert('Không thể chọn trùng trường! Vui lòng chọn các trường khác nhau.', 'danger');
                    return false;
                }
                selectedSchools.push(value);
            }
        }
        
        // Check duplicate priorities
        for (let select of document.querySelectorAll('.wish-priority')) {
            const value = select.value;
            if (value) {
                if (selectedPriorities.includes(value)) {
                    e.preventDefault();
                    showInlineAlert('Thứ tự ưu tiên không được trùng! Vui lòng chọn thứ tự khác nhau.', 'danger');
                    return false;
                }
                selectedPriorities.push(value);
            }
        }
        
        // Confirm before submit
        if (!confirm('Bạn có chắc chắn muốn đăng ký nguyện vọng với thông tin này?')) {
            e.preventDefault();
            return false;
        }
        return true;
    }
    
    function showInlineAlert(message, type = 'danger') {
        const container = document.getElementById('formAlerts');
        if (!container) return;
        // Clear existing alerts
        container.innerHTML = '';
        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show`;
        wrapper.setAttribute('role', 'alert');
        wrapper.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close';
        btn.setAttribute('data-bs-dismiss', 'alert');
        btn.setAttribute('aria-label', 'Close');
        wrapper.appendChild(btn);
        container.appendChild(wrapper);
        // Scroll to alert
        wrapper.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
    </script>
</body>
</html>

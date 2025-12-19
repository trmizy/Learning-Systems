<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="/assets/css/schoolAccount.css">

<div class="school-account-container">
    <div class="page-header">
        <h1><i class="fas fa-user-plus"></i> Cấp tài khoản cho trường</h1>
    </div>

    <?php if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])): 
        $icons = ['success'=>'check-circle', 'danger'=>'exclamation-circle', 'warning'=>'exclamation-triangle', 'info'=>'info-circle'];
        foreach ($_SESSION['messages'] as $msg): ?>
            <div class="alert alert-<?= $msg['type'] ?>">
                <i class="fas fa-<?= $icons[$msg['type']] ?? 'info-circle' ?>"></i>
                <?= htmlspecialchars($msg['text']) ?>
            </div>
        <?php endforeach; unset($_SESSION['messages']); endif; ?>

    <?php if ($newAccountInfo): ?>
        <div class="account-info-box">
            <h4><i class="fas fa-check-circle"></i> Thông tin tài khoản đã tạo</h4>
            <div class="account-info-item"><strong>Trường:</strong> <span><?= htmlspecialchars($newAccountInfo['tenTruong']) ?></span></div>
            <div class="account-info-item"><strong>Mã trường:</strong> <span class="value"><?= htmlspecialchars($newAccountInfo['maTruong']) ?></span></div>            
            <!-- ⚠️ THÊM: Hiển thị mã tài khoản -->
            <div class="account-info-item"><strong>Mã tài khoản:</strong> <span class="value"><?= htmlspecialchars($newAccountInfo['maTaiKhoan']) ?></span></div>
            
            <div class="account-info-item"><strong>Tên đăng nhập:</strong> <span class="value"><?= htmlspecialchars($newAccountInfo['tenDangNhap']) ?></span></div>
            <div class="account-info-item"><strong>Mật khẩu:</strong> <span class="value"><?= htmlspecialchars($newAccountInfo['matKhau']) ?></span></div>
            <div class="account-info-item"><strong>Email:</strong> <span><?= htmlspecialchars($newAccountInfo['email']) ?></span></div>
            <div class="account-info-item"><strong>Vai trò:</strong> <span class="badge badge-primary">Admin (Phòng giáo vụ)</span></div>
            
            <p style="margin-top: 15px; color: #00695c;"><i class="fas fa-info-circle"></i> Thông tin đã được gửi đến email của trường. Vui lòng thông báo cho trường kiểm tra hộp thư.</p>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3><i class="fas fa-school"></i> Danh sách các trường THPT</h3>
            <button type="button" class="btn btn-success" onclick="openAddSchoolModal()">
                <i class="fas fa-plus"></i> Thêm trường mới
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($danhSachTruong)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Không có dữ liệu trường trong hệ thống.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="school-table">
                        <thead>
                            <tr>
                                <th width="8%">Mã trường</th>
                                <th width="22%">Tên trường</th>
                                <th width="25%">Địa chỉ</th>
                                <th width="15%">Email</th>
                                <th width="10%">Số điện thoại</th>
                                <th width="10%">Trạng thái</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachTruong as $truong): 
                                $daCap = $truong['trangThaiCapTaiKhoan'] === 'Đã cấp';
                                $coEmail = !empty($truong['email']);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($truong['maTruong']) ?></td>
                                    <td><strong><?= htmlspecialchars($truong['tenTruong']) ?></strong></td>
                                    <td><?= htmlspecialchars($truong['diaChi']) ?></td>
                                    <td>
                                        <?php if ($coEmail): ?>
                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($truong['email']) ?>
                                        <?php else: ?>
                                            <span style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($truong['soDienThoai']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $daCap ? 'success' : 'warning' ?>">
                                            <i class="fas fa-<?= $daCap ? 'check' : 'times' ?>"></i> 
                                            <?= $daCap ? 'Đã cấp' : 'Chưa cấp' ?>
                                        </span>
                                        <?php if (!$coEmail && !$daCap): ?>
                                            <br><small style="color: #dc3545;">Thiếu email</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($daCap): ?>
                                            <button class="btn btn-sm btn-danger btn-delete-account" 
                                                    data-matruong="<?= htmlspecialchars($truong['maTruong']) ?>" 
                                                    data-tentruong="<?= htmlspecialchars($truong['tenTruong']) ?>">
                                                <i class="fas fa-trash"></i> Xóa TK
                                            </button>
                                        <?php elseif (!$coEmail): ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Cần bổ sung email trước">
                                                <i class="fas fa-ban"></i> Thiếu email
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary btn-create-account" 
                                                    data-matruong="<?= htmlspecialchars($truong['maTruong']) ?>" 
                                                    data-tentruong="<?= htmlspecialchars($truong['tenTruong']) ?>" 
                                                    data-email="<?= htmlspecialchars($truong['email']) ?>">
                                                <i class="fas fa-user-plus"></i> Cấp tài khoản
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal thêm trường mới -->
<div id="addSchoolModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Thêm trường mới vào hệ thống</h3>
            <span class="close" onclick="closeAddSchoolModal()">&times;</span>
        </div>
        <form method="POST" action="/public/index.php?action=schoolAccount_nhanvienso" id="addSchoolForm">
            <input type="hidden" name="action" value="add_school">
            <div class="modal-body">
                <div class="form-group">
                    <label for="tenTruong">Tên trường <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="tenTruong" name="tenTruong" required 
                           placeholder="Ví dụ: THPT Lê Hồng Phong">
                </div>
                
                <div class="form-group">
                    <label for="diaChi">Địa chỉ</label>
                    <input type="text" class="form-control" id="diaChi" name="diaChi" 
                           placeholder="Ví dụ: 240 Nguyễn Thị Minh Khai, Quận 3, TP. HCM">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span style="color: red;">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="Ví dụ: thptlehongphong@moet.edu.vn">
                    <small class="form-text">Email sẽ được dùng làm tên đăng nhập khi cấp tài khoản</small>
                </div>
                
                <div class="form-group">
                    <label for="soDienThoai">Số điện thoại</label>
                    <input type="tel" class="form-control" id="soDienThoai" name="soDienThoai" 
                           pattern="[0-9]{10,11}" placeholder="Ví dụ: 02838293785">
                    <small class="form-text">10-11 chữ số</small>
                </div>
                
                <div class="alert alert-info" style="margin-top: 15px;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Lưu ý:</strong> Mã trường sẽ được tạo tự động theo format TRXXX (ví dụ: TR001, TR002...)
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu thông tin
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeAddSchoolModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal cấp tài khoản -->
<div id="createAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Xác nhận cấp tài khoản</h3>
            <span class="close" onclick="closeCreateModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn cấp tài khoản cho trường:</p>
            <p><strong id="createSchoolName"></strong></p>
            <p>Thông tin tài khoản sẽ được gửi đến email: <strong id="createSchoolEmail"></strong></p>
        </div>
        <div class="modal-footer">
            <form method="POST" action="/public/index.php?action=schoolAccount_nhanvienso" id="createAccountForm">
                <input type="hidden" name="action" value="create_account">
                <input type="hidden" name="maTruong" id="createMaTruong">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Xác nhận
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal xóa tài khoản -->
<div id="deleteAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Xác nhận xóa tài khoản</h3>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa tài khoản của trường:</p>
            <p><strong id="deleteSchoolName"></strong></p>
            <p style="color: #dc3545;">
                <i class="fas fa-exclamation-circle"></i> 
                <strong>Lưu ý:</strong> Trường sẽ không thể đăng nhập vào hệ thống sau khi xóa tài khoản.
            </p>
        </div>
        <div class="modal-footer">
            <form method="POST" action="/public/index.php?action=schoolAccount_nhanvienso" id="deleteAccountForm">
                <input type="hidden" name="action" value="delete_account">
                <input type="hidden" name="maTruong" id="deleteMaTruong">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Xác nhận xóa
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Mở modal thêm trường mới
function openAddSchoolModal() {
    document.getElementById('addSchoolModal').classList.add('show');
    // Reset form
    document.getElementById('addSchoolForm').reset();
}

// Đóng modal thêm trường
function closeAddSchoolModal() {
    document.getElementById('addSchoolModal').classList.remove('show');
}

// Mở modal cấp tài khoản
function showModal(type, data) {
    if (type === 'create') {
        document.getElementById('createMaTruong').value = data.maTruong;
        document.getElementById('createSchoolName').textContent = data.tenTruong;
        document.getElementById('createSchoolEmail').textContent = data.email;
        document.getElementById('createAccountModal').classList.add('show');
    } else {
        document.getElementById('deleteMaTruong').value = data.maTruong;
        document.getElementById('deleteSchoolName').textContent = data.tenTruong;
        document.getElementById('deleteAccountModal').classList.add('show');
    }
}

// Xử lý click nút "Cấp tài khoản" ở mỗi hàng
document.querySelectorAll('.btn-create-account').forEach(btn => {
    btn.addEventListener('click', function() {
        showModal('create', {
            maTruong: this.dataset.matruong, 
            tenTruong: this.dataset.tentruong, 
            email: this.dataset.email
        });
    });
});

// Xử lý click nút "Xóa TK"
document.querySelectorAll('.btn-delete-account').forEach(btn => {
    btn.addEventListener('click', function() {
        showModal('delete', {
            maTruong: this.dataset.matruong, 
            tenTruong: this.dataset.tentruong
        });
    });
});

function closeCreateModal() { 
    document.getElementById('createAccountModal').classList.remove('show'); 
}

function closeDeleteModal() { 
    document.getElementById('deleteAccountModal').classList.remove('show'); 
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    if (event.target.id === 'addSchoolModal') closeAddSchoolModal();
    if (event.target.id === 'createAccountModal') closeCreateModal();
    if (event.target.id === 'deleteAccountModal') closeDeleteModal();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
require_once __DIR__ . '/../../layouts/header.php';
// If view is opened directly (not via module), initialize controller to provide data
if (!isset($controller) || !is_object($controller)) {
    require_once dirname(__DIR__, 3) . '/config/database.php';
    require_once dirname(__DIR__, 3) . '/controllers/admin/quanLyTaiKhoanController.php';
    $db = Database::getInstance()->getConnection();
    $controller = new QuanLyTaiKhoanController($db);
}

// Prepare list using query parameters when not provided by module
$search = $_GET['search'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';
$taiKhoanList = $controller->getDanhSachTaiKhoan($search, $trangThai);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-users"></i> Quản Lý Tài Khoản</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="/modules/admin/quanLyTaiKhoan.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo Tài Khoản Mới
            </a>
        </div>
    </div>

    <!-- Tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="list">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo tên đăng nhập, email hoặc mã tài khoản" 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="trangThai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="ACTIVE" <?php echo ($_GET['trangThai'] ?? '') === 'ACTIVE' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="INACTIVE" <?php echo ($_GET['trangThai'] ?? '') === 'INACTIVE' ? 'selected' : ''; ?>>Đã vô hiệu hóa</option>
                        <option value="SUSPENDED" <?php echo ($_GET['trangThai'] ?? '') === 'SUSPENDED' ? 'selected' : ''; ?>>Tạm khóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thông báo -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['messageType'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['messageType']); ?>
    <?php endif; ?>

    <!-- Danh sách tài khoản -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Tài Khoản</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Số Điện Thoại</th>
                        <th>Vai Trò</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($taiKhoanList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Không có tài khoản nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($taiKhoanList as $tk): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($tk['maTaiKhoan']); ?></code></td>
                                <td><?php echo htmlspecialchars($tk['tenDangNhap']); ?></td>
                                <td><?php echo htmlspecialchars($tk['email']); ?></td>
                                <td><?php echo htmlspecialchars($tk['soDienThoai'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    $vaiTroArray = explode(', ', $tk['vaiTro'] ?? '');
                                    foreach ($vaiTroArray as $vt):
                                        if (!empty($vt)):
                                            $vaiTroLabel = [
                                                'admin' => 'Quản trị',
                                                'bgh' => 'BGH',
                                                'ph' => 'Phó HT',
                                                'gvbm' => 'GVBM',
                                                'gv' => 'GV',
                                                'nhanvienso' => 'NV Sở',
                                                'hs' => 'HS'
                                            ];
                                            $label = $vaiTroLabel[$vt] ?? $vt;
                                            $colors = [
                                                'admin' => 'danger',
                                                'bgh' => 'warning',
                                                'ph' => 'info',
                                                'gvbm' => 'primary',
                                                'gv' => 'secondary',
                                                'nhanvienso' => 'dark',
                                                'hs' => 'success'
                                            ];
                                            $color = $colors[$vt] ?? 'secondary';
                                    ?>
                                        <span class="badge bg-<?php echo $color; ?>"><?php echo $label; ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = $tk['trangThai'] === 'ACTIVE' ? 'success' : ($tk['trangThai'] === 'INACTIVE' ? 'danger' : 'warning');
                                    $statusLabel = $tk['trangThai'] === 'ACTIVE' ? 'Hoạt động' : ($tk['trangThai'] === 'INACTIVE' ? 'Vô hiệu' : 'Tạm khóa');
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                </td>
                                <td>
                                    <a href="/modules/admin/quanLyTaiKhoan.php?action=edit&maTaiKhoan=<?php echo urlencode($tk['maTaiKhoan']); ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/modules/admin/quanLyTaiKhoan.php?action=permissions&maTaiKhoan=<?php echo urlencode($tk['maTaiKhoan']); ?>" 
                                       class="btn btn-sm btn-info" title="Quản lý quyền">
                                        <i class="fas fa-lock"></i>
                                    </a>
                                    <?php if ($tk['vaiTro'] !== 'admin'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="xoaTaiKhoan('<?php echo htmlspecialchars($tk['maTaiKhoan']); ?>', '<?php echo htmlspecialchars($tk['tenDangNhap']); ?>')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác Nhận Xóa Tài Khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa tài khoản <strong id="accountToDelete"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAccount = null;

function xoaTaiKhoan(maTaiKhoan, tenDangNhap) {
    currentAccount = maTaiKhoan;
    document.getElementById('accountToDelete').textContent = tenDangNhap;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentAccount) {
        const form = document.createElement('form');
        form.method = 'POST';
        // Force posting to module root to avoid carrying over query params (e.g. trangThai filter)
        form.action = '/modules/admin/quanLyTaiKhoan.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="maTaiKhoan" value="${currentAccount}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
});
</script>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

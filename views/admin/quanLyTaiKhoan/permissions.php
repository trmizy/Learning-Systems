<?php
require_once __DIR__ . '/../../layouts/header.php';

// If view is opened directly (not via module), initialize controller to provide data
if (!isset($controller) || !is_object($controller)) {
    require_once dirname(__DIR__, 3) . '/config/database.php';
    require_once dirname(__DIR__, 3) . '/controllers/admin/quanLyTaiKhoanController.php';
    $db = Database::getInstance()->getConnection();
    $controller = new QuanLyTaiKhoanController($db);
}

if (!isset($_GET['maTaiKhoan'])) {
    $_SESSION['message'] = 'Mã tài khoản không được cung cấp';
    $_SESSION['messageType'] = 'danger';
    header('Location: /modules/admin/quanLyTaiKhoan.php?action=list');
    exit;
}

$taiKhoan = $controller->getChiTietTaiKhoan($_GET['maTaiKhoan']);
if (!$taiKhoan) {
    $_SESSION['message'] = 'Không tìm thấy tài khoản';
    $_SESSION['messageType'] = 'danger';
    header('Location: /modules/admin/quanLyTaiKhoan.php?action=list');
    exit;
}

$vaiTroHienTai = $controller->getDanhSachVaiTroTaiKhoan($_GET['maTaiKhoan']);
$danhSachVaiTro = $controller->getDanhSachVaiTroCoSan();
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-lock"></i> Quản Lý Quyền & Vai Trò</h2>
            <p class="text-muted">Tài khoản: <strong><?php echo htmlspecialchars($taiKhoan['tenDangNhap']); ?></strong></p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/modules/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['messageType'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['messageType']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh Sách Vai Trò Có Sẵn</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formVaiTro">
                        <input type="hidden" name="action" value="updatePermissions">
                        <input type="hidden" name="maTaiKhoan" value="<?php echo htmlspecialchars($_GET['maTaiKhoan']); ?>">

                        <div class="row">
                            <?php foreach ($danhSachVaiTro as $key => $label): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="vaiTro[]" 
                                                       value="<?php echo $key; ?>" id="vaiTro_<?php echo $key; ?>"
                                                       <?php echo in_array($key, $vaiTroHienTai) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="vaiTro_<?php echo $key; ?>">
                                                    <strong><?php echo $label; ?></strong>
                                                </label>
                                            </div>
                                            <p class="mb-0 mt-2 text-muted small">
                                                <?php 
                                                $moTa = [
                                                    'admin' => 'Quản trị toàn bộ hệ thống, có quyền truy cập tất cả tính năng',
                                                    'bgh' => 'Ban Giám Hiệu: Quản lý phê duyệt tổ hợp môn',
                                                    'ph' => 'Phó Hiệu Trưởng: Hỗ trợ quản lý nhân sự',
                                                    'gvbm' => 'Giáo Viên Bộ Môn: Quản lý điểm, lớp học',
                                                    'gv' => 'Giáo Viên: Nhập điểm, xem danh sách lớp',
                                                    'nhanvienso' => 'Nhân Viên Sở: Hỗ trợ quản lý hành chính',
                                                    'hs' => 'Học Sinh: Xem thông tin cá nhân, kết quả học tập'
                                                ];
                                                echo $moTa[$key] ?? 'Không có mô tả';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-exclamation-circle"></i> 
                            <strong>Lưu ý:</strong> Một tài khoản có thể có nhiều vai trò. Hãy chọn vai trò phù hợp với vị trí công việc của người dùng.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/modules/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập Nhật Quyền
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông Tin Tài Khoản</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Mã:</dt>
                        <dd class="col-sm-7"><code><?php echo htmlspecialchars($taiKhoan['maTaiKhoan']); ?></code></dd>

                        <dt class="col-sm-5">Tên Đăng Nhập:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($taiKhoan['tenDangNhap']); ?></dd>

                        <dt class="col-sm-5">Email:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($taiKhoan['email']); ?></dd>

                        <dt class="col-sm-5">Điện Thoại:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($taiKhoan['soDienThoai'] ?? '-'); ?></dd>

                        <dt class="col-sm-5">Trạng Thái:</dt>
                        <dd class="col-sm-7">
                            <?php 
                            $statusClass = $taiKhoan['trangThai'] === 'ACTIVE' ? 'success' : ($taiKhoan['trangThai'] === 'INACTIVE' ? 'danger' : 'warning');
                            $statusLabel = $taiKhoan['trangThai'] === 'ACTIVE' ? 'Hoạt động' : ($taiKhoan['trangThai'] === 'INACTIVE' ? 'Vô hiệu' : 'Tạm khóa');
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                        </dd>

                        <dt class="col-sm-5">Vai Trò Hiện Tại:</dt>
                        <dd class="col-sm-7">
                            <?php if (empty($vaiTroHienTai)): ?>
                                <span class="text-danger">Chưa có vai trò</span>
                            <?php else: ?>
                                <?php foreach ($vaiTroHienTai as $vt): ?>
                                    <span class="badge bg-primary"><?php echo $danhSachVaiTro[$vt] ?? $vt; ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3 bg-warning bg-opacity-10 border-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shield-alt"></i> Bảo Mật</h5>
                    <p class="mb-2 small">Cấp quyền cẩn thận. Mỗi vai trò đều có khả năng truy cập các tính năng quan trọng.</p>
                    <p class="mb-0 small">Để thay đổi mật khẩu, hãy sử dụng chức năng "Chỉnh Sửa Tài Khoản".</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

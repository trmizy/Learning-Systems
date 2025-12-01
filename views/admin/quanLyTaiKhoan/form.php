<?php
$require_header = __DIR__ . '/../../layouts/header.php';
require_once $require_header;

// Ensure $action is defined (module normally sets it). This helps IDEs and direct view access.
$action = $action ?? ($_GET['action'] ?? null);

// Ensure controller is available for both runtime and static analysis (IDE).
if (!isset($controller) || !is_object($controller)) {
    require_once dirname(__DIR__, 3) . '/config/database.php';
    require_once dirname(__DIR__, 3) . '/controllers/admin/quanLyTaiKhoanController.php';
    $db = Database::getInstance()->getConnection();
    $controller = new QuanLyTaiKhoanController($db);
}

$isEdit = false;
$taiKhoan = [];
$vaiTroHienTai = [];

if ($action === 'edit' && isset($_GET['maTaiKhoan'])) {
    $isEdit = true;
    $taiKhoan = $controller->getChiTietTaiKhoan($_GET['maTaiKhoan']);
    $vaiTroHienTai = $controller->getDanhSachVaiTroTaiKhoan($_GET['maTaiKhoan']);
    
    if (!$taiKhoan) {
        $_SESSION['message'] = 'Không tìm thấy tài khoản';
        $_SESSION['messageType'] = 'danger';
        header('Location: /modules/admin/quanLyTaiKhoan.php?action=list');
        exit;
    }
}

$danhSachVaiTro = [];
$danhSachTrangThai = [];

// Lấy các tham số hỗ trợ hiển thị
$danhSachVaiTro = $controller->getDanhSachVaiTroCoSan();
$danhSachTrangThai = $controller->getDanhSachTrangThai();
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><?php echo $isEdit ? '<i class="fas fa-edit"></i> Chỉnh Sửa Tài Khoản' : '<i class="fas fa-plus"></i> Tạo Tài Khoản Mới'; ?></h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="/modules/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="formTaiKhoan">
                        <input type="hidden" name="action" value="<?php echo $isEdit ? 'update' : 'create'; ?>">
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="maTaiKhoan" value="<?php echo htmlspecialchars($taiKhoan['maTaiKhoan']); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Mã Tài Khoản</label>
                                <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($taiKhoan['maTaiKhoan']); ?>">
                                <small class="text-muted">Mã tài khoản không thể thay đổi</small>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label" for="maTaiKhoan">Mã Tài Khoản <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="maTaiKhoan" name="maTaiKhoan" required 
                                       placeholder="VD: TK001" value="<?php echo htmlspecialchars($_POST['maTaiKhoan'] ?? ''); ?>">
                                <small class="text-muted">Vd: TKGV001, TKHS001, v.v.</small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="tenDangNhap">Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required 
                                   placeholder="example@email.com" value="<?php echo htmlspecialchars($taiKhoan['tenDangNhap'] ?? $_POST['tenDangNhap'] ?? ''); ?>">
                        </div>

                        <?php if (!$isEdit): ?>
                            <div class="mb-3">
                                <label class="form-label" for="matKhau">Mật Khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="matKhau" name="matKhau" required 
                                       placeholder="Nhập mật khẩu (ít nhất 6 ký tự)">
                                <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="email@example.com" value="<?php echo htmlspecialchars($taiKhoan['email'] ?? $_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="soDienThoai">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="soDienThoai" name="soDienThoai" 
                                   placeholder="0xxx-xxx-xxx" value="<?php echo htmlspecialchars($taiKhoan['soDienThoai'] ?? $_POST['soDienThoai'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="trangThai">Trạng Thái <span class="text-danger">*</span></label>
                            <select class="form-select" id="trangThai" name="trangThai" required>
                                <?php foreach ($danhSachTrangThai as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" 
                                        <?php echo ($taiKhoan['trangThai'] ?? $_POST['trangThai'] ?? 'ACTIVE') === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="maTruong">Mã Trường</label>
                            <input type="text" class="form-control" id="maTruong" name="maTruong" 
                                   placeholder="VD: TR001" value="<?php echo htmlspecialchars($taiKhoan['maTruong'] ?? $_POST['maTruong'] ?? ''); ?>">
                            <small class="text-muted">Để trống nếu không liên kết với trường cụ thể</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vai Trò <span class="text-danger">*</span></label>
                            <div class="border p-3 rounded" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($danhSachVaiTro as $key => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="vaiTro[]" 
                                               value="<?php echo $key; ?>" id="vaiTro_<?php echo $key; ?>"
                                               <?php echo in_array($key, $vaiTroHienTai) || (isset($_POST['vaiTro']) && in_array($key, $_POST['vaiTro'] ?? [])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="vaiTro_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">Chọn ít nhất một vai trò</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/modules/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $isEdit ? 'Cập Nhật' : 'Tạo Tài Khoản'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle"></i> Hướng Dẫn</h5>
                    <ul class="mb-0">
                        <li>Mã tài khoản: Định danh duy nhất cho tài khoản</li>
                        <li>Tên đăng nhập: Sử dụng để đăng nhập (thường là email)</li>
                        <li><?php echo $isEdit ? 'Để trống trường mật khẩu nếu không muốn thay đổi' : 'Mật khẩu phải có ít nhất 6 ký tự'; ?></li>
                        <li>Chọn ít nhất một vai trò cho tài khoản</li>
                        <li>Trạng thái: Kiểm soát xem tài khoản có hoạt động hay không</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

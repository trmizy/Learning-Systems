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

// ⚠️ FIX: Lấy maTruong theo cách đúng - KHÔNG truy cập $controller->db
$maTruongHienTai = null;
$user = current_user();
if ($user && isset($user['username'])) {
    try {
        // Sử dụng Database singleton thay vì $controller->db
        require_once dirname(__DIR__, 3) . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT t.maTruong, t.tenTruong
            FROM taikhoan tk
            LEFT JOIN nhanvienphonggiaovu nvgv ON tk.maTaiKhoan = nvgv.maTaiKhoan
            LEFT JOIN truong t ON nvgv.maTruong = t.maTruong
            WHERE tk.tenDangNhap = ?
            LIMIT 1
        ");
        $stmt->execute([$user['username']]);
        $truongInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($truongInfo && $truongInfo['maTruong']) {
            $maTruongHienTai = $truongInfo['maTruong'];
        }
    } catch (PDOException $e) {
        error_log("Error getting maTruong: " . $e->getMessage());
    }
}

if ($action === 'edit' && isset($_GET['maTaiKhoan'])) {
    $isEdit = true;
    $taiKhoan = $controller->getChiTietTaiKhoan($_GET['maTaiKhoan']);
    $vaiTroHienTai = $controller->getDanhSachVaiTroTaiKhoan($_GET['maTaiKhoan']);
    
    if (!$taiKhoan) {
        $_SESSION['message'] = 'Không tìm thấy tài khoản';
        $_SESSION['messageType'] = 'danger';
        header('Location: /models/admin/quanLyTaiKhoan.php?action=list');
        exit;
    }
}

$danhSachVaiTro = [];
$danhSachTrangThai = [];
$danhSachTruong = [];

// Lấy các tham số hỗ trợ hiển thị
$danhSachVaiTro = $controller->getDanhSachVaiTroCoSan();
$danhSachTrangThai = $controller->getDanhSachTrangThai();
$danhSachTruong = $controller->getDanhSachTruong();
?>

<div class="container-fluid mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['messageType'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['messageType']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <h2><?php echo $isEdit ? '<i class="fas fa-edit"></i> Chỉnh Sửa Tài Khoản' : '<i class="fas fa-plus"></i> Tạo Tài Khoản Mới'; ?></h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="/models/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="formTaiKhoan" action="<?php echo $isEdit 
                        ? '/models/admin/quanLyTaiKhoan.php?action=edit&maTaiKhoan=' . urlencode($taiKhoan['maTaiKhoan']) 
                        : '/models/admin/quanLyTaiKhoan.php?action=create'; ?>">
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="maTaiKhoan" value="<?php echo htmlspecialchars($taiKhoan['maTaiKhoan']); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Mã Tài Khoản</label>
                                <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($taiKhoan['maTaiKhoan']); ?>">
                                <small class="text-muted">Mã tài khoản không thể thay đổi</small>
                            </div>
                        <?php else: ?>
                            <!-- Chọn loại tài khoản (Role) -->
                            <div class="mb-3">
                                <label class="form-label" for="roleSelect">Loại Tài Khoản <span class="text-danger">*</span></label>
                                <select class="form-select" name="vaiTro[]" id="roleSelect" required>
                                    <option value="">-- Chọn loại tài khoản --</option>
                                    <option value="hs">Học Sinh</option>
                                </select>
                                <small class="text-muted">Mã tài khoản sẽ tự động phát sinh dựa trên loại tài khoản được chọn</small>
                            </div>
                        <?php endif; ?>

                        <?php if ($isEdit && (in_array('gv', $vaiTroHienTai, true) || in_array('gvbm', $vaiTroHienTai, true))) : ?>
                        <?php 
                            $giaoVien = $controller->getGiaoVienByMaTaiKhoan($taiKhoan['maTaiKhoan']);
                        ?>
                        <hr>
                        <h5 class="mt-3"><i class="fas fa-chalkboard-teacher"></i> Thông Tin Giáo Viên</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="gv_hoTen">Họ Tên</label>
                                <input class="form-control" id="gv_hoTen" name="gv_hoTen" value="<?= htmlspecialchars($_POST['gv_hoTen'] ?? ($giaoVien['hoTen'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="gv_ngaySinh">Ngày Sinh</label>
                                <input type="date" class="form-control" id="gv_ngaySinh" name="gv_ngaySinh" value="<?= htmlspecialchars($_POST['gv_ngaySinh'] ?? ($giaoVien['ngaySinh'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="gv_gioiTinh">Giới Tính</label>
                                <select class="form-select" id="gv_gioiTinh" name="gv_gioiTinh">
                                    <?php $gtgv = $_POST['gv_gioiTinh'] ?? ($giaoVien['gioiTinh'] ?? ''); ?>
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam" <?= $gtgv==='Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Nu" <?= $gtgv==='Nu' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Khac" <?= $gtgv==='Khac' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="gv_email">Email (GV)</label>
                                <input type="email" class="form-control" id="gv_email" name="gv_email" value="<?= htmlspecialchars($_POST['gv_email'] ?? ($giaoVien['email'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="gv_sdt">Số Điện Thoại (GV)</label>
                                <input class="form-control" id="gv_sdt" name="gv_sdt" value="<?= htmlspecialchars($_POST['gv_sdt'] ?? ($giaoVien['soDienThoai'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="gv_diaChi">Địa Chỉ</label>
                                <input class="form-control" id="gv_diaChi" name="gv_diaChi" value="<?= htmlspecialchars($_POST['gv_diaChi'] ?? ($giaoVien['diaChi'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="gv_monHocPhuTrach">Môn Học Phụ Trách</label>
                                <input class="form-control" id="gv_monHocPhuTrach" name="gv_monHocPhuTrach" value="<?= htmlspecialchars($_POST['gv_monHocPhuTrach'] ?? ($giaoVien['monHocPhuTrach'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="gv_trinhDoHocVan">Trình Độ Học Vấn</label>
                                <input class="form-control" id="gv_trinhDoHocVan" name="gv_trinhDoHocVan" value="<?= htmlspecialchars($_POST['gv_trinhDoHocVan'] ?? ($giaoVien['trinhDoHocVan'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="gv_chucVu">Chức Vụ</label>
                                <input class="form-control" id="gv_chucVu" name="gv_chucVu" value="<?= htmlspecialchars($_POST['gv_chucVu'] ?? ($giaoVien['chucVu'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="gv_anhDaiDien">Ảnh Đại Diện (URL)</label>
                                <input class="form-control" id="gv_anhDaiDien" name="gv_anhDaiDien" value="<?= htmlspecialchars($_POST['gv_anhDaiDien'] ?? ($giaoVien['anhDaiDien'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="gv_soCCCD">Số CCCD</label>
                                <input class="form-control" id="gv_soCCCD" name="gv_soCCCD" value="<?= htmlspecialchars($_POST['gv_soCCCD'] ?? ($giaoVien['soCCCD'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="gv_tinhTrangTaiKhoan">Tình Trạng Tài Khoản</label>
                                <select class="form-select" id="gv_tinhTrangTaiKhoan" name="gv_tinhTrangTaiKhoan">
                                    <?php $ttgv = $_POST['gv_tinhTrangTaiKhoan'] ?? ($giaoVien['tinhTrangTaiKhoan'] ?? ''); ?>
                                    <option value="">-- Không đặt --</option>
                                    <option value="ACTIVE" <?= $ttgv==='ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>
                                    <option value="INACTIVE" <?= $ttgv==='INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>
                                    <option value="SUSPENDED" <?= $ttgv==='SUSPENDED' ? 'selected' : '' ?>>SUSPENDED</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="tenDangNhap">Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tenDangNhap" name="tenDangNhap" required 
                                placeholder="example@email.com" 
                                value="<?php echo htmlspecialchars($taiKhoan['tenDangNhap'] ?? $_POST['tenDangNhap'] ?? ''); ?>"
                                <?php echo $isEdit ? 'readonly' : ''; ?>>
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
                            <label class="form-label" for="maTruong">Trường</label>
                            <?php if ($maTruongHienTai): ?>
                                <!-- ✅ Nếu có trường mặc định: Hiển thị read-only + hidden input -->
                                <?php 
                                $truongDefault = null;
                                foreach ($danhSachTruong as $truong) {
                                    if ($truong['maTruong'] === $maTruongHienTai) {
                                        $truongDefault = $truong;
                                        break;
                                    }
                                }
                                ?>
                                <input type="text" class="form-control" disabled 
                                       value="<?php echo htmlspecialchars(($truongDefault['tenTruong'] ?? 'N/A') . ' (' . $maTruongHienTai . ')'); ?>">
                                <input type="hidden" name="maTruong" value="<?php echo htmlspecialchars($maTruongHienTai); ?>">
                                <small class="text-muted">Tài khoản sẽ được liên kết với trường của bạn</small>
                            <?php else: ?>
                                <!-- ❌ Nếu KHÔNG có trường: Hiển thị dropdown (dành cho nhanvienso) -->
                                <select class="form-select" id="maTruong" name="maTruong">
                                    <option value="">-- Không liên kết với trường cụ thể --</option>
                                    <?php foreach ($danhSachTruong as $truong): ?>
                                        <option value="<?php echo htmlspecialchars($truong['maTruong']); ?>"
                                            <?php echo ($taiKhoan['maTruong'] ?? $_POST['maTruong'] ?? '') === $truong['maTruong'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($truong['tenTruong'] . ' (' . $truong['maTruong'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Chọn trường để liên kết tài khoản</small>
                            <?php endif; ?>
                        </div>

                        <?php 
                        // Check if account has only "hs" or "ph" role
                        $isFixedRoleAccount = count($vaiTroHienTai) === 1 && in_array($vaiTroHienTai[0], ['hs', 'ph']);
                        ?>
                        
                        <?php if ($isEdit && !$isFixedRoleAccount): ?>
                        <div class="mb-3">
                            <label class="form-label">Vai Trò <span class="text-danger">*</span></label>
                            <div class="border p-3 rounded" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($danhSachVaiTro as $key => $label): ?>
                                    <?php 
                                    // Skip "admin" and "nhanvienso" roles when creating new account
                                    if (!$isEdit && in_array($key, ['admin', 'nhanvienso'])) {
                                        continue;
                                    }
                                    ?>
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
                        <?php elseif ($isEdit && $isFixedRoleAccount): ?>
                        <div class="mb-3">
                            <label class="form-label">Vai Trò</label>
                            <div class="alert alert-info">
                                <i class="fas fa-lock"></i> Vai trò của tài khoản này đã được cố định là:
                                <strong>
                                    <?php 
                                    $roleLabel = [
                                        'hs' => 'Học Sinh',
                                        'ph' => 'Phụ Huynh'
                                    ];
                                    echo $roleLabel[$vaiTroHienTai[0]] ?? $vaiTroHienTai[0];
                                    ?>
                                </strong>
                                <br>
                                <small>Loại tài khoản này chỉ hỗ trợ một vai trò duy nhất và không thể thay đổi.</small>
                            </div>
                            <!-- Hidden input to maintain role on update -->
                            <input type="hidden" name="vaiTro[]" value="<?php echo $vaiTroHienTai[0]; ?>">
                        </div>
                        <?php endif; ?>

                        <?php if ($isEdit && in_array('ph', $vaiTroHienTai, true)) : ?>
                        <?php 
                            $phuHuynh = $controller->getPhuHuynhByMaTaiKhoan($taiKhoan['maTaiKhoan']);
                        ?>
                        <hr>
                        <h5 class="mt-3"><i class="fas fa-user-friends"></i> Thông Tin Phụ Huynh</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ph_hoTen">Họ Tên</label>
                                <input class="form-control" id="ph_hoTen" name="ph_hoTen" value="<?= htmlspecialchars($_POST['ph_hoTen'] ?? ($phuHuynh['hoTen'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="ph_gioiTinh">Giới Tính</label>
                                <select class="form-select" id="ph_gioiTinh" name="ph_gioiTinh">
                                    <?php $gtph = $_POST['ph_gioiTinh'] ?? ($phuHuynh['gioiTinh'] ?? ''); ?>
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam" <?= $gtph==='Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Nu" <?= $gtph==='Nu' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Khac" <?= $gtph==='Khac' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="ph_moiQuanHe">Mối Quan Hệ</label>
                                <input class="form-control" id="ph_moiQuanHe" name="ph_moiQuanHe" value="<?= htmlspecialchars($_POST['ph_moiQuanHe'] ?? ($phuHuynh['moiQuanHe'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ph_email">Email (PH)</label>
                                <input type="email" class="form-control" id="ph_email" name="ph_email" value="<?= htmlspecialchars($_POST['ph_email'] ?? ($phuHuynh['email'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ph_sdt">Số Điện Thoại (PH)</label>
                                <input class="form-control" id="ph_sdt" name="ph_sdt" value="<?= htmlspecialchars($_POST['ph_sdt'] ?? ($phuHuynh['soDienThoai'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="ph_diaChi">Địa Chỉ</label>
                                <input class="form-control" id="ph_diaChi" name="ph_diaChi" value="<?= htmlspecialchars($_POST['ph_diaChi'] ?? ($phuHuynh['diaChi'] ?? '')) ?>">
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isEdit && in_array('hs', $vaiTroHienTai, true)) : ?>
                        <?php 
                            // Lấy thông tin học sinh liên kết để hiển thị đầy đủ
                            $hocSinh = $controller->getHocSinhByMaTaiKhoan($taiKhoan['maTaiKhoan']);
                        ?>
                        <hr>
                        <h5 class="mt-3"><i class="fas fa-user-graduate"></i> Thông Tin Học Sinh</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="hs_hoTen">Họ Tên</label>
                                <input class="form-control" id="hs_hoTen" name="hs_hoTen" value="<?= htmlspecialchars($_POST['hs_hoTen'] ?? ($hocSinh['hoTen'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="hs_ngaySinh">Ngày Sinh</label>
                                <input type="date" class="form-control" id="hs_ngaySinh" name="hs_ngaySinh" value="<?= htmlspecialchars($_POST['hs_ngaySinh'] ?? ($hocSinh['ngaySinh'] ?? '')) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="hs_gioiTinh">Giới Tính</label>
                                <select class="form-select" id="hs_gioiTinh" name="hs_gioiTinh">
                                    <?php $gt = $_POST['hs_gioiTinh'] ?? ($hocSinh['gioiTinh'] ?? ''); ?>
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam" <?= $gt==='Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Nu" <?= $gt==='Nu' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Khac" <?= $gt==='Khac' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="hs_soCCCD">Số CCCD</label>
                                <input class="form-control" id="hs_soCCCD" name="hs_soCCCD" value="<?= htmlspecialchars($_POST['hs_soCCCD'] ?? ($hocSinh['soCCCD'] ?? '')) ?>">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="hs_diaChi">Địa Chỉ</label>
                                <input class="form-control" id="hs_diaChi" name="hs_diaChi" value="<?= htmlspecialchars($_POST['hs_diaChi'] ?? ($hocSinh['diaChi'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="hs_email">Email (HS)</label>
                                <input type="email" class="form-control" id="hs_email" name="hs_email" value="<?= htmlspecialchars($_POST['hs_email'] ?? ($hocSinh['email'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="hs_sdt">Số Điện Thoại (HS)</label>
                                <input class="form-control" id="hs_sdt" name="hs_sdt" value="<?= htmlspecialchars($_POST['hs_sdt'] ?? ($hocSinh['sdt'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="hs_maLop">Mã Lớp</label>
                                <input class="form-control" id="hs_maLop" name="hs_maLop" value="<?= htmlspecialchars($_POST['hs_maLop'] ?? ($hocSinh['maLop'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="hs_trangThai">Trạng Thái Học Sinh</label>
                                <select class="form-select" id="hs_trangThai" name="hs_trangThai">
                                    <?php $tt = $_POST['hs_trangThai'] ?? ($hocSinh['trangThai'] ?? ''); ?>
                                    <option value="">-- Không đặt --</option>
                                    <option value="DANGHOC" <?= $tt==='DANGHOC' ? 'selected' : '' ?>>Đang học</option>
                                    <option value="BAOLUU" <?= $tt==='BAOLUU' ? 'selected' : '' ?>>Bảo lưu</option>
                                    <option value="THOIHOC" <?= $tt==='THOIHOC' ? 'selected' : '' ?>>Thôi học</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/models/admin/quanLyTaiKhoan.php?action=list" class="btn btn-secondary">Hủy</a>
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

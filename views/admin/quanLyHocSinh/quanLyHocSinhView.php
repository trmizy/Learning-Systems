<?php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['admin']);

$pageTitle = 'Quản lý hồ sơ học sinh';
require_once __DIR__ . '/../../layouts/header.php';

// Expect controller to provide these variables; fallbacks for direct include
$students = $students ?? [];
$error = $error ?? null;
$isSearch = $isSearch ?? (isset($_GET['search']) && $_GET['search'] == '1');
$q_maHS = $q_maHS ?? trim($_GET['maHS'] ?? '');
$q_hoTen = $q_hoTen ?? trim($_GET['hoTen'] ?? '');
$q_maLop = $q_maLop ?? trim($_GET['maLop'] ?? '');
$q_khoi = $q_khoi ?? trim($_GET['khoi'] ?? '');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Quản lý hồ sơ học sinh</h3>
        <div>
            <a href="/modules/quanLyHocSinh/create.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm học sinh
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">Lỗi truy vấn: <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Form tìm kiếm -->
    <form method="get" class="row g-2 mb-3">
        <input type="hidden" name="search" value="1" />
        <div class="col-md-3">
            <input type="text" name="maHS" class="form-control" placeholder="Mã học sinh" value="<?php echo htmlspecialchars($q_maHS); ?>" />
        </div>
        <div class="col-md-3">
            <input type="text" name="hoTen" class="form-control" placeholder="Họ tên" value="<?php echo htmlspecialchars($q_hoTen); ?>" />
        </div>
        <div class="col-md-2">
            <input type="text" name="maLop" class="form-control" placeholder="Lớp" value="<?php echo htmlspecialchars($q_maLop); ?>" />
        </div>
        <div class="col-md-2">
            <input type="text" name="khoi" class="form-control" placeholder="Khối" value="<?php echo htmlspecialchars($q_khoi); ?>" />
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>

    <?php if ($isSearch): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mã HS</th>
                        <th>Họ tên</th>
                        <th>Ngày sinh</th>
                        <th>Lớp</th>
                        <th>Học lực</th>
                        <th>Hạnh kiểm</th>
                        <th>ĐTB môn</th>
                        <th>Phụ huynh</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="10" class="text-center">Không tìm thấy học sinh nào.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($students as $s): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($s['maHS']); ?></td>
                            <td><?php echo htmlspecialchars($s['hoTen']); ?></td>
                            <td><?php echo !empty($s['ngaySinh']) ? htmlspecialchars($s['ngaySinh']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($s['maLop']); ?></td>
                            <td><?php echo htmlspecialchars($s['xepLoaiHocLuc'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($s['loaiHanhKiem'] ?? '-'); ?></td>
                            <td><?php echo is_numeric($s['diemTrungBinhMon']) ? number_format($s['diemTrungBinhMon'],2) : '-'; ?></td>
                            <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($s['phuHuynh_info'] ?? '-'); ?></td>
                            <td>
                                <a href="/modules/quanLyHocSinh/view.php?maHS=<?php echo urlencode($s['maHS']); ?>" class="btn btn-sm btn-outline-primary">Xem</a>
                                <a href="/modules/quanLyHocSinh/edit.php?maHS=<?php echo urlencode($s['maHS']); ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Vui lòng nhập tiêu chí tìm kiếm và bấm <strong>Tìm kiếm</strong>.</div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

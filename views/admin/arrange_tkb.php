<?php
// Biến được controller truyền vào:
// $flash_success, $flash_error
// $dsNamHoc, $dsHocKy, $namHoc, $hocKy
// $dsLop, $dsMonHoc, $dsPhong
// $previewAll, $maLop, $tkbLopDangXem, $conflicts
?>

<div class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa-solid fa-calendar-days me-2"></i>Sắp xếp thời khóa biểu
        </h4>
        <a href="/public/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại Dashboard
        </a>
    </div>

    <!-- Banner -->
    <div class="p-4 rounded-4 mb-4 text-white" style="background: linear-gradient(135deg,#4e54c8,#8f94fb);">
        <h3 class="fw-bold mb-2">Sắp xếp thời khóa biểu</h3>
        <p class="mb-2">
            Tự động hoặc thủ công sắp xếp TKB cho toàn bộ lớp từ thứ 2 đến thứ 6.
            Thứ 2 tiết 1 là <b>chào cờ</b>, Thứ 6 tiết 4 là <b>sinh hoạt lớp</b>.
        </p>
        <form method="post" class="row g-3 align-items-center">
            <input type="hidden" name="action" value="auto_all">
            <div class="col-md-3">
                <label class="form-label text-white-50 mb-1">Năm học</label>
                <select name="namHoc" class="form-select form-select-sm">
                    <option value="">-- Chọn năm học --</option>
                    <?php foreach ($dsNamHoc as $nh): ?>
                        <option value="<?= htmlspecialchars($nh) ?>"
                            <?= $nh === $namHoc ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nh) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-white-50 mb-1">Học kỳ</label>
                <select name="hocKy" class="form-select form-select-sm">
                    <option value="">-- Chọn học kỳ --</option>
                    <?php foreach ($dsHocKy as $hk): ?>
                        <option value="<?= htmlspecialchars($hk) ?>"
                            <?= $hk === $hocKy ? 'selected' : '' ?>>
                            <?= htmlspecialchars($hk) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-warning w-100">
                    <i class="fa-solid fa-robot me-2"></i>Tự động sắp xếp TKB (tất cả lớp)
                </button>
            </div>
        </form>
    </div>

    <!-- Flash message -->
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($flash_success) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($flash_error) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Cột trái: danh sách lớp + form thủ công -->
        <div class="col-lg-4 mb-4">
            <!-- Danh sách lớp -->
            <div class="card mb-4">
                <div class="card-header bg-light fw-semibold">
                    Danh sách lớp (<?= $namHoc && $hocKy ? htmlspecialchars("$namHoc - $hocKy") : 'Chưa chọn' ?>)
                </div>
                <div class="card-body" style="max-height: 350px; overflow:auto;">
                    <?php if ($namHoc === '' || $hocKy === ''): ?>
                        <p class="text-muted mb-0">Chọn năm học và học kỳ để xem danh sách lớp.</p>
                    <?php elseif (empty($dsLop)): ?>
                        <p class="text-muted mb-0">Không tìm thấy lớp nào cho năm học đã chọn.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($dsLop as $lop): ?>
                                <?php
                                $ml = $lop['maLop'];
                                $hasPreview = !empty($previewAll[$ml]);
                                ?>
                                <a href="?module=tkb&namHoc=<?= urlencode($namHoc) ?>&hocKy=<?= urlencode($hocKy) ?>&maLop=<?= urlencode($ml) ?>"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $ml === $maLop ? 'active' : '' ?>">
                                    <span>
                                        <strong><?= htmlspecialchars($ml) ?></strong>
                                        <span class="text-muted small">- <?= htmlspecialchars($lop['tenLop']) ?></span>
                                    </span>
                                    <span class="badge bg-<?= $hasPreview ? 'success' : 'secondary' ?> rounded-pill">
                                        <?= $hasPreview ? 'Đã tạo TKB' : 'Chưa tạo TKB' ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form xếp thủ công -->
            <div class="card">
                <div class="card-header bg-light fw-semibold">
                    Xếp thủ công (slot trống)
                </div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="manual_add">
                        <div class="col-12">
                            <label class="form-label">Năm học</label>
                            <select name="namHoc" class="form-select form-select-sm" required>
                                <option value="">-- Chọn năm học --</option>
                                <?php foreach ($dsNamHoc as $nh): ?>
                                    <option value="<?= htmlspecialchars($nh) ?>"
                                        <?= $nh === $namHoc ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nh) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Học kỳ</label>
                            <select name="hocKy" class="form-select form-select-sm" required>
                                <option value="">-- Chọn học kỳ --</option>
                                <?php foreach ($dsHocKy as $hk): ?>
                                    <option value="<?= htmlspecialchars($hk) ?>"
                                        <?= $hk === $hocKy ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($hk) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lớp</label>
                            <select name="maLop" class="form-select form-select-sm" required>
                                <option value="">-- Chọn lớp --</option>
                                <?php foreach ($dsLop as $lop): ?>
                                    <option value="<?= htmlspecialchars($lop['maLop']) ?>"
                                        <?= $lop['maLop'] === $maLop ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lop['maLop']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Môn học</label>
                            <select name="maMonHoc" class="form-select form-select-sm" required>
                                <option value="">-- Chọn môn học --</option>
                                <?php foreach ($dsMonHoc as $m): ?>
                                    <option value="<?= htmlspecialchars($m['maMonHoc']) ?>">
                                        <?= htmlspecialchars($m['tenMon']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ngày học</label>
                            <input type="date" name="ngayHoc" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label">Tiết</label>
                            <select name="tiet" class="form-select form-select-sm" required>
                                <option value="">--</option>
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label">Phòng</label>
                            <select name="maPhong" class="form-select form-select-sm">
                                <option value="">--</option>
                                <?php foreach ($dsPhong as $p): ?>
                                    <option value="<?= htmlspecialchars($p['maPhong']) ?>">
                                        <?= htmlspecialchars($p['maPhong']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <button class="btn btn-primary btn-sm w-100">
                                <i class="fa-solid fa-plus me-1"></i>Thêm tiết thủ công
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cột phải: TKB chi tiết -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        Thời khóa biểu đã xếp
                        <?php if ($maLop): ?>
                            - Lớp <strong><?= htmlspecialchars($maLop) ?></strong>
                            <?php if ($namHoc && $hocKy): ?>
                                (<?= htmlspecialchars("$namHoc - $hocKy") ?>)
                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                    <?php if ($maLop && !empty($tkbLopDangXem)): ?>
                        <form method="post" class="d-flex align-items-center gap-2 mb-0">
                            <input type="hidden" name="action" value="save_class">
                            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                            <input type="hidden" name="hocKy"  value="<?= htmlspecialchars($hocKy) ?>">
                            <input type="hidden" name="maLop"  value="<?= htmlspecialchars($maLop) ?>">
                            <?php if (!empty($conflicts)): ?>
                                <div class="form-check me-2">
                                    <input class="form-check-input" type="checkbox" id="force_save" name="force_save" value="1">
                                    <label class="form-check-label small" for="force_save">
                                        Chấp nhận ghi đè dù có trùng
                                    </label>
                                </div>
                            <?php endif; ?>
                            <button class="btn btn-success btn-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Lưu TKB lớp này
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$maLop): ?>
                        <p class="text-muted mb-0">Chọn năm học, học kỳ và một lớp ở bên trái để xem hoặc chỉnh sửa thời khóa biểu.</p>
                    <?php elseif (empty($tkbLopDangXem)): ?>
                        <p class="text-muted mb-0">
                            Chưa có thời khóa biểu cho lớp này. Hãy bấm "Tự động sắp xếp TKB" hoặc xếp thủ công.
                        </p>
                    <?php else: ?>
                        <?php
                        // Build ma trận [thu][tiet] từ $tkbLopDangXem
                        $matrix = [];
                        $days   = [];
                        foreach ($tkbLopDangXem as $row) {
                            $thu  = (int)($row['thu'] ?? date('N', strtotime($row['ngayHoc'])));
                            $tiet = (int)$row['tiet'];
                            $matrix[$thu][$tiet] = $row;
                            $days[$thu] = $row['ngayHoc'];
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light">
                                <tr>
                                    <th style="width:70px;">Tiết</th>
                                    <?php for ($thu = 2; $thu <= 6; $thu++): ?>
                                        <th>
                                            Thứ <?= $thu ?>
                                            <?php if (!empty($days[$thu])): ?>
                                                <div class="small text-muted">
                                                    <?= date('d/m/Y', strtotime($days[$thu])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </th>
                                    <?php endfor; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php for ($tiet = 1; $tiet <= 7; $tiet++): ?>
                                    <tr>
                                        <th class="bg-light"><?= $tiet ?></th>
                                        <?php for ($thu = 2; $thu <= 6; $thu++): ?>
                                            <?php $cell = $matrix[$thu][$tiet] ?? null; ?>
                                            <td class="text-start">
                                                <?php if ($cell): ?>
                                                    <?php if ($cell['loaiTiet'] === 'Chao_co'): ?>
                                                        <span class="badge bg-info text-dark">Chào cờ</span>
                                                    <?php elseif ($cell['loaiTiet'] === 'Sinh_hoat'): ?>
                                                        <span class="badge bg-success">Sinh hoạt lớp</span>
                                                    <?php else: ?>
                                                        <div><strong><?= htmlspecialchars($cell['tenMon'] ?? '') ?></strong></div>
                                                        <?php if (!empty($cell['tenGV'])): ?>
                                                            <div class="small text-muted">GV: <?= htmlspecialchars($cell['tenGV']) ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($cell['maPhong'])): ?>
                                                            <div class="small text-muted">P: <?= htmlspecialchars($cell['maPhong']) ?></div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted small">Trống</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($conflicts)): ?>
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white fw-semibold">
                        Trùng lịch phát hiện
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">
                            Các slot dưới đây đang trùng giáo viên hoặc phòng với lớp khác trong hệ thống.
                            Nếu vẫn muốn lưu, hãy tick "Chấp nhận ghi đè" phía trên rồi bấm lưu lại.
                        </p>
                        <ul class="mb-0">
                            <?php foreach ($conflicts as $err): ?>
                                <li>
                                    <strong><?= htmlspecialchars($err['slot']) ?></strong>:
                                    GV <?= htmlspecialchars($err['gv'] ?? $err['maGV']) ?>,
                                    phòng <?= htmlspecialchars($err['phong'] ?? '') ?>,
                                    đang dạy lớp <?= htmlspecialchars($err['lop']) ?>.
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

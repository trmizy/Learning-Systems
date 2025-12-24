<?php
/**
 * @var string $flash_success
 * @var string $flash_error
 * @var array  $dsNamHoc
 * @var array  $dsHocKy
 * @var string $namHoc
 * @var string $hocKy
 * @var array  $dsLop
 * @var array  $dsPhong
 * @var array  $previewAll
 * @var string $maLop
 * @var array  $tkbLopDangXem
 * @var array  $conflicts
 * @var array  $dsMonCuaLop
 * @var string $maMonHoc
 * @var array  $slotTrong
 * @var string $viewMode
 * @var int    $displayTuan
 * @var array  $displayNgayThu
 * @var array|null $thongTinHocKy
 * @var int    $tongTuanHocKy
 * @var array  $dsTuanHocKy
 * @var array  $dsThangHocKy
 */
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

        <?php if (!empty($thongTinHocKy)): ?>
            <div class="small text-white-50 mb-2">
                Áp dụng từ:
                <b>Tuần <?= (int)$thongTinHocKy['week_start'] ?> đến tuần <?= (int)$thongTinHocKy['week_end'] ?></b>
                (<?= date('d/m/Y', strtotime($thongTinHocKy['start'])) ?> - <?= date('d/m/Y', strtotime($thongTinHocKy['end'])) ?>)
            </div>
        <?php endif; ?>

        <p class="mb-3">
            Chọn <b>Học kỳ</b> → chọn <b>Lớp</b> → chọn <b>Môn</b> → chọn <b>Slot trống</b> → <b>Xác nhận</b> để lưu.
        </p>

        <!-- Chọn năm học + học kỳ -->
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="module" value="tkb">

            <div class="col-md-3">
                <label class="form-label text-white-50 mb-1">Năm học</label>
                <select name="namHoc" class="form-select form-select-sm">
                    <option value="">-- Chọn năm học --</option>
                    <?php foreach ($dsNamHoc as $nh): ?>
                        <option value="<?= htmlspecialchars($nh) ?>" <?= ($nh === $namHoc) ? 'selected' : '' ?>>
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
                        <option value="<?= htmlspecialchars($hk) ?>" <?= ((string)$hk === (string)$hocKy || ('HK'.$hk) === (string)$hocKy) ? 'selected' : '' ?>>
                            HK<?= htmlspecialchars((string)$hk) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-light w-100">
                    <i class="fa-solid fa-filter me-2"></i>Xem danh sách lớp
                </button>
            </div>

            <div class="col-md-3">
                <button class="btn btn-warning w-100"
                        form="form_auto_all"
                        <?= ($namHoc === '' || $hocKy === '') ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-robot me-2"></i>Tự động sắp xếp (tất cả lớp)
                </button>
            </div>
        </form>

        <form method="post" id="form_auto_all" class="d-none">
            <input type="hidden" name="action" value="auto_all">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= htmlspecialchars($hocKy) ?>">
        </form>
    </div>

    <!-- Flash -->
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
        <!-- Cột trái -->
        <div class="col-lg-4 mb-4">

            <!-- Danh sách lớp -->
            <div class="card mb-4">
                <div class="card-header bg-light fw-semibold">
                    Danh sách lớp (<?= ($namHoc && $hocKy) ? htmlspecialchars($namHoc . ' - HK' . (string)$hocKy) : 'Chưa chọn học kỳ' ?>)
                </div>
                <div class="card-body" style="max-height: 320px; overflow:auto;">
                    <?php if ($hocKy === ''): ?>
                        <p class="text-muted mb-0">Chọn học kỳ để xem danh sách lớp.</p>
                    <?php elseif (empty($dsLop)): ?>
                        <p class="text-muted mb-0">Không tìm thấy lớp nào cho năm học đã chọn.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($dsLop as $lop): ?>
                                <?php
                                $ml = $lop['maLop'];
                                $hasPreview = !empty($previewAll[$ml]);
                                ?>
                                <a href="?module=tkb&namHoc=<?= urlencode($namHoc) ?>&hocKy=<?= urlencode((string)$hocKy) ?>&maLop=<?= urlencode($ml) ?>"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= ($ml === $maLop) ? 'active' : '' ?>">
                                    <span>
                                        <strong><?= htmlspecialchars($ml) ?></strong>
                                        <span class="text-muted small">- <?= htmlspecialchars($lop['tenLop']) ?></span>
                                    </span>
                                    <span class="badge bg-<?= $hasPreview ? 'success' : 'secondary' ?> rounded-pill">
                                        <?= $hasPreview ? 'Có đề xuất' : 'Chưa có đề xuất' ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Xếp thủ công -->
            <div class="card">
                <div class="card-header bg-light fw-semibold">
                    Xếp thủ công (slot trống)
                </div>
                <div class="card-body">
                    <?php if ($namHoc === '' || $hocKy === ''): ?>
                        <p class="text-muted mb-0">Vui lòng chọn năm học và học kỳ trước.</p>
                    <?php elseif ($maLop === ''): ?>
                        <p class="text-muted mb-0">Chọn một lớp ở danh sách bên trên để xếp thủ công.</p>
                    <?php else: ?>

                        <!-- Chọn môn -->
                        <form method="get" class="row g-2 mb-3">
                            <input type="hidden" name="module" value="tkb">
                            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                            <input type="hidden" name="hocKy" value="<?= htmlspecialchars((string)$hocKy) ?>">
                            <input type="hidden" name="maLop" value="<?= htmlspecialchars($maLop) ?>">

                            <div class="col-12">
                                <label class="form-label">Môn học của lớp</label>
                                <select name="maMonHoc" class="form-select form-select-sm" required>
                                    <option value="">-- Chọn môn học --</option>
                                    <?php foreach ($dsMonCuaLop as $m): ?>
                                        <option value="<?= htmlspecialchars($m['maMonHoc']) ?>"
                                            <?= ($m['maMonHoc'] === ($maMonHoc ?? '')) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['tenMon']) ?>
                                            (<?= (int)($m['soTietTuan'] ?? 0) ?> tiết/tuần • <?= (int)($m['soTietHocKy'] ?? 0) ?> tiết/HK)
                                            <?php if (!empty($m['tenGV'])): ?>
                                                - GV: <?= htmlspecialchars($m['tenGV']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($dsMonCuaLop)): ?>
                                    <div class="small text-muted mt-1">
                                        Không có môn nào: kiểm tra <b>phanconggiangday</b> (maLop/namHoc/hocKy) và giá trị hocKy đang là <b>1/2</b>.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 mt-2">
                                <button class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i>Xem slot trống
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($maMonHoc)): ?>
                            <form method="post" class="row g-2"
                                  onsubmit="return confirm('Xác nhận sắp xếp thời khóa biểu cho môn đã chọn?');">
                                <input type="hidden" name="action" value="manual_add">
                                <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                                <input type="hidden" name="hocKy" value="<?= htmlspecialchars((string)$hocKy) ?>">
                                <input type="hidden" name="maLop" value="<?= htmlspecialchars($maLop) ?>">
                                <input type="hidden" name="maMonHoc" value="<?= htmlspecialchars($maMonHoc) ?>">

                                <div class="col-12">
                                    <label class="form-label">Ngày & tiết trống</label>
                                    <select name="slot" class="form-select form-select-sm" required>
                                        <option value="">-- Chọn slot trống --</option>
                                        <?php foreach ($slotTrong as $s): ?>
                                            <?php
                                            $thu = (int)$s['thu'];
                                            $tiet = (int)$s['tiet'];
                                            $dateShow = $displayNgayThu[$thu] ?? null;
                                            $buoi = ($tiet <= 4) ? 'Sáng' : 'Chiều';
                                            ?>
                                            <option value="<?= $thu . '|' . $tiet ?>">
                                                <?= $buoi ?> - Thứ <?= $thu ?> - Tiết <?= $tiet ?>
                                                <?php if ($dateShow): ?>
                                                    (<?= date('d/m/Y', strtotime($dateShow)) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($slotTrong)): ?>
                                        <div class="small text-muted mt-1">Không còn slot trống (hoặc bị trùng GV/phòng).</div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Phòng học (bỏ trống = theo lớp)</label>
                                    <select name="maPhong" class="form-select form-select-sm">
                                        <option value="">-- Tự theo lớp --</option>
                                        <?php foreach ($dsPhong as $p): ?>
                                            <option value="<?= htmlspecialchars($p['maPhong']) ?>">
                                                <?= htmlspecialchars($p['maPhong']) ?> - <?= htmlspecialchars($p['tenPhong'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 mt-2">
                                    <button class="btn btn-primary btn-sm w-100" <?= empty($slotTrong) ? 'disabled' : '' ?>>
                                        <i class="fa-solid fa-circle-check me-1"></i>Xác nhận sắp xếp
                                    </button>
                                </div>

                                <div class="col-12">
                                    <a class="btn btn-outline-danger btn-sm w-100"
                                       href="?module=tkb&namHoc=<?= urlencode($namHoc) ?>&hocKy=<?= urlencode((string)$hocKy) ?>&maLop=<?= urlencode($maLop) ?>"
                                       onclick="return confirm('Xác nhận hủy bỏ?');">
                                        <i class="fa-solid fa-ban me-1"></i>Hủy sắp xếp
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Cột phải -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">
                        Thời khóa biểu
                        <?php if ($maLop): ?>
                            - Lớp <strong><?= htmlspecialchars($maLop) ?></strong>
                            <?php if ($namHoc && $hocKy): ?>
                                (<?= htmlspecialchars($namHoc . ' - HK' . (string)$hocKy) ?>)
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($displayTuan) && !empty($tongTuanHocKy)): ?>
                            <span class="text-muted small">• Đang xem: Tuần <?= (int)$displayTuan ?>/<?= (int)$tongTuanHocKy ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Bộ lọc tuần/tháng/kỳ -->
                    <?php if ($maLop && $namHoc && $hocKy): ?>
                        <form method="get" class="d-flex align-items-center gap-2 mb-0">
                            <input type="hidden" name="module" value="tkb">
                            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                            <input type="hidden" name="hocKy" value="<?= htmlspecialchars((string)$hocKy) ?>">
                            <input type="hidden" name="maLop" value="<?= htmlspecialchars($maLop) ?>">

                            <select name="viewMode" class="form-select form-select-sm" style="width:140px;">
                                <option value="hocKy" <?= ($viewMode === 'hocKy') ? 'selected' : '' ?>>Theo học kỳ</option>
                                <option value="tuan"  <?= ($viewMode === 'tuan') ? 'selected' : '' ?>>Theo tuần</option>
                                <option value="thang" <?= ($viewMode === 'thang') ? 'selected' : '' ?>>Theo tháng</option>
                            </select>

                            <select name="tuan" class="form-select form-select-sm" style="width:140px;">
                                <?php foreach ($dsTuanHocKy as $w): ?>
                                    <option value="<?= (int)$w['tuan'] ?>" <?= ((int)$w['tuan'] === (int)$displayTuan) ? 'selected' : '' ?>>
                                        Tuần <?= (int)$w['tuan'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="thang" class="form-select form-select-sm" style="width:140px;">
                                <option value="">-- Tháng --</option>
                                <?php foreach ($dsThangHocKy as $m): ?>
                                    <option value="<?= htmlspecialchars($m) ?>" <?= ($m === ($thang ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button class="btn btn-outline-primary btn-sm">Lọc</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if (!$maLop): ?>
                        <p class="text-muted mb-0">Chọn học kỳ và một lớp để xem thời khóa biểu.</p>
                    <?php elseif (empty($tkbLopDangXem)): ?>
                        <p class="text-muted mb-0">Chưa có thời khóa biểu cho lớp này (tuần đang xem).</p>
                    <?php else: ?>
                        <?php
                        // Build ma trận [thu][tiet]
                        $matrix = [];
                        foreach ($tkbLopDangXem as $row) {
                            $thu  = (int)($row['thu'] ?? 2);
                            $tiet = (int)$row['tiet'];
                            $matrix[$thu][$tiet] = $row;
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light">
                                <tr>
                                    <th style="width:90px;">Buổi</th>
                                    <th style="width:70px;">Tiết</th>
                                    <?php for ($thu = 2; $thu <= 6; $thu++): ?>
                                        <th>
                                            Thứ <?= $thu ?>
                                            <?php if (!empty($displayNgayThu[$thu])): ?>
                                                <div class="small text-muted">
                                                    <?= date('d/m/Y', strtotime($displayNgayThu[$thu])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </th>
                                    <?php endfor; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php for ($tiet = 1; $tiet <= 8; $tiet++): ?>
                                    <tr>
                                        <?php if ($tiet === 1): ?>
                                            <th class="bg-light" rowspan="4">Sáng</th>
                                        <?php elseif ($tiet === 5): ?>
                                            <th class="bg-light" rowspan="4">Chiều</th>
                                        <?php endif; ?>

                                        <th class="bg-light"><?= $tiet ?></th>

                                        <?php for ($thu = 2; $thu <= 6; $thu++): ?>
                                            <?php $cell = $matrix[$thu][$tiet] ?? null; ?>
                                            <td class="text-start">
                                                <?php if ($cell): ?>
                                                    <?php if (($cell['loaiTiet'] ?? '') === 'Chao_co'): ?>
                                                        <span class="badge bg-info text-dark">Chào cờ</span>
                                                    <?php elseif (($cell['loaiTiet'] ?? '') === 'Sinh_hoat'): ?>
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

                        <?php if (!empty($previewAll[$maLop])): ?>
                            <div class="d-flex justify-content-end mt-2">
                                <form method="post" class="d-flex align-items-center gap-2 mb-0">
                                    <input type="hidden" name="action" value="save_class">
                                    <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
                                    <input type="hidden" name="hocKy" value="<?= htmlspecialchars((string)$hocKy) ?>">
                                    <input type="hidden" name="maLop" value="<?= htmlspecialchars($maLop) ?>">
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
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($conflicts)): ?>
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white fw-semibold">
                        Trùng lịch phát hiện
                    </div>
                    <div class="card-body">
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

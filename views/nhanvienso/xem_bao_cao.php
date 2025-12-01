<?php
// File: views/nhanvienso/xem_bao_cao.php
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Báo cáo & Thống kê Giáo dục</h2>

    <div class="card shadow-sm mb-4 bg-light">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="xem_bao_cao">
                <?php if(isset($_GET['search'])): ?><input type="hidden" name="search" value="1"><?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Trường học <span class="text-danger">*</span></label>
                    <select name="maTruong" class="form-select" required onchange="this.form.submit()">
                        <option value="">-- Chọn trường --</option>
                        <?php foreach ($danhSachTruong as $tr): ?>
                            <option value="<?php echo $tr['maTruong']; ?>" <?php echo ($maTruong == $tr['maTruong']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tr['tenTruong']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Năm học <span class="text-danger">*</span></label>
                    <select name="namHoc" class="form-select" required onchange="this.form.submit()">
                        <option value="">-- Chọn năm --</option>
                        <?php foreach ($danhSachNamHoc as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($namHoc == $year) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Học kỳ <span class="text-danger">*</span></label>
                    <select name="hocKy" class="form-select" required>
                        <option value="HK1" <?php echo ($hocKy == 'HK1') ? 'selected' : ''; ?>>Học kỳ 1</option>
                        <option value="HK2" <?php echo ($hocKy == 'HK2') ? 'selected' : ''; ?>>Học kỳ 2</option>
                    </select>
                </div>

                <div class="w-100 d-none d-md-block"></div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Khối (Tùy chọn)</label>
                    <select name="maKhoi" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả Khối --</option>
                        <?php foreach ($danhSachKhoi as $k): ?>
                            <option value="<?php echo $k['maKhoi']; ?>" <?php echo ($maKhoi == $k['maKhoi']) ? 'selected' : ''; ?>>
                                Khối <?php echo htmlspecialchars($k['khoiLop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Lớp (Tùy chọn)</label>
                    <select name="maLop" class="form-select">
                        <option value="">-- Tất cả Lớp --</option>
                        <?php foreach ($danhSachLop as $l): ?>
                            <option value="<?php echo $l['maLop']; ?>" <?php echo ($maLop == $l['maLop']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($l['tenLop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 ms-auto">
                    <button type="submit" name="search" value="1" class="btn btn-primary w-100">
                        <i class="fa-solid fa-chart-simple me-2"></i>Thống kê
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-warning text-center"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['search']) && empty($error_message)): ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold text-primary d-flex justify-content-between">
                        <span><i class="fa-solid fa-chart-column me-2"></i>Biểu đồ Phân phối Học lực</span>
                        <small class="text-muted">
                            <?php 
                                echo "Năm $namHoc"; 
                                if($maKhoi) echo " - Khối $maKhoi";
                                if($maLop) echo " - Lớp $maLop";
                            ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="hocLucChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold text-success">
                        <i class="fa-solid fa-file-contract me-2"></i>Báo cáo đã nhận
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($ketQuaBaoCao)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-regular fa-folder-open fa-2x mb-2"></i>
                                <p class="small">Chưa có báo cáo nào.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ketQuaBaoCao as $bc): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($bc['tieuDe']); ?></h6>
                                        <small class="text-muted"><?php echo date('d/m', strtotime($bc['ngayLap'])); ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted"><?php echo htmlspecialchars($bc['loaiBaoCao']); ?></p>
                                    <?php if ($bc['fileUrl']): ?>
                                        <a href="<?php echo htmlspecialchars($bc['fileUrl']); ?>" class="btn btn-sm btn-outline-primary mt-1" target="_blank">Tải về</a>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('hocLucChart').getContext('2d');
                const labels = <?php echo $chartData['labels']; ?>;
                const data = <?php echo $chartData['data']; ?>;
                const total = data.reduce((a, b) => a + b, 0);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Học sinh',
                            data: data,
                            backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#dc3545'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: total > 0 ? 'Tổng số: ' + total + ' học sinh' : 'Không có dữ liệu' }
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>
</div>
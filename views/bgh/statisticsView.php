<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Thống kê điểm số và hạnh kiểm
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Form bộ lọc -->
                    <form method="POST" action="" id="filterForm">
                        <input type="hidden" name="action" value="view_statistics">
                        
                        <div class="row g-3 mb-4">
                            <!-- Năm học -->
                            <div class="col-md-3">
                                <label class="form-label">Năm học <span class="text-danger">*</span></label>
                                <select name="namHoc" class="form-select" required>
                                    <option value="">-- Chọn năm học --</option>
                                    <?php foreach ($danhSachNamHoc as $nh): ?>
                                        <option value="<?= htmlspecialchars($nh['namHoc']) ?>" <?= (isset($filters['namHoc']) && $filters['namHoc'] === $nh['namHoc']) || (!isset($filters['namHoc']) && $nh['namHoc'] === $macDinh['namHoc']) ? 'selected' : '' ?>><?= htmlspecialchars($nh['namHoc']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Học kỳ -->
                            <div class="col-md-3">
                                <label class="form-label">Học kỳ <span class="text-danger">*</span></label>
                                <select name="hocKy" class="form-select" required>
                                    <option value="">-- Chọn học kỳ --</option>
                                    <?php foreach ($danhSachHocKy as $hk): ?>
                                        <option value="<?= htmlspecialchars($hk['hocKy']) ?>" <?= (isset($filters['hocKy']) && $filters['hocKy'] === $hk['hocKy']) || (!isset($filters['hocKy']) && $hk['hocKy'] === $macDinh['hocKy']) ? 'selected' : '' ?>><?= htmlspecialchars($hk['tenHocKy']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Khối -->
                            <div class="col-md-3">
                                <label class="form-label">Khối</label>
                                <select name="khoi" class="form-select" id="khoiSelect">
                                    <option value="">-- Tất cả khối --</option>
                                    <?php foreach ($danhSachKhoi as $k): ?>
                                        <option value="<?= htmlspecialchars($k['khoi']) ?>" <?= isset($filters['khoi']) && $filters['khoi'] === $k['khoi'] ? 'selected' : '' ?>>Khối <?= htmlspecialchars($k['khoi']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lớp -->
                            <div class="col-md-3">
                                <label class="form-label">Lớp</label>
                                <select name="maLop" class="form-select" id="lopSelect">
                                    <option value="">-- Tất cả lớp --</option>
                                    <?php foreach ($danhSachLop as $lop): ?>
                                        <option value="<?= htmlspecialchars($lop['maLop']) ?>" data-khoi="<?= htmlspecialchars($lop['khoi']) ?>" <?= isset($filters['maLop']) && $filters['maLop'] === $lop['maLop'] ? 'selected' : '' ?>><?= htmlspecialchars($lop['tenLop']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Xem thống kê
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-redo"></i> Làm mới
                            </button>
                        </div>
                    </form>

                    <?php if ($thongKeData): ?>
                        <hr class="my-4">

                        <!-- Thống kê tổng quan -->
                        <div class="row mb-4">
                            <?php 
                            $overviewCards = [
                                ['info', 'Tổng số học sinh', number_format($thongKeData['thongKe']['tongSoHocSinh'])],
                                ['success', 'Điểm TB chung', number_format($thongKeData['thongKe']['diemTrungBinhChung'], 2)],
                                ['warning', 'Năm học - Học kỳ', htmlspecialchars($thongKeData['filters']['namHoc']).' - '.htmlspecialchars($thongKeData['filters']['hocKy'])]
                            ];
                            foreach ($overviewCards as $card): ?>
                                <div class="col-md-4">
                                    <div class="card bg-<?= $card[0] ?> text-white"><div class="card-body"><h5><?= $card[1] ?></h5><h2><?= $card[2] ?></h2></div></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Biểu đồ thống kê -->
                        <?php 
                        function getBadgeColor($loai, $type = 'hocLuc') {
                            if ($type === 'hocLuc') {
                                return $loai === 'Gioi' ? 'success' : ($loai === 'Kha' ? 'info' : ($loai === 'Trung Binh' ? 'warning' : 'danger'));
                            }
                            return $loai === 'Tot' ? 'success' : ($loai === 'Kha' ? 'info' : ($loai === 'Trung Binh' ? 'warning' : 'danger'));
                        }
                        $statsCards = [
                            ['Thống kê học lực', 'primary', $thongKeData['thongKe']['hocLuc'], 'hocLuc'],
                            ['Thống kê hạnh kiểm', 'success', $thongKeData['thongKe']['hanhKiem'], 'hanhKiem']
                        ];
                        ?>
                        <div class="row mb-4">
                            <?php foreach ($statsCards as $stat): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-<?= $stat[1] ?> text-white"><h5 class="mb-0"><?= $stat[0] ?></h5></div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <thead><tr><th>Xếp loại</th><th>Số lượng</th><th>Tỉ lệ (%)</th></tr></thead>
                                                <tbody>
                                                    <?php foreach ($stat[2] as $loai => $data): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($loai) ?></strong></td>
                                                            <td><?= number_format($data['soLuong']) ?></td>
                                                            <td><div class="progress" style="height: 25px;"><div class="progress-bar bg-<?= getBadgeColor($loai, $stat[3]) ?>" style="width: <?= $data['tiLe'] ?>%"><?= $data['tiLe'] ?>%</div></div></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bảng chi tiết -->
                        <div class="card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Chi tiết học sinh</h5>
                                <button class="btn btn-sm btn-light" onclick="exportToExcel()">
                                    <i class="fas fa-download"></i> Xuất Excel
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="statisticsTable">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Mã HS</th>
                                                <th>Họ tên</th>
                                                <th>Lớp</th>
                                                <th>Khối</th>
                                                <th>Điểm TB</th>
                                                <th>Học lực</th>
                                                <th>Hạnh kiểm</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($thongKeData['danhSach'] as $index => $hs): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($hs['maHS']) ?></td>
                                                    <td><?= htmlspecialchars($hs['hoTen']) ?></td>
                                                    <td><?= htmlspecialchars($hs['tenLop']) ?></td>
                                                    <td><?= htmlspecialchars($hs['khoi']) ?></td>
                                                    <td><?= number_format($hs['diemTrungBinhChung'], 2) ?></td>
                                                    <td><span class="badge bg-<?= getBadgeColor($hs['xepLoaiHocLuc'], 'hocLuc') ?>"><?= htmlspecialchars($hs['xepLoaiHocLuc']) ?></span></td>
                                                    <td><span class="badge bg-<?= getBadgeColor($hs['loaiHanhKiem'] ?? 'Yeu', 'hanhKiem') ?>"><?= htmlspecialchars($hs['loaiHanhKiem'] ?? 'Chưa có') ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('khoiSelect').addEventListener('change', function() {
    const khoi = this.value;
    const lopSelect = document.getElementById('lopSelect');
    const options = lopSelect.getElementsByTagName('option');
    
    for (let i = 1; i < options.length; i++) {
        options[i].style.display = !khoi || options[i].dataset.khoi === khoi ? 'block' : 'none';
    }
    
    if (khoi && lopSelect.value && lopSelect.options[lopSelect.selectedIndex].dataset.khoi !== khoi) {
        lopSelect.value = '';
    }
});

function resetForm() {
    document.getElementById('filterForm').reset();
    location.reload();
}

function exportToExcel() {
    const html = document.getElementById('statisticsTable').outerHTML;
    const blob = new Blob(['\ufeff' + html], {type: 'application/vnd.ms-excel'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'thong-ke-diem-hanh-kiem.xls';
    a.click();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
$pageTitle = 'Thống kê điểm tuyển sinh - Sở GD&ĐT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .stat-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: transform 0.3s ease;
    }
    
    .stat-box:hover {
        transform: translateY(-5px);
    }
    
    .stat-box.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-box.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-box.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .chart-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="fa-solid fa-chart-line text-primary me-2"></i>
                Thống kê điểm tuyển sinh
            </h2>
            <p class="text-muted mb-0">Phân tích chi tiết kết quả tuyển sinh năm <?php echo htmlspecialchars($namTuyenSinh); ?></p>
        </div>
        
        <!-- Filter năm -->
        <div class="d-flex gap-2 align-items-center">
            <label class="fw-semibold">Năm:</label>
            <select class="form-select" style="width: auto;" onchange="window.location.href='?action=thong-ke-diem&nam=' + this.value">
                <?php foreach ($danhSachNam as $nam): ?>
                    <option value="<?php echo $nam; ?>" <?php echo $nam == $namTuyenSinh ? 'selected' : ''; ?>>
                        <?php echo $nam; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <a href="/public/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-box">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($thongKeChung['tongThiSinh']); ?></div>
                        <div class="opacity-90">Tổng số thí sinh</div>
                    </div>
                    <i class="fa-solid fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="stat-box success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($thongKeChung['diemTrungBinh'], 2); ?></div>
                        <div class="opacity-90">Điểm trung bình</div>
                    </div>
                    <i class="fa-solid fa-star fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="stat-box warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($thongKeChung['diemCaoNhat'], 2); ?></div>
                        <div class="opacity-90">Điểm cao nhất</div>
                    </div>
                    <i class="fa-solid fa-trophy fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="stat-box info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($thongKeChung['soThiSinhDat']); ?></div>
                        <div class="opacity-90">Thí sinh đạt (≥22.5đ)</div>
                    </div>
                    <i class="fa-solid fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Thống kê theo môn -->
        <div class="col-lg-6">
            <div class="card chart-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-book text-primary me-2"></i>
                        Thống kê theo môn học
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Môn học</th>
                                    <th class="text-center">Điểm TB</th>
                                    <th class="text-center">Cao nhất</th>
                                    <th class="text-center">Thấp nhất</th>
                                    <th class="text-center">SL Giỏi (≥8đ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($thongKeTheoMon as $mon): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($mon['monHoc']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo number_format($mon['diemTB'], 2); ?></span>
                                    </td>
                                    <td class="text-center text-success fw-bold"><?php echo number_format($mon['diemMax'], 1); ?></td>
                                    <td class="text-center text-danger fw-bold"><?php echo number_format($mon['diemMin'], 1); ?></td>
                                    <td class="text-center"><?php echo number_format($mon['soThiSinhGioi']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phân phối điểm -->
        <div class="col-lg-6">
            <div class="card chart-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-chart-pie text-success me-2"></i>
                        Phân phối điểm
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top thí sinh -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-medal text-warning me-2"></i>
                        Top 10 thí sinh điểm cao nhất
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Mã thí sinh</th>
                                    <th>Họ tên</th>
                                    <th class="text-center">Điểm Toán</th>
                                    <th class="text-center">Điểm Văn</th>
                                    <th class="text-center">Điểm Anh</th>
                                    <th class="text-center fw-bold">Tổng điểm</th>
                                    <th>Nơi sinh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topThiSinh as $index => $ts): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($index < 3): ?>
                                            <i class="fa-solid fa-medal fa-lg" style="color: <?php echo ['#FFD700', '#C0C0C0', '#CD7F32'][$index]; ?>"></i>
                                        <?php else: ?>
                                            <?php echo $index + 1; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($ts['maThiSinh']); ?></td>
                                    <td><?php echo htmlspecialchars($ts['hoTen']); ?></td>
                                    <td class="text-center"><?php echo number_format($ts['diemToan'], 1); ?></td>
                                    <td class="text-center"><?php echo number_format($ts['diemVan'], 1); ?></td>
                                    <td class="text-center"><?php echo number_format($ts['diemAnh'], 1); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6"><?php echo number_format($ts['diem'], 2); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($ts['noiSinh']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ phân phối điểm
    const ctx = document.getElementById('distributionChart').getContext('2d');
    const distributionData = <?php echo json_encode($phanPoiDiem); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: distributionData.map(d => d.khoangDiem),
            datasets: [{
                label: 'Số lượng thí sinh',
                data: distributionData.map(d => d.soLuong),
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

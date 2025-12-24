<?php
$pageTitle = 'Hạnh kiểm & Học lực';
require_once __DIR__ . '/../layouts/header.php';

// Hàm lấy badge color theo loại hạnh kiểm
function getHanhKiemBadge($loai) {
    $map = [
        'Tốt' => 'success',
        'Khá' => 'primary',
        'Trung bình' => 'warning',
        'Yếu' => 'danger'
    ];
    return $map[$loai] ?? 'secondary';
}

// Hàm lấy badge color theo xếp loại học lực
function getHocLucBadge($xepLoai) {
    $map = [
        'Giỏi' => 'success',
        'Khá' => 'primary',
        'Trung bình' => 'warning',
        'Yếu' => 'danger'
    ];
    return $map[$xepLoai] ?? 'secondary';
}
?>

<style>
    .result-card {
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 0;
        overflow: hidden;
    }
    
    .result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .result-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .table-custom {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .table-custom thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-custom tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }
    
    .stat-box {
        padding: 1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .stat-box h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #667eea;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold">
                        <i class="fa-solid fa-medal text-warning me-2"></i>
                        Hạnh kiểm & Học lực
                    </h2>
                    <p class="text-muted mb-0">
                        Học sinh: <strong><?php echo htmlspecialchars($thongTinHS['hoTen'] ?? 'N/A'); ?></strong>
                        <span class="mx-2">|</span>
                        Lớp: <strong><?php echo htmlspecialchars($thongTinHS['tenLop'] ?? 'N/A'); ?></strong>
                    </p>
                </div>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- Chọn học sinh (chỉ hiện với phụ huynh) -->
    <?php if (isset($danhSachCon) && !empty($danhSachCon)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card result-card">
                <div class="card-body">
                    <label class="fw-bold mb-2">
                        <i class="fa-solid fa-child me-2"></i>Chọn con em:
                    </label>
                    <select class="form-select" id="selectHocSinh" onchange="window.location.href='?action=ph-hanh-kiem-hoc-luc&maHocSinh=' + this.value">
                        <?php foreach ($danhSachCon as $con): ?>
                        <option value="<?php echo $con['maHocSinh']; ?>" 
                                <?php echo $con['maHocSinh'] == $maHocSinh ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($con['hoTen']); ?> - <?php echo htmlspecialchars($con['tenLop']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Hạnh kiểm -->
        <div class="col-lg-6 mb-4">
            <div class="card result-card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-user-check text-success me-2"></i>
                        Hạnh kiểm
                    </h5>

                    <?php if (empty($danhSachHanhKiem)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-clipboard-question d-block"></i>
                        <p class="fw-bold">Chưa có dữ liệu hạnh kiểm</p>
                        <p class="small">Thông tin sẽ được cập nhật sau khi giáo viên chủ nhiệm đánh giá</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Học kỳ</th>
                                    <th>Loại HK</th>
                                    <th>Nghỉ phép</th>
                                    <th>Nghỉ không phép</th>
                                    <th>Vi phạm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($danhSachHanhKiem as $hk): ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <?php echo htmlspecialchars($hk['hocKy'] . ' - ' . $hk['namHoc']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo getHanhKiemBadge($hk['loaiHanhKiem']); ?>">
                                            <?php echo htmlspecialchars($hk['loaiHanhKiem']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $hk['soBuoiNghiCoPhep'] ?? 0; ?> buổi
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo $hk['soBuoiNghiKhongCoPhep'] ?? 0; ?> buổi
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?php echo $hk['soLanViPham'] ?? 0; ?> lần
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Thống kê tổng -->
                    <?php if (!empty($danhSachHanhKiem)): 
                        $tongNghiPhep = array_sum(array_column($danhSachHanhKiem, 'soBuoiNghiCoPhep'));
                        $tongNghiKhongPhep = array_sum(array_column($danhSachHanhKiem, 'soBuoiNghiKhongCoPhep'));
                        $tongViPham = array_sum(array_column($danhSachHanhKiem, 'soLanViPham'));
                    ?>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-box">
                                <h3><?php echo $tongNghiPhep; ?></h3>
                                <small class="text-muted">Tổng nghỉ phép</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <h3><?php echo $tongNghiKhongPhep; ?></h3>
                                <small class="text-muted">Nghỉ không phép</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <h3><?php echo $tongViPham; ?></h3>
                                <small class="text-muted">Tổng vi phạm</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Học lực -->
        <div class="col-lg-6 mb-4">
            <div class="card result-card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-chart-line text-primary me-2"></i>
                        Học lực
                    </h5>

                    <?php if (empty($danhSachHocLuc)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-chart-simple d-block"></i>
                        <p class="fw-bold">Chưa có dữ liệu học lực</p>
                        <p class="small">Thông tin sẽ được cập nhật sau khi kết thúc học kỳ</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($danhSachHocLuc as $hl): ?>
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        <?php echo htmlspecialchars($hl['hocKy'] . ' - ' . $hl['namHoc']); ?>
                                    </h6>
                                    <span class="badge bg-<?php echo getHocLucBadge($hl['xepLoaiHocLuc']); ?> me-2">
                                        <?php echo htmlspecialchars($hl['xepLoaiHocLuc']); ?>
                                    </span>
                                    <span class="badge bg-<?php echo getHanhKiemBadge($hl['hanhKiem']); ?>">
                                        HK: <?php echo htmlspecialchars($hl['hanhKiem']); ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <div class="display-6 fw-bold text-primary">
                                        <?php echo number_format($hl['diemTrungBinh'], 2); ?>
                                    </div>
                                    <small class="text-muted">Điểm TB</small>
                                </div>
                            </div>
                            
                            <?php if (!empty($hl['nhanXet'])): ?>
                            <hr>
                            <div class="bg-light p-3 rounded">
                                <strong class="d-block mb-2">
                                    <i class="fa-solid fa-comment-dots me-2"></i>Nhận xét:
                                </strong>
                                <p class="mb-0 small">
                                    <?php echo nl2br(htmlspecialchars($hl['nhanXet'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin bổ sung -->
    <div class="row">
        <div class="col-12">
            <div class="card result-card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fa-solid fa-info-circle text-info me-2"></i>
                        Quy định về hạnh kiểm & học lực
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Xếp loại hạnh kiểm:</h6>
                            <ul class="small">
                                <li><strong>Tốt:</strong> Không vi phạm, nghỉ học &lt; 5 buổi</li>
                                <li><strong>Khá:</strong> Vi phạm nhẹ, nghỉ học 5-10 buổi</li>
                                <li><strong>TB:</strong> Vi phạm vừa phải, nghỉ 10-15 buổi</li>
                                <li><strong>Yếu:</strong> Vi phạm nghiêm trọng, nghỉ &gt; 15 buổi</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Xếp loại học lực:</h6>
                            <ul class="small">
                                <li><strong>Giỏi:</strong> Điểm TB &ge; 8.0</li>
                                <li><strong>Khá:</strong> Điểm TB 6.5 - 7.9</li>
                                <li><strong>Trung bình:</strong> Điểm TB 5.0 - 6.4</li>
                                <li><strong>Yếu:</strong> Điểm TB &lt; 5.0</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

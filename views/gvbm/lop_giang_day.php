<?php
$pageTitle = 'Lớp giảng dạy - Giáo viên';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .class-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    
    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        border-color: #667eea;
    }
    
    .class-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
    }
    
    .class-card.chu-nhiem .class-card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .badge-khoi {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .student-count {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                Lớp giảng dạy
            </h2>
            <p class="text-muted mb-0">Danh sách lớp và học sinh bạn đang giảng dạy</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/public/index.php" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="lop_giang_day">
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Năm học</label>
                    <select name="namHoc" class="form-select">
                        <option value="2024-2025" <?php echo $selected_namHoc == '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                        <option value="2023-2024" <?php echo $selected_namHoc == '2023-2024' ? 'selected' : ''; ?>>2023-2024</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Học kỳ</label>
                    <select name="hocKy" class="form-select">
                        <option value="1" <?php echo $selected_hocKy == 'HK1' ? 'selected' : ''; ?>>Học kỳ I</option>
                        <option value="2" <?php echo $selected_hocKy == 'HK2' ? 'selected' : ''; ?>>Học kỳ II</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lớp chủ nhiệm (nếu là GVCN) -->
    <?php if ($lopChuNhiem): ?>
    <div class="alert alert-success mb-4">
        <h5 class="alert-heading">
            <i class="fa-solid fa-star me-2"></i>Lớp chủ nhiệm
        </h5>
        <hr>
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0"><?php echo htmlspecialchars($lopChuNhiem['tenLop']); ?></h4>
                <p class="mb-0 text-muted">Khối <?php echo htmlspecialchars($lopChuNhiem['khoi']); ?> - Sĩ số: <?php echo $lopChuNhiem['siSo']; ?> học sinh</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="/public/index.php?action=chi_tiet_lop&maLop=<?php echo urlencode($lopChuNhiem['maLop']); ?>" class="btn btn-success">
                    <i class="fa-solid fa-users me-2"></i>Xem danh sách học sinh
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Danh sách lớp giảng dạy -->
    <?php if (empty($danhSachLop)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
            <h5>Chưa có lớp giảng dạy</h5>
            <p class="mb-0">Bạn chưa được phân công giảng dạy lớp nào trong <?php echo $selected_hocKy; ?> - <?php echo $selected_namHoc; ?></p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php 
            // Group theo lớp
            $lopGroup = [];
            foreach ($danhSachLop as $lop) {
                $key = $lop['maLop'];
                if (!isset($lopGroup[$key])) {
                    $lopGroup[$key] = [
                        'info' => $lop,
                        'monHoc' => []
                    ];
                }
                $lopGroup[$key]['monHoc'][] = $lop['tenMon'];
            }
            ?>
            
            <?php foreach ($lopGroup as $maLop => $data): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card class-card h-100">
                    <div class="class-card-header">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($data['info']['tenLop']); ?></h4>
                                <span class="badge-khoi badge bg-light text-dark">
                                    Khối <?php echo htmlspecialchars($data['info']['khoi']); ?>
                                </span>
                            </div>
                            <i class="fa-solid fa-chalkboard fa-2x opacity-75"></i>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-users me-2"></i>
                            <p class="student-count mb-0"><?php echo $data['info']['siSo']; ?></p>
                            <span class="ms-2">học sinh</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fa-solid fa-book text-primary me-2"></i>Môn học giảng dạy:
                        </h6>
                        <div class="mb-3">
                            <?php foreach ($data['monHoc'] as $mon): ?>
                            <span class="badge bg-primary me-2 mb-2"><?php echo htmlspecialchars($mon); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>
                                <i class="fa-solid fa-user-tie me-1"></i>
                                GVCN: <?php echo htmlspecialchars($data['info']['tenGVCN'] ?? 'Chưa phân công'); ?>
                            </span>
                        </div>
                        
                        <a href="/public/index.php?action=chi_tiet_lop&maLop=<?php echo urlencode($maLop); ?>" class="btn btn-primary w-100 mt-3">
                            <i class="fa-solid fa-list me-2"></i>Xem danh sách học sinh
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

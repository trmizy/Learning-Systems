<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['gvbm', 'gvcn', 'ttbm']);

$pageTitle = 'Lịch giảng dạy - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .schedule-container {
        animation: fadeIn 0.5s ease;
    }
    
    .schedule-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    
    .schedule-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        font-weight: 600;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .schedule-table td {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        vertical-align: top;
        min-height: 80px;
    }
    
    .schedule-cell {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border-left: 4px solid #667eea;
    }
    
    .schedule-cell:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    /* MỚI: Phân biệt buổi sáng/chiều */
    .schedule-cell.afternoon {
        background: linear-gradient(135deg, rgba(245, 166, 35, 0.1) 0%, rgba(248, 196, 113, 0.05) 100%);
        border-left-color: #f5a623;
    }
    
    .schedule-cell.afternoon:hover {
        background: linear-gradient(135deg, rgba(245, 166, 35, 0.2) 0%, rgba(248, 196, 113, 0.1) 100%);
    }
    
    .period-label {
        background: #667eea;
        color: white;
        padding: 0.75rem;
        text-align: center;
        font-weight: 600;
    }
    
    /* MỚI: Phân biệt buổi sáng/chiều */
    .period-label.afternoon {
        background: #f5a623;
    }
    
    /* MỚI: Phân cách buổi */
    .session-divider {
        background: linear-gradient(90deg, transparent, #e9ecef, transparent);
        height: 8px;
    }
    
    .week-navigation {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .teacher-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    /* MỚI: Badge buổi học */
    .session-badge {
        background: white;
        color: #667eea;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .session-badge.afternoon {
        color: #f5a623;
    }
</style>

<div class="container-fluid schedule-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
            </li>
            <li class="breadcrumb-item active">Lịch giảng dạy</li>
        </ol>
    </nav>

    <!-- Thông tin giáo viên -->
    <?php if ($thongTinGV): ?>
    <div class="teacher-info-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">
                    <i class="fa-solid fa-chalkboard-user me-2"></i>
                    <?php echo htmlspecialchars($thongTinGV['hoTen']); ?>
                </h4>
                <p class="mb-0">
                    <i class="fa-solid fa-book me-2"></i>
                    Môn: <?php echo htmlspecialchars($thongTinGV['monHocPhuTrach']); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/public/index.php?action=gvbm-dashboard" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Week Navigation -->
    <div class="week-navigation">
        <div class="row align-items-center">
            <div class="col-md-4">
                <a href="?action=gvbm-lich-day&week=<?php echo $prevWeekDate; ?>" 
                   class="btn btn-outline-primary">
                    <i class="fa-solid fa-chevron-left me-2"></i>Tuần trước
                </a>
            </div>
            <div class="col-md-4 text-center">
                <h5 class="mb-0">
                    <i class="fa-solid fa-calendar-week me-2"></i>
                    <?php echo $tuanHienTai; ?>
                </h5>
                <small class="text-muted">
                    <input type="date" 
                           class="form-control form-control-sm mt-2" 
                           value="<?php echo $selected_date; ?>"
                           onchange="window.location.href='?action=gvbm-lich-day&week=' + this.value">
                </small>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="?action=gvbm-lich-day&week=<?php echo $nextWeekDate; ?>" 
                   class="btn btn-outline-primary">
                    Tuần sau<i class="fa-solid fa-chevron-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Schedule Table -->
    <div class="table-responsive">
        <table class="table schedule-table mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Tiết</th>
                    <?php foreach ($daysOfWeek as $day): ?>
                    <th>
                        <?php echo $day['name']; ?><br>
                        <small><?php echo $day['date']; ?></small>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <!-- BUỔI SÁNG: Tiết 1-5 -->
                <tr class="table-light">
                    <td colspan="8" class="text-center py-2">
                        <span class="session-badge">
                            <i class="fa-solid fa-sun me-2"></i>BUỔI SÁNG
                        </span>
                    </td>
                </tr>
                <?php for ($tiet = 1; $tiet <= 5; $tiet++): ?>
                <tr>
                    <td class="period-label">
                        Tiết <?php echo $tiet; ?>
                    </td>
                    <?php for ($ngay = 1; $ngay <= 7; $ngay++): ?>
                    <td>
                        <?php if (isset($lichDayGrid[$ngay][$tiet])): 
                            $cell = $lichDayGrid[$ngay][$tiet];
                        ?>
                        <div class="schedule-cell">
                            <div class="fw-bold text-primary mb-1">
                                <?php echo htmlspecialchars($cell['tenMon']); ?>
                            </div>
                            <div class="small mb-1">
                                <i class="fa-solid fa-users me-1"></i>
                                <?php echo htmlspecialchars($cell['tenLop']); ?>
                            </div>
                            <?php if (!empty($cell['tenPhong'])): ?>
                            <div class="small text-muted">
                                <i class="fa-solid fa-door-open me-1"></i>
                                <?php echo htmlspecialchars($cell['tenPhong']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>

                <!-- PHÂN CÁCH BUỔI -->
                <tr>
                    <td colspan="8" class="session-divider"></td>
                </tr>

                <!-- BUỔI CHIỀU: Tiết 6-10 -->
                <tr class="table-light">
                    <td colspan="8" class="text-center py-2">
                        <span class="session-badge afternoon">
                            <i class="fa-solid fa-cloud-sun me-2"></i>BUỔI CHIỀU
                        </span>
                    </td>
                </tr>
                <?php for ($tiet = 6; $tiet <= 10; $tiet++): ?>
                <tr>
                    <td class="period-label afternoon">
                        Tiết <?php echo $tiet; ?>
                    </td>
                    <?php for ($ngay = 1; $ngay <= 7; $ngay++): ?>
                    <td>
                        <?php if (isset($lichDayGrid[$ngay][$tiet])): 
                            $cell = $lichDayGrid[$ngay][$tiet];
                        ?>
                        <div class="schedule-cell afternoon">
                            <div class="fw-bold mb-1" style="color: #f5a623;">
                                <?php echo htmlspecialchars($cell['tenMon']); ?>
                            </div>
                            <div class="small mb-1">
                                <i class="fa-solid fa-users me-1"></i>
                                <?php echo htmlspecialchars($cell['tenLop']); ?>
                            </div>
                            <?php if (!empty($cell['tenPhong'])): ?>
                            <div class="small text-muted">
                                <i class="fa-solid fa-door-open me-1"></i>
                                <?php echo htmlspecialchars($cell['tenPhong']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Chú thích -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="alert alert-info">
                <i class="fa-solid fa-info-circle me-2"></i>
                <strong>Chú ý:</strong> Lịch dạy được tổng hợp từ bảng phân công giảng dạy và thời khóa biểu. 
                Nếu có thay đổi, vui lòng liên hệ TTBM hoặc BGH.
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-clock me-2"></i>Khung giờ học
                    </h6>
                    <div class="small">
                        <div class="mb-2">
                            <i class="fa-solid fa-sun me-2 text-primary"></i>
                            <strong>Buổi sáng:</strong> 7h00 - 11h00
                        </div>
                        <div>
                            <i class="fa-solid fa-cloud-sun me-2 text-warning"></i>
                            <strong>Buổi chiều:</strong> 13h00 - 17h00
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

<?php
/**
 * View: Phân công giáo viên bộ môn cho lớp
 * Path: views/bgh/assignments/assign_teachers.php
 * Data: $maLop, $thongTinLop, $namHoc, $hocKy, $danhSachMonHoc, $phanCongArray, $message, $messageType
 */

$pageTitle = 'Phân công giáo viên bộ môn - ' . $thongTinLop['tenLop'];
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .subject-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .subject-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .subject-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .subject-body {
        padding: 1rem;
    }
    
    .class-info-box {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .badge-assigned {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    
    .badge-unassigned {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }
</style>

<div class="assignment-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/views/bgh/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="manage.php">Phân công giảng dạy</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($thongTinLop['tenLop']); ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                Phân công giáo viên bộ môn
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-school me-1"></i>
                Lớp: <strong><?php echo htmlspecialchars($thongTinLop['tenLop']); ?></strong>
            </p>
        </div>
        <a href="manage.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Class Info Box -->
    <div class="class-info-box">
        <div class="row">
            <div class="col-md-3">
                <div class="info-item">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <div>
                        <small class="opacity-75">Khối</small>
                        <br><strong><?php echo htmlspecialchars($thongTinLop['khoi']); ?></strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <i class="fa-solid fa-users"></i>
                    <div>
                        <small class="opacity-75">Sĩ số</small>
                        <br><strong><?php echo $thongTinLop['siSo']; ?> học sinh</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <i class="fa-solid fa-user-tie"></i>
                    <div>
                        <small class="opacity-75">GVCN</small>
                        <br><strong><?php echo $thongTinLop['tenGVCN'] ?? 'Chưa gán'; ?></strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <i class="fa-solid fa-calendar"></i>
                    <div>
                        <small class="opacity-75">Năm học</small>
                        <br><strong><?php echo htmlspecialchars($thongTinLop['namHoc']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter học kỳ -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="maLop" value="<?php echo htmlspecialchars($maLop); ?>">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Học kỳ:</label>
                    <select name="hocKy" class="form-select" onchange="this.form.submit()">
                        <option value="HK1" <?php echo $hocKy === 'HK1' ? 'selected' : ''; ?>>Học kỳ I</option>
                        <option value="HK2" <?php echo $hocKy === 'HK2' ? 'selected' : ''; ?>>Học kỳ II</option>
                        <option value="Cả năm" <?php echo $hocKy === 'Cả năm' ? 'selected' : ''; ?>>Cả năm</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <button type="button" class="btn btn-primary" onclick="openThemPhanCongModal()">
                        <i class="fa-solid fa-plus me-2"></i>Thêm phân công mới
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách phân công hiện tại -->
    <div class="row">
        <?php 
        while ($mon = $danhSachMonHoc->fetch()): 
            $daPhanCong = isset($phanCongArray[$mon['maMonHoc']]);
            $pcInfo = $daPhanCong ? $phanCongArray[$mon['maMonHoc']] : null;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card subject-card">
                <div class="subject-header">
                    <div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($mon['tenMon']); ?></h6>
                        <small><?php echo $mon['soTietTuan']; ?> tiết/tuần</small>
                    </div>
                    <?php if ($daPhanCong): ?>
                    <span class="badge-assigned">
                        <i class="fa-solid fa-check-circle"></i>
                    </span>
                    <?php else: ?>
                    <span class="badge-unassigned">
                        <i class="fa-solid fa-exclamation-circle"></i>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="subject-body">
                    <?php if ($daPhanCong): ?>
                    <div class="mb-2">
                        <i class="fa-solid fa-user-tie text-primary me-2"></i>
                        <strong><?php echo htmlspecialchars($pcInfo['tenGV']); ?></strong>
                    </div>
                    <div class="mb-2 small text-muted">
                        <i class="fa-solid fa-envelope me-1"></i>
                        <?php echo htmlspecialchars($pcInfo['emailGV'] ?? 'N/A'); ?>
                    </div>
                    <div class="mb-3 small text-muted">
                        <i class="fa-solid fa-phone me-1"></i>
                        <?php echo htmlspecialchars($pcInfo['sdtGV'] ?? 'N/A'); ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" 
                                class="btn btn-sm btn-warning flex-fill"
                                onclick="openSuaPhanCongModal('<?php echo $mon['maMonHoc']; ?>', '<?php echo htmlspecialchars($mon['tenMon']); ?>', '<?php echo $pcInfo['maGV']; ?>')">
                            <i class="fa-solid fa-edit me-1"></i>Đổi GV
                        </button>
                        <button type="button" 
                                class="btn btn-sm btn-danger"
                                onclick="xoaPhanCong('<?php echo $pcInfo['maPhanCong']; ?>', '<?php echo htmlspecialchars($mon['tenMon']); ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        Chưa phân công giáo viên
                    </p>
                    <button type="button" 
                            class="btn btn-sm btn-primary w-100"
                            onclick="openThemPhanCongModal('<?php echo $mon['maMonHoc']; ?>', '<?php echo htmlspecialchars($mon['tenMon']); ?>')">
                        <i class="fa-solid fa-user-plus me-1"></i>Phân công
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal: Thêm/Sửa phân công -->
<div class="modal fade" id="modalPhanCong" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    <span id="modalTitleText">Phân công giáo viên</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formPhanCong">
                <div class="modal-body">
                    <input type="hidden" name="action" value="them_phan_cong">
                    <input type="hidden" name="maLop" value="<?php echo htmlspecialchars($maLop); ?>">
                    <input type="hidden" name="maMonHoc" id="maMonHoc">
                    <input type="hidden" name="namHoc" value="<?php echo htmlspecialchars($namHoc); ?>">
                    <input type="hidden" name="hocKy" value="<?php echo htmlspecialchars($hocKy); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Môn học:</label>
                        <div class="alert alert-info mb-0">
                            <i class="fa-solid fa-book me-2"></i>
                            <span id="tenMonHoc"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maGV" class="form-label fw-bold">
                            Chọn giáo viên:
                            <span class="text-danger">*</span>
                        </label>
                        <select name="maGV" id="maGV" class="form-select" required>
                            <option value="">-- Chọn giáo viên --</option>
                        </select>
                        <small class="text-muted">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Ưu tiên hiển thị giáo viên phù hợp với môn học
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalPhanCong;

document.addEventListener('DOMContentLoaded', function() {
    modalPhanCong = new bootstrap.Modal(document.getElementById('modalPhanCong'));
});

// Mở modal thêm phân công
function openThemPhanCongModal(maMonHoc = '', tenMonHoc = '') {
    document.getElementById('modalTitleText').textContent = 'Phân công giáo viên';
    
    if (maMonHoc && tenMonHoc) {
        document.getElementById('maMonHoc').value = maMonHoc;
        document.getElementById('tenMonHoc').textContent = tenMonHoc;
        loadGiaoVienTheoMon(tenMonHoc);
    } else {
        document.getElementById('maMonHoc').value = '';
        document.getElementById('tenMonHoc').textContent = 'Chọn môn học trước';
    }
    
    modalPhanCong.show();
}

// Mở modal sửa phân công (đổi GV)
function openSuaPhanCongModal(maMonHoc, tenMonHoc, maGVHienTai) {
    document.getElementById('modalTitleText').textContent = 'Đổi giáo viên';
    document.getElementById('maMonHoc').value = maMonHoc;
    document.getElementById('tenMonHoc').textContent = tenMonHoc;
    
    loadGiaoVienTheoMon(tenMonHoc, maGVHienTai);
    modalPhanCong.show();
}

// Load danh sách GV theo môn
function loadGiaoVienTheoMon(tenMon, maGVChon = '') {
    console.log('Đang tải GV cho môn:', tenMon);
    
    fetch(`api/get_giao_vien_theo_mon.php?tenMon=${encodeURIComponent(tenMon)}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Dữ liệu nhận được:', data);
            console.log('Số GV tìm thấy:', data.length);
            
            const select = document.getElementById('maGV');
            select.innerHTML = '<option value="">-- Chọn giáo viên --</option>';
            
            if (data.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '⚠️ Không tìm thấy giáo viên';
                option.disabled = true;
                select.appendChild(option);
                console.warn('Không tìm thấy giáo viên nào cho môn:', tenMon);
            } else {
                data.forEach(gv => {
                    const option = document.createElement('option');
                    option.value = gv.maGV;
                    option.textContent = `${gv.hoTen} - ${gv.monHocPhuTrach || 'Chưa có môn'}`;
                    if (gv.maGV === maGVChon) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Lỗi khi tải danh sách GV:', error);
            alert('Lỗi khi tải danh sách giáo viên. Vui lòng kiểm tra console để biết chi tiết.');
        });
}

// Xóa phân công
function xoaPhanCong(maPhanCong, tenMon) {
    if (confirm(`Bạn có chắc muốn xóa phân công giảng dạy môn ${tenMon}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="xoa_phan_cong">
            <input type="hidden" name="maPhanCong" value="${maPhanCong}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

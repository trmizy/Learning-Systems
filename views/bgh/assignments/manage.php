<?php
// View: Trang quản lý phân công giảng dạy và phòng học
$pageTitle = 'Phân công giảng dạy và phòng học - BGH';
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .assignment-container {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card-assignment {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
        margin-bottom: 1.5rem;
    }
    
    .card-assignment:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .table-assignment {
        margin-bottom: 0;
    }
    
    .table-assignment thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-assignment thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
    }
    
    .table-assignment tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-assignment tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }
    
    .badge-status {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .badge-assigned {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
    }
    
    .badge-unassigned {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }
    
    .btn-action {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        border: none;
        padding: 1.5rem;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-select, .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.6rem 1rem;
        transition: all 0.2s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
</style>

<div class="assignment-container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-user-gear text-primary me-2"></i>
                Phân công giảng dạy và phòng học
            </h2>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-info-circle me-1"></i>
                Quản lý phân công giáo viên chủ nhiệm và phòng học cho các lớp
            </p>
        </div>
        <a href="/views/bgh/dashboard.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Danh sách lớp học -->
    <div class="card card-assignment">
        <div class="card-body">
            <!-- Header with Year Filter -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-list-check text-success me-2"></i>
                    Danh sách lớp học
                </h5>
                
                <!-- Year Filter -->
                <div class="d-flex align-items-center gap-2">
                    <label for="namHocFilter" class="mb-0 text-muted">
                        <i class="fa-solid fa-calendar-alt me-1"></i>
                        Năm học:
                    </label>
                    <select id="namHocFilter" class="form-select form-select-sm" style="width: auto; min-width: 150px;">
                        <?php while($namHoc = $danhSachNamHoc->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo htmlspecialchars($namHoc['namHoc']); ?>" 
                                    <?php echo $namHoc['namHoc'] === $namHocFilter ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($namHoc['namHoc']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-assignment table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lớp</th>
                            <th>Khối</th>
                            <th>Sĩ số</th>
                            <th>GVCN</th>
                            <th>Phòng học</th>
                            <th>Năm học</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($lop = $danhSachLop->fetch()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($lop['tenLop']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($lop['maLop']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary">Khối <?php echo htmlspecialchars($lop['khoi']); ?></span>
                            </td>
                            <td>
                                <i class="fa-solid fa-users me-1"></i>
                                <?php echo $lop['siSo']; ?> HS
                            </td>
                            <td>
                                <?php if ($lop['maGVCN']): ?>
                                    <div class="info-badge">
                                        <i class="fa-solid fa-user-tie text-primary"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($lop['tenGVCN']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($lop['maGVCN']); ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">
                                        <i class="fa-solid fa-exclamation-circle me-1"></i>Chưa gán
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lop['maPhong']): ?>
                                    <div class="info-badge">
                                        <i class="fa-solid fa-door-open text-success"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($lop['tenPhong']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($lop['maPhong']); ?></small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-unassigned">
                                        <i class="fa-solid fa-exclamation-circle me-1"></i>Chưa gán
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-calendar me-1"></i>
                                <?php echo htmlspecialchars($lop['namHoc']); ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1">
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary btn-action"
                                                onclick="openGanGVCNModal('<?php echo $lop['maLop']; ?>', '<?php echo htmlspecialchars($lop['tenLop']); ?>', '<?php echo $lop['maGVCN'] ?? ''; ?>')"
                                                title="Gán GVCN">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-success btn-action"
                                                onclick="openGanPhongModal('<?php echo $lop['maLop']; ?>', '<?php echo htmlspecialchars($lop['tenLop']); ?>', '<?php echo $lop['maPhong'] ?? ''; ?>')"
                                                title="Gán phòng học">
                                            <i class="fa-solid fa-door-open"></i>
                                        </button>
                                        <?php if ($lop['maGVCN']): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger btn-action"
                                                onclick="xoaGVCN('<?php echo $lop['maLop']; ?>', '<?php echo htmlspecialchars($lop['tenLop']); ?>')"
                                                title="Xóa GVCN">
                                            <i class="fa-solid fa-user-xmark"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($lop['maPhong']): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-warning btn-action"
                                                onclick="xoaPhong('<?php echo $lop['maLop']; ?>', '<?php echo htmlspecialchars($lop['tenLop']); ?>')"
                                                title="Xóa phòng">
                                            <i class="fa-solid fa-door-closed"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <a href="assign_teachers.php?maLop=<?php echo urlencode($lop['maLop']); ?>" 
                                       class="btn btn-sm btn-info btn-action"
                                       title="Phân công GV bộ môn">
                                        <i class="fa-solid fa-chalkboard-user me-1"></i>
                                        Phân công GV bộ môn
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gán GVCN -->
<div class="modal fade" id="modalGanGVCN" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user-plus me-2"></i>
                    Gán giáo viên chủ nhiệm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="gan_gvcn">
                    <input type="hidden" name="maLop" id="maLopGVCN">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lớp học</label>
                        <input type="text" class="form-control" id="tenLopGVCN" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn giáo viên chủ nhiệm</label>
                        <select name="maGV" id="maGV" class="form-select" required>
                            <option value="">-- Chọn giáo viên --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Gán phòng học -->
<div class="modal fade" id="modalGanPhong" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-door-open me-2"></i>
                    Gán phòng học
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="gan_phong">
                    <input type="hidden" name="maLop" id="maLopPhong">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lớp học</label>
                        <input type="text" class="form-control" id="tenLopPhong" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn phòng học</label>
                        <select name="maPhong" id="maPhong" class="form-select" required>
                            <option value="">-- Chọn phòng học --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mở modal gán GVCN
function openGanGVCNModal(maLop, tenLop, maGVHienTai) {
    document.getElementById('maLopGVCN').value = maLop;
    document.getElementById('tenLopGVCN').value = tenLop;
    
    // Load danh sách GV
    fetch(`api/get_giao_vien.php?maLop=${maLop}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('maGV');
            select.innerHTML = '<option value="">-- Chọn giáo viên --</option>';
            data.forEach(gv => {
                const option = document.createElement('option');
                option.value = gv.maGV;
                option.textContent = `${gv.hoTen} (${gv.monHocPhuTrach || 'N/A'})`;
                if (gv.maGV === maGVHienTai) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        });
    
    new bootstrap.Modal(document.getElementById('modalGanGVCN')).show();
}

// Mở modal gán phòng học
function openGanPhongModal(maLop, tenLop, maPhongHienTai) {
    document.getElementById('maLopPhong').value = maLop;
    document.getElementById('tenLopPhong').value = tenLop;
    
    console.log('Đang load phòng học cho lớp:', maLop);
    
    // Load danh sách phòng
    fetch(`api/get_phong_hoc.php?maLop=${maLop}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dữ liệu phòng học nhận được:', data);
            console.log('Số lượng phòng:', data.length);
            
            const select = document.getElementById('maPhong');
            select.innerHTML = '<option value="">-- Chọn phòng học --</option>';
            
            if (data.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '⚠️ Không có phòng khả dụng';
                option.disabled = true;
                select.appendChild(option);
                console.warn('Không tìm thấy phòng học nào khả dụng');
            } else {
                data.forEach(phong => {
                    const option = document.createElement('option');
                    option.value = phong.maPhong;
                    option.textContent = `${phong.tenPhong} (Sức chứa: ${phong.sucChua || 'N/A'})`;
                    if (phong.maPhong === maPhongHienTai) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Lỗi khi tải danh sách phòng học:', error);
            const select = document.getElementById('maPhong');
            select.innerHTML = '<option value="">❌ Lỗi tải dữ liệu</option>';
            alert('Không thể tải danh sách phòng học. Vui lòng kiểm tra console để biết chi tiết.');
        });
    
    new bootstrap.Modal(document.getElementById('modalGanPhong')).show();
}

// Xóa GVCN
function xoaGVCN(maLop, tenLop) {
    if (confirm(`Bạn có chắc muốn xóa giáo viên chủ nhiệm của lớp ${tenLop}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="xoa_gvcn">
            <input type="hidden" name="maLop" value="${maLop}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Xóa phòng học
function xoaPhong(maLop, tenLop) {
    if (confirm(`Bạn có chắc muốn xóa phòng học của lớp ${tenLop}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="xoa_phong">
            <input type="hidden" name="maLop" value="${maLop}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Xử lý thay đổi năm học
document.getElementById('namHocFilter').addEventListener('change', function() {
    const namHoc = this.value;
    window.location.href = `?namHoc=${encodeURIComponent(namHoc)}`;
});
</script>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

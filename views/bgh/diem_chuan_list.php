<?php
$pageTitle = 'Quản lý Điểm chuẩn';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .score-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }
    
    .score-card h3 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .table-custom {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    
    .table-custom thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-custom tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
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
                        <i class="fa-solid fa-chart-line text-warning me-2"></i>
                        Quản lý Điểm chuẩn tuyển sinh
                    </h2>
                    <p class="text-muted mb-0">Quản lý điểm chuẩn các năm tuyển sinh</p>
                </div>
                <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#modalThemDiemChuan">
                    <i class="fa-solid fa-plus me-2"></i>Thêm điểm chuẩn
                </button>
            </div>
        </div>
    </div>

    <!-- Danh sách điểm chuẩn -->
    <div class="card table-custom">
        <div class="card-body p-0">
            <?php if (empty($danhSachDiemChuan)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-chart-simple d-block"></i>
                <h5 class="fw-bold">Chưa có điểm chuẩn nào</h5>
                <p>Nhấn nút "Thêm điểm chuẩn" để bắt đầu</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">STT</th>
                            <th>Năm tuyển sinh</th>
                            <th class="text-center">Điểm chuẩn</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachDiemChuan as $index => $dc): ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $index + 1; ?></td>
                            <td>
                                <i class="fa-solid fa-calendar me-2 text-primary"></i>
                                <strong><?php echo htmlspecialchars($dc['namTuyenSinh']); ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success fs-5 px-4 py-2">
                                    <?php echo number_format($dc['soDiem'], 2); ?> điểm
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning me-2" 
                                        onclick="suaDiemChuan('<?php echo $dc['maDiemChuan']; ?>', <?php echo $dc['soDiem']; ?>, <?php echo $dc['namTuyenSinh']; ?>)">
                                    <i class="fa-solid fa-edit"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="xoaDiemChuan('<?php echo $dc['maDiemChuan']; ?>', <?php echo $dc['namTuyenSinh']; ?>)">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Thêm điểm chuẩn -->
<div class="modal fade" id="modalThemDiemChuan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/public/index.php?action=bgh-diem-chuan-them">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus me-2"></i>Thêm điểm chuẩn mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Năm tuyển sinh <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="namTuyenSinh" 
                               min="2020" max="2100" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Điểm chuẩn (0-30) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="soDiem" 
                               min="0" max="30" step="0.01" placeholder="VD: 25.50" required>
                        <div class="form-text">Điểm chuẩn = (Toán × 2) + (Văn × 2) + Anh</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa điểm chuẩn -->
<div class="modal fade" id="modalSuaDiemChuan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/public/index.php?action=bgh-diem-chuan-sua">
                <input type="hidden" name="maDiemChuan" id="editMaDiemChuan">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-edit me-2"></i>Cập nhật điểm chuẩn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Năm tuyển sinh</label>
                        <input type="number" class="form-control" id="editNamTuyenSinh" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Điểm chuẩn mới (0-30) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="soDiem" id="editSoDiem"
                               min="0" max="30" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-save me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Xóa (hidden) -->
<form id="formXoaDiemChuan" method="POST" action="/public/index.php?action=bgh-diem-chuan-xoa" style="display: none;">
    <input type="hidden" name="maDiemChuan" id="deleteMaDiemChuan">
</form>

<script>
function suaDiemChuan(maDiemChuan, soDiem, namTuyenSinh) {
    document.getElementById('editMaDiemChuan').value = maDiemChuan;
    document.getElementById('editSoDiem').value = soDiem;
    document.getElementById('editNamTuyenSinh').value = namTuyenSinh;
    
    const modal = new bootstrap.Modal(document.getElementById('modalSuaDiemChuan'));
    modal.show();
}

function xoaDiemChuan(maDiemChuan, namTuyenSinh) {
    if (confirm(`Bạn có chắc chắn muốn xóa điểm chuẩn năm ${namTuyenSinh}?\nThao tác này không thể hoàn tác!`)) {
        document.getElementById('deleteMaDiemChuan').value = maDiemChuan;
        document.getElementById('formXoaDiemChuan').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

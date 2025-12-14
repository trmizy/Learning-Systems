<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['ph']);

$pageTitle = 'Tạo đơn xin nghỉ - THPT';
require_once __DIR__ . '/../../layouts/header.php';
?>

<style>
    .leave-form-container {
        max-width: 800px;
        margin: 0 auto;
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .form-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    
    .form-card .card-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 16px 16px 0 0 !important;
        padding: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .required {
        color: #f5576c;
    }
    
    .file-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    
    .btn-file-upload {
        border: 2px dashed #dee2e6;
        padding: 2rem;
        text-align: center;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .btn-file-upload:hover {
        border-color: #f5576c;
        background: rgba(245, 87, 108, 0.05);
    }
    
    .file-input {
        position: absolute;
        font-size: 100px;
        opacity: 0;
        right: 0;
        top: 0;
        cursor: pointer;
    }
    
    .preview-section {
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        display: none;
    }
</style>

<div class="container-fluid leave-form-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=ph-leave-list">Đơn xin nghỉ</a>
            </li>
            <li class="breadcrumb-item active">Tạo đơn mới</li>
        </ol>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card form-card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fa-solid fa-file-medical me-2"></i>Tạo đơn xin nghỉ học
            </h4>
        </div>
        <div class="card-body p-4">
            <form action="/public/index.php?action=ph-leave-store" method="POST" enctype="multipart/form-data" id="leaveRequestForm">
                <!-- Chọn con -->
                <div class="mb-4">
                    <label for="maHS" class="form-label">
                        Chọn con em <span class="required">*</span>
                    </label>
                    <select class="form-select" id="maHS" name="maHS" required>
                        <?php foreach ($danhSachCon as $con): ?>
                            <option value="<?php echo htmlspecialchars($con['maHS']); ?>" 
                                    <?php echo ($con['maHS'] == $maHS) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($con['hoTen']); ?> - <?php echo htmlspecialchars($con['tenLop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ngày nghỉ -->
                <div class="mb-4">
                    <label for="ngayNghi" class="form-label">
                        Ngày nghỉ <span class="required">*</span>
                    </label>
                    <input type="date" 
                           class="form-control" 
                           id="ngayNghi" 
                           name="ngayNghi" 
                           min="<?php echo date('Y-m-d'); ?>"
                           required>
                    <div class="form-text">
                        <i class="fa-solid fa-info-circle me-1"></i>Chọn ngày bắt đầu nghỉ học
                    </div>
                </div>

                <!-- Số buổi nghỉ -->
                <div class="mb-4">
                    <label for="soBuoi" class="form-label">
                        Số buổi nghỉ <span class="required">*</span>
                    </label>
                    <select class="form-select" id="soBuoi" name="soBuoi" required>
                        <option value="1">1 buổi</option>
                        <option value="2">2 buổi (cả ngày)</option>
                        <option value="3">3 buổi</option>
                        <option value="4">4 buổi (2 ngày)</option>
                        <option value="5">5 buổi</option>
                        <option value="6">6 buổi (3 ngày)</option>
                    </select>
                </div>

                <!-- Lý do -->
                <div class="mb-4">
                    <label for="lyDo" class="form-label">
                        Lý do nghỉ học <span class="required">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="lyDo" 
                              name="lyDo" 
                              rows="4" 
                              placeholder="Vui lòng nêu rõ lý do con em xin nghỉ học..."
                              required></textarea>
                    <div class="form-text">
                        <i class="fa-solid fa-lightbulb me-1"></i>Ví dụ: Ốm đau, việc gia đình khẩn cấp, tham gia hoạt động...
                    </div>
                </div>

                <!-- Upload minh chứng -->
                <div class="mb-4">
                    <label class="form-label">
                        Minh chứng kèm theo (tùy chọn)
                    </label>
                    <div class="btn-file-upload" onclick="document.getElementById('minhChung').click()">
                        <i class="fa-solid fa-cloud-arrow-up fa-3x text-muted mb-3 d-block"></i>
                        <p class="mb-0">
                            <strong>Chọn file</strong> hoặc kéo thả vào đây
                        </p>
                        <small class="text-muted">Định dạng: JPG, PNG, PDF (Tối đa 5MB)</small>
                    </div>
                    <input type="file" 
                           class="d-none" 
                           id="minhChung" 
                           name="minhChung" 
                           accept=".jpg,.jpeg,.png,.pdf">
                    
                    <div class="preview-section" id="previewSection">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fa-solid fa-file text-primary me-2"></i>
                                <span id="fileName"></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="/public/index.php?action=ph-leave-list" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-times me-2"></i>Hủy
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi đơn
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hướng dẫn -->
    <div class="card mt-4" style="border-left: 4px solid #f5576c;">
        <div class="card-body">
            <h6 class="fw-bold text-danger">
                <i class="fa-solid fa-circle-info me-2"></i>Lưu ý quan trọng
            </h6>
            <ul class="mb-0 small">
                <li>Đơn xin nghỉ cần được gửi <strong>trước ngày nghỉ</strong> để giáo viên kịp phê duyệt</li>
                <li>Với trường hợp ốm đau, vui lòng đính kèm <strong>giấy khám bệnh hoặc đơn thuốc</strong></li>
                <li>Đơn sẽ được giáo viên chủ nhiệm xét duyệt trong vòng <strong>24 giờ</strong></li>
                <li>Phụ huynh có thể <strong>hủy đơn</strong> khi đơn đang ở trạng thái chờ duyệt</li>
                <li>Sau khi được duyệt, đơn <strong>không thể chỉnh sửa hoặc hủy</strong></li>
            </ul>
        </div>
    </div>
</div>

<script>
// Xử lý upload file
document.getElementById('minhChung').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Kiểm tra kích thước file (5MB = 5 * 1024 * 1024 bytes)
        if (file.size > 5 * 1024 * 1024) {
            alert('File quá lớn! Vui lòng chọn file dưới 5MB.');
            e.target.value = '';
            return;
        }
        
        // Hiển thị preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('previewSection').style.display = 'block';
    }
});

function removeFile() {
    document.getElementById('minhChung').value = '';
    document.getElementById('previewSection').style.display = 'none';
}

// Validate form trước khi submit
document.getElementById('leaveRequestForm').addEventListener('submit', function(e) {
    const ngayNghi = document.getElementById('ngayNghi').value;
    const today = new Date().toISOString().split('T')[0];
    
    if (ngayNghi < today) {
        e.preventDefault();
        alert('Ngày nghỉ không được chọn ngày trong quá khứ!');
        return false;
    }
});
</script>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>

<?php
$pageTitle = 'Upload điểm thi tuyển sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">
                    <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>
                    Upload điểm thi tuyển sinh
                </h2>
                <div>
                    <a href="/public/index.php?action=nhanvienso-tuyen-sinh-list" class="btn btn-outline-secondary me-2">
                        <i class="fa-solid fa-list me-1"></i>Danh sách
                    </a>
                    <a href="/public/index.php?action=nhanvienso-dashboard" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa-solid fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Hướng dẫn - CẬP NHẬT THỨ TỰ CỘT ĐÚNG -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <i class="fa-solid fa-circle-info me-2"></i>Hướng dẫn
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Định dạng file Excel:</h6>
                    <ul class="mb-3">
                        <li><strong>Cột A:</strong> Mã thí sinh (bắt buộc)</li>
                        <li><strong>Cột B:</strong> Họ và tên (bắt buộc)</li>
                        <li><strong>Cột C:</strong> Số CCCD (bắt buộc)</li>
                        <li><strong>Cột D:</strong> Ngày sinh (dd/mm/yyyy hoặc yyyy-mm-dd)</li>
                        <li><strong>Cột E:</strong> Điểm Văn</li>
                        <li><strong>Cột F:</strong> Điểm Toán</li>
                        <li><strong>Cột G:</strong> Điểm Anh</li>
                        <li><strong>Cột H:</strong> Số điện thoại</li>
                        <li><strong>Cột I:</strong> Nơi sinh</li>
                        <li><strong>Cột J:</strong> Giới tính (Nam/Nữ)</li>
                    </ul>
                    <p class="mb-0">
                        <strong>Công thức điểm:</strong> Tổng điểm = (Toán × 2) + (Văn × 2) + Anh
                    </p>
                    <p class="text-danger small mb-0">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Dòng đầu tiên là tiêu đề, hệ thống sẽ bỏ qua khi import
                    </p>
                </div>
            </div>

            <!-- Form Upload - FIX: Đảm bảo action đúng -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" 
                          action="/public/index.php?action=nhanvienso-tuyen-sinh-process" 
                          enctype="multipart/form-data" 
                          id="uploadForm">
                        
                        <!-- Năm tuyển sinh - FIX: Thêm selected mặc định -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Năm tuyển sinh <span class="text-danger">*</span>
                            </label>
                            <select name="namTuyenSinh" class="form-select" required id="namTuyenSinhSelect">
                                <option value="">-- Chọn năm --</option>
                                <?php 
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= 2020; $year--): 
                                ?>
                                    <option value="<?php echo $year; ?>" 
                                            <?php echo $year == $currentYear ? 'selected' : ''; ?>>
                                        Năm <?php echo $year; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <div class="invalid-feedback">Vui lòng chọn năm tuyển sinh</div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Chọn file Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="fileExcel" class="form-control" 
                                   accept=".xls,.xlsx" required id="fileInput">
                            <div class="form-text">
                                <i class="fa-solid fa-file-excel text-success me-1"></i>
                                Chỉ chấp nhận file .xls hoặc .xlsx (tối đa 5MB)
                            </div>
                        </div>

                        <!-- Preview thông tin file -->
                        <div id="fileInfo" class="alert alert-light d-none">
                            <strong>File đã chọn:</strong> <span id="fileName"></span><br>
                            <strong>Kích thước:</strong> <span id="fileSize"></span>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fa-solid fa-rotate-left me-2"></i>Đặt lại
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fa-solid fa-upload me-2"></i>Upload dữ liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Download template -->
            <div class="card mt-4 border-success">
                <div class="card-body">
                    <h6 class="fw-bold">
                        <i class="fa-solid fa-download text-success me-2"></i>
                        Tải file mẫu
                    </h6>
                    <p class="mb-3">Download file Excel mẫu để tham khảo định dạng</p>
                    
                    <!-- ⚠️ SỬA: Đổi link tĩnh thành route dynamic -->
                    <a href="/public/index.php?action=nhanvienso-export-template" class="btn btn-success">
                        <i class="fa-solid fa-file-excel me-2"></i>Tải file mẫu (.xlsx)
                    </a>
                    
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        File mẫu bao gồm 5 dòng dữ liệu mẫu và ghi chú chi tiết
                    </p>
                </div>
            </div>

            <!-- Hướng dẫn sửa lỗi Excel - CẬP NHẬT BẢNG ĐÚNG -->
            <div class="alert alert-info">
                <h5><i class="fa-solid fa-circle-info me-2"></i>Lưu ý cấu trúc file Excel:</h5>
                <table class="table table-sm table-bordered mt-2 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cột</th>
                            <th>Tên cột</th>
                            <th>Format</th>
                            <th>Ví dụ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>A</td>
                            <td>Mã Thí Sinh</td>
                            <td>Text</td>
                            <td>TS258188103</td>
                        </tr>
                        <tr>
                            <td>B</td>
                            <td>Họ Tên</td>
                            <td>Text</td>
                            <td>Dương Quốc Việt</td>
                        </tr>
                        <tr>
                            <td>C</td>
                            <td>CCCD</td>
                            <td>Text/Number</td>
                            <td>76974215422</td>
                        </tr>
                        <tr>
                            <td>D</td>
                            <td>Ngày Sinh</td>
                            <td>Date (dd/mm/yyyy)</td>
                            <td>07/04/2011</td>
                        </tr>
                        <tr>
                            <td>E</td>
                            <td>Điểm Văn</td>
                            <td>Number</td>
                            <td>1.2</td>
                        </tr>
                        <tr>
                            <td>F</td>
                            <td>Điểm Toán</td>
                            <td>Number</td>
                            <td>6.0</td>
                        </tr>
                        <tr>
                            <td>G</td>
                            <td>Điểm Anh</td>
                            <td>Number</td>
                            <td>1.7</td>
                        </tr>
                        <tr>
                            <td>H</td>
                            <td>Số Điện Thoại</td>
                            <td>Text</td>
                            <td>3876798520</td>
                        </tr>
                        <tr>
                            <td>I</td>
                            <td>Nơi Sinh</td>
                            <td>Text</td>
                            <td>Đồng Nai</td>
                        </tr>
                        <tr class="table-warning">
                            <td>J</td>
                            <td><strong>Giới Tính</strong></td>
                            <td><strong>⚠️ TEXT (không được để số!)</strong></td>
                            <td><strong>"Nam" hoặc "Nữ"</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>LỖI THƯỜNG GẶP:</strong> Cột <strong>Giới Tính (cột J)</strong> đang để format <strong>Số</strong> thay vì <strong>Chữ</strong>
                    <br>
                    <strong>Cách sửa:</strong> Chọn cột J → Format Cells → Text → Nhập lại "Nam" hoặc "Nữ"
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview file info
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
        document.getElementById('fileInfo').classList.remove('d-none');
    }
});

// Validate form trước khi submit
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const namTuyenSinh = document.querySelector('[name="namTuyenSinh"]').value;
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    // Kiểm tra năm tuyển sinh
    if (!namTuyenSinh || namTuyenSinh === '') {
        e.preventDefault();
        alert('⚠️ Vui lòng chọn năm tuyển sinh!');
        document.querySelector('[name="namTuyenSinh"]').focus();
        return false;
    }
    
    // Kiểm tra file
    if (!file) {
        e.preventDefault();
        alert('⚠️ Vui lòng chọn file Excel!');
        fileInput.focus();
        return false;
    }
    
    // Kiểm tra kích thước file
    if (file.size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('⚠️ File vượt quá 5MB. Vui lòng chọn file nhỏ hơn.');
        return false;
    }
    
    // Hiển thị loading
    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

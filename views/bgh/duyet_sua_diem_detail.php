<?php
$pageTitle = 'Chi tiết yêu cầu sửa điểm - BGH';
require_once __DIR__ . '/../layouts/header.php';

// Map tên loại điểm
$tenLoaiDiem = '';
if($yeuCau['loaiDiem'] == 'diemThuongXuyen') $tenLoaiDiem = 'Điểm Thường xuyên';
elseif($yeuCau['loaiDiem'] == 'diemGiuaKy') $tenLoaiDiem = 'Điểm Giữa kỳ';
elseif($yeuCau['loaiDiem'] == 'diemCuoiKy') $tenLoaiDiem = 'Điểm Cuối kỳ';
?>

<div class="container py-4">
    <a href="index.php?action=bgh-duyet-sua-diem" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>

    <h2 class="mb-4 fw-bold">
        <i class="fa-solid fa-file-invoice text-primary me-2"></i>
        Chi tiết yêu cầu sửa điểm
    </h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-info-circle me-2"></i>Thông tin yêu cầu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Học sinh:</label>
                            <p class="fw-bold"><?= htmlspecialchars($yeuCau['tenHocSinh']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Mã học sinh:</label>
                            <p class="fw-bold"><?= htmlspecialchars($yeuCau['maHS']) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Lớp:</label>
                            <p><span class="badge bg-info fs-6"><?= htmlspecialchars($yeuCau['tenLop']) ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Môn học:</label>
                            <p class="fw-bold"><?= htmlspecialchars($yeuCau['tenMon']) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Năm học:</label>
                            <p><?= htmlspecialchars($yeuCau['namHoc']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Học kỳ:</label>
                            <p><?= htmlspecialchars($yeuCau['hocKy']) ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Loại điểm:</label>
                            <p><span class="badge bg-secondary fs-6"><?= $tenLoaiDiem ?></span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Điểm hiện tại:</label>
                            <p class="fs-4 fw-bold text-danger"><?= $yeuCau['diemCu'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Điểm đề nghị:</label>
                            <p class="fs-4 fw-bold text-primary"><?= $yeuCau['diemMoi'] ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Lý do sửa điểm:</label>
                        <div class="alert alert-warning">
                            <i class="fa-solid fa-quote-left me-2"></i>
                            <?= htmlspecialchars($yeuCau['lyDo']) ?>
                            <i class="fa-solid fa-quote-right ms-2"></i>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Giáo viên yêu cầu:</label>
                            <p><?= htmlspecialchars($yeuCau['tenGiaoVien']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Thời gian gửi:</label>
                            <p><?= date('d/m/Y H:i:s', strtotime($yeuCau['ngayYeuCau'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-primary mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-check-circle me-2"></i>Duyệt yêu cầu
                    </h5>
                </div>
                <div class="card-body">
                    <form action="index.php?action=bgh-duyet-sua-diem-duyet" method="POST" onsubmit="return confirm('Xác nhận DUYỆT yêu cầu này? Điểm sẽ được cập nhật ngay lập tức.');">
                        <input type="hidden" name="maYeuCau" value="<?= $yeuCau['maYeuCau'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Ghi chú (tùy chọn):</label>
                            <textarea name="ghiChu" class="form-control" rows="3" placeholder="Nhập ghi chú nếu cần..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa-solid fa-check me-2"></i>Xác nhận duyệt
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-times-circle me-2"></i>Từ chối yêu cầu
                    </h5>
                </div>
                <div class="card-body">
                    <form action="index.php?action=bgh-duyet-sua-diem-tu-choi" method="POST" onsubmit="return confirm('Xác nhận TỪ CHỐI yêu cầu này?');">
                        <input type="hidden" name="maYeuCau" value="<?= $yeuCau['maYeuCau'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Lý do từ chối <span class="text-danger">*</span>:</label>
                            <textarea name="lyDoTuChoi" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa-solid fa-times me-2"></i>Từ chối yêu cầu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

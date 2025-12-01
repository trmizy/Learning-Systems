<?php
// File: views/gvbm/yeu_cau_sua_diem.php
?>

<div class="container mt-4">
    <h2><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Quản lý Yêu cầu Sửa điểm</h2>
    
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> 
            <?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> 
            <?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm bg-light">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="yeu_cau_sua_diem">
                
                <div class="col-md-5">
                    <label class="form-label fw-bold">Chọn Lớp:</label>
                    <select name="maLop" class="form-select" required onchange="this.form.submit()">
                        <option value="">-- Chọn lớp --</option>
                        <?php 
                        $uniqueClass = [];
                        foreach ($danhSachLop as $l) {
                            if (!in_array($l['maLop'], $uniqueClass)) {
                                $uniqueClass[] = $l['maLop'];
                                $selected = ($selectedLop == $l['maLop']) ? 'selected' : '';
                                echo "<option value='{$l['maLop']}' $selected> {$l['tenLop']}</option>";
                            }
                        } 
                        ?>
                    </select>
                </div>

                <div class="col-md-5">
                     <label class="form-label fw-bold">Môn học:</label>
                     <select name="maMon" class="form-select" required>
                        <option value="">-- Chọn môn --</option>
                        <?php 
                        foreach ($danhSachLop as $l) {
                            if ($selectedLop && $l['maLop'] == $selectedLop) {
                                $selected = ($selectedMon == $l['maMonHoc']) ? 'selected' : '';
                                echo "<option value='{$l['maMonHoc']}' $selected>{$l['tenMon']}</option>";
                            }
                        }
                        ?>
                     </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-1"></i> Xem
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selectedLop && $selectedMon): 
        $tenMonHienTai = "";
        foreach($danhSachLop as $l) if($l['maMonHoc'] == $selectedMon) $tenMonHienTai = $l['tenMon'];
    ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bangdiem-tab" data-bs-toggle="tab" data-bs-target="#bangdiem-pane" type="button">
                    <i class="fa-solid fa-table me-2"></i>Bảng điểm (Tạo yêu cầu)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lichsu-tab" data-bs-toggle="tab" data-bs-target="#lichsu-pane" type="button">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử yêu cầu
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 bg-white rounded-bottom shadow-sm" id="myTabContent">
            
            <div class="tab-pane fade show active" id="bangdiem-pane" role="tabpanel">
                <?php if (!empty($bangDiem)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">STT</th>
                                    <th width="15%">Mã HS</th>
                                    <th class="text-start">Họ và Tên</th>
                                    <th width="15%">Đ. Thường xuyên</th>
                                    <th width="15%">Đ. Giữa kỳ</th>
                                    <th width="15%">Đ. Cuối kỳ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt=1; foreach ($bangDiem as $bd): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo $bd['maHS']; ?></td>
                                    <td class="text-start fw-bold"><?php echo htmlspecialchars($bd['hoTen']); ?></td>
                                    
                                    <td>
                                        <button class="btn btn-sm btn-outline-dark w-100 border-0 py-2" 
                                                onclick="openModal('<?php echo $bd['maBangDiem']; ?>', 'diemThuongXuyen', <?php echo $bd['diemThuongXuyen'] ?? 0; ?>, '<?php echo $bd['hoTen']; ?>', 'Điểm Thường xuyên', '<?php echo $tenMonHienTai; ?>')">
                                            <?php echo $bd['diemThuongXuyen'] ?? '-'; ?> <i class="fa-solid fa-pen ms-1 text-muted" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-dark w-100 border-0 py-2" 
                                                onclick="openModal('<?php echo $bd['maBangDiem']; ?>', 'diemGiuaKy', <?php echo $bd['diemGiuaKy'] ?? 0; ?>, '<?php echo $bd['hoTen']; ?>', 'Điểm Giữa kỳ', '<?php echo $tenMonHienTai; ?>')">
                                            <?php echo $bd['diemGiuaKy'] ?? '-'; ?> <i class="fa-solid fa-pen ms-1 text-muted" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-dark w-100 border-0 py-2" 
                                                onclick="openModal('<?php echo $bd['maBangDiem']; ?>', 'diemCuoiKy', <?php echo $bd['diemCuoiKy'] ?? 0; ?>, '<?php echo $bd['hoTen']; ?>', 'Điểm Cuối kỳ', '<?php echo $tenMonHienTai; ?>')">
                                            <?php echo $bd['diemCuoiKy'] ?? '-'; ?> <i class="fa-solid fa-pen ms-1 text-muted" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">Chưa có dữ liệu điểm cho lớp này.</div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="lichsu-pane" role="tabpanel">
                <?php if (!empty($lichSuYeuCau)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Học sinh</th>
                                    <th>Loại điểm</th>
                                    <th>Điểm cũ</th>
                                    <th>Điểm mới</th>
                                    <th>Lý do</th>
                                    <th>Trạng thái</th> </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lichSuYeuCau as $yc): 
                                    // Map tên loại điểm cho đẹp
                                    $tenLoai = '';
                                    if($yc['loaiDiem'] == 'diemThuongXuyen') $tenLoai = 'Thường xuyên';
                                    elseif($yc['loaiDiem'] == 'diemGiuaKy') $tenLoai = 'Giữa kỳ';
                                    elseif($yc['loaiDiem'] == 'diemCuoiKy') $tenLoai = 'Cuối kỳ';

                                    // Tag trạng thái (Bootstrap Badge)
                                    $statusBadge = '';
                                    if($yc['trangThai'] == 'CHO_DUYET') 
                                        $statusBadge = '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Chờ duyệt</span>';
                                    elseif($yc['trangThai'] == 'DA_DUYET') 
                                        $statusBadge = '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Đã duyệt</span>';
                                    elseif($yc['trangThai'] == 'TU_CHOI') 
                                        $statusBadge = '<span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Từ chối</span>';
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($yc['ngayYeuCau'])); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($yc['hoTen']); ?></td>
                                    <td><?php echo $tenLoai; ?></td>
                                    <td class="text-muted"><?php echo $yc['diemCu']; ?></td>
                                    <td class="text-primary fw-bold"><?php echo $yc['diemMoi']; ?></td>
                                    <td><small><em><?php echo htmlspecialchars($yc['lyDo']); ?></em></small></td>
                                    <td><?php echo $statusBadge; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                        <p>Chưa có yêu cầu sửa điểm nào được gửi.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalSuaDiem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?action=gui_yeu_cau_sua_diem">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-paper-plane me-2"></i>Gửi yêu cầu sửa điểm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="maBangDiem" id="m_maBangDiem">
                    <input type="hidden" name="loaiDiem" id="m_loaiDiem">
                    <input type="hidden" name="diemCu" id="m_diemCu">
                    <input type="hidden" name="tenMon" id="m_tenMon">
                    <input type="hidden" name="maLop" value="<?php echo $selectedLop; ?>">
                    <input type="hidden" name="maMon" value="<?php echo $selectedMon; ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted">Học sinh:</label>
                        <input type="text" class="form-control fw-bold" id="m_tenHS" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted">Loại điểm:</label>
                            <input type="text" class="form-control" id="m_tenLoaiDiem" readonly>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted">Điểm hiện tại:</label>
                            <input type="text" class="form-control fw-bold text-danger" id="m_diemCuDisplay" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Điểm đề nghị sửa <span class="text-danger">*</span>:</label>
                        <input type="number" step="0.1" min="0" max="10" class="form-control border-primary" name="diemMoi" required placeholder="Nhập điểm mới...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do sửa <span class="text-danger">*</span>:</label>
                        <textarea class="form-control border-primary" name="lyDo" rows="3" required placeholder="Ví dụ: Nhập sai sót..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(maBangDiem, loaiDiemCode, diemCu, tenHS, tenLoaiDiem, tenMon) {
    document.getElementById('m_maBangDiem').value = maBangDiem;
    document.getElementById('m_loaiDiem').value = loaiDiemCode;
    document.getElementById('m_diemCu').value = diemCu;
    document.getElementById('m_tenMon').value = tenMon;
    
    document.getElementById('m_tenHS').value = tenHS;
    document.getElementById('m_tenLoaiDiem').value = tenLoaiDiem;
    document.getElementById('m_diemCuDisplay').value = diemCu;
    
    var myModal = new bootstrap.Modal(document.getElementById('modalSuaDiem'));
    myModal.show();
}
</script>
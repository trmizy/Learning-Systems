<?php
// File: views/gvbm/xem_loai.php
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa-solid fa-user-graduate text-primary me-2"></i>Xếp loại: <?php echo htmlspecialchars($tenLop); ?></h2>
        <div>
            <a href="index.php?action=xep_loai" class="btn btn-outline-secondary me-2">
                <i class="fa-solid fa-rotate me-1"></i> Tính toán lại
            </a>
            <button type="submit" form="form-xep-loai" class="btn btn-primary">
                <i class="fa-solid fa-save me-2"></i>Lưu tất cả kết quả
            </button>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-3">
            <form method="GET" action="index.php" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="xep_loai">
                <div class="col-auto fw-bold text-muted"><i class="fa-solid fa-filter me-1"></i>Lọc theo:</div>
                <div class="col-auto">
                    <select name="filter_hl" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Học lực --</option>
                        <option value="Giỏi" <?php echo ($filterHL=='Giỏi')?'selected':''; ?>>Giỏi</option>
                        <option value="Khá" <?php echo ($filterHL=='Khá')?'selected':''; ?>>Khá</option>
                        <option value="Trung bình" <?php echo ($filterHL=='Trung bình')?'selected':''; ?>>Trung bình</option>
                        <option value="Yếu" <?php echo ($filterHL=='Yếu')?'selected':''; ?>>Yếu</option>
                        <option value="Kém" <?php echo ($filterHL=='Kém')?'selected':''; ?>>Kém</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="filter_hk" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Hạnh kiểm --</option>
                        <option value="Tốt" <?php echo ($filterHK=='Tốt')?'selected':''; ?>>Tốt</option>
                        <option value="Khá" <?php echo ($filterHK=='Khá')?'selected':''; ?>>Khá</option>
                        <option value="Trung bình" <?php echo ($filterHK=='Trung bình')?'selected':''; ?>>Trung bình</option>
                        <option value="Yếu" <?php echo ($filterHK=='Yếu')?'selected':''; ?>>Yếu</option>
                    </select>
                </div>
                <?php if($filterHL || $filterHK): ?>
                <div class="col-auto">
                    <a href="index.php?action=xep_loai" class="text-danger text-decoration-none"><small>Xóa lọc</small></a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <form id="form-xep-loai" method="POST" action="index.php?action=luu_xep_loai">
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-center align-middle">
                    <tr>
                        <th rowspan="2" width="50">STT</th>
                        <th rowspan="2" style="text-align: left;">Họ và Tên</th>
                        <th colspan="3">Dữ liệu đầu vào</th>
                        <th colspan="2">Kết quả Xếp loại</th>
                        <th rowspan="2" width="250">Nhận xét</th>
                    </tr>
                    <tr>
                        <th width="80">ĐTB</th>
                        <th width="80">Nghỉ KP</th>
                        <th width="80">Vi phạm</th>
                        <th width="150">Học lực</th>
                        <th width="150">Hạnh kiểm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSach)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">Không tìm thấy học sinh nào.</td></tr>
                    <?php else: ?>
                        <?php $stt=1; foreach ($danhSach as $hs): 
                            $maHS = $hs['maHS'];
                            $coDiem = is_numeric($hs['diemTrungBinh']);
                            
                            // Màu sắc điểm số
                            $classDiem = 'text-secondary';
                            if ($coDiem) {
                                $val = (float)$hs['diemTrungBinh'];
                                if($val>=8) $classDiem='text-success fw-bold';
                                elseif($val>=5) $classDiem='text-primary';
                                else $classDiem='text-danger fw-bold';
                            }
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $stt++; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($hs['hoTen']); ?></td>
                            
                            <td class="text-center <?php echo $classDiem; ?>">
                                <?php echo $coDiem ? $hs['diemTrungBinh'] : '-'; ?>
                            </td>
                            <td class="text-center"><?php echo $hs['soBuoiNghiKhongCoPhep']; ?></td>
                            <td class="text-center"><?php echo $hs['soLanViPham']; ?></td>

                            <?php if ($coDiem): ?>
                                <td>
                                    <div class="mb-1 text-center">
                                        <small class="text-muted">Đề xuất: <strong class="text-dark"><?php echo $hs['auto_HL']; ?></strong></small>
                                    </div>
                                    
                                    <select name="data[<?php echo $maHS; ?>][hl]" class="form-select form-select-sm border-success fw-bold text-center" style="background-color: #f0fff4;">
                                        <?php 
                                            $hlOpts = ['Giỏi', 'Khá', 'Trung bình', 'Yếu', 'Kém'];
                                            
                                            // === 🚀 SỬA LẠI LOGIC ƯU TIÊN ===
                                            // 1. Nếu trong CSDL đã có (xepLoaiHocLuc không rỗng), dùng giá trị đó.
                                            // 2. Nếu chưa có, dùng giá trị Đề xuất (auto_HL).
                                            $curHL = !empty($hs['xepLoaiHocLuc']) ? $hs['xepLoaiHocLuc'] : $hs['auto_HL'];
                                            
                                            foreach($hlOpts as $opt) {
                                                $sel = ($curHL == $opt) ? 'selected' : '';
                                                echo "<option value='$opt' $sel>$opt</option>";
                                            }
                                        ?>
                                    </select>
                                </td>

                                <td>
                                    <div class="mb-1 text-center">
                                        <small class="text-muted">Đề xuất: <strong class="text-dark"><?php echo $hs['auto_HK']; ?></strong></small>
                                    </div>

                                    <select name="data[<?php echo $maHS; ?>][hk]" class="form-select form-select-sm border-warning fw-bold text-center" style="background-color: #fffaf0;">
                                        <?php 
                                            $hkOpts = ['Tốt', 'Khá', 'Trung bình', 'Yếu'];
                                            
                                            // === 🚀 SỬA LẠI LOGIC ƯU TIÊN ===
                                            $curHK = !empty($hs['loaiHanhKiem']) ? $hs['loaiHanhKiem'] : $hs['auto_HK'];
                                            
                                            foreach($hkOpts as $opt) {
                                                $sel = ($curHK == $opt) ? 'selected' : '';
                                                echo "<option value='$opt' $sel>$opt</option>";
                                            }
                                        ?>
                                    </select>
                                </td>

                                <td>
                                    <textarea name="data[<?php echo $maHS; ?>][nhanXet]" 
                                              class="form-control form-control-sm" 
                                              rows="2" 
                                              placeholder="Nhập nhận xét..."><?php echo htmlspecialchars($hs['nhanXet'] ?? ''); ?></textarea>
                                </td>

                            <?php else: ?>
                                <td colspan="3" class="text-center text-danger bg-light">
                                    <small><i class="fa-solid fa-triangle-exclamation"></i> Thiếu dữ liệu</small>
                                    <input type="hidden" name="data[<?php echo $maHS; ?>][hl]" value="">
                                    <input type="hidden" name="data[<?php echo $maHS; ?>][hk]" value="">
                                    <input type="hidden" name="data[<?php echo $maHS; ?>][nhanXet]" value="">
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
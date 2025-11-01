<?php
// File: views/gvbm/xem_lop_phu_trach.php
// (ĐÃ CẬP NHẬT - THÊM NÚT QUA LẠI TUẦN)
?>

<div class="container mt-4">

    <?php if (isset($error_message)): ?>
        <div class="alert alert-warning" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php echo $error_message; ?>
        </div>
    <?php else: ?>
        <h2>Thông tin Lớp: <?php echo htmlspecialchars($tenLop); ?></h2>
        <p>Giáo viên chủ nhiệm: <?php echo htmlspecialchars($tenGiaoVien); ?></p>
        
        <hr>

        <ul class="nav nav-tabs" id="lopTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($activeTab === 'danhsach') ? 'active' : ''; ?>" id="danhsach-tab" data-bs-toggle="tab" data-bs-target="#danhsach" type="button" role="tab">
                    <i class="fa-solid fa-users me-2"></i>Danh sách Học sinh
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($activeTab === 'diem') ? 'active' : ''; ?>" id="diem-tab" data-bs-toggle="tab" data-bs-target="#diem" type="button" role="tab">
                    <i class="fa-solid fa-marker me-2"></i>Bảng điểm
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($activeTab === 'tkb') ? 'active' : ''; ?>" id="tkb-tab" data-bs-toggle="tab" data-bs-target="#tkb" type="button" role="tab">
                    <i class="fa-solid fa-calendar-days me-2"></i>Thời khóa biểu
                </button>
            </li>
        </ul>

        <div class="tab-content" id="lopTabContent">
            
            <div class="tab-pane fade <?php echo ($activeTab === 'danhsach') ? 'show active' : ''; ?>" id="danhsach" role="tabpanel" tabindex="0">
                <h4 class="mt-3">Danh sách Học sinh</h4>
                <?php if (empty($danhSachHocSinh)): ?>
                    <div class="alert alert-info mt-3" role="alert">Lớp học hiện chưa có học sinh nào.</div>
                <?php else: ?>
                    <table class="table table-striped table-hover mt-3">
                        <thead class="table-primary">
                            <tr>
                                <th>STT</th> <th>Mã HS</th> <th>Họ và Tên</th>
                                <th>Ngày Sinh</th> <th>Giới Tính</th> <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; foreach ($danhSachHocSinh as $hocSinh): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo htmlspecialchars($hocSinh['hocSinhId']); ?></td>
                                    <td><?php echo htmlspecialchars($hocSinh['hoTen']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($hocSinh['ngaySinh'])); ?></td>
                                    <td><?php echo htmlspecialchars($hocSinh['gioiTinh']); ?></td>
                                    <td>
                                        <a href="index.php?action=xem_chi_tiet_hs&id=<?php echo $hocSinh['hocSinhId']; ?>" class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade <?php echo ($activeTab === 'diem') ? 'show active' : ''; ?>" id="diem" role="tabpanel" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h4 class="mb-0">Bảng điểm Lớp (Học kỳ 1, 2024-2025)</h4>
                    <div>
                        <button id="btn-sort-asc" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fa-solid fa-arrow-up-a-z"></i> Sắp xếp ĐTB Tăng dần
                        </button>
                        <button id="btn-sort-desc" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-arrow-down-z-a"></i> Sắp xếp ĐTB Giảm dần
                        </button>
                    </div>
                </div>
                <?php if (empty($danhSachMonHoc)): ?>
                    <div class="alert alert-info mt-3" role="alert">Chưa có dữ liệu điểm cho lớp này.</div>
                <?php else: ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-hover table-bordered text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th style="min-width: 150px; text-align: left;">Họ và Tên</th>
                                    <?php foreach ($danhSachMonHoc as $monHoc): ?>
                                        <th><?php echo htmlspecialchars($monHoc['tenMon']); ?></th>
                                    <?php endforeach; ?>
                                    <th>ĐTB</th>
                                </tr>
                            </thead>
                            <tbody id="grade-table-body">
                                <?php foreach ($danhSachHocSinh as $hocSinh): ?>
                                    <tr>
                                        <td style="text-align: left;"><?php echo htmlspecialchars($hocSinh['hoTen']); ?></td>
                                        <?php 
                                        $tongDiem = 0; $soMonCoDiem = 0;
                                        foreach ($danhSachMonHoc as $monHoc):
                                            $diem = $bangDiemLookup[$hocSinh['hocSinhId']][$monHoc['maMonHoc']] ?? null;
                                            if ($diem && isset($diem['diemCuoiKy'])) {
                                                $diemCuoiKy = (float) $diem['diemCuoiKy'];
                                                $tongDiem += $diemCuoiKy;
                                                $soMonCoDiem++;
                                                echo '<td>' . htmlspecialchars($diemCuoiKy) . '</td>';
                                            } else {
                                                echo '<td class="text-muted">-</td>';
                                            }
                                        endforeach; 
                                        ?>
                                        <td class="fw-bold">
                                            <?php echo ($soMonCoDiem > 0) ? number_format($tongDiem / $soMonCoDiem, 1) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade <?php echo ($activeTab === 'tkb') ? 'show active' : ''; ?>" id="tkb" role="tabpanel" tabindex="0">
                
                <form method="GET" action="index.php" class="row g-3 align-items-center bg-light p-3 rounded mt-3 border">
                    <input type="hidden" name="action" value="xem_lop_cn">
                    
                    <div class="col-auto">
                        <label for="week" class="form-label fw-bold mb-0">Chọn tuần:</label>
                    </div>
                    
                    <div class="col-auto">
                        <a href="index.php?action=xem_lop_cn&week=<?php echo $prevWeekDate; ?>" class="btn btn-outline-secondary" title="Tuần trước">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="week" name="week" 
                               value="<?php echo htmlspecialchars($selected_date); ?>">
                    </div>

                    <div class="col-auto">
                        <a href="index.php?action=xem_lop_cn&week=<?php echo $nextWeekDate; ?>" class="btn btn-outline-secondary" title="Tuần sau">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-search me-1"></i> Xem tuần
                        </button>
                    </div>
                </form>
                <h4 class="mt-4">Thời khóa biểu Tuần (<?php echo $tuanHienTai; ?>)</h4>

                <?php if (empty($tkbGrid)): ?>
                    <div class="alert alert-info mt-3" role="alert">
                        Không có lịch học trong tuần này.
                    </div>
                <?php else: ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered text-center" style="min-width: 800px;">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 8%; vertical-align: middle;">Tiết</th>
                                    <?php foreach ($daysOfWeek as $day): ?>
                                        <th style="width: 13%;">
                                            <div><?php echo $day['name']; ?></div>
                                            <small class="fw-normal"><?php echo $day['date']; ?></small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($tiet = 1; $tiet <= 10; $tiet++): ?>
                                    <tr>
                                        <td class="fw-bold">Tiết <?php echo $tiet; ?></td>
                                        <?php for ($ngay = 1; $ngay <= 7; $ngay++): 
                                            $tietHoc = $tkbGrid[$ngay][$tiet] ?? null;
                                        ?>
                                            <td style="height: 60px;">
                                                <?php if ($tietHoc): ?>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($tietHoc['tenMon']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($tietHoc['tenPhong']); ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAsc = document.getElementById('btn-sort-asc');
        const btnDesc = document.getElementById('btn-sort-desc');
        const tableBody = document.getElementById('grade-table-body');

        if (!tableBody || !btnAsc || !btnDesc) {
            return;
        }

        btnAsc.addEventListener('click', function() { sortGradeTable('asc'); });
        btnDesc.addEventListener('click', function() { sortGradeTable('desc'); });

        function sortGradeTable(direction) {
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            rows.sort(function(rowA, rowB) {
                const cellA = rowA.querySelector('td:last-child');
                const cellB = rowB.querySelector('td:last-child');
                let valA = cellA ? parseFloat(cellA.textContent.trim()) : -1;
                let valB = cellB ? parseFloat(cellB.textContent.trim()) : -1;
                if (isNaN(valA)) valA = -1;
                if (isNaN(valB)) valB = -1;
                return (direction === 'asc') ? valA - valB : valB - valA;
            });
            rows.forEach(row => tableBody.appendChild(row));
        }
    });
</script>
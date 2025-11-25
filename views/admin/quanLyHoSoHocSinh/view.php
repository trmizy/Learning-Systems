<?php
// Expecting $student from controller
if (empty($student)) {
    echo "<p>Học sinh không tồn tại.</p>";
    return;
}

require __DIR__ . '/../../layouts/header.php';
?>
<div class="container mt-4">
    <h2>Hồ sơ học sinh: <?php echo htmlspecialchars($student['hoTen']); ?></h2>
    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success">Cập nhật thành công.</div>
    <?php endif; ?>
    <table class="table table-bordered">
        <tr><th>Mã HS</th><td><?php echo htmlspecialchars($student['maHS']); ?></td></tr>
        <tr><th>Họ và tên</th><td><?php echo htmlspecialchars($student['hoTen']); ?></td></tr>
        <tr><th>Ngày sinh</th><td><?php echo htmlspecialchars($student['ngaySinh']); ?></td></tr>
        <tr><th>Địa chỉ (thường trú)</th><td><?php echo htmlspecialchars($student['diaChi'] ?? '-'); ?></td></tr>
        <tr><th>Địa chỉ (tạm trú)</th><td><?php echo htmlspecialchars($student['diaChiTamTru'] ?? '-'); ?></td></tr>
        <tr><th>Lớp</th><td><?php echo htmlspecialchars($student['maLop']); ?></td></tr>
        <tr><th>Xếp loại học lực</th><td><?php echo htmlspecialchars($student['xepLoaiHocLuc']); ?></td></tr>
        <tr><th>Hạnh kiểm (hiện tại)</th><td><?php echo htmlspecialchars($student['loaiHanhKiem']); ?></td></tr>
        <tr><th>Điểm TB</th><td><?php echo htmlspecialchars($student['diemTrungBinhMon']); ?></td></tr>
        <tr><th>Phụ huynh / Người giám hộ</th>
            <td>
                <?php if (!empty($student['parents'])): ?>
                    <ul class="mb-0">
                        <?php foreach ($student['parents'] as $p): ?>
                            <li><?php echo htmlspecialchars($p['hoTen'] ?? ''); ?> (<?php echo htmlspecialchars($p['moiQuanHe'] ?? ''); ?>)
                                - SĐT: <?php echo htmlspecialchars($p['soDienThoai'] ?? '-'); ?>
                                - Địa chỉ: <?php echo htmlspecialchars($p['diaChi'] ?? '-'); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?php echo htmlspecialchars($student['phuHuynh_info'] ?? '-'); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>Thành tích</th><td><?php echo htmlspecialchars($student['thanh_tich'] ?? '-'); ?></td></tr>
    </table>

    <h4>Kỷ luật / Hạnh kiểm (lịch sử)</h4>
    <?php if (!empty($student['hanhkiem_history'])): ?>
        <table class="table table-sm table-striped">
            <thead>
                <tr><th>Năm học</th><th>Học kỳ</th><th>Loại</th><th>Số buổi nghỉ (có phép)</th><th>Số buổi nghỉ (không phép)</th><th>Số lần vi phạm</th></tr>
            </thead>
            <tbody>
                <?php foreach ($student['hanhkiem_history'] as $hk): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hk['namHoc'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($hk['hocKy'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($hk['loaiHanhKiem'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($hk['soBuoiNghiCoPhep'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($hk['soBuoiNghiKhongCoPhep'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($hk['soLanViPham'] ?? '0'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có bản ghi kỷ luật.</p>
    <?php endif; ?>

    <a href="/modules/quanLyHoSoHocSinh/edit.php?maHS=<?php echo urlencode($student['maHS']); ?>" class="btn btn-primary">Chỉnh sửa</a>
    <a href="/modules/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php" class="btn btn-secondary">Quay lại</a>
</div>

<?php require __DIR__ . '/../../layouts/footer.php';


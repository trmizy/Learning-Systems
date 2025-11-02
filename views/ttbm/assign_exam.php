<?php
$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="container mt-4">
  <a href="index.php" class="btn btn-secondary mb-3"><i class="fa-solid fa-arrow-left"></i> Quay lại Dashboard</a>

  <h3 class="mb-3">Phân công giáo viên ra đề thi</h3>

  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form action="index.php?action=store_assign_exam" method="POST" class="row g-3 mb-4">

      <div class="col-md-2">
          <label class="form-label">Chọn khối</label>
          <select name="khoi" class="form-select" required>
              <option value="">-- Chọn khối --</option>
              <?php foreach ($khoi as $k): ?>
                  <option value="<?= htmlspecialchars($k) ?>">Khối <?= htmlspecialchars($k) ?></option>
              <?php endforeach; ?>
          </select>
      </div>

      <div class="col-md-4">
          <label class="form-label">Giáo viên bộ môn</label>
          <select name="listGV[]" multiple class="form-select" size="5" required>
              <?php foreach ($listGV as $gv): ?>
                  <option value="<?= $gv['maGV'] ?>">
                      <?= $gv['hoTen'] ?> (<?= $gv['monHocPhuTrach'] ?>)
                  </option>
              <?php endforeach; ?>
          </select>
          <small class="text-muted">Giữ Ctrl để chọn nhiều giáo viên</small>
      </div>

      <div class="col-md-2">
        <label class="form-label">Học kỳ</label>
        <select name="hocKy" class="form-select" required>
            <option value="">-- Chọn học kỳ --</option>
            <option value="I">Học kỳ I</option>
            <option value="II">Học kỳ II</option>
        </select>
        </div>

        <div class="col-md-2">
        <label class="form-label">Kỳ thi</label>
        <select name="kyThi" class="form-select" required>
            <option value="">-- Chọn kỳ thi --</option>
            <option value="Giữa kỳ">Giữa kỳ</option>
            <option value="Cuối kỳ">Cuối kỳ</option>
        </select>
        </div>

      <div class="col-md-2">
          <label class="form-label">Số lượng đề</label>
          <input type="number" name="soLuongDe" class="form-control" min="1" required>
      </div>

      <div class="col-md-4">
          <label class="form-label">Thời hạn nộp</label>
          <input type="datetime-local" name="thoiHan" class="form-control" required>
      </div>

      <div class="col-md-8">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghiChu" rows="2" class="form-control"></textarea>
      </div>

      <div class="col-md-12 text-end">
          <button class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Xác nhận phân công</button>
      </div>
  </form>

  <h5>Danh sách phân công hiện có</h5>
  <table class="table table-bordered table-striped table-sm">
      <thead class="table-light">
          <tr>
              <th>Học kỳ</th>
              <th>Kỳ thi</th>
              <th>Số lượng đề</th>
              <th>Thời hạn</th>
              <th>Ghi chú</th>
              <th>Giáo viên phụ trách</th>
          </tr>
      </thead>
      <tbody>
          <?php foreach ($phanCong as $row): ?>
              <tr>
                  <td><?= htmlspecialchars($row['hocKy']) ?></td>
                  <td><?= htmlspecialchars($row['kyThi']) ?></td>
                  <td><?= htmlspecialchars($row['soLuongDe']) ?></td>
                  <td><?= htmlspecialchars($row['thoiHan']) ?></td>
                  <td><?= htmlspecialchars($row['ghiChu']) ?></td>
                  <td><?= htmlspecialchars($row['giaoVien']) ?></td>
              </tr>
          <?php endforeach; ?>
      </tbody>
  </table>
</div>

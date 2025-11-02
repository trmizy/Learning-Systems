<div class="container mt-4">
  <a href="index.php" class="btn btn-secondary mb-3"><i class="fa-solid fa-arrow-left"></i> Quay lại Dashboard</a>

  <h3 class="mb-3">Phân công ra đề của tôi</h3>

  <?php if (empty($phanCong)): ?>
      <div class="alert alert-info">Hiện tại bạn chưa được phân công ra đề thi nào.</div>
  <?php else: ?>
      <table class="table table-bordered table-striped table-sm">
          <thead class="table-light">
              <tr>
                  <th>Học kỳ</th>
                  <th>Kỳ thi</th>
                  <th>Số lượng đề</th>
                  <th>Thời hạn nộp</th>
                  <th>Ghi chú</th>
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
                  </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/header.php';

// $tohopList is expected to be provided by the controller
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Danh sách tổ hợp môn</h4>
        <a href="/controllers/admin/taoCacToHopMon_controller.php?action=create" class="btn btn-primary">Tạo tổ hợp môn mới</a>
    </div>

    <?php if (!empty($_SESSION['error_messages'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($_SESSION['error_messages'] as $err) { echo '<p class="mb-0">'.htmlspecialchars($err).'</p>'; } unset($_SESSION['error_messages']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Mã tổ hợp</th>
                <th>Tên tổ hợp</th>
                <th>Danh sách môn</th>
                <th>Số lượng lớp</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tohopList)): ?>
                <?php foreach ($tohopList as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['maToHop']); ?></td>
                        <td><?php echo htmlspecialchars($row['tenToHop']); ?></td>
                        <td><?php echo htmlspecialchars($row['danhSachMon']); ?></td>
                        <td><?php echo htmlspecialchars($row['soLuongLop']); ?></td>
                        <td><?php echo htmlspecialchars($row['trangThai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Chưa có tổ hợp môn nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
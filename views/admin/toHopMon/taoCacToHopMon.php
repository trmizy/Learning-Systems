<?php require_once __DIR__ . '/../../layouts/header.php';

// $danhSachMonHoc is expected to be provided by the controller
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Tạo tổ hợp môn mới</h4>
        </div>
        <div class="card-body">
            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (!empty($_SESSION['error_messages'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    foreach ($_SESSION['error_messages'] as $error) {
                        echo "<p class='mb-0'>" . htmlspecialchars($error) . "</p>";
                    }
                    unset($_SESSION['error_messages']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Form tạo tổ hợp môn -->
            <form id="formTaoToHopMon" action="/controllers/admin/taoCacToHopMon_controller.php?action=create" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="maToHop" class="form-label">Mã tổ hợp <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="maToHop" name="maToHop" required>
                    <div class="invalid-feedback">
                        Vui lòng nhập mã tổ hợp
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tenToHop" class="form-label">Tên tổ hợp <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tenToHop" name="tenToHop" required>
                    <div class="invalid-feedback">
                        Vui lòng nhập tên tổ hợp
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Danh sách môn học <span class="text-danger">*</span></label>
                    <div id="danhSachMonContainer" class="border rounded p-2" style="max-height:240px; overflow:auto;">
                        <?php foreach ($danhSachMonHoc as $monHoc): $mhId = htmlspecialchars($monHoc['maMonHoc']); ?>
                            <div class="form-check">
                                <input class="form-check-input danh-sach-mon-checkbox" type="checkbox" name="danhSachMon[]" value="<?php echo $mhId; ?>" id="mon_<?php echo $mhId; ?>">
                                <label class="form-check-label" for="mon_<?php echo $mhId; ?>">
                                    <?php echo htmlspecialchars($monHoc['tenMon']) . ' (' . $mhId . ')'; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="invalid-feedback" id="danhSachMonFeedback" style="display:none;">
                        Vui lòng chọn ít nhất một môn học
                    </div>
                    <div class="invalid-feedback" id="danhSachMonTooManyFeedback" style="display:none; color:#842029; background:#f8d7da; padding:6px; border-radius:4px; margin-top:6px;">
                        Không được chọn quá 3 môn học
                    </div>
                </div>

                <div class="mb-3">
                    <label for="soLuongLop" class="form-label">Số lượng lớp <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="soLuongLop" name="soLuongLop" min="1" required>
                    <div class="invalid-feedback">
                        Số lượng lớp phải lớn hơn 0
                    </div>
                </div>

                <!-- Trạng thái được xử lý bởi Ban Giám Hiệu (BGH) sau khi tạo. -->
                <!-- Không hiển thị trường trạng thái cho admin khi tạo tổ hợp môn. -->

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="confirmCancel()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
            // custom validation: ensure at least one subject checkbox is checked and no more than 3
            var checkedCount = document.querySelectorAll('.danh-sach-mon-checkbox:checked').length;
            var anyChecked = checkedCount > 0;
            var tooMany = checkedCount > 3;

            // show/hide messages accordingly
            document.getElementById('danhSachMonFeedback').style.display = anyChecked ? 'none' : 'block';
            document.getElementById('danhSachMonTooManyFeedback').style.display = tooMany ? 'block' : 'none';

            if (!form.checkValidity() || !anyChecked || tooMany) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Hàm xác nhận hủy
function confirmCancel() {
    if (confirm('Bạn có muốn hủy tổ hợp đang tạo?')) {
        window.location.href = '/controllers/admin/taoCacToHopMon_controller.php';
    }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

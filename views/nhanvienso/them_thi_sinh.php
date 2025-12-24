<?php
$pageTitle = 'Thêm thí sinh';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- ✅ HIỂN THỊ FLASH MESSAGE -->
<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>
    <?php echo htmlspecialchars($_SESSION['flash_success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-xmark me-2"></i>
    <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Form thêm thí sinh -->
<div class="container mt-4">
    <h2>Thêm thí sinh mới</h2>
    <form method="POST" action="/public/index.php?action=nhanvienso-them-thi-sinh">
        <!-- ...existing form fields... -->
        
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-save me-2"></i>Lưu thông tin
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

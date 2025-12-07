<?php
require_once __DIR__ . '/models/auth.php';

$auth = new Auth();

if ($auth->hashAllPasswords()) {
    echo "✅ Đã hash tất cả password thành công!<br>";
    echo "Bây giờ tất cả tài khoản đều dùng password đã mã hóa.<br>";
    echo "<strong>⚠️ XÓA FILE NÀY SAU KHI CHẠY XONG!</strong>";
} else {
    echo "❌ Lỗi khi hash password";
}

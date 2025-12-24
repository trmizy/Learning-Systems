<?php
/**
 * Xuất giá trị an toàn, xử lý null
 */
function safe_output($value, $default = 'Chưa cập nhật') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

/**
 * Xuất giá trị với style italic nếu là default
 */
function safe_output_styled($value, $default = 'Chưa cập nhật') {
    if (empty($value)) {
        return '<span class="text-muted fst-italic">' . htmlspecialchars($default) . '</span>';
    }
    return htmlspecialchars($value);
}

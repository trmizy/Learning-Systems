<?php
/**
 * Xuất giá trị an toàn, xử lý null
 */
function safe_output($value, $default = 'Chưa cập nhật') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

/**
 * Format ngày an toàn
 */
function safe_date($dateString, $format = 'd/m/Y', $default = 'Chưa cập nhật') {
    if (!$dateString) return $default;
    $timestamp = strtotime($dateString);
    return $timestamp ? date($format, $timestamp) : $default;
}

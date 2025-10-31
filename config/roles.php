<?php
const ROLES = [
    'admin' => 'Admin Nhân viên phòng giáo vụ',
    'bgh' => 'Ban giám hiệu',
    'nhanvienso' => 'Nhân viên Sở Giáo dục',
    'totruong' => 'Tổ trưởng bộ môn',
    'gvcn' => 'Giáo viên chủ nhiệm',
    'gvbm' => 'Giáo viên bộ môn',
    'phuhuynh' => 'Phụ huynh',
    'hocsinh' => 'Học sinh'
];

// Định nghĩa quyền cho từng role
const ROLE_PERMISSIONS = [
    'nhanvienso' => ['nhanvienso', 'admin', 'giaovu', 'vanphong', 'gvcn', 'gvbm'], // Nhân viên sở có full quyền
    'giaovu' => ['admin', 'giaovu', 'vanphong', 'gvcn', 'gvbm'], // Giáo vụ có full quyền admin
    'admin' => ['admin', 'gvcn', 'gvbm'], // BGH có quyền hạn chế hơn
    'totruong' => ['totruong', 'gvbm'],
    'gvcn' => ['gvcn', 'gvbm'],
    'gvbm' => ['gvbm'],
    'phuhuynh' => ['phuhuynh'],
    'hocsinh' => ['hocsinh']
];

function getRoleName($roleKey) {
    return ROLES[$roleKey] ?? $roleKey;
}

function hasPermission($userRole, $requiredRoles) {
    if (!is_array($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }
    
    $userPermissions = ROLE_PERMISSIONS[$userRole] ?? [];
    return count(array_intersect($requiredRoles, $userPermissions)) > 0;
}
?>

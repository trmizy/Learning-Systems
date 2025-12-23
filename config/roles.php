<?php
const ROLES = [
    'admin' => 'Admin Nhân viên phòng giáo vụ',
    'bgh' => 'Ban giám hiệu',
    'nhanvienso' => 'Nhân viên Sở Giáo dục',
    'totruong' => 'Tổ trưởng bộ môn',
    'gvcn' => 'Giáo viên chủ nhiệm',
    'gvbm' => 'Giáo viên bộ môn',
    'phuhuynh' => 'Phụ huynh',
    'hocsinh' => 'Học sinh',
    'ts' => 'Thí sinh' // Thêm role mới
];

const ROLE_DISPLAY_NAMES = [
    'nhanvienso' => 'Nhân viên Sở Giáo dục',
    'admin' => 'Admin Nhân viên phòng giáo vụ',
    'bgh' => 'Ban giám hiệu',
    'ttbm' => 'Tổ trưởng bộ môn',
    'gvcn' => 'Giáo viên chủ nhiệm',
    'gvbm' => 'Giáo viên bộ môn',
    'ph' => 'Phụ huynh',
    'hs' => 'Học sinh',
    'ts' => 'Thí sinh'  // ⚠️ THÊM ROLE MỚI
];

// Định nghĩa quyền cho từng role
const ROLE_PERMISSIONS = [
    'nhanvienso' => ['full_access'],
    'admin' => ['manage_school', 'view_reports'],
    'bgh' => ['manage_teachers', 'view_statistics'],
    'ttbm' => ['manage_subject', 'assign_exams'],
    'gvcn' => ['manage_class', 'enter_conduct'],
    'gvbm' => ['enter_grades', 'view_assignments'],
    'ph' => ['view_student_info', 'submit_leave_request'],
    'hs' => ['view_grades', 'view_schedule'],
    'ts' => ['view_results', 'register_wishes']  // ⚠️ THÊM QUYỀN
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

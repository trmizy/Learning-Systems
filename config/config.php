<?php
$MENU_ITEMS = [
    'admin' => [
        [
            'icon' => 'user-graduate', 
            'text' => 'Quản lý học sinh',
            'link' => '/modules/students/list.php',
            'submenu' => [
                ['icon' => 'list', 'text' => 'Danh sách học sinh', 'link' => '/modules/students/list.php'],
                ['icon' => 'plus', 'text' => 'Thêm học sinh', 'link' => '/modules/students/create.php'],
                ['icon' => 'file-import', 'text' => 'Nhập từ Excel', 'link' => '/modules/students/import.php'],
                ['icon' => 'exchange', 'text' => 'Chuyển lớp', 'link' => '/modules/students/transfer.php'],
            ]
        ],
        [
            'icon' => 'chalkboard-user',
            'text' => 'Quản lý giảng dạy',
            'link' => '#',
            'submenu' => [
                ['icon' => 'people-roof', 'text' => 'Quản lý lớp', 'link' => '/modules/classes/list.php'],
                ['icon' => 'person-chalkboard', 'text' => 'Giáo viên', 'link' => '/modules/teachers/list.php'],
                ['icon' => 'book', 'text' => 'Môn học', 'link' => '/modules/subjects/list.php'],
                ['icon' => 'layer-group', 'text' => 'Tổ hợp môn', 'link' => '/controllers/admin/taoCacToHopMon_controller.php'],
            ]
        ],
        [
            'icon' => 'chart-line',
            'text' => 'Theo dõi học tập',
            'link' => '#',
            'submenu' => [
                ['icon' => 'square-poll-vertical', 'text' => 'Quản lý điểm', 'link' => '/modules/grades/list.php'],
                ['icon' => 'calendar-check', 'text' => 'Điểm danh', 'link' => '/modules/attendance/list.php'],
                ['icon' => 'chart-simple', 'text' => 'Báo cáo tổng hợp', 'link' => '/modules/reports/index.php'],
            ]
        ],
        [
            'icon' => 'gears',
            'text' => 'Cấu hình hệ thống',
            'link' => '#',
            'submenu' => [
                ['icon' => 'users-gear', 'text' => 'Quản lý tài khoản', 'link' => '/modules/users/list.php'],
                ['icon' => 'clock', 'text' => 'Thời khóa biểu', 'link' => '/modules/timetable/list.php'],
                ['icon' => 'sliders', 'text' => 'Thiết lập năm học', 'link' => '/modules/settings/index.php'],
                ['icon' => 'database', 'text' => 'Sao lưu dữ liệu', 'link' => '/modules/backup/index.php'],
            ]
        ],
    ],
    'bgh' => [
        ['icon' => 'fa-user-graduate', 'text' => 'Học sinh', 'link' => '/modules/students/list.php'],
        ['icon' => 'fa-people-roof', 'text' => 'Lớp học', 'link' => '/modules/classes/list.php'],
        ['icon' => 'fa-calendar-check', 'text' => 'Điểm danh', 'link' => '/modules/attendance/list.php'],
    ],
    'gvcn' => [
        ['icon' => 'fa-user-graduate', 'text' => 'Lớp chủ nhiệm', 'link' => '/modules/homeroom/index.php'],
        ['icon' => 'fa-chart-simple', 'text' => 'Tổng kết', 'link' => '/modules/homeroom/summary.php'],
    ],
    'gvbm' => [
        ['icon' => 'fa-square-poll-vertical', 'text' => 'Nhập điểm', 'link' => '/controllers/gvbm/enterPointsController.php'],
        ['icon' => 'fa-calendar-check', 'text' => 'Điểm danh', 'link' => '/modules/attendance/mark.php'],
    ],
    'hocsinh' => [
        ['icon' => 'fa-graduation-cap', 'text' => 'Kết quả học tập', 'link' => '/modules/grades/view.php'],
        ['icon' => 'fa-calendar-days', 'text' => 'Thời khóa biểu', 'link' => '/modules/timetable/student.php'],
    ],
    'phuhuynh' => [
        ['icon' => 'fa-graduation-cap', 'text' => 'Kết quả học tập', 'link' => '/modules/grades/parent.php'],
        ['icon' => 'fa-calendar-check', 'text' => 'Điểm danh', 'link' => '/modules/attendance/parent.php'],
    ]
];

// Add any other global configurations here
define('BASE_PATH', __DIR__ . '/..');
define('BASE_URL', '/');

// Giới hạn phân công giảng dạy
define('MAX_CLASSES_PER_TEACHER', 5); // Số lớp tối đa một giáo viên có thể dạy trong một học kỳ
define('MAX_SUBJECTS_PER_TEACHER', 3); // Số môn tối đa một giáo viên có thể dạy (tùy chọn)

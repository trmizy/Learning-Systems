<?php
// API: Lấy danh sách giáo viên chưa làm GVCN

// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../../../middlewares/AuthGuard.php';
require_role(['bgh', 'admin']);

// Load controller
require_once __DIR__ . '/../../../../controllers/bgh/assignment_controller.php';

// Xử lý request
$controller = new AssignmentController();
$controller->getGiaoVien();


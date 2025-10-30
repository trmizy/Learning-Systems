<?php
/**
 * API: Lấy danh sách giáo viên theo môn học
 * Path: public/bgh/assignments/api/get_giao_vien_theo_mon.php
 */

// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../../../middlewares/AuthGuard.php';
require_role(['bgh', 'admin']);

// Load controller
require_once __DIR__ . '/../../../../controllers/bgh/teacher_assignment_controller.php';

// Khởi tạo controller và gọi API method
$controller = new TeacherAssignmentController();
$controller->getGiaoVienTheoMon();



<?php
/**
 * Entry Point: Phân công giáo viên bộ môn cho lớp
 * Path: public/bgh/assignments/assign_teachers.php
 */

// Bảo vệ & kiểm tra quyền
require_once __DIR__ . "/../../../middlewares/AuthGuard.php";
require_role(["bgh", "admin"]);

// Load controller
require_once __DIR__ . "/../../../controllers/bgh/teacher_assignment_controller.php";

// Khởi tạo controller và xử lý request
$controller = new TeacherAssignmentController();
$controller->index();

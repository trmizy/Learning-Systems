<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh', 'admin']);
require_once __DIR__ . '/../../../controllers/bgh/assignment_controller.php';
$controller = new AssignmentController();
$controller->index();

<?php
require_once __DIR__ . '/../../controllers/admin/QuanLyHocSinhController.php';
require_once __DIR__ . '/../../config/database.php';

$db = Database::getInstance()->getConnection();
$controller = new QuanLyHocSinhController($db);
$controller->view();

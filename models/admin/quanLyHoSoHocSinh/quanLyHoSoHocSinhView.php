<?php
require_once __DIR__ . '/../../controllers/admin/QuanLyHoSoHocSinhController.php';
require_once __DIR__ . '/../../config/database.php';

$db = Database::getInstance()->getConnection();
$controller = new QuanLyHoSoHocSinhController($db);
$controller->index();


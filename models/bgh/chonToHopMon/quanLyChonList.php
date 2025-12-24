<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh']);

require_once __DIR__ . '/../../../models/bgh/chonToHopMonModel.php';

$controller = new class {
    public function listAction() {
        $model = new chonToHopMonModel();
        
        $trangThai = $_GET['trangThai'] ?? null;
        
        if ($trangThai) {
            $danhSachToHop = $model->getDanhSachToHopMon($trangThai);
        } else {
            $danhSachToHop = $model->getDanhSachToHopMon();
        }
        
        $thongKe = $model->getThongKeTrangThai();
        
        require_once __DIR__ . '/../../../views/bgh/chonToHopMon/list.php';
    }
};

$controller->listAction();
?>

<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh']);

// ✅ FIX: Sửa đường dẫn model để khớp với vị trí thực tế
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
        
        // ✅ FIX: Sửa đường dẫn view để khớp với vị trí thực tế
        require_once __DIR__ . '/../../../views/bgh/chonToHopMon/list.php';
    }
};

$controller->listAction();
?>

<?php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_role(['bgh']);

require_once __DIR__ . '/../../../models/bgh/chonToHopMonModel.php';

$controller = new class {
    public function detailAction() {
        $maToHop = $_GET['maToHop'] ?? null;
        
        if (!$maToHop) {
            header('Location: /models/chonToHopMon/quanLyChonList.php?error=Mã tổ hợp không hợp lệ');
            exit;
        }
        
        $model = new chonToHopMonModel();
        $chiTiet = $model->getChiTietToHopMon($maToHop);
        
        if (empty($chiTiet)) {
            header('Location: /models/chonToHopMon/quanLyChonList.php?error=Không tìm thấy tổ hợp môn');
            exit;
        }
        
        $danhSachMon = $model->getDanhSachMonTrongToHop($maToHop);
        $danhSachHocSinh = $model->getDanhSachHocSinhDangKy($maToHop);
        
        require_once __DIR__ . '/../../../views/bgh/chonToHopMon/detail.php';
    }
};

$controller->detailAction();
?>

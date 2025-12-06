<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ph/FamilyModel.php';
require_once __DIR__ . '/../../models/DiemModel.php';

class FamilyController {
    private $familyModel;
    private $diemModel;

    public function __construct() {
        $this->familyModel = new FamilyModel();
        $this->diemModel = new DiemModel();
    }

    public function index() {
        // Kiểm tra quyền
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã phụ huynh
        $maPH = $this->diemModel->getMaPhuHuynhByUsername($user['username']);
        if (!$maPH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy thông tin phụ huynh
        $thongTinPH = $this->diemModel->getThongTinPhuHuynhByMaPH($maPH);

        // ⚠️ FIX: Lấy danh sách con CHI TIẾT với UNIQUE CHECK
        $danhSachCon = $this->familyModel->getDanhSachConChiTiet($maPH);

        // ⚠️ CRITICAL FIX: Loại bỏ duplicate ở Controller level
        $uniqueCon = [];
        $seenMaHS = [];
        
        foreach ($danhSachCon as $con) {
            $maHS = $con['maHS'];
            
            // Chỉ thêm nếu chưa có trong danh sách
            if (!isset($seenMaHS[$maHS])) {
                $uniqueCon[] = $con;
                $seenMaHS[$maHS] = true;
                
                // Lấy thống kê cho con này
                $uniqueCon[count($uniqueCon) - 1]['thongKe'] = $this->familyModel->getThongKeCon($maHS);
            } else {
                // DEBUG: Log khi phát hiện duplicate
                error_log("⚠️ DUPLICATE DETECTED: maHS=$maHS bị lặp lại!");
            }
        }
        
        // Gán lại danh sách đã unique
        $danhSachCon = $uniqueCon;

        // DEBUG FINAL
        error_log("=== FamilyController Final ===");
        error_log("maPH: $maPH");
        error_log("Số con SAU KHI unique: " . count($danhSachCon));
        foreach ($danhSachCon as $idx => $con) {
            error_log("Con $idx: maHS=" . $con['maHS'] . ", hoTen=" . $con['hoTen']);
        }

        // Render view
        require_once __DIR__ . '/../../views/ph/family-profile.php';
    }
}

// Khởi tạo controller
$controller = new FamilyController();
$controller->index();

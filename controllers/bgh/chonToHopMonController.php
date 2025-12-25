<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/bgh/chonToHopMonModel.php';

class chonToHopMonController {
    private $model;

    public function __construct() {
        $this->model = new chonToHopMonModel();
    }

    public function handleRequest() {
        // Kiểm tra quyền
        require_role(['bgh']);

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_POST['action'] ?? $_GET['action'] ?? null;

        if ($method === 'POST') {
            $this->handlePost($action);
        } else {
            $this->handleGet($action);
        }
    }

    private function handlePost($action) {
        $maToHop = $_POST['maToHop'] ?? null;

        if (!$maToHop) {
            header('Location: /models/bgh/chonToHopMon/quanLyChonList.php?error=Mã tổ hợp không hợp lệ');
            exit;
        }

        // Kiểm tra tổ hợp môn có tồn tại
        if (!$this->model->checkExist($maToHop)) {
            header('Location: /models/bgh/chonToHopMon/quanLyChonList.php?error=Tổ hợp môn không tồn tại');
            exit;
        }

        $currentUser = current_user();
        $nguoiDuyet = $currentUser['maTK'] ?? null;

        try {
            if ($action === 'pheDuyet') {
                $lyDo = $_POST['lyDo'] ?? '';
                $this->model->pheDuyetToHopMon($maToHop, $lyDo, $nguoiDuyet);
                header('Location: models/bgh/chonToHopMon/quanLyChonDetail.php?maToHop=' . urlencode($maToHop) . '&success=Phê duyệt tổ hợp môn thành công');
                exit;
            } elseif ($action === 'tuChoi') {
                $lyDo = $_POST['lyDo'] ?? '';
                if (empty($lyDo)) {
                    header('Location: /models/bgh/chonToHopMon/quanLyChonDetail.php?maToHop=' . urlencode($maToHop) . '&error=Lý do từ chối không được để trống');
                    exit;
                }
                $this->model->tuChoiDuyetToHopMon($maToHop, $lyDo, $nguoiDuyet);
                header('Location: /models/bgh/chonToHopMon/quanLyChonDetail.php?maToHop=' . urlencode($maToHop) . '&success=Từ chối tổ hợp môn thành công');
                exit;
            } else {
                header('Location: /models/bgh/chonToHopMon/quanLyChonList.php?error=Hành động không hợp lệ');
                exit;
            }
        } catch (Exception $e) {
            header('Location: /models/bgh/chonToHopMon/quanLyChonDetail.php?maToHop=' . urlencode($maToHop) . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    private function handleGet($action) {
        // GET requests are handled by module entry points
        // This controller mainly handles POST requests
    }
}

// Xử lý request
$controller = new chonToHopMonController();
$controller->handleRequest();
?>

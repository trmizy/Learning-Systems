<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/ph/LeaveRequestModel.php';
require_once __DIR__ . '/../../config/database.php';

class LeaveRequestController {
    private $model;

    public function __construct() {
        $this->model = new LeaveRequestModel();
    }

    /**
     * Hiển thị form tạo đơn xin nghỉ
     */
    public function create() {
        require_role(['ph']);

        $user = current_user();
        if (!$user) {
            $_SESSION['flash_error'] = 'Không xác định được phụ huynh đang đăng nhập.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã phụ huynh
        $maPH = $this->model->getMaPhuHuynhByUsername($user['username']);
        if (!$maPH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách con
        $danhSachCon = $this->model->getDanhSachConCuaPhuHuynh($maPH);
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy mã học sinh được chọn từ GET
        $maHS = $_GET['maHS'] ?? $danhSachCon[0]['maHS'];

        // Render view
        require_once __DIR__ . '/../../views/ph/leave-requests/create.php';
    }

    /**
     * Xử lý tạo đơn xin nghỉ
     */
    public function store() {
        require_role(['ph']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=ph-leave-create');
            exit;
        }

        $user = current_user();
        $maPH = $this->model->getMaPhuHuynhByUsername($user['username']);

        // Validate dữ liệu
        $maHS = $_POST['maHS'] ?? '';
        $ngayNghi = $_POST['ngayNghi'] ?? '';
        $soBuoi = $_POST['soBuoi'] ?? 1;
        $lyDo = $_POST['lyDo'] ?? '';

        // Kiểm tra dữ liệu bắt buộc
        if (empty($maHS) || empty($ngayNghi) || empty($lyDo)) {
            $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
            header('Location: /public/index.php?action=ph-leave-create&maHS=' . urlencode($maHS));
            exit;
        }

        // Xử lý upload file minh chứng
        $minhChungPath = '';
        if (isset($_FILES['minhChung']) && $_FILES['minhChung']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/leave-requests/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['minhChung']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['minhChung']['tmp_name'], $uploadPath)) {
                $minhChungPath = '/uploads/leave-requests/' . $fileName;
            }
        }

        // Tạo đơn xin nghỉ
        $result = $this->model->createLeaveRequest([
            'maHS' => $maHS,
            'maPH' => $maPH,
            'ngay' => $ngayNghi,
            'soBuoi' => $soBuoi,
            'lyDo' => $lyDo,
            'minhChungKemTheo' => $minhChungPath
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Gửi đơn xin nghỉ thành công! Đơn đang chờ giáo viên phê duyệt.';
            header('Location: /public/index.php?action=ph-leave-list&maHS=' . urlencode($maHS));
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi gửi đơn. Vui lòng thử lại.';
            header('Location: /public/index.php?action=ph-leave-create&maHS=' . urlencode($maHS));
        }
        exit;
    }

    /**
     * Hiển thị danh sách đơn xin nghỉ
     */
    public function index() {
        require_role(['ph']);

        $user = current_user();
        $maPH = $this->model->getMaPhuHuynhByUsername($user['username']);
        
        if (!$maPH) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin phụ huynh.';
            header('Location: /public/index.php');
            exit;
        }

        // Lấy danh sách con
        $danhSachCon = $this->model->getDanhSachConCuaPhuHuynh($maPH);
        
        if (empty($danhSachCon)) {
            $_SESSION['flash_error'] = 'Không tìm thấy thông tin con em.';
            header('Location: /public/index.php');
            exit;
        }
        
        // Lấy mã học sinh được chọn
        $maHS = $_GET['maHS'] ?? $danhSachCon[0]['maHS'];

        // === DEBUG LOG ===
        error_log("=== ph-leave-list DEBUG ===");
        error_log("maPH: $maPH");
        error_log("maHS selected: $maHS");
        error_log("Total children: " . count($danhSachCon));
        
        // Lấy danh sách đơn của học sinh
        $danhSachDon = [];
        if ($maHS) {
            $danhSachDon = $this->model->getLeaveRequestsByStudent($maHS);
            error_log("Total requests: " . count($danhSachDon));
            
            // DEBUG: In ra query thực tế
            if (empty($danhSachDon)) {
                error_log("WARNING: No leave requests found for maHS=$maHS");
                
                // Kiểm tra ngược lại trong DB
                try {
                    $db = Database::getInstance()->getConnection();
                    $checkStmt = $db->prepare("SELECT COUNT(*) as total FROM donxinphep WHERE maHS = ?");
                    $checkStmt->execute([$maHS]);
                    $totalInDB = $checkStmt->fetch()['total'];
                    error_log("Total in DB for maHS=$maHS: $totalInDB");
                } catch (Exception $e) {
                    error_log("Error checking DB: " . $e->getMessage());
                }
            }
        }
        // === END DEBUG ===

        // ⚠️ QUAN TRỌNG: Truyền biến vào view
        require_once __DIR__ . '/../../views/ph/leave-requests/list.php';
    }

    /**
     * Hủy đơn xin nghỉ
     */
    public function cancel() {
        require_role(['ph']);

        $maDonXinPhep = $_GET['id'] ?? '';
        if (empty($maDonXinPhep)) {
            $_SESSION['flash_error'] = 'Không xác định được đơn cần hủy.';
            header('Location: /public/index.php?action=ph-leave-list');
            exit;
        }

        // Kiểm tra quyền sở hữu đơn
        $user = current_user();
        $maPH = $this->model->getMaPhuHuynhByUsername($user['username']);
        
        $don = $this->model->getLeaveRequestById($maDonXinPhep);
        if (!$don || $don['maPH'] !== $maPH) {
            $_SESSION['flash_error'] = 'Bạn không có quyền hủy đơn này.';
            header('Location: /public/index.php?action=ph-leave-list');
            exit;
        }

        // Chỉ cho phép hủy đơn đang chờ duyệt
        if ($don['trangThai'] !== 'Cho duyet') {
            $_SESSION['flash_error'] = 'Chỉ có thể hủy đơn đang chờ duyệt.';
            header('Location: /public/index.php?action=ph-leave-list&maHS=' . urlencode($don['maHS']));
            exit;
        }

        // Hủy đơn
        $result = $this->model->cancelLeaveRequest($maDonXinPhep);
        
        if ($result) {
            $_SESSION['flash_success'] = 'Đã hủy đơn xin nghỉ thành công.';
        } else {
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi hủy đơn.';
        }

        header('Location: /public/index.php?action=ph-leave-list&maHS=' . urlencode($don['maHS']));
        exit;
    }
}

<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/admin/quanLyHoSoHocSinhModel.php';
require_once __DIR__ . '/../../config/database.php';

class QuanLyHoSoHocSinhController {
    private $model;

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->model = new QuanLyHoSoHocSinhModel($db);
    }

    /**
     * Hiển thị danh sách tìm kiếm học sinh
     */
    public function index() {
        require_role(['admin']);

        $students = [];
        $error = null;
        $isSearch = false;

        // Kiểm tra có tìm kiếm không
        if (isset($_GET['search']) && $_GET['search'] == '1') {
            $isSearch = true;
            $criteria = [
                'maHS' => trim($_GET['maHS'] ?? ''),
                'hoTen' => trim($_GET['hoTen'] ?? ''),
                'maLop' => trim($_GET['maLop'] ?? ''),
                'khoi' => trim($_GET['khoi'] ?? ''),
            ];

            try {
                $students = $this->model->search($criteria);
            } catch (PDOException $e) {
                $error = $e->getMessage();
                error_log("Error searching students: " . $e->getMessage());
            }
        }

        // Truyền biến vào view
        $q_maHS = trim($_GET['maHS'] ?? '');
        $q_hoTen = trim($_GET['hoTen'] ?? '');
        $q_maLop = trim($_GET['maLop'] ?? '');
        $q_khoi = trim($_GET['khoi'] ?? '');

        require_once __DIR__ . '/../../views/admin/quanLyHoSoHocSinh/quanLyHoSoHocSinhView.php';
    }

    /**
     * Xem chi tiết hồ sơ học sinh
     */
    public function view() {
        require_role(['admin']);

        $maHS = $_GET['maHS'] ?? null;
        if (!$maHS) {
            $_SESSION['flash_error'] = 'Không có mã học sinh';
            header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
            exit;
        }

        try {
            $student = $this->model->find($maHS);
            if (!$student) {
                $_SESSION['flash_error'] = 'Không tìm thấy học sinh';
                header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
                exit;
            }

            // ⚠️ QUAN TRỌNG: KHÔNG gọi header ở đây
            // Header sẽ được gọi từ trong view
            require_once __DIR__ . '/../../views/admin/quanLyHoSoHocSinh/view.php';

        } catch (PDOException $e) {
            error_log("Error viewing student: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Lỗi khi tải thông tin học sinh';
            header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
            exit;
        }
    }

    /**
     * Chỉnh sửa hồ sơ học sinh
     */
    public function edit() {
        require_role(['admin']);

        $maHS = $_GET['maHS'] ?? $_POST['maHS'] ?? null;
        if (!$maHS) {
            $_SESSION['flash_error'] = 'Không có mã học sinh';
            header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
            exit;
        }

        try {
            $student = $this->model->find($maHS);
            if (!$student) {
                $_SESSION['flash_error'] = 'Không tìm thấy học sinh';
                header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
                exit;
            }

            // Xử lý POST - Cập nhật
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'hoTen' => $_POST['hoTen'] ?? $student['hoTen'],
                    'ngaySinh' => $_POST['ngaySinh'] ?? $student['ngaySinh'],
                    'diaChi' => $_POST['diaChi'] ?? $student['diaChi'],
                    'email' => $_POST['email'] ?? $student['email'],
                    'sdt' => $_POST['sdt'] ?? $student['sdt'],
                    'ph_sdt' => $_POST['ph_sdt'] ?? null,
                    'loaiHanhKiem' => $_POST['loaiHanhKiem'] ?? null,
                ];

                $this->model->update($maHS, $data);
                $_SESSION['flash_success'] = 'Cập nhật thành công';
                header('Location: /public/index.php?action=admin-xem-hs&maHS=' . urlencode($maHS));
                exit;
            }

            // Hiển thị form
            require_once __DIR__ . '/../../views/admin/quanLyHoSoHocSinh/edit.php';

        } catch (PDOException $e) {
            error_log("Error editing student: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Lỗi khi cập nhật thông tin';
            header('Location: /public/index.php?action=admin-quan-ly-hoc-sinh');
            exit;
        }
    }
}


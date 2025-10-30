<?php
/**
 * TeacherAssignmentController
 * Controller: Phân công giáo viên bộ môn cho lớp
 * Path: controllers/bgh/teacher_assignment_controller.php
 */

require_once __DIR__ . '/../../models/bgh/PhanCongModel.php';

class TeacherAssignmentController
{
    private $phanCong;
    private $message = '';
    private $messageType = '';

    public function __construct()
    {
        $this->phanCong = new PhanCongModel();
    }

    /**
     * Main page: Hiển thị trang phân công GV bộ môn
     */
    public function index()
    {
        // Lấy thông tin lớp
        $maLop = $_GET['maLop'] ?? '';
        if (empty($maLop)) {
            $_SESSION['flash_error'] = 'Thiếu mã lớp';
            header('Location: manage.php');
            exit;
        }

        $thongTinLop = $this->phanCong->getThongTinLop($maLop);
        if (!$thongTinLop) {
            $_SESSION['flash_error'] = 'Không tìm thấy lớp học';
            header('Location: manage.php');
            exit;
        }

        // Xử lý POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }

        // Lấy dữ liệu cho view
        $namHoc = $thongTinLop['namHoc'];
        $hocKy = $_GET['hocKy'] ?? 'HK1';
        $danhSachMonHoc = $this->phanCong->getAllMonHoc($namHoc);
        $danhSachPhanCong = $this->phanCong->getPhanCongGiangDay($maLop, $namHoc, $hocKy);

        // Chuyển đổi PDOStatement thành array
        $phanCongArray = [];
        while ($pc = $danhSachPhanCong->fetch()) {
            $phanCongArray[$pc['maMonHoc']] = $pc;
        }

        // Load view
        $this->loadView('bgh/assignments/assign_teachers', [
            'maLop' => $maLop,
            'thongTinLop' => $thongTinLop,
            'namHoc' => $namHoc,
            'hocKy' => $hocKy,
            'danhSachMonHoc' => $danhSachMonHoc,
            'phanCongArray' => $phanCongArray,
            'message' => $this->message,
            'messageType' => $this->messageType
        ]);
    }

    /**
     * Xử lý POST request (thêm/sửa/xóa phân công)
     */
    private function handlePostRequest()
    {
        $action = $_POST['action'] ?? '';
        $maLop = $_POST['maLop'] ?? $_GET['maLop'] ?? '';
        $maMonHoc = $_POST['maMonHoc'] ?? '';
        $maGV = $_POST['maGV'] ?? '';
        $namHoc = $_POST['namHoc'] ?? '';
        $hocKy = $_POST['hocKy'] ?? 'HK1';

        switch ($action) {
            case 'them_phan_cong':
                $this->themPhanCong($maLop, $maMonHoc, $maGV, $namHoc, $hocKy);
                break;

            case 'xoa_phan_cong':
                $maPhanCong = $_POST['maPhanCong'] ?? '';
                $this->xoaPhanCong($maPhanCong);
                break;

            default:
                $this->message = 'Hành động không hợp lệ';
                $this->messageType = 'danger';
        }
    }

    /**
     * Thêm/cập nhật phân công giảng dạy
     */
    private function themPhanCong($maLop, $maMonHoc, $maGV, $namHoc, $hocKy)
    {
        if (empty($maMonHoc) || empty($maGV)) {
            $this->message = 'Vui lòng chọn đầy đủ môn học và giáo viên';
            $this->messageType = 'danger';
            return;
        }

        // Kiểm tra đã phân công chưa
        $daPhanCong = $this->phanCong->kiemTraPhanCongTonTai($maLop, $maMonHoc, $namHoc, $hocKy);

        if ($daPhanCong) {
            // Cập nhật (đổi GV) - cần kiểm tra xem GV mới có vượt giới hạn không
            if ($daPhanCong['maGV'] !== $maGV) {
                // Chỉ kiểm tra nếu đổi sang GV khác
                $checkLimit = $this->phanCong->kiemTraGioiHanLop($maGV, $namHoc, $hocKy);
                if (!$checkLimit['success']) {
                    $this->message = $checkLimit['message'];
                    $this->messageType = 'warning';
                    return;
                }
            }
            
            if ($this->phanCong->capNhatPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy)) {
                $this->message = 'Cập nhật phân công giảng dạy thành công';
                $this->messageType = 'success';
            } else {
                $this->message = 'Lỗi khi cập nhật phân công';
                $this->messageType = 'danger';
            }
        } else {
            // Thêm mới - kiểm tra giới hạn số lớp
            $checkLimit = $this->phanCong->kiemTraGioiHanLop($maGV, $namHoc, $hocKy);
            if (!$checkLimit['success']) {
                $this->message = $checkLimit['message'];
                $this->messageType = 'warning';
                return;
            }
            
            if ($this->phanCong->themPhanCongGiangDay($maLop, $maMonHoc, $maGV, $namHoc, $hocKy)) {
                $this->message = 'Phân công giảng dạy thành công';
                $this->messageType = 'success';
            } else {
                $this->message = 'Lỗi khi phân công giảng dạy';
                $this->messageType = 'danger';
            }
        }
    }

    /**
     * Xóa phân công giảng dạy
     */
    private function xoaPhanCong($maPhanCong)
    {
        if (empty($maPhanCong)) {
            $this->message = 'Thiếu mã phân công';
            $this->messageType = 'danger';
            return;
        }

        if ($this->phanCong->xoaPhanCongGiangDay($maPhanCong)) {
            $this->message = 'Xóa phân công thành công';
            $this->messageType = 'success';
        } else {
            $this->message = 'Lỗi khi xóa phân công';
            $this->messageType = 'danger';
        }
    }

    /**
     * API: Lấy danh sách giáo viên theo môn học
     */
    public function getGiaoVienTheoMon()
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $tenMon = $_GET['tenMon'] ?? '';
        $namHoc = $_GET['namHoc'] ?? '2024-2025';
        $hocKy = $_GET['hocKy'] ?? 'HK1';
        
        // Debug log
        error_log("API getGiaoVienTheoMon - Môn: " . $tenMon);

        $result = $this->phanCong->getGiaoVienTheoMon($tenMon);

        $giaoViens = [];
        $filtered = 0;
        
        while ($row = $result->fetch()) {
            // Lấy số lớp đang dạy
            $soLopDangDay = $this->phanCong->demSoLopGVDang($row['maGV'], $namHoc, $hocKy);
            $checkLimit = $this->phanCong->kiemTraGioiHanLop($row['maGV'], $namHoc, $hocKy);
            
            // Chỉ thêm vào danh sách nếu chưa đạt giới hạn
            if ($checkLimit['success']) {
                $row['soLopDangDay'] = $soLopDangDay;
                $row['maxClasses'] = $checkLimit['maxClasses'];
                $row['canAssign'] = true;
                $row['limitMessage'] = $checkLimit['message'];
                
                $giaoViens[] = $row;
            } else {
                $filtered++;
                error_log("API getGiaoVienTheoMon - Lọc GV {$row['hoTen']} (đã đạt giới hạn: {$soLopDangDay}/{$checkLimit['maxClasses']})");
            }
        }

        error_log("API getGiaoVienTheoMon - Tìm thấy: " . count($giaoViens) . " giáo viên khả dụng, lọc bỏ: $filtered giáo viên");

        echo json_encode($giaoViens, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API: Lấy thông tin chi tiết phân công của giáo viên
     */
    public function getThongTinGiaoVien()
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $maGV = $_GET['maGV'] ?? '';
        $namHoc = $_GET['namHoc'] ?? '2024-2025';
        $hocKy = $_GET['hocKy'] ?? 'HK1';

        if (empty($maGV)) {
            echo json_encode(['error' => 'Thiếu mã giáo viên'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Lấy thông tin phân công
        $danhSachPhanCong = $this->phanCong->getThongTinPhanCongGV($maGV, $namHoc, $hocKy);
        
        // Kiểm tra giới hạn
        $checkLimit = $this->phanCong->kiemTraGioiHanLop($maGV, $namHoc, $hocKy);
        
        $response = [
            'success' => true,
            'soLopDangDay' => count($danhSachPhanCong),
            'maxClasses' => $checkLimit['maxClasses'],
            'canAssign' => $checkLimit['success'],
            'message' => $checkLimit['message'],
            'danhSachLop' => $danhSachPhanCong
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Helper: Load view
     */
    private function loadView($viewName, $data = [])
    {
        extract($data);
        require_once __DIR__ . '/../../views/' . $viewName . '.php';
    }
}

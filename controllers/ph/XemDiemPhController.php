<?php
// File: controllers/DiemController.php

require_once __DIR__ . '/../../models/ph/DiemPHModel.php'; // Đảm bảo đường dẫn đúng tới Model
require_once __DIR__ . '/../../middlewares/AuthGuard.php'; // Nếu có middleware thì bật lên

class DiemController {
    private $diemModel;

    public function __construct() {
        $this->diemModel = new DiemModel();
    }

    /**
     * Hàm xử lý chính: Phụ huynh xem điểm
     * Được gọi từ index.php?action=ph-xem-diem
     */
    public function xemDiemPhuHuynh() {
        // 1. Định nghĩa hằng số bảo mật để View không báo lỗi
        if (!defined('ACCESS_ALLOWED')) {
            define('ACCESS_ALLOWED', true);
        }

        // 2. Start session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 3. Kiểm tra đăng nhập (Logic của bạn)
        // Lưu ý: Kiểm tra key session cho đúng với hệ thống (auth hay user)
        if (!isset($_SESSION['auth']) && !isset($_SESSION['user'])) {
            header('Location: /public/index.php');
            exit;
        }
        
        // Lấy thông tin user (Hỗ trợ cả 2 cách lưu session cũ và mới của bạn)
        $user = $_SESSION['auth'] ?? $_SESSION['user'];

        // 4. Lấy mã phụ huynh
        $maPH = null;
        if (isset($user['username'])) {
            $maPH = $this->diemModel->getMaPhuHuynhByUsername($user['username']);
        }

        // Fallback demo (theo code của bạn)
        if (!$maPH) {
            // $maPH = 'PH0001'; // Tắt demo khi chạy thật
             echo "Không tìm thấy thông tin phụ huynh."; return;
        }

        // 5. Lấy danh sách con
        // Hàm này trả về Array, không phải PDOStatement
        $danhSachCon = $this->diemModel->getDanhSachConCuaPhuHuynh($maPH);

        if (empty($danhSachCon)) {
            echo "Phụ huynh chưa có con em nào trong hệ thống."; return;
        }

        // 6. Xác định học sinh cần xem
        $maHS = isset($_GET['maHS']) ? $_GET['maHS'] : null;

        // Nếu chưa chọn, mặc định lấy con đầu tiên trong mảng
        if (!$maHS && !empty($danhSachCon)) {
            $maHS = $danhSachCon[0]['maHS'];
        }

        // 7. Kiểm tra quyền: Con này có phải của PH này không?
        $isMyChild = false;
        foreach ($danhSachCon as $con) {
            if ($con['maHS'] == $maHS) {
                $isMyChild = true;
                break;
            }
        }
        
        if (!$isMyChild) {
            die('CẢNH BÁO: Bạn không có quyền xem điểm của học sinh này.');
        }

        // 8. Xử lý bộ lọc Năm học / Học kỳ
        // Dùng date() để tính tự động như logic của bạn
        $namHocHienTai = date('Y') . '-' . (date('Y') + 1);
        $hocKyHienTai = (date('n') >= 1 && date('n') <= 5) ? 'HK2' : 'HK1';

        // Nếu view trả về namHoc, lấy namHoc, nếu không lấy mặc định
        $thongTinHS_Temp = $this->diemModel->getThongTinHocSinh($maHS); // Lấy để biết năm học thực tế
        $defaultNamHoc = $thongTinHS_Temp['namHoc'] ?? $namHocHienTai;

        $namHoc = isset($_GET['namHoc']) ? $_GET['namHoc'] : $defaultNamHoc;
        $hocKy = isset($_GET['hocKy']) ? $_GET['hocKy'] : $hocKyHienTai;

        // 9. Lấy dữ liệu hiển thị
        $danhSachNamHoc = $this->diemModel->getAllNamHoc();
        $thongTinHS = $this->diemModel->getThongTinHocSinh($maHS);
        
        // Lấy bảng điểm (Lưu ý: dùng hàm trả về Array cho View dễ xử lý)
        $danhSachDiem = $this->diemModel->getBangDiemConCuaPhuHuynh($maPH, $maHS, $hocKy, $namHoc);
        
        $diemTB = $this->diemModel->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);

        // Biến $bangDiem để tương thích với view cũ nếu có
        $bangDiem = $danhSachDiem; 

        // 10. Gọi View
        // Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn
        if (file_exists(__DIR__ . '/../../views/ph/xem_diem.php')) {
            require_once __DIR__ . '/../../views/ph/xem_diem.php';
        } elseif (file_exists(__DIR__ . '/../../views/hs/xem_diem.php')) {
            // Fallback sang view học sinh nếu view PH chưa tạo
             $role = 'ph'; // Cờ để bật dropdown chọn con
            require_once __DIR__ . '/../../views/hs/xem_diem.php';
        } else {
            echo "Lỗi: Không tìm thấy file View (views/ph/xem_diem.php)";
        }
    }
}
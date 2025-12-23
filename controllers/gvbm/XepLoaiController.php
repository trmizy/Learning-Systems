<?php
// File: controllers/gvbm/XepLoaiController.php

// 1. Thiết lập đường dẫn gốc (Root Path) chuẩn xác
$rootPath = dirname(__DIR__, 2); 

// 2. Gọi các file Model & Middleware bằng đường dẫn tuyệt đối
require_once $rootPath . '/models/gvbm/XepLoaiModel.php';
require_once $rootPath . '/middlewares/AuthGuard.php';

class XepLoaiController {
    private $model;

    public function __construct() {
        $this->model = new XepLoaiModel();
    }

    public function showXepLoaiPage() {
        require_role(['gvcn']);
        
        $user = current_user();
        $username = $user['username'] ?? '';
        
        // --- Xử lý dữ liệu GV & Lớp ---
        $maGV = $this->model->getMaGVByUsername($username);
        if (!$maGV) {
            $_SESSION['flash_error'] = "Không tìm thấy hồ sơ giáo viên.";
            header('Location: index.php'); exit;
        }

        $lopInfo = $this->model->getLopChuNhiem($maGV);
        if (!$lopInfo) {
            $_SESSION['flash_error'] = "Bạn chưa được phân công chủ nhiệm.";
            header('Location: index.php'); exit;
        }

        $danhSach = $this->model->getDanhSachXepLoai($lopInfo['maLop']);
        $tenLop = $lopInfo['tenLop'];

        // --- Logic Đề xuất Tự động ---
        foreach ($danhSach as &$hs) {
            $dtb = (isset($hs['diemTrungBinh']) && is_numeric($hs['diemTrungBinh'])) ? (float)$hs['diemTrungBinh'] : -1;
            
            // Auto HL
            if ($dtb == -1) $hs['auto_HL'] = '-';
            elseif ($dtb >= 8.0) $hs['auto_HL'] = 'Giỏi';
            elseif ($dtb >= 6.5) $hs['auto_HL'] = 'Khá';
            elseif ($dtb >= 5.0) $hs['auto_HL'] = 'Trung bình';
            elseif ($dtb >= 3.5) $hs['auto_HL'] = 'Yếu';
            else $hs['auto_HL'] = 'Kém';

            // Auto HK
            $nghi = (int)($hs['soBuoiNghiKhongCoPhep'] ?? 0);
            $vipham = (int)($hs['soLanViPham'] ?? 0);
            
            if ($nghi > 5 || $vipham > 3) $hs['auto_HK'] = 'Yếu';
            elseif ($nghi > 3 || $vipham >= 2) $hs['auto_HK'] = 'Trung bình';
            elseif ($nghi > 1 || $vipham == 1) $hs['auto_HK'] = 'Khá';
            else $hs['auto_HK'] = 'Tốt';
        }

        // --- Logic Lọc ---
        $filterHL = $_GET['filter_hl'] ?? '';
        $filterHK = $_GET['filter_hk'] ?? '';
        if ($filterHL || $filterHK) {
            $danhSach = array_filter($danhSach, function($hs) use ($filterHL, $filterHK) {
                $cHL = !empty($hs['xepLoaiHocLuc']) ? $hs['xepLoaiHocLuc'] : $hs['auto_HL'];
                $cHK = !empty($hs['loaiHanhKiem']) ? $hs['loaiHanhKiem'] : $hs['auto_HK'];
                $mHL = empty($filterHL) || (mb_strtolower(trim($cHL)) == mb_strtolower(trim($filterHL)));
                $mHK = empty($filterHK) || (mb_strtolower(trim($cHK)) == mb_strtolower(trim($filterHK)));
                return $mHL && $mHK;
            });
        }

        $pageTitle = "Xếp loại - $tenLop";
        $rootPath = dirname(__DIR__, 2); // Khai báo lại root cho chắc chắn

        // === 🚀 PHẦN QUAN TRỌNG NHẤT: GỌI VIEW ===
        // Gọi Header
        require_once $rootPath . '/views/layouts/header.php';
        
        // Kiểm tra và gọi file View chính
        $viewPath = $rootPath . '/views/gvbm/xep_loai.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // In ra thông báo lỗi chi tiết đường dẫn để bạn kiểm tra
            echo "<div style='margin: 20px; padding: 20px; background: #ffebee; border: 2px solid red; color: #b71c1c;'>";
            echo "<h3>🚨 LỖI: KHÔNG TÌM THẤY FILE VIEW</h3>";
            echo "<p>PHP đang tìm file tại đường dẫn chính xác sau:</p>";
            echo "<code style='background: #fff; padding: 5px; display: block;'>$viewPath</code>";
            echo "<hr>";
            echo "<p>👉 <strong>Hãy kiểm tra lại:</strong></p>";
            echo "<ul>";
            echo "<li>File của bạn có đúng tên là <code>xem_loai.php</code> không? (Cẩn thận nhầm với <code>xep_loai.php</code>)</li>";
            echo "<li>File có nằm đúng trong thư mục <code>views/gvbm/</code> không?</li>";
            echo "</ul>";
            echo "</div>";
        }

        // Gọi Footer
        require_once $rootPath . '/views/layouts/footer.php';
    }

    public function saveXepLoai() {
        require_role(['gvcn']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
            $count = 0;
            foreach ($_POST['data'] as $maHS => $val) {
                if ($this->model->updateXepLoai($maHS, $val['hl'], $val['hk'], $val['nhanXet'] ?? '')) {
                    $count++;
                }
            }
            $_SESSION['flash_success'] = "Đã lưu thành công cho $count học sinh.";
        }
        header('Location: index.php?action=xep_loai');
        exit;
    }
}
?>
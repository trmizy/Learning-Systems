<?php
require_once '/../models/ph/DiemPHModel.php';

class DiemController {
    private $diemModel;

    public function __construct() {
        $this->diemModel = new DiemModel();
    }

    /**
     * Chức năng xem điểm dành cho Phụ huynh
     * URL: index.php?action=ph-xem-diem&maHS=...
     */
    public function xemDiemPhuHuynh() {
        // 1. Kiểm tra đăng nhập (Giả sử bạn lưu thông tin user trong Session)
        // Nếu chưa có session_start() ở index.php thì bỏ comment dòng dưới
        // session_start(); 
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
            // Chuyển hướng về trang login nếu chưa đăng nhập
            header('Location: index.php?action=login'); 
            exit;
        }

        $username = $_SESSION['user']['username'];

        // 2. Lấy tham số từ URL
        $maHS = isset($_GET['maHS']) ? $_GET['maHS'] : null;
        $namHoc = isset($_GET['namHoc']) ? $_GET['namHoc'] : '2024-2025'; // Mặc định năm hiện tại
        $hocKy = isset($_GET['hocKy']) ? $_GET['hocKy'] : 'HK1'; // Mặc định HK1

        if (!$maHS) {
            echo "Lỗi: Không tìm thấy mã học sinh.";
            return;
        }

        // 3. Lấy mã Phụ huynh từ Username hiện tại
        $maPH = $this->diemModel->getMaPhuHuynhByUsername($username);

        if (!$maPH) {
            echo "Lỗi: Tài khoản này không phải là phụ huynh.";
            return;
        }

        // 4. KIỂM TRA BẢO MẬT: Học sinh này có phải con của Phụ huynh này không?
        // Sử dụng hàm getDanhSachConCuaPhuHuynh để kiểm tra
        $dsCon = $this->diemModel->getDanhSachConCuaPhuHuynh($maPH);
        $laConCuaMinh = false;
        foreach ($dsCon as $con) {
            if ($con['maHS'] == $maHS) {
                $laConCuaMinh = true;
                break;
            }
        }

        if (!$laConCuaMinh) {
            echo "Bạn không có quyền xem điểm của học sinh này!";
            return;
        }

        // 5. Lấy dữ liệu cần thiết từ Model
        // 5.1. Thông tin học sinh
        $thongTinHS = $this->diemModel->getThongTinHocSinh($maHS);

        // 5.2. Danh sách năm học (để tạo dropdown lọc)
        $dsNamHoc = $this->diemModel->getAllNamHoc();

        // 5.3. Bảng điểm (Sử dụng hàm getBangDiemConCuaPhuHuynh trong Model bạn gửi)
        $bangDiem = $this->diemModel->getBangDiemConCuaPhuHuynh($maPH, $maHS, $hocKy, $namHoc);

        // 5.4. Tính điểm trung bình chung (nếu cần hiển thị)
        $diemTB = $this->diemModel->getDiemTrungBinhChung($maHS, $namHoc, $hocKy);

        // 6. Gọi View để hiển thị
        // Tạo file view tại đường dẫn này
        require_once 'views/phuhuynh/xem_diem.php';
    }
}
?>
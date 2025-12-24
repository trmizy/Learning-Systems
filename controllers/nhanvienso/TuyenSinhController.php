<?php
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../models/nhanvienso/TuyenSinhModel.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

class TuyenSinhController {
    private $model;

    public function __construct() {
        $this->model = new TuyenSinhModel();
    }

    /**
     * Hiển thị form upload điểm thi
     */
    public function uploadDiemThi() {
        require_role(['nhanvienso']);

        // Lấy danh sách trường để chọn
        $danhSachTruong = $this->model->getDanhSachTruong();

        require_once __DIR__ . '/../../views/nhanvienso/tuyen_sinh_upload.php';
    }

    /**
     * Xử lý upload file Excel - FIX: Cải thiện validation
     */
    public function processUpload() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Phương thức không hợp lệ';
            header('Location: /public/index.php?action=nhanvienso-tuyen-sinh-upload');
            exit;
        }

        try {
            // Validate file upload
            if (!isset($_FILES['fileExcel']) || $_FILES['fileExcel']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Vui lòng chọn file Excel');
            }

            // Validate năm tuyển sinh
            $namTuyenSinh = isset($_POST['namTuyenSinh']) ? trim($_POST['namTuyenSinh']) : '';
            
            if (empty($namTuyenSinh)) {
                throw new Exception('Vui lòng chọn năm tuyển sinh');
            }
            
            if (!is_numeric($namTuyenSinh) || $namTuyenSinh < 2020 || $namTuyenSinh > 2100) {
                throw new Exception('Năm tuyển sinh không hợp lệ');
            }

            error_log("=== Upload Process ===");
            error_log("namTuyenSinh: " . $namTuyenSinh);
            error_log("File: " . $_FILES['fileExcel']['name']);

            $file = $_FILES['fileExcel'];
            $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('File phải là định dạng Excel (.xls hoặc .xlsx)');
            }

            // Đọc file Excel
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Bỏ qua dòng tiêu đề
            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Kiểm tra dòng rỗng
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // ⚠️ FIX: MAP LẠI THỨ TỰ CỘT THEO EXCEL THỰC TẾ
                    // A (0): Mã Thí Sinh
                    // B (1): Họ Tên
                    // C (2): CCCD
                    // D (3): Ngày Sinh
                    // E (4): Điểm Văn    ← ĐÃ SỬA
                    // F (5): Điểm Toán   ← ĐÃ SỬA
                    // G (6): Điểm Anh    ← ĐÃ SỬA
                    // H (7): SĐT         ← ĐÃ SỬA
                    // I (8): Nơi Sinh    ← ĐÃ SỬA
                    // J (9): Giới Tính   ← ĐÃ SỬA
                    
                    $maThiSinh = trim($row[0] ?? '');       // A: Mã TS
                    $hoTen = trim($row[1] ?? '');           // B: Họ tên
                    $soCCCD = trim($row[2] ?? '');          // C: CCCD
                    $ngaySinhRaw = $row[3] ?? '';           // D: Ngày sinh
                    $diemVan = floatval($row[4] ?? 0);      // E: Điểm Văn ⚠️ ĐÃ SỬA
                    $diemToan = floatval($row[5] ?? 0);     // F: Điểm Toán ⚠️ ĐÃ SỬA
                    $diemAnh = floatval($row[6] ?? 0);      // G: Điểm Anh ⚠️ ĐÃ SỬA
                    $soDienThoai = trim($row[7] ?? '');     // H: SĐT ⚠️ ĐÃ SỬA
                    $noiSinh = trim($row[8] ?? '');         // I: Nơi sinh ⚠️ ĐÃ SỬA
                    $gioiTinhRaw = $row[9] ?? '';           // J: Giới tính ⚠️ ĐÃ SỬA

                    // === DEBUG LOG CHI TIẾT ===
                    error_log("=== Dòng $rowNumber - DEBUG CHI TIẾT ===");
                    error_log("row[0] (Mã TS): " . var_export($row[0] ?? null, true));
                    error_log("row[1] (Họ tên): " . var_export($row[1] ?? null, true));
                    error_log("row[2] (CCCD): " . var_export($row[2] ?? null, true));
                    error_log("row[3] (Ngày sinh): " . var_export($row[3] ?? null, true));
                    error_log("row[4] (Điểm Văn): " . var_export($row[4] ?? null, true));
                    error_log("row[5] (Điểm Toán): " . var_export($row[5] ?? null, true));
                    error_log("row[6] (Điểm Anh): " . var_export($row[6] ?? null, true));
                    error_log("row[7] (SĐT): " . var_export($row[7] ?? null, true));
                    error_log("row[8] (Nơi sinh): " . var_export($row[8] ?? null, true));
                    error_log("row[9] (Giới tính RAW): " . var_export($row[9] ?? null, true) . " | Type: " . gettype($row[9] ?? null));
                    error_log("===================");
                    // === END DEBUG ===

                    // Validate dữ liệu bắt buộc
                    if (empty($maThiSinh)) {
                        throw new Exception("Thiếu mã thí sinh");
                    }
                    if (empty($soCCCD) || empty($hoTen)) {
                        throw new Exception("Thiếu CCCD hoặc họ tên");
                    }

                    // Chuẩn hóa ngày sinh
                    $ngaySinhFormatted = $this->formatNgaySinh($ngaySinhRaw);
                    if (!$ngaySinhFormatted) {
                        throw new Exception("Ngày sinh không hợp lệ");
                    }

                    // Chuyển đổi giới tính: Nam → M, Nữ → F
                    $gioiTinh = $this->convertGioiTinh($gioiTinhRaw);
                    if (!$gioiTinh) {
                        throw new Exception("Giới tính không hợp lệ (nhận được: '$gioiTinhRaw', yêu cầu: 'Nam' hoặc 'Nữ')");
                    }

                    // Tính tổng điểm: Toán*2 + Văn*2 + Anh
                    $tongDiem = ($diemToan * 2) + ($diemVan * 2) + $diemAnh;

                    // ⚠️ FIX: Thêm diemVan, diemToan, diemAnh vào mảng
                    $thiSinhData = [
                        'maThiSinh' => $maThiSinh,
                        'soCCCD' => $soCCCD,
                        'hoTen' => $hoTen,
                        'ngaySinh' => $ngaySinhFormatted,
                        'gioiTinh' => $gioiTinh,
                        'diemVan' => $diemVan,      // ⚠️ THÊM
                        'diemToan' => $diemToan,    // ⚠️ THÊM
                        'diemAnh' => $diemAnh,      // ⚠️ THÊM
                        'diem' => $tongDiem,
                        'soDienThoai' => $soDienThoai,
                        'noiSinh' => $noiSinh,
                        'namTuyenSinh' => $namTuyenSinh
                    ];

                    // Lưu vào database
                    if ($this->model->themThiSinh($thiSinhData)) {
                        $successCount++;
                    } else {
                        throw new Exception("Không thể lưu dữ liệu");
                    }

                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = "Dòng $rowNumber: " . $e->getMessage();
                    error_log("❌ Lỗi dòng $rowNumber: " . $e->getMessage());
                }
            }

            // Thông báo kết quả
            if ($successCount > 0) {
                $_SESSION['flash_success'] = "✅ Upload thành công $successCount thí sinh (Năm $namTuyenSinh)";
            }
            if ($errorCount > 0) {
                $_SESSION['flash_error'] = "⚠️ Có $errorCount lỗi: " . implode('; ', array_slice($errors, 0, 5));
            }

        } catch (Exception $e) {
            $_SESSION['flash_error'] = '❌ Lỗi: ' . $e->getMessage();
        }

        header('Location: /public/index.php?action=nhanvienso-tuyen-sinh-upload');
        exit;
    }

    /**
     * Hiển thị danh sách thí sinh
     */
    public function danhSachThiSinh() {
        require_role(['nhanvienso']);

        $namTuyenSinh = $_GET['namTuyenSinh'] ?? date('Y');
        $search = $_GET['search'] ?? '';

        $danhSachThiSinh = $this->model->getDanhSachThiSinh($namTuyenSinh, $search);
        $thongKe = $this->model->getThongKeTuyenSinh($namTuyenSinh);

        require_once __DIR__ . '/../../views/nhanvienso/tuyen_sinh_list.php';
    }

    /**
     * Format ngày sinh từ Excel - FIX: Hỗ trợ thêm format Y-m-d H:i:s
     */
    private function formatNgaySinh($ngaySinh) {
        if (empty($ngaySinh)) {
            error_log("⚠️ formatNgaySinh: Giá trị rỗng");
            return null;
        }

        error_log("🔍 formatNgaySinh INPUT: " . var_export($ngaySinh, true) . " | Type: " . gettype($ngaySinh));

        // ===== TRƯỜNG HỢP 1: Excel Serial Date Number =====
        if (is_numeric($ngaySinh)) {
            try {
                $ngaySinhFloat = floatval($ngaySinh);
                
                if ($ngaySinhFloat >= 20000 && $ngaySinhFloat <= 50000) {
                    $unixTimestamp = ($ngaySinhFloat - 25569) * 86400;
                    $formatted = date('Y-m-d', $unixTimestamp);
                    
                    $year = (int)date('Y', $unixTimestamp);
                    
                    error_log("📅 Excel Date: $ngaySinhFloat → Unix: $unixTimestamp → Formatted: $formatted (Năm: $year)");
                    
                    if ($year >= 2005 && $year <= 2012) {
                        return $formatted;
                    } else {
                        error_log("❌ Năm sinh ngoài khoảng cho phép: $year (Yêu cầu: 2005-2012)");
                    }
                } else {
                    error_log("⚠️ Excel date number ngoài khoảng hợp lệ: $ngaySinhFloat");
                }
            } catch (Exception $e) {
                error_log("❌ Lỗi parse Excel date: " . $e->getMessage());
            }
        }

        // ===== TRƯỜNG HỢP 2: String Format d/m/Y hoặc d/m/y =====
        if (is_string($ngaySinh) && strpos($ngaySinh, '/') !== false) {
            $parts = explode('/', trim($ngaySinh));
            
            if (count($parts) === 3) {
                $day = (int)$parts[0];
                $month = (int)$parts[1];
                $year = (int)$parts[2];
                
                if ($year < 100) {
                    $year = ($year < 50) ? 2000 + $year : 1900 + $year;
                }
                
                error_log("📝 String d/m/Y: $day/$month/$year");
                
                if (checkdate($month, $day, $year) && $year >= 2005 && $year <= 2012) {
                    $formatted = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    error_log("✅ Formatted: $formatted");
                    return $formatted;
                } else {
                    error_log("❌ Ngày không hợp lệ hoặc năm ngoài khoảng: $day/$month/$year");
                }
            }
        }

        // ===== TRƯỜNG HỢP 3: String Format Y-m-d hoặc Y-m-d H:i:s =====
        if (is_string($ngaySinh)) {
            $ngaySinhTrimmed = trim($ngaySinh);
            
            // ⚠️ FIX: Tách phần ngày ra khỏi timestamp
            // VD: "2011-04-05 00:00:00" → "2011-04-05"
            if (preg_match('/^(\d{4}-\d{2}-\d{2})(\s+\d{2}:\d{2}:\d{2})?$/', $ngaySinhTrimmed, $matches)) {
                $datePart = $matches[1]; // Chỉ lấy phần YYYY-MM-DD
                $year = (int)substr($datePart, 0, 4);
                
                error_log("📅 String Y-m-d (có/không timestamp): $ngaySinhTrimmed → Date: $datePart (Năm: $year)");
                
                // Validate năm sinh
                if ($year >= 2005 && $year <= 2012) {
                    // Validate format ngày bằng strtotime
                    if (strtotime($datePart) !== false) {
                        error_log("✅ Validated: $datePart");
                        return $datePart;
                    } else {
                        error_log("❌ Ngày không hợp lệ: $datePart");
                    }
                } else {
                    error_log("❌ Năm ngoài khoảng cho phép: $year");
                }
            } else {
                error_log("⚠️ Format string không khớp regex: $ngaySinhTrimmed");
            }
        }

        // ===== TRƯỜNG HỢP 4: DateTime Object (từ PhpSpreadsheet) =====
        if ($ngaySinh instanceof \DateTime) {
            $formatted = $ngaySinh->format('Y-m-d');
            $year = (int)$ngaySinh->format('Y');
            
            error_log("🗓️ DateTime Object: $formatted (Năm: $year)");
            
            if ($year >= 2005 && $year <= 2012) {
                return $formatted;
            } else {
                error_log("❌ Năm ngoài khoảng cho phép: $year");
            }
        }

        // Không parse được
        error_log("❌ KHÔNG PARSE ĐƯỢC ngày sinh: " . var_export($ngaySinh, true));
        return null;
    }

    /**
     * Chuyển đổi giới tính - FIX: Thêm validate type
     */
    private function convertGioiTinh($gioiTinhRaw) {
        // ⚠️ FIX 1: Kiểm tra nếu là số → báo lỗi rõ ràng
        if (is_numeric($gioiTinhRaw)) {
            error_log("❌ convertGioiTinh: Giá trị là SỐ '$gioiTinhRaw', không phải text");
            return null;
        }
        
        // Chuẩn hóa: loại bỏ khoảng trắng và chuyển thành lowercase
        $gioiTinhNormalized = trim(mb_strtolower($gioiTinhRaw, 'UTF-8'));
        
        // Map giá trị - TOÀN BỘ LOWERCASE
        $genderMap = [
            'nam' => 'M',
            'nu' => 'F',
            'nữ' => 'F',
            'm' => 'M',
            'f' => 'F',
            'male' => 'M',
            'female' => 'F',
            '0' => 'F', // ⚠️ THÊM: Nếu Excel code 0=Nữ, 1=Nam
            '1' => 'M',
        ];
        
        // DEBUG LOG
        error_log("🔍 convertGioiTinh: '$gioiTinhRaw' (type: " . gettype($gioiTinhRaw) . ") → normalized: '$gioiTinhNormalized' → result: " . ($genderMap[$gioiTinhNormalized] ?? 'NULL'));
        
        return $genderMap[$gioiTinhNormalized] ?? null;
    }

    public function themThiSinh() {
        require_role(['nhanvienso']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate dữ liệu
            $data = [
                'maThiSinh' => $_POST['maThiSinh'] ?? '',
                'hoTen' => $_POST['hoTen'] ?? '',
                'soCCCD' => $_POST['soCCCD'] ?? '',
                'ngaySinh' => $_POST['ngaySinh'] ?? '',
                'gioiTinh' => $_POST['gioiTinh'] ?? '',
                'diemVan' => floatval($_POST['diemVan'] ?? 0),
                'diemToan' => floatval($_POST['diemToan'] ?? 0),
                'diemAnh' => floatval($_POST['diemAnh'] ?? 0),
                'diem' => 0, // Sẽ tính sau
                'soDienThoai' => $_POST['soDienThoai'] ?? '',
                'noiSinh' => $_POST['noiSinh'] ?? '',
                'namTuyenSinh' => $_POST['namTuyenSinh'] ?? date('Y')
            ];

            // Tính tổng điểm
            $data['diem'] = $data['diemVan'] * 2 + $data['diemToan'] * 2 + $data['diemAnh'];

            // Gọi model
            $result = $this->model->themThiSinh($data);

            // ✅ Hiển thị thông báo
            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }

            // Redirect về trang danh sách
            header('Location: /public/index.php?action=nhanvienso-tuyen-sinh');
            exit;
        }

        // Hiển thị form thêm mới
        require_once __DIR__ . '/../../views/nhanvienso/them_thi_sinh.php';
    }
}

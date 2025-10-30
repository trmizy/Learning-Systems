<?php
// Script để chạy migration tạo bảng PhanCongGiangDay
require_once __DIR__ . '/config/database.php';

echo "=== Migration: Tạo bảng PhanCongGiangDay ===\n\n";

$db = Database::getInstance()->getConnection();

// Đọc file SQL
$sqlFile = __DIR__ . '/migration_phan_cong_giang_day.sql';

if (!file_exists($sqlFile)) {
    die("❌ Không tìm thấy file migration: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Tách các câu lệnh SQL (split by semicolon)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^SET/', $stmt);
    }
);

$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        $db->exec($statement);
        $success++;
        
        // Hiển thị thông tin câu lệnh
        if (preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $matches)) {
            echo "✅ Tạo bảng: " . $matches[1] . "\n";
        } elseif (preg_match('/CREATE INDEX.*?(\w+)/i', $statement, $matches)) {
            echo "✅ Tạo index: " . $matches[1] . "\n";
        } elseif (preg_match('/INSERT INTO (\w+)/i', $statement, $matches)) {
            echo "✅ Insert dữ liệu vào: " . $matches[1] . "\n";
        } elseif (preg_match('/UPDATE (\w+)/i', $statement, $matches)) {
            echo "✅ Update dữ liệu: " . $matches[1] . "\n";
        } else {
            echo "✅ Thực thi câu lệnh thành công\n";
        }
    } catch (PDOException $e) {
        // Bỏ qua lỗi nếu bảng/index đã tồn tại
        if (strpos($e->getMessage(), 'already exists') !== false || 
            strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "⚠️  Đã tồn tại, bỏ qua: " . substr($statement, 0, 50) . "...\n";
        } else {
            $errors++;
            echo "❌ Lỗi: " . $e->getMessage() . "\n";
            echo "   Câu lệnh: " . substr($statement, 0, 100) . "...\n";
        }
    }
}

echo "\n=== Kết quả ===\n";
echo "✅ Thành công: $success câu lệnh\n";
echo "❌ Lỗi: $errors câu lệnh\n";

// Kiểm tra bảng đã tạo chưa
try {
    $stmt = $db->query("SHOW TABLES LIKE 'PhanCongGiangDay'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "\n✅ Bảng PhanCongGiangDay đã được tạo thành công!\n";
        
        // Đếm số bản ghi trong các bảng liên quan
        $stmt = $db->query("SELECT COUNT(*) as count FROM MonHoc");
        $monHocCount = $stmt->fetch()['count'];
        echo "📊 Số môn học: $monHocCount\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM GiaoVienBoMon");
        $gvCount = $stmt->fetch()['count'];
        echo "📊 Số giáo viên: $gvCount\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM PhanCongGiangDay");
        $pcCount = $stmt->fetch()['count'];
        echo "📊 Số phân công hiện tại: $pcCount\n";
    } else {
        echo "\n❌ Bảng PhanCongGiangDay chưa được tạo!\n";
    }
} catch (PDOException $e) {
    echo "\n❌ Lỗi kiểm tra: " . $e->getMessage() . "\n";
}

echo "\n=== Hoàn tất ===\n";

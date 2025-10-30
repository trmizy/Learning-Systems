<?php
/**
 * Script migration: Tạo bảng PhanCongPhongHoc và migrate dữ liệu cũ
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "📦 Bắt đầu migration...\n\n";
    
    // Đọc và thực thi SQL migration
    $sql = file_get_contents(__DIR__ . '/migration_phan_cong_phong_hoc.sql');
    $db->exec($sql);
    echo "✅ Tạo bảng PhanCongPhongHoc thành công\n\n";
    
    // Migrate dữ liệu từ PhongHoc sang PhanCongPhongHoc
    echo "📋 Migrate dữ liệu phân công phòng hiện tại...\n";
    
    $stmt = $db->prepare("
        SELECT p.maPhong, p.dangGiaoChoMaLop, l.namHoc
        FROM PhongHoc p
        JOIN LopHoc l ON l.maLop = p.dangGiaoChoMaLop
        WHERE p.dangGiaoChoMaLop IS NOT NULL
    ");
    $stmt->execute();
    $currentAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrated = 0;
    foreach ($currentAssignments as $assignment) {
        $maPhanCong = $assignment['maPhong'] . '_' . $assignment['dangGiaoChoMaLop'] . '_' . str_replace('-', '', $assignment['namHoc']);
        
        $insertStmt = $db->prepare("
            INSERT INTO PhanCongPhongHoc (maPhanCong, maPhong, maLop, namHoc, ngayPhanCong)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        try {
            $insertStmt->execute([
                $maPhanCong,
                $assignment['maPhong'],
                $assignment['dangGiaoChoMaLop'],
                $assignment['namHoc']
            ]);
            $migrated++;
            echo "   ✅ {$assignment['maPhong']} → {$assignment['dangGiaoChoMaLop']} ({$assignment['namHoc']})\n";
        } catch (PDOException $e) {
            echo "   ⚠️  Bỏ qua: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Đã migrate: $migrated phân công phòng\n";
    
    // Giải phóng tất cả phòng trong bảng PhongHoc
    echo "\n🔓 Giải phóng cột dangGiaoChoMaLop trong bảng PhongHoc...\n";
    $db->exec("UPDATE PhongHoc SET dangGiaoChoMaLop = NULL");
    echo "   ✅ Đã giải phóng tất cả phòng\n";
    
    echo "\n✨ Migration hoàn thành!\n";
    echo "💡 Từ giờ sử dụng bảng PhanCongPhongHoc để quản lý phân công phòng học\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}

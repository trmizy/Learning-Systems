<?php
/**
 * Migration: Fix collation mismatch
 * 
 * Problem: PhanCongPhongHoc table was created with utf8mb4_unicode_ci
 * while other tables (PhongHoc, LopHoc) use utf8mb4_general_ci
 * 
 * Solution: Convert PhanCongPhongHoc columns to utf8mb4_general_ci
 */

require_once __DIR__ . '/../../config/database.php';

echo "=== FIX COLLATION MISMATCH ===\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "1. Kiểm tra collation hiện tại...\n";
    
    // Check current collations
    $stmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            COLLATION_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'HeThongQuanLyHocSinh1'
          AND TABLE_NAME = 'PhanCongPhongHoc'
          AND COLUMN_NAME IN ('maPhong', 'maLop', 'namHoc')
        ORDER BY COLUMN_NAME
    ");
    
    echo "   Collation trước khi sửa:\n";
    while ($row = $stmt->fetch()) {
        echo "      - {$row['COLUMN_NAME']}: {$row['COLLATION_NAME']}\n";
    }
    echo "\n";
    
    echo "2. Chuyển đổi collation sang utf8mb4_general_ci...\n";
    
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Convert columns to utf8mb4_general_ci
    $pdo->exec("
        ALTER TABLE PhanCongPhongHoc 
        MODIFY COLUMN maPhong VARCHAR(10) 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
    ");
    echo "   ✓ Converted maPhong\n";
    
    $pdo->exec("
        ALTER TABLE PhanCongPhongHoc 
        MODIFY COLUMN maLop VARCHAR(10) 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
    ");
    echo "   ✓ Converted maLop\n";
    
    $pdo->exec("
        ALTER TABLE PhanCongPhongHoc 
        MODIFY COLUMN namHoc VARCHAR(10) 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
    ");
    echo "   ✓ Converted namHoc\n";
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n3. Kiểm tra collation sau khi sửa...\n";
    
    // Verify new collations
    $stmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            COLLATION_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'HeThongQuanLyHocSinh1'
          AND TABLE_NAME = 'PhanCongPhongHoc'
          AND COLUMN_NAME IN ('maPhong', 'maLop', 'namHoc')
        ORDER BY COLUMN_NAME
    ");
    
    echo "   Collation sau khi sửa:\n";
    while ($row = $stmt->fetch()) {
        echo "      - {$row['COLUMN_NAME']}: {$row['COLLATION_NAME']}\n";
    }
    echo "\n";
    
    echo "4. Test query sau khi fix...\n";
    
    // Test the problematic query
    $stmt = $pdo->prepare("
        SELECT 
            p.maPhong,
            p.tenPhong,
            p.sucChua,
            pc.maLop as dangGanChoLop
        FROM PhongHoc p
        LEFT JOIN PhanCongPhongHoc pc ON pc.maPhong = p.maPhong 
            AND pc.namHoc = '2024-2025'
        WHERE (p.trangThai IN ('ACTIVE', 'DANG_SU_DUNG') OR p.trangThai IS NULL)
        ORDER BY p.tenPhong
    ");
    $stmt->execute();
    
    $available = 0;
    $assigned = 0;
    
    echo "   Kết quả query:\n";
    while ($row = $stmt->fetch()) {
        if ($row['dangGanChoLop']) {
            $assigned++;
            echo "      ❌ {$row['maPhong']} - {$row['tenPhong']} (đã gán cho {$row['dangGanChoLop']})\n";
        } else {
            $available++;
            echo "      ✓ {$row['maPhong']} - {$row['tenPhong']} (khả dụng)\n";
        }
    }
    
    echo "\n   Tổng kết:\n";
    echo "      - Phòng khả dụng: $available\n";
    echo "      - Phòng đã gán: $assigned\n";
    echo "\n";
    
    echo "=== MIGRATION HOÀN TẤT ===\n";
    echo "✓ Collation đã được fix. Query có thể chạy không lỗi.\n\n";
    
} catch (PDOException $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    echo "\nChi tiết:\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}

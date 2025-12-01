<?php
session_start();

// Debug: Hiển thị POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h1>POST Data Received:</h1>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test database connection
    require_once __DIR__ . '/../../config/database.php';
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Database Connection Test:</h2>";
    try {
        $test = $db->query("SELECT 1")->fetch();
        echo "✓ Database connected successfully<br>";
        
        // Try to insert
        $maTaiKhoan = $_POST['maTaiKhoan'] ?? 'TEST001';
        $tenDangNhap = $_POST['tenDangNhap'] ?? 'test@example.com';
        $email = $_POST['email'] ?? 'test@example.com';
        $matKhau = password_hash($_POST['matKhau'] ?? 'test123', PASSWORD_DEFAULT);
        
        $query = "INSERT INTO taikhoan (maTaiKhoan, tenDangNhap, matKhau, email, soDienThoai, trangThai, maTruong)
                  VALUES (:maTaiKhoan, :tenDangNhap, :matKhau, :email, '', 'ACTIVE', NULL)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':maTaiKhoan', $maTaiKhoan);
        $stmt->bindParam(':tenDangNhap', $tenDangNhap);
        $stmt->bindParam(':matKhau', $matKhau);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            echo "✓ Account inserted successfully!<br>";
            // Verify by selecting the row we just inserted
            $select = $db->prepare("SELECT * FROM taikhoan WHERE maTaiKhoan = :maTaiKhoan");
            $select->bindParam(':maTaiKhoan', $maTaiKhoan);
            $select->execute();
            $row = $select->fetch(PDO::FETCH_ASSOC);
            echo "<h3>Inserted Row:</h3>";
            echo '<pre>' . print_r($row, true) . '</pre>';
        } else {
            echo "✗ Insert failed (execute returned false).\n";
            $err = $stmt->errorInfo();
            echo "Error info: " . print_r($err, true);
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage();
    }
} else {
    echo "<h1>Test Form</h1>";
    echo '<form method="POST">
        <input type="text" name="maTaiKhoan" placeholder="Mã TK" value="TEST001"><br>
        <input type="text" name="tenDangNhap" placeholder="Tên đăng nhập" value="test@example.com"><br>
        <input type="email" name="email" placeholder="Email" value="test@example.com"><br>
        <input type="password" name="matKhau" placeholder="Mật khẩu" value="test123"><br>
        <input type="checkbox" name="vaiTro[]" value="admin"> Admin<br>
        <button type="submit">Test Create</button>
    </form>';
}
?>

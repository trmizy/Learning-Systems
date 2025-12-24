<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void {
    if (!isset($_SESSION['auth'])) {
        header('Location: /public/index.php');
        exit;
    }
}

function require_role(array $allowed): void {
    require_login();
    $current = $_SESSION['auth']['role'] ?? null;
    
    if (!in_array($current, $allowed, true)) {
        http_response_code(403);
        include __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

function current_user(): ?array {
    return $_SESSION['auth'] ?? null;
}

function has_role(string $role): bool {
    return isset($_SESSION['auth']['roles']) && 
           in_array($role, $_SESSION['auth']['roles'], true);
}

function switch_role(string $role): bool {
    if (has_role($role)) {
        $_SESSION['auth']['role'] = $role;
        session_regenerate_id(true);
        return true;
    }
    return false;
}

require_once __DIR__ . '/../config/database.php';

/**
 * Trả về mã lớp chủ nhiệm của user hiện tại (ví dụ "10A1"), hoặc null nếu không phải GVCN hoặc không tìm thấy.
 */
function getHomeroomClassForCurrentUser() {
	$user = current_user() ?? ($_SESSION['auth'] ?? null);
	if (!$user || empty($user['username'])) return null;

	try {
		$db = Database::getInstance()->getConnection();

		// 1) Lấy maGV từ taikhoan -> giaovienbomon
		$stmt = $db->prepare("
			SELECT gv.maGV
			FROM taikhoan tk
			INNER JOIN giaovienbomon gv ON tk.maTaiKhoan = gv.maTaiKhoan
			WHERE tk.tenDangNhap = :username
			LIMIT 1
		");
		$stmt->execute(['username' => $user['username']]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row || empty($row['maGV'])) {
			error_log("getHomeroomClassForCurrentUser: maGV not found for username=" . $user['username']);
			return null;
		}
		$maGV = $row['maGV'];

		// 2) Lấy lớp chủ nhiệm từ bảng giaovienchunhiem (cột 'lop')
		$stmt2 = $db->prepare("
			SELECT lop
			FROM giaovienchunhiem
			WHERE maGV = :maGV
			LIMIT 1
		");
		$stmt2->execute(['maGV' => $maGV]);
		$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
		if ($row2 && !empty($row2['lop'])) {
			return $row2['lop'];
		}

		// Không tìm thấy lớp chủ nhiệm
		error_log("getHomeroomClassForCurrentUser: no homeroom for maGV={$maGV}");
		return null;

	} catch (PDOException $e) {
		error_log("Error getHomeroomClassForCurrentUser: " . $e->getMessage());
		return null;
	}
}

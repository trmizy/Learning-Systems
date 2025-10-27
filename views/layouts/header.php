<?php
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['auth'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?? 'Hệ thống quản lý trường THPT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/header.css" rel="stylesheet">
    <link href="/assets/css/footer.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/public/index.php">
                <i class="fa-solid fa-school"></i>
                <span class="ms-2">THPT System</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <?php if ($user): ?>
                <!-- Authenticated User Menu -->
                <ul class="navbar-nav me-auto">
                    <?php
                    $role = $user['role'] ?? 'guest';
                    if (isset($MENU_ITEMS[$role])) {
                        foreach ($MENU_ITEMS[$role] as $item) {
                            if (isset($item['submenu'])) {
                                ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                                        <?php echo htmlspecialchars($item['text']); ?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($item['submenu'] as $subitem): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?php echo htmlspecialchars($subitem['link']); ?>">
                                                    <i class="fa-solid <?php echo htmlspecialchars($subitem['icon']); ?>"></i>
                                                    <?php echo htmlspecialchars($subitem['text']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <?php
                            } else {
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo htmlspecialchars($item['link']); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                                        <?php echo htmlspecialchars($item['text']); ?>
                                    </a>
                                </li>
                                <?php
                            }
                        }
                    }
                    
                    // Admin System Menu
                    if ($role === 'admin'):
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear"></i>
                            Hệ thống
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/modules/users/list.php">
                                    <i class="fa-solid fa-users-gear"></i>
                                    Tài khoản & Phân quyền
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/modules/settings/index.php">
                                    <i class="fa-solid fa-sliders"></i>
                                    Cấu hình hệ thống
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/modules/backup/index.php">
                                    <i class="fa-solid fa-database"></i>
                                    Sao lưu & Phục hồi
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- User Info & Logout -->
                <div class="user-actions">
                    <div class="user-info d-none d-lg-flex">
                        <span class="badge bg-primary role-badge">
                            <?php echo htmlspecialchars(getRoleName($user['role'] ?? 'guest')); ?>
                        </span>
                        <span class="fw-semibold">
                            <?php echo htmlspecialchars($user['full_name'] ?? 'Người dùng'); ?>
                        </span>
                    </div>
                    
                    <a href="/public/index.php?action=logout" class="btn btn-outline-danger btn-sm btn-logout" title="Đăng xuất">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="d-none d-sm-inline ms-1">Đăng xuất</span>
                    </a>
                </div>
                
                <?php else: ?>
                <!-- Guest Menu -->
                <ul class="navbar-nav me-auto guest-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/public/index.php">
                            <i class="fa-solid fa-house"></i>
                            Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/modules/about/index.php">
                            <i class="fa-solid fa-circle-info"></i>
                            Giới thiệu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/modules/news/index.php">
                            <i class="fa-solid fa-newspaper"></i>
                            Tin tức
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/modules/contact/index.php">
                            <i class="fa-solid fa-envelope"></i>
                            Liên hệ
                        </a>
                    </li>
                </ul>

                <!-- Auth Buttons -->
                <div class="d-flex align-items-center gap-2 auth-buttons">
                    <a href="/controllers/auth/login.php" class="btn btn-outline-primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Đăng nhập
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">

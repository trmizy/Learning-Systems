<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get and clear flash messages
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - Hệ thống quản lý trường THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body">
                <!-- Logo Section -->
                <div class="auth-logo">
                    <div class="auth-logo-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h4>Đăng nhập</h4>
                    <p>Hệ thống quản lý trường THPT</p>
                </div>

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="post" action="/controllers/auth/login.php" id="loginForm">
                    <!-- Email/Username Input -->
                    <div class="form-floating has-icon">
                        <i class="input-icon fa-solid fa-envelope"></i>
                        <input type="text" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="Email hoặc tên đăng nhập"
                               required
                               autocomplete="username">
                        <label for="email">Email hoặc tên đăng nhập</label>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="form-floating has-icon password-wrapper">
                        <i class="input-icon fa-solid fa-lock"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Mật khẩu"
                               required
                               autocomplete="current-password">
                        <label for="password">Mật khẩu</label>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="remember" 
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-auth-submit">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Đăng nhập
                    </button>

                    <!-- Footer Links -->
                    <div class="auth-footer">
                        <a href="/controllers/auth/forgot-password.php">
                            <i class="fa-solid fa-key me-1"></i>Quên mật khẩu?
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="back-to-home">
            <a href="/views/guest/welcome.php">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Quay lại trang chủ
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form Submission Loading State
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Đang xử lý...</span>';
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

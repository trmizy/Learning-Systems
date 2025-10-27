<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hệ thống Quản lý Học sinh THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/header.css" rel="stylesheet">
    <link href="/assets/css/footer.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow-x: hidden;
        }

        /* Navigation */
        .welcome-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .welcome-navbar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0d6efd, #0dcaf0, #198754, #ffc107);
        }

        .welcome-navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .welcome-navbar .btn-login {
            background: white;
            color: #667eea;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid white;
        }

        .welcome-navbar .btn-login:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        /* Hero Section */
        .hero-section { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6rem 0 8rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: pulse 6s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.6s both;
        }

        .hero-btn {
            padding: 1rem 2.5rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 3px solid white;
            margin: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }

        .hero-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary-hero {
            background: white;
            color: #667eea;
        }

        .btn-primary-hero:hover {
            background: white;
            color: #764ba2;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.4);
        }

        .btn-outline-hero {
            background: transparent;
            color: white;
        }

        .btn-outline-hero:hover {
            background: white;
            color: #667eea;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.4);
        }

        /* Stats Section */
        .stats-section {
            margin-top: -4rem;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 0;
            animation: fadeInUp 0.8s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 48px rgba(102, 126, 234, 0.3);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: white;
            animation: bounce 2s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Features Section */
        .features-section {
            padding: 5rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 1.2rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.4s ease;
            cursor: pointer;
            border: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.25);
        }

        .feature-icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            color: white;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h5 {
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .feature-card p {
            color: #6c757d;
            line-height: 1.8;
        }

        /* Roles Section */
        .roles-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 5rem 0;
        }

        .role-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.25);
        }

        .role-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1.5rem;
            color: white;
        }

        .role-card h5 {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .role-features {
            text-align: left;
            margin-top: 1.5rem;
        }

        .role-features li {
            margin-bottom: 0.75rem;
            color: #6c757d;
            padding-left: 1.5rem;
            position: relative;
        }

        .role-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.1rem;
            }
            .stat-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 767.98px) {
            .hero-title {
                font-size: 2rem;
            }
            .hero-btn {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
                display: block;
                width: 100%;
            }
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark welcome-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-graduation-cap me-2"></i>
                THPT System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#features">Tính năng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#roles">Vai trò</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#about">Giới thiệu</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="/controllers/auth/login.php" class="btn btn-login">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">
                    Hệ thống Quản lý<br>Trường THPT Hiện đại
                </h1>
                <p class="hero-subtitle">
                    Giải pháp toàn diện cho công tác quản lý và điều hành nhà trường<br>
                    Dễ dàng - Hiệu quả - Chuyên nghiệp
                </p>
                <div class="hero-buttons">
                    <a href="/controllers/auth/login.php" class="btn hero-btn btn-primary-hero">
                        <i class="fa-solid fa-rocket me-2"></i>Bắt đầu ngay
                    </a>
                    <a href="#features" class="btn hero-btn btn-outline-hero">
                        <i class="fa-solid fa-circle-info me-2"></i>Tìm hiểu thêm
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon purple mx-auto">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <div class="stat-number">2,500+</div>
                        <div class="stat-label">Học sinh</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon green mx-auto">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div class="stat-number">150+</div>
                        <div class="stat-label">Giáo viên</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon orange mx-auto">
                            <i class="fa-solid fa-school"></i>
                        </div>
                        <div class="stat-number">45+</div>
                        <div class="stat-label">Lớp học</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon blue mx-auto">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Tỷ lệ tốt nghiệp</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Tính năng nổi bật</h2>
            <p class="section-subtitle">Hệ thống quản lý toàn diện với đầy đủ chức năng</p>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <h5 class="text-center">Quản lý Học sinh</h5>
                        <p class="text-center">Hồ sơ, điểm số, hạnh kiểm, xếp loại học lực và các thông tin học tập chi tiết</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fa-solid fa-chalkboard-teacher"></i>
                        </div>
                        <h5 class="text-center">Giáo viên & Giảng dạy</h5>
                        <p class="text-center">Phân công giảng dạy, quản lý điểm, chủ nhiệm và thời khóa biểu</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <h5 class="text-center">Thời khóa biểu</h5>
                        <p class="text-center">Sắp xếp lịch học tự động, quản lý phòng học và giáo viên giảng dạy</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h5 class="text-center">Báo cáo & Thống kê</h5>
                        <p class="text-center">Tổng hợp, phân tích dữ liệu và xuất báo cáo theo nhiều tiêu chí</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h5 class="text-center">Phụ huynh</h5>
                        <p class="text-center">Theo dõi kết quả học tập, hạnh kiểm và giao tiếp với nhà trường</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <h5 class="text-center">Thông báo</h5>
                        <p class="text-center">Gửi thông báo tự động đến học sinh, giáo viên và phụ huynh</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles-section" id="roles">
        <div class="container">
            <h2 class="section-title">Dành cho mọi đối tượng</h2>
            <p class="section-subtitle">Giao diện riêng biệt và tính năng phù hợp cho từng vai trò</p>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card role-card">
                        <div class="role-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <h5>Quản trị viên</h5>
                        <ul class="role-features">
                            <li>Quản lý toàn bộ hệ thống</li>
                            <li>Phân quyền người dùng</li>
                            <li>Xem báo cáo tổng hợp</li>
                            <li>Cấu hình hệ thống</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card role-card">
                        <div class="role-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h5>Giáo viên</h5>
                        <ul class="role-features">
                            <li>Quản lý điểm số</li>
                            <li>Xem thời khóa biểu</li>
                            <li>Quản lý lớp chủ nhiệm</li>
                            <li>Gửi thông báo</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card role-card">
                        <div class="role-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <h5>Học sinh</h5>
                        <ul class="role-features">
                            <li>Xem điểm và xếp loại</li>
                            <li>Kiểm tra thời khóa biểu</li>
                            <li>Gửi đơn xin nghỉ</li>
                            <li>Nhận thông báo</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card role-card">
                        <div class="role-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h5>Phụ huynh</h5>
                        <ul class="role-features">
                            <li>Theo dõi học tập con</li>
                            <li>Xem điểm và hạnh kiểm</li>
                            <li>Liên hệ giáo viên</li>
                            <li>Nhận thông báo</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">
                    <i class="fa-solid fa-rocket me-3"></i>
                    Sẵn sàng bắt đầu?
                </h2>
                <p class="cta-subtitle">
                    Đăng nhập ngay để trải nghiệm hệ thống quản lý hiện đại và hiệu quả
                </p>
                <div>
                    <a href="/controllers/auth/login.php" class="btn hero-btn btn-primary-hero">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .role-card, .stat-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>

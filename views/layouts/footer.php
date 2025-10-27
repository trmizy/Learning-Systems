</div> <!-- Close container from header -->

    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-lg-3 col-md-6 footer-section">
                    <h5><i class="fa-solid fa-school me-2"></i>Trường THPT</h5>
                    <p class="footer-text">
                        Hệ thống quản lý trường THPT hiện đại, chuyên nghiệp và hiệu quả.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link facebook" title="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link youtube" title="Youtube">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                        <a href="#" class="social-link zalo" title="Zalo">
                            <i class="fa-solid fa-z"></i>
                        </a>
                        <a href="mailto:contact@truongthpt.edu.vn" class="social-link email" title="Email">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        <a href="tel:02812345678" class="social-link phone" title="Hotline">
                            <i class="fa-solid fa-phone"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6 footer-section">
                    <h5><i class="fa-solid fa-link me-2"></i>Liên kết nhanh</h5>
                    <ul class="footer-links">
                        <li><a href="/public/index.php"><i class="fa-solid fa-chevron-right"></i>Trang chủ</a></li>
                        <li><a href="/modules/about/index.php"><i class="fa-solid fa-chevron-right"></i>Giới thiệu</a></li>
                        <li><a href="/modules/news/index.php"><i class="fa-solid fa-chevron-right"></i>Tin tức</a></li>
                        <li><a href="/modules/contact/index.php"><i class="fa-solid fa-chevron-right"></i>Liên hệ</a></li>
                        <li><a href="/modules/support/index.php"><i class="fa-solid fa-chevron-right"></i>Hỗ trợ</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6 footer-section">
                    <h5><i class="fa-solid fa-address-book me-2"></i>Thông tin liên hệ</h5>
                    <ul class="footer-contact">
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>123 Đường ABC, Quận XYZ, TP.HCM</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <a href="tel:02812345678">(028) 1234-5678</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:contact@truongthpt.edu.vn">contact@truongthpt.edu.vn</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-clock"></i>
                            <span>T2 - T6: 7:00 - 17:00</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter & Stats -->
                <div class="col-lg-3 col-md-6 footer-section">
                    <h5><i class="fa-solid fa-newspaper me-2"></i>Đăng ký nhận tin</h5>
                    <p class="footer-text">
                        Nhận thông tin mới nhất từ nhà trường
                    </p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email của bạn" required>
                            <button class="btn" type="submit">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div class="footer-stats mt-3">
                        <div class="stat-item">
                            <i class="fa-solid fa-users"></i>
                            <span class="stat-number">2,500+</span>
                            <span class="stat-label">Học sinh</span>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span class="stat-number">150+</span>
                            <span class="stat-label">Giáo viên</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> <a href="/public/index.php">Hệ thống quản lý trường THPT</a>. 
                        Phát triển bởi <strong>Nhóm CNTT</strong>
                    </p>
                    <ul class="footer-bottom-links">
                        <li><a href="/modules/privacy/index.php">Chính sách bảo mật</a></li>
                        <li><a href="/modules/terms/index.php">Điều khoản sử dụng</a></li>
                        <li><a href="/modules/sitemap/index.php">Sơ đồ trang</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" title="Lên đầu trang">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Back to Top Button
        const backToTopBtn = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Newsletter Form
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const email = newsletterForm.querySelector('input[type="email"]').value;
                alert('Cảm ơn bạn đã đăng ký! Email: ' + email);
                newsletterForm.reset();
            });
        }
    </script>
</body>
</html>

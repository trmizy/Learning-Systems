<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4"><i class="fa-solid fa-circle-question me-2"></i>Hỗ Trợ</h1>

                    <p class="lead">Trang Hỗ Trợ cung cấp các câu trả lời cho những câu hỏi thường gặp và hướng dẫn sử dụng hệ thống.</p>

                    <div class="accordion mb-5" id="supportAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Làm cách nào để đăng nhập vào hệ thống?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    <p>Để đăng nhập vào hệ thống Learning System:</p>
                                    <ol>
                                        <li>Truy cập trang đăng nhập tại /public/index.php</li>
                                        <li>Nhập tên đăng nhập hoặc email của bạn</li>
                                        <li>Nhập mật khẩu của bạn</li>
                                        <li>Nhấp nút "Đăng nhập"</li>
                                    </ol>
                                    <p>Nếu quên mật khẩu, hãy nhấp vào liên kết "Quên mật khẩu?" để đặt lại.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Tôi quên mật khẩu, tôi phải làm gì?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    <p>Nếu bạn quên mật khẩu:</p>
                                    <ol>
                                        <li>Truy cập trang đăng nhập</li>
                                        <li>Nhấp vào liên kết "Quên mật khẩu?"</li>
                                        <li>Nhập email hoặc tên đăng nhập của bạn</li>
                                        <li>Kiểm tra hộp thư email để nhận liên kết đặt lại mật khẩu</li>
                                        <li>Nhấp vào liên kết và tạo mật khẩu mới</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Làm cách nào để cập nhật hồ sơ của tôi?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    <p>Để cập nhật hồ sơ:</p>
                                    <ol>
                                        <li>Đăng nhập vào hệ thống</li>
                                        <li>Truy cập mục "Hồ sơ" hoặc "Tài khoản"</li>
                                        <li>Nhấp nút "Chỉnh sửa" hoặc "Edit"</li>
                                        <li>Cập nhật thông tin cần thiết</li>
                                        <li>Nhấp "Lưu" để lưu thay đổi</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Hệ thống hỗ trợ những trình duyệt nào?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    <p>Learning System được hỗ trợ tốt nhất trên:</p>
                                    <ul>
                                        <li>Google Chrome phiên bản mới nhất</li>
                                        <li>Mozilla Firefox phiên bản mới nhất</li>
                                        <li>Safari phiên bản mới nhất</li>
                                        <li>Microsoft Edge phiên bản mới nhất</li>
                                    </ul>
                                    <p>Để có trải nghiệm tốt nhất, hãy cập nhật trình duyệt của bạn lên phiên bản mới nhất.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Tôi gặp lỗi, tôi phải liên hệ ai?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    <p>Nếu gặp bất kỳ lỗi nào:</p>
                                    <ol>
                                        <li>Hãy thử làm mới trang (F5 hoặc Ctrl+R)</li>
                                        <li>Xóa cache trình duyệt và thử lại</li>
                                        <li>Thử sử dụng trình duyệt khác</li>
                                        <li>Nếu vẫn gặp lỗi, hãy liên hệ với nhóm CNTT qua email: <a href="mailto:support@truongthpt.edu.vn">support@truongthpt.edu.vn</a></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa-solid fa-headset me-2"></i>Liên hệ hỗ trợ</h5>
                            <p>Nếu bạn có câu hỏi khác hoặc cần hỗ trợ thêm, hãy liên hệ:</p>
                            <ul class="list-unstyled">
                                <li><strong>Email:</strong> <a href="mailto:support@truongthpt.edu.vn">support@truongthpt.edu.vn</a></li>
                                <li><strong>Điện thoại:</strong> <a href="tel:02812345678">(028) 1234-5678</a></li>
                                <li><strong>Giờ làm việc:</strong> Thứ 2 - Thứ 6: 7:00 - 17:00</li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5><i class="fa-solid fa-file-lines me-2"></i>Tài liệu hữu ích</h5>
                            <ul>
                                <li><a href="#" class="text-decoration-none">Hướng dẫn sử dụng hệ thống (PDF)</a></li>
                                <li><a href="#" class="text-decoration-none">Video hướng dẫn đăng nhập</a></li>
                                <li><a href="#" class="text-decoration-none">Câu hỏi thường gặp mở rộng</a></li>
                                <li><a href="/modules/about/index.php" class="text-decoration-none">Về Learning System</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

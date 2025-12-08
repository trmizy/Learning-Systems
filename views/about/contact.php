<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4"><i class="fa-solid fa-envelope me-2"></i>Liên Hệ</h1>

                    <div class="row mb-5">
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin liên hệ</h5>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <strong><i class="fa-solid fa-map-marker-alt me-2"></i>Địa chỉ:</strong><br>
                                    123 Đường ABC, Quận XYZ, TP.HCM
                                </li>
                                <li class="mb-3">
                                    <strong><i class="fa-solid fa-phone me-2"></i>Điện thoại:</strong><br>
                                    <a href="tel:02812345678">(028) 1234-5678</a>
                                </li>
                                <li class="mb-3">
                                    <strong><i class="fa-solid fa-envelope me-2"></i>Email:</strong><br>
                                    <a href="mailto:contact@truongthpt.edu.vn">contact@truongthpt.edu.vn</a>
                                </li>
                                <li class="mb-3">
                                    <strong><i class="fa-solid fa-clock me-2"></i>Giờ làm việc:</strong><br>
                                    Thứ 2 - Thứ 6: 7:00 - 17:00
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">Gửi tin nhắn cho chúng tôi</h5>
                            <form>
                                <div class="mb-3">
                                    <label for="contactName" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="contactName" placeholder="Nhập tên của bạn" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="contactEmail" placeholder="Nhập email của bạn" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactPhone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="contactPhone" placeholder="Nhập số điện thoại">
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Nội dung tin nhắn</label>
                                    <textarea class="form-control" id="contactMessage" rows="4" placeholder="Nhập nội dung tin nhắn của bạn" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Gửi tin nhắn
                                </button>
                            </form>
                        </div>
                    </div>

                    <hr>

                    <div class="mt-4">
                        <h5 class="mb-3">Bản đồ vị trí</h5>
                        <div class="alert alert-info" role="alert">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            <strong>Ghi chú:</strong> Bản đồ Google Maps sẽ được nhúng tại đây. Hãy liên hệ quản trị viên để cấu hình chi tiết.
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

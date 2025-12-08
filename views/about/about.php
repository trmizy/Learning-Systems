<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-3"><i class="fa-solid fa-info-circle me-2"></i>Giới Thiệu</h1>

                    <p class="lead">"Learning System" là một dự án web quản lý học sinh được phát triển nhằm mục đích hỗ trợ hoạt động quản trị, giảng dạy và theo dõi kết quả học tập tại các trường THPT. Dự án cung cấp các chức năng quản lý tài khoản, phân quyền, quản lý hồ sơ học sinh, tổ hợp môn, phê duyệt, và nhiều tiện ích khác để giúp nhà trường vận hành hiệu quả hơn.</p>

                    <h5 class="mt-4">Mục tiêu chính</h5>
                    <ul>
                        <li>Hỗ trợ quản lý học sinh, giáo viên và nhân sự trường học.</li>
                        <li>Tối ưu hóa quy trình phê duyệt, phân công và báo cáo.</li>
                        <li>Cung cấp giao diện thân thiện, dễ sử dụng cho các vai trò: quản trị, ban giám hiệu, giáo viên và học sinh.</li>
                    </ul>

                    <h5 class="mt-4">Ghi chú quan trọng</h5>
                    <div class="alert alert-warning" role="alert">
                        <strong>Lưu ý:</strong> Đây là trang web phục vụ mục đích học tập, không phục vụ cho thương mại.
                    </div>

                    <p class="text-muted small">Phiên bản hiện tại là bản demo / mẫu để học tập và phát triển. Nếu bạn muốn triển khai thực tế cho môi trường sản xuất, hãy cân nhắc các yêu cầu bảo mật, sao lưu, và tuân thủ pháp lý trước khi đưa vào sử dụng.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

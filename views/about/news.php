<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-3"><i class="fa-solid fa-newspaper me-2"></i>Tin Tức</h1>

                    <p class="lead">Trang Tin Tức sẽ hiển thị các thông báo và bản tin liên quan đến hoạt động nhà trường và dự án. Dưới đây là một số tin mẫu để minh họa.</p>

                    <style>
                        .news-card { 
                            overflow: hidden; 
                            margin-bottom: 2rem; 
                            border-radius: 8px; 
                            box-shadow: 0 6px 18px rgba(0,0,0,0.06); 
                        }
                        .news-image { 
                            float: left; 
                            width: 50%; 
                            min-height: 300px; 
                            background-size: cover; 
                            background-position: center; 
                        }
                        .news-content { 
                            float: right; 
                            width: 50%; 
                            padding: 2rem; 
                            box-sizing: border-box; 
                        }
                        .news-content h3 { 
                            margin-top: 0; 
                            color: #333;
                        }
                        @media (max-width: 767.98px) {
                            .news-image, .news-content { 
                                float: none; 
                                width: 100%; 
                            }
                            .news-image { 
                                min-height: 180px; 
                            }
                        }
                    </style>

                    <div class="news-card">
                        <div class="news-image" style="background-image: url('/assets/images/news/news1.svg');"></div>
                        <div class="news-content">
                            <h3>Bản tin 1: Khai giảng năm học mới</h3>
                            <p class="text-muted">Ngày 01/09/2025: Lễ khai giảng diễn ra thành công tại trường THPT mẫu.</p>
                            <p>Buổi lễ khai giảng được tổ chức trang trọng với sự tham dự của toàn bộ giáo viên, học sinh và khách mời đại diện nhà trường.</p>
                        </div>
                    </div>

                    <div class="news-card">
                        <div class="news-image" style="background-image: url('/assets/images/news/news2.svg');"></div>
                        <div class="news-content">
                            <h3>Bản tin 2: Cập nhật hệ thống</h3>
                            <p class="text-muted">Ngày 15/10/2025: Hệ thống được cập nhật phiên bản mới, cải thiện giao diện người dùng.</p>
                            <p>Phiên bản mới bao gồm tối ưu hiệu năng, sửa lỗi và nâng cấp trải nghiệm cho giáo viên và học sinh.</p>
                        </div>
                    </div>

                    <div class="news-card">
                        <div class="news-image" style="background-image: url('/assets/images/news/news3.svg');"></div>
                        <div class="news-content">
                            <h3>Bản tin 3: Thông báo nội bộ</h3>
                            <p class="text-muted">Ngày 20/11/2025: Thông báo về lịch họp giao ban toàn trường.</p>
                            <p>Cuộc họp giao ban sẽ nhằm đánh giá kết quả học tập và triển khai kế hoạch cho học kỳ tiếp theo.</p>
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <strong>Ghi chú:</strong> Trang Tin Tức hiện đang là bản mẫu. Nội dung thực tế sẽ được quản trị viên cập nhật từ giao diện quản trị.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>

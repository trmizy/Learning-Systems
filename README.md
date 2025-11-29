# 📝 CHỨC NĂNG ĐĂNG KÝ NGUYỆN VỌNG
---
## Tổng quan
### Mô tả
Chức năng **Đăng ký nguyện vọng** cho phép Phụ huynh đăng ký các trường học mong muốn cho con em mình tham gia vào hệ thống tuyển sinh.
### Mục đích
- Đăng ký thông tin thí sinh (học sinh dự thi)
- Chọn tối đa 3 trường theo thứ tự ưu tiên
- Tự động tạo mã thí sinh và mã nguyện vọng
- Cho phép chỉnh sửa nguyện vọng đã đăng ký
- Xem lại thông tin sau khi đăng ký thành công
### Đối tượng sử dụng
- **Phụ huynh (ph)**: Người duy nhất có quyền đăng ký nguyện vọng cho con em
### Đặc điểm nổi bật
- ✅ **Tự động tạo mã**: Mã thí sinh (TSYYZZZZZZXX), mã nguyện vọng (NV000001)
- ✅ **Validation chặt chẽ**: CCCD 12 số, SĐT 10 số, tối đa 3 nguyện vọng
- ✅ **Chống trùng lặp**: Không cho chọn trùng trường, trùng thứ tự ưu tiên
- ✅ **Cập nhật thông minh**: Nếu đã đăng ký → Cập nhật + Xóa nguyện vọng cũ
- ✅ **Transaction an toàn**: Rollback khi có lỗi
---
##  Yêu cầu hệ thống
### Công nghệ sử dụng
- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS**: Bootstrap 5
- **Icons**: Font Awesome 6
---
## Cấu trúc chức năng
### Sơ đồ thư mục
```
Learning-Systems/
│
├── views/
│   └── ph/
│       └── wishRegistrationView.php         # Giao diện đăng ký
│
├── controllers/
│   └── ph/
│       └── wishRegistrationController.php   # Controller xử lý
│
├── models/
│   └── WishRegistrationModel.php            # Model nguyện vọng
│
├── assets/
│   └── css/
│       └── wishRegistration.css             # Style riêng
│
└── middlewares/
    └── AuthGuard.php                        # Kiểm tra quyền
```
### Các file chính
#### 1. **wishRegistrationView.php** (View)
- Form nhập thông tin thí sinh:
  - Họ tên (3+ ký tự)
  - Số CCCD (12 số)
  - Số điện thoại (10 số, bắt đầu 0)
- Form chọn nguyện vọng (1-3 nguyện vọng):
  - Dropdown chọn trường
  - Input thứ tự ưu tiên (1, 2, 3)
  - Nút thêm/xóa nguyện vọng
- Card hiển thị kết quả đăng ký
#### 2. **wishRegistrationController.php** (Controller)
- Kiểm tra quyền phụ huynh
- Lấy danh sách trường từ database
- Xử lý POST: Đăng ký nguyện vọng
- Xử lý kết quả: Thành công/Lỗi
- Redirect sau khi submit
#### 3. **WishRegistrationModel.php** (Model)
- `getDanhSachTruong()`: Lấy danh sách trường
- `validateThiSinh()`: Validate thông tin thí sinh
- `validateNguyenVong()`: Validate danh sách nguyện vọng
- `kiemTraThiSinhTonTai()`: Kiểm tra CCCD đã tồn tại
- `taoMaThiSinh()`: Tạo mã thí sinh tự động
- `taoMaNguyenVong()`: Tạo mã nguyện vọng tự động
- `dangKyNguyenVong()`: Xử lý đăng ký
- `getDanhSachNguyenVong()`: Lấy nguyện vọng đã đăng ký
---
## Hướng dẫn sử dụng

### 1. Truy cập chức năng
- **Bước 1**: Đăng nhập bằng tài khoản Phụ huynh
- **Bước 2**: Vào menu **"Đăng ký nguyện vọng"**

### 2. Nhập thông tin thí sinh (quy tắc)
- **Họ tên**:
```
- Độ dài: Tối thiểu 3 ký tự
- Ví dụ hợp lệ: "Nguyễn Văn An", "Trần Thị Bích"
- Ví dụ không hợp lệ: "An" (< 3 ký tự)
```
- **Số CCCD**:
```
- Format: 12 chữ số (không có ký tự đặc biệt)
- Ví dụ hợp lệ: "001234567890"
- Ví dụ không hợp lệ: "12345678" (< 12 số), "123-456-789" (có dấu -)
- Regex: /^\d{12}$/
```
- **Số điện thoại**:
```
- Format: 10 chữ số, bắt đầu bằng 0
- Ví dụ hợp lệ: "0912345678", "0987654321"
- Ví dụ không hợp lệ: "912345678" (không bắt đầu 0), "09123456789" (11 số)
- Regex: /^0\d{9}$/
```

### 3. Chọn nguyện vọng
- Chọn 1–3 trường, mỗi trường chỉ được chọn một lần; nhập thứ tự ưu tiên (1..3).

### 4. Thao tác với nguyện vọng
- **Thêm nguyện vọng**: Chọn trường và nút "Thêm" (tối đa 3).
- **Xóa nguyện vọng**: Bấm nút xóa bên cạnh nguyện vọng cần loại bỏ.

---
## Hai kịch bản

### Trường hợp A — Phụ huynh ĐÃ liên kết với học sinh

- Mô tả: Tài khoản phụ huynh đã có bản ghi liên kết trong bảng `phuhuynh_hocsinh` (phụ huynh đã có con đang học THPT trong hệ thống).
- Hành vi hệ thống:
  1. Khi phụ huynh bấm **"Đăng ký ngay"**, controller sẽ phát hiện liên kết và KHÔNG cho vào form đăng ký.
  2. Controller đặt `$_SESSION['messages'][] = ['type' => 'info', 'text' => 'Con em đang học thpt']` và redirect về `views/ph/dashboard.php`.
  3. Dashboard sẽ hiển thị thông báo đó (alert màu đỏ theo cấu hình hiện tại).
- Kết quả mong đợi khi kiểm thử:
  - Sau khi nhấn "Đăng ký ngay" bạn được trả về dashboard và thấy alert: **"Con em đang học thpt"**.

### Trường hợp B — Phụ huynh CHƯA liên kết với học sinh

- Mô tả: Tài khoản phụ huynh chưa có bản ghi trong `phuhuynh_hocsinh`.
- Hành vi hệ thống:
  1. Khi bấm **"Đăng ký ngay"**, controller cho phép tiếp tục và hiển thị form đăng ký nguyện vọng.
  2. Người dùng nhập thông tin thí sinh, chọn nguyện vọng và submit; hệ thống xử lý như luồng đăng ký bình thường (xem phần Luồng xử lý bên dưới).
- Kết quả mong đợi khi kiểm thử:
  - Form đăng ký hiển thị và sau khi submit, dữ liệu được validate và lưu vào DB.

---

### 5. Luồng xử lý khi thực hiện đăng ký (áp dụng cho Trường hợp B)
```php
// Luồng xử lý
1. Validate thông tin thí sinh
   - Họ tên >= 3 ký tự
   - CCCD = 12 số
   - SĐT = 10 số, bắt đầu 0
2. Validate nguyện vọng
   - Số lượng 1-3
   - Không trùng trường
   - Thứ tự ưu tiên 1-3, không trùng
3. Kiểm tra thí sinh đã tồn tại (theo CCCD)
   - Đã tồn tại → UPDATE thông tin + XÓA nguyện vọng cũ
   - Chưa tồn tại → INSERT thí sinh mới
4. Tạo mã tự động
   - Mã thí sinh: TSYYZZZZZZXX (nếu mới)
   - Mã nguyện vọng: NV000001, NV000002, ...
5. Lưu nguyện vọng mới
6. Commit transaction
```
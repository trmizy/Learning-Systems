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
**Bước 1**: Đăng nhập với tài khoản Phụ huynh
**Bước 2**: Vào menu "Đăng ký nguyện vọng"
### 2. Nhập thông tin thí sinh
**Quy tắc nhập**:
**Họ tên**:
```
- Độ dài: Tối thiểu 3 ký tự
- Ví dụ hợp lệ: "Nguyễn Văn An", "Trần Thị Bích"
- Ví dụ không hợp lệ: "An" (< 3 ký tự)
```
**Số CCCD**:
```
- Format: 12 chữ số (không có ký tự đặc biệt)
- Ví dụ hợp lệ: "001234567890"
- Ví dụ không hợp lệ: "12345678" (< 12 số), "123-456-789" (có dấu -)
- Regex: /^\d{12}$/
```
**Số điện thoại**:
```
- Format: 10 chữ số, bắt đầu bằng 0
- Ví dụ hợp lệ: "0912345678", "0987654321"
- Ví dụ không hợp lệ: "912345678" (không bắt đầu 0), "09123456789" (11 số)
- Regex: /^0\d{9}$/
```
### 3. Chọn nguyện vọng
### 4. Thao tác với nguyện vọng
**Thêm nguyện vọng**:
**Xóa nguyện vọng**:
### 5. Đăng ký
**Bước 1**: Nhập đầy đủ thông tin thí sinh và chọn nguyện vọng
**Bước 2**: Nhấn nút "📝 Đăng ký nguyện vọng"
**Bước 3**: Hệ thống xử lý
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
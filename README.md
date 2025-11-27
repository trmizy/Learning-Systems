# 📚 CHỨC NĂNG CẤP TÀI KHOẢN CHO TRƯỜNG
## Tổng quan
### Mô tả
Chức năng **Cấp tài khoản cho trường** cho phép Nhân viên Sở Giáo dục quản lý và tạo tài khoản đăng nhập cho các trường THPT trong hệ thống.
### Mục đích
- Tạo tài khoản đăng nhập cho các trường THPT chưa có tài khoản
- Quản lý danh sách tài khoản đã cấp
- Xóa tài khoản khi cần thiết
- Đảm bảo tính bảo mật với mật khẩu được mã hóa
### Đối tượng sử dụng
- **Nhân viên Sở (nhanvienso)**: Người duy nhất có quyền cấp và quản lý tài khoản trường
---
## Yêu cầu hệ thống
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
│   └── nhanvienso/
│       └── schoolAccountView.php          # Giao diện chính
│
├── controllers/
│   └── nhanvienso/
│       └── schoolAccountController.php    # Xử lý logic
│
├── models/
│   └── SchoolAccountModel.php             # Tương tác database
│
├── assets/
│   └── css/
│       └── schoolAccount.css              # Style riêng
│
└── middlewares/
    └── AuthGuard.php                      # Kiểm tra quyền truy cập
```
### Các file chính
#### 1. **schoolAccountView.php** (View)
- Hiển thị bảng danh sách tất cả các trường với trạng thái tài khoản
- Hiển thị thông tin tài khoản vừa tạo (nếu có)
- Nút "Cấp tài khoản" cho trường chưa có tài khoản
- Nút "Xóa tài khoản" cho trường đã có tài khoản
- Modal xác nhận xóa tài khoản
#### 2. **schoolAccountController.php** (Controller)
- Xử lý action `create_account`: Tạo tài khoản tự động
- Xử lý action `delete_account`: Xóa tài khoản trường
- Lấy thông tin nhân viên sở đang thực hiện
- Gọi Model và xử lý kết quả
- Gửi email thông tin tài khoản
#### 3. **SchoolAccountModel.php** (Model)
- `getDanhSachTruongVaTrangThaiTaiKhoan()`: Lấy tất cả trường từ view
- `taoTaiKhoanChoTruong()`: Tự động tạo tài khoản với mật khẩu mặc định
- `xoaTaiKhoanTruong()`: Xóa tài khoản trường (vai trò admin)
- `guiEmailThongTinTaiKhoan()`: Gửi email thông tin đăng nhập
- Tạo mã tài khoản và mã nhân viên tự động
---
##  Hướng dẫn sử dụng
### 1. Truy cập chức năng
**Bước 1**: Đăng nhập với tài khoản Nhân viên Sở
**Bước 2**: Vào menu "Cấp tài khoản cho trường"
### 2. Cấp tài khoản mới
#### Giao diện
Hiển thị bảng danh sách tất cả các trường với trạng thái tài khoản
#### Các bước thực hiện
**Bước 1**: Chọn trường cần cấp tài khoản
- Xem danh sách trường trong bảng
- Kiểm tra cột "Trạng thái tài khoản":
  - ⚠️ **Chưa cấp**: Có thể cấp tài khoản
  - ✅ **Đã cấp**: Đã có tài khoản (không thể cấp lại)
- Kiểm tra cột "Email": Trường phải có email mới cấp được
**Bước 2**: Nhấn nút "Cấp tài khoản"
- Chỉ hiển thị nút này cho trường **chưa cấp** và **có email**
- Hệ thống tự động tạo:
  - **Tên đăng nhập**: Sử dụng email của trường
  - **Mật khẩu mặc định**: `1111` (trường cần đổi sau lần đầu đăng nhập)
  - **Mã tài khoản**: Format `TKGVUXXX` (ví dụ: TKGVU001)
  - **Mã nhân viên giáo vụ**: Format `TRXXXNVYYZZZZ` (ví dụ: TR001NV250001)
  - **Vai trò**: `admin` (tài khoản quản trị trường)
**Bước 3**: Hệ thống xử lý tự động
- ✅ Kiểm tra trường đã có tài khoản chưa
- ✅ Kiểm tra email có tồn tại không
- ✅ Tạo tài khoản trong bảng `taikhoan`
- ✅ Tạo nhân viên phòng giáo vụ trong bảng `nhanvienphonggiaovu`
- ✅ Gán vai trò `admin` trong bảng `taikhoan_vaitro`
- ✅ Gửi email thông tin tài khoản đến trường
- ✅ Hiển thị thông tin tài khoản vừa tạo trên màn hình
### 3. Xem danh sách trường và trạng thái tài khoản
#### Thông tin hiển thị trong bảng
```
- Mã trường (ví dụ: TR001)
- Tên trường
- Địa chỉ
- Email
- Số điện thoại
- Trạng thái Email (Đủ thông tin / Thiếu email)
- Trạng thái tài khoản (Đã cấp / Chưa cấp)
- Thao tác (Cấp tài khoản / Xóa tài khoản)
```
#### Các trạng thái
- ✅ **Đã cấp**: Tài khoản đã được tạo thành công
  - Hiển thị badge xanh
  - Có nút "Xóa tài khoản"
- ⚠️ **Chưa cấp**: Trường chưa có tài khoản
  - Hiển thị badge vàng
  - Có nút "Cấp tài khoản" (nếu có email)
- 📧 **Đủ thông tin** / **Thiếu email**: Trạng thái email
  - Đủ thông tin: có thể cấp tài khoản
  - Thiếu email: không thể cấp (cần bổ sung email trước)
### 4. Xóa tài khoản
**Bước 1**: Nhấn nút "Xóa tài khoản" trên bảng danh sách
- Chỉ hiển thị cho trường đã có tài khoản (trạng thái "Đã cấp")
**Bước 2**: Xác nhận trong Modal
**Bước 3**: Xác nhận xóa
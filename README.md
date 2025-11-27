# 📝 CHỨC NĂNG NHẬP ĐIỂM
---
##  Tổng quan
### Mô tả
Chức năng **Nhập điểm** cho phép Giáo viên bộ môn nhập và quản lý điểm số của học sinh cho các lớp được phân công giảng dạy.
### Mục đích
- Nhập điểm thường xuyên, giữa kỳ, cuối kỳ cho học sinh
- Tự động tính điểm trung bình theo công thức
- Cập nhật điểm đã nhập trước đó
- Xem bảng điểm theo lớp và môn học
- Lọc theo năm học và học kỳ
### Đối tượng sử dụng
- **Giáo viên bộ môn (gvbm)**: Người duy nhất có quyền nhập điểm cho các lớp được phân công
### Đặc điểm nổi bật
- ✅ **Tính điểm tự động**: JavaScript tính điểm trung bình real-time
- ✅ **Cập nhật thông minh**: Chỉ cập nhật các cột điểm có thay đổi
- ✅ **Phân quyền chặt chẽ**: Chỉ nhập điểm cho lớp được phân công
- ✅ **Validation điểm**: Kiểm tra điểm từ 0-10
- ✅ **Lưu hàng loạt**: Lưu điểm cả lớp trong một lần submit
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
│   └── gvbm/
│       └── enterPointsView.php           # Giao diện nhập điểm
│
├── controllers/
│   └── gvbm/
│       └── enterPointsController.php     # Controller xử lý
│
├── models/
│   └── enterPointsModel.php              # Model điểm số
│
├── assets/
│   └── css/
│       └── nhapdiem.css                  # Style riêng
│
└── middlewares/
    └── AuthGuard.php                     # Kiểm tra quyền
```
### Các file chính
#### 1. **enterPointsView.php** (View)
- Dropdown chọn năm học, học kỳ
- Dropdown chọn lớp và môn học được phân công
- Bảng nhập điểm với các cột:
  - STT
  - Họ tên học sinh
  - Điểm thường xuyên
  - Điểm giữa kỳ
  - Điểm cuối kỳ
  - Điểm trung bình (tự động tính)
- JavaScript tính điểm real-time
#### 2. **enterPointsController.php** (Controller)
- Lấy mã giáo viên từ session
- Lấy danh sách lớp được phân công
- Lấy bảng điểm hiện tại
- Xử lý POST: Lưu nhiều điểm cùng lúc
- Redirect sau khi lưu thành công
#### 3. **enterPointsModel.php** (Model)
- `getDanhSachLopPhanCong()`: Lấy lớp được phân công
- `getBangDiem()`: Lấy bảng điểm của lớp
- `validateDiem()`: Kiểm tra điểm hợp lệ (0-10)
- `luuDiem()`: Lưu/cập nhật điểm một học sinh
- `luuNhieuDiem()`: Lưu điểm cả lớp
- `generateMaBangDiem()`: Tạo mã bảng điểm tự động
---
##  Hướng dẫn sử dụng
### 1. Truy cập chức năng
**Bước 1**: Đăng nhập với tài khoản Giáo viên bộ môn
**Bước 2**: Vào menu "Nhập điểm"
### 2. Chọn năm học và học kỳ
### 3. Chọn lớp và môn học
**Dropdown "Chọn lớp và môn học"**
**Thông tin hiển thị sau khi chọn**
### 4. Nhập điểm
**Nhập điểm**:
```javascript
// Input điểm
- Type: number
- Step: 0.5 (cho phép nhập 7.5, 8.5, etc.)
- Min: 0
- Max: 10
- Placeholder: "0-10"
// Validation
- Điểm phải từ 0 đến 10
- Có thể để trống (chưa nhập)
- Tự động tính điểm TB khi nhập
```
---
## Công thức tính điểm
### Công thức điểm trung bình

```
Điểm Trung Bình = (Điểm TX + Điểm GK × 2 + Điểm CK × 3) / 6
```
**Trong đó**:
- **Điểm TX**: Điểm thường xuyên (hệ số 1)
- **Điểm GK**: Điểm giữa kỳ (hệ số 2)
- **Điểm CK**: Điểm cuối kỳ (hệ số 3)
**Điều kiện tính**:
- Phải có đủ cả 3 loại điểm
- Nếu thiếu một loại → Không tính điểm TB
### Ví dụ tính điểm
**Ví dụ 1**: Học sinh đầy đủ điểm
```
Input:
- Điểm TX: 8
- Điểm GK: 7
- Điểm CK: 9
Tính:
DTB = (8 + 7×2 + 9×3) / 6
    = (8 + 14 + 27) / 6
    = 49 / 6
    = 8.166...
Kết quả: 8.2 (làm tròn 1 chữ số)
```
**Ví dụ 2**: Học sinh thiếu điểm
```
Input:
- Điểm TX: 8
- Điểm GK: 7
- Điểm CK: (chưa nhập)
Kết quả: (không tính) - Hiển thị trống
```
**Ví dụ 3**: Điểm tối đa
```
Input:
- Điểm TX: 10
- Điểm GK: 10
- Điểm CK: 10
Tính:
DTB = (10 + 10×2 + 10×3) / 6
    = (10 + 20 + 30) / 6
    = 60 / 6
    = 10.0
Kết quả: 10.0
```
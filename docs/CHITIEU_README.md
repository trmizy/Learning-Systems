# Chức năng: Phân bổ chỉ tiêu tuyển sinh

## 📋 Mô tả
Chức năng cho phép **Nhân viên Sở Giáo dục** phân bổ chỉ tiêu tuyển sinh cho các trường THPT trong hệ thống theo từng năm học.

## 🎯 Use Case
**Actor chính:** Nhân viên Sở  
**Actor phụ:** Không

### Tiền điều kiện
- Nhân viên Sở đăng nhập hệ thống thành công
- Danh sách các trường THPT đã có trong hệ thống
- Tổng chỉ tiêu tuyển sinh của năm học đã cập nhật trong hệ thống

### Hậu điều kiện
- Số lượng chỉ tiêu tuyển sinh của các trường được lưu vào CSDL
- Gửi thông báo về các trường về chỉ tiêu phân bổ

## 🏗️ Cấu trúc MVC

### 1. Model
**File:** `models/ChiTieuTuyenSinh.php`

Các phương thức chính:
- `getDanhSachNamHoc()` - Lấy danh sách năm học
- `getDanhSachTruong()` - Lấy danh sách các trường THPT
- `getChiTieuTheoNamHoc($namHoc)` - Lấy chỉ tiêu đã phân bổ
- `getTongChiTieuNamHoc($namHoc)` - Lấy tổng chỉ tiêu được phê duyệt
- `getGoiYChiTieu($maTruong, $namHoc)` - Tính gợi ý chỉ tiêu cho trường
- `luuPhanBoChiTieu($namHoc, $chiTieuData, $maNhanVienSo)` - Lưu phân bổ chỉ tiêu
- `validateChiTieuData($chiTieuData)` - Kiểm tra tính hợp lệ dữ liệu
- `capNhatTongChiTieu($namHoc, $tongChiTieu)` - Cập nhật tổng chỉ tiêu năm học

### 2. Controller
**File:** `controllers/nhanvienso/ChiTieuController.php`

Các action:
- `index()` - Hiển thị trang phân bổ chỉ tiêu
- `submit()` - Xử lý form phân bổ chỉ tiêu
- `updateTongChiTieu()` - Cập nhật tổng chỉ tiêu
- `getGoiY()` - API lấy gợi ý chỉ tiêu (AJAX)
- `cancel()` - Hủy phân bổ

### 3. View
**File:** `views/nhanvienso/chitieu/phanbo.php`

Các thành phần giao diện:
- Header với thông tin tổng quan
- Thống kê chỉ tiêu (Cards)
- Form cập nhật tổng chỉ tiêu
- Bảng danh sách trường với input chỉ tiêu
- Tính toán tổng chỉ tiêu realtime
- Modal xác nhận hủy phân bổ
- Lịch sử phân bổ

### 4. CSS
**File:** `assets/css/chitieu.css`

Styling cho:
- Responsive design
- Gradient cards
- Form controls
- Tables
- Buttons
- Modals
- Animations

## 📊 Database

### Bảng chính: `ChiTieuTuyenSinh`
```sql
CREATE TABLE ChiTieuTuyenSinh (
  maChiTieu VARCHAR(30) PRIMARY KEY,
  namHoc VARCHAR(15),
  tongChiTieu INT,
  chiTieuPhanBo INT,
  maTruong VARCHAR(30),
  maNhanVienSo VARCHAR(30),
  ngayBanHanh DATE,
  FOREIGN KEY (maTruong) REFERENCES Truong(maTruong),
  FOREIGN KEY (maNhanVienSo) REFERENCES NhanVienSo(maNhanVienSo)
);
```

### Bảng liên quan:
- `Truong` - Thông tin các trường THPT
- `NhanVienSo` - Thông tin nhân viên sở
- `TaiKhoan` - Tài khoản đăng nhập
- `LopHoc` - Để lấy danh sách năm học

## 🚀 Cài đặt

### Bước 1: Chạy script SQL
```bash
# Import database schema và dữ liệu mẫu
mysql -u root -p HeThongQuanLyHocSinh1 < database/setup_chitieu.sql
```

### Bước 2: Kiểm tra file cấu hình
Đảm bảo file `.env` có cấu hình đúng:
```env
DB_HOST=localhost
DB_NAME=HeThongQuanLyHocSinh1
DB_USER=root
DB_PASS=your_password
```

### Bước 3: Cấu hình routing
File `public/index.php` đã được cập nhật để xử lý controller `chitieu`

### Bước 4: Cấp quyền
Vai trò `nhanvienso` đã được thêm vào `config/roles.php`

## 🔐 Tài khoản test

**Username:** `nhanvienso`  
**Password:** `123456`  
**Email:** `nhanvienso@sgd.edu.vn`

## 📱 Sử dụng

### 1. Đăng nhập
- Truy cập `/Learning-Systems/public/index.php`
- Đăng nhập với tài khoản nhân viên sở

### 2. Truy cập chức năng
- Click vào card "Chỉ tiêu" trong dashboard
- Hoặc truy cập trực tiếp: `/Learning-Systems/public/index.php?controller=chitieu&action=index`

### 3. Phân bổ chỉ tiêu

#### Bước 1: Cập nhật tổng chỉ tiêu năm học
- Chọn năm học
- Nhập tổng chỉ tiêu được phê duyệt
- Click "Cập nhật"

#### Bước 2: Chọn năm học cần phân bổ
- Chọn năm học từ dropdown
- Hệ thống hiển thị danh sách trường

#### Bước 3: Nhập chỉ tiêu cho từng trường
- Nhập số lượng chỉ tiêu cho mỗi trường
- Hoặc click "Áp dụng gợi ý" để sử dụng số gợi ý
- Hoặc click "Áp dụng tất cả gợi ý" để áp dụng cho tất cả

#### Bước 4: Kiểm tra tổng chỉ tiêu
- Hệ thống tự động tính tổng chỉ tiêu đang nhập
- Hiển thị còn lại / vượt quá
- Đổi màu cảnh báo nếu vượt quá

#### Bước 5: Xác nhận phân bổ
- Click "Xác nhận phân bổ"
- Xác nhận trong dialog
- Hệ thống lưu và gửi thông báo

## ✨ Tính năng

### Tính năng chính
✅ Hiển thị danh sách trường THPT  
✅ Chọn năm học để phân bổ  
✅ Nhập chỉ tiêu cho từng trường  
✅ Gợi ý chỉ tiêu tự động dựa trên dữ liệu  
✅ Tính toán tổng chỉ tiêu realtime  
✅ Kiểm tra vượt quá tổng chỉ tiêu  
✅ Áp dụng gợi ý cho một hoặc tất cả trường  
✅ Lưu phân bổ vào database  
✅ Lịch sử phân bổ  

### Validation
✅ Chỉ tiêu phải là số dương  
✅ Không được để trống  
✅ Không được vượt quá tổng chỉ tiêu  
✅ Phải nhập đủ cho tất cả các trường  
✅ Không được là số thập phân  

### UX/UI
✅ Responsive design  
✅ Gradient colors  
✅ Smooth animations  
✅ Real-time calculation  
✅ Modal confirmations  
✅ Toast notifications  
✅ Loading states  

## 🔄 Basic Flow

| Nhân viên Sở | Hệ thống |
|--------------|----------|
| 1. Chọn chức năng Phân bổ chỉ tiêu tuyển sinh | 2. Hiển thị trang phân bổ chỉ tiêu tuyển sinh |
| 3. Chọn năm học | 4. Hiển thị danh sách các trường |
| 5. Nhập số lượng chỉ tiêu cho từng trường | 6. Hệ thống đưa ra con số gợi ý. Tự động cộng dồn chỉ tiêu |
| | 7. Kiểm tra tổng chỉ tiêu với số được phê duyệt |
| 8. Xác nhận phân bổ | 9. Lưu dữ liệu |
| | 10. Gửi thông báo về các trường. Hiển thị thông báo thành công |

## ⚠️ Alternative Flows

### 5.1 Nhập chỉ tiêu không hợp lệ
- Hệ thống báo lỗi và yêu cầu nhập lại
- Highlight input bị lỗi
- Quay lại bước 4

### 7.1 Vượt quá tổng chỉ tiêu
- Hệ thống thông báo lỗi
- Hiển thị số vượt quá
- Yêu cầu nhập lại

### 9.1 Bỏ trống chỉ tiêu
- Hệ thống cảnh báo
- Yêu cầu nhập đủ cho các trường
- Quay lại bước 5

### 8.1 Hủy phân bổ (Exception)
- Nhân viên chọn "Hủy phân bổ"
- Hệ thống hiển thị modal xác nhận
- Nhân viên xác nhận
- Kết thúc use case

## 🐛 Troubleshooting

### Lỗi kết nối database
```
Lỗi: Không thể kết nối database
Giải pháp: Kiểm tra file .env và cấu hình MySQL
```

### Không thấy chức năng
```
Lỗi: Không hiển thị menu "Phân bổ chỉ tiêu"
Giải pháp: 
1. Kiểm tra vai trò user có phải "nhanvienso"
2. Xóa cache session
3. Đăng nhập lại
```

### Validation error
```
Lỗi: Không lưu được dữ liệu
Giải pháp:
1. Kiểm tra đã nhập đủ chỉ tiêu cho tất cả trường
2. Kiểm tra chỉ tiêu là số dương
3. Kiểm tra không vượt quá tổng chỉ tiêu
```

## 📝 To-Do / Cải tiến

- [ ] Thêm chức năng export Excel
- [ ] Thêm chức năng import từ Excel
- [ ] Gửi email thông báo cho trường
- [ ] Lưu log thay đổi chi tiết
- [ ] Thêm biểu đồ thống kê
- [ ] In phiếu phân bổ
- [ ] Phê duyệt đa cấp
- [ ] So sánh chỉ tiêu các năm

## 📞 Liên hệ

Nếu có thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ team phát triển.

---

**Phiên bản:** 1.0  
**Ngày cập nhật:** 2024-03-15  
**Người phát triển:** Team PTUD  

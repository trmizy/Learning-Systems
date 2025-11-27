# 📊 CHỨC NĂNG PHÂN BỔ CHỈ TIÊU TUYỂN SINH
---
## Tổng quan
### Mô tả
Chức năng **Phân bổ chỉ tiêu tuyển sinh** cho phép Nhân viên Sở Giáo dục phân bổ số lượng học sinh tuyển sinh cho từng trường THPT theo từng năm học.
### Mục đích
- Phân bổ chỉ tiêu tuyển sinh công bằng cho các trường THPT
- Sử dụng thuật toán gợi ý dựa trên dữ liệu năm trước
- Quản lý lịch sử phân bổ qua các năm học
- Đảm bảo tổng chỉ tiêu phân bổ không vượt quá chỉ tiêu được phê duyệt
- Khóa cố định năm học 2023-2024 (năm cơ sở)
### Đối tượng sử dụng
- **Nhân viên Sở (nhanvienso)**: Người duy nhất có quyền phân bổ chỉ tiêu
### Đặc điểm nổi bật
- **Thuật toán gợi ý đơn giản**: Tăng 5% so với năm trước
- **Năm cơ sở cố định**: Năm 2023-2024 không thể chỉnh sửa/xóa
- **Validate tổng chỉ tiêu**: Cảnh báo nếu vượt/thiếu so với phê duyệt
- **Lịch sử phân bổ**: Theo dõi các lần phân bổ gần đây
- **Real-time calculation**: JavaScript tính tổng tức thời
---
## Yêu cầu hệ thống
### Công nghệ sử dụng
- **Backend**: PHP 8.x (OOP)
- **Database**: MySQL/MariaDB với View
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS**: Custom CSS (`chitieu.css`)
- **Icons**: Emoji Unicode
---
## Cấu trúc chức năng
### Sơ đồ thư mục
```
Learning-Systems/
│
├── views/
│   └── nhanvienso/
│       └── targetAllocation.php          # Giao diện phân bổ
│
├── controllers/
│   └── nhanvienso/
│       └── targetsController.php         # Controller chính
│
├── models/
│   └── admissionTargetsModel.php         # Model với thuật toán
│
├── assets/
│   └── css/
│       └── chitieu.css                   # Style riêng
│
└── middlewares/
    └── AuthGuard.php                     # Kiểm tra quyền
```
### Các file chính
#### 1. **targetAllocation.php** (View)
- **4 card thống kê**: Tổng trường, Tổng phê duyệt, Đã phân bổ, Tỷ lệ
- **Form chọn năm học**: Dropdown với auto-submit
- **Bảng phân bổ 7 cột**:
  - STT
  - Thông tin trường (Tên + Mã + Địa chỉ)
  - **Chỉ tiêu năm trước** (data thực tế)
  - **Gợi ý năm nay** (năm trước × 1.05)
  - Chỉ tiêu phân bổ (input)
  - Đã phân bổ (hiển thị)
  - Thao tác (nút áp dụng gợi ý)
- **Tổng kết real-time**: Phê duyệt, Đang nhập, Còn lại/Vượt
- **4 nút action**: Hủy, Xóa DB, Áp dụng tất cả, Xác nhận
- **Lịch sử phân bổ**: 10 bản ghi gần nhất
- **Code đã tối giản**: Dùng ternary operator, rút gọn if-else
#### 2. **targetsController.php** (Controller)
**Public methods**:
- `index()`: Load data và hiển thị view
- `submit()`: Lưu phân bổ (validation đơn giản)
- `getGoiY()`: API AJAX trả gợi ý
- `cancel()`: Redirect về trang chủ
- `reset()`: Xóa phân bổ (có confirm, lock 2023-2024)

**Private helper methods**:
- `layChiTieuDaPhanBo($namHoc)`: Lấy chỉ tiêu đã phân bổ cho năm hiện tại
- `layChiTieuNamTruoc($danhSachTruong, $namHoc)`: Lấy chỉ tiêu năm trước THỰC TẾ từ DB
- `tinhGoiYChoTungTruong($danhSachTruong, $namHoc)`: Tính gợi ý cho tất cả trường

#### 3. **admissionTargetsModel.php** (Model)
**Methods chính**:
- `loadNamHoc()`: Lấy danh sách năm học từ DB
- `getDanhSachTruong()`: Lấy tất cả trường THPT
- `getTongPheDuyet($namHoc)`: Logic đơn giản
  - 2023-2024 → 2000
  - Có trong DB → lấy từ DB
  - Chưa có → 180 × số trường
- `tinhGoiYChiTieu($maTruong, $namHoc)`: Thuật toán đơn giản
  - Năm trước = 0 → Chia đều
  - Năm trước > 0 → × 1.05 (tăng 5%)
  - Không làm tròn, không giới hạn
- `getChiTieuNamTruoc($maTruong, $namHoc)`: Private - Query năm trước
- `getChiTieuNamTruocThucTe($maTruong, $namHoc)`: **Public** - Lấy chỉ tiêu năm trước từ DB (dùng cho controller)
- `luuPhanBo()`: Transaction-based save
- `kiemTraTongChiTieu()`: Validate input (5 rules)
- `xoaPhanBoTheoNamHoc()`: Delete with check
- `getLichSuPhanBo()`: Dùng viewChiTieu
---
## Hướng dẫn sử dụng
### 1. Truy cập chức năng
**Bước 1**: Đăng nhập với tài khoản Nhân viên Sở
**Bước 2**: Vào menu "Phân bổ chỉ tiêu tuyển sinh"
### 2. Xem thống kê tổng quan
Màn hình hiển thị 4 card thống kê:
**Card 1: Tổng trường THPT**
- Số lượng trường trong hệ thống
**Card 2: Tổng chỉ tiêu phê duyệt**
- Tổng chỉ tiêu cho năm học được chọn
- Năm 2023-2024: 2,000 (cố định)
- Các năm khác: Lấy từ DB, nếu chưa có thì lấy tổng năm trước × 1.05
**Card 3: Đã phân bổ**
- Tổng chỉ tiêu đã phân bổ cho các trường
- Hiển thị "Còn lại" hoặc "Vượt"
**Card 4: Tỷ lệ phân bổ**
- Phần trăm đã phân bổ so với phê duyệt
### 3. Chọn năm học
**Bước 1**: Chọn năm học từ dropdown
**Bước 2**: Hệ thống tự động load
**Lưu ý đặc biệt**: Năm 2023-2024
```
⚠️ Năm cơ sở - Không thể chỉnh sửa
- Các input bị disable (màu xám)
- Không có nút "Áp dụng gợi ý"
- Không có nút "Xác nhận phân bổ"
- Không có nút "Xóa phân bổ"
- Hiển thị cảnh báo: "Đã được cố định"
```
### 4. Phân bổ chỉ tiêu
#### Cách 1: Nhập thủ công
**Bước 1**: Nhập chỉ tiêu cho từng trường
**Bước 2**: Theo dõi tổng kết
**Bước 3**: Xác nhận phân bổ
#### Cách 2: Sử dụng gợi ý
**Áp dụng gợi ý cho từng trường**:
**Áp dụng tất cả gợi ý**:

## Cơ sở dữ liệu

### Bảng ChiTieuTuyenSinh
Lưu trữ thông tin phân bổ chỉ tiêu cho từng trường theo năm học.

**Format mã chỉ tiêu**: `CT + Năm(2số) + MãTrường`
- CT2324TR001: Chỉ tiêu năm 2023-2024 cho trường TR001
- CT2425TR002: Chỉ tiêu năm 2024-2025 cho trường TR002

### VIEW viewChiTieu
Tối ưu hiển thị dữ liệu chỉ tiêu với JOIN sẵn.

**Sử dụng trong**:
- Hiển thị lịch sử phân bổ
- Thống kê tổng hợp
---
## Validation
### Validation đầu vào
**kiemTraTongChiTieu()**:
1. ✅ Phải là số
2. ✅ Không được âm  
3. ✅ Không được = 0
4. ✅ Phải là số nguyên (không có thập phân)
5. ✅ Tổng không được vượt tổng phê duyệt

---
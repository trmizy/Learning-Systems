# 📊 CHỨC NĂNG XEM THỐNG KÊ ĐIỂM VÀ HẠNH KIỂM
##  Tổng quan
### Mô tả
Chức năng **Xem thống kê điểm và hạnh kiểm** cho phép Ban Giám hiệu xem báo cáo tổng hợp về kết quả học tập và hạnh kiểm của học sinh theo nhiều tiêu chí lọc.
### Mục đích
- Xem thống kê điểm trung bình chung của học sinh
- Xem xếp loại học lực (Giỏi, Khá, Trung Bình, Yếu)
- Xem xếp loại hạnh kiểm (Tốt, Khá, Trung Bình, Yếu)
- Lọc theo năm học, học kỳ, khối, lớp
- Tính toán tỉ lệ phần trăm theo từng loại
- Xuất báo cáo tổng hợp
### Đối tượng sử dụng
- **Ban Giám hiệu (bgh)**: Hiệu trưởng, Phó hiệu trưởng có quyền xem thống kê toàn trường
### Đặc điểm nổi bật
- ✅ **Database View tối ưu**: viewThongKeDiemHanhKiem tính sẵn điểm TB
- ✅ **Bộ lọc linh hoạt**: Năm học, học kỳ, khối, lớp (cascade)
- ✅ **Thống kê tự động**: Tổng số, điểm TB, tỉ lệ % theo loại
- ✅ **Xếp loại theo quy chuẩn**: Học lực + Hạnh kiểm
- ✅ **Hiển thị trực quan**: Bảng dữ liệu + Cards thống kê
---
##  Yêu cầu hệ thống
### Công nghệ sử dụng
- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB với VIEW
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS**: Bootstrap 5
- **Icons**: Font Awesome 6
---
##  Cấu trúc chức năng
### Sơ đồ thư mục
```
Learning-Systems/
│
├── views/
│   └── bgh/
│       └── statisticsView.php              # Giao diện thống kê
│
├── controllers/
│   └── bgh/
│       └── statisticsController.php        # Controller xử lý
│
├── models/
│   └── StatisticsModel.php                 # Model thống kê
│
├── viewThongKeDiemHanhKiem.sql             # SQL tạo VIEW
│
└── middlewares/
    └── AuthGuard.php                       # Kiểm tra quyền
```
### Các file chính
#### 1. **statisticsView.php** (View)
- Form bộ lọc:
  - Dropdown năm học
  - Dropdown học kỳ (HK1, HK2)
  - Dropdown khối (10, 11, 12)
  - Dropdown lớp (theo khối)
- Cards thống kê tổng quan:
  - Tổng số học sinh
  - Điểm trung bình chung
  - Thống kê học lực (Giỏi/Khá/TB/Yếu)
  - Thống kê hạnh kiểm (Tốt/Khá/TB/Yếu)
- Bảng danh sách học sinh:
  - STT, Họ tên, Lớp
  - Điểm TB chung
  - Xếp loại học lực
  - Xếp loại hạnh kiểm
#### 2. **statisticsController.php** (Controller)
- Kiểm tra quyền BGH
- Lấy dữ liệu cho dropdowns
- Xử lý POST: Lọc thống kê
- Trả về kết quả cho view
#### 3. **StatisticsModel.php** (Model)
- `getDanhSachNamHoc()`: Lấy năm học có dữ liệu
- `getDanhSachHocKy()`: Lấy danh sách học kỳ (HK1, HK2)
- `getDanhSachKhoi()`: Lấy danh sách khối (10, 11, 12)
- `getDanhSachLop()`: Lấy danh sách lớp (theo khối)
- `getNamHocHocKyGanNhat()`: Lấy năm học/học kỳ mới nhất
- `thongKeDiemHanhKiem()`: Thực hiện thống kê với filters
- `tinhToanThongKe()`: Tính toán số liệu thống kê
#### 4. **viewThongKeDiemHanhKiem** (Database VIEW)
- VIEW tổng hợp dữ liệu từ nhiều bảng
- Tự động tính điểm trung bình môn
- Tự động xếp loại học lực
- JOIN: hocsinh, lophoc, bangdiem, hanhkiem
---
##  Hướng dẫn sử dụng
### 1. Truy cập chức năng
**Bước 1**: Đăng nhập với tài khoản Ban Giám hiệu
**Bước 2**: Vào xem "Báo cáo học vụ"
### 2. Chọn bộ lọc
**Quy tắc lọc**:
**Năm học** (Bắt buộc):
```
- Hiển thị: Các năm học có dữ liệu
- Format: "2024-2025", "2023-2024"
- Sắp xếp: Từ mới đến cũ
- Mặc định: Năm học gần nhất
```
**Học kỳ** (Bắt buộc):
```
- HK1: Học kỳ 1
- HK2: Học kỳ 2
```
**Khối** (Tùy chọn):
```
- Tất cả: Xem toàn trường
- 10: Chỉ khối 10
- 11: Chỉ khối 11
- 12: Chỉ khối 12
- Cascade: Chọn khối → Dropdown lớp chỉ hiện lớp của khối đó
```
**Lớp** (Tùy chọn):
```
- Tất cả: Xem tất cả lớp (của khối đã chọn)
- 10A1, 10A2, ...: Xem từng lớp cụ thể
- Phụ thuộc: Phải chọn khối trước
```
### 3. Xem kết quả thống kê
### 4. Các trường hợp lọc
**Trường hợp 1: Xem toàn trường**
```
Input:
  - Năm học: 2024-2025
  - Học kỳ: HK1
  - Khối: Tất cả
  - Lớp: Tất cả

Kết quả:
  - Hiển thị TẤT CẢ học sinh trong trường
  - Tổng hợp: Toàn bộ khối 10, 11, 12
```
**Trường hợp 2: Xem theo khối**
```
Input:
  - Năm học: 2024-2025
  - Học kỳ: HK1
  - Khối: 10
  - Lớp: Tất cả

Kết quả:
  - Hiển thị TẤT CẢ học sinh khối 10
  - Tổng hợp: Tất cả lớp 10A1, 10A2, 10A3, ...
```
**Trường hợp 3: Xem theo lớp cụ thể**
```
Input:
  - Năm học: 2024-2025
  - Học kỳ: HK1
  - Khối: 10
  - Lớp: 10A1

Kết quả:
  - Hiển thị CHỈ học sinh lớp 10A1
  - Tổng hợp: Chỉ lớp 10A1
```
**Trường hợp 4: So sánh giữa các học kỳ**
```
Bước 1: Xem HK1 (năm 2024-2025)
Bước 2: Xem HK2 (năm 2024-2025)
→ BGH tự so sánh kết quả
```
### 5. Hiểu các chỉ số thống kê
**Tổng số học sinh**:
```
- Đếm số học sinh thỏa điều kiện lọc
- Chỉ đếm học sinh có trạng thái "DANGHOC"
- Ví dụ: 350
```
**Điểm trung bình chung**:
```
- Công thức: Tổng điểm TB của tất cả HS / Số HS
- Làm tròn: 2 chữ số thập phân
```
**Thống kê học lực**:
```
Ví dụ:
Giỏi: 45 học sinh (12.86%)
  → 45 HS có điểm TB >= 8
  → 45/350 = 12.86%
Khá: 120 học sinh (34.29%)
  → 120 HS có điểm TB từ 6.5 đến <8
  → 120/350 = 34.29%
Trung Bình: 150 học sinh (42.86%)
  → 150 HS có điểm TB từ 5 đến <6.5
  → 150/350 = 42.86%
Yếu: 35 học sinh (10.00%)
  → 35 HS có điểm TB <5
  → 35/350 = 10.00%
```
**Thống kê hạnh kiểm**:
```
Tốt: 200 học sinh (57.14%)
Khá: 100 học sinh (28.57%)
Trung Bình: 40 học sinh (11.43%)
Yếu: 10 học sinh (2.86%)
Tổng: 200 + 100 + 40 + 10 = 350 ✓
```
## 🌟 Tổng Quan | Overview
### 📝 Mô tả

Chức năng **Phân bổ chỉ tiêu tuyển sinh** là một hệ thống quản lý thông minh, cho phép Nhân viên Sở Giáo dục & Đào tạo phân bổ số lượng học sinh tuyển sinh cho **11 trường THPT** một cách khoa học và hiệu quả.

<table>
<tr>
<td width="50%">

#### 🎯 **Đầu vào**
- 📊 Dữ liệu năm học trước  
- 🏫 Thông tin 11 trường THPT  
- 👥 Số học sinh hiện tại  
- 📈 Năng lực và quy mô trường  

</td>
<td width="50%">

#### 📤 **Đầu ra**
- 🎓 Chỉ tiêu cho từng trường  
- 📊 Phân tích % phân bổ  
- ✅ Validation tự động  
- 📧 Thông báo cho các trường  

</td>
</tr>
</table>

### ✨ Đặc điểm nổi bật

<table>
<tr>
<td align="center" width="33%">
<img src="https://img.icons8.com/fluency/96/lock.png" width="64" alt="Lock"/>
<h4>🔒 Năm gốc cố định</h4>
<p><small>2023-2024 với 8,000 chỉ tiêu<br/>Không thể chỉnh sửa</small></p>
</td>
<td align="center" width="33%">
<img src="https://img.icons8.com/fluency/96/artificial-intelligence.png" width="64" alt="AI"/>
<h4>🤖 Gợi ý thông minh</h4>
<p><small>Thuật toán weighted average<br/>4 yếu tố với trọng số</small></p>
</td>
<td align="center" width="33%">
<img src="https://img.icons8.com/fluency/96/checkmark.png" width="64" alt="Check"/>
<h4>✅ Validation 3 lớp</h4>
<p><small>HTML5 + JavaScript + PHP<br/>Real-time feedback</small></p>
</td>
</tr>
<tr>
<td align="center" width="33%">
<img src="https://img.icons8.com/fluency/96/color-palette.png" width="64" alt="Colors"/>
<h4>🎨 Visual Feedback</h4>
<p><small>Màu sắc trực quan<br/>Đỏ / Vàng / Xanh</small></p>
</td>
</tr>
</table>

---

<div align="center">

## 🎯 Tính Năng Chính | Key Features

</div>

### 🎓 1. Quản lý Năm học

<details open>
<summary><b>Click to expand</b></summary>

| Feature | Description | Status |
|---------|-------------|--------|
| 📅 Danh sách năm học | 2023-2024 → 2026-2027 | ✅ |
| 🔒 Khóa năm gốc | 2023-2024 (Read-only) | ✅ |
| 🔄 Chuyển đổi năm | Dropdown selection | ✅ |
| 📊 Hiển thị thống kê | Tổng/Đã phân bổ/Còn lại | ✅ |

</details>

### 📝 2. Phân bổ Chỉ tiêu
<details open>
**Chức năng:**
- ✍️ Nhập chỉ tiêu cho 11 trường (TR001 - TR011)
- 🔢 Tự động tính tổng real-time
- 📊 Hiển thị % phân bổ cho từng trường
- ⚠️ Cảnh báo nếu tổng không khớp
- 🎨 Visual feedback (🔴 Đỏ / 🟡 Vàng / 🟢 Xanh)

</details>

### 🤖 3. Gợi ý Thông minh

<details open>
<summary><b>Click to expand</b></summary>

**Thuật toán Weighted Average:**

```
Chỉ tiêu gợi ý = Σ (Yếu tố[i] × Trọng số[i])
                 i=1..4

Yếu tố 1: Chỉ tiêu năm trước      × 40%
Yếu tố 2: Số học sinh hiện tại    × 30%
Yếu tố 3: Tỷ lệ tốt nghiệp         × 20%
Yếu tố 4: Cơ sở vật chất           × 10%
```

**Đặc điểm:**
- 🎯 Làm tròn đến bội số 50
- 📏 Giới hạn: min=100, max=2,000
- ⚡ Tính toán tức thì
- 🔘 Áp dụng từng trường hoặc tất cả

</details>

### ✅ 4. Validation Đa lớp
<details>
**Kiểm tra:**
- ❌ Số âm → Báo lỗi + Clear input
- ❌ Số 0 → Báo lỗi + Clear input
- ❌ Số thập phân → Chỉ chấp nhận số nguyên
- ⚠️ Tổng vượt quá → Cảnh báo (vàng)
- ✅ Tổng khớp → Cho phép submit (xanh)

</details>

### 📚 5. Lịch sử Phân bổ

<details>
<summary><b>Click to expand</b></summary>

**Hiển thị 5 lần gần nhất:**

| Năm học | Ngày phân bổ | Số trường | Tổng chỉ tiêu | Người thực hiện |
|---------|--------------|-----------|---------------|-----------------|
| 2024-2025 | 15/03/2024 | 11 | 9,500 | Nguyễn Văn A |
| 2023-2024 | 10/02/2023 | 11 | 8,000 | Trần Thị B |

</details>

---

## 📁 Cấu trúc dự án

```
Learning-Systems/
│
├── models/
│   └── admissionTargetsModel.php          (578 dòng) - Business logic
│
├── controllers/
│   └── nhanvienso/
│       └── targetsController.php          (190 dòng) - Request handling
│
├── views/
│   └── nhanvienso/
│       ├── dashboard.php                   - Dashboard NV Sở
│       └── targetAllocation.php           (444 dòng) - Form phân bổ
│
│
├── public/
│   └── index.php                           - Router chính
│
└── config/
    ├── database.php                        - Database connection
    └── roles.php                           - Role definitions
```

### Thống kê

| Loại | Số lượng | Tổng dòng code |
|------|----------|----------------|
| **Model** | 1 file | ~578 dòng |
| **Controller** | 1 file | ~190 dòng |
| **View** | 1 file | ~444 dòng |
| **Database Scripts** | 7 files | ~690 dòng |
| **TỔNG** | **10 files** | **~1,902 dòng** |

---

## 🏗️ Kiến trúc MVC
### 1. Model - `admissionTargetsModel.php`

**Class:** `AdmissionTargetsModel`

**Các phương thức chính:**

| Phương thức | Dòng code | Chức năng |
|-------------|-----------|-----------|
| `loadNamHoc()` | 15-46 | Lấy danh sách năm học từ DB + hardcoded |
| `getDanhSachTruong()` | 48-62 | Lấy danh sách 11 trường THPT |
| `getMaNhanVienSoByUsername($username)` | 65-105 | **Lấy maNhanVienSo từ username** |
| `getTongPheDuyet($namHoc)` | 94-135 | Lấy tổng chỉ tiêu đã được phê duyệt |
| `tinhGoiYChiTieu($maTruong, $namHoc)` | 238-310 | **Thuật toán gợi ý 4 yếu tố** |
| `kiemTraTongChiTieu($data, $namHoc)` | 515-551 | Validation với 3 kiểm tra riêng |
| `tinhTongChiTieuDaNhap($data)` | 553-558 | Tính tổng từ array |
| `luuPhanBo($namHoc, $data, $nvs)` | 429-513 | Lưu vào DB với Transaction |
| `guiThongBaoPhanBo($namHoc, $data)` | 515-520 | Gửi notification (placeholder) |

### 2. Controller - `targetsController.php`

**Class:** `TargetsController`

**Các action:**

| Action | Method | URL | Chức năng |
|--------|--------|-----|-----------|
| `index()` | GET | `?controller=targets&action=index` | Hiển thị form |
| `submit()` | POST | `?controller=targets&action=submit` | Xử lý submit |
| `getGoiY()` | GET | `?controller=targets&action=getGoiY` | API AJAX |
| `cancel()` | GET | `?controller=targets&action=cancel` | Hủy bỏ |


### 3. View - `targetAllocation.php`

**Các section chính:**

1. **Header & Statistics** (dòng 10-60)
   - Thẻ thống kê: Tổng, Đã phân bổ, Còn lại, % hoàn thành
   
2. **Year Selector** (dòng 62-90)
   - Dropdown chọn năm học
   - JavaScript reload khi đổi năm
   
3. **Allocation Table** (dòng 110-250)
   - Bảng 11 trường với input fields
   - Cột: STT, Tên trường, Chỉ tiêu, Gợi ý, %
   
4. **Action Buttons** (dòng 252-290)
   - Lưu phân bổ
   - Tải gợi ý
   - Hủy bỏ
   
5. **JavaScript Validation** (dòng 292-440)
   - `validateNegativeInput()` - Real-time
   - `validateForm()` - Pre-submit
   - `updateTotal()` - Tính tổng
   - `loadGoiY()` - AJAX load suggestions

**Conditional Rendering cho năm 2023-2024:**

---

## 🧮 Thuật toán phân bổ

### Weighted Average với 4 yếu tố

```php
public function tinhGoiYChiTieu($maTruong, $namHoc) {
    // === YẾU TỐ 1: Chỉ tiêu năm trước (40% trọng số) ===
    $chiTieuNamTruoc = $this->getChiTieuNamTruoc($maTruong, $namHoc);
    
    // === YẾU TỐ 2: Tỷ lệ tăng trưởng hệ thống (30% trọng số) ===
    $tyLeTangTruong = $this->getTyLeTangTruongChiTieu($namHoc);
    // Công thức: (TongMoi - TongCu) / TongCu
    // Giới hạn: -20% đến +20%
    
    // === YẾU TỐ 3: Số học sinh hiện tại (20% trọng số) ===
    $soLuongHS = $this->getSoLuongHocSinhHienTai($maTruong);
    
    // === YẾU TỐ 4: Năng lực trường (10% trọng số) ===
    $nangLucTruong = $this->getNangLucTruong($maTruong);
    // Giá trị: -0.2 đến +0.2 (điều chỉnh ±20%)
    
    // === TÍNH TOÁN ===
    if ($chiTieuNamTruoc > 0) {
        // Trường hợp CÓ dữ liệu năm trước
        $goiY = round($chiTieuNamTruoc * (1 + $tyLeTangTruong));
        $goiY = round($goiY * 0.8 + ($soLuongHS / 3) * 0.2);
        $goiY = round($goiY * (1 + $nangLucTruong * 0.1));
    } else {
        // Trường hợp KHÔNG có dữ liệu năm trước
        $tongChiTieu = $this->getTongPheDuyet($namHoc);
        $soTruong = count($this->getDanhSachTruong());
        
        $goiYTrungBinh = round($tongChiTieu / $soTruong);
        $goiY = round($goiYTrungBinh * (1 + $nangLucTruong * 0.2));
        $goiY = round($goiY * 0.7 + ($soLuongHS / 3) * 0.3);
    }
    
    // === ĐIỀU CHỈNH HỢP LÝ ===
    $goiY = max($goiY, 100);      // Tối thiểu 100
    $goiY = min($goiY, 2000);     // Tối đa 2,000
    $goiY = round($goiY / 50) * 50; // Làm tròn bội số 50
    
    return $goiY;
}
```

### Ví dụ tính toán cho Trường THPT A

**Input:**
- Chỉ tiêu năm trước: 750
- Tỷ lệ tăng trưởng hệ thống: +4% (0.04)
- Số học sinh hiện tại: 900
- Năng lực trường: 0 (trung bình)

**Tính toán:**

```
Bước 1: goiY = 750 * (1 + 0.04) = 780

Bước 2: goiY = 780 * 0.8 + (900 / 3) * 0.2
              = 624 + 60 = 684

Bước 3: goiY = 684 * (1 + 0 * 0.1) = 684

Bước 4: Làm tròn bội số 50 = 700

Kết quả: 700 học sinh
```

### Tổng chỉ tiêu tự động

Đối với các năm KHÔNG phải 2023-2024:

```php
private function tinhTongChiTieuTuDong($namHoc) {
    // Yếu tố 1: Tổng năm trước (60%)
    $tongNamTruoc = $this->getTongChiTieuNamTruocGanNhat($namHoc);
    
    // Yếu tố 2: Số học sinh / 3 (30%)
    $tongHS = $this->getTongSoHocSinhHienTai();
    $goiYTheoHS = round($tongHS / 3);
    
    // Yếu tố 3: Quy mô hệ thống (10%)
    $soTruong = count($this->getDanhSachTruong());
    $goiYTheoTruong = $soTruong * 1200; // Trung bình 1,200/trường
    
    if ($tongNamTruoc > 0) {
        // Có dữ liệu năm trước: Tăng 4%/năm
        $tangTruong = 1.04;
        $tongChiTieu = round(
            ($tongNamTruoc * $tangTruong) * 0.60 +
            $goiYTheoHS * 0.30 +
            $goiYTheoTruong * 0.10
        );
    } else {
        // Không có dữ liệu: Dựa vào HS và trường
        $tongChiTieu = round(
            $goiYTheoHS * 0.70 +
            $goiYTheoTruong * 0.30
        );
    }
    
    // Làm tròn bội số 500
    $tongChiTieu = round($tongChiTieu / 500) * 500;
    
    // Giới hạn: 100*soTruong đến 2000*soTruong
    $min = $soTruong * 100;
    $max = $soTruong * 2000;
    $tongChiTieu = max(min($tongChiTieu, $max), $min);
    
    return $tongChiTieu;
}
```

---

## 🗄️ Database Schema

### Bảng `ChiTieuTuyenSinh`

### Cấu trúc dữ liệu

#### Record PHÂN BỔ (cho từng trường)

```sql
INSERT INTO ChiTieuTuyenSinh VALUES (
    'CT_2023-2024_TR001_1234567890',  -- maChiTieu
    '2023-2024',                       -- namHoc
    8000,                              -- tongChiTieu = NULL
    750,                               -- chiTieuPhanBo
    'TR001',                           -- maTruong
    1,                                 -- maNhanVienSo
    '2023-02-01'                              -- ngayBanHanh
);
```

### Dữ liệu mẫu năm 2023-2024

| maTruong | Tên trường | chiTieuPhanBo | % |
|----------|-----------|---------------|---|
| TR001 | THPT Minh Khai | 750 | 9.38% |
| TR002 | THPT Lê Quý Đôn | 720 | 9.00% |
| TR003 | THPT Nguyễn Huệ | 730 | 9.13% |
| TR004 | THPT Trần Phú | 710 | 8.88% |
| TR005 | THPT Phan Châu Trinh | 740 | 9.25% |
| TR006 | THPT Nguyễn Thị Minh Khai | 750 | 9.38% |
| TR007 | THPT Trần Đại Nghĩa | 720 | 9.00% |
| TR008 | THPT Marie Curie | 730 | 9.13% |
| TR009 | THPT Gia Định | 710 | 8.88% |
| TR010 | THPT Bùi Thị Xuân | 740 | 9.25% |
| TR011 | THPT Nguyễn Du | 700 | 8.75% |
| **TỔNG** | | **8,000** | **100%** |

---

## 📖 Hướng dẫn sử dụng

### 1. Đăng nhập
- Role: Nhân viên Sở GD&ĐT

### 2. Truy cập Dashboard
Từ Dashboard, click vào thẻ **"Chỉ tiêu"** → **"Phân bổ chỉ tiêu"**

### 3. Chọn năm học

- Dropdown hiển thị 4 năm: 2023-2024 đến 2026-2027
- Năm 2023-2024 có nhãn "NĂM GỐC 🔒"
- Khi đổi năm, trang tự động reload

### 4. Xem năm gốc 2023-2024 (chỉ đọc)


**Đặc điểm:**
- ❌ Input fields bị **disabled** (không nhập được)
- ❌ Buttons "Lưu" và "Tải gợi ý" bị **ẩn**
- ⚠️ Alert cảnh báo: "NĂM GỐC ĐÃ KHÓA"
- ✅ Chỉ có thể **XEM**, không sửa

### 5. Phân bổ cho năm mới (2024-2025)


#### Bước 5.1: Nhập chỉ tiêu thủ công

```
Trường THPT A: 700 ✅
Trường THPT B: 680 ✅
Trường THPT C: 720 ✅
...
Tổng tự động: 8,320
```

#### Bước 5.2: Hoặc sử dụng gợi ý

Click **"Tải gợi ý"** → Hệ thống tự động điền:


#### Bước 5.3: Validation real-time

**Nhập số âm:**
```
Input: -100
→ Border đỏ, background hồng
→ Tooltip: "❌ KHÔNG ĐƯỢC NHẬP SỐ ÂM!"
→ Sau 500ms: Tự động xóa + Alert
```

**Nhập số 0:**
```
Input: 0
→ Border vàng (#ffc107)
→ Tooltip: "⚠️ Không được bằng 0!"
```

**Nhập hợp lệ:**
```
Input: 750
→ Border xanh (#28a745)
→ Tooltip: "✅ Hợp lệ"
→ Tổng tự động cập nhật
```

#### Bước 5.4: Submit

Click **"Lưu phân bổ"** → Validation form:

```javascript
// Kiểm tra tổng
if (tongNhap != tongPheDuyet) {
    alert('❌ Tổng không khớp!');
    return false;
}

// Kiểm tra số âm
if (hasNegative) {
    alert('❌ Các trường có số âm: ' + danhSach);
    return false;
}

// Kiểm tra số 0
if (hasZero) {
    alert('⚠️ Các trường có số 0: ' + danhSach);
    return false;
}

// Submit thành công
form.submit();
```

### 6. Xem lịch sử phân bổ

Hiển thị 5 lần phân bổ gần nhất:

| Năm học | Ngày ban hành | Số trường | Tổng phân bổ | Tổng chỉ tiêu |
|---------|---------------|-----------|--------------|---------------|
| 2024-2025 | 31/10/2025 | 11 | 8,320 | 8,320 |
| 2023-2024 | 01/09/2023 | 11 | 8,000 | 8,000 |

---

## 🎯 Chức năng mới: Phân bổ chỉ tiêu tuyển sinh

### 📝 Mô tả
Chức năng cho phép Nhân viên Sở phân bổ chỉ tiêu tuyển sinh cho 11 trường THPT 
với thuật toán gợi ý thông minh và validation 3 lớp.

### ✨ Các thay đổi chính
- ✅ Model: `admissionTargetsModel.php` với 10 methods
- ✅ Controller: `targetsController.php` với 4 actions
- ✅ View: `targetAllocation.php` với real-time validation
- ✅ Router: Thêm route 'targets' và giữ 'chitieu' (legacy)
- ✅ Documentation: `README_ADMISSION_TARGETS.md` (2290 dòng)

---

## �🗺️ Roadmap

### Version 1.0 (Hiện tại) ✅

- [x] MVC architecture hoàn chỉnh
- [x] Phân bổ chỉ tiêu cho 11 trường
- [x] Thuật toán weighted average 4 yếu tố
- [x] Validation 3 lớp
- [x] Năm gốc 2023-2024 cố định
- [x] Visual feedback real-time
- [x] Lịch sử phân bổ

### Version 1.1 (Kế hoạch) 🔜

- [ ] **Email notification** - Gửi thông báo tự động cho hiệu trưởng
- [ ] **Export Excel** - Xuất file phân bổ ra Excel
- [ ] **PDF Report** - Tạo báo cáo PDF chuyên nghiệp
- [ ] **Comparison View** - So sánh phân bổ giữa các năm
- [ ] **Charts & Analytics** - Biểu đồ phân tích xu hướng
- [ ] **Audit Log** - Lưu lại lịch sử thay đổi chi tiết

### Version 1.2 (Tương lai) 📅

- [ ] **Advanced Algorithm** - Tích hợp machine learning
- [ ] **Multi-year Planning** - Lập kế hoạch 3-5 năm
- [ ] **School Feedback** - Hiệu trưởng có thể góp ý
- [ ] **Approval Workflow** - Quy trình phê duyệt đa cấp
- [ ] **API Integration** - RESTful API cho mobile app
- [ ] **Real-time Collaboration** - Nhiều NV Sở làm việc cùng lúc


---

<div align="center">
  <sub>Hệ thống quản lý học sinh - PTUD 2025</sub>
</div>

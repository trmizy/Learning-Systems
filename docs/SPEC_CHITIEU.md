# 📋 Đặc tả Chức năng Phân bổ Chỉ tiêu Tuyển sinh

## 1. Tổng quan

### 1.1 Mô tả chức năng
Chức năng **Phân bổ chỉ tiêu tuyển sinh** cho phép **Nhân viên Sở Giáo dục** phân bổ chỉ tiêu tuyển sinh cho các trường THPT trong hệ thống dựa trên **tổng chỉ tiêu đã được Sở GD&ĐT nhập sẵn**.

### 1.2 Vai trò liên quan
- **Sở GD&ĐT (cấp cao hơn, ví dụ: Admin/Trưởng phòng)**: Nhập **tổng chỉ tiêu** cho từng năm học vào hệ thống
- **Nhân viên Sở**: Phân bổ chỉ tiêu cho từng trường THPT dựa trên tổng chỉ tiêu đã có sẵn

### 1.3 Quy trình tổng quát
```
[Sở GD&ĐT nhập tổng chỉ tiêu vào hệ thống] 
         ↓
[Nhân viên Sở xem tổng chỉ tiêu (read-only)]
         ↓
[Nhân viên Sở phân bổ cho từng trường]
         ↓
[Hệ thống kiểm tra: tổng phân bổ = tổng đã nhập]
         ↓
[Lưu phân bổ]
```

---

## 2. Yêu cầu chức năng

### 2.1 Điều kiện tiên quyết (Precondition)
- ✅ Người dùng đã đăng nhập với vai trò **"nhanvienso"**
- ✅ **Sở GD&ĐT đã nhập tổng chỉ tiêu** cho năm học cần phân bổ vào database
- ✅ Hệ thống đã có dữ liệu về:
  - Danh sách trường THPT
  - Năm học cần phân bổ
  - Dữ liệu lịch sử (năm trước) để tính gợi ý

### 2.2 Dữ liệu đầu vào (Input)
| Tên trường | Kiểu dữ liệu | Bắt buộc | Mô tả |
|------------|--------------|----------|-------|
| `namHoc` | String | ✅ Có | Năm học (vd: "2024-2025") |
| `chiTieu_[maTruong]` | Integer | ✅ Có | Chỉ tiêu cho từng trường |

**Ví dụ dữ liệu POST:**
```php
$_POST = [
    'namHoc' => '2024-2025',
    'chitieu_THPT001' => 1250,
    'chitieu_THPT002' => 1200,
    'chitieu_THPT003' => 1150,
    // ...
];
```

### 2.3 Dữ liệu đầu ra (Output)
- **Thông báo thành công**: "Phân bổ chỉ tiêu tuyển sinh thành công!"
- **Thông báo lỗi**: Danh sách lỗi chi tiết (nếu có)
- **Bảng lịch sử phân bổ**: Hiển thị 5 lần phân bổ gần nhất

### 2.4 Điều kiện hậu tố (Postcondition)
- ✅ Dữ liệu phân bổ được lưu vào bảng `ChiTieuTuyenSinh`
- ✅ Mỗi trường có 1 bản ghi chỉ tiêu cho năm học đó
- ✅ Tổng chỉ tiêu phân bổ = Tổng chỉ tiêu phê duyệt
- ✅ Lịch sử phân bổ được cập nhật

---

## 3. Use Case: Phân bổ chỉ tiêu tuyển sinh

### 3.1 Basic Flow (Luồng chính)

#### Bước 1: Nhân viên Sở truy cập trang phân bổ
```
URL: /public/index.php?controller=chitieu&action=index
Method: GET
```

**Hệ thống hiển thị:**
- 📊 Thống kê tổng quan:
  - Tổng số trường THPT
  - **Tổng chỉ tiêu đã được Sở nhập sẵn** (chỉ đọc, không thể sửa)
  - Chỉ tiêu đã phân bổ
  - Tỷ lệ phân bổ
- 📝 Form chọn năm học
- 📋 Bảng danh sách trường với:
  - Tên trường
  - Số học sinh hiện tại
  - **Chỉ tiêu năm trước** (2023-2024)
  - **Gợi ý** (dựa trên thuật toán Weighted Average)
  - Ô nhập chỉ tiêu

#### Bước 2: Nhân viên Sở chọn năm học
- Chọn năm học từ dropdown (vd: "2024-2025")
- Hệ thống tải:
  - **Tổng chỉ tiêu** (đã được Sở nhập sẵn vào database)
  - Chỉ tiêu đã phân bổ (nếu có)
  - Gợi ý cho từng trường

#### Bước 3: Nhân viên Sở nhập chỉ tiêu cho từng trường
- Có thể:
  - ✏️ Nhập thủ công
  - 💡 Sử dụng gợi ý (nút "Áp dụng gợi ý")
- Hệ thống **tự động tính tổng** và hiển thị:
  - Tổng chỉ tiêu đang nhập
  - Còn lại (hoặc Vượt)

#### Bước 4: Validation (Kiểm tra dữ liệu)
**Client-side (JavaScript):**
```javascript
// Kiểm tra tổng = tổng chỉ tiêu phê duyệt
if (tongChiTieuDangNhap !== tongChiTieuPheDuyet) {
    alert("Tổng chỉ tiêu phải bằng " + tongChiTieuPheDuyet);
    return false;
}

// Kiểm tra mỗi trường > 0
for (each truong) {
    if (chiTieu <= 0) {
        alert("Chỉ tiêu phải > 0");
        return false;
    }
}
```

**Server-side (PHP):**
```php
// models/ChiTieuTuyenSinh.php
public function validateChiTieuData($chiTieuData) {
    $errors = [];
    
    // 1. Kiểm tra đã nhập đủ chưa
    if (count($chiTieuData) === 0) {
        $errors[] = "Chưa nhập chỉ tiêu";
    }
    
    // 2. Kiểm tra từng trường
    foreach ($chiTieuData as $maTruong => $soLuong) {
        if ($soLuong < 100) {
            $errors[] = "$maTruong: Chỉ tiêu tối thiểu 100";
        }
        if ($soLuong > 2000) {
            $errors[] = "$maTruong: Chỉ tiêu tối đa 2000";
        }
    }
    
    // 3. Kiểm tra tổng = tổng phê duyệt
    $tongNhap = array_sum($chiTieuData);
    $tongPheDuyet = $this->getTongChiTieuNamHoc($namHoc);
    if ($tongNhap !== $tongPheDuyet) {
        $errors[] = "Tổng chỉ tiêu phải = $tongPheDuyet";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
```

#### Bước 5: Submit và lưu phân bổ
```php
// controllers/nhanvienso/ChiTieuController.php
public function submit() {
    // Lấy dữ liệu
    $namHoc = $_POST['namHoc'];
    $chiTieuData = [...]; // Array [maTruong => soLuong]
    
    // Validate
    $validation = $this->model->validateChiTieuData($chiTieuData);
    if (!$validation['valid']) {
        $_SESSION['error'] = implode('<br>', $validation['errors']);
        return;
    }
    
    // Lưu vào database
    $result = $this->model->luuPhanBoChiTieu($namHoc, $chiTieuData);
    
    if ($result['success']) {
        $_SESSION['success'] = "Phân bổ thành công!";
    }
}
```

#### Bước 6: Hiển thị kết quả
- ✅ Thông báo thành công
- 📊 Cập nhật thống kê
- 📋 Hiển thị lịch sử phân bổ

---

### 3.2 Alternative Flow 1: Chỉnh sửa phân bổ đã có

**Điều kiện:** Năm học đã được phân bổ trước đó

**Luồng:**
1. Nhân viên Sở chọn năm học đã phân bổ
2. Hệ thống hiển thị chỉ tiêu đã phân bổ trong form
3. Nhân viên Sở chỉnh sửa
4. Hệ thống cảnh báo: "Năm học này đã có phân bổ. Bạn có muốn cập nhật?"
5. Nhân viên Sở xác nhận
6. Hệ thống **cập nhật** (không tạo mới)

**SQL:**
```sql
-- Nếu đã tồn tại: UPDATE
UPDATE ChiTieuTuyenSinh 
SET chiTieuPhanBo = ?, ngayCapNhat = NOW()
WHERE namHoc = ? AND maTruong = ?

-- Nếu chưa: INSERT
INSERT INTO ChiTieuTuyenSinh (...) VALUES (...)
```

---

### 3.3 Alternative Flow 2: Validation thất bại

**Các trường hợp lỗi:**

#### Lỗi 1: Tổng chỉ tiêu không khớp
```
Input: 
  - Tổng phê duyệt: 12,000
  - Tổng nhập: 11,500

Output:
  ❌ "Tổng chỉ tiêu phải bằng 12,000 (hiện tại: 11,500)"
```

#### Lỗi 2: Chỉ tiêu quá thấp
```
Input: THPT007 = 50

Output:
  ❌ "THPT007: Chỉ tiêu tối thiểu là 100"
```

#### Lỗi 3: Chỉ tiêu quá cao
```
Input: THPT001 = 2,500

Output:
  ❌ "THPT001: Chỉ tiêu tối đa là 2,000"
```

#### Lỗi 4: Chưa nhập đủ
```
Input: 
  - Tổng trường: 10
  - Đã nhập: 8

Output:
  ❌ "Vui lòng nhập chỉ tiêu cho tất cả các trường!"
```

---

### 3.4 Alternative Flow 3: Hủy phân bổ

**Luồng:**
1. Nhân viên Sở nhấn nút "Hủy"
2. Hệ thống hỏi: "Bạn có chắc muốn hủy?"
3. Nhân viên Sở xác nhận
4. Hệ thống reset form về trạng thái ban đầu
5. Hiển thị thông báo: "Đã hủy phân bổ chỉ tiêu"

---

## 4. Quy tắc nghiệp vụ (Business Rules)

### 4.1 Quy tắc về Tổng chỉ tiêu

#### BR-001: Tổng chỉ tiêu đã được Sở nhập sẵn
- **Mô tả:** Nhân viên Sở **KHÔNG được** cập nhật tổng chỉ tiêu
- **Lý do:** Tổng chỉ tiêu đã được nhập sẵn vào database (bởi cấp cao hơn trong Sở)
- **Hệ thống:** Chỉ hiển thị tổng chỉ tiêu ở chế độ **read-only**

```php
// ĐÚNG ✅
<div class="stat-card">
    <h3>Tổng chỉ tiêu</h3>
    <p><?php echo number_format($tongChiTieuPheDuyet); ?></p>
    <span class="badge">Đã nhập sẵn</span>
</div>

// SAI ❌ - Không được có form cập nhật tổng chỉ tiêu
<form method="POST">
    <input name="tongChiTieu" /> <!-- KHÔNG ĐƯỢC PHÉP -->
    <button>Cập nhật tổng chỉ tiêu</button>
</form>
```

#### BR-002: Tổng phân bổ = Tổng đã nhập
```
Σ(Chỉ tiêu trường i) = Tổng chỉ tiêu (đã nhập sẵn)

Ví dụ:
  THPT001: 1,250
  THPT002: 1,200
  ...
  THPT010: 1,100
  ────────────────
  Tổng:   12,000  ✅ (= Tổng đã nhập)
```

### 4.2 Quy tắc về Chỉ tiêu từng trường

#### BR-003: Giới hạn chỉ tiêu
```
100 ≤ Chỉ tiêu trường ≤ 2,000
```

**Lý do:**
- **Min = 100:** Trường quá nhỏ không hiệu quả
- **Max = 2,000:** Trường quá lớn khó quản lý

#### BR-004: Chỉ tiêu phải > 0
```sql
CHECK (chiTieuPhanBo > 0)
```

### 4.3 Quy tắc về Gợi ý

#### BR-005: Gợi ý dựa trên năm trước
- **Dữ liệu gốc:** Chỉ tiêu năm 2023-2024
- **Thuật toán:** Weighted Average (4 factors)
- **Kết quả:** Gợi ý hợp lý, có thể chỉnh sửa

```php
// Thuật toán gợi ý
$goiY = f(
    ChiTieuNamTruoc × 40%,      // Ví dụ: 1,200
    TyLeTangTruong × 30%,        // Ví dụ: +4.35%
    SoLuongHS × 20%,             // Ví dụ: 3,600 học sinh
    NangLucTruong × 10%          // Ví dụ: +15%
);
// Kết quả: 1,250
```

### 4.4 Quy tắc về Năm học

#### BR-006: Mỗi năm học phân bổ 1 lần
- **Mô tả:** Mỗi năm học chỉ có **1 lần phân bổ chính thức**
- **Cập nhật:** Có thể chỉnh sửa nhưng không tạo bản ghi mới

```sql
-- Check trùng
SELECT COUNT(*) FROM ChiTieuTuyenSinh 
WHERE namHoc = ? AND maTruong = ?

-- Nếu > 0: UPDATE
-- Nếu = 0: INSERT
```

---

## 5. Cấu trúc Database

### 5.1 Bảng `ChiTieuTuyenSinh`

```sql
CREATE TABLE ChiTieuTuyenSinh (
    maChiTieu VARCHAR(50) PRIMARY KEY,
    namHoc VARCHAR(10) NOT NULL,
    tongChiTieu INT NOT NULL,           -- Đã nhập sẵn vào database (read-only cho Nhân viên Sở)
    chiTieuPhanBo INT NOT NULL,         -- Do Nhân viên Sở phân bổ
    maTruong VARCHAR(50) NOT NULL,
    ngayBanHanh DATE NOT NULL,
    ngayCapNhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    maNhanVienSo VARCHAR(50),           -- Nhân viên phân bổ
    
    FOREIGN KEY (maTruong) REFERENCES TruongHoc(maTruong),
    FOREIGN KEY (maNhanVienSo) REFERENCES NhanVienSo(maNhanVienSo) 
        ON DELETE SET NULL,
    
    CHECK (chiTieuPhanBo > 0),
    CHECK (chiTieuPhanBo >= 100 AND chiTieuPhanBo <= 2000),
    
    UNIQUE KEY unique_truong_namhoc (maTruong, namHoc)
);
```

**Giải thích các trường:**
- `tongChiTieu`: **Đã được nhập sẵn** vào database, Nhân viên Sở chỉ đọc
- `chiTieuPhanBo`: **Do Nhân viên Sở** nhập, phải tuân thủ quy tắc
- `maNhanVienSo`: Lưu vết ai đã phân bổ, có thể NULL

### 5.2 Dữ liệu mẫu

```sql
-- 2023-2024 (đã phân bổ, làm cơ sở cho gợi ý)
INSERT INTO ChiTieuTuyenSinh VALUES
('CT_2023-2024_THPT001', '2023-2024', 11500, 1200, 'THPT001', '2023-06-01', NOW(), 'NVS001'),
('CT_2023-2024_THPT002', '2023-2024', 11500, 1150, 'THPT002', '2023-06-01', NOW(), 'NVS001'),
-- ...

-- 2024-2025 (chưa phân bổ, có tổng chỉ tiêu phê duyệt = 12,000)
-- Nhân viên Sở sẽ phân bổ dựa trên tổng này
```

---

## 6. Giao diện người dùng (UI/UX)

### 6.1 Layout tổng quan

```
┌──────────────────────────────────────────────────────┐
│  📊 Phân bổ chỉ tiêu tuyển sinh                      │
├──────────────────────────────────────────────────────┤
│  [Thống kê]                                          │
│  ┌──────────┬──────────┬──────────┬──────────┐     │
│  │ Tổng     │ Tổng CT  │ Đã phân  │ Tỷ lệ    │     │
│  │ trường   │ phê duyệt│ bổ       │          │     │
│  │   10     │  12,000  │  0       │   0%     │     │
│  └──────────┴──────────┴──────────┴──────────┘     │
├──────────────────────────────────────────────────────┤
│  [Form phân bổ]                                      │
│  Năm học: [2024-2025 ▼]  [Xem gợi ý]               │
│                                                      │
│  ┌────────────────────────────────────────────────┐ │
│  │ Trường    │ HS hiện tại │ Năm trước │ Gợi ý   │ │
│  ├────────────────────────────────────────────────┤ │
│  │ THPT001   │ 3,600       │ 1,200     │ 1,250   │ │
│  │ THPT002   │ 3,450       │ 1,150     │ 1,200   │ │
│  │ ...                                            │ │
│  └────────────────────────────────────────────────┘ │
│                                                      │
│  Tổng đang nhập: 0 / 12,000                         │
│  [Áp dụng tất cả gợi ý]  [Xác nhận]  [Hủy]         │
└──────────────────────────────────────────────────────┘
```

### 6.2 Thống kê cards

```html
<div class="stat-card primary">
    <h3>Tổng trường THPT</h3>
    <p class="value">10</p>
    <p class="subtext">Trường trong hệ thống</p>
</div>

<div class="stat-card success">
    <h3>Tổng chỉ tiêu</h3>
    <p class="value">12,000</p>
    <p class="subtext">Năm học 2024-2025</p>
    <span class="badge">Đã nhập sẵn</span>
</div>
```

**Lưu ý UI:**
- ❌ **KHÔNG có** form cập nhật tổng chỉ tiêu
- ✅ **CHỈ hiển thị** tổng chỉ tiêu ở chế độ read-only
- 📌 Badge "Đã nhập sẵn" để làm rõ

---

## 7. Validation Rules

### 7.1 Client-side (JavaScript)

```javascript
function validateForm() {
    let errors = [];
    let tongChiTieu = 0;
    
    // 1. Kiểm tra từng trường
    document.querySelectorAll('input[name^="chitieu_"]').forEach(input => {
        let value = parseInt(input.value) || 0;
        
        if (value <= 0) {
            errors.push(input.dataset.tenTruong + ": Chỉ tiêu phải > 0");
        }
        if (value < 100) {
            errors.push(input.dataset.tenTruong + ": Tối thiểu 100");
        }
        if (value > 2000) {
            errors.push(input.dataset.tenTruong + ": Tối đa 2,000");
        }
        
        tongChiTieu += value;
    });
    
    // 2. Kiểm tra tổng
    const tongPheDuyet = parseInt(document.getElementById('tongChiTieuPheDuyet').textContent.replace(/,/g, ''));
    if (tongChiTieu !== tongPheDuyet) {
        errors.push(`Tổng chỉ tiêu phải bằng ${tongPheDuyet.toLocaleString()} (hiện tại: ${tongChiTieu.toLocaleString()})`);
    }
    
    // 3. Hiển thị lỗi
    if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
    }
    
    return true;
}
```

### 7.2 Server-side (PHP)

```php
public function validateChiTieuData($chiTieuData, $namHoc) {
    $errors = [];
    
    // 1. Kiểm tra có dữ liệu không
    if (empty($chiTieuData)) {
        $errors[] = "Chưa nhập chỉ tiêu cho bất kỳ trường nào";
        return ['valid' => false, 'errors' => $errors];
    }
    
    // 2. Lấy tổng chỉ tiêu (đã nhập sẵn)
    $tongPheDuyet = $this->getTongChiTieuNamHoc($namHoc);
    if ($tongPheDuyet <= 0) {
        $errors[] = "Năm học $namHoc chưa có tổng chỉ tiêu trong hệ thống";
        return ['valid' => false, 'errors' => $errors];
    }
    
    // 3. Kiểm tra từng trường
    $tongNhap = 0;
    foreach ($chiTieuData as $maTruong => $soLuong) {
        if (!is_numeric($soLuong) || $soLuong <= 0) {
            $errors[] = "$maTruong: Chỉ tiêu phải là số dương";
        } elseif ($soLuong < 100) {
            $errors[] = "$maTruong: Chỉ tiêu tối thiểu 100";
        } elseif ($soLuong > 2000) {
            $errors[] = "$maTruong: Chỉ tiêu tối đa 2,000";
        }
        $tongNhap += $soLuong;
    }
    
    // 4. Kiểm tra tổng
    if ($tongNhap != $tongPheDuyet) {
        $errors[] = "Tổng chỉ tiêu phải bằng " . number_format($tongPheDuyet) . 
                    " (hiện tại: " . number_format($tongNhap) . ")";
    }
    
    // 5. Kiểm tra đã nhập đủ chưa
    $danhSachTruong = $this->getDanhSachTruong();
    if (count($chiTieuData) < count($danhSachTruong)) {
        $errors[] = "Vui lòng nhập chỉ tiêu cho tất cả " . count($danhSachTruong) . " trường";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
```

---

## 8. Security & Authorization

### 8.1 Phân quyền

```php
// middlewares/AuthGuard.php
function require_role($roles) {
    $user = current_user();
    
    if (!isset($user['role']) || !in_array($user['role'], $roles)) {
        http_response_code(403);
        die("Bạn không có quyền truy cập chức năng này");
    }
}

// controllers/nhanvienso/ChiTieuController.php
public function __construct() {
    require_role(['nhanvienso']); // CHỈ nhân viên sở
    $this->model = new ChiTieuTuyenSinh();
}
```

### 8.2 SQL Injection Prevention

```php
// SỬ DỤNG Prepared Statements
$stmt = $this->db->prepare("
    INSERT INTO ChiTieuTuyenSinh (maChiTieu, namHoc, tongChiTieu, chiTieuPhanBo, maTruong)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$maChiTieu, $namHoc, $tongChiTieu, $chiTieu, $maTruong]);
```

### 8.3 XSS Prevention

```php
// Trong view
<td><?php echo htmlspecialchars($truong['tenTruong']); ?></td>
<input value="<?php echo htmlspecialchars($chiTieu); ?>">
```

---

## 9. Testing Scenarios

### 9.1 Test Case 1: Phân bổ mới thành công

**Input:**
- Năm học: 2024-2025
- Tổng (đã nhập sẵn): 12,000
- THPT001: 1,250
- THPT002: 1,200
- ... (tổng = 12,000)

**Expected Output:**
- ✅ Lưu thành công
- ✅ Thông báo: "Phân bổ chỉ tiêu tuyển sinh thành công!"
- ✅ Cập nhật thống kê

### 9.2 Test Case 2: Tổng không khớp

**Input:**
- Tổng (đã nhập sẵn): 12,000
- Tổng nhập: 11,500

**Expected Output:**
- ❌ Thông báo lỗi: "Tổng chỉ tiêu phải bằng 12,000 (hiện tại: 11,500)"
- ❌ Không lưu dữ liệu

### 9.3 Test Case 3: Chỉ tiêu quá thấp

**Input:**
- THPT007: 50

**Expected Output:**
- ❌ "THPT007: Chỉ tiêu tối thiểu là 100"

### 9.4 Test Case 4: Cập nhật phân bổ đã có

**Input:**
- Năm học 2024-2025 đã phân bổ
- Chỉnh sửa THPT001: 1,250 → 1,300
- Chỉnh sửa THPT002: 1,200 → 1,150

**Expected Output:**
- ✅ UPDATE (không INSERT mới)
- ✅ Thông báo: "Cập nhật phân bổ thành công!"

---

## 10. Tóm tắt

### 10.1 Điểm quan trọng

✅ **Đúng đặc tả:**
1. **Tổng chỉ tiêu đã được Sở nhập sẵn** vào database → Nhân viên Sở CHỈ đọc, KHÔNG sửa
2. Nhân viên Sở CHỈ phân bổ cho từng trường
3. Tổng phân bổ PHẢI BẰNG tổng đã nhập sẵn
4. Gợi ý dựa trên dữ liệu năm 2023-2024

❌ **Sai đặc tả (ĐÃ XÓA):**
1. ~~Form cập nhật tổng chỉ tiêu~~ → ĐÃ XÓA
2. ~~Action `updateTongChiTieu`~~ → ĐÃ XÓA
3. ~~Method `capNhatTongChiTieu()`~~ → ĐÃ XÓA

### 10.2 Checklist triển khai

- [x] Xóa form "Cập nhật tổng chỉ tiêu" trong View
- [x] Xóa action `updateTongChiTieu` trong Controller
- [x] Xóa method `capNhatTongChiTieu()` trong Model
- [x] Giữ lại `getTongChiTieuNamHoc()` để đọc tổng đã nhập sẵn
- [x] Validation kiểm tra tổng phân bổ = tổng đã nhập
- [x] UI chỉ hiển thị tổng chỉ tiêu (read-only)
- [x] Thêm badge "Đã nhập sẵn" để làm rõ

---

**Phiên bản:** 2.0 (Cập nhật theo đặc tả chính xác)  
**Ngày:** 2024-10-30  
**Tác giả:** GitHub Copilot  

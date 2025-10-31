# 📋 Hướng dẫn thiết lập hệ thống với mã trường TR001-TR011

## 🎯 Tổng quan

Hệ thống sử dụng **11 trường** với mã từ **TR001** đến **TR011**:
- TR001: THPT Minh Khai
- TR002: THPT Lê Quý Đôn  
- TR003: THPT Nguyễn Huệ
- TR004: THPT Trần Phú
- TR005: THPT Phan Châu Trinh
- TR006: THPT Nguyễn Thị Minh Khai
- TR007: THPT Trần Đại Nghĩa
- TR008: THPT Marie Curie
- TR009: THPT Gia Định
- TR010: THPT Bùi Thị Xuân
- TR011: THPT Nguyễn Du

**Năm 2023-2024 cố định:** Tổng = 8,000 chỉ tiêu (đã phân bổ cho 11 trường)

---

## 🚀 CÁCH 1: Setup nhanh (Khuyên dùng)

### Chạy 1 file duy nhất:

```
Learning-Systems/database/setup_complete_TR001_TR011.sql
```

Script này sẽ:
- ✅ Tạo/cập nhật 11 trường (TR001-TR011)
- ✅ Xóa dữ liệu cũ
- ✅ Thêm Placeholder năm 2023-2024
- ✅ Phân bổ 8,000 chỉ tiêu cho 11 trường
- ✅ Hiển thị kết quả chi tiết

### Kết quả mong đợi:

```
Tổng số bản ghi: 12 (1 placeholder + 11 trường)
Placeholder: 1
Đã phân bổ: 11
Tổng chỉ tiêu: 8,000
Tổng đã phân bổ: 8,000
```

---

## 🔧 CÁCH 2: Setup từng bước

### Bước 1: Tạo 11 trường

```sql
-- File: create_truong_TR001_TR011.sql
-- Tạo/cập nhật 11 trường với mã TR001-TR011
```

### Bước 2: Cố định năm 2023-2024

```sql
-- File: fix_base_year_8000.sql
-- Phân bổ 8,000 chỉ tiêu cho 11 trường (TỰ ĐỘNG lấy từ DB)
```

### Bước 3: Dọn dẹp (nếu có lỗi)

```sql
-- File: cleanup_2023_2024.sql
-- Xóa dữ liệu trùng lặp, giữ lại đúng 12 bản ghi
```

---

## ⚙️ Đặc điểm của script

### ✅ TỰ ĐỘNG lấy mã trường

**KHÔNG hardcode** THPT001, THPT002... nữa!

Script sử dụng query động:

```sql
SET @soThuTu := 0;
INSERT INTO ChiTieuTuyenSinh (...)
SELECT 
    CONCAT('CT_2023-2024_', maTruong),
    '2023-2024',
    8000,
    CASE (@soThuTu := @soThuTu + 1)
        WHEN 1 THEN 850
        WHEN 2 THEN 820
        -- ...
    END,
    maTruong,
    'NVS001',
    '2023-02-01'
FROM Truong
ORDER BY maTruong
LIMIT 11;
```

→ Tự động lấy 11 trường đầu tiên theo thứ tự maTruong!

### ✅ Phân bổ chi tiết năm 2023-2024

| Mã trường | Tên trường | Chỉ tiêu |
|-----------|------------|----------|
| TR001 | THPT Minh Khai | 850 |
| TR002 | THPT Lê Quý Đôn | 820 |
| TR003 | THPT Nguyễn Huệ | 800 |
| TR004 | THPT Trần Phú | 780 |
| TR005 | THPT Phan Châu Trinh | 750 |
| TR006 | THPT Nguyễn Thị Minh Khai | 850 |
| TR007 | THPT Trần Đại Nghĩa | 820 |
| TR008 | THPT Marie Curie | 800 |
| TR009 | THPT Gia Định | 780 |
| TR010 | THPT Bùi Thị Xuân | 750 |
| TR011 | THPT Nguyễn Du | 800 |
| **TỔNG** | | **8,000** |

---

## 🔍 Kiểm tra sau khi chạy

### Trong phpMyAdmin:

```sql
-- Xem 11 trường
SELECT * FROM Truong ORDER BY maTruong;

-- Xem phân bổ năm 2023-2024
SELECT maTruong, chiTieuPhanBo, tongChiTieu 
FROM ChiTieuTuyenSinh 
WHERE namHoc = '2023-2024'
ORDER BY maTruong;

-- Kiểm tra tổng
SELECT 
    COUNT(*) as 'Tổng bản ghi',
    SUM(CASE WHEN maTruong IS NOT NULL THEN chiTieuPhanBo ELSE 0 END) as 'Tổng phân bổ'
FROM ChiTieuTuyenSinh
WHERE namHoc = '2023-2024';
-- Phải ra: 12 bản ghi, 8000 chỉ tiêu
```

### Trên giao diện:

1. **Refresh trình duyệt** (Ctrl + F5)
2. **Chọn năm 2023-2024:**
   - ✅ Phải thấy 11 trường với chỉ tiêu đã điền sẵn
   - ✅ Tất cả input bị DISABLE (màu xám)
   - ✅ KHÔNG có nút "Xác nhận phân bổ"
   - ✅ Có thông báo "🔒 Năm học 2023-2024 đã được cố định"

3. **Chọn năm 2024-2025:**
   - ✅ Tổng chỉ tiêu: ~8,320 (tự động tính)
   - ✅ Có gợi ý cho từng trường
   - ✅ Có thể nhập và submit bình thường

---

## ⚠️ Lưu ý quan trọng

### 1. Foreign Key Constraint

Nếu gặp lỗi:
```
Cannot delete or update a parent row: a foreign key constraint fails
```

→ Script đã tự động xử lý bằng:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- ... UPDATE các bảng ...
SET FOREIGN_KEY_CHECKS = 1;
```

### 2. Backup trước khi chạy

Xuất database trong phpMyAdmin để phòng có lỗi.

### 3. Chạy đúng thứ tự

Nếu dùng CÁCH 2 (từng bước):
1. `create_truong_TR001_TR011.sql` (tạo trường)
2. `fix_base_year_8000.sql` (phân bổ chỉ tiêu)
3. `cleanup_2023_2024.sql` (dọn dẹp nếu cần)

---

## 📂 Tổng hợp các file

```
Learning-Systems/database/
├── setup_complete_TR001_TR011.sql    ← CHẠY FILE NÀY (All-in-one)
├── create_truong_TR001_TR011.sql     (Tạo 11 trường)
├── fix_base_year_8000.sql            (Cố định năm 2023-2024)
├── cleanup_2023_2024.sql             (Dọn dẹp trùng lặp)
├── update_matruong_THPT011.sql       (Đổi mã nếu cần)
└── check_before_update.sql           (Kiểm tra FK trước khi đổi)
```

---

## 🎉 Hoàn tất!

Sau khi chạy script, hệ thống sẵn sàng với:
- ✅ 11 trường (TR001 - TR011)
- ✅ Năm 2023-2024 cố định = 8,000 (đã khóa)
- ✅ Các năm sau tự động tính toán
- ✅ Giao diện hoạt động đầy đủ

🚀 **Chạy `setup_complete_TR001_TR011.sql` và refresh trình duyệt là xong!**

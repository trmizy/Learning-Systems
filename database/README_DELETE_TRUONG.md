# 🔧 Hướng dẫn XÓA TRƯỜNG THỦ CÔNG trong phpMyAdmin

## ❌ **Lỗi bạn đang gặp:**

```
#1451 - Cannot delete or update a parent row: 
a foreign key constraint fails 
(`hethongquanlyhocsinh1`.`chitieutuyensinh`, 
CONSTRAINT `chitieutuyensinh_ibfk_1` 
FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`))
```

### 🔍 **Nguyên nhân:**

Khi bạn chạy lệnh:
```sql
DELETE FROM Truong WHERE maTruong = 'TR001';
```

MySQL từ chối vì:
- Bảng `ChiTieuTuyenSinh` đang có dòng tham chiếu đến `TR001`
- Foreign Key constraint **KHÔNG CHO PHÉP** xóa bảng cha khi bảng con còn dữ liệu

---

## ✅ **GIẢI PHÁP:**

### **CÁCH 1: Dùng SET FOREIGN_KEY_CHECKS = 0 (Nhanh nhất)**

```sql
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM Truong WHERE maTruong = 'TR001';
-- Hoặc xóa tất cả:
DELETE FROM Truong;

SET FOREIGN_KEY_CHECKS = 1;
```

**Ưu điểm:** Nhanh, đơn giản  
**Nhược điểm:** Có thể gây orphan data (dữ liệu con mất cha)

---

### **CÁCH 2: Xóa Child trước, Parent sau (An toàn nhất)**

```sql
-- Bước 1: Xóa ChiTieuTuyenSinh (bảng con)
DELETE FROM ChiTieuTuyenSinh WHERE maTruong = 'TR001';

-- Bước 2: Xóa HocSinh
DELETE FROM HocSinh WHERE maTruong = 'TR001';

-- Bước 3: Xóa LopHoc
DELETE FROM LopHoc WHERE maTruong = 'TR001';

-- Bước 4: Xóa GiaoVienBoMon
DELETE FROM GiaoVienBoMon WHERE maTruong = 'TR001';

-- Bước 5: Xóa NhanVienGiaoVu
DELETE FROM NhanVienGiaoVu WHERE maTruong = 'TR001';

-- Bước 6: Xóa BanGiamHieu
DELETE FROM BanGiamHieu WHERE maTruong = 'TR001';

-- Bước 7: Xóa TaiKhoan liên quan
DELETE FROM TaiKhoan WHERE maTruong = 'TR001';

-- Bước 8: Xóa Truong (bảng cha - SAU CÙNG)
DELETE FROM Truong WHERE maTruong = 'TR001';
```

**Ưu điểm:** An toàn, không gây orphan data  
**Nhược điểm:** Nhiều bước

---

### **CÁCH 3: Dùng Script tự động**

#### **Option A: Xóa tất cả + Tạo lại**
```
File: reset_and_create_all_data.sql
→ Xóa hết → Tạo 11 trường mới + Đầy đủ dữ liệu
```

#### **Option B: Chỉ xóa (không tạo lại)**
```
File: delete_all_truong_safely.sql
→ Chỉ xóa tất cả trường + dữ liệu liên quan
```

---

## 📋 **SỬ DỤNG TRONG phpMyAdmin:**

### **Bước 1:** Mở phpMyAdmin → Database `HeThongQuanLyHocSinh1`

### **Bước 2:** Click tab `SQL`

### **Bước 3:** Copy & Paste 1 trong 3 cách trên

### **Bước 4:** Click `Go` để thực thi

---

## 🎯 **KHUYẾN NGHỊ:**

### **Nếu muốn XÓA HẾT + TẠO LẠI:**
```sql
-- Chạy file này:
reset_and_create_all_data.sql
```

**Kết quả:**
- ✅ Xóa tất cả trường cũ
- ✅ Tạo 11 trường mới (TR001-TR011)
- ✅ Tạo 165 lớp học
- ✅ Tạo 1,100 học sinh
- ✅ Cố định năm 2023-2024 = 8,000 chỉ tiêu

### **Nếu chỉ muốn XÓA (không tạo lại):**
```sql
-- Chạy file này:
delete_all_truong_safely.sql
```

**Kết quả:**
- ✅ Xóa tất cả trường
- ✅ Xóa tất cả dữ liệu liên quan
- ❌ KHÔNG tạo lại gì

---

## 🔍 **KIỂM TRA SAU KHI XÓA:**

```sql
-- Kiểm tra còn bao nhiêu trường
SELECT COUNT(*) FROM Truong;
-- Phải ra: 0 (nếu xóa hết)

-- Kiểm tra ChiTieuTuyenSinh
SELECT COUNT(*) FROM ChiTieuTuyenSinh;
-- Phải ra: 0

-- Kiểm tra HocSinh
SELECT COUNT(*) FROM HocSinh;
-- Phải ra: 0
```

---

## ⚠️ **LƯU Ý QUAN TRỌNG:**

### **1. Backup trước khi xóa!**
```
phpMyAdmin → Export → Quick → Go
```

### **2. Hiểu rõ Foreign Key:**

```
Truong (Parent)
    ↑
    └─── ChiTieuTuyenSinh (Child)
    └─── LopHoc (Child)
    └─── HocSinh (Child)
    └─── BanGiamHieu (Child)
```

**Quy tắc:** 
- ❌ Không thể xóa Parent khi Child còn tham chiếu
- ✅ Phải xóa Child trước, hoặc tắt FK check

### **3. SET FOREIGN_KEY_CHECKS = 0:**

⚠️ **Cẩn thận:** Chỉ dùng khi bạn chắc chắn về hành động!

```sql
SET FOREIGN_KEY_CHECKS = 0;  -- Tắt check
-- ... xóa dữ liệu ...
SET FOREIGN_KEY_CHECKS = 1;  -- Bật lại (QUAN TRỌNG!)
```

---

## 📂 **Tổng hợp các file:**

```
Learning-Systems/database/
├── reset_and_create_all_data.sql       ← XÓA + TẠO LẠI (Khuyên dùng)
├── delete_all_truong_safely.sql        ← CHỈ XÓA
├── setup_complete_TR001_TR011.sql      ← CHỈ TẠO (không xóa)
└── README_DELETE_TRUONG.md             ← File này
```

---

## 🚀 **NHANH NHẤT:**

```sql
-- Copy 3 dòng này vào SQL tab trong phpMyAdmin:
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM Truong;
SET FOREIGN_KEY_CHECKS = 1;
```

✅ **XONG!**

Sau đó chạy `reset_and_create_all_data.sql` để tạo lại dữ liệu mới! 🎉

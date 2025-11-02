-- Tối ưu performance cho bảng ChiTieuTuyenSinh
CREATE INDEX idx_chitieu_namhoc ON ChiTieuTuyenSinh(namHoc);
CREATE INDEX idx_chitieu_matruong ON ChiTieuTuyenSinh(maTruong);
CREATE INDEX idx_chitieu_namhoc_matruong ON ChiTieuTuyenSinh(namHoc, maTruong);

-- Tối ưu cho bảng Truong
CREATE INDEX idx_truong_ten ON Truong(tenTruong);

-- Tối ưu cho bảng HocSinh
CREATE INDEX idx_hocsinh_mataikhoan ON HocSinh(maTaiKhoan);

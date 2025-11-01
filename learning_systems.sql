-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 10:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learning_systems`
--

-- --------------------------------------------------------

--
-- Table structure for table `bangbaocaothongke`
--

CREATE TABLE `bangbaocaothongke` (
  `maBaoCao` varchar(30) NOT NULL,
  `tieuDe` varchar(200) DEFAULT NULL,
  `loaiBaoCao` varchar(50) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL,
  `maTaiKhoanLap` varchar(50) DEFAULT NULL,
  `hocKy` varchar(10) DEFAULT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `tuNgay` date DEFAULT NULL,
  `denNgay` date DEFAULT NULL,
  `thamSoJson` text DEFAULT NULL,
  `fileUrl` varchar(500) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `ngayLap` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bangbaocaothongke`
--

INSERT INTO `bangbaocaothongke` (`maBaoCao`, `tieuDe`, `loaiBaoCao`, `maTruong`, `maTaiKhoanLap`, `hocKy`, `namHoc`, `tuNgay`, `denNgay`, `thamSoJson`, `fileUrl`, `trangThai`, `ngayLap`) VALUES
('BC_0001', 'Thống kê điểm HK1 10A1', 'DIEM', 'TR001', 'TK_ADMIN_TR001', 'HK1', '2024-2025', '2024-09-01', '2024-12-31', '{\"lop\":\"10A1\"}', '/files/bc_10a1_hk1.pdf', 'HOAN_TAT', '2025-10-30 10:27:55');

-- --------------------------------------------------------

--
-- Table structure for table `bangdiem`
--

CREATE TABLE `bangdiem` (
  `maBangDiem` varchar(30) NOT NULL,
  `diemThuongXuyen` float DEFAULT NULL,
  `diemGiuaKy` float DEFAULT NULL,
  `diemCuoiKy` float DEFAULT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `hocKy` varchar(10) DEFAULT NULL,
  `maHS` varchar(20) DEFAULT NULL,
  `maMonHoc` varchar(20) DEFAULT NULL,
  `maGV` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bangdiem`
--

INSERT INTO `bangdiem` (`maBangDiem`, `diemThuongXuyen`, `diemGiuaKy`, `diemCuoiKy`, `namHoc`, `hocKy`, `maHS`, `maMonHoc`, `maGV`) VALUES
('BD_HS001_TOAN_HK1', 8, 7.5, 8.5, '2024-2025', 'HK1', 'HS_001', 'TOAN', 'GV_TOAN_01'),
('BD_HS001_VAN_HK1', 7, 7, 7.5, '2024-2025', 'HK1', 'HS_001', 'VAN', 'GV_VAN_01'),
('BD_HS002_ANH_HK1', 7.5, 7, 8, '2024-2025', 'HK1', 'HS_002', 'ANH', 'GV_ANH_01');

-- --------------------------------------------------------

--
-- Table structure for table `bangiamhieu`
--

CREATE TABLE `bangiamhieu` (
  `maBGH` varchar(30) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `chucVu` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL,
  `maTaiKhoan` varchar(50) DEFAULT NULL,
  `ngayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bangiamhieu`
--

INSERT INTO `bangiamhieu` (`maBGH`, `hoTen`, `chucVu`, `email`, `soDienThoai`, `trangThai`, `maTruong`, `maTaiKhoan`, `ngayTao`) VALUES
('BGH_TR001', 'Ngô Đức Hiệu', 'Hiệu trưởng', 'bgh.tr001@edu.vn', '0905555002', 'ACTIVE', 'TR001', 'TK_BGH_TR001', '2025-10-30 10:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `bangphancongrade`
--

CREATE TABLE `bangphancongrade` (
  `hocKy` varchar(10) NOT NULL,
  `kyThi` varchar(50) NOT NULL,
  `soLuongDe` int(11) DEFAULT NULL,
  `thoiHan` datetime DEFAULT NULL,
  `ghiChu` varchar(255) DEFAULT NULL,
  `maGV` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bangphancongrade`
--

INSERT INTO `bangphancongrade` (`hocKy`, `kyThi`, `soLuongDe`, `thoiHan`, `ghiChu`, `maGV`) VALUES
('HK1', 'KTGK', 4, '2024-11-30 17:00:00', 'Mỗi GV soạn 1 đề', 'GV_TTBM_01');

-- --------------------------------------------------------

--
-- Table structure for table `chitieutuyensinh`
--

CREATE TABLE `chitieutuyensinh` (
  `maChiTieu` varchar(30) NOT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `tongChiTieu` int(11) DEFAULT NULL,
  `chiTieuPhanBo` int(11) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL,
  `maNhanVienSo` varchar(30) DEFAULT NULL,
  `ngayBanHanh` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitieutuyensinh`
--

INSERT INTO `chitieutuyensinh` (`maChiTieu`, `namHoc`, `tongChiTieu`, `chiTieuPhanBo`, `maTruong`, `maNhanVienSo`, `ngayBanHanh`) VALUES
('CTTS_TR001_2024', '2024-2025', 400, 400, 'TR001', 'NVS_01', '2024-06-01');

-- --------------------------------------------------------

--
-- Table structure for table `donxinphep`
--

CREATE TABLE `donxinphep` (
  `maDonXinPhep` varchar(30) NOT NULL,
  `ngay` datetime DEFAULT NULL,
  `soBuoi` int(11) DEFAULT NULL,
  `lyDo` varchar(255) DEFAULT NULL,
  `minhChungKemTheo` varchar(255) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maHS` varchar(20) DEFAULT NULL,
  `maPH` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donxinphep`
--

INSERT INTO `donxinphep` (`maDonXinPhep`, `ngay`, `soBuoi`, `lyDo`, `minhChungKemTheo`, `trangThai`, `maHS`, `maPH`) VALUES
('DXP_0001', '2024-10-05 07:30:00', 2, 'Ốm', '/files/giaykham1.pdf', 'DA_DUYET', 'HS_001', 'PH_001'),
('DXP_0002', '2024-11-15 07:30:00', 1, 'Việc gia đình', NULL, 'CHO_DUYET', 'HS_002', 'PH_002');

-- --------------------------------------------------------

--
-- Table structure for table `giaovienbomon`
--

CREATE TABLE `giaovienbomon` (
  `maGV` varchar(20) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `monHocPhuTrach` varchar(100) DEFAULT NULL,
  `trinhDoHocVan` varchar(100) DEFAULT NULL,
  `chucVu` varchar(100) DEFAULT NULL,
  `anhDaiDien` varchar(255) DEFAULT NULL,
  `soCCCD` varchar(20) DEFAULT NULL,
  `tinhTrangTaiKhoan` varchar(30) DEFAULT NULL,
  `maTaiKhoan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `giaovienbomon`
--

INSERT INTO `giaovienbomon` (`maGV`, `hoTen`, `ngaySinh`, `gioiTinh`, `email`, `soDienThoai`, `diaChi`, `monHocPhuTrach`, `trinhDoHocVan`, `chucVu`, `anhDaiDien`, `soCCCD`, `tinhTrangTaiKhoan`, `maTaiKhoan`) VALUES
('GV_ANH_01', 'Lê Minh Anh', '1990-07-12', 'Nam', 'anh01@edu.vn', '0902222003', 'Hà Nội', 'Anh', 'CN', 'GVBM', NULL, '012345678903', 'ACTIVE', 'TK_GV_ANH_01'),
('GV_TOAN_01', 'Nguyễn Văn Toán', '1985-03-05', 'Nam', 'toan01@edu.vn', '0902222001', 'Hà Nội', 'Toán', 'ThS', 'GVBM', NULL, '012345678901', 'ACTIVE', 'TK_GV_TOAN_01'),
('GV_TR001_01', 'Nguyễn Văn Minh', '1985-01-20', 'Nam', 'nguyenvanminh@test.edu.vn', '0900000003', '123 Đường A, Hà Nội', 'Toán', 'ThS', 'GVBM', NULL, '011234567890', 'ACTIVE', 'TK_GVBM_TR001'),
('GV_TR001_02', 'Trần Thị Lan', '1986-02-15', 'Nữ', 'tranthilan@test.edu.vn', '0900000004', '123 Đường A, Hà Nội', 'Văn', 'ThS', 'GVBM', NULL, '010987654321', 'ACTIVE', 'TK_GVCN_TR001'),
('GV_TR001_03', 'Phạm Quốc Tuấn', '1984-03-10', 'Nam', 'phamquoctuan@test.edu.vn', '0900000005', '123 Đường A, Hà Nội', 'Toán', 'ThS', 'GVBM', NULL, '019999999999', 'ACTIVE', 'TK_TTBM_TR001'),
('GV_TR001_07', 'Trần Văn Giang', '1990-01-01', 'Nam', 'tranvangiang@test.edu.vn', '0900000011', 'Hà Nội', 'Anh', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_08', 'Phạm Thị Hà', '1990-01-01', 'Nữ', 'phamthiha@test.edu.vn', '0900000012', 'Hà Nội', 'Sử', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_09', 'Lê Văn Hải', '1990-01-01', 'Nam', 'levanhai@test.edu.vn', '0900000013', 'Hà Nội', 'Địa', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_10', 'Nguyễn Thị Kim', '1990-01-01', 'Nữ', 'nguyenthikim@test.edu.vn', '0900000014', 'Hà Nội', 'GDCD', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_11', 'Hoàng Văn Lực', '1990-01-01', 'Nam', 'hoangvanluc@test.edu.vn', '0900000015', 'Hà Nội', 'TD', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_12', 'Trần Thị Mai', '1990-01-01', 'Nữ', 'tranthimai@test.edu.vn', '0900000016', 'Hà Nội', 'Tin', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_13', 'Nguyễn Văn Toàn', '1988-03-15', 'Nam', 'nguyenvantoan@test.edu.vn', '0900000017', 'Hà Nội', 'Toán', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_14', 'Trần Thị Hoa', '1989-04-20', 'Nữ', 'tranthihoa@test.edu.vn', '0900000018', 'Hà Nội', 'Văn', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_15', 'Lê Minh Anh', '1990-05-25', 'Nữ', 'leminhanh@test.edu.vn', '0900000019', 'Hà Nội', 'Anh', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_16', 'Phạm Văn Bình', '1987-06-10', 'Nam', 'phamvanbinh@test.edu.vn', '0900000020', 'Hà Nội', 'Lý', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_17', 'Nguyễn Thị Lan', '1991-07-15', 'Nữ', 'nguyenthilan@test.edu.vn', '0900000021', 'Hà Nội', 'Hóa', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_18', 'Vũ Thị Ngọc', '1990-08-20', 'Nữ', 'vuthingoc@test.edu.vn', '0900000022', 'Hà Nội', 'Hóa', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_19', 'Đặng Văn Nam', '1989-09-15', 'Nam', 'dangvannam@test.edu.vn', '0900000023', 'Hà Nội', 'Sinh', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_20', 'Bùi Thị Thanh', '1991-10-25', 'Nữ', 'buithithanh@test.edu.vn', '0900000024', 'Hà Nội', 'Lý', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_21', 'Trần Văn Hùng', '1988-11-30', 'Nam', 'tranvanhung@test.edu.vn', '0900000025', 'Hà Nội', 'Sử', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_22', 'Nguyễn Thị Thu', '1992-01-05', 'Nữ', 'nguyenthithu@test.edu.vn', '0900000026', 'Hà Nội', 'Địa', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_23', 'Phạm Văn Đức', '1987-02-14', 'Nam', 'phamvanduc@test.edu.vn', '0900000027', 'Hà Nội', 'GDCD', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_24', 'Lê Thị Hằng', '1993-03-22', 'Nữ', 'lethihang@test.edu.vn', '0900000028', 'Hà Nội', 'TD', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_25', 'Hoàng Văn Quân', '1985-04-10', 'Nam', 'hoangvanquan@test.edu.vn', '0900000029', 'Hà Nội', 'QP', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_26', 'Nguyễn Văn Chiến', '1986-05-18', 'Nam', 'nguyenvanchien@test.edu.vn', '0900000030', 'Hà Nội', 'QP', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TR001_27', 'Trần Văn Phúc', '1991-06-28', 'Nam', 'tranvanphuc@test.edu.vn', '0900000031', 'Hà Nội', 'Tin', 'ThS', 'GVBM', NULL, '999999999999', 'ACTIVE', NULL),
('GV_TTBM_01', 'Phạm Quốc Tổ', '1984-09-20', 'Nam', 'ttbm01@edu.vn', '0902222004', 'Hà Nội', 'Toán', 'ThS', 'GVBM', NULL, '012345678904', 'ACTIVE', 'TK_GV_TTBM_01'),
('GV_VAN_01', 'Trần Thị Văn', '1987-05-10', 'Nữ', 'van01@edu.vn', '0902222002', 'Hà Nội', 'Văn', 'ThS', 'GVBM', NULL, '012345678902', 'ACTIVE', 'TK_GV_VAN_01');

-- --------------------------------------------------------

--
-- Table structure for table `giaovienchunhiem`
--

CREATE TABLE `giaovienchunhiem` (
  `maGV` varchar(20) NOT NULL,
  `lop` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `giaovienchunhiem`
--

INSERT INTO `giaovienchunhiem` (`maGV`, `lop`) VALUES
('GV_ANH_01', 'TR001-10A1_20232024'),
('GV_TOAN_01', 'TR001-10A2_20232024'),
('GV_TR001_01', 'TR001-11A1_20232024'),
('GV_TR001_03', 'TR001-12A1_20232024'),
('GV_TR001_07', 'TR001-10A1_20252026'),
('GV_TR001_08', 'TR001-10A2_20252026'),
('GV_TR001_09', 'TR001-11A1_20252026'),
('GV_TR001_11', 'TR001-12A1_20252026'),
('GV_TR001_12', 'TR001-10A1_20262027'),
('GV_TR001_13', 'TR001-10A2_20262027'),
('GV_TR001_15', 'TR001-11A1_20262027'),
('GV_TR001_17', 'TR001-12A1_20262027'),
('GV_TR001_19', 'TR001-10A1'),
('GV_TR001_27', 'TR001-10A2');

-- --------------------------------------------------------

--
-- Table structure for table `hanhkiem`
--

CREATE TABLE `hanhkiem` (
  `maHanhKiem` varchar(30) NOT NULL,
  `soBuoiNghiCoPhep` int(11) DEFAULT NULL,
  `soBuoiNghiKhongCoPhep` int(11) DEFAULT NULL,
  `soLanViPham` int(11) DEFAULT NULL,
  `hocKy` varchar(10) DEFAULT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `loaiHanhKiem` varchar(20) DEFAULT NULL,
  `maHS` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hanhkiem`
--

INSERT INTO `hanhkiem` (`maHanhKiem`, `soBuoiNghiCoPhep`, `soBuoiNghiKhongCoPhep`, `soLanViPham`, `hocKy`, `namHoc`, `loaiHanhKiem`, `maHS`) VALUES
('HK_001', 1, 0, 0, 'HK1', '2024-2025', 'Tốt', 'HS_001'),
('HK_002', 0, 1, 1, 'HK1', '2024-2025', 'Khá', 'HS_002'),
('HK_003', 2, 0, 0, 'HK1', '2024-2025', 'Tốt', 'HS_003');

-- --------------------------------------------------------

--
-- Table structure for table `hocluc`
--

CREATE TABLE `hocluc` (
  `maHocLuc` varchar(30) NOT NULL,
  `diemTrungBinh` float DEFAULT NULL,
  `hanhKiem` varchar(20) DEFAULT NULL,
  `xepLoaiHocLuc` varchar(20) DEFAULT NULL,
  `maHS` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hocluc`
--

INSERT INTO `hocluc` (`maHocLuc`, `diemTrungBinh`, `hanhKiem`, `xepLoaiHocLuc`, `maHS`) VALUES
('HL_001', 8.2, 'Tốt', 'Giỏi', 'HS_001'),
('HL_002', 7, 'Khá', 'Khá', 'HS_002'),
('HL_003', 6.1, 'Trung bình', 'TB', 'HS_003');

-- --------------------------------------------------------

--
-- Table structure for table `hocsinh`
--

CREATE TABLE `hocsinh` (
  `maHS` varchar(20) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `soCCCD` varchar(20) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maTaiKhoan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hocsinh`
--

INSERT INTO `hocsinh` (`maHS`, `hoTen`, `ngaySinh`, `soCCCD`, `diaChi`, `email`, `gioiTinh`, `sdt`, `trangThai`, `maTaiKhoan`) VALUES
('HS_001', 'Ngô Nhật Minh', '2009-01-15', '010011223344', 'Hà Nội', 'hs001@edu.vn', 'Nam', '0903333001', 'DANG_HOC', 'TK_HS_001'),
('HS_002', 'Phạm Gia Hân', '2009-04-20', '010011223345', 'Hà Nội', 'hs002@edu.vn', 'Nữ', '0903333002', 'DANG_HOC', 'TK_HS_002'),
('HS_003', 'Nguyễn Mạnh Quân', '2009-07-25', '010011223346', 'Hà Nội', 'hs003@edu.vn', 'Nam', '0903333003', 'DANG_HOC', 'TK_HS_003'),
('HS_004', 'Trần Khánh Linh', '2008-10-30', '010011223347', 'Hà Nội', NULL, 'Nữ', '0903333004', 'DANG_HOC', NULL),
('HS_005', 'Đỗ Hải Đăng', '2008-12-05', '010011223348', 'Hà Nội', NULL, 'Nam', '0903333005', 'DANG_HOC', NULL),
('HS_TR001_001', 'Học sinh Test 1', '2007-09-01', '010011234567', '123 Đường A, Hà Nội', 'hs@test.edu.vn', 'Nam', '0900000006', 'DANG_HOC', 'TK_HS_TR001_001');

-- --------------------------------------------------------

--
-- Table structure for table `khoi`
--

CREATE TABLE `khoi` (
  `maKhoi` varchar(20) NOT NULL,
  `khoiLop` varchar(10) DEFAULT NULL,
  `soLuongLop` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khoi`
--

INSERT INTO `khoi` (`maKhoi`, `khoiLop`, `soLuongLop`) VALUES
('10', '10', 2),
('11', '11', 1),
('12', '12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lichsuthaydoihosogv`
--

CREATE TABLE `lichsuthaydoihosogv` (
  `maHoSo` varchar(30) NOT NULL,
  `hanhDong` varchar(20) DEFAULT NULL,
  `thoiGian` date DEFAULT NULL,
  `nguoiThucHien` varchar(150) DEFAULT NULL,
  `maGV` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lichsuthaydoihosogv`
--

INSERT INTO `lichsuthaydoihosogv` (`maHoSo`, `hanhDong`, `thoiGian`, `nguoiThucHien`, `maGV`) VALUES
('HSO_GV_0001', 'UPDATE', '2024-10-01', 'admin.tr001', 'GV_TOAN_01');

-- --------------------------------------------------------

--
-- Table structure for table `lophoc`
--

CREATE TABLE `lophoc` (
  `maLop` varchar(20) NOT NULL,
  `tenLop` varchar(100) DEFAULT NULL,
  `siSo` int(11) DEFAULT NULL,
  `khoi` varchar(20) NOT NULL,
  `namHoc` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lophoc`
--

INSERT INTO `lophoc` (`maLop`, `tenLop`, `siSo`, `khoi`, `namHoc`) VALUES
('TR001-10A1', '10A1', 45, '10', '2024-2025'),
('TR001-10A1_20232024', '10A1', 45, '10', '2023-2024'),
('TR001-10A1_20252026', '10A1', 45, '10', '2025-2026'),
('TR001-10A1_20262027', '10A1', 45, '10', '2026-2027'),
('TR001-10A2', '10A2', 43, '10', '2024-2025'),
('TR001-10A2_20232024', '10A2', 43, '10', '2023-2024'),
('TR001-10A2_20252026', '10A2', 43, '10', '2025-2026'),
('TR001-10A2_20262027', '10A2', 43, '10', '2026-2027'),
('TR001-10A3', '10A3', 42, '10', '2024-2025'),
('TR001-10A4', '10A4', 40, '10', '2024-2025'),
('TR001-10A5', '10A5', 44, '10', '2024-2025'),
('TR001-10A6', '10A6', 43, '10', '2024-2025'),
('TR001-11A1', '11A1', 44, '11', '2024-2025'),
('TR001-11A1_20232024', '11A1', 44, '11', '2023-2024'),
('TR001-11A1_20252026', '11A1', 44, '11', '2025-2026'),
('TR001-11A1_20262027', '11A1', 44, '11', '2026-2027'),
('TR001-11A2', '11A2', 42, '11', '2024-2025'),
('TR001-11A3', '11A3', 41, '11', '2024-2025'),
('TR001-11A4', '11A4', 43, '11', '2024-2025'),
('TR001-11A5', '11A5', 44, '11', '2024-2025'),
('TR001-12A1', '12A1', 45, '12', '2024-2025'),
('TR001-12A1_20232024', '12A1', 45, '12', '2023-2024'),
('TR001-12A1_20252026', '12A1', 45, '12', '2025-2026'),
('TR001-12A1_20262027', '12A1', 45, '12', '2026-2027'),
('TR001-12A2', '12A2', 43, '12', '2024-2025'),
('TR001-12A3', '12A3', 42, '12', '2024-2025'),
('TR001-12A4', '12A4', 44, '12', '2024-2025'),
('TR001-12A5', '12A5', 41, '12', '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `monhoc`
--

CREATE TABLE `monhoc` (
  `maMonHoc` varchar(20) NOT NULL,
  `tenMon` varchar(100) DEFAULT NULL,
  `soTietTuan` int(11) DEFAULT NULL,
  `loaiMonHoc` varchar(50) DEFAULT NULL,
  `hocKy` varchar(10) DEFAULT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `moTa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monhoc`
--

INSERT INTO `monhoc` (`maMonHoc`, `tenMon`, `soTietTuan`, `loaiMonHoc`, `hocKy`, `namHoc`, `moTa`) VALUES
('ANH', 'Tiếng Anh', 3, 'Bắt buộc', 'HK1', '2024-2025', 'Ngoại ngữ'),
('DIA', 'Địa lý', 2, 'Tự chọn', 'HK1', '2024-2025', 'KHXH'),
('GDCD', 'Giáo dục công dân', 1, 'Tự chọn', 'HK1', '2024-2025', 'KHXH'),
('HOA', 'Hóa học', 3, 'Tự chọn', 'HK1', '2024-2025', 'KHTN'),
('LY', 'Vật lý', 3, 'Tự chọn', 'HK1', '2024-2025', 'KHTN'),
('QP', 'Quốc phòng', 1, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Quốc phòng'),
('SINH', 'Sinh học', 2, 'Tự chọn', 'HK1', '2024-2025', 'KHTN'),
('SU', 'Lịch sử', 2, 'Tự chọn', 'HK1', '2024-2025', 'KHXH'),
('TD', 'Thể dục', 2, 'Chính khóa', 'Cả năm', '2024-2025', 'Môn Thể dục'),
('TIN', 'Tin học', 2, 'Tự chọn', 'HK1', '2024-2025', 'CNTT cơ bản'),
('TOAN', 'Toán', 5, 'Bắt buộc', 'HK1', '2024-2025', 'Môn Toán THPT'),
('VAN', 'Ngữ văn', 4, 'Bắt buộc', 'HK1', '2024-2025', 'Môn Văn THPT');

-- --------------------------------------------------------

--
-- Table structure for table `nguyenvong`
--

CREATE TABLE `nguyenvong` (
  `maNguyenVong` varchar(30) NOT NULL,
  `thuTuUuTien` int(11) DEFAULT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `maThiSinh` varchar(30) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguyenvong`
--

INSERT INTO `nguyenvong` (`maNguyenVong`, `thuTuUuTien`, `soLuong`, `maThiSinh`, `maTruong`) VALUES
('NV_0001', 1, 1, 'TS_0001', 'TR001'),
('NV_0002', 1, 1, 'TS_0002', 'TR002');

-- --------------------------------------------------------

--
-- Table structure for table `nhanvienphonggiaovu`
--

CREATE TABLE `nhanvienphonggiaovu` (
  `maNVGiaoVu` varchar(30) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `chucDanh` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL,
  `maTaiKhoan` varchar(50) DEFAULT NULL,
  `ngayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nhanvienphonggiaovu`
--

INSERT INTO `nhanvienphonggiaovu` (`maNVGiaoVu`, `hoTen`, `chucDanh`, `email`, `soDienThoai`, `trangThai`, `maTruong`, `maTaiKhoan`, `ngayTao`) VALUES
('NVGV_TR001', 'Lý Phương Giáo Vụ', 'Chuyên viên', 'admin.tr001@edu.vn', '0905555003', 'ACTIVE', 'TR001', 'TK_ADMIN_TR001', '2025-10-30 10:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `nhanvienso`
--

CREATE TABLE `nhanvienso` (
  `maNhanVienSo` varchar(30) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `chucVu` varchar(100) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maTaiKhoan` varchar(50) DEFAULT NULL,
  `ngayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nhanvienso`
--

INSERT INTO `nhanvienso` (`maNhanVienSo`, `hoTen`, `email`, `soDienThoai`, `chucVu`, `trangThai`, `maTaiKhoan`, `ngayTao`) VALUES
('NVS001', 'Nguyễn Quốc Dũng', 'nvs@test.edu.vn', '0900999001', 'Chuyên viên', 'ACTIVE', 'TK_NVS_01', '2025-10-30 10:20:15'),
('NVS_01', 'Đặng Quốc Sở', 'nvs.tr001@edu.vn', '0905555001', 'Chuyên viên', 'ACTIVE', 'TK_NVS_01', '2025-10-30 10:27:55');

-- --------------------------------------------------------

--
-- Table structure for table `phanconggiangday`
--

CREATE TABLE `phanconggiangday` (
  `maPhanCong` varchar(50) NOT NULL,
  `maLop` varchar(20) NOT NULL,
  `maMonHoc` varchar(20) NOT NULL,
  `maGV` varchar(20) NOT NULL,
  `namHoc` varchar(15) DEFAULT NULL,
  `hocKy` varchar(10) DEFAULT NULL,
  `ghiChu` varchar(255) DEFAULT NULL,
  `ngayPhanCong` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phanconggiangday`
--

INSERT INTO `phanconggiangday` (`maPhanCong`, `maLop`, `maMonHoc`, `maGV`, `namHoc`, `hocKy`, `ghiChu`, `ngayPhanCong`) VALUES
('', 'TR001-10A1_20232024', 'GDCD', 'GV_TR001_10', '2023-2024', 'HK1', NULL, '2025-10-30 12:42:48'),
('PC_TR001-10A1_GDCD_1761807516', 'TR001-10A1', 'GDCD', 'GV_TR001_10', '2024-2025', 'HK1', NULL, '2025-10-30 13:58:36'),
('PC_TR001-10A2_GDCD_1761813711', 'TR001-10A2', 'GDCD', 'GV_TR001_10', '2024-2025', 'HK1', NULL, '2025-10-30 15:41:51'),
('PC_TR001-10A2_HOA_1761814544', 'TR001-10A2', 'HOA', 'GV_TR001_17', '2024-2025', 'HK1', NULL, '2025-10-30 15:55:44'),
('PC_TR001-10A3_GDCD_1761810768', 'TR001-10A3', 'GDCD', 'GV_TR001_10', '2024-2025', 'HK1', NULL, '2025-10-30 14:52:48'),
('PC_TR001-10A4_HOA_1761814569', 'TR001-10A4', 'HOA', 'GV_TR001_17', '2024-2025', 'HK1', NULL, '2025-10-30 15:56:09'),
('PC_TR001-10A5_HOA_1761814579', 'TR001-10A5', 'HOA', 'GV_TR001_17', '2024-2025', 'HK1', NULL, '2025-10-30 15:56:19'),
('PC_TR001-11A1_GDCD_1761798049', 'TR001-11A1', 'GDCD', 'GV_TR001_10', '2024-2025', 'HK1', NULL, '2025-10-30 11:20:49'),
('PC_TR001-12A1_GDCD_1761796177', 'TR001-12A1', 'GDCD', 'GV_TR001_10', '2024-2025', 'HK1', NULL, '2025-10-30 10:49:37'),
('PC_TR001-12A1_SU_1761796119', 'TR001-12A1', 'SU', 'GV_TR001_08', '2024-2025', 'HK1', NULL, '2025-10-30 10:48:39'),
('TR001-10A1_20232024_ANH_HK1_20232024', 'TR001-10A1_20232024', 'ANH', 'GV_TR001_15', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_HOA_HK1_20232024', 'TR001-10A1_20232024', 'HOA', 'GV_TR001_17', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_LY_HK1_20232024', 'TR001-10A1_20232024', 'LY', 'GV_TR001_16', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_QP_HK1_20232024', 'TR001-10A1_20232024', 'QP', 'GV_TR001_25', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_SINH_HK1_20232024', 'TR001-10A1_20232024', 'SINH', 'GV_TR001_19', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_SU_HK1_20232024', 'TR001-10A1_20232024', 'SU', 'GV_TR001_08', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_TD_HK1_20232024', 'TR001-10A1_20232024', 'TD', 'GV_TR001_11', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_TIN_HK1_20232024', 'TR001-10A1_20232024', 'TIN', 'GV_TR001_12', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_TOAN_HK1_20232024', 'TR001-10A1_20232024', 'TOAN', 'GV_TOAN_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20232024_VAN_HK1_20232024', 'TR001-10A1_20232024', 'VAN', 'GV_VAN_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_ANH_HK1_20252026', 'TR001-10A1_20252026', 'ANH', 'GV_TR001_07', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_GDCD_HK1_20252026', 'TR001-10A1_20252026', 'GDCD', 'GV_TR001_23', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_HOA_HK1_20252026', 'TR001-10A1_20252026', 'HOA', 'GV_TR001_17', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_LY_HK1_20252026', 'TR001-10A1_20252026', 'LY', 'GV_TR001_16', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_QP_HK1_20252026', 'TR001-10A1_20252026', 'QP', 'GV_TR001_26', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_SINH_HK1_20252026', 'TR001-10A1_20252026', 'SINH', 'GV_TR001_19', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_SU_HK1_20252026', 'TR001-10A1_20252026', 'SU', 'GV_TR001_21', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_TD_HK1_20252026', 'TR001-10A1_20252026', 'TD', 'GV_TR001_24', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_TIN_HK1_20252026', 'TR001-10A1_20252026', 'TIN', 'GV_TR001_27', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_TOAN_HK1_20252026', 'TR001-10A1_20252026', 'TOAN', 'GV_TOAN_01', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20252026_VAN_HK1_20252026', 'TR001-10A1_20252026', 'VAN', 'GV_VAN_01', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_ANH_HK1_20262027', 'TR001-10A1_20262027', 'ANH', 'GV_TR001_15', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_GDCD_HK1_20262027', 'TR001-10A1_20262027', 'GDCD', 'GV_TR001_10', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_HOA_HK1_20262027', 'TR001-10A1_20262027', 'HOA', 'GV_TR001_17', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_LY_HK1_20262027', 'TR001-10A1_20262027', 'LY', 'GV_TR001_20', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_QP_HK1_20262027', 'TR001-10A1_20262027', 'QP', 'GV_TR001_26', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_SINH_HK1_20262027', 'TR001-10A1_20262027', 'SINH', 'GV_TR001_19', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_SU_HK1_20262027', 'TR001-10A1_20262027', 'SU', 'GV_TR001_21', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_TD_HK1_20262027', 'TR001-10A1_20262027', 'TD', 'GV_TR001_11', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_TIN_HK1_20262027', 'TR001-10A1_20262027', 'TIN', 'GV_TR001_27', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_TOAN_HK1_20262027', 'TR001-10A1_20262027', 'TOAN', 'GV_TR001_03', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_20262027_VAN_HK1_20262027', 'TR001-10A1_20262027', 'VAN', 'GV_TR001_14', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_ANH_HK1_20242025', 'TR001-10A1', 'ANH', 'GV_TR001_15', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_HOA_HK1_20242025', 'TR001-10A1', 'HOA', 'GV_TR001_17', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_LY_HK1_20242025', 'TR001-10A1', 'LY', 'GV_TR001_20', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_QP_HK1_20242025', 'TR001-10A1', 'QP', 'GV_TR001_26', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_SINH_HK1_20242025', 'TR001-10A1', 'SINH', 'GV_TR001_19', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_SU_HK1_20242025', 'TR001-10A1', 'SU', 'GV_TR001_21', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_TD_HK1_20242025', 'TR001-10A1', 'TD', 'GV_TR001_24', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_TIN_HK1_20242025', 'TR001-10A1', 'TIN', 'GV_TR001_27', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_TOAN_HK1_20242025', 'TR001-10A1', 'TOAN', 'GV_TOAN_01', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A1_VAN_HK1_20242025', 'TR001-10A1', 'VAN', 'GV_TR001_02', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_ANH_HK1_20232024', 'TR001-10A2_20232024', 'ANH', 'GV_ANH_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_GDCD_HK1_20232024', 'TR001-10A2_20232024', 'GDCD', 'GV_TR001_10', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_HOA_HK1_20232024', 'TR001-10A2_20232024', 'HOA', 'GV_TR001_18', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_LY_HK1_20232024', 'TR001-10A2_20232024', 'LY', 'GV_TR001_20', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_QP_HK1_20232024', 'TR001-10A2_20232024', 'QP', 'GV_TR001_25', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_SINH_HK1_20232024', 'TR001-10A2_20232024', 'SINH', 'GV_TR001_19', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_SU_HK1_20232024', 'TR001-10A2_20232024', 'SU', 'GV_TR001_21', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_TD_HK1_20232024', 'TR001-10A2_20232024', 'TD', 'GV_TR001_11', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_TIN_HK1_20232024', 'TR001-10A2_20232024', 'TIN', 'GV_TR001_27', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_TOAN_HK1_20232024', 'TR001-10A2_20232024', 'TOAN', 'GV_TOAN_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20232024_VAN_HK1_20232024', 'TR001-10A2_20232024', 'VAN', 'GV_TR001_02', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_ANH_HK1_20252026', 'TR001-10A2_20252026', 'ANH', 'GV_ANH_01', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_GDCD_HK1_20252026', 'TR001-10A2_20252026', 'GDCD', 'GV_TR001_10', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_HOA_HK1_20252026', 'TR001-10A2_20252026', 'HOA', 'GV_TR001_18', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_LY_HK1_20252026', 'TR001-10A2_20252026', 'LY', 'GV_TR001_20', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_QP_HK1_20252026', 'TR001-10A2_20252026', 'QP', 'GV_TR001_26', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_SINH_HK1_20252026', 'TR001-10A2_20252026', 'SINH', 'GV_TR001_19', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_SU_HK1_20252026', 'TR001-10A2_20252026', 'SU', 'GV_TR001_08', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_TD_HK1_20252026', 'TR001-10A2_20252026', 'TD', 'GV_TR001_11', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_TIN_HK1_20252026', 'TR001-10A2_20252026', 'TIN', 'GV_TR001_12', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_TOAN_HK1_20252026', 'TR001-10A2_20252026', 'TOAN', 'GV_TR001_01', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20252026_VAN_HK1_20252026', 'TR001-10A2_20252026', 'VAN', 'GV_TR001_02', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_ANH_HK1_20262027', 'TR001-10A2_20262027', 'ANH', 'GV_TR001_07', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_GDCD_HK1_20262027', 'TR001-10A2_20262027', 'GDCD', 'GV_TR001_23', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_HOA_HK1_20262027', 'TR001-10A2_20262027', 'HOA', 'GV_TR001_18', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_LY_HK1_20262027', 'TR001-10A2_20262027', 'LY', 'GV_TR001_16', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_QP_HK1_20262027', 'TR001-10A2_20262027', 'QP', 'GV_TR001_26', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_SINH_HK1_20262027', 'TR001-10A2_20262027', 'SINH', 'GV_TR001_19', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_SU_HK1_20262027', 'TR001-10A2_20262027', 'SU', 'GV_TR001_21', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_TD_HK1_20262027', 'TR001-10A2_20262027', 'TD', 'GV_TR001_24', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_TIN_HK1_20262027', 'TR001-10A2_20262027', 'TIN', 'GV_TR001_27', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_TOAN_HK1_20262027', 'TR001-10A2_20262027', 'TOAN', 'GV_TOAN_01', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_20262027_VAN_HK1_20262027', 'TR001-10A2_20262027', 'VAN', 'GV_TR001_14', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_ANH_HK1_20242025', 'TR001-10A2', 'ANH', 'GV_TR001_15', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_LY_HK1_20242025', 'TR001-10A2', 'LY', 'GV_TR001_20', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_QP_HK1_20242025', 'TR001-10A2', 'QP', 'GV_TR001_25', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_SINH_HK1_20242025', 'TR001-10A2', 'SINH', 'GV_TR001_19', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_TD_HK1_20242025', 'TR001-10A2', 'TD', 'GV_TR001_24', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_TIN_HK1_20242025', 'TR001-10A2', 'TIN', 'GV_TR001_12', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-10A2_TOAN_HK1_20242025', 'TR001-10A2', 'TOAN', 'GV_TTBM_01', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_ANH_HK1_20232024', 'TR001-11A1_20232024', 'ANH', 'GV_TR001_15', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_GDCD_HK1_20232024', 'TR001-11A1_20232024', 'GDCD', 'GV_TR001_23', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_HOA_HK1_20232024', 'TR001-11A1_20232024', 'HOA', 'GV_TR001_17', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_LY_HK1_20232024', 'TR001-11A1_20232024', 'LY', 'GV_TR001_16', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_QP_HK1_20232024', 'TR001-11A1_20232024', 'QP', 'GV_TR001_25', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_SINH_HK1_20232024', 'TR001-11A1_20232024', 'SINH', 'GV_TR001_19', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_SU_HK1_20232024', 'TR001-11A1_20232024', 'SU', 'GV_TR001_08', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_TD_HK1_20232024', 'TR001-11A1_20232024', 'TD', 'GV_TR001_24', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_TIN_HK1_20232024', 'TR001-11A1_20232024', 'TIN', 'GV_TR001_27', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_TOAN_HK1_20232024', 'TR001-11A1_20232024', 'TOAN', 'GV_TR001_03', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20232024_VAN_HK1_20232024', 'TR001-11A1_20232024', 'VAN', 'GV_VAN_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_ANH_HK1_20252026', 'TR001-11A1_20252026', 'ANH', 'GV_TR001_07', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_GDCD_HK1_20252026', 'TR001-11A1_20252026', 'GDCD', 'GV_TR001_10', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_HOA_HK1_20252026', 'TR001-11A1_20252026', 'HOA', 'GV_TR001_17', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_LY_HK1_20252026', 'TR001-11A1_20252026', 'LY', 'GV_TR001_16', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_QP_HK1_20252026', 'TR001-11A1_20252026', 'QP', 'GV_TR001_26', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_SINH_HK1_20252026', 'TR001-11A1_20252026', 'SINH', 'GV_TR001_19', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_SU_HK1_20252026', 'TR001-11A1_20252026', 'SU', 'GV_TR001_08', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_TD_HK1_20252026', 'TR001-11A1_20252026', 'TD', 'GV_TR001_24', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_TIN_HK1_20252026', 'TR001-11A1_20252026', 'TIN', 'GV_TR001_12', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_TOAN_HK1_20252026', 'TR001-11A1_20252026', 'TOAN', 'GV_TR001_13', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20252026_VAN_HK1_20252026', 'TR001-11A1_20252026', 'VAN', 'GV_TR001_14', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_ANH_HK1_20262027', 'TR001-11A1_20262027', 'ANH', 'GV_ANH_01', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_GDCD_HK1_20262027', 'TR001-11A1_20262027', 'GDCD', 'GV_TR001_23', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_HOA_HK1_20262027', 'TR001-11A1_20262027', 'HOA', 'GV_TR001_18', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_LY_HK1_20262027', 'TR001-11A1_20262027', 'LY', 'GV_TR001_20', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_QP_HK1_20262027', 'TR001-11A1_20262027', 'QP', 'GV_TR001_25', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_SINH_HK1_20262027', 'TR001-11A1_20262027', 'SINH', 'GV_TR001_19', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_SU_HK1_20262027', 'TR001-11A1_20262027', 'SU', 'GV_TR001_08', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_TD_HK1_20262027', 'TR001-11A1_20262027', 'TD', 'GV_TR001_24', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_TIN_HK1_20262027', 'TR001-11A1_20262027', 'TIN', 'GV_TR001_12', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_TOAN_HK1_20262027', 'TR001-11A1_20262027', 'TOAN', 'GV_TOAN_01', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_20262027_VAN_HK1_20262027', 'TR001-11A1_20262027', 'VAN', 'GV_TR001_14', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_ANH_HK1_20242025', 'TR001-11A1', 'ANH', 'GV_ANH_01', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_HOA_HK1_20242025', 'TR001-11A1', 'HOA', 'GV_TR001_17', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_LY_HK1_20242025', 'TR001-11A1', 'LY', 'GV_TR001_16', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_QP_HK1_20242025', 'TR001-11A1', 'QP', 'GV_TR001_26', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_SINH_HK1_20242025', 'TR001-11A1', 'SINH', 'GV_TR001_19', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_SU_HK1_20242025', 'TR001-11A1', 'SU', 'GV_TR001_21', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_TD_HK1_20242025', 'TR001-11A1', 'TD', 'GV_TR001_24', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_TIN_HK1_20242025', 'TR001-11A1', 'TIN', 'GV_TR001_27', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_TOAN_HK1_20242025', 'TR001-11A1', 'TOAN', 'GV_TOAN_01', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-11A1_VAN_HK1_20242025', 'TR001-11A1', 'VAN', 'GV_TR001_14', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_ANH_HK1_20232024', 'TR001-12A1_20232024', 'ANH', 'GV_TR001_15', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_GDCD_HK1_20232024', 'TR001-12A1_20232024', 'GDCD', 'GV_TR001_10', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_HOA_HK1_20232024', 'TR001-12A1_20232024', 'HOA', 'GV_TR001_17', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_LY_HK1_20232024', 'TR001-12A1_20232024', 'LY', 'GV_TR001_20', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_QP_HK1_20232024', 'TR001-12A1_20232024', 'QP', 'GV_TR001_25', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_SINH_HK1_20232024', 'TR001-12A1_20232024', 'SINH', 'GV_TR001_19', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_SU_HK1_20232024', 'TR001-12A1_20232024', 'SU', 'GV_TR001_21', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_TD_HK1_20232024', 'TR001-12A1_20232024', 'TD', 'GV_TR001_24', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_TIN_HK1_20232024', 'TR001-12A1_20232024', 'TIN', 'GV_TR001_12', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_TOAN_HK1_20232024', 'TR001-12A1_20232024', 'TOAN', 'GV_TR001_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20232024_VAN_HK1_20232024', 'TR001-12A1_20232024', 'VAN', 'GV_VAN_01', '2023-2024', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_ANH_HK1_20252026', 'TR001-12A1_20252026', 'ANH', 'GV_TR001_07', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_GDCD_HK1_20252026', 'TR001-12A1_20252026', 'GDCD', 'GV_TR001_10', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_HOA_HK1_20252026', 'TR001-12A1_20252026', 'HOA', 'GV_TR001_17', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_LY_HK1_20252026', 'TR001-12A1_20252026', 'LY', 'GV_TR001_16', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_QP_HK1_20252026', 'TR001-12A1_20252026', 'QP', 'GV_TR001_25', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_SINH_HK1_20252026', 'TR001-12A1_20252026', 'SINH', 'GV_TR001_19', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_SU_HK1_20252026', 'TR001-12A1_20252026', 'SU', 'GV_TR001_08', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_TD_HK1_20252026', 'TR001-12A1_20252026', 'TD', 'GV_TR001_11', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_TIN_HK1_20252026', 'TR001-12A1_20252026', 'TIN', 'GV_TR001_27', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_TOAN_HK1_20252026', 'TR001-12A1_20252026', 'TOAN', 'GV_TR001_03', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20252026_VAN_HK1_20252026', 'TR001-12A1_20252026', 'VAN', 'GV_TR001_02', '2025-2026', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_ANH_HK1_20262027', 'TR001-12A1_20262027', 'ANH', 'GV_ANH_01', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_GDCD_HK1_20262027', 'TR001-12A1_20262027', 'GDCD', 'GV_TR001_10', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_HOA_HK1_20262027', 'TR001-12A1_20262027', 'HOA', 'GV_TR001_18', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_LY_HK1_20262027', 'TR001-12A1_20262027', 'LY', 'GV_TR001_16', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_QP_HK1_20262027', 'TR001-12A1_20262027', 'QP', 'GV_TR001_26', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_SINH_HK1_20262027', 'TR001-12A1_20262027', 'SINH', 'GV_TR001_19', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_SU_HK1_20262027', 'TR001-12A1_20262027', 'SU', 'GV_TR001_08', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_TD_HK1_20262027', 'TR001-12A1_20262027', 'TD', 'GV_TR001_24', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_TIN_HK1_20262027', 'TR001-12A1_20262027', 'TIN', 'GV_TR001_12', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_TOAN_HK1_20262027', 'TR001-12A1_20262027', 'TOAN', 'GV_TR001_03', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_20262027_VAN_HK1_20262027', 'TR001-12A1_20262027', 'VAN', 'GV_TR001_02', '2026-2027', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_ANH_HK1_20242025', 'TR001-12A1', 'ANH', 'GV_TR001_07', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_HOA_HK1_20242025', 'TR001-12A1', 'HOA', 'GV_TR001_18', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_LY_HK1_20242025', 'TR001-12A1', 'LY', 'GV_TR001_16', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_QP_HK1_20242025', 'TR001-12A1', 'QP', 'GV_TR001_26', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_SINH_HK1_20242025', 'TR001-12A1', 'SINH', 'GV_TR001_19', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_TD_HK1_20242025', 'TR001-12A1', 'TD', 'GV_TR001_11', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_TIN_HK1_20242025', 'TR001-12A1', 'TIN', 'GV_TR001_12', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_TOAN_HK1_20242025', 'TR001-12A1', 'TOAN', 'GV_TTBM_01', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27'),
('TR001-12A1_VAN_HK1_20242025', 'TR001-12A1', 'VAN', 'GV_TR001_14', '2024-2025', 'HK1', NULL, '2025-10-30 12:46:27');

-- --------------------------------------------------------

--
-- Table structure for table `phancongphonghoc`
--

CREATE TABLE `phancongphonghoc` (
  `maPhanCong` varchar(50) NOT NULL,
  `maPhong` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `maLop` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namHoc` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ngayPhanCong` datetime DEFAULT current_timestamp(),
  `ghiChu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phancongphonghoc`
--

INSERT INTO `phancongphonghoc` (`maPhanCong`, `maPhong`, `maLop`, `namHoc`, `ngayPhanCong`, `ghiChu`) VALUES
('P101_TR001-10A1_20242025', 'P101', 'TR001-10A1', '2024-2025', '2025-10-30 14:51:43', NULL),
('P102_TR001-10A2_20242025', 'P102', 'TR001-10A2', '2024-2025', '2025-10-30 15:55:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phieudangkytohopmon`
--

CREATE TABLE `phieudangkytohopmon` (
  `maDKToHop` varchar(30) NOT NULL,
  `ngayDK` datetime DEFAULT NULL,
  `maHS` varchar(20) NOT NULL,
  `maToHop` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieudangkytohopmon`
--

INSERT INTO `phieudangkytohopmon` (`maDKToHop`, `ngayDK`, `maHS`, `maToHop`) VALUES
('DKTH_0001', '2024-09-15 10:00:00', 'HS_001', 'KHTN'),
('DKTH_0002', '2024-09-16 10:00:00', 'HS_002', 'KHXH');

-- --------------------------------------------------------

--
-- Table structure for table `phonghoc`
--

CREATE TABLE `phonghoc` (
  `maPhong` varchar(20) NOT NULL,
  `tenPhong` varchar(100) DEFAULT NULL,
  `sucChua` int(11) DEFAULT NULL,
  `dangGiaoChoMaLop` varchar(20) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phonghoc`
--

INSERT INTO `phonghoc` (`maPhong`, `tenPhong`, `sucChua`, `dangGiaoChoMaLop`, `trangThai`) VALUES
('P101', 'Phòng 101', 50, NULL, 'DANG_SU_DUNG'),
('P102', 'Phòng 102', 50, NULL, 'DANG_SU_DUNG'),
('P201', 'Phòng 201', 50, NULL, 'DANG_SU_DUNG'),
('P301', 'Phòng 301', 50, NULL, 'DANG_SU_DUNG');

-- --------------------------------------------------------

--
-- Table structure for table `phuhuynh`
--

CREATE TABLE `phuhuynh` (
  `maPH` varchar(20) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `moiQuanHe` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phuhuynh`
--

INSERT INTO `phuhuynh` (`maPH`, `hoTen`, `email`, `soDienThoai`, `diaChi`, `gioiTinh`, `moiQuanHe`) VALUES
('PH_001', 'Ngô Văn Hậu', 'ph001@edu.vn', '0904444001', 'Hà Nội', 'Nam', 'Cha'),
('PH_002', 'Phạm Thị Thu', 'ph002@edu.vn', '0904444002', 'Hà Nội', 'Nữ', 'Mẹ'),
('PH_003', 'Nguyễn Thị Hoa', 'ph003@edu.vn', '0904444003', 'Hà Nội', 'Nữ', 'Mẹ'),
('PH_004', 'Trần Văn Minh', 'ph004@edu.vn', '0904444004', 'Hà Nội', 'Nam', 'Cha'),
('PH_005', 'Đỗ Thị Hà', 'ph005@edu.vn', '0904444005', 'Hà Nội', 'Nữ', 'Mẹ'),
('PH_TR001_001', 'Phụ huynh Test 1', 'ph@test.edu.vn', '0900000007', '123 Đường A, Hà Nội', 'Nam', 'Cha');

-- --------------------------------------------------------

--
-- Table structure for table `phuhuynh_hocsinh`
--

CREATE TABLE `phuhuynh_hocsinh` (
  `maPH` varchar(20) NOT NULL,
  `maHS` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phuhuynh_hocsinh`
--

INSERT INTO `phuhuynh_hocsinh` (`maPH`, `maHS`) VALUES
('PH_001', 'HS_001'),
('PH_002', 'HS_002'),
('PH_003', 'HS_003'),
('PH_004', 'HS_004'),
('PH_005', 'HS_005'),
('PH_TR001_001', 'HS_TR001_001');

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan`
--

CREATE TABLE `taikhoan` (
  `maTaiKhoan` varchar(50) NOT NULL,
  `tenDangNhap` varchar(100) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `maTruong` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taikhoan`
--

INSERT INTO `taikhoan` (`maTaiKhoan`, `tenDangNhap`, `matKhau`, `email`, `soDienThoai`, `trangThai`, `maTruong`) VALUES
('TK_ADMIN_TR001', 'test.admin', '12345678', 'admin@test.edu.vn', '0901111003', 'ACTIVE', 'TR001'),
('TK_BGH_TR001', 'test.bgh', '12345678', 'bgh@test.edu.vn', '0901111002', 'ACTIVE', 'TR001'),
('TK_GVBM_TR001', 'test.gvbm', '12345678', 'gvbm@test.edu.vn', '0900000003', 'ACTIVE', 'TR001'),
('TK_GVCN_TR001', 'test.gvcn', '12345678', 'gvcn@test.edu.vn', '0900000004', 'ACTIVE', 'TR001'),
('TK_GV_ANH_01', 'gv.anh01', '12345678', 'anh01@edu.vn', '0901111006', 'ACTIVE', 'TR001'),
('TK_GV_TOAN_01', 'gv.toan01', '12345678', 'toan01@edu.vn', '0901111004', 'ACTIVE', 'TR001'),
('TK_GV_TTBM_01', 'gv.ttbm01', '12345678', 'ttbm01@edu.vn', '0901111007', 'ACTIVE', 'TR001'),
('TK_GV_VAN_01', 'gv.van01', '12345678', 'van01@edu.vn', '0901111005', 'ACTIVE', 'TR001'),
('TK_HS_001', 'hs001', '12345678', 'hs001@edu.vn', '0901111008', 'ACTIVE', 'TR001'),
('TK_HS_002', 'hs002', '12345678', 'hs002@edu.vn', '0901111009', 'ACTIVE', 'TR001'),
('TK_HS_003', 'hs003', '12345678', 'hs003@edu.vn', '0901111010', 'ACTIVE', 'TR001'),
('TK_HS_TR001_001', 'test.hs', '12345678', 'hs@test.edu.vn', '0900000006', 'ACTIVE', 'TR001'),
('TK_NVS_01', 'test.nhanvienso', '12345678', 'nhanvienso@test.edu.vn', '0901111001', 'ACTIVE', NULL),
('TK_PH_001', 'ph001', '12345678', 'ph001@edu.vn', '0901111011', 'ACTIVE', 'TR001'),
('TK_PH_TR001_001', 'test.ph', '12345678', 'ph@test.edu.vn', '0900000007', 'ACTIVE', 'TR001'),
('TK_TTBM_TR001', 'test.ttbm', '12345678', 'ttbm@test.edu.vn', '0900000005', 'ACTIVE', 'TR001');

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan_vaitro`
--

CREATE TABLE `taikhoan_vaitro` (
  `maTaiKhoan` varchar(50) NOT NULL,
  `maVaiTro` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taikhoan_vaitro`
--

INSERT INTO `taikhoan_vaitro` (`maTaiKhoan`, `maVaiTro`) VALUES
('TK_ADMIN_TR001', 'admin'),
('TK_BGH_TR001', 'bgh'),
('TK_GVBM_TR001', 'gvbm'),
('TK_GVCN_TR001', 'gvcn'),
('TK_GV_ANH_01', 'gvbm'),
('TK_GV_TOAN_01', 'gvbm'),
('TK_GV_TTBM_01', 'ttbm'),
('TK_GV_VAN_01', 'gvbm'),
('TK_HS_001', 'hs'),
('TK_HS_002', 'hs'),
('TK_HS_003', 'hs'),
('TK_HS_TR001_001', 'hs'),
('TK_NVS_01', 'nhanvienso'),
('TK_PH_001', 'ph'),
('TK_PH_TR001_001', 'ph'),
('TK_TTBM_TR001', 'ttbm');

-- --------------------------------------------------------

--
-- Table structure for table `thisinh`
--

CREATE TABLE `thisinh` (
  `maThiSinh` varchar(30) NOT NULL,
  `hoTen` varchar(150) DEFAULT NULL,
  `soCCCD` varchar(20) DEFAULT NULL,
  `diem` int(11) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thisinh`
--

INSERT INTO `thisinh` (`maThiSinh`, `hoTen`, `soCCCD`, `diem`, `soDienThoai`) VALUES
('TS_0001', 'Trần Bảo Ngân', '012345000001', 28, '0906666001'),
('TS_0002', 'Vũ Anh Quân', '012345000002', 26, '0906666002');

-- --------------------------------------------------------

--
-- Table structure for table `thoikhoabieu`
--

CREATE TABLE `thoikhoabieu` (
  `maThoiKhoaBieu` varchar(30) NOT NULL,
  `tiet` int(11) DEFAULT NULL,
  `loaiTiet` varchar(50) DEFAULT NULL,
  `ngayHoc` date DEFAULT NULL,
  `maLop` varchar(20) DEFAULT NULL,
  `maMonHoc` varchar(20) DEFAULT NULL,
  `maPhong` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thoikhoabieu`
--

INSERT INTO `thoikhoabieu` (`maThoiKhoaBieu`, `tiet`, `loaiTiet`, `ngayHoc`, `maLop`, `maMonHoc`, `maPhong`) VALUES
('TKB_10A1_20241007_T1', 1, 'LT', '2024-10-07', 'TR001-10A1', 'TOAN', 'P101'),
('TKB_10A1_20241007_T2', 2, 'LT', '2024-10-07', 'TR001-10A1', 'VAN', 'P101'),
('TKB_10A1_20241008_T1', 1, 'LT', '2024-10-08', 'TR001-10A1', 'ANH', 'P101');

-- --------------------------------------------------------

--
-- Table structure for table `tohopmon`
--

CREATE TABLE `tohopmon` (
  `maToHop` varchar(20) NOT NULL,
  `tenToHop` varchar(100) DEFAULT NULL,
  `danhSachMon` varchar(500) DEFAULT NULL,
  `soLuongLop` int(11) DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tohopmon`
--

INSERT INTO `tohopmon` (`maToHop`, `tenToHop`, `danhSachMon`, `soLuongLop`, `trangThai`) VALUES
('CNTT', 'Công nghệ thông tin', 'TOAN,TIN,ANH', 1, 'ACTIVE'),
('KHTN', 'Khoa học tự nhiên', 'LY,HOA,SINH', 2, 'ACTIVE'),
('KHXH', 'Khoa học xã hội', 'SU,DIA,GDCD', 2, 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `tohopmon_monhoc`
--

CREATE TABLE `tohopmon_monhoc` (
  `maToHop` varchar(20) NOT NULL,
  `maMonHoc` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tohopmon_monhoc`
--

INSERT INTO `tohopmon_monhoc` (`maToHop`, `maMonHoc`) VALUES
('CNTT', 'ANH'),
('CNTT', 'TIN'),
('CNTT', 'TOAN'),
('KHTN', 'HOA'),
('KHTN', 'LY'),
('KHTN', 'SINH'),
('KHXH', 'DIA'),
('KHXH', 'GDCD'),
('KHXH', 'SU');

-- --------------------------------------------------------

--
-- Table structure for table `totruongbomon`
--

CREATE TABLE `totruongbomon` (
  `maGV` varchar(20) NOT NULL,
  `monPhuTrach` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `totruongbomon`
--

INSERT INTO `totruongbomon` (`maGV`, `monPhuTrach`) VALUES
('GV_TR001_03', 'Toán'),
('GV_TTBM_01', 'Toán');

-- --------------------------------------------------------

--
-- Table structure for table `truong`
--

CREATE TABLE `truong` (
  `maTruong` varchar(30) NOT NULL,
  `tenTruong` varchar(200) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `truong`
--

INSERT INTO `truong` (`maTruong`, `tenTruong`, `diaChi`, `email`, `soDienThoai`) VALUES
('TR001', 'THPT Minh Khai', '123 Đường A, Quận Ba Đình, Hà Nội', 'contact@minhkhai.edu.vn', '0900000001'),
('TR002', 'THPT Lê Quý Đôn', '45 Trần Phú, Quận 5, TP.HCM', 'lqd@edu.vn', '0900000002');

-- --------------------------------------------------------

--
-- Table structure for table `vaitro`
--

CREATE TABLE `vaitro` (
  `maVaiTro` varchar(30) NOT NULL,
  `tenVaiTro` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaitro`
--

INSERT INTO `vaitro` (`maVaiTro`, `tenVaiTro`) VALUES
('admin', 'Nhân viên phòng giáo vụ'),
('bgh', 'Ban giám hiệu'),
('gvbm', 'Giáo viên bộ môn'),
('gvcn', 'Giáo viên chủ nhiệm'),
('hs', 'Học sinh'),
('nhanvienso', 'Nhân viên Sở'),
('ph', 'Phụ huynh'),
('ttbm', 'Tổ trưởng bộ môn');

-- --------------------------------------------------------

--
-- Table structure for table `yeucausuadiem`
--

CREATE TABLE `yeucausuadiem` (
  `maYeuCau` varchar(30) NOT NULL,
  `monHoc` varchar(100) DEFAULT NULL,
  `diemCu` float DEFAULT NULL,
  `diemMoi` float DEFAULT NULL,
  `lyDo` varchar(255) DEFAULT NULL,
  `ngayYeuCau` datetime DEFAULT NULL,
  `trangThai` varchar(20) DEFAULT NULL,
  `giaoVienXemXet` varchar(150) DEFAULT NULL,
  `ngayXuLy` datetime DEFAULT NULL,
  `maBangDiem` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `yeucausuadiem`
--

INSERT INTO `yeucausuadiem` (`maYeuCau`, `monHoc`, `diemCu`, `diemMoi`, `lyDo`, `ngayYeuCau`, `trangThai`, `giaoVienXemXet`, `ngayXuLy`, `maBangDiem`) VALUES
('YCSD_0001', 'Toán', 7.5, 8, 'Chấm sót điểm TX', '2024-11-20 09:00:00', 'DA_DUYET', 'Nguyễn Văn Toán', '2024-11-22 14:30:00', 'BD_HS001_TOAN_HK1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bangbaocaothongke`
--
ALTER TABLE `bangbaocaothongke`
  ADD PRIMARY KEY (`maBaoCao`),
  ADD KEY `maTruong` (`maTruong`),
  ADD KEY `maTaiKhoanLap` (`maTaiKhoanLap`);

--
-- Indexes for table `bangdiem`
--
ALTER TABLE `bangdiem`
  ADD PRIMARY KEY (`maBangDiem`),
  ADD KEY `maHS` (`maHS`),
  ADD KEY `maMonHoc` (`maMonHoc`),
  ADD KEY `maGV` (`maGV`);

--
-- Indexes for table `bangiamhieu`
--
ALTER TABLE `bangiamhieu`
  ADD PRIMARY KEY (`maBGH`),
  ADD KEY `maTruong` (`maTruong`),
  ADD KEY `maTaiKhoan` (`maTaiKhoan`);

--
-- Indexes for table `bangphancongrade`
--
ALTER TABLE `bangphancongrade`
  ADD PRIMARY KEY (`hocKy`,`kyThi`),
  ADD KEY `maGV` (`maGV`);

--
-- Indexes for table `chitieutuyensinh`
--
ALTER TABLE `chitieutuyensinh`
  ADD PRIMARY KEY (`maChiTieu`),
  ADD KEY `maTruong` (`maTruong`),
  ADD KEY `maNhanVienSo` (`maNhanVienSo`);

--
-- Indexes for table `donxinphep`
--
ALTER TABLE `donxinphep`
  ADD PRIMARY KEY (`maDonXinPhep`),
  ADD KEY `maHS` (`maHS`),
  ADD KEY `maPH` (`maPH`);

--
-- Indexes for table `giaovienbomon`
--
ALTER TABLE `giaovienbomon`
  ADD PRIMARY KEY (`maGV`),
  ADD KEY `maTaiKhoan` (`maTaiKhoan`);

--
-- Indexes for table `giaovienchunhiem`
--
ALTER TABLE `giaovienchunhiem`
  ADD PRIMARY KEY (`maGV`);

--
-- Indexes for table `hanhkiem`
--
ALTER TABLE `hanhkiem`
  ADD PRIMARY KEY (`maHanhKiem`),
  ADD KEY `maHS` (`maHS`);

--
-- Indexes for table `hocluc`
--
ALTER TABLE `hocluc`
  ADD PRIMARY KEY (`maHocLuc`),
  ADD KEY `maHS` (`maHS`);

--
-- Indexes for table `hocsinh`
--
ALTER TABLE `hocsinh`
  ADD PRIMARY KEY (`maHS`),
  ADD KEY `maTaiKhoan` (`maTaiKhoan`);

--
-- Indexes for table `khoi`
--
ALTER TABLE `khoi`
  ADD PRIMARY KEY (`maKhoi`);

--
-- Indexes for table `lichsuthaydoihosogv`
--
ALTER TABLE `lichsuthaydoihosogv`
  ADD PRIMARY KEY (`maHoSo`),
  ADD KEY `maGV` (`maGV`);

--
-- Indexes for table `lophoc`
--
ALTER TABLE `lophoc`
  ADD PRIMARY KEY (`maLop`),
  ADD KEY `khoi` (`khoi`);

--
-- Indexes for table `monhoc`
--
ALTER TABLE `monhoc`
  ADD PRIMARY KEY (`maMonHoc`);

--
-- Indexes for table `nguyenvong`
--
ALTER TABLE `nguyenvong`
  ADD PRIMARY KEY (`maNguyenVong`),
  ADD KEY `maThiSinh` (`maThiSinh`),
  ADD KEY `maTruong` (`maTruong`);

--
-- Indexes for table `nhanvienphonggiaovu`
--
ALTER TABLE `nhanvienphonggiaovu`
  ADD PRIMARY KEY (`maNVGiaoVu`),
  ADD KEY `maTruong` (`maTruong`),
  ADD KEY `maTaiKhoan` (`maTaiKhoan`);

--
-- Indexes for table `nhanvienso`
--
ALTER TABLE `nhanvienso`
  ADD PRIMARY KEY (`maNhanVienSo`),
  ADD KEY `maTaiKhoan` (`maTaiKhoan`);

--
-- Indexes for table `phanconggiangday`
--
ALTER TABLE `phanconggiangday`
  ADD PRIMARY KEY (`maPhanCong`),
  ADD UNIQUE KEY `UK_PhanCong` (`maLop`,`maMonHoc`,`namHoc`,`hocKy`),
  ADD KEY `IX_PhanCongGiangDay_maLop` (`maLop`),
  ADD KEY `IX_PhanCongGiangDay_maGV` (`maGV`),
  ADD KEY `IX_PhanCongGiangDay_maMonHoc` (`maMonHoc`);

--
-- Indexes for table `phancongphonghoc`
--
ALTER TABLE `phancongphonghoc`
  ADD PRIMARY KEY (`maPhanCong`),
  ADD UNIQUE KEY `unique_phong_nam` (`maPhong`,`namHoc`),
  ADD UNIQUE KEY `unique_lop_nam` (`maLop`,`namHoc`),
  ADD KEY `idx_phong` (`maPhong`),
  ADD KEY `idx_lop` (`maLop`),
  ADD KEY `idx_nam_hoc` (`namHoc`);

--
-- Indexes for table `phieudangkytohopmon`
--
ALTER TABLE `phieudangkytohopmon`
  ADD PRIMARY KEY (`maDKToHop`),
  ADD KEY `maHS` (`maHS`),
  ADD KEY `maToHop` (`maToHop`);

--
-- Indexes for table `phonghoc`
--
ALTER TABLE `phonghoc`
  ADD PRIMARY KEY (`maPhong`);

--
-- Indexes for table `phuhuynh`
--
ALTER TABLE `phuhuynh`
  ADD PRIMARY KEY (`maPH`);

--
-- Indexes for table `phuhuynh_hocsinh`
--
ALTER TABLE `phuhuynh_hocsinh`
  ADD PRIMARY KEY (`maPH`,`maHS`),
  ADD KEY `maHS` (`maHS`);

--
-- Indexes for table `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`maTaiKhoan`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`),
  ADD KEY `FK_TaiKhoan_Truong` (`maTruong`);

--
-- Indexes for table `taikhoan_vaitro`
--
ALTER TABLE `taikhoan_vaitro`
  ADD PRIMARY KEY (`maTaiKhoan`,`maVaiTro`),
  ADD KEY `maVaiTro` (`maVaiTro`);

--
-- Indexes for table `thisinh`
--
ALTER TABLE `thisinh`
  ADD PRIMARY KEY (`maThiSinh`);

--
-- Indexes for table `thoikhoabieu`
--
ALTER TABLE `thoikhoabieu`
  ADD PRIMARY KEY (`maThoiKhoaBieu`),
  ADD KEY `maLop` (`maLop`),
  ADD KEY `maMonHoc` (`maMonHoc`),
  ADD KEY `maPhong` (`maPhong`);

--
-- Indexes for table `tohopmon`
--
ALTER TABLE `tohopmon`
  ADD PRIMARY KEY (`maToHop`);

--
-- Indexes for table `tohopmon_monhoc`
--
ALTER TABLE `tohopmon_monhoc`
  ADD PRIMARY KEY (`maToHop`,`maMonHoc`),
  ADD KEY `IX_ToHopMon_MonHoc_maMonHoc` (`maMonHoc`);

--
-- Indexes for table `totruongbomon`
--
ALTER TABLE `totruongbomon`
  ADD PRIMARY KEY (`maGV`);

--
-- Indexes for table `truong`
--
ALTER TABLE `truong`
  ADD PRIMARY KEY (`maTruong`);

--
-- Indexes for table `vaitro`
--
ALTER TABLE `vaitro`
  ADD PRIMARY KEY (`maVaiTro`);

--
-- Indexes for table `yeucausuadiem`
--
ALTER TABLE `yeucausuadiem`
  ADD PRIMARY KEY (`maYeuCau`),
  ADD KEY `maBangDiem` (`maBangDiem`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bangbaocaothongke`
--
ALTER TABLE `bangbaocaothongke`
  ADD CONSTRAINT `bangbaocaothongke_ibfk_1` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`),
  ADD CONSTRAINT `bangbaocaothongke_ibfk_2` FOREIGN KEY (`maTaiKhoanLap`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `bangdiem`
--
ALTER TABLE `bangdiem`
  ADD CONSTRAINT `bangdiem_ibfk_1` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`),
  ADD CONSTRAINT `bangdiem_ibfk_2` FOREIGN KEY (`maMonHoc`) REFERENCES `monhoc` (`maMonHoc`),
  ADD CONSTRAINT `bangdiem_ibfk_3` FOREIGN KEY (`maGV`) REFERENCES `giaovienbomon` (`maGV`);

--
-- Constraints for table `bangiamhieu`
--
ALTER TABLE `bangiamhieu`
  ADD CONSTRAINT `bangiamhieu_ibfk_1` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`),
  ADD CONSTRAINT `bangiamhieu_ibfk_2` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `bangphancongrade`
--
ALTER TABLE `bangphancongrade`
  ADD CONSTRAINT `bangphancongrade_ibfk_1` FOREIGN KEY (`maGV`) REFERENCES `totruongbomon` (`maGV`);

--
-- Constraints for table `chitieutuyensinh`
--
ALTER TABLE `chitieutuyensinh`
  ADD CONSTRAINT `chitieutuyensinh_ibfk_1` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`),
  ADD CONSTRAINT `chitieutuyensinh_ibfk_2` FOREIGN KEY (`maNhanVienSo`) REFERENCES `nhanvienso` (`maNhanVienSo`);

--
-- Constraints for table `donxinphep`
--
ALTER TABLE `donxinphep`
  ADD CONSTRAINT `donxinphep_ibfk_1` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`),
  ADD CONSTRAINT `donxinphep_ibfk_2` FOREIGN KEY (`maPH`) REFERENCES `phuhuynh` (`maPH`);

--
-- Constraints for table `giaovienbomon`
--
ALTER TABLE `giaovienbomon`
  ADD CONSTRAINT `giaovienbomon_ibfk_1` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `giaovienchunhiem`
--
ALTER TABLE `giaovienchunhiem`
  ADD CONSTRAINT `giaovienchunhiem_ibfk_1` FOREIGN KEY (`maGV`) REFERENCES `giaovienbomon` (`maGV`);

--
-- Constraints for table `hanhkiem`
--
ALTER TABLE `hanhkiem`
  ADD CONSTRAINT `hanhkiem_ibfk_1` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`);

--
-- Constraints for table `hocluc`
--
ALTER TABLE `hocluc`
  ADD CONSTRAINT `hocluc_ibfk_1` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`);

--
-- Constraints for table `hocsinh`
--
ALTER TABLE `hocsinh`
  ADD CONSTRAINT `hocsinh_ibfk_1` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `lichsuthaydoihosogv`
--
ALTER TABLE `lichsuthaydoihosogv`
  ADD CONSTRAINT `lichsuthaydoihosogv_ibfk_1` FOREIGN KEY (`maGV`) REFERENCES `giaovienbomon` (`maGV`);

--
-- Constraints for table `lophoc`
--
ALTER TABLE `lophoc`
  ADD CONSTRAINT `lophoc_ibfk_1` FOREIGN KEY (`khoi`) REFERENCES `khoi` (`maKhoi`);

--
-- Constraints for table `nguyenvong`
--
ALTER TABLE `nguyenvong`
  ADD CONSTRAINT `nguyenvong_ibfk_1` FOREIGN KEY (`maThiSinh`) REFERENCES `thisinh` (`maThiSinh`),
  ADD CONSTRAINT `nguyenvong_ibfk_2` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`);

--
-- Constraints for table `nhanvienphonggiaovu`
--
ALTER TABLE `nhanvienphonggiaovu`
  ADD CONSTRAINT `nhanvienphonggiaovu_ibfk_1` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`),
  ADD CONSTRAINT `nhanvienphonggiaovu_ibfk_2` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `nhanvienso`
--
ALTER TABLE `nhanvienso`
  ADD CONSTRAINT `nhanvienso_ibfk_1` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`);

--
-- Constraints for table `phanconggiangday`
--
ALTER TABLE `phanconggiangday`
  ADD CONSTRAINT `phanconggiangday_ibfk_1` FOREIGN KEY (`maLop`) REFERENCES `lophoc` (`maLop`) ON DELETE CASCADE,
  ADD CONSTRAINT `phanconggiangday_ibfk_2` FOREIGN KEY (`maMonHoc`) REFERENCES `monhoc` (`maMonHoc`) ON DELETE CASCADE,
  ADD CONSTRAINT `phanconggiangday_ibfk_3` FOREIGN KEY (`maGV`) REFERENCES `giaovienbomon` (`maGV`) ON DELETE CASCADE;

--
-- Constraints for table `phieudangkytohopmon`
--
ALTER TABLE `phieudangkytohopmon`
  ADD CONSTRAINT `phieudangkytohopmon_ibfk_1` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`),
  ADD CONSTRAINT `phieudangkytohopmon_ibfk_2` FOREIGN KEY (`maToHop`) REFERENCES `tohopmon` (`maToHop`);

--
-- Constraints for table `phuhuynh_hocsinh`
--
ALTER TABLE `phuhuynh_hocsinh`
  ADD CONSTRAINT `phuhuynh_hocsinh_ibfk_1` FOREIGN KEY (`maPH`) REFERENCES `phuhuynh` (`maPH`),
  ADD CONSTRAINT `phuhuynh_hocsinh_ibfk_2` FOREIGN KEY (`maHS`) REFERENCES `hocsinh` (`maHS`);

--
-- Constraints for table `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `FK_TaiKhoan_Truong` FOREIGN KEY (`maTruong`) REFERENCES `truong` (`maTruong`);

--
-- Constraints for table `taikhoan_vaitro`
--
ALTER TABLE `taikhoan_vaitro`
  ADD CONSTRAINT `taikhoan_vaitro_ibfk_1` FOREIGN KEY (`maTaiKhoan`) REFERENCES `taikhoan` (`maTaiKhoan`),
  ADD CONSTRAINT `taikhoan_vaitro_ibfk_2` FOREIGN KEY (`maVaiTro`) REFERENCES `vaitro` (`maVaiTro`);

--
-- Constraints for table `thoikhoabieu`
--
ALTER TABLE `thoikhoabieu`
  ADD CONSTRAINT `thoikhoabieu_ibfk_1` FOREIGN KEY (`maLop`) REFERENCES `lophoc` (`maLop`),
  ADD CONSTRAINT `thoikhoabieu_ibfk_2` FOREIGN KEY (`maMonHoc`) REFERENCES `monhoc` (`maMonHoc`),
  ADD CONSTRAINT `thoikhoabieu_ibfk_3` FOREIGN KEY (`maPhong`) REFERENCES `phonghoc` (`maPhong`);

--
-- Constraints for table `tohopmon_monhoc`
--
ALTER TABLE `tohopmon_monhoc`
  ADD CONSTRAINT `tohopmon_monhoc_ibfk_1` FOREIGN KEY (`maToHop`) REFERENCES `tohopmon` (`maToHop`) ON DELETE CASCADE,
  ADD CONSTRAINT `tohopmon_monhoc_ibfk_2` FOREIGN KEY (`maMonHoc`) REFERENCES `monhoc` (`maMonHoc`) ON DELETE CASCADE;

--
-- Constraints for table `totruongbomon`
--
ALTER TABLE `totruongbomon`
  ADD CONSTRAINT `totruongbomon_ibfk_1` FOREIGN KEY (`maGV`) REFERENCES `giaovienbomon` (`maGV`);

--
-- Constraints for table `yeucausuadiem`
--
ALTER TABLE `yeucausuadiem`
  ADD CONSTRAINT `yeucausuadiem_ibfk_1` FOREIGN KEY (`maBangDiem`) REFERENCES `bangdiem` (`maBangDiem`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

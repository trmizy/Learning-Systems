<?php
require_once __DIR__ . '/../../config/database.php';

class DuyetSuaDiemModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách yêu cầu chờ duyệt - FIX: Đúng tên bảng và cột
     */
    public function getDanhSachYeuCauChoDuyet() {
        try {
            // ⚠️ FIX: Sửa tên bảng và cột cho khớp với schema thực tế
            $sql = "SELECT 
                        yc.maYeuCau,
                        yc.ngayYeuCau,
                        yc.loaiDiem,
                        yc.diemCu,
                        yc.diemMoi,
                        yc.lyDo,
                        yc.maBangDiem,
                        hs.hoTen as tenHocSinh,
                        hs.maHS,
                        lh.tenLop,
                        mh.tenMon,
                        gv.hoTen as tenGiaoVien
                    FROM yeucausuadiem yc
                    INNER JOIN bangdiem bd ON yc.maBangDiem = bd.maBangDiem
                    INNER JOIN hocsinh hs ON bd.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    INNER JOIN monhoc mh ON bd.maMonHoc = mh.maMonHoc
                    INNER JOIN giaovienbomon gv ON yc.maGV = gv.maGV
                    WHERE yc.trangThai = 'CHO_DUYET'
                    ORDER BY yc.ngayYeuCau DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG
            error_log("=== getDanhSachYeuCauChoDuyet ===");
            error_log("Số bản ghi: " . count($result));
            if (count($result) > 0) {
                error_log("Bản ghi đầu: " . print_r($result[0], true));
            } else {
                // Kiểm tra bảng có dữ liệu không
                $checkSql = "SELECT COUNT(*) as total FROM yeucausuadiem";
                $checkStmt = $this->db->query($checkSql);
                $total = $checkStmt->fetchColumn();
                error_log("Tổng số bản ghi trong bảng yeucausuadiem: $total");
                
                if ($total > 0) {
                    // Có dữ liệu nhưng không có CHO_DUYET
                    $statusSql = "SELECT trangThai, COUNT(*) as count FROM yeucausuadiem GROUP BY trangThai";
                    $statusStmt = $this->db->query($statusSql);
                    $statuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Phân bố trạng thái: " . print_r($statuses, true));
                }
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getDanhSachYeuCauChoDuyet: " . $e->getMessage());
            error_log("SQL Error Code: " . $e->getCode());
            return [];
        }
    }

    /**
     * Lấy chi tiết yêu cầu - FIX: Đúng tên cột
     */
    public function getChiTietYeuCau($maYeuCau) {
        try {
            $sql = "SELECT 
                        yc.*,
                        hs.hoTen as tenHocSinh,
                        hs.maHS,
                        lh.tenLop,
                        lh.maLop,
                        mh.tenMon,
                        mh.maMonHoc,
                        gv.hoTen as tenGiaoVien,
                        bd.namHoc,
                        bd.hocKy
                    FROM yeucausuadiem yc
                    INNER JOIN bangdiem bd ON yc.maBangDiem = bd.maBangDiem
                    INNER JOIN hocsinh hs ON bd.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    INNER JOIN monhoc mh ON bd.maMonHoc = mh.maMonHoc
                    INNER JOIN giaovienbomon gv ON yc.maGV = gv.maGV
                    WHERE yc.maYeuCau = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maYeuCau]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getChiTietYeuCau: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Duyệt yêu cầu và cập nhật điểm - FIX: Xử lý an toàn loaiDiem
     */
    public function duyetYeuCau($maYeuCau, $maBGH, $ghiChu = '') {
        try {
            $this->db->beginTransaction();

            // Lấy thông tin yêu cầu
            $yeuCau = $this->getChiTietYeuCau($maYeuCau);
            
            if (!$yeuCau) {
                throw new Exception("Không tìm thấy yêu cầu");
            }

            // ⚠️ FIX: Validate loaiDiem trước khi dùng trong SQL
            $loaiDiem = $yeuCau['loaiDiem'];
            $allowedColumns = ['diemThuongXuyen', 'diemGiuaKy', 'diemCuoiKy'];
            
            if (!in_array($loaiDiem, $allowedColumns)) {
                throw new Exception("Loại điểm không hợp lệ: $loaiDiem");
            }

            // Cập nhật trạng thái yêu cầu
            $sql = "UPDATE yeucausuadiem 
                    SET trangThai = 'DA_DUYET',
                        maBGH = ?,
                        ngayDuyet = NOW(),
                        ghiChuBGH = ?
                    WHERE maYeuCau = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maBGH, $ghiChu, $maYeuCau]);

            // ⚠️ FIX: Kiểm tra maBangDiem tồn tại
            $maBangDiem = $yeuCau['maBangDiem'];
            if (empty($maBangDiem)) {
                throw new Exception("Không tìm thấy mã bảng điểm");
            }

            // ⚠️ FIX: Cập nhật điểm AN TOÀN với prepared statement
            $diemMoi = $yeuCau['diemMoi'];
            
            // Validate điểm mới
            if (!is_numeric($diemMoi) || $diemMoi < 0 || $diemMoi > 10) {
                throw new Exception("Điểm mới không hợp lệ: $diemMoi");
            }

            // Tạo câu SQL an toàn
            if ($loaiDiem === 'diemThuongXuyen') {
                $sqlUpdateDiem = "UPDATE bangdiem SET diemThuongXuyen = ? WHERE maBangDiem = ?";
            } elseif ($loaiDiem === 'diemGiuaKy') {
                $sqlUpdateDiem = "UPDATE bangdiem SET diemGiuaKy = ? WHERE maBangDiem = ?";
            } elseif ($loaiDiem === 'diemCuoiKy') {
                $sqlUpdateDiem = "UPDATE bangdiem SET diemCuoiKy = ? WHERE maBangDiem = ?";
            }
            
            $stmtUpdate = $this->db->prepare($sqlUpdateDiem);
            $resultUpdate = $stmtUpdate->execute([$diemMoi, $maBangDiem]);

            // ⚠️ FIX: Kiểm tra xem có cập nhật được không
            if (!$resultUpdate) {
                throw new Exception("Không thể cập nhật điểm vào bảng điểm");
            }

            // Kiểm tra số dòng bị ảnh hưởng
            $rowsAffected = $stmtUpdate->rowCount();
            if ($rowsAffected === 0) {
                error_log("WARNING: Không có dòng nào được cập nhật. maBangDiem có thể không tồn tại: $maBangDiem");
                // Không throw exception vì có thể điểm mới = điểm cũ
            }

            $this->db->commit();
            
            // DEBUG
            error_log("=== duyetYeuCau SUCCESS ===");
            error_log("maYeuCau: $maYeuCau");
            error_log("maBangDiem: $maBangDiem");
            error_log("loaiDiem: $loaiDiem");
            error_log("diemMoi: $diemMoi");
            error_log("rowsAffected: $rowsAffected");
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error duyetYeuCau: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Từ chối yêu cầu
     */
    public function tuChoiYeuCau($maYeuCau, $maBGH, $lyDoTuChoi) {
        try {
            $sql = "UPDATE yeucausuadiem 
                    SET trangThai = 'TU_CHOI',
                        maBGH = ?,
                        ngayDuyet = NOW(),
                        ghiChuBGH = ?
                    WHERE maYeuCau = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$maBGH, $lyDoTuChoi, $maYeuCau]);
            
            // DEBUG
            error_log("=== tuChoiYeuCau ===");
            error_log("maYeuCau: $maYeuCau | Result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error tuChoiYeuCau: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy mã BGH từ username
     */
    public function getMaBGHByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT bgh.maBGH
                FROM taikhoan tk
                INNER JOIN bangiamhieu bgh ON tk.maTaiKhoan = bgh.maTaiKhoan
                WHERE tk.tenDangNhap = ? AND tk.trangThai = 'ACTIVE'
                LIMIT 1
            ");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            
            return $result ? $result['maBGH'] : null;
            
        } catch (PDOException $e) {
            error_log("Error getMaBGHByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy lịch sử yêu cầu đã xử lý (đã duyệt hoặc từ chối)
     */
    public function getLichSuYeuCau($limit = 50, $offset = 0) {
        try {
            $sql = "SELECT 
                        yc.maYeuCau,
                        yc.ngayYeuCau,
                        yc.loaiDiem,
                        yc.diemCu,
                        yc.diemMoi,
                        yc.lyDo,
                        yc.trangThai,
                        yc.ngayDuyet,
                        yc.ghiChuBGH,
                        yc.maBangDiem,
                        hs.hoTen as tenHocSinh,
                        hs.maHS,
                        lh.tenLop,
                        mh.tenMon,
                        gv.hoTen as tenGiaoVien,
                        bgh.hoTen as tenBGH
                    FROM yeucausuadiem yc
                    INNER JOIN bangdiem bd ON yc.maBangDiem = bd.maBangDiem
                    INNER JOIN hocsinh hs ON bd.maHS = hs.maHS
                    INNER JOIN lophoc lh ON hs.maLop = lh.maLop
                    INNER JOIN monhoc mh ON bd.maMonHoc = mh.maMonHoc
                    INNER JOIN giaovienbomon gv ON yc.maGV = gv.maGV
                    LEFT JOIN bangiamhieu bgh ON yc.maBGH = bgh.maBGH
                    WHERE yc.trangThai IN ('DA_DUYET', 'TU_CHOI')
                    ORDER BY yc.ngayDuyet DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG
            error_log("=== getLichSuYeuCau ===");
            error_log("Số bản ghi lịch sử: " . count($result));
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error getLichSuYeuCau: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số yêu cầu đã xử lý
     */
    public function demTongLichSu() {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM yeucausuadiem 
                    WHERE trangThai IN ('DA_DUYET', 'TU_CHOI')";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['total'] : 0;
            
        } catch (PDOException $e) {
            error_log("Error demTongLichSu: " . $e->getMessage());
            return 0;
        }
    }
}

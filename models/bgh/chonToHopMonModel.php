<?php
require_once __DIR__ . '/../../config/database.php';

class chonToHopMonModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lấy danh sách tổ hợp môn chờ duyệt hoặc đã duyệt
     */
    public function getDanhSachToHopMon($trangThai = null) {
        // Lấy danh sách từ VIEW để tối ưu
        $sql = "SELECT * FROM vw_bgh_chon_to_hop_mon";
        $params = [];
        if ($trangThai !== null) {
            $sql .= " WHERE trangThai = ?";
            $params[] = $trangThai;
        }
        $sql .= " ORDER BY ngayTao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết một tổ hợp môn
     */
    public function getChiTietToHopMon($maToHop) {
        // Lấy chi tiết từ VIEW (VIEW đã tổng hợp tên các môn và số HS)
        $sql = "SELECT * FROM vw_bgh_chon_to_hop_mon WHERE maToHop = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maToHop]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách học sinh đã đăng ký tổ hợp môn
     */
    public function getDanhSachHocSinhDangKy($maToHop) {
        $sql = "SELECT 
                    phd.maDKToHop,
                    phd.maHS,
                    hs.hoTen AS tenHocSinh,
                    hs.gioiTinh,
                    lo.maLop AS lop,
                    phd.ngayDK
                FROM phieudangkytohopmon phd
                JOIN hocsinh hs ON hs.maHS = phd.maHS
                LEFT JOIN lophoc lo ON lo.maLop = hs.maLop
                WHERE phd.maToHop = ?
                ORDER BY phd.ngayDK DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maToHop]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách môn học trong tổ hợp
     */
    public function getDanhSachMonTrongToHop($maToHop) {
        $sql = "SELECT 
                    m.maMonHoc AS maMon,
                    m.tenMon AS tenMon,
                    m.soTietTuan AS soTiet
                FROM tohopmon_monhoc tm
                JOIN monhoc m ON m.maMonHoc = tm.maMonHoc
                WHERE tm.maToHop = ?
                ORDER BY m.tenMon";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maToHop]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Phê duyệt tổ hợp môn (cập nhật trạng thái thành APPROVED)
     */
    public function pheDuyetToHopMon($maToHop, $lyDo = '', $nguoiDuyet = null) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE tohopmon 
                    SET trangThai = 'APPROVED',
                        ngayDuyet = NOW(),
                        nguoiDuyet = ?,
                        lyDoDuyet = ?
                    WHERE maToHop = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$nguoiDuyet, $lyDo, $maToHop]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Từ chối duyệt tổ hợp môn (cập nhật trạng thái thành REJECTED)
     */
    public function tuChoiDuyetToHopMon($maToHop, $lyDo, $nguoiDuyet = null) {
        try {
            $this->db->beginTransaction();
            
            if (empty($lyDo)) {
                throw new Exception('Lý do từ chối không được để trống');
            }
            
            $sql = "UPDATE tohopmon 
                    SET trangThai = 'REJECTED',
                        ngayDuyet = NOW(),
                        nguoiDuyet = ?,
                        lyDoDuyet = ?
                    WHERE maToHop = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$nguoiDuyet, $lyDo, $maToHop]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lấy thống kê theo trạng thái
     */
    public function getThongKeTrangThai() {
        // Thống kê dựa trên VIEW để phản ánh những join/aggregate đã chuẩn hóa
        $sql = "SELECT trangThai, COUNT(*) AS soLuong FROM vw_bgh_chon_to_hop_mon GROUP BY trangThai";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra tổ hợp môn có tồn tại hay không
     */
    public function checkExist($maToHop) {
        $sql = "SELECT COUNT(*) as count FROM vw_bgh_chon_to_hop_mon WHERE maToHop = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maToHop]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
?>

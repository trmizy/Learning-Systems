<?php
require_once __DIR__ . '/../../config/database.php';

class TaoCacToHopMonModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kiểm tra mã tổ hợp đã tồn tại chưa
     */
    public function checkExist($maToHop) {
        $sql = "SELECT COUNT(*) as count FROM tohopmon WHERE maToHop = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$maToHop]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Validate dữ liệu đầu vào
     */
    public function validateData($data) {
        $errors = [];
        
        // Kiểm tra các trường bắt buộc
        if (empty($data['maToHop'])) {
            $errors[] = "Mã tổ hợp không được để trống";
        }
        if (empty($data['tenToHop'])) {
            $errors[] = "Tên tổ hợp không được để trống";
        }
        if (empty($data['danhSachMon'])) {
            $errors[] = "Danh sách môn không được để trống";
        }
        if (!isset($data['soLuongLop']) || $data['soLuongLop'] <= 0) {
            $errors[] = "Số lượng lớp phải lớn hơn 0";
        }
        // Kiểm tra các môn học có tồn tại trong hệ thống không
        if (!empty($data['danhSachMon'])) {
            $monHocIds = explode(',', $data['danhSachMon']);
            // Kiểm tra số lượng môn trong tổ hợp (không quá 3)
            if (count($monHocIds) > 3) {
                $errors[] = "Không được tạo tổ hợp có nhiều hơn 3 môn";
            }
            foreach ($monHocIds as $monHocId) {
                $sql = "SELECT COUNT(*) as count FROM monhoc WHERE maMonHoc = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$monHocId]);
                $result = $stmt->fetch();
                if ($result['count'] == 0) {
                    $errors[] = "Môn học $monHocId không tồn tại trong hệ thống";
                }
            }
        }

        return $errors;
    }

    /**
     * Tạo tổ hợp môn mới
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Insert vào bảng tohopmon
            // Khi admin tạo, trạng thái mặc định là 'PENDING' (chờ duyệt) - BGH sẽ duyệt sau
            $sql = "INSERT INTO tohopmon (maToHop, tenToHop, danhSachMon, soLuongLop, trangThai) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['maToHop'],
                $data['tenToHop'],
                $data['danhSachMon'],
                $data['soLuongLop'],
                'PENDING'
            ]);

            // Insert các bản ghi liên kết vào tohopmon_monhoc
            $monHocIds = explode(',', $data['danhSachMon']);
            $sql = "INSERT INTO tohopmon_monhoc (maToHop, maMonHoc) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            foreach ($monHocIds as $monHocId) {
                $stmt->execute([$data['maToHop'], $monHocId]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lấy danh sách môn học để hiển thị trong form
     */
    public function getDanhSachMonHoc() {
        $sql = "SELECT maMonHoc, tenMon FROM monhoc";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách tổ hợp môn
     */
    public function getDanhSachToHopMon() {
        $sql = "SELECT maToHop, tenToHop, danhSachMon, soLuongLop, trangThai FROM tohopmon ORDER BY maToHop";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

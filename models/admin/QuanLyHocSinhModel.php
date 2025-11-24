<?php
class QuanLyHocSinhModel
{
    protected $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // search using vw_hocsinh_ho_so view
    public function search(array $criteria = [], $limit = 500)
    {
        $where = [];
        $params = [];
        if (!empty($criteria['maHS'])) {
            $where[] = 'maHS = :maHS';
            $params[':maHS'] = $criteria['maHS'];
        }
        if (!empty($criteria['hoTen'])) {
            $where[] = 'hoTen LIKE :hoTen';
            $params[':hoTen'] = '%' . $criteria['hoTen'] . '%';
        }
        if (!empty($criteria['maLop'])) {
            $where[] = 'maLop = :maLop';
            $params[':maLop'] = $criteria['maLop'];
        }
        if (!empty($criteria['khoi'])) {
            $where[] = 'khoi = :khoi';
            $params[':khoi'] = $criteria['khoi'];
        }

        $sql = "SELECT maHS, hoTen, ngaySinh, soCCCD, diaChi, email, gioiTinh, sdt, maLop, khoi, xepLoaiHocLuc, loaiHanhKiem, diemTrungBinhMon, phuHuynh_info FROM vw_hocsinh_ho_so";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY hoTen ASC LIMIT ' . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $maHS)
    {
        $sql = "SELECT * FROM vw_hocsinh_ho_so WHERE maHS = :maHS LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maHS' => $maHS]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            return null;
        }

        // fetch all parents linked to this student
        $sql = "SELECT ph.maPH, ph.hoTen, ph.soDienThoai, ph.moiQuanHe, ph.diaChi FROM phuhuynh ph JOIN phuhuynh_hocsinh phhs ON ph.maPH = phhs.maPH WHERE phhs.maHS = :maHS";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maHS' => $maHS]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $student['parents'] = $parents;

        // fetch hanhkiem history (kỷ luật)
        $sql = "SELECT maHanhKiem, soBuoiNghiCoPhep, soBuoiNghiKhongCoPhep, soLanViPham, hocKy, namHoc, loaiHanhKiem FROM hanhkiem WHERE maHS = :maHS ORDER BY namHoc DESC, hocKy DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maHS' => $maHS]);
        $hk = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $student['hanhkiem_history'] = $hk;

        return $student;
    }

    // update hocsinh + first parent phone + hanhkiem (loaiHanhKiem)
    public function update(string $maHS, array $data)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE hocsinh SET hoTen = :hoTen, ngaySinh = :ngaySinh, diaChi = :diaChi, email = :email, sdt = :sdt WHERE maHS = :maHS";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hoTen' => $data['hoTen'] ?? null,
                ':ngaySinh' => $data['ngaySinh'] ?? null,
                ':diaChi' => $data['diaChi'] ?? null,
                ':email' => $data['email'] ?? null,
                ':sdt' => $data['sdt'] ?? null,
                ':maHS' => $maHS,
            ]);

            if (isset($data['ph_sdt'])) {
                $sql = "SELECT ph.maPH FROM phuhuynh ph JOIN phuhuynh_hocsinh phhs ON ph.maPH = phhs.maPH WHERE phhs.maHS = :maHS LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':maHS' => $maHS]);
                $ph = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ph) {
                    $sql = "UPDATE phuhuynh SET soDienThoai = :sdt WHERE maPH = :maPH";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':sdt' => $data['ph_sdt'], ':maPH' => $ph['maPH']]);
                }
            }

            if (isset($data['loaiHanhKiem'])) {
                $sql = "SELECT maHanhKiem FROM hanhkiem WHERE maHS = :maHS LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':maHS' => $maHS]);
                $hk = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($hk) {
                    $sql = "UPDATE hanhkiem SET loaiHanhKiem = :loai WHERE maHanhKiem = :maHanhKiem";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':loai' => $data['loaiHanhKiem'], ':maHanhKiem' => $hk['maHanhKiem']]);
                } else {
                    $newId = 'HK' . strtoupper(substr(sha1(uniqid('', true)), 0, 12));
                    $sql = "INSERT INTO hanhkiem (maHanhKiem, loaiHanhKiem, maHS) VALUES (:ma, :loai, :maHS)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':ma' => $newId, ':loai' => $data['loaiHanhKiem'], ':maHS' => $maHS]);
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

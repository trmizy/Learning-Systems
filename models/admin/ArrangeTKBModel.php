<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class ArrangeTKBModel
{
    private PDO $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /* ================== DANH MỤC CƠ BẢN ================== */

    /** Danh sách năm học (ví dụ 2024-2025) */
    public function getDanhSachNamHoc(): array
    {
        $sql = "SELECT DISTINCT namHoc FROM LopHoc ORDER BY namHoc DESC";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /** Danh sách học kỳ (fix) */
    public function getDanhSachHocKy(): array
    {
        return ['HK1', 'HK2'];
    }

    /** Danh sách lớp theo năm học */
    public function getDanhSachLopTheoNamHoc(string $namHoc): array
    {
        $sql = "SELECT maLop, tenLop, khoi 
                FROM LopHoc 
                WHERE namHoc = :namHoc 
                ORDER BY khoi, maLop";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':namHoc' => $namHoc]);
        return $stmt->fetchAll();
    }

    /** Danh sách môn đã được phân công cho lớp trong năm học + học kỳ */
    public function getMonCuaLop(string $maLop, string $namHoc, string $hocKy): array
    {
        $sql = "
            SELECT 
                pc.maMonHoc,
                mh.tenMon,
                mh.soTietTuan,
                pc.maGV,
                gv.hoTen AS tenGV
            FROM phanconggiangday pc
            JOIN monhoc mh ON mh.maMonHoc = pc.maMonHoc
            JOIN giaovienbomon gv ON gv.maGV = pc.maGV
            WHERE pc.maLop = :maLop
              AND pc.namHoc = :namHoc
              AND pc.hocKy = :hocKy
            ORDER BY mh.soTietTuan DESC, mh.maMonHoc
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKy
        ]);
        return $stmt->fetchAll();
    }

    /** Phòng học chính của lớp (nếu có) */
    public function getPhongCuaLop(string $maLop): ?string
    {
        $sql = "SELECT maPhong 
                FROM phonghoc 
                WHERE dangGiaoChoMaLop = :maLop 
                  AND trangThai = 'SU_DUNG'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':maLop' => $maLop]);
        $row = $stmt->fetch();
        return $row['maPhong'] ?? null;
    }

    /** Danh sách phòng học dùng được */
    public function getDanhSachPhongHoc(): array
    {
        $sql = "SELECT maPhong, tenPhong 
                FROM phonghoc
                WHERE trangThai = 'SU_DUNG'
                ORDER BY maPhong";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Danh sách môn toàn hệ thống (để xếp thủ công) */
    public function getDanhSachMonHocTheoNamHoc(string $namHoc, string $hocKy): array
    {
        $sql = "SELECT maMonHoc, tenMon 
                FROM monhoc 
                WHERE namHoc = :namHoc 
                  AND (hocKy = :hocKy OR hocKy = 'CaNam')
                ORDER BY tenMon";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKy
        ]);
        return $stmt->fetchAll();
    }

    /* ================== XỬ LÝ THỜI GIAN HỌC KỲ ================== */

    /**
     * Trả về [ngayBatDau, ngayKetThuc] cho năm học & học kỳ.
     * Giản lược: 
     * - HK1: 01/09 (năm đầu) -> 15/01 (năm sau)
     * - HK2: 16/01 (năm sau) -> 31/05 (năm sau)
     */
    public function getKhoangThoiGianHocKy(string $namHoc, string $hocKy): array
    {
        // Ví dụ "2024-2025"
        [$y1, $y2] = explode('-', $namHoc);
        $y1 = (int)$y1;
        $y2 = (int)$y2;

        if ($hocKy === 'HK1') {
            $start = sprintf('%04d-09-01', $y1);
            $end   = sprintf('%04d-01-15', $y2);
        } else {
            // HK2
            $start = sprintf('%04d-01-16', $y2);
            $end   = sprintf('%04d-05-31', $y2);
        }

        // Đẩy start về thứ 2 đầu tiên (Monday)
        $startTs = strtotime($start);
        $dow = (int)date('N', $startTs); // 1=Mon ... 7=Sun
        if ($dow > 1) {
            $startTs = strtotime('next monday', $startTs);
        }
        $start = date('Y-m-d', $startTs);

        return [$start, $end];
    }

    /** Trả về mảng [2=>ngàyThứ2, 3=>..., 6=>...] cho tuần mẫu */
    public function getNgayTungThuTrongTuan(string $namHoc, string $hocKy): array
    {
        [$start, ] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $mondayTs = strtotime($start);
        $result = [];
        // Thứ 2..6
        for ($thu = 2; $thu <= 6; $thu++) {
            $offset = $thu - 2;
            $result[$thu] = date('Y-m-d', strtotime("+$offset day", $mondayTs));
        }
        return $result;
    }

    /* ================== AUTO TKB (PREVIEW) ================== */

    /**
     * Tạo thời khóa biểu đề xuất cho tất cả lớp trong năm học & học kỳ.
     * Trả về:
     * [
     *   '10A1' => [ [row], [row], ... ],
     *   '10A2' => [ ... ],
     *   ...
     * ]
     * Mỗi row gồm: thu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, tenMon, maGV, tenGV, maPhong
     */
    public function generateAutoTKBForAll(string $namHoc, string $hocKy): array
    {
        $result = [];
        $lopList = $this->getDanhSachLopTheoNamHoc($namHoc);
        $ngayThu = $this->getNgayTungThuTrongTuan($namHoc, $hocKy);
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        foreach ($lopList as $lop) {
            $maLop = $lop['maLop'];
            $monList = $this->getMonCuaLop($maLop, $namHoc, $hocKy);
            if (empty($monList)) {
                // Không có phân công -> bỏ qua
                $result[$maLop] = [];
                continue;
            }

            $maPhong = $this->getPhongCuaLop($maLop) ?? null;

            // ma trận [thu][tiet] = null|row
            $grid = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 7; $tiet++) {
                    $grid[$thu][$tiet] = null;
                }
            }

            // Chào cờ & sinh hoạt
            $grid[2][1] = [
                'thu'        => 2,
                'tiet'       => 1,
                'loaiTiet'   => 'Chao_co',
                'ngayHoc'    => $ngayThu[2],
                'ngayKetThuc'=> $ngayKetThuc,
                'maLop'      => $maLop,
                'maMonHoc'   => null,
                'tenMon'     => 'Chào cờ',
                'maGV'       => null,
                'tenGV'      => null,
                'maPhong'    => $maPhong
            ];

            $grid[6][4] = [
                'thu'        => 6,
                'tiet'       => 4,
                'loaiTiet'   => 'Sinh_hoat',
                'ngayHoc'    => $ngayThu[6],
                'ngayKetThuc'=> $ngayKetThuc,
                'maLop'      => $maLop,
                'maMonHoc'   => null,
                'tenMon'     => 'Sinh hoạt lớp',
                'maGV'       => null,
                'tenGV'      => null,
                'maPhong'    => $maPhong
            ];

            // Sắp môn: duyệt theo môn, lấp slot trống
            foreach ($monList as $mon) {
                $soTiet = (int)$mon['soTietTuan'];
                for ($i = 0; $i < $soTiet; $i++) {
                    $pos = $this->timSlotTrong($grid, $mon['maMonHoc']);
                    if (!$pos) {
                        // hết chỗ trống, thôi kệ, môn này thiếu tiết
                        break;
                    }
                    [$thu, $tiet] = $pos;
                    $grid[$thu][$tiet] = [
                        'thu'        => $thu,
                        'tiet'       => $tiet,
                        'loaiTiet'   => 'Chinh_khoa',
                        'ngayHoc'    => $ngayThu[$thu],
                        'ngayKetThuc'=> $ngayKetThuc,
                        'maLop'      => $maLop,
                        'maMonHoc'   => $mon['maMonHoc'],
                        'tenMon'     => $mon['tenMon'],
                        'maGV'       => $mon['maGV'],
                        'tenGV'      => $mon['tenGV'],
                        'maPhong'    => $maPhong
                    ];
                }
            }

            // Convert về list row
            $rows = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 7; $tiet++) {
                    if ($grid[$thu][$tiet] !== null) {
                        $rows[] = $grid[$thu][$tiet];
                    }
                }
            }

            $result[$maLop] = $rows;
        }

        return $result;
    }

    /**
     * Tìm slot trống cho môn.
     * Rule đơn giản:
     *  - Duyệt từ Thứ 2 → Thứ 6, Tiết 1 → 7
     *  - Bỏ qua ô đã có dữ liệu
     *  - Hạn chế 1 môn không > 2 tiết / ngày (tránh dồn)
     */
    private function timSlotTrong(array $grid, string $maMonHoc): ?array
    {
        // Đếm số tiết của môn theo từng ngày
        $countPerDay = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $countPerDay[$thu] = 0;
            for ($tiet = 1; $tiet <= 7; $tiet++) {
                $cell = $grid[$thu][$tiet] ?? null;
                if ($cell && $cell['maMonHoc'] === $maMonHoc) {
                    $countPerDay[$thu]++;
                }
            }
        }

        for ($thu = 2; $thu <= 6; $thu++) {
            for ($tiet = 1; $tiet <= 7; $tiet++) {
                // bỏ slot đã dùng
                if ($grid[$thu][$tiet] !== null) continue;
                // tránh cho môn > 2 tiết / ngày
                if ($countPerDay[$thu] >= 2) continue;

                return [$thu, $tiet];
            }
        }
        return null;
    }

    /* ================== LẤY / LƯU TKB ================== */

    /** Lấy TKB đã lưu trong DB cho 1 lớp */
    public function getTKBFromDb(string $maLop, string $namHoc, string $hocKy): array
    {
        [$ngayBatDau, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $sql = "
            SELECT 
                tkb.maThoiKhoaBieu,
                tkb.tiet,
                tkb.loaiTiet,
                tkb.ngayHoc,
                tkb.ngayKetThuc,
                tkb.maLop,
                tkb.maMonHoc,
                mh.tenMon,
                tkb.maPhong,
                pc.maGV,
                gv.hoTen AS tenGV
            FROM thoikhoabieu tkb
            LEFT JOIN monhoc mh ON mh.maMonHoc = tkb.maMonHoc
            LEFT JOIN phanconggiangday pc 
                   ON pc.maLop = tkb.maLop 
                  AND pc.maMonHoc = tkb.maMonHoc
                  AND pc.namHoc = :namHoc
                  AND pc.hocKy  = :hocKy
            LEFT JOIN giaovienbomon gv ON gv.maGV = pc.maGV
            WHERE tkb.maLop = :maLop
              AND tkb.ngayHoc = :ngayBatDau
              AND tkb.ngayKetThuc = :ngayKetThuc
            ORDER BY tkb.ngayHoc, tkb.tiet
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'      => $maLop,
            ':namHoc'     => $namHoc,
            ':hocKy'      => $hocKy,
            ':ngayBatDau' => $ngayBatDau,
            ':ngayKetThuc'=> $ngayKetThuc
        ]);
        $rows = $stmt->fetchAll();

        // Đính thêm 'thu' (2..6) dựa vào ngayHoc
        $result = [];
        foreach ($rows as $row) {
            $dow = (int)date('N', strtotime($row['ngayHoc'])); // 1..7
            $row['thu'] = $dow;
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Kiểm tra trùng lịch với DB hiện tại.
     *  - Trùng giáo viên cùng thời điểm
     *  - Trùng phòng cùng thời điểm
     * Trả về danh sách lỗi.
     */
    public function findConflicts(array $plan, string $namHoc, string $hocKy, string $maLop): array
    {
        if (empty($plan)) return [];

        [$ngayBatDau, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $errors = [];

        foreach ($plan as $row) {
            if (empty($row['maGV']) && empty($row['maPhong'])) {
                continue;
            }

            $sql = "
                SELECT tkb.maLop, tkb.tiet, tkb.ngayHoc, tkb.maMonHoc, tkb.maPhong,
                       pc.maGV, gv.hoTen AS tenGV
                FROM thoikhoabieu tkb
                LEFT JOIN phanconggiangday pc 
                       ON pc.maLop = tkb.maLop 
                      AND pc.maMonHoc = tkb.maMonHoc
                      AND pc.namHoc = :namHoc
                      AND pc.hocKy  = :hocKy
                LEFT JOIN giaovienbomon gv ON gv.maGV = pc.maGV
                WHERE tkb.ngayHoc = :ngayHoc
                  AND tkb.tiet    = :tiet
                  AND tkb.ngayKetThuc = :ngayKetThuc
                  AND tkb.maLop <> :maLop
                  AND (
                        (pc.maGV IS NOT NULL AND pc.maGV = :maGV)
                     OR (tkb.maPhong IS NOT NULL AND tkb.maPhong = :maPhong)
                  )
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':namHoc'     => $namHoc,
                ':hocKy'      => $hocKy,
                ':ngayHoc'    => $row['ngayHoc'],
                ':ngayKetThuc'=> $ngayKetThuc,
                ':tiet'       => $row['tiet'],
                ':maLop'      => $maLop,
                ':maGV'       => $row['maGV'] ?? '',
                ':maPhong'    => $row['maPhong'] ?? ''
            ]);

            $conflicts = $stmt->fetchAll();
            foreach ($conflicts as $c) {
                $errors[] = [
                    'slot'    => sprintf('Thứ %d - Tiết %d', (int)date('N', strtotime($row['ngayHoc'])), $row['tiet']),
                    'lop'     => $c['maLop'],
                    'phong'   => $c['maPhong'],
                    'gv'      => $c['tenGV'],
                    'maGV'    => $c['maGV']
                ];
            }
        }

        return $errors;
    }

    /** Lưu TKB (ghi đè) cho 1 lớp */
    public function saveTKBForClass(string $maLop, string $namHoc, string $hocKy, array $plan): void
    {
        [$ngayBatDau, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        // Xóa TKB cũ của lớp trong khoảng học kỳ
        $delSql = "
            DELETE FROM thoikhoabieu
            WHERE maLop = :maLop
              AND ngayHoc = :ngayBatDau
              AND ngayKetThuc = :ngayKetThuc
        ";
        $del = $this->conn->prepare($delSql);
        $del->execute([
            ':maLop'      => $maLop,
            ':ngayBatDau' => $ngayBatDau,
            ':ngayKetThuc'=> $ngayKetThuc
        ]);

        // Chèn mới
        $insSql = "
            INSERT INTO thoikhoabieu
            (maThoiKhoaBieu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, maPhong)
            VALUES (:maTKB, :tiet, :loaiTiet, :ngayHoc, :ngayKetThuc, :maLop, :maMonHoc, :maPhong)
        ";
        $ins = $this->conn->prepare($insSql);

        foreach ($plan as $row) {
            $maTKB = sprintf(
                'TKB_%s_%s_%d',
                $maLop,
                str_replace('-', '', $row['ngayHoc']),
                $row['tiet']
            );
            $ins->execute([
                ':maTKB'       => $maTKB,
                ':tiet'        => $row['tiet'],
                ':loaiTiet'    => $row['loaiTiet'],
                ':ngayHoc'     => $row['ngayHoc'],
                ':ngayKetThuc' => $row['ngayKetThuc'],
                ':maLop'       => $maLop,
                ':maMonHoc'    => $row['maMonHoc'],
                ':maPhong'     => $row['maPhong']
            ]);
        }
    }

    /** Thêm 1 ô TKB thủ công (cho slot đang trống) */
    public function addManualSlot(array $data): bool
    {
        [$ngayBatDau, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($data['namHoc'], $data['hocKy']);

        // Kiểm tra đã tồn tại slot hay chưa
        $checkSql = "
            SELECT COUNT(*) 
            FROM thoikhoabieu
            WHERE maLop   = :maLop
              AND ngayHoc = :ngayHoc
              AND ngayKetThuc = :ngayKetThuc
              AND tiet    = :tiet
        ";
        $chk = $this->conn->prepare($checkSql);
        $chk->execute([
            ':maLop'      => $data['maLop'],
            ':ngayHoc'    => $data['ngayHoc'],
            ':ngayKetThuc'=> $ngayKetThuc,
            ':tiet'       => $data['tiet']
        ]);
        if ((int)$chk->fetchColumn() > 0) {
            return false;
        }

        $maTKB = sprintf(
            'TKB_%s_%s_%d',
            $data['maLop'],
            str_replace('-', '', $data['ngayHoc']),
            $data['tiet']
        );

        $sql = "
            INSERT INTO thoikhoabieu
            (maThoiKhoaBieu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, maPhong)
            VALUES (:maTKB, :tiet, :loaiTiet, :ngayHoc, :ngayKetThuc, :maLop, :maMonHoc, :maPhong)
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':maTKB'       => $maTKB,
            ':tiet'        => $data['tiet'],
            ':loaiTiet'    => 'Chinh_khoa',
            ':ngayHoc'     => $data['ngayHoc'],
            ':ngayKetThuc' => $ngayKetThuc,
            ':maLop'       => $data['maLop'],
            ':maMonHoc'    => $data['maMonHoc'],
            ':maPhong'     => $data['maPhong']
        ]);
    }
}

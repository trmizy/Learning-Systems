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

    /* ================== Helpers ================== */

    private function normalizeHocKy(string $hocKy): string
    {
        $hocKy = trim($hocKy);
        if ($hocKy === 'HK1') return '1';
        if ($hocKy === 'HK2') return '2';
        return $hocKy; // '1' hoặc '2'
    }

    /** Thứ VN (2..6) -> offset so với Thứ 2 */
    private function offsetFromThu2(int $thuVN): int
    {
        return $thuVN - 2; // 2->0, 3->1, ..., 6->4
    }

    /* ================== DANH MỤC CƠ BẢN ================== */

    public function getNamHocHienTai(): string
    {
        $sql = "SELECT MAX(namHoc) FROM lophoc";
        $v = $this->conn->query($sql)->fetchColumn();
        return $v ? (string)$v : '';
    }

    public function getDanhSachNamHoc(): array
    {
        $sql = "SELECT DISTINCT namHoc FROM lophoc ORDER BY namHoc DESC";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    /** DB đang dùng 1/2 */
    public function getDanhSachHocKy(): array
    {
        return ['1', '2'];
    }

    public function getDanhSachLopTheoNamHoc(string $namHoc): array
    {
        $sql = "SELECT maLop, tenLop, khoi
                FROM lophoc
                WHERE namHoc = :namHoc
                ORDER BY khoi, maLop";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':namHoc' => $namHoc]);
        return $stmt->fetchAll();
    }

    public function getDanhSachPhongHoc(): array
    {
        $sql = "SELECT maPhong, tenPhong
                FROM phonghoc
                WHERE trangThai = 'SU_DUNG'
                ORDER BY maPhong";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Phòng của lớp lấy từ phancongphonghoc */
    public function getPhongCuaLop(string $maLop, string $namHoc): ?string
    {
        $sql = "
            SELECT pcp.maPhong
            FROM phancongphonghoc pcp
            JOIN phonghoc ph ON ph.maPhong = pcp.maPhong
            WHERE pcp.maLop = :maLop
              AND pcp.namHoc = :namHoc
              AND ph.trangThai = 'SU_DUNG'
            ORDER BY pcp.ngayPhanCong DESC
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc
        ]);
        $row = $stmt->fetch();
        return $row['maPhong'] ?? null;
    }

    /** Danh sách môn đã được phân công dạy cho lớp trong năm học + học kỳ */
    public function getMonCuaLop(string $maLop, string $namHoc, string $hocKy): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        $tongTuan = $this->getTongSoTuanHocKy($namHoc, $hocKyDb);

        $sql = "
            SELECT
                pc.maMonHoc,
                mh.tenMon,
                mh.soTietTuan,
                pc.maGV,
                gv.hoTen AS tenGV
            FROM phanconggiangday pc
            JOIN monhoc mh ON mh.maMonHoc = pc.maMonHoc
            LEFT JOIN giaovienbomon gv ON gv.maGV = pc.maGV
            WHERE pc.maLop = :maLop
              AND pc.namHoc = :namHoc
              AND pc.hocKy  = :hocKy
            ORDER BY mh.soTietTuan DESC, mh.maMonHoc
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKyDb
        ]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['soTietTuan']  = (int)($r['soTietTuan'] ?? 0);
            $r['soTietHocKy'] = (int)$r['soTietTuan'] * max(1, $tongTuan);
        }
        unset($r);

        // sort: tổng tiết HK desc, rồi tiết/tuần desc
        usort($rows, function ($a, $b) {
            $x = (int)($b['soTietHocKy'] ?? 0) <=> (int)($a['soTietHocKy'] ?? 0);
            if ($x !== 0) return $x;
            return (int)($b['soTietTuan'] ?? 0) <=> (int)($a['soTietTuan'] ?? 0);
        });

        return $rows;
    }

    public function getGiaoVienDayMonCuaLop(string $maLop, string $maMonHoc, string $namHoc, string $hocKy): ?array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        $sql = "
            SELECT pc.maGV, gv.hoTen AS tenGV
            FROM phanconggiangday pc
            LEFT JOIN giaovienbomon gv ON gv.maGV = pc.maGV
            WHERE pc.maLop = :maLop
              AND pc.maMonHoc = :maMonHoc
              AND pc.namHoc = :namHoc
              AND pc.hocKy = :hocKy
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'    => $maLop,
            ':maMonHoc' => $maMonHoc,
            ':namHoc'   => $namHoc,
            ':hocKy'    => $hocKyDb
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /* ================== THỜI GIAN HỌC KỲ (KHÔNG CẦN BẢNG HOCKY) ================== */

    /**
     * Trả về [ngayBatDau, ngayKetThuc] cho năm học & học kỳ.
     * Giản lược:
     * - HK1 (1): 01/09 (năm đầu) -> 15/01 (năm sau)
     * - HK2 (2): 16/01 (năm sau) -> 31/05 (năm sau)
     * Start được đẩy về thứ 2 đầu tiên.
     */
    public function getKhoangThoiGianHocKy(string $namHoc, string $hocKy): array
    {
        $hocKy = $this->normalizeHocKy($hocKy);

        [$y1, $y2] = explode('-', $namHoc);
        $y1 = (int)$y1;
        $y2 = (int)$y2;

        if ($hocKy === '1') {
            $start = sprintf('%04d-09-01', $y1);
            $end   = sprintf('%04d-01-15', $y2);
        } else {
            $start = sprintf('%04d-01-16', $y2);
            $end   = sprintf('%04d-05-31', $y2);
        }

        // đẩy start về thứ 2 đầu tiên
        $startTs = strtotime($start);
        $dow = (int)date('N', $startTs); // 1=Mon..7=Sun
        if ($dow > 1) {
            $startTs = strtotime('next monday', $startTs);
        }
        $start = date('Y-m-d', $startTs);

        return [$start, $end];
    }

    public function getTongSoTuanHocKy(string $namHoc, string $hocKy): int
    {
        $hocKy = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $startTs = strtotime($start);
        $endTs   = strtotime($end);
        if ($endTs <= $startTs) return 1;

        $days = (int)floor(($endTs - $startTs) / 86400) + 1;
        return (int)ceil($days / 7);
    }

    public function getThongTinHocKy(string $namHoc, string $hocKy): array
    {
        $hocKy = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $totalWeeks = $this->getTongSoTuanHocKy($namHoc, $hocKy);

        return [
            'start'      => $start,
            'end'        => $end,
            'week_start' => 1,
            'week_end'   => $totalWeeks,
        ];
    }

    public function getDanhSachTuanHocKy(string $namHoc, string $hocKy): array
    {
        $totalWeeks = $this->getTongSoTuanHocKy($namHoc, $hocKy);
        $out = [];
        for ($w = 1; $w <= $totalWeeks; $w++) $out[] = ['tuan' => $w];
        return $out;
    }

    public function getDanhSachThangHocKy(string $namHoc, string $hocKy): array
    {
        $hocKy = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $startDt = new DateTime(substr($start, 0, 7) . '-01');
        $endDt   = new DateTime(substr($end, 0, 7) . '-01');

        $months = [];
        while ($startDt <= $endDt) {
            $months[] = $startDt->format('Y-m');
            $startDt->modify('+1 month');
        }
        return $months;
    }

    public function getTuanDauTienTrongThang(string $namHoc, string $hocKy, string $thang): int
    {
        $hocKy = $this->normalizeHocKy($hocKy);
        [$start, ] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $startTs = strtotime($start);
        $firstDayMonthTs = strtotime($thang . '-01');
        if ($firstDayMonthTs < $startTs) return 1;

        $diffDays = (int)floor(($firstDayMonthTs - $startTs) / 86400);
        return (int)floor($diffDays / 7) + 1;
    }

    /** Trả về mảng [2=>dateMon, 3=>..., 6=>...] theo tuần đang xem */
    public function getNgayTungThuTrongTuanTheoTuan(string $namHoc, string $hocKy, int $tuan): array
    {
        $hocKy = $this->normalizeHocKy($hocKy);
        [$start, ] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $tuan = max(1, $tuan);
        $mondayTs = strtotime($start . ' +' . (($tuan - 1) * 7) . ' days');

        $result = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $offset = $this->offsetFromThu2($thu);
            $result[$thu] = date('Y-m-d', strtotime("+$offset day", $mondayTs));
        }
        return $result;
    }

    private function getNgayTheoThuTrongTuan(string $namHoc, string $hocKy, int $tuan, int $thuVN): string
    {
        $map = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, $tuan);
        return $map[$thuVN] ?? $map[2];
    }

    /* ================== DB BUSY MAP (GV/PHÒNG) ================== */

    private function buildBusyMapFromDb(string $namHoc, string $hocKy): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);

        // Lọc theo lớp thuộc năm học để không dính dữ liệu năm khác
        $sql = "
            SELECT tkb.maGV, tkb.maPhong, tkb.ngayHoc, tkb.tiet
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            WHERE lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.ngayHoc BETWEEN :start AND :end
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKyDb,
            ':start'  => $start,
            ':end'    => $end
        ]);
        $rows = $stmt->fetchAll();

        $teacherBusy = [];
        $roomBusy = [];

        foreach ($rows as $r) {
            $thu = (int)date('N', strtotime($r['ngayHoc'])) + 1; // Mon->2
            $tiet = (int)$r['tiet'];

            if (!empty($r['maGV'])) {
                $teacherBusy[$r['maGV']][$thu][$tiet] = true;
            }
            if (!empty($r['maPhong'])) {
                $roomBusy[$r['maPhong']][$thu][$tiet] = true;
            }
        }

        return [$teacherBusy, $roomBusy];
    }

    private function isBusy(array $busyMap, ?string $key, int $thu, int $tiet): bool
    {
        if (!$key) return false;
        return !empty($busyMap[$key][$thu][$tiet]);
    }

    /* ================== AUTO TKB (PREVIEW PATTERN) ================== */

    /**
     * Trả về pattern theo tuần:
     * [
     *   '10A1' => [ ['thu'=>2,'tiet'=>1,...], ... ],
     *   ...
     * ]
     */
    public function generateAutoTKBForAll(string $namHoc, string $hocKy): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);

        $result = [];
        $lopList = $this->getDanhSachLopTheoNamHoc($namHoc);

        // busy map từ DB hiện tại
        [$teacherBusy, $roomBusy] = $this->buildBusyMapFromDb($namHoc, $hocKyDb);

        foreach ($lopList as $lop) {
            $maLop = $lop['maLop'];

            $monList = $this->getMonCuaLop($maLop, $namHoc, $hocKyDb);
            if (empty($monList)) {
                $result[$maLop] = [];
                continue;
            }

            $maPhong = $this->getPhongCuaLop($maLop, $namHoc) ?? null;

            // grid [thu 2..6][tiet 1..8]
            $grid = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 8; $tiet++) {
                    $grid[$thu][$tiet] = null;
                }
            }

            // Cố định
            $grid[2][1] = [
                'thu'      => 2,
                'tiet'     => 1,
                'loaiTiet' => 'Chao_co',
                'maLop'    => $maLop,
                'maMonHoc' => null,
                'tenMon'   => 'Chào cờ',
                'maGV'     => null,
                'tenGV'    => null,
                'maPhong'  => $maPhong
            ];
            $grid[6][4] = [
                'thu'      => 6,
                'tiet'     => 4,
                'loaiTiet' => 'Sinh_hoat',
                'maLop'    => $maLop,
                'maMonHoc' => null,
                'tenMon'   => 'Sinh hoạt lớp',
                'maGV'     => null,
                'tenGV'    => null,
                'maPhong'  => $maPhong
            ];

            // Sắp theo môn (đã sort theo tổng tiết HK + tiết/tuần)
            foreach ($monList as $mon) {
                $soTiet = (int)$mon['soTietTuan'];
                $maGV   = $mon['maGV'] ?? null;

                for ($i = 0; $i < $soTiet; $i++) {
                    $pos = $this->timSlotTrongTranhTrung($grid, $mon['maMonHoc'], $maGV, $maPhong, $teacherBusy, $roomBusy);
                    if (!$pos) break;

                    [$thu, $tiet] = $pos;

                    $grid[$thu][$tiet] = [
                        'thu'      => $thu,
                        'tiet'     => $tiet,
                        'loaiTiet' => 'Chinh_khoa',
                        'maLop'    => $maLop,
                        'maMonHoc' => $mon['maMonHoc'],
                        'tenMon'   => $mon['tenMon'],
                        'maGV'     => $maGV,
                        'tenGV'    => $mon['tenGV'] ?? null,
                        'maPhong'  => $maPhong
                    ];

                    // đánh dấu busy để lớp sau không trùng
                    if ($maGV) $teacherBusy[$maGV][$thu][$tiet] = true;
                    if ($maPhong) $roomBusy[$maPhong][$thu][$tiet] = true;
                }
            }

            // Convert grid -> rows
            $rows = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 8; $tiet++) {
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
     * Rule tìm slot:
     * - duyệt Thứ 2..6, Tiết 1..8
     * - ô trống
     * - tránh dồn 1 môn > 2 tiết/ngày
     * - tránh trùng GV/Phòng (busy map)
     */
    private function timSlotTrongTranhTrung(array $grid, string $maMonHoc, ?string $maGV, ?string $maPhong, array $teacherBusy, array $roomBusy): ?array
    {
        $countPerDay = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $countPerDay[$thu] = 0;
            for ($tiet = 1; $tiet <= 8; $tiet++) {
                $cell = $grid[$thu][$tiet] ?? null;
                if ($cell && ($cell['maMonHoc'] ?? '') === $maMonHoc) {
                    $countPerDay[$thu]++;
                }
            }
        }

        for ($thu = 2; $thu <= 6; $thu++) {
            for ($tiet = 1; $tiet <= 8; $tiet++) {
                if ($grid[$thu][$tiet] !== null) continue;
                if ($countPerDay[$thu] >= 2) continue;

                // tránh trùng GV/Phòng
                if ($this->isBusy($teacherBusy, $maGV, $thu, $tiet)) continue;
                if ($this->isBusy($roomBusy, $maPhong, $thu, $tiet)) continue;

                // tránh chiếm slot cố định (đã set rồi nên thực tế không vào đây)
                if ($thu === 2 && $tiet === 1) continue;
                if ($thu === 6 && $tiet === 4) continue;

                return [$thu, $tiet];
            }
        }
        return null;
    }

    /* ================== SLOT TRỐNG (THỦ CÔNG) ================== */

    /**
     * Trả về slot trống dạng [ ['thu'=>2,'tiet'=>5], ... ]
     * Có check trùng GV/phòng theo môn được chọn.
     */
    public function getSlotTrongCuaLop(string $maLop, string $namHoc, string $hocKy, string $maMonHoc): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);

        // slot đã có của lớp (pattern theo thứ/tiết)
        $sql = "
            SELECT DISTINCT DAYOFWEEK(tkb.ngayHoc) AS thu, tkb.tiet
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            WHERE tkb.maLop = :maLop
              AND lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.ngayHoc BETWEEN :start AND :end
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKyDb,
            ':start'  => $start,
            ':end'    => $end
        ]);
        $occRows = $stmt->fetchAll();

        $occupied = [];
        foreach ($occRows as $r) {
            $occupied[(int)$r['thu']][(int)$r['tiet']] = true;
        }

        // cố định chào cờ & sinh hoạt
        $occupied[2][1] = true;
        $occupied[6][4] = true;

        // thông tin GV + phòng để check trùng
        $gv = $this->getGiaoVienDayMonCuaLop($maLop, $maMonHoc, $namHoc, $hocKyDb);
        $maGV = $gv['maGV'] ?? null;

        $maPhong = $this->getPhongCuaLop($maLop, $namHoc) ?? null;

        $slots = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            for ($tiet = 1; $tiet <= 8; $tiet++) {
                if (!empty($occupied[$thu][$tiet])) continue;

                // tránh trùng GV/Phòng với lớp khác
                if ($this->hasDbConflictByThuTiet($namHoc, $hocKyDb, $maLop, $thu, $tiet, $maGV, $maPhong)) {
                    continue;
                }

                $slots[] = ['thu' => $thu, 'tiet' => $tiet];
            }
        }
        return $slots;
    }

    /* ================== CONFLICT CHECK (PATTERN) ================== */

    /** Check trùng theo pattern (thứ/tiết) với DB */
    public function findConflictsPattern(array $planPattern, string $namHoc, string $hocKy, string $maLop): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        $errors = [];

        foreach ($planPattern as $row) {
            $thu  = (int)($row['thu'] ?? 0);
            $tiet = (int)($row['tiet'] ?? 0);
            $maGV = $row['maGV'] ?? null;
            $maPhong = $row['maPhong'] ?? null;

            if ($thu < 2 || $thu > 6 || $tiet < 1 || $tiet > 8) continue;

            $conf = $this->getDbConflictsByThuTiet($namHoc, $hocKyDb, $maLop, $thu, $tiet, $maGV, $maPhong);
            foreach ($conf as $c) {
                $errors[] = [
                    'slot'  => sprintf('Thứ %d - Tiết %d', $thu, $tiet),
                    'lop'   => $c['maLop'] ?? '',
                    'phong' => $c['maPhong'] ?? '',
                    'gv'    => $c['tenGV'] ?? ($c['maGV'] ?? ''),
                    'maGV'  => $c['maGV'] ?? ''
                ];
            }
        }

        return $errors;
    }

    private function hasDbConflictByThuTiet(string $namHoc, string $hocKyDb, string $maLop, int $thu, int $tiet, ?string $maGV, ?string $maPhong): bool
    {
        $conf = $this->getDbConflictsByThuTiet($namHoc, $hocKyDb, $maLop, $thu, $tiet, $maGV, $maPhong);
        return !empty($conf);
    }

    private function getDbConflictsByThuTiet(string $namHoc, string $hocKyDb, string $maLop, int $thu, int $tiet, ?string $maGV, ?string $maPhong): array
    {
        if (!$maGV && !$maPhong) return [];

        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);

        $sql = "
            SELECT
                tkb.maLop, tkb.maGV, tkb.maPhong,
                gv.hoTen AS tenGV
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            LEFT JOIN giaovienbomon gv ON gv.maGV = tkb.maGV
            WHERE lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.tiet = :tiet
              AND DAYOFWEEK(tkb.ngayHoc) = :thu
              AND tkb.maLop <> :maLop
              AND tkb.ngayHoc BETWEEN :start AND :end
              AND (
                    (:maGV <> '' AND tkb.maGV = :maGV)
                 OR (:maPhong <> '' AND tkb.maPhong = :maPhong)
              )
            LIMIT 50
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':namHoc'  => $namHoc,
            ':hocKy'   => $hocKyDb,
            ':tiet'    => $tiet,
            ':thu'     => $thu,
            ':maLop'   => $maLop,
            ':start'   => $start,
            ':end'     => $end,
            ':maGV'    => $maGV ?? '',
            ':maPhong' => $maPhong ?? ''
        ]);
        return $stmt->fetchAll();
    }

    /* ================== LẤY TKB THEO TUẦN (ĐỂ FILTER) ================== */

    public function getTKBByWeek(string $maLop, string $namHoc, string $hocKy, int $tuan): array
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);

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
                tkb.maGV,
                gv.hoTen AS tenGV,
                tkb.tuanHoc
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            LEFT JOIN monhoc mh ON mh.maMonHoc = tkb.maMonHoc
            LEFT JOIN giaovienbomon gv ON gv.maGV = tkb.maGV
            WHERE tkb.maLop = :maLop
              AND lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.tuanHoc = :tuan
              AND tkb.ngayHoc BETWEEN :start AND :end
            ORDER BY tkb.ngayHoc, tkb.tiet
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKyDb,
            ':tuan'   => max(1, $tuan),
            ':start'  => $start,
            ':end'    => $end
        ]);
        $rows = $stmt->fetchAll();

        // gắn thêm thu VN 2..6
        $out = [];
        foreach ($rows as $r) {
            $r['thu'] = (int)date('N', strtotime($r['ngayHoc'])) + 1;
            $out[] = $r;
        }
        return $out;
    }

    /* ================== LƯU / THÊM TKB (GHI THEO TỪNG TUẦN) ================== */

    /** Xóa toàn bộ TKB của lớp trong học kỳ */
    private function deleteTKBOfClassInSemester(string $maLop, string $namHoc, string $hocKyDb): void
    {
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);
        $sql = "
            DELETE tkb
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            WHERE tkb.maLop = :maLop
              AND lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.ngayHoc BETWEEN :start AND :end
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKyDb,
            ':start'  => $start,
            ':end'    => $end
        ]);
    }

    /**
     * Lưu TKB từ pattern (thứ/tiết) => nhân ra tất cả tuần trong HK (có tuanHoc)
     */
    public function saveTKBForClassFromPattern(string $maLop, string $namHoc, string $hocKy, array $pattern): void
    {
        $hocKyDb = $this->normalizeHocKy($hocKy);
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKyDb);
        $totalWeeks = $this->getTongSoTuanHocKy($namHoc, $hocKyDb);

        $this->conn->beginTransaction();
        try {
            $this->deleteTKBOfClassInSemester($maLop, $namHoc, $hocKyDb);

            $ins = $this->conn->prepare("
                INSERT INTO thoikhoabieu
                (maThoiKhoaBieu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, maPhong, maGV, soTiet, hocKy, ghiChu, tuanHoc)
                VALUES
                (:maTKB, :tiet, :loaiTiet, :ngayHoc, :ngayKetThuc, :maLop, :maMonHoc, :maPhong, :maGV, :soTiet, :hocKy, :ghiChu, :tuanHoc)
            ");

            foreach ($pattern as $row) {
                $thu = (int)($row['thu'] ?? 0);
                $tiet = (int)($row['tiet'] ?? 0);
                if ($thu < 2 || $thu > 6 || $tiet < 1 || $tiet > 8) continue;

                for ($w = 1; $w <= $totalWeeks; $w++) {
                    $ngayHoc = $this->getNgayTheoThuTrongTuan($namHoc, $hocKyDb, $w, $thu);

                    $maTKB = sprintf(
                        'TKB_%s_%s_W%d_T%d',
                        $maLop,
                        str_replace('-', '', $ngayHoc),
                        $w,
                        $tiet
                    );

                    $ins->execute([
                        ':maTKB'       => $maTKB,
                        ':tiet'        => $tiet,
                        ':loaiTiet'    => $row['loaiTiet'] ?? 'Chinh_khoa',
                        ':ngayHoc'     => $ngayHoc,
                        ':ngayKetThuc' => $ngayKetThuc,
                        ':maLop'       => $maLop,
                        ':maMonHoc'    => $row['maMonHoc'] ?? null,
                        ':maPhong'     => $row['maPhong'] ?? null,
                        ':maGV'        => $row['maGV'] ?? null,
                        ':soTiet'      => 1,
                        ':hocKy'       => $hocKyDb,
                        ':ghiChu'      => null,
                        ':tuanHoc'     => $w
                    ]);
                }
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('saveTKBForClassFromPattern: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Thêm 1 slot (pattern) và nhân ra tất cả tuần.
     * Return false nếu slot đã tồn tại cho lớp (theo thứ/tiết) trong HK.
     */
    public function addManualSlotToAllWeeks(array $data): bool
    {
        $hocKyDb = $this->normalizeHocKy((string)$data['hocKy']);
        $thu  = (int)$data['thu'];
        $tiet = (int)$data['tiet'];

        [$start, $end] = $this->getKhoangThoiGianHocKy($data['namHoc'], $hocKyDb);

        // check tồn tại slot theo thứ/tiết (bất kỳ tuần nào)
        $chk = $this->conn->prepare("
            SELECT COUNT(*)
            FROM thoikhoabieu tkb
            JOIN lophoc lh ON lh.maLop = tkb.maLop
            WHERE tkb.maLop = :maLop
              AND lh.namHoc = :namHoc
              AND tkb.hocKy = :hocKy
              AND tkb.tiet = :tiet
              AND DAYOFWEEK(tkb.ngayHoc) = :thu
              AND tkb.ngayHoc BETWEEN :start AND :end
        ");
        $chk->execute([
            ':maLop'  => $data['maLop'],
            ':namHoc' => $data['namHoc'],
            ':hocKy'  => $hocKyDb,
            ':tiet'   => $tiet,
            ':thu'    => $thu,
            ':start'  => $start,
            ':end'    => $end
        ]);
        if ((int)$chk->fetchColumn() > 0) return false;

        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($data['namHoc'], $hocKyDb);
        $totalWeeks = $this->getTongSoTuanHocKy($data['namHoc'], $hocKyDb);

        $ins = $this->conn->prepare("
            INSERT INTO thoikhoabieu
            (maThoiKhoaBieu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, maPhong, maGV, soTiet, hocKy, ghiChu, tuanHoc)
            VALUES
            (:maTKB, :tiet, :loaiTiet, :ngayHoc, :ngayKetThuc, :maLop, :maMonHoc, :maPhong, :maGV, :soTiet, :hocKy, :ghiChu, :tuanHoc)
        ");

        $this->conn->beginTransaction();
        try {
            for ($w = 1; $w <= $totalWeeks; $w++) {
                $ngayHoc = $this->getNgayTheoThuTrongTuan($data['namHoc'], $hocKyDb, $w, $thu);
                $maTKB = sprintf(
                    'TKB_%s_%s_W%d_T%d',
                    $data['maLop'],
                    str_replace('-', '', $ngayHoc),
                    $w,
                    $tiet
                );

                $ins->execute([
                    ':maTKB'       => $maTKB,
                    ':tiet'        => $tiet,
                    ':loaiTiet'    => $data['loaiTiet'] ?? 'Chinh_khoa',
                    ':ngayHoc'     => $ngayHoc,
                    ':ngayKetThuc' => $ngayKetThuc,
                    ':maLop'       => $data['maLop'],
                    ':maMonHoc'    => $data['maMonHoc'] ?? null,
                    ':maPhong'     => $data['maPhong'] ?? null,
                    ':maGV'        => $data['maGV'] ?? null,
                    ':soTiet'      => 1,
                    ':hocKy'       => $hocKyDb,
                    ':ghiChu'      => null,
                    ':tuanHoc'     => $w
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('addManualSlotToAllWeeks: ' . $e->getMessage());
            return false;
        }
    }
}

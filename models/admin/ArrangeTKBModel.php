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

    /* ================== CƠ BẢN ================== */

    public function getNamHocHienTai(): string
    {
        $sql = "SELECT namHoc FROM LopHoc ORDER BY namHoc DESC LIMIT 1";
        $stmt = $this->conn->query($sql);
        $v = $stmt ? $stmt->fetchColumn() : '';
        return is_string($v) ? $v : '';
    }

    public function getDanhSachNamHoc(): array
    {
        $sql = "SELECT DISTINCT namHoc FROM LopHoc ORDER BY namHoc DESC";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    public function getDanhSachHocKy(): array
    {
        return ['HK1', 'HK2'];
    }

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

    public function getDanhSachPhongHoc(): array
    {
        $sql = "SELECT maPhong, tenPhong 
                FROM phonghoc
                WHERE trangThai = 'SU_DUNG'
                ORDER BY maPhong";
        $stmt = $this->conn->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

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

    /* ================== HỌC KỲ / TUẦN ================== */

    public function getKhoangThoiGianHocKy(string $namHoc, string $hocKy): array
    {
        [$y1, $y2] = explode('-', $namHoc);
        $y1 = (int)$y1;
        $y2 = (int)$y2;

        if ($hocKy === 'HK1') {
            $start = sprintf('%04d-09-01', $y1);
            $end   = sprintf('%04d-01-15', $y2);
        } else {
            $start = sprintf('%04d-01-16', $y2);
            $end   = sprintf('%04d-05-31', $y2);
        }

        $startTs = strtotime($start);
        $dow = (int)date('N', $startTs);
        if ($dow > 1) {
            $startTs = strtotime('next monday', $startTs);
        }
        $start = date('Y-m-d', $startTs);

        return [$start, $end];
    }

    public function getTongSoTuanHocKy(string $namHoc, string $hocKy): int
    {
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $s = strtotime($start);
        $e = strtotime($end);
        if ($e < $s) return 0;
        $days = (int)floor(($e - $s) / 86400) + 1;
        return (int)ceil($days / 7);
    }

    public function getThongTinHocKy(string $namHoc, string $hocKy): array
    {
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $totalWeeks = $this->getTongSoTuanHocKy($namHoc, $hocKy);
        return [
            'start' => $start,
            'end' => $end,
            'week_start' => 1,
            'week_end' => max(1, $totalWeeks)
        ];
    }

    public function getNgayTungThuTrongTuanTheoTuan(string $namHoc, string $hocKy, int $tuan): array
    {
        [$start, ] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $baseMondayTs = strtotime($start);

        $tuan = max(1, $tuan);
        $mondayTs = strtotime('+' . (($tuan - 1) * 7) . ' day', $baseMondayTs);

        $result = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $offset = $thu - 2;
            $result[$thu] = date('Y-m-d', strtotime("+$offset day", $mondayTs));
        }
        return $result;
    }

    public function getNgayTungThuTrongTuan(string $namHoc, string $hocKy): array
    {
        return $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
    }

    public function getNgayHocTheoThuTemplate(string $namHoc, string $hocKy, int $thu): ?string
    {
        $ngayThu = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        return $ngayThu[$thu] ?? null;
    }

    public function getDanhSachTuanHocKy(string $namHoc, string $hocKy): array
    {
        $total = $this->getTongSoTuanHocKy($namHoc, $hocKy);
        [$start, $end] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $weeks = [];
        $startTs = strtotime($start);
        for ($w = 1; $w <= max(1, $total); $w++) {
            $ws = strtotime('+' . (($w - 1) * 7) . ' day', $startTs);
            $we = strtotime('+4 day', $ws);
            $weeks[] = [
                'tuan' => $w,
                'start' => date('Y-m-d', $ws),
                'end'   => date('Y-m-d', min($we, strtotime($end))),
                'month' => date('Y-m', $ws)
            ];
        }
        return $weeks;
    }

    public function getDanhSachThangHocKy(string $namHoc, string $hocKy): array
    {
        $weeks = $this->getDanhSachTuanHocKy($namHoc, $hocKy);
        $months = [];
        foreach ($weeks as $w) $months[$w['month']] = true;
        return array_keys($months);
    }

    public function getTuanDauTienTrongThang(string $namHoc, string $hocKy, string $ym): int
    {
        $weeks = $this->getDanhSachTuanHocKy($namHoc, $hocKy);
        foreach ($weeks as $w) {
            if (($w['month'] ?? '') === $ym) return (int)$w['tuan'];
        }
        return 1;
    }

    /* ================== MÔN CỦA LỚP (SẮP XẾP THEO TỔNG TIẾT HK + TIẾT/TUẦN) ================== */

    public function getMonCuaLop(string $maLop, string $namHoc, string $hocKy): array
    {
        $soTuan = max(1, $this->getTongSoTuanHocKy($namHoc, $hocKy));

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
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':maLop'  => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy'  => $hocKy
        ]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['soTietHocKy'] = (int)$r['soTietTuan'] * $soTuan;
        }
        unset($r);

        usort($rows, static function ($a, $b) {
            $x = (int)($b['soTietHocKy'] ?? 0) <=> (int)($a['soTietHocKy'] ?? 0);
            if ($x !== 0) return $x;
            return (int)($b['soTietTuan'] ?? 0) <=> (int)($a['soTietTuan'] ?? 0);
        });

        return $rows;
    }

    /* ================== THỰC HÀNH (TÙY CỘT DB) ================== */

    private function tableHasColumn(string $table, string $column): bool
    {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
        $stmt->execute([':col' => $column]);
        return (bool)$stmt->fetch();
    }

    public function monHocCoThucHanh(string $maMonHoc): bool
    {
        if ($this->tableHasColumn('monhoc', 'coThucHanh')) {
            $stmt = $this->conn->prepare("SELECT coThucHanh FROM monhoc WHERE maMonHoc = :m LIMIT 1");
            $stmt->execute([':m' => $maMonHoc]);
            return (int)($stmt->fetchColumn() ?? 0) === 1;
        }
        if ($this->tableHasColumn('monhoc', 'soTietThucHanh')) {
            $stmt = $this->conn->prepare("SELECT soTietThucHanh FROM monhoc WHERE maMonHoc = :m LIMIT 1");
            $stmt->execute([':m' => $maMonHoc]);
            return (int)($stmt->fetchColumn() ?? 0) > 0;
        }
        return false;
    }

    /* ================== ✅ GV CỦA LỚP+MÔN ================== */

    public function getGiaoVienDayMonCuaLop(string $maLop, string $maMonHoc, string $namHoc, string $hocKy): ?array
    {
        $sql = "
            SELECT pc.maGV, gv.hoTen AS tenGV
            FROM phanconggiangday pc
            JOIN giaovienbomon gv ON gv.maGV = pc.maGV
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
            ':hocKy'    => $hocKy
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /* ================== SLOT TRỐNG ================== */

    private function buildInParams(array $values, string $prefix = ':p'): array
    {
        $params = [];
        $placeholders = [];
        foreach (array_values($values) as $i => $v) {
            $k = $prefix . $i;
            $placeholders[] = $k;
            $params[$k] = $v;
        }
        return [$placeholders, $params];
    }

    public function getSlotTrongCuaLop(string $maLop, string $namHoc, string $hocKy): array
    {
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        $ngayThuTemplate = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        $dates = array_values($ngayThuTemplate);

        [$ph, $inParams] = $this->buildInParams($dates, ':d');

        $sql = "
            SELECT ngayHoc, tiet
            FROM thoikhoabieu
            WHERE maLop = :maLop
              AND ngayHoc IN (" . implode(',', $ph) . ")
              AND ngayKetThuc = :ngayKetThuc
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge($inParams, [
            ':maLop' => $maLop,
            ':ngayKetThuc' => $ngayKetThuc
        ]));
        $exists = $stmt->fetchAll();

        $occupied = [];
        foreach ($exists as $r) {
            $occupied[$r['ngayHoc']][(int)$r['tiet']] = true;
        }

        // slot cố định
        $occupied[$ngayThuTemplate[2]][1] = true; // chào cờ
        $occupied[$ngayThuTemplate[6]][4] = true; // sinh hoạt

        $empty = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $date = $ngayThuTemplate[$thu];
            for ($tiet = 1; $tiet <= 8; $tiet++) { // 8 tiết/ngày
                if (!empty($occupied[$date][$tiet])) continue;
                $empty[] = ['thu' => $thu, 'tiet' => $tiet, 'ngayHoc' => $date];
            }
        }
        return $empty;
    }

    /* ================== ✅ TRÁNH TRÙNG GV/PHÒNG KHI AUTO ================== */

    private function buildBusyMapsFromDb(string $namHoc, string $hocKy): array
    {
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $ngayThu = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        $dates = array_values($ngayThu);

        [$ph, $inParams] = $this->buildInParams($dates, ':d');

        $sql = "
            SELECT tkb.ngayHoc, tkb.tiet, tkb.maPhong, pc.maGV
            FROM thoikhoabieu tkb
            LEFT JOIN phanconggiangday pc
                ON pc.maLop = tkb.maLop
               AND pc.maMonHoc = tkb.maMonHoc
               AND pc.namHoc = :namHoc
               AND pc.hocKy  = :hocKy
            WHERE tkb.ngayHoc IN (" . implode(',', $ph) . ")
              AND tkb.ngayKetThuc = :ngayKetThuc
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge($inParams, [
            ':namHoc' => $namHoc,
            ':hocKy' => $hocKy,
            ':ngayKetThuc' => $ngayKetThuc
        ]));
        $rows = $stmt->fetchAll();

        $busyGV = [];
        $busyPhong = [];
        foreach ($rows as $r) {
            $date = (string)$r['ngayHoc'];
            $tiet = (int)$r['tiet'];
            if (!empty($r['maGV'])) $busyGV[$date][$tiet][(string)$r['maGV']] = true;
            if (!empty($r['maPhong'])) $busyPhong[$date][$tiet][(string)$r['maPhong']] = true;
        }
        return [$busyGV, $busyPhong];
    }

    private function markBusy(array &$busyGV, array &$busyPhong, array $row): void
    {
        $date = (string)$row['ngayHoc'];
        $tiet = (int)$row['tiet'];
        if (!empty($row['maGV'])) $busyGV[$date][$tiet][(string)$row['maGV']] = true;
        if (!empty($row['maPhong'])) $busyPhong[$date][$tiet][(string)$row['maPhong']] = true;
    }

    private function isGVFree(array $busyGV, string $date, int $tiet, ?string $maGV): bool
    {
        if ($maGV === null || $maGV === '') return true;
        return empty($busyGV[$date][$tiet][$maGV]);
    }

    private function isPhongFree(array $busyPhong, string $date, int $tiet, ?string $maPhong): bool
    {
        if ($maPhong === null || $maPhong === '') return true;
        return empty($busyPhong[$date][$tiet][$maPhong]);
    }

    private function timSlotTrongKhongTrung(
        array $grid,
        string $maMonHoc,
        array $ngayThu,
        array $busyGV,
        array $busyPhong,
        ?string $maGV,
        ?string $maPhong
    ): ?array {
        // hạn chế 1 môn không >2 tiết/ngày
        $countPerDay = [];
        for ($thu = 2; $thu <= 6; $thu++) {
            $countPerDay[$thu] = 0;
            for ($t = 1; $t <= 8; $t++) {
                $cell = $grid[$thu][$t] ?? null;
                if ($cell && ($cell['maMonHoc'] ?? null) === $maMonHoc) $countPerDay[$thu]++;
            }
        }

        for ($thu = 2; $thu <= 6; $thu++) {
            for ($tiet = 1; $tiet <= 8; $tiet++) {
                if ($grid[$thu][$tiet] !== null) continue;
                if ($countPerDay[$thu] >= 2) continue;

                $date = $ngayThu[$thu];

                // ✅ né trùng GV và phòng
                if (!$this->isGVFree($busyGV, $date, $tiet, $maGV)) continue;
                if (!$this->isPhongFree($busyPhong, $date, $tiet, $maPhong)) continue;

                return [$thu, $tiet];
            }
        }
        return null;
    }

    /* ================== AUTO (PREVIEW) ================== */

    public function generateAutoTKBForAll(string $namHoc, string $hocKy): array
    {
        $result = [];
        $lopList = $this->getDanhSachLopTheoNamHoc($namHoc);

        $ngayThu = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);

        // ✅ bận từ DB
        [$busyGV, $busyPhong] = $this->buildBusyMapsFromDb($namHoc, $hocKy);

        foreach ($lopList as $lop) {
            $maLop = $lop['maLop'];
            $monList = $this->getMonCuaLop($maLop, $namHoc, $hocKy);

            if (empty($monList)) {
                $result[$maLop] = [];
                continue;
            }

            $maPhong = $this->getPhongCuaLop($maLop) ?? null;

            // grid 8 tiết
            $grid = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 8; $tiet++) $grid[$thu][$tiet] = null;
            }

            // Chào cờ
            $grid[2][1] = [
                'thu' => 2, 'tiet' => 1, 'loaiTiet' => 'Chao_co',
                'ngayHoc' => $ngayThu[2], 'ngayKetThuc' => $ngayKetThuc,
                'maLop' => $maLop, 'maMonHoc' => null, 'tenMon' => 'Chào cờ',
                'maGV' => null, 'tenGV' => null, 'maPhong' => $maPhong
            ];

            // Sinh hoạt
            $grid[6][4] = [
                'thu' => 6, 'tiet' => 4, 'loaiTiet' => 'Sinh_hoat',
                'ngayHoc' => $ngayThu[6], 'ngayKetThuc' => $ngayKetThuc,
                'maLop' => $maLop, 'maMonHoc' => null, 'tenMon' => 'Sinh hoạt lớp',
                'maGV' => null, 'tenGV' => null, 'maPhong' => $maPhong
            ];

            foreach ($monList as $mon) {
                $soTiet = (int)$mon['soTietTuan'];
                $maMon = (string)$mon['maMonHoc'];
                $maGV  = (string)$mon['maGV'];

                for ($i = 0; $i < $soTiet; $i++) {
                    // ✅ tìm slot không trùng GV/phòng
                    $pos = $this->timSlotTrongKhongTrung($grid, $maMon, $ngayThu, $busyGV, $busyPhong, $maGV, $maPhong);
                    if (!$pos) {
                        // hết slot hợp lệ -> dừng môn này (không cố nhét gây trùng)
                        break;
                    }

                    [$thu, $tiet] = $pos;

                    $row = [
                        'thu' => $thu,
                        'tiet' => $tiet,
                        'loaiTiet' => 'Chinh_khoa',
                        'ngayHoc' => $ngayThu[$thu],
                        'ngayKetThuc' => $ngayKetThuc,
                        'maLop' => $maLop,
                        'maMonHoc' => $maMon,
                        'tenMon' => (string)$mon['tenMon'],
                        'maGV' => $maGV,
                        'tenGV' => (string)$mon['tenGV'],
                        'maPhong' => $maPhong
                    ];

                    $grid[$thu][$tiet] = $row;
                    $this->markBusy($busyGV, $busyPhong, $row); // ✅ mark bận để lớp sau né
                }
            }

            $rows = [];
            for ($thu = 2; $thu <= 6; $thu++) {
                for ($tiet = 1; $tiet <= 8; $tiet++) {
                    if ($grid[$thu][$tiet] !== null) $rows[] = $grid[$thu][$tiet];
                }
            }

            $result[$maLop] = $rows;
        }

        return $result;
    }

    /* ================== LẤY TKB DB (TUẦN MẪU) ================== */

    public function getTKBFromDb(string $maLop, string $namHoc, string $hocKy): array
    {
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $ngayThu = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        $dates = array_values($ngayThu);

        [$ph, $inParams] = $this->buildInParams($dates, ':d');

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
              AND tkb.ngayHoc IN (" . implode(',', $ph) . ")
              AND tkb.ngayKetThuc = :ngayKetThuc
            ORDER BY tkb.ngayHoc, tkb.tiet
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge($inParams, [
            ':maLop' => $maLop,
            ':namHoc' => $namHoc,
            ':hocKy' => $hocKy,
            ':ngayKetThuc' => $ngayKetThuc
        ]));
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $dow = (int)date('N', strtotime($row['ngayHoc'])); // Mon=1..Sun=7
            $row['thu'] = $dow + 1; // Mon->Thứ 2
            $result[] = $row;
        }
        return $result;
    }

    /* ================== ✅ FIND CONFLICTS: nếu thiếu maGV thì tự suy ra từ phanconggiangday ================== */

    public function findConflicts(array $plan, string $namHoc, string $hocKy, string $maLop): array
    {
        if (empty($plan)) return [];

        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $errors = [];

        foreach ($plan as $row) {
            $ngayHoc = $row['ngayHoc'] ?? '';
            $tiet    = (int)($row['tiet'] ?? 0);
            if ($ngayHoc === '' || $tiet <= 0) continue;

            $maPhong = $row['maPhong'] ?? '';

            // ✅ nếu maGV rỗng nhưng có maMonHoc => suy ra maGV từ phân công giảng dạy
            $maGV = $row['maGV'] ?? '';
            if (($maGV === '' || $maGV === null) && !empty($row['maMonHoc'])) {
                $gv = $this->getGiaoVienDayMonCuaLop($maLop, (string)$row['maMonHoc'], $namHoc, $hocKy);
                $maGV = $gv['maGV'] ?? '';
            }

            if ($maGV === '' && $maPhong === '') continue;

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
                        (:maGV <> '' AND pc.maGV = :maGV)
                     OR (:maPhong <> '' AND tkb.maPhong = :maPhong)
                  )
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':namHoc' => $namHoc,
                ':hocKy' => $hocKy,
                ':ngayHoc' => $ngayHoc,
                ':tiet' => $tiet,
                ':ngayKetThuc' => $ngayKetThuc,
                ':maLop' => $maLop,
                ':maGV' => (string)$maGV,
                ':maPhong' => (string)$maPhong,
            ]);

            $conflicts = $stmt->fetchAll();
            foreach ($conflicts as $c) {
                $thuVN = (int)date('N', strtotime($ngayHoc)) + 1;
                $errors[] = [
                    'slot'  => sprintf('Thứ %d - Tiết %d', $thuVN, $tiet),
                    'lop'   => $c['maLop'],
                    'phong' => $c['maPhong'],
                    'gv'    => $c['tenGV'],
                    'maGV'  => $c['maGV']
                ];
            }
        }

        return $errors;
    }

    /* ================== SAVE / ADD MANUAL ================== */

    public function saveTKBForClass(string $maLop, string $namHoc, string $hocKy, array $plan): void
    {
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($namHoc, $hocKy);
        $ngayThu = $this->getNgayTungThuTrongTuanTheoTuan($namHoc, $hocKy, 1);
        $dates = array_values($ngayThu);

        [$ph, $inParams] = $this->buildInParams($dates, ':d');

        $delSql = "
            DELETE FROM thoikhoabieu
            WHERE maLop = :maLop
              AND ngayHoc IN (" . implode(',', $ph) . ")
              AND ngayKetThuc = :ngayKetThuc
        ";
        $del = $this->conn->prepare($delSql);
        $del->execute(array_merge($inParams, [
            ':maLop' => $maLop,
            ':ngayKetThuc' => $ngayKetThuc
        ]));

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
                str_replace('-', '', (string)$row['ngayHoc']),
                (int)$row['tiet']
            );

            $ins->execute([
                ':maTKB' => $maTKB,
                ':tiet' => (int)$row['tiet'],
                ':loaiTiet' => (string)$row['loaiTiet'],
                ':ngayHoc' => (string)$row['ngayHoc'],
                ':ngayKetThuc' => (string)$row['ngayKetThuc'],
                ':maLop' => $maLop,
                ':maMonHoc' => $row['maMonHoc'],
                ':maPhong' => $row['maPhong']
            ]);
        }
    }

    public function addManualSlot(array $data): bool
    {
        [, $ngayKetThuc] = $this->getKhoangThoiGianHocKy($data['namHoc'], $data['hocKy']);

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
            ':maLop' => $data['maLop'],
            ':ngayHoc' => $data['ngayHoc'],
            ':ngayKetThuc' => $ngayKetThuc,
            ':tiet' => (int)$data['tiet']
        ]);
        if ((int)$chk->fetchColumn() > 0) return false;

        $maTKB = sprintf(
            'TKB_%s_%s_%d',
            $data['maLop'],
            str_replace('-', '', (string)$data['ngayHoc']),
            (int)$data['tiet']
        );

        $sql = "
            INSERT INTO thoikhoabieu
            (maThoiKhoaBieu, tiet, loaiTiet, ngayHoc, ngayKetThuc, maLop, maMonHoc, maPhong)
            VALUES (:maTKB, :tiet, :loaiTiet, :ngayHoc, :ngayKetThuc, :maLop, :maMonHoc, :maPhong)
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':maTKB' => $maTKB,
            ':tiet' => (int)$data['tiet'],
            ':loaiTiet' => (string)($data['loaiTiet'] ?? 'Ly_thuyet'),
            ':ngayHoc' => (string)$data['ngayHoc'],
            ':ngayKetThuc' => (string)$ngayKetThuc,
            ':maLop' => (string)$data['maLop'],
            ':maMonHoc' => (string)$data['maMonHoc'],
            ':maPhong' => (string)($data['maPhong'] ?? '')
        ]);
    }
}

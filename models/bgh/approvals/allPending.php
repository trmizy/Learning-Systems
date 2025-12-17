<?php
// modules/bgh/approvals/allPending.php
require_once __DIR__ . '/../../../middlewares/AuthGuard.php';
require_once __DIR__ . '/../../../config/database.php';
require_role(['bgh']);

require_once __DIR__ . '/../../../models/bgh/chonToHopMonModel.php';

$pageTitle = 'Tất cả yêu cầu chờ duyệt';
require_once __DIR__ . '/../../../views/layouts/header.php';

$db = Database::getInstance()->getConnection();
$items = [];

// 1) Tổ hợp môn: lấy từng mục PENDING (chi tiết)
try {
    $chonModel = new chonToHopMonModel();
    $pendingToHop = $chonModel->getDanhSachToHopMon('PENDING');
    foreach ($pendingToHop as $t) {
        $items[] = [
            'type' => 'tohopmon',
            'title' => 'Tổ hợp môn: ' . ($t['tenToHop'] ?? $t['maToHop']),
            'submitter' => $t['nguoiTao'] ?? 'Phòng Giáo Vụ',
            'date' => $t['ngayTao'] ?? null,
            'link' => '/modules/chonToHopMon/quanLyChonDetail.php?maToHop=' . urlencode($t['maToHop'] ?? ''),
            'meta' => $t
        ];
    }
} catch (Exception $e) {
    // nếu lỗi model, bỏ qua
}

// 2) Các loại yêu cầu khác: lấy tổng số đang PENDING và tạo liên kết tới module tương ứng
$otherModules = [
    'tohopmon' => [
        'table' => 'tohopmon',
        'label' => 'Tổ hợp môn',
        'link' => '/modules/chonToHopMon/quanLyChonList.php'
    ],
    'score_edit' => [
        'table' => 'phieussuadiem',
        'label' => 'Sửa điểm',
        'link' => '/modules/bgh/score-edits/pending.php'
    ],
    'conduct' => [
        'table' => 'hanhkiem',
        'label' => 'Hạnh kiểm',
        'link' => '/modules/bgh/conduct/pending.php'
    ],
    'exam' => [
        'table' => 'dethi',
        'label' => 'Đề thi',
        'link' => '/modules/bgh/exams/pending.php'
    ],
    'assignment' => [
        'table' => 'phanconggiangday',
        'label' => 'Phân công',
        'link' => '/modules/bgh/assignments/pending.php'
    ]
];

foreach ($otherModules as $key => $mod) {
    try {
        $sql = "SELECT COUNT(*) FROM `" . $mod['table'] . "` WHERE `trangThai` = 'PENDING'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $count = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $count = 0; // nếu bảng không tồn tại hoặc lỗi, coi là 0
    }
    $items[] = [
        'type' => $key,
        'title' => $mod['label'] . " (" . $count . " chờ duyệt)",
        'submitter' => '',
        'date' => null,
        'link' => $mod['link'],
        'count' => $count
    ];
}

// Sắp xếp: các mục có date (mới nhất) lên trên, sau đó theo type
usort($items, function($a, $b) {
    $da = isset($a['date']) && $a['date'] ? strtotime($a['date']) : 0;
    $dbt = isset($b['date']) && $b['date'] ? strtotime($b['date']) : 0;
    if ($da === $dbt) return strcmp($a['type'], $b['type']);
    return $dbt - $da;
});

?>
<div class="container py-4">
    <h3 class="mb-3"><i class="fa-solid fa-list me-2"></i><?php echo htmlspecialchars($pageTitle); ?></h3>

    <div class="list-group">
        <?php foreach ($items as $it): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($it['title']); ?></div>
                    <?php if (!empty($it['submitter'])): ?><div class="small text-muted">Người gửi: <?= htmlspecialchars($it['submitter']); ?></div><?php endif; ?>
                    <?php if (!empty($it['date'])): ?><div class="small text-muted">Ngày: <?= htmlspecialchars($it['date']); ?></div><?php endif; ?>
                </div>
                <div class="text-end">
                    <?php if (!empty($it['link'])): ?>
                        <a href="<?= $it['link']; ?>" class="btn btn-sm btn-primary">Mở</a>
                    <?php else: ?>
                        <span class="badge bg-secondary">Không có liên kết</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4">
        <a href="/views/bgh/dashboard.php" class="btn btn-outline-secondary">Quay lại Dashboard</a>
    </div>
</div>

<?php
require_once __DIR__ . '/../../../views/layouts/footer.php';
// end file
?>

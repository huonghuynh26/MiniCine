<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$showId = (int)($_GET['show_id'] ?? 0);
if (!$showId) { echo json_encode(['active' => false]); exit; }

// Kiểm tra Flash Sale có tồn tại không
$db   = db();
$stmt = $db->prepare("
    SELECT fs.*, s.start_time, s.end_time
    FROM tblFlashSales fs
    JOIN tblShows s ON s.id = fs.show_id
    WHERE fs.show_id = ? AND fs.is_active = 1
");
$stmt->bind_param('i', $showId);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) {
    echo json_encode(['active' => false, 'reason' => 'Chưa có Flash Sale cho suất này']);
    exit;
}

$now       = time();
$startTime = strtotime($sale['start_time']);
$pre2h     = $startTime - 2 * 3600;

$sale_result = getFlashSale($showId);

echo json_encode([
    'active'       => $sale_result !== null,
    'discount_pct' => $sale_result['discount_pct'] ?? 0,
    'reason'       => $sale_result['reason'] ?? '',
    'trigger_type' => $sale['trigger_type'],
    'now'          => date('H:i:s'),
    'start_time'   => date('H:i:s', $startTime),
    'pre2h_from'   => date('H:i:s', $pre2h),
    'in_window'    => ($now >= $pre2h && $now < $startTime),
    'avail_pct'    => ($sale['trigger_type'] === 'pre2h') ? getAvailableSeatPct($showId) : -1,
]);
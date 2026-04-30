<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
startSession();

$user = currentUser();
if (!$user) { echo json_encode(['ok' => false]); exit; }

$body    = json_decode(file_get_contents('php://input'), true);
$showId  = (int)($body['show_id'] ?? 0);
$seatIds = array_map('intval', $body['seat_ids'] ?? []);

if (!$showId || empty($seatIds)) {
    echo json_encode(['ok' => false]);
    exit;
}

$db = db();
$db->begin_transaction();
try {
    $in = implode(',', array_fill(0, count($seatIds), '?'));
    $types  = 'ii' . str_repeat('i', count($seatIds));
    $params = array_merge([$showId, $user['id']], $seatIds);

    $stmt = $db->prepare("
        UPDATE tblSeatStatus
        SET status='available', held_by=NULL, held_until=NULL,
            version_number = version_number + 1
        WHERE show_id=? AND held_by=? AND seat_id IN ($in)
          AND status='held'
    ");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $db->commit();
    echo json_encode(['ok' => true, 'released' => $stmt->affected_rows]);
} catch (Throwable $e) {
    $db->rollback();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
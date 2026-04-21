<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$user = currentUser();
if (!$user) { echo json_encode(['ok' => false, 'msg' => 'Vui lòng đăng nhập.']); exit; }

$body    = json_decode(file_get_contents('php://input'), true);
$showId  = (int)($body['show_id'] ?? 0);
$seatIds = array_map('intval', $body['seat_ids'] ?? []);

if (!$showId || empty($seatIds)) {
    echo json_encode(['ok' => false, 'msg' => 'Thiếu dữ liệu.']);
    exit;
}

echo json_encode(holdSeats($showId, $seatIds, $user['id']));

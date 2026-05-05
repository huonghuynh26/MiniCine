<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$user = currentUser();
if (!$user) {
    echo json_encode(['ok' => false, 'msg' => 'Vui lòng đăng nhập.']);
    exit;
}

// ── Kiểm tra tài khoản còn active không (realtime từ DB) ──────────
$db   = db();
$stmt = $db->prepare("SELECT email_verified FROM tblUsers WHERE id=?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row || !$row['email_verified']) {
    // Huỷ session luôn
    session_destroy();
    echo json_encode([
        'ok'      => false,
        'msg'     => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.',
        'locked'  => true,
    ]);
    exit;
}

$body    = json_decode(file_get_contents('php://input'), true);
$showId  = (int)($body['show_id'] ?? 0);
$seatIds = array_map('intval', $body['seat_ids'] ?? []);

if (!$showId || empty($seatIds)) {
    echo json_encode(['ok' => false, 'msg' => 'Thiếu dữ liệu.']);
    exit;
}

echo json_encode(holdSeats($showId, $seatIds, $user['id']));
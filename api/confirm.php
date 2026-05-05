<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';
require_once __DIR__ . '/../includes/mail.php';

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
    session_destroy();
    echo json_encode([
        'ok'     => false,
        'msg'    => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.',
        'locked' => true,
    ]);
    exit;
}

$body       = json_decode(file_get_contents('php://input'), true);
$showId     = (int)($body['show_id']    ?? 0);
$seatIds    = array_map('intval', $body['seat_ids']   ?? []);
$pointsUsed = (int)($body['points_used'] ?? 0);

if (!$showId || empty($seatIds)) {
    echo json_encode(['ok' => false, 'msg' => 'Thiếu dữ liệu.']);
    exit;
}

// Validate điểm
$userPoints     = (int)$db->query("SELECT total_points FROM tblUsers WHERE id={$user['id']}")->fetch_assoc()['total_points'];
if ($pointsUsed > $userPoints) $pointsUsed = $userPoints;
$pointsUsed     = (int)(floor($pointsUsed / 100) * 100);
$pointsDiscount = (int)(floor($pointsUsed / 100) * 10000);

$result = confirmBooking($showId, $seatIds, $user['id'], $pointsUsed, $pointsDiscount);
if (!$result['ok']) {
    echo json_encode($result);
    exit;
}

// Build booking data for email
$stmt = $db->prepare("
    SELECT s.*, m.title as movie_title, r.name as room_name
    FROM tblShows s
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms  r ON r.id = s.room_id
    WHERE s.id=?
");
$stmt->bind_param('i', $showId);
$stmt->execute();
$show = $stmt->get_result()->fetch_assoc();

$emailItems = [];
foreach ($result['items'] as $item) {
    $stmt = $db->prepare("SELECT row, number, type FROM tblSeats WHERE id=?");
    $stmt->bind_param('i', $item['seat_id']);
    $stmt->execute();
    $s     = $stmt->get_result()->fetch_assoc();
    $label = $s['type'] === 'couple'
        ? "{$s['row']}" . ($s['number']*2-1) . "-{$s['row']}{$s['number']}c"
        : "{$s['row']}{$s['number']}";
    $emailItems[] = array_merge($item, ['seat_label' => $label]);
}

$qrText   = $result['qr_code'];
$qrDataUri = generateTextQR($qrText);

$bookingData = [
    'id'          => $result['booking_id'],
    'movie_title' => $show['movie_title'],
    'show_time'   => date('H:i d/m/Y', strtotime($show['start_time'])),
    'room_name'   => $show['room_name'],
    'total_price' => $result['total'],
    'items'       => $emailItems,
];

sendBookingConfirmEmail($user['email'], $user['name'], $bookingData, $qrDataUri);

// Update session points
$_SESSION['user']['points'] = (int)$db->query(
    "SELECT total_points FROM tblUsers WHERE id={$user['id']}"
)->fetch_assoc()['total_points'];

echo json_encode($result);

function generateTextQR(string $text): string {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180">'
         . '<rect width="180" height="180" fill="#fff"/>'
         . '<rect x="10" y="10" width="160" height="160" fill="none" stroke="#000" stroke-width="2"/>'
         . '<text x="90" y="80" text-anchor="middle" font-family="monospace" font-size="11" fill="#000">QR CODE</text>'
         . '<text x="90" y="100" text-anchor="middle" font-family="monospace" font-size="9" fill="#000">' . htmlspecialchars($text) . '</text>'
         . '<text x="90" y="120" text-anchor="middle" font-family="monospace" font-size="8" fill="#999">Xuất trình tại cửa rạp</text>'
         . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
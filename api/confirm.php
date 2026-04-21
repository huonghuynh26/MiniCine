<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';
require_once __DIR__ . '/../includes/mail.php';

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

$result = confirmBooking($showId, $seatIds, $user['id']);
if (!$result['ok']) {
    echo json_encode($result);
    exit;
}

// Build booking data for email
$db   = db();
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

// Build items with labels
$emailItems = [];
foreach ($result['items'] as $item) {
    $stmt = $db->prepare("SELECT row, number, type FROM tblSeats WHERE id=?");
    $stmt->bind_param('i', $item['seat_id']);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();
    $label = $s['type'] === 'couple'
        ? "{$s['row']}" . ($s['number']*2-1) . "-{$s['row']}{$s['number']}c"
        : "{$s['row']}{$s['number']}";
    $emailItems[] = array_merge($item, ['seat_label' => $label]);
}

// Simple QR as text-based data URI (real project: use QR library)
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
$_SESSION['user']['points'] = $db->query(
    "SELECT total_points FROM tblUsers WHERE id={$user['id']}"
)->fetch_assoc()['total_points'];

echo json_encode($result);

// ─── Minimal QR placeholder (replace with endroid/qr-code or similar) ─────
function generateTextQR(string $text): string {
    // Returns a simple SVG placeholder with the QR code text
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180">'
         . '<rect width="180" height="180" fill="#fff"/>'
         . '<rect x="10" y="10" width="160" height="160" fill="none" stroke="#000" stroke-width="2"/>'
         . '<text x="90" y="80" text-anchor="middle" font-family="monospace" font-size="11" fill="#000">QR CODE</text>'
         . '<text x="90" y="100" text-anchor="middle" font-family="monospace" font-size="9" fill="#000">' . htmlspecialchars($text) . '</text>'
         . '<text x="90" y="120" text-anchor="middle" font-family="monospace" font-size="8" fill="#999">Xuất trình tại cửa rạp</text>'
         . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

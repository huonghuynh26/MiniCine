<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');

$movieId = (int)($_GET['movie_id'] ?? 0);
if (!$movieId) { echo json_encode(['ok'=>false,'shows'=>[]]); exit; }

$db   = db();
$stmt = $db->prepare("
    SELECT s.*, r.name as room_name, r.floor
    FROM tblShows s
    JOIN tblRooms r ON r.id = s.room_id
    WHERE s.movie_id=? AND s.end_time > NOW()
    ORDER BY s.start_time ASC
");
$stmt->bind_param('i', $movieId);
$stmt->execute();
$shows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$result = [];
foreach ($shows as $s) {
    $flash = getFlashSale($s['id']);
    $result[] = [
        'id'         => $s['id'],
        'time'       => date('H:i', strtotime($s['start_time'])),
        'date'       => date('Y-m-d', strtotime($s['start_time'])),
        'date_label' => date('l, d/m/Y', strtotime($s['start_time'])),
        'room'       => $s['room_name'],
        'floor'      => $s['floor'],
        'flash'      => $flash ? $flash['discount_pct'] : 0,
    ];
}

echo json_encode(['ok'=>true,'shows'=>$result]);
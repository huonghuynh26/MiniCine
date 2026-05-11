<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$showId = (int)($_GET['show_id'] ?? 0);
if (!$showId) { echo json_encode(['error' => 'Missing show_id']); exit; }

// Nếu có param ?init=1 → đây là lần load đầu tiên khi vào trang
// → release ghế held cũ của user này (phòng trường hợp họ thoát ra vào lại)
$isInit = isset($_GET['init']) && $_GET['init'] === '1';

$seats = getSeatsLayout($showId, $isInit);
echo json_encode($seats);
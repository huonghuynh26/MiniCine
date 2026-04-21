<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$showId = (int)($_GET['show_id'] ?? 0);
$n      = min(8, max(1, (int)($_GET['n'] ?? 2)));

if (!$showId) { echo json_encode(['ok' => false, 'msg' => 'Missing show_id']); exit; }

echo json_encode(suggestSeats($showId, $n));

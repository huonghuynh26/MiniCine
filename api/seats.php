<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$showId = (int)($_GET['show_id'] ?? 0);
if (!$showId) { echo json_encode(['error' => 'Missing show_id']); exit; }

$seats = getSeatsLayout($showId);
echo json_encode($seats);

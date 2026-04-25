<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json');
startSession();

$showId = (int)($_GET['show_id'] ?? 0);
if (!$showId) { echo json_encode(['active' => false]); exit; }

$sale = getFlashSale($showId);
echo json_encode([
    'active'       => $sale !== null,
    'discount_pct' => $sale['discount_pct'] ?? 0,
    'reason'       => $sale['reason'] ?? '',
]);
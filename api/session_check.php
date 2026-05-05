<?php
/**
 * API: Kiểm tra session + trạng thái tài khoản realtime
 * Booking page gọi mỗi 30s để detect tài khoản bị khóa
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
startSession();

$user = currentUser();
if (!$user) {
    echo json_encode(['ok' => false, 'reason' => 'not_logged_in']);
    exit;
}

// Check DB realtime
$db   = db();
$stmt = $db->prepare("SELECT email_verified FROM tblUsers WHERE id=?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || !$row['email_verified']) {
    session_destroy();
    echo json_encode([
        'ok'     => false,
        'reason' => 'locked',
        'msg'    => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.',
    ]);
    exit;
}

echo json_encode(['ok' => true]);
<?php
// ─── Database ────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'minicine');

// ─── App ─────────────────────────────────────────────────────────────────────
// Tự động detect URL — không cần sửa khi đổi tên thư mục
define('APP_NAME', 'MiniCine');

if (!defined('APP_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // __DIR__ = .../htdocs/xxx/source/config  → đi lên 1 cấp = thư mục source
    $docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $selfDir  = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $subPath  = str_replace($docRoot, '', $selfDir);
    define('APP_URL', $protocol . '://' . $host . $subPath);
}

// ─── Gmail SMTP (PHPMailer) ───────────────────────────────────────────────────
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'huonghuynhuwu@gmail.com');   // Điền Gmail
define('MAIL_PASSWORD', 'euzmmzigjmjtdylj');       // App Password 
define('MAIL_FROM',     'huonghuynhuwu@gmail.com');
define('MAIL_FROM_NAME', 'MiniCine');

// ─── Session & Security ───────────────────────────────────────────────────────
define('SEAT_HOLD_MINUTES', 5);
define('POINTS_PER_100', 10000); // 100 điểm = 10.000đ

// ─── Seat points ─────────────────────────────────────────────────────────────
define('POINTS_STANDARD', 10);
define('POINTS_VIP',      35);
define('POINTS_COUPLE',   20);

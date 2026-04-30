<?php
require_once __DIR__ . '/config.php';

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        if ($conn->connect_error) {
            die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
        }
        // Sync MySQL timezone với PHP để tránh lệch giờ khi so sánh held_until
        $offset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime()) / 3600;
        $sign   = $offset >= 0 ? '+' : '-';
        $tz     = sprintf('%s%02d:00', $sign, abs($offset));
        $conn->query("SET time_zone = '{$tz}'");
    }
    return $conn;
}
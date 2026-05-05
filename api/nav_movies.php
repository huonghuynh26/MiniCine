<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = db();
$ageColors = ['P'=>'#27ae60','T13'=>'#2980b9','T16'=>'#e67e22','T18'=>'#e8192c'];
$out = ['showing'=>[], 'upcoming'=>[]];

// Kiểm tra cột age_rating có tồn tại không
$hasAgeRating = false;
$cols = $db->query("SHOW COLUMNS FROM tblMovies LIKE 'age_rating'");
if ($cols && $cols->num_rows > 0) $hasAgeRating = true;

$ageSelect = $hasAgeRating ? "age_rating" : "'P' as age_rating";

foreach (['showing','upcoming'] as $status) {
    $rows = $db->query("
        SELECT id, title, poster_url, $ageSelect
        FROM tblMovies
        WHERE status='$status'
        ORDER BY created_at DESC
        LIMIT 4
    ");
    if (!$rows) continue;
    while ($m = $rows->fetch_assoc()) {
        $ar = $m['age_rating'] ?? 'P';
        $out[$status][] = [
            'id'         => (int)$m['id'],
            'title'      => $m['title'],
            'poster_url' => $m['poster_url'],
            'age_rating' => $ar,
            'age_color'  => $ageColors[$ar] ?? '#27ae60',
        ];
    }
}

echo json_encode($out);
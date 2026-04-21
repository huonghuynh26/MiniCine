<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/booking.php';

startSession();
$id   = (int)($_GET['id'] ?? 0);
$db   = db();

$stmt = $db->prepare("SELECT * FROM tblMovies WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
if (!$movie) { header('Location: ' . APP_URL); exit; }

// Get upcoming shows for this movie
$stmt = $db->prepare("
    SELECT s.*, r.name as room_name, r.floor
    FROM tblShows s
    JOIN tblRooms r ON r.id = s.room_id
    WHERE s.movie_id=? AND s.start_time > NOW()
    ORDER BY s.start_time ASC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$shows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Group by date
$byDate = [];
foreach ($shows as $s) {
    $date = date('Y-m-d', strtotime($s['start_time']));
    $byDate[$date][] = $s;
}

renderHead(htmlspecialchars($movie['title']));
?>
<?php renderNav(); ?>

<div class="section">
  <div style="display:flex;gap:40px;flex-wrap:wrap">
    <!-- Poster -->
    <div style="flex-shrink:0">
      <div style="width:220px;aspect-ratio:2/3;background:#1a1a1a;border-radius:12px;overflow:hidden;
                  display:flex;align-items:center;justify-content:center;font-size:80px">
        <?php if ($movie['poster_url']): ?>
          <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($movie['poster_url']) ?>"
               style="width:100%;height:100%;object-fit:cover" alt="">
        <?php else: ?>🎬<?php endif; ?>
      </div>
    </div>

    <!-- Info -->
    <div style="flex:1;min-width:260px">
      <span class="badge badge-<?= $movie['status'] ?>" style="margin-bottom:12px">
        <?= $movie['status'] === 'showing' ? 'Đang chiếu' : 'Sắp chiếu' ?>
      </span>
      <h1 style="font-size:32px;font-weight:900;margin:8px 0 16px"><?= htmlspecialchars($movie['title']) ?></h1>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
        <span class="text-muted">🎭 <?= htmlspecialchars($movie['genre'] ?? 'N/A') ?></span>
        <span class="text-muted">⏱ <?= $movie['duration_min'] ?> phút</span>
      </div>
      <p style="color:#aaa;line-height:1.7;margin-bottom:24px"><?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?></p>

      <?php if ($movie['trailer_url']): ?>
      <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" target="_blank"
         class="btn btn-outline">▶ Xem trailer</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Showtimes -->
  <div class="mt-4">
    <h2 class="section-title">Chọn suất chiếu</h2>
    <?php if (empty($shows)): ?>
      <p class="text-muted">Chưa có suất chiếu nào.</p>
    <?php else: ?>
      <?php foreach ($byDate as $date => $dayShows): ?>
      <div class="mb-3">
        <div style="font-size:14px;font-weight:600;color:#666;margin-bottom:12px">
          📅 <?= date('l, d/m/Y', strtotime($date)) ?>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <?php foreach ($dayShows as $s):
            $sale = getFlashSale($s['id']);
          ?>
          <a href="<?= APP_URL ?>/booking.php?show_id=<?= $s['id'] ?>"
             class="btn btn-outline" style="flex-direction:column;gap:4px;padding:12px 20px;text-align:center">
            <span style="font-size:16px;font-weight:700"><?= date('H:i', strtotime($s['start_time'])) ?></span>
            <span style="font-size:12px;color:#666"><?= htmlspecialchars($s['room_name']) ?></span>
            <?php if ($sale): ?>
            <span style="font-size:11px;background:#e50914;color:#fff;padding:2px 6px;border-radius:4px">
              ⚡ -<?= $sale['discount_pct'] ?>%
            </span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php renderFooter(); ?>

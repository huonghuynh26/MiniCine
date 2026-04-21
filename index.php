<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/booking.php';

startSession();

// Fetch movies
$db   = db();
$tab  = $_GET['tab'] ?? 'showing';
$tab  = in_array($tab, ['showing','upcoming']) ? $tab : 'showing';
$stmt = $db->prepare("SELECT * FROM tblMovies WHERE status=? ORDER BY created_at DESC");
$stmt->bind_param('s', $tab);
$stmt->execute();
$movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Flash sale banner — check any active show
$flashShows = $db->query("
    SELECT fs.*, m.title, s.start_time, r.name as room_name
    FROM tblFlashSales fs
    JOIN tblShows s  ON s.id = fs.show_id
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms r  ON r.id = s.room_id
    WHERE fs.is_active=1 AND s.start_time > NOW()
    ORDER BY s.start_time ASC LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

renderHead('Trang chủ');
?>
<style>
.tabs { display:flex; gap:8px; margin-bottom:32px; }
.tab-btn {
  padding:10px 24px; border-radius:8px; border:1px solid #333;
  background:transparent; color:#888; font-size:14px; font-weight:600; cursor:pointer;
  font-family:inherit; transition:all .15s;
}
.tab-btn.active { background:#e50914; color:#fff; border-color:#e50914; }
</style>
<?php renderNav(); ?>

<section class="hero">
  <h1>Rạp chiếu phim <span>MiniCine</span></h1>
  <p>3 phòng chiếu – 4 tầng – Ghế VIP & Couple – Đặt vé online 100%</p>
  <?php if (!currentUser()): ?>
  <a href="<?= APP_URL ?>/register.php" class="btn btn-primary btn-lg">Đăng ký ngay</a>
  <?php endif; ?>
</section>

<div class="section">
  <!-- Flash Sale Banner -->
  <?php foreach ($flashShows as $fs):
    $sale = getFlashSale($fs['show_id']);
    if (!$sale) continue;
  ?>
  <div class="flash-sale-banner mb-3">
    <div class="flash-icon">⚡</div>
    <div>
      <h3>Flash Sale – Giảm <?= $sale['discount_pct'] ?>%!</h3>
      <p><?= htmlspecialchars($fs['title']) ?> · <?= $fs['room_name'] ?> ·
         <?= date('H:i d/m', strtotime($fs['start_time'])) ?></p>
    </div>
    <a href="<?= APP_URL ?>/booking.php?show_id=<?= $fs['show_id'] ?>"
       class="btn btn-primary" style="margin-left:auto;white-space:nowrap">
      Đặt ngay
    </a>
  </div>
  <?php endforeach; ?>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab-btn <?= $tab==='showing'?'active':'' ?>"
      onclick="location.href='?tab=showing'">🎬 Đang chiếu</button>
    <button class="tab-btn <?= $tab==='upcoming'?'active':'' ?>"
      onclick="location.href='?tab=upcoming'">🗓 Sắp chiếu</button>
  </div>

  <?php if (empty($movies)): ?>
    <p class="text-muted text-center" style="padding:48px">Chưa có phim nào.</p>
  <?php else: ?>
  <div class="movies-grid">
    <?php foreach ($movies as $m): ?>
    <a href="<?= APP_URL ?>/movie.php?id=<?= $m['id'] ?>" style="text-decoration:none;color:inherit">
    <div class="movie-card">
      <div class="poster">
        <?php if ($m['poster_url']): ?>
          <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($m['poster_url']) ?>"
               alt="<?= htmlspecialchars($m['title']) ?>">
        <?php else: ?>
          🎬
        <?php endif; ?>
      </div>
      <div class="movie-card-body">
        <div class="movie-card-title"><?= htmlspecialchars($m['title']) ?></div>
        <div class="movie-card-meta mt-1">
          <?= htmlspecialchars($m['genre'] ?? '') ?> · <?= $m['duration_min'] ?> phút
        </div>
        <div class="mt-1">
          <span class="badge badge-<?= $m['status'] ?>">
            <?= $m['status'] === 'showing' ? 'Đang chiếu' : 'Sắp chiếu' ?>
          </span>
        </div>
      </div>
    </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php renderFooter(); ?>

<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/booking.php';

startSession();

$db  = db();
$tab = in_array($_GET['tab'] ?? '', ['showing','upcoming']) ? $_GET['tab'] : 'showing';

$stmt = $db->prepare("SELECT * FROM tblMovies WHERE status=? ORDER BY created_at DESC");
$stmt->bind_param('s', $tab);
$stmt->execute();
$movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$flashShows = $db->query("
    SELECT fs.*, m.title, s.start_time, r.name as room_name
    FROM tblFlashSales fs
    JOIN tblShows   s ON s.id  = fs.show_id
    JOIN tblMovies  m ON m.id  = s.movie_id
    JOIN tblRooms   r ON r.id  = s.room_id
    WHERE fs.is_active=1 AND s.end_time > NOW()
    ORDER BY s.start_time ASC LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

$ageColors = ['P'=>'#27ae60','T13'=>'#2980b9','T16'=>'#e67e22','T18'=>'#e8192c'];

renderHead('Trang chủ');
?>
<?php renderNav(); ?>

<!-- Banner Slideshow -->
<div class="banner-slider">
  <div class="banner-track" id="bannerTrack">
    <div class="banner-slide" style="background:linear-gradient(135deg,#1a0000 0%,#6b0000 50%,#e50914 100%)">
      <div class="banner-content">
        <div class="banner-badge">⚡ FLASH SALE</div>
        <h2 class="banner-title">Giảm đến <span>50%</span> giá vé</h2>
        <p class="banner-desc">Đặt vé trước 12h — nhận ưu đãi cực sốc mỗi ngày tại MiniCine</p>
        <a href="<?= APP_URL ?>/booking.php" class="banner-btn">Mua vé ngay →</a>
      </div><div class="banner-deco">🎟️</div>
    </div>
    <div class="banner-slide" style="background:linear-gradient(135deg,#0a0a2e 0%,#1a1a6e 50%,#0066cc 100%)">
      <div class="banner-content">
        <div class="banner-badge" style="background:rgba(0,150,255,.25);color:#66ccff">🎬 ĐANG CHIẾU</div>
        <h2 class="banner-title">Phim Hot <span style="color:#66ccff">Tháng Này</span></h2>
        <p class="banner-desc">Hàng chục bộ phim bom tấn đang chiếu tại MiniCine</p>
        <a href="<?= APP_URL ?>/index.php?tab=showing" class="banner-btn" style="background:#0066cc">Xem phim →</a>
      </div><div class="banner-deco">🎬</div>
    </div>
    <div class="banner-slide" style="background:linear-gradient(135deg,#1a1200 0%,#5a3d00 50%,#f5a623 100%)">
      <div class="banner-content">
        <div class="banner-badge" style="background:rgba(245,166,35,.25);color:#f5c518">🛍️ STAR SHOP</div>
        <h2 class="banner-title">Combo <span style="color:#f5c518">Bắp + Nước</span></h2>
        <p class="banner-desc">Bình Minion limited edition, bắp rang bơ thơm lừng</p>
        <a href="#" class="banner-btn" style="background:#d4890a">Khám phá →</a>
      </div><div class="banner-deco">🍿</div>
    </div>
    <div class="banner-slide" style="background:linear-gradient(135deg,#0a1a0a 0%,#0d4a1a 50%,#1db954 100%)">
      <div class="banner-content">
        <div class="banner-badge" style="background:rgba(29,185,84,.25);color:#4cff8a">⭐ THÀNH VIÊN</div>
        <h2 class="banner-title">Tích điểm — <span style="color:#4cff8a">Nhận quà</span></h2>
        <p class="banner-desc">Đăng ký thành viên MiniCine — mỗi vé mua là điểm thưởng</p>
        <a href="<?= APP_URL ?>/register.php" class="banner-btn" style="background:#1db954">Đăng ký ngay →</a>
      </div><div class="banner-deco">🎁</div>
    </div>
  </div>
  <button class="banner-prev" onclick="bannerMove(-1)">&#8249;</button>
  <button class="banner-next" onclick="bannerMove(1)">&#8250;</button>
  <div class="banner-dots" id="bannerDots">
    <span class="dot active" onclick="bannerGoTo(0)"></span>
    <span class="dot" onclick="bannerGoTo(1)"></span>
    <span class="dot" onclick="bannerGoTo(2)"></span>
    <span class="dot" onclick="bannerGoTo(3)"></span>
  </div>
</div>
<script>
(function(){
  let cur=0,total=4,timer;
  const track=document.getElementById('bannerTrack');
  const dots=document.querySelectorAll('.dot');
  function go(n){cur=(n+total)%total;track.style.transform='translateX(-'+(cur*100)+'%)';dots.forEach((d,i)=>d.classList.toggle('active',i===cur));}
  window.bannerMove=n=>{go(cur+n);reset();};
  window.bannerGoTo=n=>{go(n);reset();};
  function reset(){clearInterval(timer);timer=setInterval(()=>go(cur+1),4500);}
  reset();
})();
</script>

<div class="section">
  <!-- Flash sale -->
  <?php foreach ($flashShows as $fs):
    $sale = getFlashSale($fs['show_id']); if (!$sale) continue; ?>
  <div class="flash-sale-banner mb-3">
    <div class="flash-icon">⚡</div>
    <div>
      <h3>Flash Sale – Giảm <?= $sale['discount_pct'] ?>%!</h3>
      <p><?= htmlspecialchars($fs['title']) ?> · <?= $fs['room_name'] ?> · <?= date('H:i d/m', strtotime($fs['start_time'])) ?></p>
    </div>
    <a href="<?= APP_URL ?>/booking.php?show_id=<?= $fs['show_id'] ?>" class="btn btn-primary" style="margin-left:auto;white-space:nowrap">Đặt ngay</a>
  </div>
  <?php endforeach; ?>

  <!-- Header + Tabs -->
  <div class="movies-section-header">
    <div class="movies-section-title">PHIM</div>
    <div class="movies-tabs">
      <button class="movies-tab <?= $tab==='showing'?'active':'' ?>" onclick="location.href='?tab=showing'">Đang chiếu</button>
      <button class="movies-tab <?= $tab==='upcoming'?'active':'' ?>" onclick="location.href='?tab=upcoming'">Sắp chiếu</button>
    </div>
  </div>

  <?php if (empty($movies)): ?>
    <p class="text-muted text-center" style="padding:48px">Chưa có phim nào.</p>
  <?php else: ?>
  <div class="movies-grid">
    <?php foreach ($movies as $m):
      $ar      = $m['age_rating'] ?? 'P';
      $arColor = $ageColors[$ar] ?? '#27ae60';
      $rating  = round((float)($m['avg_rating'] ?? 0), 1);
      $hasRating = $rating > 0;
    ?>
    <div class="movie-card">
      <div class="poster">
        <?php if ($m['poster_url']): ?>
          <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
        <?php else: ?>🎬<?php endif; ?>

        <!-- Age badge -->
        <?php if ($ar !== 'P'): ?>
        <span class="poster-age-badge" style="background:<?= $arColor ?>"><?= $ar ?></span>
        <?php endif; ?>

        <!-- Rating badge -->
        <?php if ($hasRating): ?>
        <span class="poster-rating">⭐ <?= $rating ?></span>
        <?php endif; ?>

        <!-- Hover overlay -->
        <div class="poster-overlay">
          <a href="<?= APP_URL ?>/movie.php?id=<?= $m['id'] ?>" class="btn-buy-ticket-sm">
            🎟️ Mua vé
          </a>
          <?php if ($m['trailer_url']): ?>
          <a href="<?= htmlspecialchars($m['trailer_url']) ?>" target="_blank" class="btn-trailer-sm" onclick="event.stopPropagation()">
            ▶ Trailer
          </a>
          <?php endif; ?>
        </div>
      </div>

      <a href="<?= APP_URL ?>/movie.php?id=<?= $m['id'] ?>" style="text-decoration:none;color:inherit">
        <div class="movie-card-body">
          <div class="movie-card-title"><?= htmlspecialchars($m['title']) ?></div>
          <div class="movie-card-meta"><?= htmlspecialchars($m['genre'] ?? '') ?> · <?= $m['duration_min'] ?> phút</div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php renderFooter(); ?>
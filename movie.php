<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/booking.php';

startSession();
$id   = (int)($_GET['id'] ?? 0);
$db   = db();
$user = currentUser();

$stmt = $db->prepare("SELECT * FROM tblMovies WHERE id=?");
$stmt->bind_param('i', $id); $stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
if (!$movie) { header('Location: ' . APP_URL); exit; }

// Rating stats
$rStmt = $db->prepare("SELECT AVG(rating) as avg, COUNT(*) as cnt FROM tblRatings WHERE movie_id=?");
$rStmt->bind_param('i', $id); $rStmt->execute();
$rData = $rStmt->get_result()->fetch_assoc();
$avgRating  = round((float)$rData['avg'], 1);
$ratingCnt  = (int)$rData['cnt'];

// User's own rating
$myRating = 0;
if ($user) {
    $mStmt = $db->prepare("SELECT rating FROM tblRatings WHERE movie_id=? AND user_id=?");
    $mStmt->bind_param('ii', $id, $user['id']); $mStmt->execute();
    $myRating = (int)($mStmt->get_result()->fetch_assoc()['rating'] ?? 0);
}

// Handle AJAX rating submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate'])) {
    header('Content-Type: application/json');
    if (!$user) { echo json_encode(['ok'=>false,'msg'=>'Chưa đăng nhập']); exit; }
    $star = max(1, min(10, (int)$_POST['rate']));
    $ins = $db->prepare("INSERT INTO tblRatings(movie_id,user_id,rating) VALUES(?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating)");
    $ins->bind_param('iii', $id, $user['id'], $star);
    $ins->execute();
    // Update cached avg
    $db->query("UPDATE tblMovies SET avg_rating=(SELECT ROUND(AVG(rating),1) FROM tblRatings WHERE movie_id={$id}) WHERE id={$id}");
    // Recalc
    $rStmt->execute();
    $rData = $rStmt->get_result()->fetch_assoc();
    echo json_encode(['ok'=>true,'avg'=>round((float)$rData['avg'],1),'cnt'=>(int)$rData['cnt']]);
    exit;
}

// Shows
$sStmt = $db->prepare("
    SELECT s.*, r.name as room_name, r.floor
    FROM tblShows s JOIN tblRooms r ON r.id=s.room_id
    WHERE s.movie_id=? AND s.end_time > NOW()
    ORDER BY s.start_time ASC
");
$sStmt->bind_param('i', $id); $sStmt->execute();
$shows = $sStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$byDate = [];
foreach ($shows as $s) $byDate[date('Y-m-d', strtotime($s['start_time']))][] = $s;

// Sidebar: other showing movies
$others = $db->query("SELECT id,title,poster_url,avg_rating FROM tblMovies WHERE status='showing' AND id<>{$id} ORDER BY created_at DESC LIMIT 5")
             ->fetch_all(MYSQLI_ASSOC);

$ar       = $movie['age_rating'] ?? 'P';
$ageColors= ['P'=>'#27ae60','T13'=>'#2980b9','T16'=>'#e67e22','T18'=>'#e8192c'];
$arColor  = $ageColors[$ar] ?? '#27ae60';

// Genres as array
$genres = array_filter(array_map('trim', explode(',', $movie['genre'] ?? '')));

renderHead(htmlspecialchars($movie['title']));
?>
<style>
/* ── Movie detail page ── */
.movie-detail-page{background:#f5f5f5;min-height:calc(100vh - 64px)}

/* Hero backdrop */
.movie-hero{
  background:#111;
  position:relative;
  overflow:hidden;
  padding:48px 0 0;
}
.movie-hero-bg{
  position:absolute;inset:0;
  background-size:cover;background-position:center top;
  filter:blur(20px) brightness(.25);
  transform:scale(1.1);
}
.movie-hero-inner{
  max-width:1100px;margin:0 auto;padding:0 24px 40px;
  display:flex;gap:36px;align-items:flex-start;position:relative;z-index:1;
}

/* Poster */
.movie-hero-poster{
  width:220px;flex-shrink:0;
  border-radius:12px;overflow:hidden;
  box-shadow:0 16px 48px rgba(0,0,0,.6);
  aspect-ratio:2/3;background:#1a1a1a;
  display:flex;align-items:center;justify-content:center;font-size:60px;
  position:relative;
}
.movie-hero-poster img{width:100%;height:100%;object-fit:cover}
.hero-age-badge{
  position:absolute;bottom:10px;right:10px;
  padding:4px 10px;border-radius:5px;font-size:13px;font-weight:800;color:#fff;
}

/* Info */
.movie-hero-info{flex:1;min-width:0;color:#fff}
.movie-hero-title{font-size:32px;font-weight:900;margin:0 0 10px;letter-spacing:-.5px}
.movie-hero-meta{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.meta-item{display:flex;align-items:center;gap:5px;font-size:13px;color:rgba(255,255,255,.7)}

/* Clickable rating */
.rating-widget{
  display:inline-flex;align-items:center;gap:8px;cursor:pointer;
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
  padding:6px 14px;border-radius:20px;transition:background .15s;
}
.rating-widget:hover{background:rgba(255,255,255,.14)}
.rating-num{font-size:17px;font-weight:900;color:#f5c518}
.rating-count{font-size:11px;color:rgba(255,255,255,.5)}

/* Genres */
.genre-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.genre-tag{background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);padding:4px 12px;border-radius:14px;font-size:12px;font-weight:500}

/* Action buttons */
.hero-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-hero-buy{
  display:inline-flex;align-items:center;gap:7px;
  background:#e8192c;color:#fff;border:none;
  padding:12px 28px;border-radius:8px;font-size:15px;font-weight:700;
  cursor:pointer;text-decoration:none;transition:all .15s;font-family:inherit;
}
.btn-hero-buy:hover{background:#c5101f;text-decoration:none;color:#fff}
.btn-hero-trailer{
  display:inline-flex;align-items:center;gap:7px;
  background:rgba(255,255,255,.1);color:#fff;
  border:1.5px solid rgba(255,255,255,.3);
  padding:11px 24px;border-radius:8px;font-size:14px;font-weight:600;
  text-decoration:none;transition:all .15s;
}
.btn-hero-trailer:hover{background:rgba(255,255,255,.2);color:#fff;text-decoration:none}

/* Body */
.movie-body{max-width:1100px;margin:0 auto;padding:28px 24px;display:grid;grid-template-columns:1fr 280px;gap:24px;align-items:start}

/* Content card */
.content-card{background:#fff;border-radius:12px;border:1px solid #e8e8e8;overflow:hidden;margin-bottom:16px}
.content-card-header{padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:8px}
.content-card-header h3{font-size:15px;font-weight:700;color:#222;margin:0;border-left:3px solid #e8192c;padding-left:10px}
.content-card-body{padding:20px}

/* Showtime */
.showtime-date-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
.showtime-date-tab{
  padding:8px 16px;border-radius:8px;border:1px solid #ddd;
  background:#fff;font-size:12px;font-weight:600;color:#555;
  cursor:pointer;text-align:center;min-width:72px;
  transition:all .15s;font-family:inherit;
}
.showtime-date-tab:hover{border-color:#e8192c;color:#e8192c}
.showtime-date-tab.active{background:#e8192c;border-color:#e8192c;color:#fff}
.showtime-date-tab .day{font-size:11px}
.showtime-date-tab .date{font-size:13px;font-weight:800}

.showtime-group{margin-bottom:14px}
.showtime-room-name{font-size:13px;font-weight:700;color:#333;margin-bottom:8px;
  padding-bottom:6px;border-bottom:1px solid #f0f0f0}
.showtime-btns{display:flex;gap:8px;flex-wrap:wrap}
.st-btn{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  padding:9px 14px;border:1px solid #ddd;border-radius:6px;
  background:#fff;font-family:inherit;cursor:pointer;transition:all .15s;
  text-decoration:none;color:#333;min-width:68px;
}
.st-btn:hover{border-color:#e8192c;color:#e8192c;text-decoration:none}
.st-btn .st-time{font-size:14px;font-weight:700}
.st-btn .st-type{font-size:10px;color:#aaa}
.st-btn .st-flash-tag{font-size:9px;background:#e8192c;color:#fff;padding:1px 4px;border-radius:2px;font-weight:600}

/* Sidebar */
.sidebar-card{background:#fff;border-radius:12px;border:1px solid #e8e8e8;overflow:hidden;margin-bottom:14px}
.sidebar-card-header{padding:12px 16px;border-bottom:1px solid #f0f0f0}
.sidebar-card-header h4{font-size:13px;font-weight:700;color:#222;margin:0;border-left:3px solid #e8192c;padding-left:8px}
.sidebar-movie-item{display:flex;gap:10px;padding:10px 14px;border-bottom:1px solid #f5f5f5;text-decoration:none;transition:background .15s}
.sidebar-movie-item:last-child{border-bottom:none}
.sidebar-movie-item:hover{background:#f9f9f9;text-decoration:none}
.sidebar-poster{width:52px;height:72px;border-radius:5px;overflow:hidden;background:#eee;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:20px}
.sidebar-poster img{width:100%;height:100%;object-fit:cover}
.sidebar-movie-title{font-size:12px;font-weight:600;color:#222;line-height:1.4;margin-bottom:4px}
.sidebar-movie-rating{font-size:11px;color:#f5c518;font-weight:600}

/* Rating Modal */
.rating-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:20px}
.rating-modal{background:#fff;border-radius:16px;overflow:hidden;width:100%;max-width:400px;text-align:center}
.rating-modal-poster{width:100%;height:200px;object-fit:cover;display:block}
.rating-modal-poster-placeholder{width:100%;height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:60px}
.rating-modal-body{padding:24px}
.rating-modal-title{font-size:17px;font-weight:800;color:#222;margin-bottom:4px}
.star-rating-row{display:flex;justify-content:center;gap:6px;margin:18px 0}
.star-rating-row .star{font-size:26px;cursor:pointer;color:#ddd;transition:color .1s}
.star-rating-row .star.lit{color:#f5c518}
.rating-modal-actions{display:flex;gap:8px;margin-top:4px}
.btn-rating-cancel{flex:1;padding:11px;border-radius:8px;border:1px solid #ddd;background:#f5f5f5;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;color:#555}
.btn-rating-submit{flex:1;padding:11px;border-radius:8px;border:none;background:#e8192c;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit}
.btn-rating-submit:disabled{background:#fca5a5;cursor:not-allowed}

@media(max-width:768px){
  .movie-hero-inner{flex-direction:column;align-items:center;text-align:center}
  .movie-hero-poster{width:160px}
  .movie-hero-meta{justify-content:center}
  .genre-tags{justify-content:center}
  .hero-actions{justify-content:center}
  .movie-body{grid-template-columns:1fr}
}
</style>

<?php renderNav(); ?>

<div class="movie-detail-page">

<!-- Hero -->
<div class="movie-hero">
  <?php if ($movie['poster_url']): ?>
  <div class="movie-hero-bg" style="background-image:url('<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($movie['poster_url']) ?>')"></div>
  <?php endif; ?>
  <div class="movie-hero-inner">
    <div class="movie-hero-poster">
      <?php if ($movie['poster_url']): ?>
        <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($movie['poster_url']) ?>" alt="">
      <?php else: ?>🎬<?php endif; ?>
      <?php if ($ar !== 'P'): ?>
      <span class="hero-age-badge" style="background:<?= $arColor ?>"><?= $ar ?></span>
      <?php endif; ?>
    </div>

    <div class="movie-hero-info">
      <h1 class="movie-hero-title"><?= htmlspecialchars($movie['title']) ?></h1>
      <div class="movie-hero-meta">
        <span class="meta-item">⏱ <?= $movie['duration_min'] ?> phút</span>
        <?php if ($movie['created_at']): ?>
        <span class="meta-item">📅 <?= date('d/m/Y', strtotime($movie['created_at'])) ?></span>
        <?php endif; ?>
        <!-- Rating widget — bấm vào để đánh giá -->
        <div class="rating-widget" onclick="openRatingModal()" title="Bấm để đánh giá">
          <span class="rating-num">⭐ <span id="avgRatingDisplay"><?= $avgRating ?: '–' ?></span></span>
          <span class="rating-count">(<span id="ratingCntDisplay"><?= $ratingCnt ?></span> đánh giá)</span>
        </div>
      </div>

      <?php if (!empty($genres)): ?>
      <div class="genre-tags">
        <?php foreach ($genres as $g): ?>
        <span class="genre-tag"><?= htmlspecialchars($g) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <p style="color:rgba(255,255,255,.7);line-height:1.7;margin-bottom:22px;font-size:14px;max-width:600px">
        <?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?>
      </p>

      <div class="hero-actions">
        <?php if ($movie['status'] === 'showing'): ?>
        <a href="<?= APP_URL ?>/booking.php" class="btn-hero-buy">🎟️ Mua vé ngay</a>
        <?php endif; ?>
        <?php if ($movie['trailer_url']): ?>
        <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" target="_blank" class="btn-hero-trailer">▶ Xem trailer</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div><!-- /hero -->

<!-- Body -->
<div class="movie-body">
  <div>
    <!-- Description card -->
    <?php if ($movie['description']): ?>
    <div class="content-card">
      <div class="content-card-header"><h3>Nội Dung Phim</h3></div>
      <div class="content-card-body" style="color:#555;font-size:14px;line-height:1.8">
        <?= nl2br(htmlspecialchars($movie['description'])) ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Showtimes -->
    <div class="content-card">
      <div class="content-card-header"><h3>Lịch Chiếu</h3></div>
      <div class="content-card-body">
        <?php if (empty($shows)): ?>
          <p style="color:#aaa;font-size:13px">Chưa có suất chiếu nào.</p>
        <?php else: ?>
        <!-- Date tabs -->
        <div class="showtime-date-tabs" id="dateTabs">
          <?php $first = true; foreach ($byDate as $date => $_): ?>
          <button class="showtime-date-tab <?= $first?'active':'' ?>"
                  onclick="switchDate('<?= $date ?>',this)"
                  data-date="<?= $date ?>">
            <div class="day"><?= date('D', strtotime($date)) ?></div>
            <div class="date"><?= date('d/m', strtotime($date)) ?></div>
          </button>
          <?php $first=false; endforeach; ?>
        </div>

        <!-- Shows per date -->
        <?php $first=true; foreach ($byDate as $date => $dayShows): ?>
        <div class="date-shows" id="shows-<?= $date ?>" style="<?= $first?'':'display:none' ?>">
          <!-- Group by room -->
          <?php
            $byRoom = [];
            foreach ($dayShows as $s) $byRoom[$s['room_name']][] = $s;
          ?>
          <?php foreach ($byRoom as $roomName => $roomShows): ?>
          <div class="showtime-group">
            <div class="showtime-room-name">🏠 <?= htmlspecialchars($roomName) ?></div>
            <div class="showtime-btns">
              <?php foreach ($roomShows as $s):
                $sale = getFlashSale($s['id']); ?>
              <a href="<?= APP_URL ?>/booking.php?show_id=<?= $s['id'] ?>" class="st-btn">
                <span class="st-time"><?= date('H:i', strtotime($s['start_time'])) ?></span>
                <span class="st-type">2D</span>
                <?php if ($sale): ?>
                <span class="st-flash-tag">⚡ -<?= $sale['discount_pct'] ?>%</span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php $first=false; endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div>
    <div class="sidebar-card">
      <div class="sidebar-card-header"><h4>Phim Đang Chiếu</h4></div>
      <?php foreach ($others as $o):
        $or = round((float)($o['avg_rating']??0),1); ?>
      <a href="<?= APP_URL ?>/movie.php?id=<?= $o['id'] ?>" class="sidebar-movie-item">
        <div class="sidebar-poster">
          <?php if ($o['poster_url']): ?>
            <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($o['poster_url']) ?>" alt="">
          <?php else: ?>🎬<?php endif; ?>
        </div>
        <div>
          <div class="sidebar-movie-title"><?= htmlspecialchars($o['title']) ?></div>
          <?php if ($or > 0): ?>
          <div class="sidebar-movie-rating">⭐ <?= $or ?></div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <a href="<?= APP_URL ?>/index.php" style="display:block;text-align:center;padding:12px;color:#e8192c;font-size:13px;font-weight:600;text-decoration:none;border-top:1px solid #f0f0f0">Xem thêm →</a>
    </div>
  </div>
</div>

</div><!-- /movie-detail-page -->

<!-- Rating Modal -->
<div class="rating-modal-overlay" id="ratingModal">
  <div class="rating-modal">
    <?php if ($movie['poster_url']): ?>
    <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($movie['poster_url']) ?>" class="rating-modal-poster" alt="">
    <?php else: ?>
    <div class="rating-modal-poster-placeholder">🎬</div>
    <?php endif; ?>
    <div class="rating-modal-body">
      <div class="rating-modal-title"><?= htmlspecialchars($movie['title']) ?></div>
      <div style="font-size:16px;font-weight:800;color:#f5c518;margin-top:6px">
        ⭐ <span id="modalAvg"><?= $avgRating ?: '–' ?></span>
        <span style="font-size:12px;color:#aaa;font-weight:400">(<span id="modalCnt"><?= $ratingCnt ?></span> đánh giá)</span>
      </div>

      <div class="star-rating-row" id="starRow">
        <?php for ($i=1;$i<=10;$i++): ?>
        <span class="star <?= ($myRating>=$i?'lit':'') ?>" data-val="<?= $i ?>" onclick="selectStar(<?= $i ?>)" onmouseover="hoverStar(<?= $i ?>)" onmouseleave="resetStars()">★</span>
        <?php endfor; ?>
      </div>
      <div style="font-size:13px;color:#888;margin-bottom:16px" id="starLabel">
        <?= $myRating ? "Bạn đã chấm: {$myRating}/10" : 'Bấm để chấm điểm' ?>
      </div>

      <div class="rating-modal-actions">
        <button class="btn-rating-cancel" onclick="closeRatingModal()">Đóng</button>
        <button class="btn-rating-submit" id="ratingSubmitBtn" onclick="submitRating()" <?= $user?'':'disabled' ?>>
          <?= $user ? '☑ Xác Nhận' : 'Đăng nhập để đánh giá' ?>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const MOVIE_ID = <?= $id ?>;
const BASE_URL = '<?= APP_URL ?>';
let selectedStar = <?= $myRating ?>;
const myOld = <?= $myRating ?>;

function switchDate(date, btn) {
  document.querySelectorAll('.showtime-date-tab').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.date-shows').forEach(d=>d.style.display='none');
  const el = document.getElementById('shows-'+date);
  if (el) el.style.display='block';
}

function openRatingModal()  { document.getElementById('ratingModal').style.display='flex'; }
function closeRatingModal() { document.getElementById('ratingModal').style.display='none'; resetStars(); }

function hoverStar(n) {
  document.querySelectorAll('.star').forEach((s,i)=>s.classList.toggle('lit',i<n));
  document.getElementById('starLabel').textContent = n + '/10 điểm';
}
function resetStars() {
  document.querySelectorAll('.star').forEach((s,i)=>s.classList.toggle('lit',i<selectedStar));
  document.getElementById('starLabel').textContent = selectedStar ? `Bạn đã chấm: ${selectedStar}/10` : 'Bấm để chấm điểm';
}
function selectStar(n) {
  selectedStar = n;
  resetStars();
}

async function submitRating() {
  if (!selectedStar) return;
  const btn = document.getElementById('ratingSubmitBtn');
  btn.disabled = true; btn.textContent = '⏳ Đang lưu...';
  const fd = new FormData();
  fd.append('rate', selectedStar);
  const res  = await fetch(`${BASE_URL}/movie.php?id=${MOVIE_ID}`, {method:'POST',body:fd});
  const data = await res.json();
  if (data.ok) {
    document.getElementById('avgRatingDisplay').textContent = data.avg || '–';
    document.getElementById('ratingCntDisplay').textContent = data.cnt;
    document.getElementById('modalAvg').textContent = data.avg || '–';
    document.getElementById('modalCnt').textContent = data.cnt;
    btn.textContent = '✓ Đã lưu!';
    setTimeout(closeRatingModal, 800);
  } else {
    btn.disabled = false; btn.textContent = '☑ Xác Nhận';
    alert(data.msg || 'Lỗi, thử lại nhé.');
  }
}

// Close modal on overlay click
document.getElementById('ratingModal').addEventListener('click', function(e){
  if (e.target === this) closeRatingModal();
});
</script>

<?php renderFooter(); ?>
<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/booking.php';

requireLogin();
$user   = currentUser();
$db     = db();

// step: 1=chọn phim/suất  2=chọn ghế  3=chọn thức ăn  4=thanh toán
$step   = (int)($_GET['step']    ?? 1);
$showId = (int)($_GET['show_id'] ?? 0);

if ($showId && $step < 2) $step = 2;

$show = null;
if ($showId) {
    // Check if age_rating column exists
    $hasAgeCol = $db->query("SHOW COLUMNS FROM tblMovies LIKE 'age_rating'")->num_rows > 0;
    $ageSelect = $hasAgeCol ? "m.age_rating," : "'P' as age_rating,";

    $stmt = $db->prepare("
        SELECT s.*, m.title as movie_title, m.duration_min, m.poster_url,
               m.genre, {$ageSelect} r.name as room_name, r.floor
        FROM tblShows s
        JOIN tblMovies m ON m.id = s.movie_id
        JOIN tblRooms  r ON r.id = s.room_id
        WHERE s.id=? AND s.end_time > NOW()
    ");
    $stmt->bind_param('i', $showId);
    $stmt->execute();
    $show = $stmt->get_result()->fetch_assoc();
    if (!$show) { $step = 1; $showId = 0; }
}

$movies = [];
if ($step === 1) {
    $movies = $db->query("SELECT * FROM tblMovies WHERE status='showing' ORDER BY created_at DESC")
                 ->fetch_all(MYSQLI_ASSOC);
}

$prices = []; $flash = null;
if ($show) {
    $res = $db->query("SELECT seat_type, price FROM tblPrices");
    while ($r = $res->fetch_assoc()) $prices[$r['seat_type']] = (int)$r['price'];
    $flash = getFlashSale($showId);
}

// Age rating config
$ageInfo = [
    'P'   => ['label'=>'P',   'color'=>'#27ae60', 'msg'=>''],
    'T13' => ['label'=>'T13', 'color'=>'#2980b9', 'msg'=>'Phim dành cho khán giả từ 13 tuổi trở lên. Người xem dưới 13 tuổi phải có người giám hộ đi kèm.'],
    'T16' => ['label'=>'T16', 'color'=>'#e67e22', 'msg'=>'Phim dành cho khán giả từ 16 tuổi trở lên. Không phù hợp với người xem dưới 16 tuổi.'],
    'T18' => ['label'=>'T18', 'color'=>'#e8192c', 'msg'=>'Phim dành cho khán giả từ 18 tuổi trở lên. Cấm khán giả dưới 18 tuổi.'],
];

// Combo items (giả lập — bạn có thể tạo bảng DB sau)
$combos = [
    ['id'=>1, 'name'=>'Combo 1 Big',          'desc'=>'1 bắp rang bơ vừa + 1 Pepsi mát lạnh',                     'price'=>90000,  'emoji'=>'🍿'],
    ['id'=>2, 'name'=>'Combo 2 Big',           'desc'=>'1 bắp rang bơ lớn + 2 Pepsi cỡ lớn',                      'price'=>109000, 'emoji'=>'🍿'],
    ['id'=>3, 'name'=>'Combo 1 Big Extra',     'desc'=>'1 bắp lớn + 1 Pepsi + 1 gói snack Premium',               'price'=>115000, 'emoji'=>'🥤'],
    ['id'=>4, 'name'=>'Combo 2 Big Extra',     'desc'=>'1 bắp lớn + 2 Pepsi + 1 snack Premium',                   'price'=>134000, 'emoji'=>'🥤'],
    ['id'=>5, 'name'=>'Combo 3',               'desc'=>'2 bắp rang bơ + 3 Pepsi mát lạnh',                        'price'=>149000, 'emoji'=>'🎉'],
    ['id'=>6, 'name'=>'Combo 4 (Nhóm bạn)',   'desc'=>'3 bắp rang bơ lớn + 4 Pepsi – siêu tiết kiệm!',           'price'=>229000, 'emoji'=>'🎊'],
];

renderHead('Mua vé - MiniCine');
?>
<style>
/* Progress bar */
.booking-progress{background:#fff;border-bottom:1px solid #e8e8e8}
.progress-inner{max-width:960px;margin:0 auto;display:flex;align-items:center;justify-content:center;padding:0 20px}
.prog-step{display:flex;flex-direction:column;align-items:center;padding:16px 28px;position:relative;min-width:130px;text-align:center}
.prog-step::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:transparent;border-radius:2px 2px 0 0}
.prog-step.active::after{background:#e8192c}
.prog-step.done::after{background:#ddd}
.prog-step-label{font-size:12px;font-weight:600;color:#bbb;white-space:nowrap}
.prog-step.active .prog-step-label{color:#e8192c}
.prog-step.done .prog-step-label{color:#666}
.prog-sep{width:40px;height:1px;background:#ddd;flex-shrink:0}

/* Layout */
.booking-page{background:#f5f5f5;min-height:calc(100vh - 120px);padding:24px}
.booking-wrap{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 310px;gap:20px;align-items:start}

/* Accordion */
.acc-card{background:#fff;border-radius:10px;border:1px solid #e0e0e0;margin-bottom:12px;overflow:hidden}
.acc-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;font-size:15px;font-weight:700;color:#222;cursor:pointer;user-select:none}
.acc-icon{width:26px;height:26px;background:#e8192c;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:800;flex-shrink:0}
.acc-chevron{color:#aaa;font-size:12px;transition:transform .2s}
.acc-card.open .acc-chevron{transform:rotate(180deg)}
.acc-body{display:none;padding:0 20px 20px}
.acc-card.open .acc-body{display:block}

/* Movie list */
.movie-list-scroll{display:flex;gap:12px;overflow-x:auto;padding-bottom:6px}
.movie-list-scroll::-webkit-scrollbar{height:4px}
.movie-list-scroll::-webkit-scrollbar-thumb{background:#ddd;border-radius:2px}
.movie-pick-card{flex-shrink:0;width:120px;cursor:pointer;border-radius:8px;overflow:hidden;border:2px solid transparent;transition:border-color .15s,transform .15s;background:#f5f5f5;position:relative}
.movie-pick-card:hover{transform:translateY(-3px);border-color:#e8192c}
.movie-pick-card.selected{border-color:#e8192c}
.movie-pick-card img,.movie-pick-card .no-poster{width:100%;aspect-ratio:2/3;object-fit:cover;display:flex;align-items:center;justify-content:center;font-size:36px;background:#ddd}
.mpcard-title{padding:6px 7px 8px;font-size:11px;font-weight:600;color:#222;line-height:1.3;text-align:center}
.movie-pick-card.selected .mpcard-title{color:#e8192c}
.age-badge-card{position:absolute;top:6px;right:6px;padding:2px 6px;border-radius:3px;font-size:10px;font-weight:800;color:#fff}

/* Date tabs */
.date-tabs{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.date-tab{padding:7px 14px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:12px;font-weight:600;color:#555;cursor:pointer;transition:all .15s;text-align:center;font-family:inherit}
.date-tab:hover{border-color:#e8192c;color:#e8192c}
.date-tab.active{background:#e8192c;border-color:#e8192c;color:#fff}

/* Showtime */
.showtime-btns{display:flex;gap:8px;flex-wrap:wrap}
.showtime-btn{padding:9px 14px;border:1px solid #ddd;border-radius:6px;background:#fff;font-size:13px;font-weight:700;color:#333;cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:2px;min-width:68px;text-align:center;font-family:inherit}
.showtime-btn:hover{border-color:#e8192c;color:#e8192c}
.showtime-btn.picked{background:#e8192c;border-color:#e8192c;color:#fff}
.st-room{font-size:10px;color:#aaa;font-weight:500}
.showtime-btn.picked .st-room{color:rgba(255,255,255,.7)}
.st-flash{font-size:9px;background:#e8192c;color:#fff;padding:1px 4px;border-radius:3px;font-weight:600}
.showtime-btn.picked .st-flash{background:rgba(255,255,255,.25)}
.showtime-group-title{font-size:12px;font-weight:600;color:#888;margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid #f0f0f0}

/* Order panel */
.order-panel{background:#fff;border-radius:10px;border:1px solid #e0e0e0;overflow:hidden;position:sticky;top:76px}
.order-panel-top{height:5px;background:linear-gradient(90deg,#e8192c,#ff6b00)}
.order-panel-body{padding:18px}
.order-poster{width:72px;height:100px;border-radius:6px;overflow:hidden;background:#eee;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0}
.order-poster img{width:100%;height:100%;object-fit:cover}
.order-movie-title{font-size:13px;font-weight:700;color:#222;margin-bottom:3px;line-height:1.4}
.order-meta{font-size:11px;color:#999;line-height:1.5}
.order-divider{border:none;border-top:1px dashed #e0e0e0;margin:14px 0}
.order-total-label{font-size:13px;color:#888}
.order-total-amount{font-size:17px;font-weight:800;color:#e8192c}
.btn-continue{display:block;width:100%;background:#e8192c;color:#fff;border:none;border-radius:8px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;transition:all .15s;margin-top:12px;font-family:inherit}
.btn-continue:hover:not(:disabled){background:#c5101f}
.btn-continue:disabled{background:#fca5a5;cursor:not-allowed}
.btn-back{display:block;text-align:center;color:#e8192c;font-size:13px;font-weight:600;padding:7px;margin-top:4px;cursor:pointer;background:none;border:none;font-family:inherit;text-decoration:none;width:100%}
.btn-back:hover{text-decoration:underline}

/* Seat map */
.seat-wrap-outer{background:#fff;border-radius:10px;border:1px solid #e0e0e0;padding:20px}
.seat.available{background:#f0f0f0;border-color:#ccc;color:#555}
.seat.available:hover{background:#ffe5e5;border-color:#e8192c}
.seat.vip-seat{background:#fff9e0;border-color:#e0c040;color:#b8860b}
.seat.vip-seat:hover{background:#fff3b0;border-color:#f5c518}
.seat.couple-seat{background:#ede5ff;border-color:#b39ddb;color:#7c4dff}
.seat.couple-seat:hover{background:#dcd0ff;border-color:#7c4dff}
.seat.held{background:#ffe0b2;border-color:#ff9800;color:#e65100;cursor:not-allowed}
.seat.booked{background:#e0e0e0;border-color:#bbb;color:#aaa;cursor:not-allowed}
.seat.selected{background:#ff9800;border-color:#e65100;color:#fff;transform:scale(1.1)}
.suggest-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;padding:10px 14px;background:#fafafa;border-radius:8px;border:1px solid #f0f0f0}
.suggest-bar label{font-size:12px;color:#666}
.suggest-bar input{width:56px;padding:5px 8px;border:1px solid #ddd;border-radius:5px;font-size:13px;text-align:center;font-family:inherit}
.suggest-bar input:focus{outline:none;border-color:#e8192c}
.btn-suggest{padding:6px 14px;border-radius:5px;background:#fff;border:1px solid #ddd;font-size:12px;font-weight:600;color:#333;cursor:pointer;font-family:inherit}
.btn-suggest:hover{border-color:#e8192c;color:#e8192c}
.btn-clear{padding:6px 10px;border-radius:5px;background:none;border:1px solid #ddd;font-size:12px;color:#888;cursor:pointer;font-family:inherit}
.countdown{background:#fff9e0;border:1px solid #e0c040;color:#b8860b;border-radius:8px;padding:8px 14px;font-size:13px;display:inline-flex;align-items:center;gap:6px;margin-bottom:12px}
.countdown.urgent{background:#fff0f0;border-color:#ffb3b3;color:#c0392b}
.flash-banner-light{background:linear-gradient(135deg,#e8192c,#ff6b00);border-radius:8px;padding:12px 16px;display:flex;align-items:center;gap:10px;margin-bottom:14px;color:#fff}
.flash-banner-light h3{font-size:14px;font-weight:800;margin-bottom:1px}
.flash-banner-light p{font-size:11px;opacity:.85}
.sel-seat-row{display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:1px solid #f5f5f5}
.sel-seat-row:last-child{border-bottom:none}

/* ── Combo step ── */
.combo-list{display:flex;flex-direction:column;gap:12px}
.combo-item{background:#fff;border-radius:10px;border:1px solid #e0e0e0;padding:16px;display:flex;align-items:center;gap:14px}
.combo-emoji{font-size:40px;width:60px;text-align:center;flex-shrink:0}
.combo-info{flex:1}
.combo-name{font-size:14px;font-weight:700;color:#222;margin-bottom:3px}
.combo-desc{font-size:12px;color:#888;margin-bottom:6px}
.combo-price{font-size:14px;font-weight:800;color:#e8192c}
.combo-qty{display:flex;align-items:center;gap:8px;flex-shrink:0}
.qty-btn{width:28px;height:28px;border-radius:50%;border:1px solid #ddd;background:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:inherit;transition:all .15s;line-height:1}
.qty-btn:hover{border-color:#e8192c;color:#e8192c}
.qty-num{min-width:20px;text-align:center;font-size:14px;font-weight:700;color:#222}

/* ── Payment step ── */
.payment-card{background:#fff;border-radius:10px;border:1px solid #e0e0e0;padding:24px;margin-bottom:12px}
.payment-card h3{font-size:15px;font-weight:700;color:#222;margin-bottom:16px}
.order-info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f5f5f5;font-size:13px}
.order-info-row:last-child{border-bottom:none}
.order-info-label{color:#888}
.order-info-value{font-weight:600;color:#222;text-align:right}

/* Combo in panel */
.combo-panel-row{display:flex;justify-content:space-between;font-size:12px;padding:3px 0;color:#666}

/* ── Modal (age + payment) ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px}
.modal-box{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;text-align:center}

/* QR */
.qr-ticket{background:#fff;border-radius:16px;border:2px dashed #e0e0e0;padding:24px;text-align:center;max-width:340px;margin:0 auto}
.qr-ticket-top{background:linear-gradient(135deg,#e8192c,#ff6b00);border-radius:10px;padding:14px;margin-bottom:16px;color:#fff}
.qr-ticket-top h3{font-size:16px;font-weight:800}
.qr-ticket-row{display:flex;justify-content:space-between;font-size:12px;padding:5px 0;border-bottom:1px solid #f5f5f5}
.qr-ticket-row:last-child{border-bottom:none}
.qr-ticket-label{color:#999}
.qr-ticket-value{font-weight:600;color:#222}

@media(max-width:768px){
  .booking-wrap{grid-template-columns:1fr}
  .order-panel{position:static}
  .booking-page{padding:12px}
  .prog-step{min-width:80px;padding:12px 8px}
  .prog-step-label{font-size:11px}
  .prog-sep{width:16px}
}
</style>

<?php renderNav(); ?>

<!-- Progress bar -->
<div class="booking-progress">
  <div class="progress-inner">
    <?php
    $steps = ['Chọn phim / Rạp / Suất','Chọn ghế','Chọn thức ăn','Thanh toán','Xác nhận'];
    foreach ($steps as $i => $label):
      $sn = $i + 1;
      $cls = $step === $sn ? 'active' : ($step > $sn ? 'done' : '');
    ?>
    <?php if ($i > 0): ?><div class="prog-sep"></div><?php endif; ?>
    <div class="prog-step <?= $cls ?>">
      <span class="prog-step-label"><?= $label ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="booking-page">
<div class="booking-wrap">

<?php /* ═══════ STEP 1 ═══════ */ if ($step === 1): ?>

  <div>
    <div class="acc-card open" id="acc-movie">
      <div class="acc-header" onclick="toggleAcc('acc-movie')">
        <div style="display:flex;align-items:center;gap:10px"><div class="acc-icon">1</div><span id="acc-movie-label">Chọn phim</span></div>
        <span class="acc-chevron">▾</span>
      </div>
      <div class="acc-body">
        <?php if (empty($movies)): ?><p style="color:#888;font-size:13px">Chưa có phim đang chiếu.</p>
        <?php else: ?>
        <div class="movie-list-scroll">
          <?php foreach ($movies as $m):
            $ar = $m['age_rating'] ?? 'P';
            $arColor = $ageInfo[$ar]['color'] ?? '#27ae60';
          ?>
          <div class="movie-pick-card" id="mpcard-<?= $m['id'] ?>"
               onclick="selectMovie(<?= $m['id'] ?>,<?= htmlspecialchars(json_encode($m['title'])) ?>,<?= htmlspecialchars(json_encode($m['poster_url']??'')) ?>,<?= htmlspecialchars(json_encode($ar)) ?>)">
            <?php if ($m['poster_url']): ?>
              <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($m['poster_url']) ?>" alt="">
            <?php else: ?><div class="no-poster">🎬</div><?php endif; ?>
            <?php if ($ar !== 'P'): ?>
            <span class="age-badge-card" style="background:<?= $arColor ?>"><?= $ar ?></span>
            <?php endif; ?>
            <div class="mpcard-title"><?= htmlspecialchars($m['title']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="acc-card" id="acc-show">
      <div class="acc-header" onclick="toggleAcc('acc-show')">
        <div style="display:flex;align-items:center;gap:10px"><div class="acc-icon">2</div><span id="acc-show-label">Chọn suất</span></div>
        <span class="acc-chevron">▾</span>
      </div>
      <div class="acc-body" id="showBody"><p style="color:#aaa;font-size:13px">Vui lòng chọn phim trước.</p></div>
    </div>
  </div>

  <div class="order-panel">
    <div class="order-panel-top"></div>
    <div class="order-panel-body">
      <div style="display:flex;gap:12px;align-items:flex-start">
        <div class="order-poster" id="panelPoster">🎬</div>
        <div><div class="order-movie-title" id="panelTitle">–</div><div class="order-meta" id="panelMeta">Chưa chọn phim</div></div>
      </div>
      <hr class="order-divider">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="order-total-label">Tổng cộng</span><span class="order-total-amount">0 đ</span>
      </div>
      <a href="<?= APP_URL ?>/index.php" class="btn-back">← Quay lại</a>
      <button class="btn-continue" disabled id="btnContinue">Tiếp tục</button>
    </div>
  </div>

<?php /* ═══════ STEP 2: Chọn ghế ═══════ */ elseif ($step === 2):
  $ar = $show['age_rating'] ?? 'P';
  $arMsg = $ageInfo[$ar]['msg'] ?? '';
  $arColor = $ageInfo[$ar]['color'] ?? '#27ae60';
?>

  <div>
    <div class="acc-card" id="acc-chosen">
      <div class="acc-header" onclick="toggleAcc('acc-chosen')">
        <div style="display:flex;align-items:center;gap:10px">
          <div class="acc-icon" style="background:#27ae60">✓</div>
          <span><?= htmlspecialchars($show['movie_title']) ?> – <?= htmlspecialchars($show['room_name']) ?> – <?= date('H:i d/m',strtotime($show['start_time'])) ?></span>
        </div>
        <span class="acc-chevron">▾</span>
      </div>
      <div class="acc-body" style="color:#888;font-size:13px">
        📅 <?= date('l, d/m/Y',strtotime($show['start_time'])) ?> &nbsp;·&nbsp; ⏱ <?= $show['duration_min'] ?> phút &nbsp;·&nbsp; 🏠 Tầng <?= $show['floor'] ?>
      </div>
    </div>

    <div class="seat-wrap-outer">
      <div id="flash-banner" class="flash-banner-light" style="display:<?= $flash?'flex':'none' ?>">
        <div style="font-size:22px">⚡</div>
        <div><h3>Flash Sale – Giảm <span id="flash-pct"><?= $flash?$flash['discount_pct']:0 ?>%</span></h3><p>Áp dụng ghế Standard và VIP.</p></div>
      </div>
      <div class="suggest-bar">
        <label>Gợi ý ghế liền kề:</label>
        <input type="number" id="suggest-n" min="1" max="8" value="2">
        <button class="btn-suggest" onclick="suggestSeats()">🎯 Tìm ghế</button>
        <button class="btn-clear" onclick="clearSelection()">✕ Xoá</button>
      </div>
      <div id="suggest-msg"></div>
      <div id="countdown-wrap" style="display:none"><div class="countdown">⏱ Ghế được giữ trong: <strong id="countdown-time">5:00</strong></div></div>
      <div class="screen-curve">
        <svg viewBox="0 0 400 30" xmlns="http://www.w3.org/2000/svg">
          <path d="M20 28 Q200 4 380 28" fill="none" stroke="#bbb" stroke-width="3" stroke-linecap="round"/>
          <text x="200" y="20" text-anchor="middle" fill="#bbb" font-size="10" font-family="Inter">MÀN HÌNH</text>
        </svg>
      </div>
      <div class="seat-map-wrap" id="seat-map"><div class="spinner"></div></div>
      <div class="seat-legend" style="margin-top:16px">
        <div class="legend-item"><div class="legend-box" style="background:#f0f0f0;border-color:#ccc"></div>Trống</div>
        <div class="legend-item"><div class="legend-box" style="background:#fff9e0;border-color:#e0c040"></div>VIP</div>
        <div class="legend-item"><div class="legend-box" style="background:#ede5ff;border-color:#b39ddb"></div>Couple</div>
        <div class="legend-item"><div class="legend-box" style="background:#ffe0b2;border-color:#ff9800"></div>Đang giữ</div>
        <div class="legend-item"><div class="legend-box" style="background:#e0e0e0;border-color:#bbb"></div>Đã đặt</div>
        <div class="legend-item"><div class="legend-box" style="background:#ff9800;border-color:#e65100"></div>Đang chọn</div>
      </div>
    </div>
  </div>

  <div class="order-panel">
    <div class="order-panel-top"></div>
    <div class="order-panel-body">
      <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px">
        <div class="order-poster">
          <?php if ($show['poster_url']): ?>
            <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($show['poster_url']) ?>" alt="">
          <?php else: ?>🎬<?php endif; ?>
        </div>
        <div>
          <div class="order-movie-title"><?= htmlspecialchars($show['movie_title']) ?></div>
          <?php if ($ar !== 'P'): ?>
          <span style="background:<?= $arColor ?>;color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:700"><?= $ar ?></span>
          <?php endif; ?>
          <div class="order-meta" style="margin-top:4px"><?= htmlspecialchars($show['room_name']) ?></div>
          <div class="order-meta"><?= date('H:i – l, d/m/Y',strtotime($show['start_time'])) ?></div>
        </div>
      </div>
      <hr class="order-divider">
      <div id="selected-list"><p style="color:#bbb;font-size:12px;text-align:center;padding:6px 0">Chưa chọn ghế nào</p></div>
      <div id="points-section" style="display:none;margin-top:10px">
        <div style="background:#fff9e0;border:1px solid #e0c040;border-radius:8px;padding:10px">
          <div style="font-size:11px;font-weight:700;color:#b8860b;margin-bottom:5px">⭐ Dùng điểm thưởng</div>
          <div style="font-size:11px;color:#888;margin-bottom:7px">
            Bạn có <strong style="color:#b8860b"><?= number_format($user['points']) ?></strong> điểm
          </div>
          <div style="display:flex;gap:5px">
            <input type="number" id="points-input" min="0" max="<?= $user['points'] ?>" step="100" value="0"
                   style="flex:1;padding:6px 8px;border:1px solid #ddd;border-radius:5px;font-size:12px;font-family:inherit"
                   oninput="applyPoints()">
            <button onclick="useAllPoints()" style="padding:6px 8px;border:1px solid #ddd;border-radius:5px;font-size:11px;font-weight:600;background:#fff;cursor:pointer;font-family:inherit;white-space:nowrap">Tất cả</button>
          </div>
          <div id="points-discount-display" style="font-size:11px;color:#27ae60;margin-top:4px"></div>
        </div>
      </div>
      <hr class="order-divider">
      <div id="points-row" style="display:none;justify-content:space-between;font-size:12px;margin-bottom:5px">
        <span style="color:#b8860b">⭐ Giảm điểm</span>
        <span id="points-discount-amount" style="color:#b8860b;font-weight:600">-0đ</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="order-total-label">Tổng cộng</span>
        <span class="order-total-amount" id="total-price">0 đ</span>
      </div>
      <a href="<?= APP_URL ?>/booking.php" class="btn-back">← Quay lại</a>
      <button class="btn-continue" id="pay-btn" disabled onclick="onContinueClick()">Tiếp tục</button>
    </div>
  </div>

  <!-- Age rating modal -->
  <?php if ($arMsg): ?>
  <div class="modal-overlay" id="age-modal" style="display:flex">
    <div class="modal-box">
      <div style="width:52px;height:52px;background:<?= $arColor ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;color:#fff;margin:0 auto 16px"><?= $ar ?></div>
      <h3 style="font-size:17px;font-weight:800;margin-bottom:10px;color:#222">Xác nhận mua vé cho người có độ tuổi phù hợp</h3>
      <p style="font-size:13px;color:#e8192c;line-height:1.6;margin-bottom:22px"><?= $arMsg ?></p>
      <div style="display:flex;gap:10px">
        <button onclick="window.location.href='<?= APP_URL ?>/booking.php'"
          style="flex:1;padding:12px;border-radius:8px;border:1px solid #ddd;background:#f5f5f5;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit">Từ chối</button>
        <button onclick="document.getElementById('age-modal').style.display='none'"
          style="flex:1;padding:12px;border-radius:8px;border:none;background:#e8192c;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit">Xác nhận</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

<?php /* ═══════ STEP 3: Chọn thức ăn ═══════ */ elseif ($step === 3): ?>

  <div>
    <div class="acc-card" id="acc-chosen3">
      <div class="acc-header" onclick="toggleAcc('acc-chosen3')">
        <div style="display:flex;align-items:center;gap:10px">
          <div class="acc-icon" style="background:#27ae60">✓</div>
          <span><?= htmlspecialchars($show['movie_title']) ?> – <?= htmlspecialchars($show['room_name']) ?> – <?= date('H:i d/m',strtotime($show['start_time'])) ?></span>
        </div>
        <span class="acc-chevron">▾</span>
      </div>
      <div class="acc-body" style="font-size:12px;color:#888">
        Ghế: <span id="seatSummary3" style="font-weight:600;color:#222"><?= htmlspecialchars($_GET['seats'] ?? '?') ?></span>
      </div>
    </div>

    <div style="background:#fff;border-radius:10px;border:1px solid #e0e0e0;padding:20px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;color:#222">Chọn Combo / Sản phẩm</h3>
      <div class="combo-list">
        <?php foreach ($combos as $c): ?>
        <div class="combo-item">
          <div class="combo-emoji"><?= $c['emoji'] ?></div>
          <div class="combo-info">
            <div class="combo-name"><?= htmlspecialchars($c['name']) ?></div>
            <div class="combo-desc"><?= htmlspecialchars($c['desc']) ?></div>
            <div class="combo-price"><?= number_format($c['price']) ?> đ</div>
          </div>
          <div class="combo-qty">
            <button class="qty-btn" onclick="changeQty(<?= $c['id'] ?>,<?= $c['price'] ?>,-1)">−</button>
            <span class="qty-num" id="qty-<?= $c['id'] ?>">0</span>
            <button class="qty-btn" onclick="changeQty(<?= $c['id'] ?>,<?= $c['price'] ?>,1)">+</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="order-panel">
    <div class="order-panel-top"></div>
    <div class="order-panel-body">
      <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px">
        <div class="order-poster">
          <?php if ($show['poster_url']): ?><img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($show['poster_url']) ?>" alt=""><?php else: ?>🎬<?php endif; ?>
        </div>
        <div>
          <div class="order-movie-title"><?= htmlspecialchars($show['movie_title']) ?></div>
          <div class="order-meta"><?= htmlspecialchars($show['room_name']) ?> · <?= date('H:i d/m',strtotime($show['start_time'])) ?></div>
        </div>
      </div>
      <hr class="order-divider">
      <!-- Ghế -->
      <div style="font-size:11px;font-weight:700;color:#888;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Ghế đã chọn</div>
      <div id="seatListPanel" style="font-size:12px;color:#444;margin-bottom:8px"><?= htmlspecialchars($_GET['seats_display'] ?? $_GET['seats'] ?? '') ?></div>
      <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
        <span style="color:#888">Tiền ghế</span>
        <span style="font-weight:600" id="seatSubtotal"><?= number_format((int)($_GET['seat_total']??0)) ?> đ</span>
      </div>
      <hr class="order-divider">
      <!-- Combo -->
      <div style="font-size:11px;font-weight:700;color:#888;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Combo đã chọn</div>
      <div id="comboListPanel" style="margin-bottom:6px"><p style="font-size:12px;color:#bbb">Chưa chọn combo nào</p></div>
      <hr class="order-divider">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="order-total-label">Tổng cộng</span>
        <span class="order-total-amount" id="grand-total"><?= number_format((int)($_GET['seat_total']??0)) ?> đ</span>
      </div>
      <button class="btn-back" onclick="_navigatingInternally=true;window.location.href='<?= APP_URL ?>/booking.php?show_id=<?= $showId ?>&step=2'">← Quay lại</button>
      <button class="btn-continue" id="btnStep3" onclick="goToPayment()">Tiếp tục</button>
    </div>
  </div>

<?php /* ═══════ STEP 4: Thanh toán ═══════ */ elseif ($step === 4): ?>

  <div>
    <div class="payment-card">
      <h3>Thông tin đặt vé</h3>
      <div class="order-info-row"><span class="order-info-label">Phim</span><span class="order-info-value"><?= htmlspecialchars($show['movie_title']) ?></span></div>
      <div class="order-info-row"><span class="order-info-label">Rạp</span><span class="order-info-value"><?= htmlspecialchars($show['room_name']) ?></span></div>
      <div class="order-info-row"><span class="order-info-label">Suất chiếu</span><span class="order-info-value"><?= date('H:i – l, d/m/Y',strtotime($show['start_time'])) ?></span></div>
      <div class="order-info-row"><span class="order-info-label">Ghế</span><span class="order-info-value"><?= htmlspecialchars($_GET['seats']??'') ?></span></div>
      <?php if (!empty($_GET['combos_display'])): ?>
      <div class="order-info-row"><span class="order-info-label">Combo</span><span class="order-info-value"><?= htmlspecialchars($_GET['combos_display']) ?></span></div>
      <?php endif; ?>
      <div class="order-info-row" style="margin-top:6px">
        <span class="order-info-label" style="font-weight:700;color:#222">Tổng tiền</span>
        <span style="font-weight:800;font-size:16px;color:#e8192c"><?= number_format((int)($_GET['grand_total']??0)) ?> đ</span>
      </div>
    </div>

    <div class="payment-card">
      <h3>Xác nhận thanh toán</h3>
      <p style="font-size:13px;color:#888;margin-bottom:20px">Bấm nút bên dưới để hoàn tất đặt vé. Mã QR sẽ được gửi về email và hiển thị ngay sau khi xác nhận.</p>
      <div style="background:#f9f9f9;border-radius:10px;padding:20px;text-align:center;border:1px solid #eee;margin-bottom:16px">
        <div style="font-size:40px;margin-bottom:8px">🏦</div>
        <div style="font-size:22px;font-weight:800;color:#e8192c;margin-bottom:4px"><?= number_format((int)($_GET['grand_total']??0)) ?> đ</div>
        <div style="font-size:12px;color:#888">Ngân hàng: MiniCine Bank · STK: 1234567890</div>
        <div style="font-size:12px;color:#888">Nội dung: <span style="color:#e8192c;font-weight:600" id="payRef">MINICINE-<?= time() ?></span></div>
      </div>
      <button class="btn-continue" id="confirmBtn" onclick="confirmPayment()">✓ Xác nhận thanh toán</button>
    </div>
  </div>

  <div class="order-panel">
    <div class="order-panel-top"></div>
    <div class="order-panel-body">
      <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px">
        <div class="order-poster">
          <?php if ($show['poster_url']): ?><img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($show['poster_url']) ?>" alt=""><?php else: ?>🎬<?php endif; ?>
        </div>
        <div>
          <div class="order-movie-title"><?= htmlspecialchars($show['movie_title']) ?></div>
          <div class="order-meta"><?= htmlspecialchars($show['room_name']) ?></div>
          <div class="order-meta"><?= date('H:i – l, d/m/Y',strtotime($show['start_time'])) ?></div>
        </div>
      </div>
      <hr class="order-divider">
      <div style="font-size:12px;color:#888;margin-bottom:4px">Ghế: <strong style="color:#222"><?= htmlspecialchars($_GET['seats']??'') ?></strong></div>
      <?php if (!empty($_GET['combos_display'])): ?>
      <div style="font-size:12px;color:#888;margin-bottom:4px">Combo: <strong style="color:#222"><?= htmlspecialchars($_GET['combos_display']) ?></strong></div>
      <?php endif; ?>
      <hr class="order-divider">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="order-total-label">Tổng cộng</span>
        <span class="order-total-amount"><?= number_format((int)($_GET['grand_total']??0)) ?> đ</span>
      </div>
      <a href="<?= APP_URL ?>/booking.php?show_id=<?= $showId ?>&step=3&<?= http_build_query(['seats'=>$_GET['seats']??'','seat_total'=>$_GET['seat_total']??0,'seat_ids'=>$_GET['seat_ids']??'','points_used'=>$_GET['points_used']??0]) ?>" class="btn-back">← Quay lại</a>
    </div>
  </div>

<?php endif; ?>

</div><!-- .booking-wrap -->
</div><!-- .booking-page -->

<script>
const BASE_URL = '<?= APP_URL ?>';
function toggleAcc(id){ document.getElementById(id).classList.toggle('open'); }

<?php if ($step === 1): ?>
// ════ STEP 1 ════
let selectedShowId = null;
function selectMovie(movieId, title, posterUrl, ageRating) {
  document.querySelectorAll('.movie-pick-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('mpcard-'+movieId).classList.add('selected');
  document.getElementById('acc-movie-label').textContent='Chọn phim – '+title;
  document.getElementById('panelTitle').textContent=title;
  if(posterUrl) document.getElementById('panelPoster').innerHTML=`<img src="${BASE_URL}/uploads/posters/${posterUrl}" alt="">`;
  document.getElementById('panelMeta').textContent='Vui lòng chọn suất chiếu';
  document.getElementById('acc-movie').classList.remove('open');
  document.getElementById('acc-show').classList.add('open');
  document.getElementById('acc-show-label').textContent='Chọn suất';
  selectedShowId=null; document.getElementById('btnContinue').disabled=true;
  loadShows(movieId);
}
async function loadShows(movieId){
  const body=document.getElementById('showBody');
  body.innerHTML='<div style="color:#aaa;font-size:13px;padding:8px 0">⏳ Đang tải...</div>';
  try{
    const r=await fetch(`${BASE_URL}/api/get_shows.php?movie_id=${movieId}`);
    const data=await r.json();
    if(!data.ok||!data.shows.length){body.innerHTML='<p style="color:#aaa;font-size:13px">Không có suất chiếu nào.</p>';return;}
    const byDate={};
    data.shows.forEach(s=>{if(!byDate[s.date])byDate[s.date]=[];byDate[s.date].push(s);});
    const dates=Object.keys(byDate);
    let html='<div class="date-tabs" id="dateTabs">';
    dates.forEach((d,i)=>{html+=`<button class="date-tab ${i===0?'active':''}" onclick="filterDate('${d}',this)">${byDate[d][0].date_label.split(',')[0]}<br><span style="font-size:11px">${d.slice(5)}</span></button>`;});
    html+='</div>';
    dates.forEach((d,i)=>{
      html+=`<div class="showtime-group date-group" data-date="${d}" style="${i>0?'display:none':''}">
        <div class="showtime-group-title">📅 ${byDate[d][0].date_label}</div><div class="showtime-btns">`;
      byDate[d].forEach(s=>{html+=`<button class="showtime-btn" id="sbt-${s.id}" onclick="selectShow(${s.id},'${s.time}','${s.room}','${s.date_label}',${s.flash},this)"><span>${s.time}</span><span class="st-room">${s.room}</span>${s.flash?`<span class="st-flash">⚡-${s.flash}%</span>`:''}</button>`;});
      html+='</div></div>';
    });
    body.innerHTML=html;
  }catch(e){body.innerHTML='<p style="color:#e8192c;font-size:13px">Lỗi tải dữ liệu.</p>';}
}
function filterDate(date,btn){
  document.querySelectorAll('.date-tab').forEach(b=>b.classList.remove('active'));btn.classList.add('active');
  document.querySelectorAll('.date-group').forEach(g=>{g.style.display=g.dataset.date===date?'':'none';});
}
function selectShow(showId,time,room,dateLabel,flashDisc,btn){
  selectedShowId=showId;
  document.querySelectorAll('.showtime-btn').forEach(b=>b.classList.remove('picked'));btn.classList.add('picked');
  document.getElementById('acc-show-label').textContent=`Chọn suất – ${time} · ${room}`;
  let meta=`${room} · ${time} · ${dateLabel}`;
  if(flashDisc) meta+=` <span style="background:#e8192c;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px">⚡-${flashDisc}%</span>`;
  document.getElementById('panelMeta').innerHTML=meta;
  const btn2=document.getElementById('btnContinue');
  btn2.disabled=false; btn2.onclick=()=>{window.location.href=`${BASE_URL}/booking.php?show_id=${showId}&step=2`;};
}

<?php elseif ($step === 2):
  $ar = $show['age_rating'] ?? 'P';
?>
// ════ STEP 2 ════
const SHOW_ID=<?= $showId ?>;const FLASH_DISC=<?= $flash?$flash['discount_pct']:0 ?>;
const PRICES=<?= json_encode($prices) ?>;const MY_ID=<?= $user['id'] ?>;
const USER_POINTS=<?= (int)$user['points'] ?>;const POINTS_RATE=100;const POINTS_VALUE=10000;
let selected={},holdTimer=null,holdSeconds=0,pollInterval=null,pointsUsed=0,currentFlashDisc=FLASH_DISC;

async function loadSeats(){const res=await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);const data=await res.json();renderSeats(data);}
function renderSeats(seats){
  const byRow={};seats.forEach(s=>{(byRow[s.row]=byRow[s.row]||[]).push(s);});
  let evicted=[];
  seats.forEach(s=>{if(!selected[s.id])return;const t=(s.status==='held'&&parseInt(s.held_by)!==MY_ID)||s.status==='booked';if(t){evicted.push(`${s.row}${s.number}`);delete selected[s.id];}});
  if(evicted.length)showMsg(`⚠️ Ghế <strong>${evicted.join(', ')}</strong> vừa bị người khác đặt.`,'warning');
  let html='';
  for(const row of ['A','B','C','D','E','F','G']){
    if(!byRow[row])continue;html+=`<div class="seat-row"><div class="seat-row-label">${row}</div>`;
    byRow[row].forEach(s=>{
      let cls='seat';const label=s.type==='couple'?`${s.row}${s.number*2-1}-${s.row}${s.number*2}`:`${s.row}${s.number}`;
      if(s.type==='couple')cls+=' couple';
      const isMine=!!selected[s.id],isBooked=s.status==='booked',isHeldByOther=s.status==='held'&&parseInt(s.held_by)!==MY_ID,isHeldByMe=s.status==='held'&&parseInt(s.held_by)===MY_ID;
      if(isHeldByOther||isBooked){cls+=isBooked?' booked':' held';}
      else if(isMine||isHeldByMe){cls+=' selected';}
      else if(s.type==='vip'){cls+=' available vip-seat';}
      else if(s.type==='couple'){cls+=' available couple-seat';}
      else{cls+=' available';}
      const canClick=!isBooked&&!isHeldByOther;
      const click=canClick?`onclick="toggleSeat(${JSON.stringify(s).replace(/"/g,"'")})"`:'' ;
      html+=`<div class="${cls}" ${click} title="${label}">${label}</div>`;
    });html+='</div>';
  }
  document.getElementById('seat-map').innerHTML=html;updateSummary();
}
function showMsg(text,type){const el=document.getElementById('suggest-msg');el.innerHTML=`<div class="alert alert-${type}" style="margin-bottom:10px">${text}</div>`;setTimeout(()=>el.innerHTML='',4000);}
async function toggleSeat(s){
  if(selected[s.id]){delete selected[s.id];renderOptimistic();try{await fetch(`${BASE_URL}/api/release.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:[s.id]})});}catch(e){}await loadSeats();return;}
  selected[s.id]=s;renderOptimistic();
  const res=await fetch(`${BASE_URL}/api/hold.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:[s.id]})});
  const data=await res.json();
  if(!data.ok){delete selected[s.id];showMsg(`⚠️ Ghế ${s.row}${s.number} vừa bị người khác chọn.`,'warning');}else{startCountdown();}
  await loadSeats();
}
function renderOptimistic(){document.querySelectorAll('.seat').forEach(el=>{const t=el.getAttribute('title');const isSel=Object.values(selected).some(s=>{const l=s.type==='couple'?`${s.row}${s.number*2-1}-${s.row}${s.number*2}`:`${s.row}${s.number}`;return l===t;});if(isSel){el.className=el.className.replace(/\bavailable\b|\bvip-seat\b|\bcouple-seat\b|\bheld\b/g,'').trim();if(!el.className.includes('selected'))el.className+=' selected';}});updateSummary();}
async function clearSelection(){const ids=Object.keys(selected).map(Number);selected={};renderOptimistic();if(ids.length){try{await fetch(`${BASE_URL}/api/release.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:ids})});}catch(e){}}await loadSeats();document.getElementById('countdown-wrap').style.display='none';clearInterval(holdTimer);}
function updateSummary(){
  const items=Object.values(selected);document.getElementById('points-section').style.display=items.length?'block':'none';
  if(!items.length){document.getElementById('selected-list').innerHTML='<p style="color:#bbb;font-size:12px;text-align:center;padding:6px 0">Chưa chọn ghế nào</p>';document.getElementById('total-price').textContent='0 đ';document.getElementById('pay-btn').disabled=true;document.getElementById('points-row').style.display='none';pointsUsed=0;if(document.getElementById('points-input'))document.getElementById('points-input').value=0;return;}
  let subtotal=0,html='';
  items.forEach(s=>{const base=PRICES[s.type]||0;const disc=(currentFlashDisc>0&&s.type!=='couple')?currentFlashDisc:0;const price=Math.round(base*(1-disc/100));subtotal+=price;const label=s.type==='couple'?`${s.row}${s.number*2-1}-${s.row}${s.number*2}`:`${s.row}${s.number}`;html+=`<div class="sel-seat-row"><span>${label} <small style="color:#aaa">(${s.type})</small></span><span style="font-weight:600">${disc?`<s style="color:#ccc;font-size:10px">${base.toLocaleString()}đ</s> `:''}${price.toLocaleString()}đ</span></div>`;});
  document.getElementById('selected-list').innerHTML=html;
  const pd=Math.floor(pointsUsed/POINTS_RATE)*POINTS_VALUE;const ft=Math.max(0,subtotal-pd);
  if(pd>0){document.getElementById('points-row').style.display='flex';document.getElementById('points-discount-amount').textContent='-'+pd.toLocaleString()+'đ';}else{document.getElementById('points-row').style.display='none';}
  document.getElementById('total-price').textContent=ft.toLocaleString()+' đ';document.getElementById('pay-btn').disabled=false;
}
function applyPoints(){const input=document.getElementById('points-input');let pts=Math.floor((parseInt(input.value)||0)/100)*100;const sub=Object.values(selected).reduce((s,x)=>{const b=PRICES[x.type]||0;const d=(currentFlashDisc>0&&x.type!=='couple')?currentFlashDisc:0;return s+Math.round(b*(1-d/100));},0);pts=Math.min(pts,USER_POINTS,Math.floor(sub/POINTS_VALUE)*POINTS_RATE);pts=Math.max(0,pts);input.value=pts;pointsUsed=pts;const disc=Math.floor(pts/POINTS_RATE)*POINTS_VALUE;document.getElementById('points-discount-display').textContent=pts>0?`✓ Giảm ${disc.toLocaleString()}đ (dùng ${pts.toLocaleString()} điểm)`:'';updateSummary();}
function useAllPoints(){const sub=Object.values(selected).reduce((s,x)=>{const b=PRICES[x.type]||0;const d=(currentFlashDisc>0&&x.type!=='couple')?currentFlashDisc:0;return s+Math.round(b*(1-d/100));},0);const pts=Math.min(USER_POINTS,Math.floor(sub/POINTS_VALUE)*POINTS_RATE);document.getElementById('points-input').value=pts;pointsUsed=pts;const disc=Math.floor(pts/POINTS_RATE)*POINTS_VALUE;document.getElementById('points-discount-display').textContent=pts>0?`✓ Giảm ${disc.toLocaleString()}đ (dùng ${pts.toLocaleString()} điểm)`:'';updateSummary();}
async function suggestSeats(){const n=parseInt(document.getElementById('suggest-n').value)||2;const res=await fetch(`${BASE_URL}/api/suggest.php?show_id=${SHOW_ID}&n=${n}`);const data=await res.json();if(!data.ok){showMsg(data.msg,'error');return;}if(data.fallback)showMsg(`Không đủ ${data.requested} ghế liền kề, gợi ý ${data.seats.length} ghế.`,'warning');const oldIds=Object.keys(selected).map(Number);selected={};if(oldIds.length){try{await fetch(`${BASE_URL}/api/release.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:oldIds})});}catch(e){}}const holdRes=await fetch(`${BASE_URL}/api/hold.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:data.seats.map(s=>s.id)})});const holdData=await holdRes.json();if(holdData.ok){data.seats.forEach(s=>{selected[s.id]=s;});startCountdown();}else{showMsg('Ghế gợi ý vừa bị người khác chọn.','error');}await loadSeats();}
function startCountdown(){holdSeconds=5*60;const wrap=document.getElementById('countdown-wrap');const box=wrap.querySelector('.countdown');wrap.style.display='block';box.classList.remove('urgent');if(holdTimer)clearInterval(holdTimer);holdTimer=setInterval(()=>{holdSeconds--;const m=Math.floor(holdSeconds/60),s=holdSeconds%60;document.getElementById('countdown-time').textContent=`${m}:${s.toString().padStart(2,'0')}`;if(holdSeconds<=60)box.classList.add('urgent');if(holdSeconds<=0){clearInterval(holdTimer);wrap.style.display='none';selected={};loadSeats();alert('⏱ Hết thời gian giữ ghế. Vui lòng chọn lại.');}},1000);}

async function onContinueClick(){
  const seatIds=Object.keys(selected).map(Number);if(!seatIds.length)return;
  // Verify still held
  const checkRes=await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);
  const seats=await checkRes.json();
  const stillMine=seatIds.every(id=>{const s=seats.find(x=>x.id==id);return s&&s.status==='held'&&parseInt(s.held_by)===MY_ID;});
  if(!stillMine){alert('⚠️ Một số ghế đã hết hạn giữ. Vui lòng chọn lại.');selected={};loadSeats();return;}
  // Build seat labels
  const labels=Object.values(selected).map(s=>s.type==='couple'?`${s.row}${s.number*2-1}-${s.row}${s.number*2}`:`${s.row}${s.number}`).join(', ');
  const pd=Math.floor(pointsUsed/POINTS_RATE)*POINTS_VALUE;
  const seatTotal=Object.values(selected).reduce((sum,s)=>{const b=PRICES[s.type]||0;const d=(currentFlashDisc>0&&s.type!=='couple')?currentFlashDisc:0;return sum+Math.round(b*(1-d/100));},0);
  const finalSeatTotal=Math.max(0,seatTotal-pd);
  const params=new URLSearchParams({show_id:SHOW_ID,step:3,seats:labels,seat_ids:seatIds.join(','),seat_total:finalSeatTotal,points_used:pointsUsed});
  _navigatingInternally = true;
  window.location.href=`${BASE_URL}/booking.php?${params}`;
}

async function checkFlashSale(){try{const res=await fetch(`${BASE_URL}/api/flash_status.php?show_id=${SHOW_ID}&_=${Date.now()}`);const data=await res.json();const banner=document.getElementById('flash-banner');if(data.active){banner.style.display='flex';document.getElementById('flash-pct').textContent=data.discount_pct+'%';if(currentFlashDisc!==data.discount_pct){currentFlashDisc=data.discount_pct;updateSummary();}}else{banner.style.display='none';if(currentFlashDisc!==0){currentFlashDisc=0;updateSummary();}}}catch(e){}}
function releaseAllHeld(){const ids=Object.keys(selected).map(Number);if(!ids.length)return;navigator.sendBeacon(`${BASE_URL}/api/release.php`,new Blob([JSON.stringify({show_id:SHOW_ID,seat_ids:ids})],{type:'application/json'}));}
restoreHeldSeats();checkFlashSale();pollInterval=setInterval(loadSeats,3000);setInterval(checkFlashSale,10000);
// Release ghế chỉ khi thoát hẳn khỏi booking flow
let _navigatingInternally = false;

// Đánh dấu mọi navigation nội bộ trong booking
document.addEventListener('click', function(e) {
  const a = e.target.closest('a[href]');
  if (a && a.href.includes('booking.php')) _navigatingInternally = true;
}, true);

// Đánh dấu khi submit form nội bộ
window._goInternal = function(url) {
  _navigatingInternally = true;
  window.location.href = url;
};

window.addEventListener('beforeunload', function() {
  if (!_navigatingInternally) releaseAllHeld();
});
window.addEventListener('pagehide', function(e) {
  if (!_navigatingInternally) releaseAllHeld();
});

// Khi load trang step 2, restore lại ghế đang held bởi user này
// (để xử lý trường hợp quay lại từ step khác)
async function restoreHeldSeats() {
  const res  = await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);
  const seats = await res.json();
  seats.forEach(s => {
    if (s.status === 'held' && parseInt(s.held_by) === MY_ID) {
      selected[s.id] = s;
    }
  });
  renderSeats(seats);
  if (Object.keys(selected).length > 0) startCountdown();
}

<?php elseif ($step === 3): ?>
// ════ STEP 3: Combo ════
const SHOW_ID=<?= $showId ?>;
const SEAT_TOTAL=<?= (int)($_GET['seat_total']??0) ?>;
const COMBOS=<?= json_encode(array_column($combos,'price','id')) ?>;
let qtyMap={};
function changeQty(id,price,delta){
  qtyMap[id]=(qtyMap[id]||0)+delta;if(qtyMap[id]<0)qtyMap[id]=0;
  document.getElementById('qty-'+id).textContent=qtyMap[id];
  updateComboPanel();
}
function updateComboPanel(){
  let comboTotal=0,html='',comboDisplay=[];
  <?php foreach ($combos as $c): ?>
  if(qtyMap[<?=$c['id']?>]>0){comboTotal+=qtyMap[<?=$c['id']?>]*<?=$c['price']?>;html+=`<div class="combo-panel-row"><span><?= htmlspecialchars($c['name']) ?> ×${qtyMap[<?=$c['id']?>]}</span><span style="font-weight:600">${(qtyMap[<?=$c['id']?>]*<?=$c['price']?>).toLocaleString()}đ</span></div>`;comboDisplay.push(`<?= htmlspecialchars($c['name']) ?> ×${qtyMap[<?=$c['id']?>]}`);}
  <?php endforeach; ?>
  document.getElementById('comboListPanel').innerHTML=html||'<p style="font-size:12px;color:#bbb">Chưa chọn combo nào</p>';
  document.getElementById('grand-total').textContent=(SEAT_TOTAL+comboTotal).toLocaleString()+' đ';
  window._comboTotal=comboTotal;window._comboDisplay=comboDisplay.join(', ');
}
function goToPayment(){
  const comboTotal=window._comboTotal||0;
  const comboDisplay=window._comboDisplay||'';
  const params=new URLSearchParams({
    show_id:SHOW_ID,step:4,
    seats:'<?= htmlspecialchars($_GET['seats']??'') ?>',
    seat_ids:'<?= htmlspecialchars($_GET['seat_ids']??'') ?>',
    seat_total:SEAT_TOTAL,
    points_used:'<?= (int)($_GET['points_used']??0) ?>',
    grand_total:SEAT_TOTAL+comboTotal,
    combos_display:comboDisplay
  });
  _navigatingInternally = true;
  window.location.href=`${BASE_URL}/booking.php?${params}`;
}
updateComboPanel();

<?php elseif ($step === 4): ?>
// ════ STEP 4: Thanh toán ════
const SHOW_ID=<?= $showId ?>;
const SEAT_IDS='<?= htmlspecialchars($_GET['seat_ids']??'') ?>'.split(',').map(Number).filter(Boolean);
const POINTS_USED=<?= (int)($_GET['points_used']??0) ?>;

async function confirmPayment(){
  const btn=document.getElementById('confirmBtn');
  btn.disabled=true;btn.textContent='⏳ Đang xác nhận...';
  try{
    const res=await fetch(`${BASE_URL}/api/confirm.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({show_id:SHOW_ID,seat_ids:SEAT_IDS,points_used:POINTS_USED})});
    const data=await res.json();
    if(data.ok){window.location.href=`${BASE_URL}/booking_success.php?booking_id=${data.booking_id}`;}
    else{btn.disabled=false;btn.textContent='✓ Xác nhận thanh toán';alert('❌ '+data.msg);}
  }catch(e){btn.disabled=false;btn.textContent='✓ Xác nhận thanh toán';alert('Lỗi kết nối, thử lại nhé.');}
}
<?php endif; ?>
</script>

<?php renderFooter(); ?>
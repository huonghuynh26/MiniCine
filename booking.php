<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/booking.php';

requireLogin();
$user = currentUser();

$showId = (int)($_GET['show_id'] ?? 0);
$db     = db();

$stmt = $db->prepare("
    SELECT s.*, m.title as movie_title, m.duration_min, m.poster_url,
           r.name as room_name, r.floor
    FROM tblShows s
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms  r ON r.id = s.room_id
    WHERE s.id=? AND s.start_time > DATE_SUB(NOW(), INTERVAL 3 HOUR)
");
$stmt->bind_param('i', $showId);
$stmt->execute();
$show = $stmt->get_result()->fetch_assoc();
if (!$show) {
    header('Location: ' . APP_URL);
    exit;
}

// Prices
$prices = [];
$res = $db->query("SELECT seat_type, price FROM tblPrices");
while ($r = $res->fetch_assoc()) $prices[$r['seat_type']] = (int)$r['price'];

// Flash sale
$flash = getFlashSale($showId);

renderHead('Đặt vé - ' . $show['movie_title']);
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
<?php renderNav(); ?>

<div class="section">
  <!-- Steps -->
  <div class="steps">
    <div class="step done"><span class="step-num">✓</span>Chọn phim</div>
    <div class="step-sep"></div>
    <div class="step active"><span class="step-num">2</span>Chọn ghế</div>
    <div class="step-sep"></div>
    <div class="step"><span class="step-num">3</span>Thanh toán</div>
  </div>

  <!-- Movie info -->
  <div class="card mb-3">
    <div class="card-body" style="display:flex;gap:20px;align-items:center">
      <div style="font-size:40px">🎬</div>
      <div>
        <div style="font-weight:700;font-size:18px"><?= htmlspecialchars($show['movie_title']) ?></div>
        <div class="text-muted mt-1">
          🕐 <?= date('H:i d/m/Y', strtotime($show['start_time'])) ?>
          · <?= htmlspecialchars($show['room_name']) ?>
          · Tầng <?= $show['floor'] ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Flash sale -->
  <?php if ($flash): ?>
  <div class="flash-sale-banner mb-3">
    <div class="flash-icon">⚡</div>
    <div>
      <h3>Flash Sale đang áp dụng – Giảm <?= $flash['discount_pct'] ?>%!</h3>
      <p>Áp dụng cho ghế Standard và VIP. Ghế Couple không áp dụng.</p>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:32px" id="booking-layout">

    <!-- LEFT: Seat Map -->
    <div>
      <!-- Auto suggest -->
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap">
        <span style="font-size:14px;color:#888">Gợi ý ghế liền kề:</span>
        <input type="number" id="suggest-n" min="1" max="8" value="2"
               class="form-control" style="width:72px">
        <button class="btn btn-outline btn-sm" onclick="suggestSeats()">🎯 Tìm ghế</button>
        <button class="btn btn-ghost btn-sm" onclick="clearSelection()">✕ Xoá chọn</button>
      </div>
      <div id="suggest-msg"></div>

      <!-- Countdown -->
      <div id="countdown-wrap" style="display:none;margin-bottom:16px">
        <div class="countdown" id="countdown">⏱ Ghế được giữ trong: <strong id="countdown-time">5:00</strong></div>
      </div>

      <!-- Screen -->
      <div class="screen-curve">
        <svg viewBox="0 0 400 30" xmlns="http://www.w3.org/2000/svg">
          <path d="M20 28 Q200 4 380 28" fill="none" stroke="#555" stroke-width="3" stroke-linecap="round"/>
          <text x="200" y="20" text-anchor="middle" fill="#555" font-size="10" font-family="Inter">MÀN HÌNH</text>
        </svg>
      </div>

      <!-- Seat map -->
      <div class="seat-map-wrap" id="seat-map">
        <div class="spinner"></div>
      </div>

      <!-- Legend -->
      <div class="seat-legend">
        <div class="legend-item"><div class="legend-box" style="background:#1a2a1a;border-color:#2a4a2a"></div> Trống</div>
        <div class="legend-item"><div class="legend-box" style="background:#2a2a0a;border-color:#4a4a1a"></div> VIP</div>
        <div class="legend-item"><div class="legend-box" style="background:#1a1a3a;border-color:#2a2a5a"></div> Couple</div>
        <div class="legend-item"><div class="legend-box" style="background:#2a1a0a;border-color:#4a2a0a"></div> Đang giữ</div>
        <div class="legend-item"><div class="legend-box" style="background:#1a0a0a;border-color:#2a0a0a"></div> Đã đặt</div>
        <div class="legend-item"><div class="legend-box" style="background:#e50914;border-color:#ff0a16"></div> Đang chọn</div>
      </div>
    </div>

    <!-- RIGHT: Summary -->
    <div>
      <div class="price-summary" id="price-summary">
        <div style="font-weight:700;font-size:16px;margin-bottom:16px">Tóm tắt đặt vé</div>
        <div id="selected-list">
          <p class="text-muted" style="font-size:13px">Chưa chọn ghế nào</p>
        </div>
        <hr class="divider">
        <div class="price-row total">
          <span class="label">Tổng cộng</span>
          <span class="amount" id="total-price">0đ</span>
        </div>
        <button class="btn btn-primary mt-2" style="width:100%" id="pay-btn" disabled
                onclick="proceedPayment()">
          Tiếp tục thanh toán →
        </button>
      </div>
      <p class="text-muted mt-1" style="font-size:12px">
        ⏱ Ghế được giữ 5 phút sau khi chọn
      </p>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);
     z-index:999;display:none;align-items:center;justify-content:center;padding:20px">
  <div style="background:#141414;border:1px solid #333;border-radius:16px;
              padding:32px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto">
    <h2 style="margin-bottom:4px">Thanh toán</h2>
    <p class="text-muted mb-3" style="font-size:14px">Mô phỏng chuyển khoản online</p>
    <div id="payment-qr" style="text-align:center;padding:24px;background:#1a1a1a;border-radius:8px;margin-bottom:16px">
      <div style="font-size:48px;margin-bottom:8px">🏦</div>
      <p style="font-weight:700;font-size:18px" id="pay-amount-display"></p>
      <p class="text-muted" style="font-size:13px">Ngân hàng: MiniCine Bank<br>STK: 1234567890<br>Nội dung: <span id="pay-ref"></span></p>
    </div>
    <button class="btn btn-success" style="width:100%" onclick="confirmPayment()">
      ✓ Xác nhận đã thanh toán
    </button>
    <button class="btn btn-ghost mt-1" style="width:100%" onclick="closePayment()">Huỷ</button>
  </div>
</div>

<script>
const SHOW_ID    = <?= $showId ?>;
const BASE_URL   = '<?= APP_URL ?>';
const FLASH_DISC = <?= $flash ? $flash['discount_pct'] : 0 ?>;
const PRICES     = <?= json_encode($prices) ?>;
const MY_ID      = <?= $user['id'] ?>;

let selected     = {};   // seatId → {id,row,number,type}
let holdTimer    = null;
let holdSeconds  = 0;
let pollInterval = null;

// ─── Load seat map ────────────────────────────────────────────────
async function loadSeats() {
  const res  = await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);
  const data = await res.json();
  renderSeats(data);
}

function renderSeats(seats) {
  const byRow = {};
  seats.forEach(s => { (byRow[s.row] = byRow[s.row] || []).push(s); });

  let html = '';
  for (const row of ['A','B','C','D','E','F','G']) {
    if (!byRow[row]) continue;
    html += `<div class="seat-row"><div class="seat-row-label">${row}</div>`;
    byRow[row].forEach(s => {
      let cls  = 'seat';
      let label= `${s.row}${s.number}`;
      const isMine = selected[s.id];

      if (s.type === 'couple') {
        cls += ' couple';
        label = `${s.row}${s.number * 2 - 1}-${s.row}${s.number * 2}`;
      }

      if (isMine) {
        cls += ' selected';
      } else if (s.status === 'booked') {
        cls += ' booked';
      } else if (s.status === 'held' && s.held_by != MY_ID) {
        cls += ' held';
      } else if (s.type === 'vip') {
        cls += ' available vip-seat';
      } else if (s.type === 'couple') {
        cls += ' available couple-seat';
      } else {
        cls += ' available';
      }

      const clickable = !isMine && s.status !== 'booked' && !(s.status === 'held' && s.held_by != MY_ID);
      const click = clickable ? `onclick="toggleSeat(${JSON.stringify(s).replace(/"/g,"'")})"` : '';
      html += `<div class="${cls}" ${click} title="${label}">${label}</div>`;
    });
    html += '</div>';
  }
  document.getElementById('seat-map').innerHTML = html;
  updateSummary();
}

// ─── Toggle seat ──────────────────────────────────────────────────
function toggleSeat(s) {
  if (selected[s.id]) {
    delete selected[s.id];
  } else {
    selected[s.id] = s;
  }
  loadSeats();
  updateSummary();
}

function clearSelection() { selected = {}; loadSeats(); }

// ─── Summary ──────────────────────────────────────────────────────
function updateSummary() {
  const items = Object.values(selected);
  if (!items.length) {
    document.getElementById('selected-list').innerHTML = '<p class="text-muted" style="font-size:13px">Chưa chọn ghế nào</p>';
    document.getElementById('total-price').textContent = '0đ';
    document.getElementById('pay-btn').disabled = true;
    return;
  }

  let total = 0;
  let html  = '';
  items.forEach(s => {
    const base  = PRICES[s.type] || 0;
    const disc  = (FLASH_DISC > 0 && s.type !== 'couple') ? FLASH_DISC : 0;
    const price = Math.round(base * (1 - disc / 100));
    total += price;
    const label = s.type === 'couple'
      ? `${s.row}${s.number*2-1}-${s.row}${s.number*2}`
      : `${s.row}${s.number}`;
    html += `<div class="price-row">
      <span class="label">${label} <em style="font-size:11px;color:#555">(${s.type})</em></span>
      <span>
        ${disc ? `<span class="price-original">${base.toLocaleString()}đ</span> ` : ''}
        ${price.toLocaleString()}đ
        ${disc ? `<span class="price-discount">-${disc}%</span>` : ''}
      </span>
    </div>`;
  });

  document.getElementById('selected-list').innerHTML = html;
  document.getElementById('total-price').textContent = total.toLocaleString() + 'đ';
  document.getElementById('pay-btn').disabled = false;
}

// ─── Suggest ──────────────────────────────────────────────────────
async function suggestSeats() {
  const n   = parseInt(document.getElementById('suggest-n').value) || 2;
  const res = await fetch(`${BASE_URL}/api/suggest.php?show_id=${SHOW_ID}&n=${n}`);
  const data= await res.json();
  const msg = document.getElementById('suggest-msg');

  if (!data.ok) {
    msg.innerHTML = `<div class="alert alert-error">${data.msg}</div>`;
    return;
  }
  if (data.fallback) {
    msg.innerHTML = `<div class="alert alert-warning">Không đủ ${data.requested} ghế liền kề, gợi ý ${data.seats.length} ghế.</div>`;
  } else {
    msg.innerHTML = '';
  }
  selected = {};
  data.seats.forEach(s => { selected[s.id] = s; });
  loadSeats();
}

// ─── Hold & Payment ───────────────────────────────────────────────
async function proceedPayment() {
  const seatIds = Object.keys(selected).map(Number);
  if (!seatIds.length) return;

  document.getElementById('pay-btn').disabled = true;
  document.getElementById('pay-btn').textContent = '⏳ Đang giữ ghế...';

  const res  = await fetch(`${BASE_URL}/api/hold.php`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ show_id: SHOW_ID, seat_ids: seatIds })
  });
  const data = await res.json();

  if (!data.ok) {
    document.getElementById('pay-btn').disabled = false;
    document.getElementById('pay-btn').textContent = 'Tiếp tục thanh toán →';
    alert('❌ ' + data.msg + '\nVui lòng chọn lại ghế.');
    selected = {};
    loadSeats();
    return;
  }

  // Show countdown
  holdSeconds = 5 * 60;
  document.getElementById('countdown-wrap').style.display = 'block';
  if (holdTimer) clearInterval(holdTimer);
  holdTimer = setInterval(() => {
    holdSeconds--;
    const m = Math.floor(holdSeconds / 60);
    const s = holdSeconds % 60;
    document.getElementById('countdown-time').textContent = `${m}:${s.toString().padStart(2,'0')}`;
    const el = document.getElementById('countdown');
    if (holdSeconds <= 60) el.classList.add('urgent');
    if (holdSeconds <= 0) {
      clearInterval(holdTimer);
      closePayment();
      alert('⏱ Hết thời gian giữ ghế. Vui lòng chọn lại.');
      selected = {};
      loadSeats();
    }
  }, 1000);

  // Open payment modal
  const total = Object.values(selected).reduce((sum, s) => {
    const base  = PRICES[s.type] || 0;
    const disc  = (FLASH_DISC > 0 && s.type !== 'couple') ? FLASH_DISC : 0;
    return sum + Math.round(base * (1 - disc / 100));
  }, 0);
  document.getElementById('pay-amount-display').textContent = total.toLocaleString() + 'đ';
  document.getElementById('pay-ref').textContent = 'MINICINE-' + Date.now();
  const modal = document.getElementById('payment-modal');
  modal.style.display = 'flex';
  document.getElementById('pay-btn').textContent = 'Tiếp tục thanh toán →';
}

async function confirmPayment() {
  const seatIds = Object.keys(selected).map(Number);
  const res = await fetch(`${BASE_URL}/api/confirm.php`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ show_id: SHOW_ID, seat_ids: seatIds })
  });
  const data = await res.json();
  if (data.ok) {
    window.location.href = `${BASE_URL}/booking_success.php?booking_id=${data.booking_id}`;
  } else {
    alert('❌ ' + data.msg);
    closePayment();
    loadSeats();
  }
}

function closePayment() {
  document.getElementById('payment-modal').style.display = 'none';
}

// ─── Polling every 10s ────────────────────────────────────────────
loadSeats();
pollInterval = setInterval(loadSeats, 10000);
</script>

<?php renderFooter(); ?>

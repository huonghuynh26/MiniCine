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
    WHERE s.id=? AND s.end_time > NOW()
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

  <!-- Flash sale banner (dynamic - updated by JS polling) -->
  <div id="flash-banner" class="flash-sale-banner mb-3" style="display:<?= $flash ? 'flex' : 'none' ?>">
    <div class="flash-icon">⚡</div>
    <div>
      <h3>Flash Sale đang áp dụng – Giảm <span id="flash-pct"><?= $flash ? $flash['discount_pct'] : 0 ?>%</span>!</h3>
      <p>Áp dụng cho ghế Standard và VIP. Ghế Couple không áp dụng.</p>
    </div>
  </div>

  <div class="booking-grid" id="booking-layout">

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

        <!-- Đổi điểm thưởng -->
        <div id="points-section" style="display:none;margin-bottom:12px">
          <div style="background:#1a1a00;border:1px solid #333300;border-radius:8px;padding:12px">
            <div style="font-size:13px;font-weight:600;color:#f5c518;margin-bottom:8px">
              ⭐ Dùng điểm thưởng
            </div>
            <div style="font-size:12px;color:#888;margin-bottom:8px">
              Bạn có <strong id="user-points-display" style="color:#f5c518"><?= number_format($user['points']) ?></strong> điểm
              = <strong style="color:#f5c518"><?= number_format(floor($user['points'] / 100) * 10000) ?>đ</strong>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="number" id="points-input" min="0" max="<?= $user['points'] ?>"
                     step="100" value="0" placeholder="Nhập điểm"
                     class="form-control" style="flex:1;padding:8px 10px;font-size:13px"
                     oninput="applyPoints()">
              <button class="btn btn-outline btn-sm" onclick="useAllPoints()" style="white-space:nowrap;flex-shrink:0">
                Dùng tất cả
              </button>
            </div>
            <div id="points-discount-display" style="font-size:12px;color:#4caf50;margin-top:6px"></div>
          </div>
        </div>

        <div class="price-row" id="points-row" style="display:none">
          <span class="label" style="color:#f5c518">⭐ Giảm từ điểm</span>
          <span id="points-discount-amount" style="color:#f5c518">-0đ</span>
        </div>
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
const SHOW_ID      = <?= $showId ?>;
const BASE_URL     = '<?= APP_URL ?>';
const FLASH_DISC   = <?= $flash ? $flash['discount_pct'] : 0 ?>;
const PRICES       = <?= json_encode($prices) ?>;
const MY_ID        = <?= $user['id'] ?>;
const USER_POINTS  = <?= (int)$user['points'] ?>;
const POINTS_RATE  = 100; // 100 điểm = 10000đ
const POINTS_VALUE = 10000;

let selected       = {};
let holdTimer      = null;
let holdSeconds    = 0;
let pollInterval   = null;
let pointsUsed     = 0;  // điểm đang dùng

// ─── Load seat map ────────────────────────────────────────────────
async function loadSeats() {
  const res  = await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);
  const data = await res.json();
  renderSeats(data);
}

function renderSeats(seats) {
  const byRow = {};
  seats.forEach(s => { (byRow[s.row] = byRow[s.row] || []).push(s); });

  // ── Auto-evict ghế bị người khác hold/book trong lúc polling ──
  let evicted = [];
  seats.forEach(s => {
    if (!selected[s.id]) return;
    const takenByOther = (s.status === 'held' && parseInt(s.held_by) !== MY_ID)
                      || s.status === 'booked';
    if (takenByOther) {
      evicted.push(`${s.row}${s.number}`);
      delete selected[s.id];
    }
  });
  if (evicted.length) {
    const msg = document.getElementById('suggest-msg');
    msg.innerHTML = `<div class="alert alert-warning">
      ⚠️ Ghế <strong>${evicted.join(', ')}</strong> vừa bị người khác đặt và đã bị xoá khỏi danh sách của bạn.
    </div>`;
    setTimeout(() => { msg.innerHTML = ''; }, 5000);
  }

  let html = '';
  for (const row of ['A','B','C','D','E','F','G']) {
    if (!byRow[row]) continue;
    html += `<div class="seat-row"><div class="seat-row-label">${row}</div>`;
    byRow[row].forEach(s => {
      let cls   = 'seat';
      let label = s.type === 'couple'
        ? `${s.row}${s.number * 2 - 1}-${s.row}${s.number * 2}`
        : `${s.row}${s.number}`;

      if (s.type === 'couple') cls += ' couple';

      const isMine        = !!selected[s.id];
      const isBooked      = s.status === 'booked';
      const isHeldByOther = s.status === 'held' && parseInt(s.held_by) !== MY_ID;
      const isHeldByMe    = s.status === 'held' && parseInt(s.held_by) === MY_ID;

      if (isHeldByOther || isBooked) {
        // LUÔN ưu tiên: ghế bị chiếm → cam hoặc xám, không click được
        cls += isBooked ? ' booked' : ' held';
      } else if (isMine || isHeldByMe) {
        cls += ' selected';
      } else if (s.type === 'vip') {
        cls += ' available vip-seat';
      } else if (s.type === 'couple') {
        cls += ' available couple-seat';
      } else {
        cls += ' available';
      }

      const canClick = !isBooked && !isHeldByOther;
      const click = canClick
        ? `onclick="toggleSeat(${JSON.stringify(s).replace(/"/g,"'")})"` : '';

      html += `<div class="${cls}" ${click} title="${label}">${label}</div>`;
    });
    html += '</div>';
  }
  document.getElementById('seat-map').innerHTML = html;
  updateSummary();
}

// ─── Toggle seat: click = hold ngay, click lại = release ngay ────
async function toggleSeat(s) {
  const seatId = s.id;

  if (selected[seatId]) {
    // Bỏ chọn → release ngay
    delete selected[seatId];
    renderOptimistic(); // cập nhật UI ngay không đợi server
    try {
      await fetch(`${BASE_URL}/api/release.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ show_id: SHOW_ID, seat_ids: [seatId] })
      });
    } catch(e) {}
    await loadSeats();
    return;
  }

  // Chọn → hold ngay
  selected[seatId] = s;
  renderOptimistic(); // hiện đỏ ngay trước khi API trả về

  const res  = await fetch(`${BASE_URL}/api/hold.php`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ show_id: SHOW_ID, seat_ids: [seatId] })
  });
  const data = await res.json();

  if (!data.ok) {
    // Hold thất bại → bỏ khỏi selected, báo
    delete selected[seatId];
    const msg = document.getElementById('suggest-msg');
    msg.innerHTML = `<div class="alert alert-warning">⚠️ Ghế ${s.row}${s.number} vừa bị người khác chọn.</div>`;
    setTimeout(() => { msg.innerHTML = ''; }, 3000);
  } else {
    // Hold thành công → reset countdown 5 phút
    startCountdown();
  }
  await loadSeats();
}

// Render UI ngay lập tức (optimistic) không đợi server
function renderOptimistic() {
  // Chỉ update màu ghế đang selected mà không fetch server
  document.querySelectorAll('.seat').forEach(el => {
    const title = el.getAttribute('title');
    if (!title) return;
    // tìm seat trong selected theo label
    const isSelected = Object.values(selected).some(s => {
      const lbl = s.type === 'couple'
        ? `${s.row}${s.number * 2 - 1}-${s.row}${s.number * 2}`
        : `${s.row}${s.number}`;
      return lbl === title;
    });
    if (isSelected) {
      el.className = el.className.replace(/\bavailable\b|\bvip-seat\b|\bcouple-seat\b|\bheld\b/g, '').trim();
      if (!el.className.includes('selected')) el.className += ' selected';
    }
  });
  updateSummary();
}

async function clearSelection() {
  const seatIds = Object.keys(selected).map(Number);
  selected = {};
  renderOptimistic();
  if (seatIds.length) {
    try {
      await fetch(`${BASE_URL}/api/release.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ show_id: SHOW_ID, seat_ids: seatIds })
      });
    } catch(e) {}
  }
  await loadSeats();
  // Ẩn countdown nếu đang hiện
  document.getElementById('countdown-wrap').style.display = 'none';
  clearInterval(holdTimer);
}

// ─── Summary ──────────────────────────────────────────────────────
function updateSummary() {
  const items = Object.values(selected);

  // Ẩn/hiện section điểm
  document.getElementById('points-section').style.display = items.length ? 'block' : 'none';

  if (!items.length) {
    document.getElementById('selected-list').innerHTML = '<p class="text-muted" style="font-size:13px">Chưa chọn ghế nào</p>';
    document.getElementById('total-price').textContent = '0đ';
    document.getElementById('pay-btn').disabled = true;
    document.getElementById('points-row').style.display = 'none';
    pointsUsed = 0;
    if (document.getElementById('points-input')) document.getElementById('points-input').value = 0;
    return;
  }

  let subtotal = 0;
  let html = '';
  items.forEach(s => {
    const base  = PRICES[s.type] || 0;
    const disc  = (currentFlashDisc > 0 && s.type !== 'couple') ? currentFlashDisc : 0;
    const price = Math.round(base * (1 - disc / 100));
    subtotal += price;
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

  // Tính giảm từ điểm
  const pointsDiscount = Math.floor(pointsUsed / POINTS_RATE) * POINTS_VALUE;
  const finalTotal = Math.max(0, subtotal - pointsDiscount);

  // Hiện dòng giảm điểm
  if (pointsDiscount > 0) {
    document.getElementById('points-row').style.display = 'flex';
    document.getElementById('points-discount-amount').textContent = '-' + pointsDiscount.toLocaleString() + 'đ';
  } else {
    document.getElementById('points-row').style.display = 'none';
  }

  document.getElementById('total-price').textContent = finalTotal.toLocaleString() + 'đ';
  document.getElementById('pay-btn').disabled = false;
}

// ─── Points functions ─────────────────────────────────────────
function applyPoints() {
  const input = document.getElementById('points-input');
  let pts = parseInt(input.value) || 0;

  // Làm tròn xuống bội số 100
  pts = Math.floor(pts / 100) * 100;

  // Tính subtotal hiện tại
  let subtotal = Object.values(selected).reduce((sum, s) => {
    const base = PRICES[s.type] || 0;
    const disc = (currentFlashDisc > 0 && s.type !== 'couple') ? currentFlashDisc : 0;
    return sum + Math.round(base * (1 - disc / 100));
  }, 0);

  // Không dùng điểm quá tổng tiền
  const maxPointsByTotal = Math.floor(subtotal / POINTS_VALUE) * POINTS_RATE;
  pts = Math.min(pts, USER_POINTS, maxPointsByTotal);
  pts = Math.max(0, pts);

  input.value = pts;
  pointsUsed  = pts;

  const discount = Math.floor(pts / POINTS_RATE) * POINTS_VALUE;
  const display  = document.getElementById('points-discount-display');
  if (pts > 0) {
    display.textContent = `✓ Giảm ${discount.toLocaleString()}đ (dùng ${pts.toLocaleString()} điểm)`;
  } else {
    display.textContent = '';
  }

  updateSummary();
}

function useAllPoints() {
  let subtotal = Object.values(selected).reduce((sum, s) => {
    const base = PRICES[s.type] || 0;
    const disc = (currentFlashDisc > 0 && s.type !== 'couple') ? currentFlashDisc : 0;
    return sum + Math.round(base * (1 - disc / 100));
  }, 0);

  const maxByTotal = Math.floor(subtotal / POINTS_VALUE) * POINTS_RATE;
  const pts = Math.min(USER_POINTS, maxByTotal);
  document.getElementById('points-input').value = pts;
  pointsUsed = pts;

  const discount = Math.floor(pts / POINTS_RATE) * POINTS_VALUE;
  document.getElementById('points-discount-display').textContent =
    pts > 0 ? `✓ Giảm ${discount.toLocaleString()}đ (dùng ${pts.toLocaleString()} điểm)` : '';

  updateSummary();
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
  } else { msg.innerHTML = ''; }

  // Release ghế cũ trước
  const oldIds = Object.keys(selected).map(Number);
  selected = {};
  if (oldIds.length) {
    try {
      await fetch(`${BASE_URL}/api/release.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ show_id: SHOW_ID, seat_ids: oldIds })
      });
    } catch(e) {}
  }

  // Hold ghế mới
  const newIds = data.seats.map(s => s.id);
  const holdRes = await fetch(`${BASE_URL}/api/hold.php`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ show_id: SHOW_ID, seat_ids: newIds })
  });
  const holdData = await holdRes.json();
  if (holdData.ok) {
    data.seats.forEach(s => { selected[s.id] = s; });
    startCountdown();
  } else {
    msg.innerHTML = `<div class="alert alert-error">Ghế gợi ý vừa bị người khác chọn. Thử lại nhé.</div>`;
  }
  await loadSeats();
}

// ─── Countdown khi có ghế đang held ──────────────────────────────
function startCountdown() {
  holdSeconds = 5 * 60;
  document.getElementById('countdown-wrap').style.display = 'block';
  document.getElementById('countdown').classList.remove('urgent');
  if (holdTimer) clearInterval(holdTimer);
  holdTimer = setInterval(() => {
    holdSeconds--;
    const m = Math.floor(holdSeconds / 60);
    const s = holdSeconds % 60;
    document.getElementById('countdown-time').textContent = `${m}:${s.toString().padStart(2,'0')}`;
    if (holdSeconds <= 60) document.getElementById('countdown').classList.add('urgent');
    if (holdSeconds <= 0) {
      clearInterval(holdTimer);
      document.getElementById('countdown-wrap').style.display = 'none';
      selected = {};
      loadSeats();
      alert('⏱ Hết thời gian giữ ghế. Vui lòng chọn lại.');
    }
  }, 1000);
}

// ─── Payment ──────────────────────────────────────────────────────
async function proceedPayment() {
  const seatIds = Object.keys(selected).map(Number);
  if (!seatIds.length) return;

  document.getElementById('pay-btn').disabled = true;
  document.getElementById('pay-btn').textContent = '⏳ Đang xử lý...';

  // Kiểm tra ghế vẫn còn held bởi mình
  const checkRes = await fetch(`${BASE_URL}/api/seats.php?show_id=${SHOW_ID}&_=${Date.now()}`);
  const seats    = await checkRes.json();
  const stillMine = seatIds.every(id => {
    const s = seats.find(x => x.id == id);
    return s && s.status === 'held' && parseInt(s.held_by) === MY_ID;
  });

  if (!stillMine) {
    document.getElementById('pay-btn').disabled = false;
    document.getElementById('pay-btn').textContent = 'Tiếp tục thanh toán →';
    alert('⚠️ Một số ghế đã hết hạn giữ. Vui lòng chọn lại.');
    selected = {};
    loadSeats();
    return;
  }

  const pointsDiscount = Math.floor(pointsUsed / POINTS_RATE) * POINTS_VALUE;
  const total = Object.values(selected).reduce((sum, s) => {
    const base = PRICES[s.type] || 0;
    const disc = (currentFlashDisc > 0 && s.type !== 'couple') ? currentFlashDisc : 0;
    return sum + Math.round(base * (1 - disc / 100));
  }, 0);
  const finalTotal = Math.max(0, total - pointsDiscount);

  document.getElementById('pay-amount-display').textContent = finalTotal.toLocaleString() + 'đ';
  if (pointsDiscount > 0) {
    document.getElementById('pay-amount-display').innerHTML =
      `<span style="text-decoration:line-through;color:#555;font-size:14px">${total.toLocaleString()}đ</span>
       <br><strong style="color:#f5c518">-${pointsDiscount.toLocaleString()}đ điểm thưởng</strong>
       <br><strong style="font-size:22px">${finalTotal.toLocaleString()}đ</strong>`;
  }
  document.getElementById('pay-ref').textContent = 'MINICINE-' + Date.now();
  document.getElementById('payment-modal').style.display = 'flex';
  document.getElementById('pay-btn').textContent = 'Tiếp tục thanh toán →';
  document.getElementById('pay-btn').disabled = false;
}

async function confirmPayment() {
  const seatIds = Object.keys(selected).map(Number);
  const btn = document.querySelector('#payment-modal .btn-success');
  btn.disabled = true;
  btn.textContent = '⏳ Đang xác nhận...';

  const res = await fetch(`${BASE_URL}/api/confirm.php`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ show_id: SHOW_ID, seat_ids: seatIds, points_used: pointsUsed })
  });
  const data = await res.json();
  if (data.ok) {
    clearInterval(pollInterval);
    clearInterval(holdTimer);
    window.location.href = `${BASE_URL}/booking_success.php?booking_id=${data.booking_id}`;
  } else {
    btn.disabled = false;
    btn.textContent = '✓ Xác nhận đã thanh toán';
    alert('❌ ' + data.msg);
    closePayment();
    loadSeats();
  }
}

function closePayment() {
  document.getElementById('payment-modal').style.display = 'none';
}

// ─── Poll flash sale status ───────────────────────────────────────
let currentFlashDisc = FLASH_DISC;

async function checkFlashSale() {
  try {
    const res  = await fetch(`${BASE_URL}/api/flash_status.php?show_id=${SHOW_ID}&_=${Date.now()}`);
    const data = await res.json();
    const banner = document.getElementById('flash-banner');
    if (data.active) {
      banner.style.display = 'flex';
      document.getElementById('flash-pct').textContent = data.discount_pct + '%';
      if (currentFlashDisc !== data.discount_pct) {
        currentFlashDisc = data.discount_pct;
        updateSummary();
      }
    } else {
      banner.style.display = 'none';
      if (currentFlashDisc !== 0) { currentFlashDisc = 0; updateSummary(); }
    }
  } catch(e) {}
}

// ─── Start polling ────────────────────────────────────────────────
loadSeats();
checkFlashSale();
pollInterval = setInterval(loadSeats, 3000);
setInterval(checkFlashSale, 10000); // check flash sale mỗi 10s để bắt post15m kịp thời

// ─── Release ghế khi user đóng tab / thoát trang ─────────────────
function releaseAllHeld() {
  const seatIds = Object.keys(selected).map(Number);
  if (!seatIds.length) return;
  const payload = JSON.stringify({ show_id: SHOW_ID, seat_ids: seatIds, user_id: MY_ID });
  navigator.sendBeacon(`${BASE_URL}/api/release.php`, new Blob([payload], { type: 'application/json' }));
}

window.addEventListener('beforeunload', releaseAllHeld);
window.addEventListener('pagehide',     releaseAllHeld); // iOS Safari
</script>

<?php renderFooter(); ?>
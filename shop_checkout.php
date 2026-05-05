<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/qr_png.php';

startSession();
requireLogin();
$user = currentUser();
$db   = db();

// Support direct buy vs cart
$directItem = null;
if (isset($_GET['name'])) {
    $directItem = [
        'name'  => $_GET['name'],
        'price' => (int)($_GET['price'] ?? 0),
        'emoji' => $_GET['emoji'] ?? '🛍️',
        'qty'   => 1,
    ];
    $items = [$directItem];
    $total = $directItem['price'];
} else {
    if (empty($_SESSION['cart'])) {
        header('Location: ' . APP_URL . '/star_shop.php'); exit;
    }
    $items = $_SESSION['cart'];
    $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
}

$userPoints = (int)$user['points'];
$confirmed  = false;
$orderCode  = '';
$qrDataUri  = '';
$finalTotal = $total;
$pointsUsed = 0;

// Handle confirm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm') {
    $pointsUsed     = max(0, min($userPoints, (int)($_POST['points_used'] ?? 0)));
    $pointsDiscount = floor($pointsUsed / 100) * 10000;
    $finalTotal     = max(0, $total - $pointsDiscount);

    // Deduct points
    if ($pointsUsed > 0) {
        $db->query("UPDATE tblUsers SET points = points - {$pointsUsed} WHERE id = {$user['id']}");
    }

    // Generate order code & QR
    $orderCode = 'SHOP-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $qrDataUri = generateQrPng($orderCode);

    // Send confirmation email
    require_once __DIR__ . '/includes/mail.php';
    $orderData = [
        'code'            => $orderCode,
        'items'           => $items,
        'total'           => $finalTotal,
        'points_discount' => $pointsDiscount,
    ];
    sendShopOrderEmail($user['email'], $user['name'], $orderData);

    // Clear cart
    if (!$directItem) $_SESSION['cart'] = [];
    $confirmed = true;
}

renderHead('Thanh toán – Star Shop');
?>
<style>
.checkout-page { background:#f5f5f5; min-height:calc(100vh - 64px); padding:28px 24px; }
.checkout-wrap { max-width:1000px; margin:0 auto; display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
.page-back { display:inline-flex; align-items:center; gap:6px; color:#555; font-size:15px; font-weight:600; text-decoration:none; margin-bottom:20px; transition:color .15s; }
.page-back:hover { color:#e8192c; }
.checkout-card { background:#fff; border-radius:12px; border:1px solid #e8e8e8; padding:24px; margin-bottom:16px; }
.checkout-card h3 { font-size:15px; font-weight:700; color:#222; margin-bottom:18px; border-left:3px solid #e8192c; padding-left:10px; }
.points-box { background:#fff9e0; border:1px solid #e0c040; border-radius:10px; padding:14px; }
.points-input-row { display:flex; gap:8px; margin-top:8px; }
.points-input-row input { flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:13px; font-family:inherit; }
.points-input-row input:focus { outline:none; border-color:#e8192c; }
.btn-all-pts { padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:12px; font-weight:600; background:#fff; cursor:pointer; font-family:inherit; white-space:nowrap; }
.points-result { font-size:12px; color:#27ae60; margin-top:6px; font-weight:600; }
.summary-panel { background:#fff; border-radius:12px; border:1px solid #e8e8e8; padding:20px; position:sticky; top:80px; }
.summary-panel-top { height:5px; background:linear-gradient(90deg,#e8192c,#ff6b00); border-radius:12px 12px 0 0; margin:-20px -20px 16px; }
.summary-title { font-size:14px; font-weight:700; color:#e8192c; margin-bottom:14px; }
.summary-item { display:flex; gap:10px; align-items:center; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #f5f5f5; }
.summary-item:last-of-type { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.summary-item-img { width:44px; height:44px; background:#f5f5f5; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.summary-item-name { font-size:12px; font-weight:600; color:#222; }
.summary-item-price { font-size:12px; color:#e8192c; font-weight:700; }
.summary-divider { border:none; border-top:1px dashed #e8e8e8; margin:12px 0; }
.summary-row { display:flex; justify-content:space-between; font-size:13px; padding:3px 0; color:#888; }
.summary-total-row { display:flex; justify-content:space-between; align-items:center; padding-top:10px; font-weight:800; }
.summary-total-row span:last-child { font-size:17px; color:#e8192c; }
.btn-pay { display:block; width:100%; background:#e8192c; color:#fff !important; border:none; border-radius:8px; padding:13px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; text-align:center; margin-top:14px; transition:background .15s; text-decoration:none; }
.btn-pay:hover { background:#c5101f; }
.btn-cancel { display:block; text-align:center; color:#888; font-size:13px; padding:8px; margin-top:4px; text-decoration:none; }

/* Success page */
.success-wrap { max-width:440px; margin:0 auto; }
.success-header { text-align:center; margin-bottom:24px; }
.success-icon { width:60px; height:60px; background:linear-gradient(135deg,#27ae60,#2ecc71); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 14px; }
.success-header h2 { font-size:22px; font-weight:900; color:#222; margin-bottom:6px; }
.success-header p { font-size:13px; color:#888; }
.order-ticket { background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.1); margin-bottom:16px; }
.order-ticket-header { background:linear-gradient(135deg,#e8192c,#ff6b00); padding:18px 20px; color:#fff; }
.order-ticket-header h3 { font-size:15px; font-weight:800; margin-bottom:2px; }
.order-ticket-header p { font-size:12px; opacity:.8; }
.order-ticket-body { padding:18px 20px; }
.order-ticket-row { display:flex; justify-content:space-between; font-size:13px; padding:7px 0; border-bottom:1px solid #f5f5f5; }
.order-ticket-row:last-child { border-bottom:none; }
.order-ticket-label { color:#888; }
.order-ticket-value { font-weight:600; color:#222; }
.order-ticket-total { font-size:16px; font-weight:800; color:#e8192c; }
.tear-line { display:flex; align-items:center; height:24px; position:relative; margin:0; }
.tear-line::before { content:''; position:absolute; left:0; right:0; top:50%; border-top:2px dashed #e8e8e8; }
.tear-circle { width:24px; height:24px; background:#f5f5f5; border-radius:50%; position:relative; z-index:1; flex-shrink:0; }
.tear-circle.left { margin-left:-12px; }
.tear-circle.right { margin-left:auto; margin-right:-12px; }
.qr-section { padding:20px; text-align:center; background:#fff; }
.qr-box { background:#fff; border:2px solid #f0f0f0; border-radius:10px; padding:14px; display:inline-block; margin-bottom:8px; }
.btn-actions { display:flex; gap:10px; margin-top:14px; }
.btn-actions a { flex:1; padding:11px; border-radius:8px; font-size:13px; font-weight:700; text-align:center; text-decoration:none; transition:all .15s; }

@media(max-width:768px){ .checkout-wrap{grid-template-columns:1fr} .summary-panel{position:static} }

/* Payment modal */
.pay-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; align-items:center; justify-content:center; padding:20px; }
.pay-modal { background:#fff; border-radius:16px; padding:32px; width:100%; max-width:420px; text-align:center; }
.pay-modal h3 { font-size:18px; font-weight:800; margin-bottom:4px; color:#222; }
.pay-modal p { font-size:13px; color:#888; margin-bottom:20px; }
.pay-modal-info { background:#fafafa; border-radius:10px; padding:20px; border:1px solid #eee; margin-bottom:20px; }
.pay-modal-amount { font-size:26px; font-weight:900; color:#e8192c; margin-bottom:6px; }
.pay-modal-sub { font-size:12px; color:#888; }
.pay-modal-actions { display:flex; gap:10px; }
.btn-modal-confirm { flex:1; background:#27ae60; color:#fff; border:none; border-radius:8px; padding:13px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-modal-cancel  { flex:1; background:#f5f5f5; color:#888; border:none; border-radius:8px; padding:13px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }
</style>

<?php renderNav(); ?>

<div class="checkout-page">

<?php if ($confirmed): ?>
<!-- ══════ THÀNH CÔNG ══════ -->
<div class="success-wrap" style="padding:0 20px">
  <div class="success-header">
    <div class="success-icon">✓</div>
    <h2>Đặt hàng thành công!</h2>
    <p>Xuất trình mã QR tại quầy MiniCine để nhận hàng</p>
  </div>

  <div class="order-ticket">
    <div class="order-ticket-header">
      <h3>🛍️ MiniCine Star Shop</h3>
      <p>Đơn hàng của bạn đã được xác nhận</p>
    </div>
    <div class="order-ticket-body">
      <?php foreach ($items as $item): ?>
      <div class="order-ticket-row">
        <span class="order-ticket-label"><?= $item['emoji'] ?> <?= htmlspecialchars($item['name']) ?></span>
        <span class="order-ticket-value">
          <?php if (($item['qty']??1) > 1): ?>×<?= $item['qty'] ?> · <?php endif; ?>
          <?= number_format($item['price'] * ($item['qty']??1)) ?>đ
        </span>
      </div>
      <?php endforeach; ?>
      <?php if ($pointsUsed > 0): ?>
      <div class="order-ticket-row">
        <span class="order-ticket-label">⭐ Giảm điểm Stars</span>
        <span style="color:#27ae60;font-weight:600">-<?= number_format(floor($pointsUsed/100)*10000) ?>đ</span>
      </div>
      <?php endif; ?>
      <div class="order-ticket-row">
        <span class="order-ticket-label" style="font-weight:700;color:#222">Tổng thanh toán</span>
        <span class="order-ticket-total"><?= number_format($finalTotal) ?>đ</span>
      </div>
      <div class="order-ticket-row">
        <span class="order-ticket-label">Mã đơn hàng</span>
        <span class="order-ticket-value" style="font-family:monospace;color:#e8192c;font-size:12px"><?= $orderCode ?></span>
      </div>
    </div>

    <div class="tear-line"><div class="tear-circle left"></div><div class="tear-circle right"></div></div>

    <div class="qr-section">
      <p style="font-size:12px;font-weight:700;color:#555;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px">Mã QR nhận hàng</p>
      <div class="qr-box">
        <img src="<?= $qrDataUri ?>" width="180" height="180" alt="QR" style="display:block">
      </div>
      <p style="font-size:12px;color:#888;margin-top:8px">Xuất trình mã này tại quầy MiniCine để nhận sản phẩm</p>
      <p style="font-size:11px;color:#aaa;font-family:monospace;margin-top:4px"><?= $orderCode ?></p>
    </div>
  </div>

  <div class="btn-actions">
    <a href="<?= APP_URL ?>/star_shop.php" style="border:1.5px solid #e8192c;color:#e8192c;background:#fff">🛍️ Tiếp tục mua</a>
    <a href="<?= APP_URL ?>/index.php" style="background:#e8192c;color:#fff">🎬 Trang chủ</a>
  </div>
</div>

<?php else: ?>
<!-- ══════ FORM THANH TOÁN ══════ -->
<div style="max-width:1000px;margin:0 auto;padding:0 24px">
  <a href="<?= APP_URL ?>/<?= $directItem ? 'star_shop.php' : 'cart.php' ?>" class="page-back">← Thanh toán</a>
</div>

<form method="post">
<input type="hidden" name="action" value="confirm">
<div class="checkout-wrap">

  <div>
    <!-- Dùng điểm -->
    <div class="checkout-card">
      <h3>⭐ Áp dụng điểm Stars</h3>
      <div class="points-box">
        <div style="font-size:12px;font-weight:700;color:#b8860b;margin-bottom:4px">Điểm Stars của bạn</div>
        <div style="font-size:12px;color:#888">
          Bạn có <strong style="color:#b8860b"><?= number_format($userPoints) ?></strong> điểm
          = <strong style="color:#b8860b"><?= number_format(floor($userPoints/100)*10000) ?>đ</strong>
        </div>
        <div class="points-input-row">
          <input type="number" name="points_used" id="pointsInput"
                 min="0" max="<?= $userPoints ?>" step="100" value="0"
                 oninput="calcDiscount()" placeholder="Nhập số điểm">
          <button type="button" class="btn-all-pts" onclick="useAll()">Tất cả</button>
        </div>
        <div class="points-result" id="pointsResult"></div>
      </div>
    </div>

    <!-- Xác nhận -->
    <div class="checkout-card">
      <h3>✅ Xác nhận đơn hàng</h3>
      <p style="font-size:13px;color:#888;margin-bottom:16px">Sản phẩm sẽ được chuẩn bị sẵn tại quầy rạp. Xuất trình mã QR khi đến nhận.</p>
      <div style="background:#fafafa;border-radius:10px;padding:20px;text-align:center;border:1px solid #f0f0f0">
        <div style="font-size:40px;margin-bottom:8px">🏪</div>
        <div style="font-size:22px;font-weight:800;color:#e8192c;margin-bottom:4px" id="payAmountDisplay"><?= number_format($total) ?> đ</div>
        <div style="font-size:12px;color:#888">Nhận hàng tại quầy MiniCine · Xuất trình mã QR</div>
      </div>
    </div>
  </div>

  <!-- Panel tóm tắt -->
  <div class="summary-panel">
    <div class="summary-panel-top"></div>
    <div class="summary-title">🛒 Tóm tắt đơn hàng</div>
    <?php foreach ($items as $item): ?>
    <div class="summary-item">
      <div class="summary-item-img"><?= $item['emoji'] ?></div>
      <div style="flex:1;min-width:0">
        <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
        <div class="summary-item-price">
          <?= number_format($item['price']) ?>đ
          <?php if (($item['qty']??1) > 1): ?> × <?= $item['qty'] ?><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <hr class="summary-divider">
    <div class="summary-row"><span>Tổng tiền hàng</span><span><?= number_format($total) ?> đ</span></div>
    <div class="summary-row" id="discountRow" style="display:none;color:#27ae60">
      <span>⭐ Giảm điểm Stars</span>
      <span id="discountAmount">-0đ</span>
    </div>
    <hr class="summary-divider">
    <div class="summary-total-row">
      <span>Số tiền thanh toán</span>
      <span id="finalTotal"><?= number_format($total) ?> đ</span>
    </div>
    <button type="button" class="btn-pay" onclick="openPayModal()">Thanh Toán</button>
    <a href="<?= APP_URL ?>/<?= $directItem ? 'star_shop.php' : 'cart.php' ?>" class="btn-cancel">Hủy</a>
  </div>

</div>
</form>

<!-- Payment modal -->
<div class="pay-modal-overlay" id="payModal" style="display:none">
  <div class="pay-modal">
    <h3>THÔNG TIN ĐƠN HÀNG</h3>
    <p>Xác nhận thông tin trước khi thanh toán</p>
    <div class="pay-modal-info">
      <div class="pay-modal-amount" id="modalAmount"></div>
      <div class="pay-modal-sub">Thanh toán & nhận hàng tại quầy MiniCine</div>
    </div>
    <div style="font-size:12px;color:#888;margin-bottom:20px">
      Email xác nhận sẽ được gửi đến <strong><?= htmlspecialchars($user['email']) ?></strong>
    </div>
    <div class="pay-modal-actions">
      <button class="btn-modal-cancel" onclick="closePayModal()">Hủy</button>
      <button class="btn-modal-confirm" id="btnModalConfirm" onclick="submitPayment()">✓ Xác nhận thanh toán</button>
    </div>
  </div>
</div>
<?php endif; ?>

</div>

<script>
const TOTAL = <?= $total ?>;
const USER_POINTS = <?= $userPoints ?>;

function calcDiscount() {
  const input = document.getElementById('pointsInput');
  let pts = Math.floor((parseInt(input.value)||0)/100)*100;
  const maxByTotal = Math.floor(TOTAL/10000)*100;
  pts = Math.min(pts, USER_POINTS, maxByTotal);
  pts = Math.max(0, pts);
  input.value = pts;
  const disc  = Math.floor(pts/100)*10000;
  const final = Math.max(0, TOTAL - disc);
  if (disc > 0) {
    document.getElementById('discountRow').style.display = 'flex';
    document.getElementById('discountAmount').textContent = '-' + disc.toLocaleString() + 'đ';
    document.getElementById('pointsResult').textContent = '✓ Giảm ' + disc.toLocaleString() + 'đ';
  } else {
    document.getElementById('discountRow').style.display = 'none';
    document.getElementById('pointsResult').textContent = '';
  }
  document.getElementById('finalTotal').textContent = final.toLocaleString() + ' đ';
  document.getElementById('payAmountDisplay').textContent = final.toLocaleString() + ' đ';
}

function useAll() {
  document.getElementById('pointsInput').value = Math.min(USER_POINTS, Math.floor(TOTAL/10000)*100);
  calcDiscount();
}

function openPayModal() {
  const finalEl = document.getElementById('finalTotal');
  document.getElementById('modalAmount').textContent = finalEl ? finalEl.textContent : '<?= number_format($total) ?> đ';
  document.getElementById('payModal').style.display = 'flex';
}
function closePayModal() {
  document.getElementById('payModal').style.display = 'none';
}
function submitPayment() {
  const btn = document.getElementById('btnModalConfirm');
  btn.disabled = true; btn.textContent = '⏳ Đang xử lý...';
  document.querySelector('form').submit();
}
document.getElementById('payModal')?.addEventListener('click', function(e) {
  if (e.target === this) closePayModal();
});
</script>

<?php renderFooter(); ?>
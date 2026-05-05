<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

startSession();
requireLogin();

// Cart stored in session
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '🛍️');
    $qty   = max(1,(int)($_POST['qty'] ?? 1));
    if ($name && $price > 0) {
        $key = md5($name);
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = compact('name','price','emoji','qty');
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true,'count'=>array_sum(array_column($_SESSION['cart'],'qty'))]);
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'] ?? '';
    $qty = (int)($_POST['qty'] ?? 0);
    if (isset($_SESSION['cart'][$key])) {
        if ($qty <= 0) unset($_SESSION['cart'][$key]);
        else $_SESSION['cart'][$key]['qty'] = $qty;
    }
    header('Location: cart.php'); exit;
}

if ($action === 'remove') {
    $key = $_GET['key'] ?? '';
    unset($_SESSION['cart'][$key]);
    header('Location: cart.php'); exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header('Location: cart.php'); exit;
}

$cart    = $_SESSION['cart'];
$total   = array_sum(array_map(fn($i)=>$i['price']*$i['qty'], $cart));
$count   = array_sum(array_column($cart,'qty'));

renderHead('Giỏ hàng – MiniCine');
?>
<style>
.cart-page { background:#f5f5f5; min-height:calc(100vh - 64px); padding:28px 24px; }
.cart-wrap { max-width:1000px; margin:0 auto; display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
.cart-title { font-size:20px; font-weight:800; color:#222; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
.cart-card { background:#fff; border-radius:12px; border:1px solid #e8e8e8; overflow:hidden; }
.cart-empty { text-align:center; padding:60px 20px; }
.cart-empty .empty-icon { font-size:64px; margin-bottom:16px; }
.cart-empty p { color:#888; font-size:14px; margin-bottom:20px; }
.cart-item { display:flex; align-items:center; gap:14px; padding:16px 20px; border-bottom:1px solid #f5f5f5; }
.cart-item:last-child { border-bottom:none; }
.cart-item-img { width:60px; height:60px; background:#f5f5f5; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
.cart-item-name { font-size:14px; font-weight:700; color:#222; margin-bottom:4px; }
.cart-item-price { font-size:13px; color:#e8192c; font-weight:700; }
.cart-qty { display:flex; align-items:center; gap:8px; margin-left:auto; }
.qty-btn { width:28px; height:28px; border-radius:50%; border:1px solid #ddd; background:#fff; font-size:16px; cursor:pointer; font-family:inherit; transition:all .15s; display:flex; align-items:center; justify-content:center; }
.qty-btn:hover { border-color:#e8192c; color:#e8192c; }
.qty-num { font-size:14px; font-weight:700; min-width:24px; text-align:center; }
.cart-item-total { font-size:14px; font-weight:700; color:#e8192c; min-width:90px; text-align:right; margin-left:12px; }
.btn-remove { background:none; border:none; color:#ccc; font-size:18px; cursor:pointer; padding:4px; margin-left:8px; transition:color .15s; }
.btn-remove:hover { color:#e8192c; }

/* Summary panel */
.summary-panel { background:#fff; border-radius:12px; border:1px solid #e8e8e8; padding:20px; position:sticky; top:80px; }
.summary-title { font-size:14px; font-weight:700; color:#e8192c; margin-bottom:16px; display:flex; align-items:center; gap:6px; }
.summary-row { display:flex; justify-content:space-between; font-size:13px; padding:6px 0; border-bottom:1px solid #f5f5f5; }
.summary-row:last-of-type { border-bottom:none; }
.summary-total { display:flex; justify-content:space-between; align-items:center; padding:12px 0 16px; font-weight:800; font-size:15px; }
.summary-total span:last-child { color:#e8192c; font-size:18px; }
.btn-checkout { display:block; width:100%; background:#e8192c; color:#fff; border:none; border-radius:8px; padding:13px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; text-align:center; text-decoration:none; transition:background .15s; margin-bottom:8px; }
.btn-checkout:hover { background:#c5101f; color:#fff; text-decoration:none; }
.btn-continue { display:block; width:100%; background:#fff; color:#e8192c; border:1.5px solid #e8192c; border-radius:8px; padding:11px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; text-align:center; text-decoration:none; transition:all .15s; margin-bottom:8px; }
.btn-continue:hover { background:#fff5f5; }
.btn-clear { display:block; width:100%; background:#f5f5f5; color:#888; border:1px solid #ddd; border-radius:8px; padding:10px; font-size:13px; font-weight:500; cursor:pointer; font-family:inherit; text-align:center; text-decoration:none; transition:all .15s; }
.btn-clear:hover { background:#eee; color:#555; }

@media(max-width:768px){ .cart-wrap{grid-template-columns:1fr} .summary-panel{position:static} }
</style>

<?php renderNav(); ?>

<div class="cart-page">
<div class="cart-wrap">

  <div>
    <div class="cart-title">🛒 Giỏ hàng <?php if($count>0): ?><span style="font-size:14px;font-weight:500;color:#888">(<?= $count ?> sản phẩm)</span><?php endif; ?></div>
    <div class="cart-card">
      <?php if (empty($cart)): ?>
        <div class="cart-empty">
          <div class="empty-icon">🛒</div>
          <p>Giỏ hàng của bạn đang trống!</p>
          <a href="<?= APP_URL ?>/star_shop.php" class="btn-checkout" style="display:inline-block;width:auto;padding:11px 28px">Khám phá Star Shop</a>
        </div>
      <?php else: ?>
        <?php foreach ($cart as $key => $item): ?>
        <div class="cart-item">
          <div class="cart-item-img"><?= $item['emoji'] ?></div>
          <div style="flex:1;min-width:0">
            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="cart-item-price"><?= number_format($item['price']) ?> đ / cái</div>
          </div>
          <form method="post" style="display:contents">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
            <div class="cart-qty">
              <button type="submit" name="qty" value="<?= $item['qty']-1 ?>" class="qty-btn">−</button>
              <span class="qty-num"><?= $item['qty'] ?></span>
              <button type="submit" name="qty" value="<?= $item['qty']+1 ?>" class="qty-btn">+</button>
            </div>
          </form>
          <div class="cart-item-total"><?= number_format($item['price']*$item['qty']) ?> đ</div>
          <a href="?action=remove&key=<?= urlencode($key) ?>" class="btn-remove" title="Xóa">🗑</a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Summary -->
  <div class="summary-panel">
    <div class="summary-title">🛒 Tóm tắt đơn hàng</div>
    <?php foreach ($cart as $item): ?>
    <div class="summary-row">
      <span><?= htmlspecialchars($item['name']) ?> ×<?= $item['qty'] ?></span>
      <span><?= number_format($item['price']*$item['qty']) ?> đ</span>
    </div>
    <?php endforeach; ?>
    <div class="summary-total">
      <span>Số tiền thanh toán</span>
      <span><?= number_format($total) ?> đ</span>
    </div>
    <?php if (!empty($cart)): ?>
    <a href="<?= APP_URL ?>/shop_checkout.php" class="btn-checkout">Đặt hàng</a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/star_shop.php" class="btn-continue">Tiếp tục mua sắm</a>
    <?php if (!empty($cart)): ?>
    <a href="?action=clear" class="btn-clear" onclick="return confirm('Xóa toàn bộ giỏ hàng?')">Xóa giỏ hàng</a>
    <?php endif; ?>
  </div>

</div>
</div>

<?php renderFooter(); ?>
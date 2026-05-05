<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';

startSession();

$tab = $_GET['tab'] ?? 'combo';
$validTabs = ['combo', 'merchandise', 'limited'];
if (!in_array($tab, $validTabs)) $tab = 'combo';

// Dữ liệu sản phẩm (hardcode — có thể chuyển DB sau)
$products = [
    'combo' => [
        [
            'name'  => 'Combo 1 Big',
            'desc'  => '1 bắp rang bơ vừa + 1 Pepsi mát lạnh. Thỏa mãn cơn thèm khi xem phim!',
            'price' => 90000,
            'emoji' => '🍿',
            'tag'   => '',
        ],
        [
            'name'  => 'Combo 2 Big',
            'desc'  => '1 bắp rang bơ lớn + 2 Pepsi cỡ lớn. Nhân đôi sự sảng khoái!',
            'price' => 109000,
            'emoji' => '🥤',
            'tag'   => 'Phổ biến',
        ],
        [
            'name'  => 'Combo 1 Big Extra',
            'desc'  => '1 bắp lớn + 1 Pepsi + 1 gói snack Premium tùy chọn.',
            'price' => 115000,
            'emoji' => '🍿',
            'tag'   => '',
        ],
        [
            'name'  => 'Combo 2 Big Extra',
            'desc'  => '1 bắp lớn + 2 Pepsi + 1 snack Premium. Tiết kiệm hơn 33.000đ!',
            'price' => 134000,
            'emoji' => '🥤',
            'tag'   => 'Tiết kiệm',
        ],
        [
            'name'  => 'Combo 3',
            'desc'  => '2 bắp rang bơ + 3 Pepsi mát lạnh. Chia sẻ niềm vui với bạn bè!',
            'price' => 149000,
            'emoji' => '🎉',
            'tag'   => '',
        ],
        [
            'name'  => 'Combo 4 (Nhóm bạn)',
            'desc'  => '3 bắp rang bơ lớn + 4 Pepsi. Siêu tiết kiệm hơn 95.000đ!',
            'price' => 229000,
            'emoji' => '🎊',
            'tag'   => 'Nhóm',
        ],
        [
            'name'  => 'Snack Nachos',
            'desc'  => 'Nachos giòn rụm phủ sốt phô mai nóng hổi.',
            'price' => 55000,
            'emoji' => '🧀',
            'tag'   => '',
        ],
        [
            'name'  => 'Hot Dog',
            'desc'  => 'Xúc xích nóng với bánh mì mềm, sốt tương cà.',
            'price' => 49000,
            'emoji' => '🌭',
            'tag'   => '',
        ],
    ],
    'merchandise' => [
        [
            'name'  => 'Bình Minion Limited',
            'desc'  => 'Bình nước Minion phiên bản giới hạn, dung tích 700ml. Siêu cute!',
            'price' => 199000,
            'emoji' => '🟡',
            'tag'   => 'Limited',
        ],
        [
            'name'  => 'Ly MiniCine Capybara',
            'desc'  => 'Ly nước Capybara độc quyền MiniCine, dung tích 600ml.',
            'price' => 175000,
            'emoji' => '🦫',
            'tag'   => 'Mới',
        ],
        [
            'name'  => 'Túi vải Canvas MiniCine',
            'desc'  => 'Túi canvas thân thiện môi trường, in logo MiniCine thời thượng.',
            'price' => 89000,
            'emoji' => '👜',
            'tag'   => '',
        ],
        [
            'name'  => 'Móc khóa Popcorn',
            'desc'  => 'Móc khóa hình hộp bắp rang bơ siêu dễ thương.',
            'price' => 39000,
            'emoji' => '🔑',
            'tag'   => '',
        ],
        [
            'name'  => 'Poster Phim A3',
            'desc'  => 'Poster phim đang chiếu in chất lượng cao, khổ A3.',
            'price' => 59000,
            'emoji' => '🖼️',
            'tag'   => '',
        ],
        [
            'name'  => 'Áo thun MiniCine',
            'desc'  => 'Áo thun cotton 100%, in logo MiniCine, có size S-XL.',
            'price' => 249000,
            'emoji' => '👕',
            'tag'   => 'Hot',
        ],
    ],
    'limited' => [
        [
            'name'  => 'Set Frozen 2 – Elsa Cup',
            'desc'  => 'Ly nước Elsa phiên bản Frozen 2, kèm straw hình bông tuyết.',
            'price' => 199000,
            'emoji' => '❄️',
            'tag'   => 'Limited',
        ],
        [
            'name'  => 'Funko Pop Avengers',
            'desc'  => 'Mô hình Funko Pop nhân vật Avengers, cao 10cm, hộp có thể trưng bày.',
            'price' => 349000,
            'emoji' => '🦸',
            'tag'   => 'Collector',
        ],
        [
            'name'  => 'Set Combo Jujutsu Kaisen',
            'desc'  => '1 ly + 1 hộp mù nhân vật Jujutsu Kaisen. Số lượng có hạn!',
            'price' => 299000,
            'emoji' => '⚡',
            'tag'   => 'Giới hạn',
        ],
        [
            'name'  => 'Tote Bag Zootopia',
            'desc'  => 'Túi vải in hình nhân vật Zootopia, kích thước A4.',
            'price' => 129000,
            'emoji' => '🦊',
            'tag'   => 'Limited',
        ],
    ],
];

$tabLabels = [
    'combo'       => ['icon'=>'🍿', 'label'=>'Combo Bắp & Nước'],
    'merchandise' => ['icon'=>'🛍️', 'label'=>'Quà Lưu Niệm'],
    'limited'     => ['icon'=>'⭐', 'label'=>'Phiên Bản Giới Hạn'],
];

$tagColors = [
    'Phổ biến' => ['bg'=>'#e8192c','text'=>'#fff'],
    'Tiết kiệm'=> ['bg'=>'#27ae60','text'=>'#fff'],
    'Nhóm'     => ['bg'=>'#2980b9','text'=>'#fff'],
    'Limited'  => ['bg'=>'#8e44ad','text'=>'#fff'],
    'Mới'      => ['bg'=>'#e67e22','text'=>'#fff'],
    'Hot'      => ['bg'=>'#e8192c','text'=>'#fff'],
    'Collector'=> ['bg'=>'#2c3e50','text'=>'#fff'],
    'Giới hạn' => ['bg'=>'#8e44ad','text'=>'#fff'],
];

renderHead('Star Shop – MiniCine');
?>
<style>
.starshop-page { background: #f5f5f5; min-height: calc(100vh - 64px); }

/* Hero banner */
.starshop-hero {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
  padding: 48px 24px 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.starshop-hero::before {
  content: '🍿🎬🥤🎟️🍿🎬🥤🎟️';
  position: absolute; top: 10px; left: 0; right: 0;
  font-size: 28px; opacity: .07; letter-spacing: 24px;
  white-space: nowrap; overflow: hidden;
}
.starshop-hero h1 {
  font-size: 36px; font-weight: 900; color: #fff; margin-bottom: 8px;
  letter-spacing: -1px;
}
.starshop-hero h1 span { color: #f5a623; }
.starshop-hero p { font-size: 15px; color: rgba(255,255,255,.65); max-width: 480px; margin: 0 auto; }

/* Tab navigation */
.starshop-tabs {
  background: #fff;
  border-bottom: 1px solid #e8e8e8;
  position: sticky;
  top: 64px;
  z-index: 100;
}
.starshop-tabs-inner {
  max-width: 1100px; margin: 0 auto; padding: 0 24px;
  display: flex; gap: 0;
}
.starshop-tab {
  display: flex; align-items: center; gap: 7px;
  padding: 16px 24px;
  font-size: 14px; font-weight: 600; color: #888;
  cursor: pointer; background: none; border: none;
  border-bottom: 3px solid transparent;
  transition: all .15s; font-family: inherit;
  white-space: nowrap;
  text-decoration: none;
  position: relative; bottom: -1px;
}
.starshop-tab:hover { color: #333; }
.starshop-tab.active { color: #e8192c; border-bottom-color: #e8192c; }

/* Content */
.starshop-content {
  max-width: 1100px; margin: 0 auto; padding: 28px 24px;
}
.starshop-section-header {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 20px;
}
.starshop-section-title {
  font-size: 18px; font-weight: 800; color: #222;
  border-left: 4px solid #e8192c; padding-left: 12px;
}
.starshop-section-desc { font-size: 13px; color: #888; margin-left: auto; }

/* Product grid */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 20px;
}
.product-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e8e8e8;
  overflow: hidden;
  transition: transform .15s, box-shadow .15s;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,.1);
}
.product-img {
  width: 100%; aspect-ratio: 1/1;
  background: linear-gradient(135deg, #f8f4ff, #fff3e0);
  display: flex; align-items: center; justify-content: center;
  font-size: 72px; position: relative;
}
.product-tag {
  position: absolute; top: 10px; right: 10px;
  padding: 3px 10px; border-radius: 12px;
  font-size: 11px; font-weight: 700;
}
.product-body { padding: 14px 16px 16px; }
.product-name {
  font-size: 15px; font-weight: 700; color: #222;
  margin-bottom: 5px;
}
.product-desc {
  font-size: 12px; color: #888; line-height: 1.5;
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.product-price {
  font-size: 18px; font-weight: 800; color: #e8192c;
  margin-bottom: 12px;
}
.product-actions { display: flex; gap: 8px; }
.btn-buy-now {
  flex: 1; background: #e8192c; color: #fff;
  border: none; border-radius: 7px; padding: 10px;
  font-size: 13px; font-weight: 700; cursor: pointer;
  font-family: inherit; transition: background .15s;
}
.btn-buy-now:hover { background: #c5101f; }
.btn-add-cart {
  padding: 10px 12px; border-radius: 7px;
  border: 1.5px solid #e8192c; background: #fff;
  color: #e8192c; font-size: 13px; font-weight: 600;
  cursor: pointer; font-family: inherit;
  transition: all .15s; white-space: nowrap;
  display: flex; align-items: center; gap: 5px;
}
.btn-add-cart:hover { background: #fff5f5; }

/* Notice banner */
.combo-notice {
  background: linear-gradient(135deg, #fff8e1, #fff3cd);
  border: 1px solid #f5c518;
  border-radius: 10px; padding: 14px 18px;
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 20px; font-size: 13px; color: #8a6d00;
}

/* Toast */
.cart-toast {
  position: fixed; bottom: 24px; right: 24px;
  background: #1a1a1a; color: #fff;
  padding: 14px 18px; border-radius: 10px;
  font-size: 13px;
  box-shadow: 0 6px 24px rgba(0,0,0,.35);
  opacity: 0; transform: translateY(12px);
  transition: all .25s; z-index: 9999;
  display: flex; align-items: center;
  min-width: 260px; max-width: 320px;
  border-left: 4px solid #e8192c;
}
.cart-toast.show { opacity: 1; transform: translateY(0); }

@media (max-width: 768px) {
  .starshop-hero h1 { font-size: 24px; }
  .product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .starshop-tab { padding: 12px 14px; font-size: 13px; }
}
</style>

<?php renderNav(); ?>

<div class="starshop-page">

  <!-- Hero -->
  <div class="starshop-hero">
    <h1>⭐ MINI<span>SHOP</span></h1>
    <p>Combo bắp nước, quà lưu niệm và merchandise độc quyền — trải nghiệm xem phim trọn vẹn hơn!</p>
  </div>

  <!-- Tabs -->
  <div class="starshop-tabs">
    <div class="starshop-tabs-inner">
      <?php foreach ($tabLabels as $key => $info): ?>
      <a href="?tab=<?= $key ?>" class="starshop-tab <?= $tab===$key?'active':'' ?>">
        <?= $info['icon'] ?> <?= $info['label'] ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Content -->
  <div class="starshop-content">

    <?php if ($tab === 'combo'): ?>
    <div class="combo-notice">
      <span style="font-size:20px">💡</span>
      <div>Combo bắp nước có thể thêm vào đơn ngay khi đặt vé! Hoặc mua trước tại đây để nhận tại quầy rạp.</div>
    </div>
    <?php endif; ?>

    <div class="starshop-section-header">
      <div class="starshop-section-title">
        <?= $tabLabels[$tab]['icon'] ?> <?= $tabLabels[$tab]['label'] ?>
      </div>
      <div class="starshop-section-desc"><?= count($products[$tab]) ?> sản phẩm</div>
    </div>

    <div class="product-grid">
      <?php foreach ($products[$tab] as $p):
        $tagStyle = '';
        if ($p['tag'] && isset($tagColors[$p['tag']])) {
            $tc = $tagColors[$p['tag']];
            $tagStyle = "background:{$tc['bg']};color:{$tc['text']}";
        }
      ?>
      <div class="product-card">
        <div class="product-img">
          <span><?= $p['emoji'] ?></span>
          <?php if ($p['tag']): ?>
          <span class="product-tag" style="<?= $tagStyle ?>"><?= htmlspecialchars($p['tag']) ?></span>
          <?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-desc"><?= htmlspecialchars($p['desc']) ?></div>
          <div class="product-price"><?= number_format($p['price']) ?> đ</div>
          <div class="product-actions">
            <a href="<?= APP_URL ?>/shop_checkout.php?name=<?= urlencode($p['name']) ?>&price=<?= $p['price'] ?>&emoji=<?= urlencode($p['emoji']) ?>" class="btn-buy-now">Mua ngay</a>
            <button class="btn-add-cart" onclick="addToCart(<?= htmlspecialchars(json_encode($p['name'])) ?>,<?= $p['price'] ?>,<?= htmlspecialchars(json_encode($p['emoji'])) ?>)">🛒 Thêm</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($tab === 'combo'): ?>
    <!-- Upsell banner -->
    <div style="background:linear-gradient(135deg,#e8192c,#ff6b00);border-radius:12px;padding:24px;margin-top:28px;display:flex;align-items:center;gap:20px;color:#fff">
      <div style="font-size:48px">🎬</div>
      <div>
        <div style="font-size:17px;font-weight:800;margin-bottom:4px">Mua vé kèm combo — tiết kiệm hơn!</div>
        <div style="font-size:13px;opacity:.85">Thêm combo bắp nước ngay trong bước đặt vé, nhận tại quầy rạp không cần xếp hàng.</div>
      </div>
      <a href="<?= APP_URL ?>/booking.php" style="margin-left:auto;background:#fff;color:#e8192c;padding:11px 22px;border-radius:8px;font-size:14px;font-weight:700;text-decoration:none;white-space:nowrap;flex-shrink:0">Đặt vé ngay →</a>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Toast -->
<div class="cart-toast" id="cartToast">
  <div style="display:flex;align-items:center;gap:10px;flex:1">
    <span style="font-size:20px">✓</span>
    <div>
      <div style="font-weight:700;font-size:13px" id="toastName"></div>
      <div style="font-size:11px;opacity:.8" id="toastPrice"></div>
    </div>
  </div>
  <a href="<?= APP_URL ?>/cart.php" style="color:#f5c518;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;margin-left:12px">Xem giỏ →</a>
</div>

<script>
const BASE = '<?= APP_URL ?>';

async function addToCart(name, price, emoji) {
  const fd = new FormData();
  fd.append('action','add');
  fd.append('name', name);
  fd.append('price', price);
  fd.append('emoji', emoji);
  fd.append('qty', 1);
  const res  = await fetch(BASE+'/cart.php', {method:'POST', body:fd});
  const data = await res.json();
  // Update cart badge
  const badge = document.getElementById('navCartCount');
  if (badge && data.count > 0) {
    badge.textContent = data.count;
    badge.style.display = 'flex';
  }
  // Show toast
  showToast(name, price);
}

function showToast(name, price) {
  const t = document.getElementById('cartToast');
  document.getElementById('toastName').textContent = name;
  document.getElementById('toastPrice').textContent = '1 × ' + price.toLocaleString() + 'đ';
  t.classList.add('show');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}
</script>

<?php renderFooter(); ?>
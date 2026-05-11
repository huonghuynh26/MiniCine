<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['standard','vip','couple'] as $type) {
        $price = (int)str_replace([',','.'], '', $_POST[$type] ?? 0);
        if ($price > 0) {
            $s = $db->prepare("UPDATE tblPrices SET price=? WHERE seat_type=?");
            $s->bind_param('is', $price, $type); $s->execute();
        }
    }
    $msg = 'Đã cập nhật giá vé.';
}

$prices = [];
foreach ($db->query("SELECT seat_type, price FROM tblPrices")->fetch_all(MYSQLI_ASSOC) as $r)
    $prices[$r['seat_type']] = (int)$r['price'];

$typeInfo = [
    'standard' => ['label'=>'Ghế Standard', 'icon'=>'⬛', 'color'=>'#27ae60'],
    'vip'      => ['label'=>'Ghế VIP',      'icon'=>'⭐', 'color'=>'#f5c518'],
    'couple'   => ['label'=>'Ghế Couple',   'icon'=>'💑', 'color'=>'#9c88ff'],
];

renderHead('Quản lý giá vé');
?>
<style>
.prices-page {
  display: flex;
  justify-content: center;
  padding: 32px 24px;
}
.prices-inner { width: 100%; max-width: 560px; }
.prices-title {
  font-size: 22px; font-weight: 800; color: #fff;
  margin-bottom: 24px;
  display: flex; align-items: center; gap: 10px;
}
.prices-title::before {
  content: ''; display: block;
  width: 4px; height: 22px;
  background: #e8192c; border-radius: 2px;
}
.price-card {
  background: #1a1a1a;
  border: 1px solid #252525;
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
}
.price-row {
  display: flex; align-items: center;
  justify-content: space-between;
  gap: 16px; margin-bottom: 14px;
}
.price-label {
  display: flex; align-items: center; gap: 10px;
  font-size: 15px; font-weight: 600; color: #ccc;
}
.price-input-wrap { position: relative; width: 180px; }
.price-input-wrap input {
  width: 100%;
  background: #111;
  border: 1px solid #2a2a2a;
  border-radius: 8px;
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  padding: 10px 36px 10px 14px;
  font-family: inherit;
  transition: border-color .15s;
  text-align: right;
}
.price-input-wrap input:focus {
  outline: none;
  border-color: #e8192c;
}
.price-input-wrap .currency {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  color: #555; font-size: 13px; pointer-events: none;
}
.hint-box {
  background: #111;
  border: 1px solid #222;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 12px;
  color: #555;
  margin-bottom: 18px;
}
.btn-save {
  width: 100%;
  background: #e8192c;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s;
  box-shadow: 0 4px 14px rgba(232,25,44,.3);
}
.btn-save:hover { background: #c5101f; }

/* Preview section */
.preview-title {
  font-size: 14px; font-weight: 700; color: #555;
  margin: 28px 0 14px;
  text-transform: uppercase; letter-spacing: .5px;
}
.preview-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
.preview-card {
  background: #1a1a1a;
  border: 1px solid #222;
  border-radius: 10px;
  padding: 16px;
}
.preview-card-title {
  font-size: 12px; font-weight: 700;
  margin-bottom: 10px;
}
.preview-base { font-size: 13px; color: #888; margin-bottom: 6px; }
.preview-base strong { color: #ccc; }
.preview-disc { font-size: 12px; color: #e8192c; margin-top: 3px; }
.preview-na { font-size: 12px; color: #333; margin-top: 6px; }
</style>

<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-content">
<div class="prices-page">
<div class="prices-inner">

  <div class="prices-title">Quản lý giá vé</div>

  <?php if ($msg): ?>
  <div style="background:#0f2d1a;border:1px solid #1a4a2a;color:#4ade80;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;font-weight:500">
    ✓ <?= htmlspecialchars($msg) ?>
  </div>
  <?php endif; ?>

  <form method="post">
    <div class="price-card">
      <?php foreach ($typeInfo as $type => $info): ?>
      <div class="price-row">
        <div class="price-label">
          <span style="font-size:20px"><?= $info['icon'] ?></span>
          <div>
            <div style="color:<?= $info['color'] ?>;font-weight:700"><?= $info['label'] ?></div>
            <div style="font-size:11px;color:#555;margin-top:1px">Giá niêm yết</div>
          </div>
        </div>
        <div class="price-input-wrap">
          <input type="text" name="<?= $type ?>"
                 value="<?= number_format($prices[$type] ?? 0) ?>"
                 oninput="this.value=this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,',')">
          <span class="currency">đ</span>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="hint-box">
        💡 Flash Sale sẽ tự động tính % giảm dựa trên giá niêm yết này. Ghế Couple không áp dụng Flash Sale.
      </div>

      <button class="btn-save" type="submit">💾 Lưu giá vé</button>
    </div>
  </form>

  <!-- Preview -->
  <div class="preview-title">⚡ Xem trước giá sau Flash Sale</div>
  <div class="preview-grid">
    <?php foreach ($typeInfo as $type => $info):
      $base = $prices[$type] ?? 0; ?>
    <div class="preview-card">
      <div class="preview-card-title" style="color:<?= $info['color'] ?>">
        <?= $info['icon'] ?> <?= $info['label'] ?>
      </div>
      <div class="preview-base">Gốc: <strong><?= number_format($base) ?>đ</strong></div>
      <?php if ($type !== 'couple'): ?>
        <div class="preview-disc">⚡ -30%: <?= number_format(round($base*.7)) ?>đ</div>
        <div class="preview-disc">⚡ -50%: <?= number_format(round($base*.5)) ?>đ</div>
      <?php else: ?>
        <div class="preview-na">Không áp dụng</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

</div>
</div>
</div><!-- admin-content -->
</div><!-- admin-layout -->
<?php renderFooter(); ?>
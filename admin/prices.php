<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $types = ['standard', 'vip', 'couple'];
    foreach ($types as $type) {
        $price = (int)str_replace([',','.'], '', $_POST[$type] ?? 0);
        if ($price > 0) {
            $s = $db->prepare("UPDATE tblPrices SET price=? WHERE seat_type=?");
            $s->bind_param('is', $price, $type);
            $s->execute();
        }
    }
    $msg = 'Đã cập nhật giá vé.';
}

$prices = [];
$rows = $db->query("SELECT seat_type, price FROM tblPrices")->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) $prices[$r['seat_type']] = (int)$r['price'];

renderHead('Quản lý giá vé');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <h1 style="font-size:22px;font-weight:800;margin-bottom:24px">Quản lý giá vé</h1>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <div class="card" style="max-width:480px">
    <div class="card-body">
      <form method="post">
        <?php
        $typeInfo = [
          'standard' => ['label'=>'Ghế Standard','icon'=>'⬛','color'=>'#4caf50'],
          'vip'      => ['label'=>'Ghế VIP','icon'=>'⭐','color'=>'#f5c518'],
          'couple'   => ['label'=>'Ghế Couple','icon'=>'💑','color'=>'#9c88ff'],
        ];
        foreach ($typeInfo as $type => $info): ?>
        <div class="form-group">
          <label>
            <span style="color:<?= $info['color'] ?>"><?= $info['icon'] ?></span>
            <?= $info['label'] ?>
          </label>
          <div style="position:relative">
            <input class="form-control" type="text" name="<?= $type ?>"
                   value="<?= number_format($prices[$type] ?? 0) ?>"
                   style="padding-right:40px"
                   oninput="this.value=this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,',')">
            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#555;font-size:13px">đ</span>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="background:#1a1a1a;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px">
          <div class="text-muted">💡 Flash Sale sẽ tự động tính % giảm trên giá này</div>
        </div>
        <button class="btn btn-primary" style="width:100%" type="submit">Lưu giá vé</button>
      </form>
    </div>
  </div>

  <!-- Preview -->
  <div style="margin-top:24px">
    <h3 style="font-size:16px;margin-bottom:12px;color:#888">Xem trước giá sau Flash Sale</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <?php foreach (['standard','vip','couple'] as $type):
        $base = $prices[$type] ?? 0;
        $p30  = round($base * 0.7);
        $p50  = round($base * 0.5);
        $info = $typeInfo[$type];
      ?>
      <div class="card" style="padding:16px;min-width:160px">
        <div style="font-size:13px;color:<?= $info['color'] ?>;font-weight:700;margin-bottom:8px">
          <?= $info['icon'] ?> <?= $info['label'] ?>
        </div>
        <div style="font-size:13px">Gốc: <strong><?= number_format($base) ?>đ</strong></div>
        <?php if ($type !== 'couple'): ?>
        <div style="font-size:12px;color:#e50914">⚡ -30%: <?= number_format($p30) ?>đ</div>
        <div style="font-size:12px;color:#e50914">⚡ -50%: <?= number_format($p50) ?>đ</div>
        <?php else: ?>
        <div style="font-size:12px;color:#555">Không áp dụng Flash Sale</div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php renderFooter(); ?>

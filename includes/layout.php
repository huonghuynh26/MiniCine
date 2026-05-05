<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

function renderHead(string $title = 'MiniCine', string $extra = ''): void {
    $t    = htmlspecialchars($title);
    $base = APP_URL;
    $user = currentUser();

    // Script check session — inject vào MỌI trang, chạy ngay khi load
    $sessionCheckScript = '';
    if ($user && $user['role'] !== 'admin') {
        $sessionCheckScript = "
<script>
(function() {
  // Check ngay khi trang load
  async function checkSession() {
    try {
      const res  = await fetch('{$base}/api/session_check.php?_=' + Date.now());
      const data = await res.json();
      if (!data.ok && data.reason === 'locked') {
        alert('🔒 ' + (data.msg || 'Tài khoản đã bị khóa.'));
        window.location.href = '{$base}/login.php';
      }
    } catch(e) {}
  }

  // Chạy ngay khi DOM ready
  document.addEventListener('DOMContentLoaded', checkSession);

  // Và poll mỗi 15s
  setInterval(checkSession, 15000);

  // Và mỗi khi user click bất cứ đâu
  let lastCheck = 0;
  document.addEventListener('click', function() {
    const now = Date.now();
    if (now - lastCheck > 5000) { // throttle 5s để không spam
      lastCheck = now;
      checkSession();
    }
  }, true);
})();
</script>";
    }

    echo "<!DOCTYPE html>
<html lang=\"vi\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">
<title>{$t} – MiniCine</title>
<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap\" rel=\"stylesheet\">
<link rel=\"stylesheet\" href=\"" . parse_url($base, PHP_URL_PATH) . "/assets/css/main.css\">
{$extra}
</head>
<body>
{$sessionCheckScript}\n";
}

function renderNav(): void {
    $u    = currentUser();
    $base = APP_URL;
    $name = $u ? htmlspecialchars($u['name']) : '';
    $pts  = $u ? number_format($u['points'])  : '0';
    ?>
<nav class="navbar">
  <div class="navbar-inner">
    <a class="navbar-brand" href="<?= $base ?>/index.php">
      <div class="brand-icon">🎬</div>
      <span>MINI<em>CINE</em></span>
    </a>
    <a href="<?= $base ?>/booking.php" class="btn-buy-ticket">
      <span class="star-icon">★</span> Mua Vé
    </a>
    <ul class="nav-menu">
      <li class="has-dropdown">
        <a href="<?= $base ?>/star_shop.php">Star Shop <span class="chevron">▾</span></a>
        <div class="nav-dropdown">
          <a href="<?= $base ?>/star_shop.php?tab=combo">🍿 Combo Bắp & Nước</a>
          <a href="<?= $base ?>/star_shop.php?tab=merchandise">🛍️ Quà Lưu Niệm</a>
          <a href="<?= $base ?>/star_shop.php?tab=limited">⭐ Phiên Bản Giới Hạn</a>
        </div>
      </li>
      <?php if ($u && $u['role'] === 'admin'): ?>
      <li><a href="<?= $base ?>/admin/index.php">⚙ Admin</a></li>
      <?php endif; ?>
    </ul>
    <div class="nav-right">
      <button class="nav-icon-btn" title="Tìm kiếm">🔍</button>

      <?php if ($u): ?>
        <!-- Giỏ hàng -->
        <a href="<?= $base ?>/cart.php" class="nav-cart-btn" id="navCartBtn" title="Giỏ hàng">
          🛒
          <span class="nav-cart-count" id="navCartCount" style="display:none">0</span>
        </a>

        <!-- Avatar dropdown -->
        <div class="nav-avatar-wrap">
          <div class="nav-avatar" id="navAvatar">
            <div class="nav-avatar-circle"><?= mb_strtoupper(mb_substr($name,0,1)) ?></div>
          </div>
          <div class="nav-avatar-dropdown">
            <div class="nav-avatar-header">
              <div class="nav-avatar-circle-lg"><?= mb_strtoupper(mb_substr($name,0,1)) ?></div>
              <div>
                <div style="font-weight:700;font-size:14px;color:#222"><?= $name ?></div>
                <div style="font-size:12px;color:#f5a623;font-weight:600">⭐ <?= $pts ?> điểm Stars</div>
              </div>
            </div>
            <a href="<?= $base ?>/my_bookings.php" class="nav-avatar-item">🎟️ Lịch sử đặt vé</a>
            <a href="<?= $base ?>/cart.php" class="nav-avatar-item">🛒 Giỏ hàng</a>
            <?php if ($u['role'] === 'admin'): ?>
            <a href="<?= $base ?>/admin/index.php" class="nav-avatar-item">⚙️ Quản trị</a>
            <?php endif; ?>
            <a href="<?= $base ?>/logout.php" class="nav-avatar-item nav-avatar-logout">↩️ Đăng xuất</a>
          </div>
        </div>

      <?php else: ?>
        <a href="<?= $base ?>/login.php" class="nav-text-link">Đăng Nhập</a>
        <a href="<?= $base ?>/register.php" class="btn-nav-gstar"><span>★</span> Đăng Ký</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php
}

function renderFooter(): void {
    $base = APP_URL;
    $y    = date('Y');
    echo "<footer class=\"footer\"><p>© {$y} MiniCine · Rạp chiếu phim chất lượng cao · 4 tầng, 3 phòng chiếu</p></footer>\n";
    $cartCount = 0;
    if (isset($_SESSION['cart'])) {
        $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
    }
    echo "<script>
(function(){
  const badge = document.getElementById('navCartCount');
  if (badge) {
    const c = {$cartCount};
    if (c > 0) { badge.textContent = c; badge.style.display = 'flex'; }
  }
})();
</script>\n</body></html>";
}
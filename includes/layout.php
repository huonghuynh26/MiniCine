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

    echo "<nav class=\"navbar\">\n";
    echo "  <a class=\"navbar-brand\" href=\"{$base}/index.php\">MINI<span>CINE</span></a>\n";
    echo "  <div class=\"navbar-links\">\n";

    if ($u && $u['role'] === 'admin') {
        echo "    <a href='{$base}/admin/index.php'>⚙ Admin</a>\n";
    }

    if ($u) {
        echo "    <span class='nav-points'>⭐ {$pts} điểm</span>\n";
        echo "    <a href='{$base}/my_bookings.php'>Vé của tôi</a>\n";
        echo "    <a href='{$base}/logout.php' class='btn btn-outline'>Đăng xuất ({$name})</a>\n";
    } else {
        echo "    <a href='{$base}/login.php' class='btn btn-outline'>Đăng nhập</a>\n";
        echo "    <a href='{$base}/register.php' class='btn btn-primary'>Đăng ký</a>\n";
    }

    echo "  </div>\n</nav>\n";
}

function renderFooter(): void {
    $y = date('Y');
    echo "<footer class=\"footer\"><p>© {$y} MiniCine · Rạp chiếu phim chất lượng cao · 4 tầng, 3 phòng chiếu</p></footer>\n</body></html>";
}
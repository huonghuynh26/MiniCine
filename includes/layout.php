<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

function renderHead(string $title = 'MiniCine', string $extra = ''): void {
    $t    = htmlspecialchars($title);
    $base = APP_URL;

    // CSS path tuyệt đối từ server root
    $cssPath = parse_url($base, PHP_URL_PATH) . '/assets/css/main.css';

    echo "<!DOCTYPE html>
<html lang=\"vi\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">
<title>{$t} – MiniCine</title>
<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap\" rel=\"stylesheet\">
<link rel=\"stylesheet\" href=\"{$cssPath}\">
{$extra}
</head>
<body>\n";
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

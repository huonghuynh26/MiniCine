<?php
$cur = basename($_SERVER['PHP_SELF']);
$base = APP_URL . '/admin';
?>
<nav class="admin-sidebar">
  <a href="<?= $base ?>/index.php"    class="<?= $cur==='index.php'   ?'active':'' ?>">📊 Dashboard</a>
  <a href="<?= $base ?>/movies.php"   class="<?= $cur==='movies.php'  ?'active':'' ?>">🎬 Phim</a>
  <a href="<?= $base ?>/shows.php"    class="<?= $cur==='shows.php'   ?'active':'' ?>">🕐 Suất chiếu</a>
  <a href="<?= $base ?>/prices.php"   class="<?= $cur==='prices.php'  ?'active':'' ?>">💰 Giá vé</a>
  <a href="<?= $base ?>/flash.php"    class="<?= $cur==='flash.php'   ?'active':'' ?>">⚡ Flash Sale</a>
  <a href="<?= $base ?>/bookings.php" class="<?= $cur==='bookings.php'?'active':'' ?>">📋 Đơn đặt vé</a>
  <a href="<?= $base ?>/users.php"    class="<?= $cur==='users.php'   ?'active':'' ?>">👥 Khách hàng</a>
  <a href="<?= APP_URL ?>/index.php" style="margin-top:auto;color:#555">← Trang chủ</a>
</nav>

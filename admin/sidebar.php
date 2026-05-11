<?php
$cur  = basename($_SERVER['PHP_SELF']);
$base = APP_URL . '/admin';
?>
<style>
.admin-sidebar {
  width: 220px !important;
  min-width: 220px !important;
  background: #141414 !important;
  border-right: 1px solid #1f1f1f !important;
  padding: 12px !important;
  flex-shrink: 0 !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 2px !important;
  min-height: calc(100vh - 64px) !important;
}
.admin-sidebar .sb-section {
  font-size: 10px;
  font-weight: 700;
  color: #444;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  padding: 14px 10px 5px;
  display: block;
  cursor: default;
}
.admin-sidebar a {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  padding: 10px 14px !important;
  font-size: 13.5px !important;
  font-weight: 500 !important;
  color: #888 !important;
  border-radius: 8px !important;
  text-decoration: none !important;
  transition: all .15s !important;
  background: none !important;
  border: none !important;
  border-left: none !important;
}
.admin-sidebar a:hover {
  color: #fff !important;
  background: #222 !important;
  text-decoration: none !important;
}
.admin-sidebar a.active {
  color: #fff !important;
  background: #e8192c !important;
  font-weight: 600 !important;
  box-shadow: 0 2px 10px rgba(232,25,44,.35) !important;
}
.admin-sidebar .sb-divider {
  height: 1px;
  background: #1f1f1f;
  margin: 6px 0;
}
.admin-sidebar .sb-back {
  margin-top: auto !important;
  color: #444 !important;
  font-size: 12px !important;
  padding: 8px 14px !important;
}
.admin-sidebar .sb-back:hover {
  color: #777 !important;
  background: none !important;
  box-shadow: none !important;
}
.admin-layout {
  display: flex !important;
  background: #0f0f0f !important;
  min-height: calc(100vh - 64px) !important;
}
.admin-content {
  flex: 1 !important;
  padding: 28px 36px !important;
  overflow-x: auto !important;
}
</style>

<nav class="admin-sidebar">

  <span class="sb-section">Tổng quan</span>
  <a href="<?= $base ?>/index.php" class="<?= $cur==='index.php' ? 'active' : '' ?>">
    📊 <span>Dashboard</span>
  </a>

  <div class="sb-divider"></div>
  <span class="sb-section">Nội dung</span>
  <a href="<?= $base ?>/movies.php" class="<?= $cur==='movies.php' ? 'active' : '' ?>">
    🎬 <span>Phim</span>
  </a>
  <a href="<?= $base ?>/shows.php" class="<?= $cur==='shows.php' ? 'active' : '' ?>">
    🕐 <span>Suất chiếu</span>
  </a>

  <div class="sb-divider"></div>
  <span class="sb-section">Vận hành</span>
  <a href="<?= $base ?>/prices.php" class="<?= $cur==='prices.php' ? 'active' : '' ?>">
    💰 <span>Giá vé</span>
  </a>
  <a href="<?= $base ?>/flash.php" class="<?= $cur==='flash.php' ? 'active' : '' ?>">
    ⚡ <span>Flash Sale</span>
  </a>
  <a href="<?= $base ?>/bookings.php" class="<?= $cur==='bookings.php' ? 'active' : '' ?>">
    📋 <span>Đơn đặt vé</span>
  </a>
  <a href="<?= $base ?>/users.php" class="<?= $cur==='users.php' ? 'active' : '' ?>">
    👥 <span>Khách hàng</span>
  </a>

  <div class="sb-divider"></div>
  <a href="<?= APP_URL ?>/index.php" class="sb-back">
    ← Về trang chủ
  </a>

</nav>
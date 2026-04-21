<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$user = currentUser();
$bookingId = (int)($_GET['booking_id'] ?? 0);
$db = db();

$stmt = $db->prepare("
    SELECT b.*, m.title as movie_title, s.start_time, r.name as room_name
    FROM tblBookings b
    JOIN tblShows  s ON s.id = b.show_id
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms  r ON r.id = s.room_id
    WHERE b.id=? AND b.user_id=?
");
$stmt->bind_param('ii', $bookingId, $user['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) { header('Location: ' . APP_URL); exit; }

$stmt = $db->prepare("
    SELECT bi.*, se.row, se.number, se.type
    FROM tblBookingItems bi
    JOIN tblSeats se ON se.id = bi.seat_id
    WHERE bi.booking_id=?
");
$stmt->bind_param('i', $bookingId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total points earned
$pts = $db->prepare("
    SELECT SUM(points_delta) as pts FROM tblPointsLog WHERE booking_id=?
");
$pts->bind_param('i', $bookingId);
$pts->execute();
$earnedPts = (int)$pts->get_result()->fetch_assoc()['pts'];

renderHead('Đặt vé thành công!');
?>
<?php renderNav(); ?>

<div class="section">
<div class="booking-success">
  <div class="checkmark">🎉</div>
  <h2>Đặt vé thành công!</h2>
  <p class="text-muted">Email xác nhận đã được gửi đến <strong><?= htmlspecialchars($user['email']) ?></strong></p>

  <div class="card mt-3" style="text-align:left;padding:20px">
    <div class="price-row">
      <span class="label">Phim</span>
      <strong><?= htmlspecialchars($booking['movie_title']) ?></strong>
    </div>
    <div class="price-row">
      <span class="label">Suất chiếu</span>
      <span><?= date('H:i d/m/Y', strtotime($booking['start_time'])) ?></span>
    </div>
    <div class="price-row">
      <span class="label">Phòng</span>
      <span><?= htmlspecialchars($booking['room_name']) ?></span>
    </div>
    <div class="price-row">
      <span class="label">Ghế</span>
      <span>
        <?php foreach ($items as $item):
          $label = $item['type'] === 'couple'
            ? "{$item['row']}" . ($item['number']*2-1) . "-{$item['row']}{$item['number']}c"
            : "{$item['row']}{$item['number']}";
          echo "<span class='badge' style='background:#1a2a1a;color:#4caf50;margin:2px'>{$label}</span>";
        endforeach; ?>
      </span>
    </div>
    <div class="price-row total">
      <span class="label">Tổng tiền</span>
      <span class="amount"><?= number_format($booking['total_price']) ?>đ</span>
    </div>
  </div>

  <!-- QR Code -->
  <div class="qr-container mt-3">
    <svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180">
      <rect width="180" height="180" fill="#fff"/>
      <!-- QR pattern placeholder - replace with real QR library -->
      <?php
      // Simple visual pattern based on booking QR code
      $qr = $booking['qr_code'];
      srand(crc32($qr));
      for ($row = 0; $row < 8; $row++) {
          for ($col = 0; $col < 8; $col++) {
              if (rand(0,1)) {
                  $x = 20 + $col * 18;
                  $y = 20 + $row * 18;
                  echo "<rect x='{$x}' y='{$y}' width='16' height='16' fill='#000'/>";
              }
          }
      }
      ?>
      <text x="90" y="168" text-anchor="middle" font-family="monospace" font-size="9" fill="#333">
        <?= htmlspecialchars($qr) ?>
      </text>
    </svg>
  </div>
  <p class="text-muted" style="font-size:13px">Xuất trình mã QR này tại cửa rạp</p>
  <p class="text-gold mt-1">⭐ +<?= $earnedPts ?> điểm thưởng đã được cộng vào tài khoản</p>

  <div class="mt-3" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/my_bookings.php" class="btn btn-outline">📋 Xem tất cả vé</a>
    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary">🎬 Đặt vé tiếp</a>
  </div>
</div>
</div>

<?php renderFooter(); ?>

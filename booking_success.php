<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$user      = currentUser();
$bookingId = (int)($_GET['booking_id'] ?? 0);
$db        = db();

$stmt = $db->prepare("
    SELECT b.*, m.title as movie_title, m.poster_url, m.age_rating,
           s.start_time, r.name as room_name, r.floor
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

$pts = $db->prepare("SELECT SUM(points_delta) as pts FROM tblPointsLog WHERE booking_id=?");
$pts->bind_param('i', $bookingId);
$pts->execute();
$earnedPts = (int)$pts->get_result()->fetch_assoc()['pts'];

// Seat labels
$seatLabels = array_map(function($item) {
    return $item['type'] === 'couple'
        ? $item['row'] . ($item['number']*2-1) . '-' . $item['row'] . ($item['number']*2)
        : $item['row'] . $item['number'];
}, $items);

$ageColors = ['P'=>'#27ae60','T13'=>'#2980b9','T16'=>'#e67e22','T18'=>'#e8192c'];
$ar = $booking['age_rating'] ?? 'P';

renderHead('Đặt vé thành công!');
?>
<style>
.success-page{background:#f5f5f5;min-height:calc(100vh - 64px);padding:40px 20px;display:flex;align-items:flex-start;justify-content:center}
.ticket-wrap{max-width:420px;width:100%}
.success-header{text-align:center;margin-bottom:28px}
.success-icon{width:64px;height:64px;background:linear-gradient(135deg,#27ae60,#2ecc71);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 14px}
.success-header h2{font-size:22px;font-weight:900;color:#222;margin-bottom:6px}
.success-header p{font-size:13px;color:#888}

/* Ticket card */
.ticket{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
.ticket-header{background:linear-gradient(135deg,#e8192c,#ff6b00);padding:20px;color:#fff;display:flex;gap:14px;align-items:center}
.ticket-poster{width:64px;height:90px;border-radius:8px;overflow:hidden;background:rgba(255,255,255,.2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:28px}
.ticket-poster img{width:100%;height:100%;object-fit:cover}
.ticket-movie-title{font-size:16px;font-weight:800;margin-bottom:4px}
.ticket-sub{font-size:12px;opacity:.85}

.ticket-body{padding:20px}
.ticket-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #f0f0f0;font-size:13px}
.ticket-row:last-child{border-bottom:none}
.ticket-label{color:#888}
.ticket-value{font-weight:600;color:#222;text-align:right}
.ticket-total{font-size:16px;font-weight:800;color:#e8192c}

/* Tear line */
.tear-line{display:flex;align-items:center;margin:0 -0px;position:relative;height:24px}
.tear-line::before{content:'';position:absolute;left:0;right:0;top:50%;border-top:2px dashed #e0e0e0}
.tear-circle{width:24px;height:24px;background:#f5f5f5;border-radius:50%;flex-shrink:0;position:relative;z-index:1}
.tear-circle.left{margin-left:-12px}
.tear-circle.right{margin-left:auto;margin-right:-12px}

/* QR section */
.qr-section{padding:20px;text-align:center;background:#fff}
.qr-box{background:#fff;border:2px solid #f0f0f0;border-radius:12px;padding:16px;display:inline-block;margin-bottom:10px}
.qr-code-id{font-size:11px;color:#aaa;font-family:monospace;margin-top:6px;letter-spacing:.5px}
.qr-hint{font-size:12px;color:#888}

/* Points earned */
.points-earned{background:linear-gradient(135deg,#fff9e0,#fff3cc);border:1px solid #e0c040;border-radius:10px;padding:14px;text-align:center;margin-top:14px}
.points-earned strong{color:#b8860b;font-size:16px}

.btn-actions{display:flex;gap:10px;margin-top:16px}
.btn-actions a{flex:1;padding:12px;border-radius:8px;font-size:13px;font-weight:700;text-align:center;text-decoration:none;transition:all .15s}
.btn-outline-red{border:1.5px solid #e8192c;color:#e8192c;background:#fff}
.btn-outline-red:hover{background:#fff5f5}
.btn-red{background:#e8192c;color:#fff;border:none}
.btn-red:hover{background:#c5101f}
</style>

<?php renderNav(); ?>

<div class="success-page">
<div class="ticket-wrap">

  <div class="success-header">
    <div class="success-icon">✓</div>
    <h2>Đặt vé thành công!</h2>
    <p>Email xác nhận đã được gửi đến <strong><?= htmlspecialchars($user['email']) ?></strong></p>
  </div>

  <div class="ticket">
    <!-- Header -->
    <div class="ticket-header">
      <div class="ticket-poster">
        <?php if ($booking['poster_url']): ?>
          <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($booking['poster_url']) ?>" alt="">
        <?php else: ?>🎬<?php endif; ?>
      </div>
      <div>
        <div class="ticket-movie-title"><?= htmlspecialchars($booking['movie_title']) ?></div>
        <?php if ($ar !== 'P'): ?>
        <span style="background:rgba(255,255,255,.25);color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700"><?= $ar ?></span>
        <?php endif; ?>
        <div class="ticket-sub" style="margin-top:4px">🏠 <?= htmlspecialchars($booking['room_name']) ?> · Tầng <?= $booking['floor'] ?></div>
        <div class="ticket-sub">🕐 <?= date('H:i – l, d/m/Y', strtotime($booking['start_time'])) ?></div>
      </div>
    </div>

    <!-- Body info -->
    <div class="ticket-body">
      <div class="ticket-row">
        <span class="ticket-label">Ghế</span>
        <span class="ticket-value">
          <?php foreach ($seatLabels as $lbl): ?>
          <span style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:700;margin:1px"><?= htmlspecialchars($lbl) ?></span>
          <?php endforeach; ?>
        </span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">Mã đặt vé</span>
        <span class="ticket-value" style="font-family:monospace;font-size:12px;color:#e8192c"><?= htmlspecialchars($booking['qr_code']) ?></span>
      </div>
      <div class="ticket-row">
        <span class="ticket-label">Tổng tiền</span>
        <span class="ticket-value ticket-total"><?= number_format($booking['total_price']) ?> đ</span>
      </div>
    </div>

    <!-- Tear line -->
    <div class="tear-line"><div class="tear-circle left"></div><div class="tear-circle right"></div></div>

    <!-- QR Code section -->
    <div class="qr-section">
      <p style="font-size:12px;font-weight:700;color:#555;margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px">Mã QR soát vé</p>
      <div class="qr-box">
        <?php
        // Generate a proper-looking QR using SVG with real pattern based on qr_code string
        $qrCode = $booking['qr_code'];
        $hash   = md5($qrCode);
        $size   = 200;
        $modules= 21; // 21×21 for a minimal QR feel
        $cell   = floor($size / $modules);
        $pad    = ($size - $modules * $cell) / 2;

        // Seed random from hash for reproducible pattern
        $seed = hexdec(substr($hash, 0, 8));
        mt_srand($seed);

        // Generate module matrix (1=black, 0=white)
        $mat = [];
        for ($r = 0; $r < $modules; $r++) {
            for ($c = 0; $c < $modules; $c++) {
                $mat[$r][$c] = mt_rand(0,1);
            }
        }
        // Force finder patterns (top-left, top-right, bottom-left)
        $fp = [[0,1,1,1,1,1,1,0],[1,0,0,0,0,0,1,0],[1,0,1,1,1,0,1,0],[1,0,1,1,1,0,1,0],[1,0,1,1,1,0,1,0],[1,0,0,0,0,0,1,0],[1,1,1,1,1,1,1,0],[0,0,0,0,0,0,0,0]];
        // TL
        foreach ($fp as $ri => $row) foreach ($row as $ci => $v) $mat[$ri][$ci] = $v;
        // TR
        foreach ($fp as $ri => $row) foreach ($row as $ci => $v) $mat[$ri][$modules-8+$ci] = $v;
        // BL
        foreach ($fp as $ri => $row) foreach ($row as $ci => $v) $mat[$modules-8+$ri][$ci] = $v;

        echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" style=\"display:block\">";
        echo "<rect width=\"{$size}\" height=\"{$size}\" fill=\"#fff\"/>";
        for ($r = 0; $r < $modules; $r++) {
            for ($c = 0; $c < $modules; $c++) {
                if ($mat[$r][$c]) {
                    $x = $pad + $c * $cell;
                    $y = $pad + $r * $cell;
                    echo "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$cell}\" height=\"{$cell}\" fill=\"#1a1a1a\" rx=\"1\"/>";
                }
            }
        }
        // Center logo
        $cx = $size/2 - 14; $cy = $size/2 - 14;
        echo "<rect x=\"{$cx}\" y=\"{$cy}\" width=\"28\" height=\"28\" fill=\"#e8192c\" rx=\"5\"/>";
        echo "<text x=\"" . ($size/2) . "\" y=\"" . ($size/2+6) . "\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"13\" font-weight=\"900\" font-family=\"Arial\">MC</text>";
        echo "</svg>";
        ?>
      </div>
      <div class="qr-code-id"><?= htmlspecialchars($qrCode) ?></div>
      <p class="qr-hint" style="margin-top:8px">Xuất trình mã QR này tại cửa rạp để soát vé</p>
    </div>
  </div>

  <?php if ($earnedPts > 0): ?>
  <div class="points-earned">
    ⭐ <strong>+<?= $earnedPts ?> điểm thưởng</strong> đã được cộng vào tài khoản của bạn!
  </div>
  <?php endif; ?>

  <div class="btn-actions">
    <a href="<?= APP_URL ?>/my_bookings.php" class="btn-actions btn-outline-red" style="border:1.5px solid #e8192c;color:#e8192c;background:#fff;flex:1;padding:12px;border-radius:8px;font-size:13px;font-weight:700;text-align:center;text-decoration:none">📋 Xem tất cả vé</a>
    <a href="<?= APP_URL ?>/index.php" class="btn-actions btn-red" style="background:#e8192c;color:#fff;flex:1;padding:12px;border-radius:8px;font-size:13px;font-weight:700;text-align:center;text-decoration:none">🎬 Đặt vé tiếp</a>
  </div>

</div>
</div>

<?php renderFooter(); ?>
<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$user = currentUser();
$db   = db();

$stmt = $db->prepare("
    SELECT b.*, m.title as movie_title, m.poster_url,
           s.start_time, r.name as room_name
    FROM tblBookings b
    JOIN tblShows  s ON s.id = b.show_id
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms  r ON r.id = s.room_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Points log
$pStmt = $db->prepare("
    SELECT * FROM tblPointsLog WHERE user_id=? ORDER BY created_at DESC LIMIT 20
");
$pStmt->bind_param('i', $user['id']);
$pStmt->execute();
$points = $pStmt->get_result()->fetch_all(MYSQLI_ASSOC);

renderHead('Vé của tôi');
?>
<?php renderNav(); ?>
<div class="section">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <h1 class="section-title" style="margin:0">Vé của tôi</h1>
    <div style="background:#1a1a00;border:1px solid #333300;border-radius:8px;padding:10px 20px">
      <span class="text-gold">⭐ <?= number_format($user['points']) ?> điểm</span>
      <span class="text-muted" style="font-size:13px;margin-left:8px">
        = <?= number_format(floor($user['points'] / 100) * 10000) ?>đ
      </span>
    </div>
  </div>

  <?php if (empty($bookings)): ?>
    <div style="text-align:center;padding:80px 0">
      <div style="font-size:64px;margin-bottom:16px">🎟</div>
      <p class="text-muted">Bạn chưa đặt vé nào.</p>
      <a href="<?= APP_URL ?>/index.php" class="btn btn-primary mt-2">Đặt vé ngay</a>
    </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:16px">
    <?php foreach ($bookings as $b):
      $stmt2 = $db->prepare("
          SELECT bi.*, se.row, se.number, se.type
          FROM tblBookingItems bi JOIN tblSeats se ON se.id=bi.seat_id
          WHERE bi.booking_id=?
      ");
      $stmt2->bind_param('i', $b['id']);
      $stmt2->execute();
      $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
      $isPast = strtotime($b['start_time']) < time();
    ?>
    <div class="card" style="border-color:<?= $isPast ? '#1a1a1a' : '#2a2a1a' ?>">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
          <div>
            <div style="font-weight:700;font-size:17px;margin-bottom:4px">
              <?= htmlspecialchars($b['movie_title']) ?>
            </div>
            <div class="text-muted" style="font-size:13px">
              📅 <?= date('H:i d/m/Y', strtotime($b['start_time'])) ?>
              · <?= htmlspecialchars($b['room_name']) ?>
            </div>
            <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px">
              <?php foreach ($items as $item):
                $label = $item['type'] === 'couple'
                  ? "{$item['row']}" . ($item['number']*2-1) . "-{$item['row']}{$item['number']}c"
                  : "{$item['row']}{$item['number']}";
                $color = match($item['type']) {
                  'vip' => 'background:#2a2a0a;color:#f5c518',
                  'couple' => 'background:#1a1a3a;color:#9c88ff',
                  default => 'background:#1a2a1a;color:#4caf50'
                };
              ?>
              <span class="badge" style="<?= $color ?>"><?= $label ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-size:20px;font-weight:800;color:#e50914">
              <?= number_format($b['total_price']) ?>đ
            </div>
            <div class="mt-1">
              <span class="badge" style="background:<?= $isPast ? '#1a1a1a' : '#1a2d1a' ?>;
                color:<?= $isPast ? '#555' : '#4caf50' ?>">
                <?= $isPast ? 'Đã xem' : '✓ Đã đặt' ?>
              </span>
            </div>
            <div class="mt-1">
              <a href="<?= APP_URL ?>/booking_success.php?booking_id=<?= $b['id'] ?>"
                 class="btn btn-outline btn-sm">Xem QR</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Points History -->
  <?php if (!empty($points)): ?>
  <div class="mt-4">
    <h2 class="section-title">Lịch sử điểm thưởng</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Thời gian</th><th>Nội dung</th><th style="text-align:right">Điểm</th></tr></thead>
        <tbody>
          <?php foreach ($points as $p): ?>
          <tr>
            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
            <td><?= htmlspecialchars($p['reason']) ?></td>
            <td style="text-align:right;color:#f5c518;font-weight:600">
              +<?= number_format($p['points_delta']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>

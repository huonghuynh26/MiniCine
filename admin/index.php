<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db = db();

// Stats
$today    = date('Y-m-d');
$thisWeek = date('Y-m-d', strtotime('monday this week'));
$thisMonth= date('Y-m-01');

function revenue(mysqli $db, string $from, string $to = null): int {
    $to = $to ?: date('Y-m-d');
    $r = $db->query("
        SELECT COALESCE(SUM(total_price),0) v FROM tblBookings
        WHERE payment_status='paid'
        AND DATE(created_at) BETWEEN '{$from}' AND '{$to}'
    ")->fetch_assoc();
    return (int)$r['v'];
}

$revToday  = revenue($db, $today);
$revWeek   = revenue($db, $thisWeek);
$revMonth  = revenue($db, $thisMonth);
$totalBook = (int)$db->query("SELECT COUNT(*) v FROM tblBookings WHERE payment_status='paid'")->fetch_assoc()['v'];
$totalUsers= (int)$db->query("SELECT COUNT(*) v FROM tblUsers WHERE role='customer'")->fetch_assoc()['v'];
$totalMovies=(int)$db->query("SELECT COUNT(*) v FROM tblMovies WHERE status='showing'")->fetch_assoc()['v'];

// Revenue by seat type
$seatRev = $db->query("
    SELECT s.type, SUM(bi.price) as rev, COUNT(*) as cnt
    FROM tblBookingItems bi JOIN tblSeats s ON s.id=bi.seat_id
    GROUP BY s.type
")->fetch_all(MYSQLI_ASSOC);

// Recent bookings
$recent = $db->query("
    SELECT b.id, b.total_price, b.created_at, u.full_name, u.email,
           m.title as movie_title, r.name as room_name, s.start_time
    FROM tblBookings b
    JOIN tblUsers  u ON u.id = b.user_id
    JOIN tblShows  s ON s.id = b.show_id
    JOIN tblMovies m ON m.id = s.movie_id
    JOIN tblRooms  r ON r.id = s.room_id
    WHERE b.payment_status='paid'
    ORDER BY b.created_at DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Daily revenue last 7 days
$daily = $db->query("
    SELECT DATE(created_at) as d, SUM(total_price) as rev
    FROM tblBookings WHERE payment_status='paid'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY d ASC
")->fetch_all(MYSQLI_ASSOC);

renderHead('Admin Dashboard');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">Dashboard</h1>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card"><div class="label">Doanh thu hôm nay</div><div class="value red"><?= number_format($revToday) ?>đ</div></div>
    <div class="stat-card"><div class="label">Doanh thu tuần này</div><div class="value red"><?= number_format($revWeek) ?>đ</div></div>
    <div class="stat-card"><div class="label">Doanh thu tháng này</div><div class="value red"><?= number_format($revMonth) ?>đ</div></div>
    <div class="stat-card"><div class="label">Tổng đơn đặt vé</div><div class="value gold"><?= number_format($totalBook) ?></div></div>
    <div class="stat-card"><div class="label">Khách hàng</div><div class="value green"><?= number_format($totalUsers) ?></div></div>
    <div class="stat-card"><div class="label">Phim đang chiếu</div><div class="value"><?= $totalMovies ?></div></div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:32px">
    <!-- Revenue chart -->
    <div class="card">
      <div class="card-body">
        <div style="font-weight:700;margin-bottom:16px">Doanh thu 7 ngày gần nhất</div>
        <canvas id="revenueChart" height="80"></canvas>
      </div>
    </div>
    <!-- Seat type revenue -->
    <div class="card">
      <div class="card-body">
        <div style="font-weight:700;margin-bottom:16px">Doanh thu theo loại ghế</div>
        <?php foreach ($seatRev as $sr):
          $color = match($sr['type']) { 'vip'=>'#f5c518','couple'=>'#9c88ff',default=>'#4caf50' };
          $label = match($sr['type']) { 'vip'=>'VIP','couple'=>'Couple',default=>'Standard' };
        ?>
        <div style="margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
            <span style="color:<?= $color ?>"><?= $label ?></span>
            <span class="text-muted"><?= $sr['cnt'] ?> vé · <?= number_format($sr['rev']) ?>đ</span>
          </div>
          <div style="background:#1a1a1a;border-radius:4px;height:6px">
            <div style="background:<?= $color ?>;height:6px;border-radius:4px;
              width:<?= $seatRev ? min(100, round($sr['rev'] / max(array_column($seatRev,'rev')) * 100)) : 0 ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Recent bookings -->
  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div style="font-weight:700">Đơn đặt vé gần đây</div>
        <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline btn-sm">Xem tất cả</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Khách hàng</th><th>Phim</th><th>Suất</th><th style="text-align:right">Tổng tiền</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $b): ?>
            <tr>
              <td class="text-muted">#<?= $b['id'] ?></td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($b['full_name']) ?></div>
                <div class="text-muted" style="font-size:12px"><?= htmlspecialchars($b['email']) ?></div>
              </td>
              <td><?= htmlspecialchars($b['movie_title']) ?></td>
              <td class="text-muted"><?= date('H:i d/m', strtotime($b['start_time'])) ?> · <?= $b['room_name'] ?></td>
              <td style="text-align:right;font-weight:700;color:#e50914"><?= number_format($b['total_price']) ?>đ</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_column($daily, 'd')) ?>;
const data   = <?= json_encode(array_map(fn($r) => (int)$r['rev'], $daily)) ?>;
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: labels.map(d => new Date(d).toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'})),
    datasets: [{
      label: 'Doanh thu',
      data,
      backgroundColor: '#e50914cc',
      borderColor: '#e50914',
      borderWidth: 1,
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#666' }, grid: { color: '#1a1a1a' } },
      y: { ticks: { color: '#666', callback: v => (v/1000)+'k' }, grid: { color: '#1a1a1a' } }
    }
  }
});
</script>
<?php renderFooter(); ?>

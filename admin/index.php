<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db = db();

$today    = date('Y-m-d');
$thisWeek = date('Y-m-d', strtotime('monday this week'));
$thisMonth= date('Y-m-01');

function revenue(mysqli $db, string $from, string $to = null): int {
    $to = $to ?: date('Y-m-d');
    return (int)$db->query("
        SELECT COALESCE(SUM(total_price),0) v FROM tblBookings
        WHERE payment_status='paid'
        AND DATE(created_at) BETWEEN '{$from}' AND '{$to}'
    ")->fetch_assoc()['v'];
}

$revToday   = revenue($db, $today);
$revWeek    = revenue($db, $thisWeek);
$revMonth   = revenue($db, $thisMonth);
$totalBook  = (int)$db->query("SELECT COUNT(*) v FROM tblBookings WHERE payment_status='paid'")->fetch_assoc()['v'];
$totalUsers = (int)$db->query("SELECT COUNT(*) v FROM tblUsers WHERE role='customer'")->fetch_assoc()['v'];
$totalMovies= (int)$db->query("SELECT COUNT(*) v FROM tblMovies WHERE status='showing'")->fetch_assoc()['v'];

$seatRev = $db->query("
    SELECT s.type, SUM(bi.price) as rev, COUNT(*) as cnt
    FROM tblBookingItems bi JOIN tblSeats s ON s.id=bi.seat_id
    GROUP BY s.type
")->fetch_all(MYSQLI_ASSOC);

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

$daily = $db->query("
    SELECT DATE(created_at) as d, SUM(total_price) as rev
    FROM tblBookings WHERE payment_status='paid'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY d ASC
")->fetch_all(MYSQLI_ASSOC);

renderHead('Admin Dashboard');
?>
<style>
body { background: #f5f6fa !important; }

/* Cards */
.db-card {
  background: #1a1a1a;
  border-radius: 12px;
  border: 1px solid #252525;
  box-shadow: 0 2px 8px rgba(0,0,0,.04);
  overflow: hidden;
  margin-bottom: 20px;
}
.db-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #222;
}
.db-card-title {
  font-size: 14px;
  font-weight: 700;
  color: #e0e0e0;
}
.db-card-body { padding: 20px; }

/* Seat revenue bars */
.seat-bar-row { margin-bottom: 14px; }
.seat-bar-label { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
.seat-bar-name { font-weight: 600; color: #ccc; }
.seat-bar-meta { color: #999; font-size: 12px; }
.seat-bar-track { background: #f0f0f0; border-radius: 6px; height: 8px; }
.seat-bar-fill  { height: 8px; border-radius: 6px; }

/* Table */
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th {
  text-align: left; padding: 10px 14px;
  font-size: 11px; font-weight: 700; color: #555;
  text-transform: uppercase; letter-spacing: .5px;
  border-bottom: 1px solid #f0f0f0;
  background: #141414;
}
.admin-table td {
  padding: 12px 14px;
  font-size: 13px; color: #ccc;
  border-bottom: 1px solid #f8f8f8;
  vertical-align: middle;
}
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #141414; }
.td-id { color: #bbb; font-size: 12px; font-family: monospace; }
.td-name { font-weight: 600; color: #e0e0e0; }
.td-sub { font-size: 11px; color: #555; margin-top: 2px; }
.td-amount { font-weight: 700; color: #e8192c; text-align: right; }
</style>

<?php renderNav(); ?>

<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-content">
  <div class="admin-page-title">Dashboard</div>

  <!-- Stats -->
  <div class="stats-grid" style="display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:24px">
    <?php
    $cards = [
      ['💰','Doanh thu hôm nay', number_format($revToday).'đ',  '#e8192c'],
      ['📅','Doanh thu tuần này', number_format($revWeek).'đ',   '#e8192c'],
      ['📈','Doanh thu tháng',    number_format($revMonth).'đ',  '#e8192c'],
      ['🎟️','Tổng đơn đặt vé',   number_format($totalBook),     '#f5c518'],
      ['👥','Khách hàng',         number_format($totalUsers),    '#27ae60'],
      ['🎬','Phim đang chiếu',    $totalMovies,                  '#4fa3e0'],
    ];
    foreach ($cards as [$icon,$label,$val,$color]):
    ?>
    <div style="background:#1a1a1a;border:1px solid #252525;border-radius:12px;padding:18px 16px;position:relative;overflow:hidden;transition:transform .15s;cursor:default"
         onmouseenter="this.style.transform='translateY(-2px)'" onmouseleave="this.style.transform=''">
      <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $color ?>;border-radius:12px 12px 0 0"></div>
      <div style="font-size:22px;margin-bottom:10px;opacity:.7"><?= $icon ?></div>
      <div style="font-size:11px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px"><?= $label ?></div>
      <div style="font-size:22px;font-weight:800;color:<?= $color ?>;line-height:1"><?= $val ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts row -->
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">

    <!-- Revenue chart -->
    <div class="db-card">
      <div class="db-card-header">
        <div class="db-card-title">📊 Doanh thu 7 ngày gần nhất</div>
      </div>
      <div class="db-card-body">
        <canvas id="revenueChart" height="100"></canvas>
      </div>
    </div>

    <!-- Seat type revenue -->
    <div class="db-card">
      <div class="db-card-header">
        <div class="db-card-title">🪑 Doanh thu theo loại ghế</div>
      </div>
      <div class="db-card-body">
        <?php
        $maxRev = $seatRev ? max(array_column($seatRev,'rev')) : 1;
        $seatConfig = ['standard'=>['Standard','#27ae60'],'vip'=>['VIP','#e6a817'],'couple'=>['Couple','#9c88ff']];
        foreach ($seatRev as $sr):
            [$label,$color] = $seatConfig[$sr['type']] ?? [$sr['type'],'#aaa'];
            $pct = $maxRev ? round($sr['rev']/$maxRev*100) : 0;
        ?>
        <div class="seat-bar-row">
          <div class="seat-bar-label">
            <span class="seat-bar-name" style="color:<?= $color ?>"><?= $label ?></span>
            <span class="seat-bar-meta"><?= $sr['cnt'] ?> vé · <?= number_format($sr['rev']) ?>đ</span>
          </div>
          <div class="seat-bar-track">
            <div class="seat-bar-fill" style="background:<?= $color ?>;width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($seatRev)): ?>
        <p style="color:#bbb;font-size:13px;text-align:center;padding:20px 0">Chưa có dữ liệu</p>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Recent bookings -->
  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title">🎟️ Đơn đặt vé gần đây</div>
      <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline btn-sm">Xem tất cả →</a>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Khách hàng</th>
            <th>Phim</th>
            <th>Suất chiếu</th>
            <th style="text-align:right">Tổng tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $b): ?>
          <tr>
            <td class="td-id">#<?= $b['id'] ?></td>
            <td>
              <div class="td-name"><?= htmlspecialchars($b['full_name']) ?></div>
              <div class="td-sub"><?= htmlspecialchars($b['email']) ?></div>
            </td>
            <td><?= htmlspecialchars($b['movie_title']) ?></td>
            <td style="color:#888;font-size:12px">
              <?= date('H:i d/m', strtotime($b['start_time'])) ?><br>
              <span style="color:#bbb"><?= $b['room_name'] ?></span>
            </td>
            <td class="td-amount"><?= number_format($b['total_price']) ?>đ</td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent)): ?>
          <tr><td colspan="5" style="text-align:center;color:#bbb;padding:32px">Chưa có đơn đặt vé nào</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- admin-content -->
</div><!-- admin-layout -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_column($daily,'d')) ?>;
const data   = <?= json_encode(array_map(fn($r)=>(int)$r['rev'], $daily)) ?>;
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: labels.map(d => new Date(d+'T00:00:00').toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'})),
    datasets: [{
      label: 'Doanh thu',
      data,
      backgroundColor: 'rgba(232,25,44,.12)',
      borderColor: '#e8192c',
      borderWidth: 2,
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#aaa', font:{size:12} }, grid: { color: '#1f1f1f' } },
      y: { ticks: { color: '#aaa', font:{size:12}, callback: v => (v/1000)+'k' }, grid: { color: '#1f1f1f' } }
    }
  }
});
</script>
<?php renderFooter(); ?>
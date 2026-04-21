<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db = db();

// Filters
$filterDate  = $_GET['date']  ?? '';
$filterMovie = (int)($_GET['movie'] ?? 0);
$filterRoom  = (int)($_GET['room'] ?? 0);
$search      = trim($_GET['q'] ?? '');

$where = ["b.payment_status='paid'"];
$params = []; $types = '';

if ($filterDate) { $where[] = "DATE(b.created_at)=?"; $params[] = $filterDate; $types .= 's'; }
if ($filterMovie){ $where[] = "s.movie_id=?";          $params[] = $filterMovie; $types .= 'i'; }
if ($filterRoom) { $where[] = "s.room_id=?";           $params[] = $filterRoom;  $types .= 'i'; }
if ($search)     { $where[] = "(u.email LIKE ? OR u.full_name LIKE ?)";
                   $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }

$wSql = implode(' AND ', $where);
$sql = "
    SELECT b.id, b.total_price, b.qr_code, b.created_at,
           u.full_name, u.email,
           m.title as movie_title, r.name as room_name, s.start_time,
           (SELECT GROUP_CONCAT(CONCAT(se.row,se.number) ORDER BY se.row,se.number SEPARATOR ', ')
            FROM tblBookingItems bi JOIN tblSeats se ON se.id=bi.seat_id
            WHERE bi.booking_id=b.id) as seats
    FROM tblBookings b
    JOIN tblUsers  u ON u.id=b.user_id
    JOIN tblShows  s ON s.id=b.show_id
    JOIN tblMovies m ON m.id=s.movie_id
    JOIN tblRooms  r ON r.id=s.room_id
    WHERE {$wSql}
    ORDER BY b.created_at DESC
    LIMIT 200
";
$stmt = $db->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalRev = array_sum(array_column($bookings, 'total_price'));

// For filter dropdowns
$movies = $db->query("SELECT id,title FROM tblMovies ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$rooms  = $db->query("SELECT * FROM tblRooms ORDER BY floor")->fetch_all(MYSQLI_ASSOC);

renderHead('Đơn đặt vé');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <h1 style="font-size:22px;font-weight:800;margin-bottom:16px">📋 Đơn đặt vé</h1>

  <!-- Filters -->
  <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px">
    <input class="form-control" type="text" name="q" value="<?= htmlspecialchars($search) ?>"
           placeholder="Tìm tên, email..." style="width:200px">
    <input class="form-control" type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" style="width:160px">
    <select class="form-control" name="movie" style="width:180px">
      <option value="">Tất cả phim</option>
      <?php foreach ($movies as $m): ?>
      <option value="<?= $m['id'] ?>" <?= $filterMovie==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="form-control" name="room" style="width:140px">
      <option value="">Tất cả phòng</option>
      <?php foreach ($rooms as $r): ?>
      <option value="<?= $r['id'] ?>" <?= $filterRoom==$r['id']?'selected':'' ?>><?= $r['name'] ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Lọc</button>
    <a href="?" class="btn btn-outline">Xoá lọc</a>
  </form>

  <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap">
    <div class="stat-card" style="flex:1;min-width:140px">
      <div class="label">Số đơn</div>
      <div class="value gold"><?= count($bookings) ?></div>
    </div>
    <div class="stat-card" style="flex:1;min-width:140px">
      <div class="label">Tổng doanh thu</div>
      <div class="value red"><?= number_format($totalRev) ?>đ</div>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Khách hàng</th><th>Phim</th><th>Suất / Phòng</th>
          <th>Ghế</th><th style="text-align:right">Tổng tiền</th><th>Thời gian đặt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td class="text-muted"><?= $b['id'] ?></td>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($b['full_name']) ?></div>
            <div class="text-muted" style="font-size:12px"><?= htmlspecialchars($b['email']) ?></div>
          </td>
          <td><?= htmlspecialchars($b['movie_title']) ?></td>
          <td class="text-muted">
            <?= date('H:i d/m', strtotime($b['start_time'])) ?><br>
            <span style="font-size:12px"><?= $b['room_name'] ?></span>
          </td>
          <td style="font-size:13px;color:#aaa"><?= htmlspecialchars($b['seats'] ?? '—') ?></td>
          <td style="text-align:right;font-weight:700;color:#e50914">
            <?= number_format($b['total_price']) ?>đ
          </td>
          <td class="text-muted" style="font-size:12px">
            <?= date('H:i d/m/Y', strtotime($b['created_at'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?>
        <tr><td colspan="7" class="text-muted text-center" style="padding:32px">Không có đơn nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<?php renderFooter(); ?>

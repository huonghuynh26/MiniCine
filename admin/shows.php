<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = $err = '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $s = $db->prepare("DELETE FROM tblShows WHERE id=?");
    $s->bind_param('i', $id); $s->execute();
    $msg = 'Đã xoá suất chiếu.';
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId   = (int)$_POST['movie_id'];
    $roomId    = (int)$_POST['room_id'];
    $startTime = $_POST['start_time'] ?? '';

    if (!$movieId || !$roomId || !$startTime) {
        $err = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        // Get movie duration to calc end_time
        $dur = (int)$db->query("SELECT duration_min FROM tblMovies WHERE id=$movieId")->fetch_assoc()['duration_min'];
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + $dur * 60);

        // Check conflict: same room, overlapping time
        $check = $db->prepare("
            SELECT id FROM tblShows
            WHERE room_id=? AND id != ?
              AND NOT (end_time <= ? OR start_time >= ?)
        ");
        $editId = (int)($_POST['id'] ?? 0);
        $check->bind_param('iiss', $roomId, $editId, $startTime, $endTime);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $err = 'Phòng chiếu đã có suất khác trong khung giờ này.';
        } else {
            if ($editId) {
                $s = $db->prepare("UPDATE tblShows SET movie_id=?,room_id=?,start_time=?,end_time=? WHERE id=?");
                $s->bind_param('iissi', $movieId,$roomId,$startTime,$endTime,$editId);
                $s->execute();
                $msg = 'Đã cập nhật suất chiếu.';
            } else {
                $s = $db->prepare("INSERT INTO tblShows(movie_id,room_id,start_time,end_time) VALUES(?,?,?,?)");
                $s->bind_param('iiss', $movieId,$roomId,$startTime,$endTime);
                $s->execute();

                // Init seat statuses for this show
                $newShowId = $db->insert_id;
                $db->query("
                    INSERT IGNORE INTO tblSeatStatus(show_id,seat_id,status,version_number)
                    SELECT {$newShowId}, id, 'available', 0
                    FROM tblSeats WHERE room_id={$roomId}
                ");
                $msg = 'Đã tạo suất chiếu mới.';
            }
        }
    }
}

// Load movies & rooms for form
$movies = $db->query("SELECT id,title FROM tblMovies WHERE status IN ('showing','upcoming') ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$rooms  = $db->query("SELECT * FROM tblRooms ORDER BY floor")->fetch_all(MYSQLI_ASSOC);

// Load shows with fill rate
$shows = $db->query("
    SELECT s.*, m.title as movie_title, r.name as room_name, r.floor,
      (SELECT COUNT(*) FROM tblSeats WHERE room_id=s.room_id) as total_seats,
      (SELECT COUNT(*) FROM tblSeatStatus ss WHERE ss.show_id=s.id AND ss.status='booked') as booked_seats
    FROM tblShows s
    JOIN tblMovies m ON m.id=s.movie_id
    JOIN tblRooms  r ON r.id=s.room_id
    ORDER BY s.start_time DESC
")->fetch_all(MYSQLI_ASSOC);

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM tblShows WHERE id=?");
    $s->bind_param('i',$editId); $s->execute();
    $editing = $s->get_result()->fetch_assoc();
}

renderHead('Quản lý suất chiếu');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800">Quản lý suất chiếu</h1>
    <button class="btn btn-primary" onclick="toggleForm()">+ Thêm suất chiếu</button>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Form -->
  <div id="show-form" class="card mb-3" style="<?= ($editing || !empty($err)) ? '' : 'display:none' ?>">
    <div class="card-body">
      <h3 style="margin-bottom:16px"><?= $editing ? 'Sửa suất chiếu' : 'Thêm suất chiếu mới' ?></h3>
      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? '' ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
          <div class="form-group">
            <label>Phim *</label>
            <select class="form-control" name="movie_id" required>
              <option value="">-- Chọn phim --</option>
              <?php foreach ($movies as $m): ?>
              <option value="<?= $m['id'] ?>" <?= ($editing['movie_id']??'')==$m['id']?'selected':'' ?>>
                <?= htmlspecialchars($m['title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Phòng chiếu *</label>
            <select class="form-control" name="room_id" required>
              <option value="">-- Chọn phòng --</option>
              <?php foreach ($rooms as $r): ?>
              <option value="<?= $r['id'] ?>" <?= ($editing['room_id']??'')==$r['id']?'selected':'' ?>>
                <?= htmlspecialchars($r['name']) ?> (Tầng <?= $r['floor'] ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Thời gian bắt đầu *</label>
            <input class="form-control" type="datetime-local" name="start_time" required
                   value="<?= $editing ? date('Y-m-d\TH:i', strtotime($editing['start_time'])) : '' ?>">
          </div>
        </div>
        <div style="display:flex;gap:12px">
          <button class="btn btn-primary" type="submit"><?= $editing ? 'Cập nhật' : 'Tạo suất chiếu' ?></button>
          <button class="btn btn-outline" type="button" onclick="toggleForm()">Huỷ</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>Phim</th><th>Phòng</th><th>Thời gian</th><th>Tỷ lệ lấp đầy</th><th>Thao tác</th></tr></thead>
      <tbody>
        <?php foreach ($shows as $s):
          $pct = $s['total_seats'] ? round($s['booked_seats'] / $s['total_seats'] * 100) : 0;
          $isPast = strtotime($s['start_time']) < time();
        ?>
        <tr style="<?= $isPast ? 'opacity:.5' : '' ?>">
          <td style="font-weight:600"><?= htmlspecialchars($s['movie_title']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($s['room_name']) ?></td>
          <td><?= date('H:i d/m/Y', strtotime($s['start_time'])) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="background:#1a1a1a;border-radius:4px;height:6px;width:80px">
                <div style="background:<?= $pct>80?'#e50914':($pct>50?'#f5c518':'#4caf50') ?>;
                  height:6px;border-radius:4px;width:<?= $pct ?>%"></div>
              </div>
              <span class="text-muted" style="font-size:13px"><?= $s['booked_seats'] ?>/<?= $s['total_seats'] ?> (<?= $pct ?>%)</span>
            </div>
          </td>
          <td>
            <?php if (!$isPast): ?>
            <a href="?edit=<?= $s['id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
            <?php endif; ?>
            <a href="?action=delete&id=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Xoá suất chiếu này?')">Xoá</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<script>
function toggleForm() {
  const f = document.getElementById('show-form');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php renderFooter(); ?>

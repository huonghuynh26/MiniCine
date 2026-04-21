<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = $err = '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $showId  = (int)$_POST['show_id'];
    $disc    = min(90, max(1, (int)$_POST['discount_pct']));
    $trigger = in_array($_POST['trigger_type'],['pre2h','post15m','manual']) ? $_POST['trigger_type'] : 'manual';

    if (!$showId) { $err = 'Vui lòng chọn suất chiếu.'; }
    else {
        $stmt_fs = $db->prepare("
            INSERT INTO tblFlashSales(show_id,discount_pct,trigger_type,is_active)
            VALUES(?,?,?,1)
            ON DUPLICATE KEY UPDATE discount_pct=?, trigger_type=?, is_active=1
        ");
        $stmt_fs->bind_param('iisis', $showId, $disc, $trigger, $disc, $trigger);
        $stmt_fs->execute();
        $msg = 'Đã lưu Flash Sale.';
    }
}

if ($action === 'toggle') {
    $id = (int)$_GET['id'];
    $db->query("UPDATE tblFlashSales SET is_active = NOT is_active WHERE id=$id");
    $msg = 'Đã thay đổi trạng thái.';
}

if ($action === 'delete') {
    $id = (int)$_GET['id'];
    $s = $db->prepare("DELETE FROM tblFlashSales WHERE id=?");
    $s->bind_param('i',$id); $s->execute();
    $msg = 'Đã xoá Flash Sale.';
}

// Upcoming shows without flash sale
$freeShows = $db->query("
    SELECT s.id, m.title, s.start_time, r.name as room_name
    FROM tblShows s
    JOIN tblMovies m ON m.id=s.movie_id
    JOIN tblRooms  r ON r.id=s.room_id
    LEFT JOIN tblFlashSales fs ON fs.show_id=s.id
    WHERE s.start_time > NOW() AND fs.id IS NULL
    ORDER BY s.start_time ASC
")->fetch_all(MYSQLI_ASSOC);

// All flash sales
$sales = $db->query("
    SELECT fs.*, m.title as movie_title, s.start_time, r.name as room_name
    FROM tblFlashSales fs
    JOIN tblShows  s ON s.id=fs.show_id
    JOIN tblMovies m ON m.id=s.movie_id
    JOIN tblRooms  r ON r.id=s.room_id
    ORDER BY s.start_time DESC
")->fetch_all(MYSQLI_ASSOC);

// All shows (for form)
$allShows = $db->query("
    SELECT s.id, m.title, s.start_time, r.name as room_name
    FROM tblShows s JOIN tblMovies m ON m.id=s.movie_id JOIN tblRooms r ON r.id=s.room_id
    WHERE s.start_time > NOW() ORDER BY s.start_time ASC
")->fetch_all(MYSQLI_ASSOC);

renderHead('Flash Sale');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <h1 style="font-size:22px;font-weight:800;margin-bottom:8px">⚡ Quản lý Flash Sale</h1>
  <p class="text-muted mb-3" style="font-size:14px">
    Hệ thống tự động áp dụng: trước 2h nếu còn ≥30% ghế trống → -30% | sau 15 phút chiếu → -50%
  </p>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Add form -->
  <div class="card mb-3">
    <div class="card-body">
      <h3 style="margin-bottom:16px">Tạo Flash Sale mới</h3>
      <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <input type="hidden" name="action" value="save">
        <div class="form-group" style="flex:2;min-width:200px;margin:0">
          <label>Suất chiếu</label>
          <select class="form-control" name="show_id" required>
            <option value="">-- Chọn suất chiếu --</option>
            <?php foreach ($allShows as $s): ?>
            <option value="<?= $s['id'] ?>">
              <?= htmlspecialchars($s['title']) ?> · <?= $s['room_name'] ?> · <?= date('H:i d/m', strtotime($s['start_time'])) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="width:120px;margin:0">
          <label>Giảm (%)</label>
          <input class="form-control" type="number" name="discount_pct" min="1" max="90" value="30">
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
          <label>Kích hoạt khi</label>
          <select class="form-control" name="trigger_type">
            <option value="manual">Thủ công (ngay lập tức)</option>
            <option value="pre2h">Trước 2h (nếu ≥30% trống)</option>
            <option value="post15m">Sau 15 phút chiếu</option>
          </select>
        </div>
        <button class="btn btn-primary" type="submit" style="flex-shrink:0">Tạo Flash Sale</button>
      </form>
    </div>
  </div>

  <!-- Rules info -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px">
    <div class="card" style="padding:16px;border-color:#333300">
      <div style="color:#f5c518;font-weight:700;margin-bottom:4px">⚡ Trước 2 giờ chiếu</div>
      <div class="text-muted" style="font-size:13px">Tự động -30% nếu còn ≥30% ghế trống<br>Không áp dụng Couple</div>
    </div>
    <div class="card" style="padding:16px;border-color:#330000">
      <div style="color:#e50914;font-weight:700;margin-bottom:4px">🔥 Sau 15 phút chiếu</div>
      <div class="text-muted" style="font-size:13px">Tự động -50% toàn bộ ghế còn trống<br>Không áp dụng Couple</div>
    </div>
    <div class="card" style="padding:16px">
      <div style="color:#aaa;font-weight:700;margin-bottom:4px">🎛 Thủ công</div>
      <div class="text-muted" style="font-size:13px">Admin bật/tắt trực tiếp<br>Tùy chỉnh % giảm</div>
    </div>
  </div>

  <!-- Flash sale list -->
  <div class="table-wrap">
    <table>
      <thead><tr><th>Phim & Suất chiếu</th><th>Loại</th><th>Giảm giá</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
      <tbody>
        <?php foreach ($sales as $fs):
          $isPast = strtotime($fs['start_time']) < time();
          $triggerLabel = ['pre2h'=>'Trước 2h','post15m'=>'Sau 15 phút','manual'=>'Thủ công'][$fs['trigger_type']];
        ?>
        <tr style="<?= $isPast?'opacity:.4':'' ?>">
          <td>
            <div style="font-weight:600"><?= htmlspecialchars($fs['movie_title']) ?></div>
            <div class="text-muted" style="font-size:12px">
              <?= $fs['room_name'] ?> · <?= date('H:i d/m/Y', strtotime($fs['start_time'])) ?>
            </div>
          </td>
          <td><span class="badge" style="background:#1a1a1a;color:#888"><?= $triggerLabel ?></span></td>
          <td><span style="color:#e50914;font-weight:700;font-size:18px">-<?= $fs['discount_pct'] ?>%</span></td>
          <td>
            <span class="badge" style="background:<?= $fs['is_active']?'#0f2d1a':'#1a1a1a' ?>;
              color:<?= $fs['is_active']?'#4caf50':'#555' ?>">
              <?= $fs['is_active'] ? '● Đang hoạt động' : '○ Tắt' ?>
            </span>
          </td>
          <td>
            <a href="?action=toggle&id=<?= $fs['id'] ?>" class="btn btn-outline btn-sm">
              <?= $fs['is_active'] ? 'Tắt' : 'Bật' ?>
            </a>
            <a href="?action=delete&id=<?= $fs['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Xoá Flash Sale này?')">Xoá</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($sales)): ?>
        <tr><td colspan="5" class="text-muted text-center" style="padding:32px">Chưa có Flash Sale nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<?php renderFooter(); ?>
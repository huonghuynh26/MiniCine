<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = $err = '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$editId = (int)($_GET['edit'] ?? 0);

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    // Xóa theo thứ tự: booking items → bookings → seat statuses → shows → movie
    $db->query("DELETE bi FROM tblBookingItems bi JOIN tblBookings b ON b.id=bi.booking_id JOIN tblShows s ON s.id=b.show_id WHERE s.movie_id=$id");
    $db->query("DELETE b FROM tblBookings b JOIN tblShows s ON s.id=b.show_id WHERE s.movie_id=$id");
    $db->query("DELETE ss FROM tblSeatStatus ss JOIN tblShows s ON s.id=ss.show_id WHERE s.movie_id=$id");
    $db->query("DELETE FROM tblShows WHERE movie_id=$id");
    $db->query("DELETE FROM tblRatings WHERE movie_id=$id");
    $s = $db->prepare("DELETE FROM tblMovies WHERE id=?");
    $s->bind_param('i', $id); $s->execute();
    $msg = 'Đã xoá phim.';
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $title      = trim($_POST['title'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $genreArr   = $_POST['genres'] ?? [];
    $genre      = implode(', ', array_filter(array_map('trim', $genreArr)));
    $duration   = (int)($_POST['duration_min'] ?? 90);
    $trailer    = trim($_POST['trailer_url'] ?? '');
    $status     = in_array($_POST['status'],['showing','upcoming','ended']) ? $_POST['status'] : 'upcoming';
    $age_rating = in_array($_POST['age_rating'],['P','T13','T16','T18']) ? $_POST['age_rating'] : 'P';

    $posterFile = null;
    if (!empty($_FILES['poster']['name'])) {
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = uniqid('poster_') . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], __DIR__ . '/../uploads/posters/' . $fname);
            $posterFile = $fname;
        }
    }

    if (!$title) {
        $err = 'Tên phim không được để trống.';
    } elseif ($id) {
        $sql = $posterFile
            ? "UPDATE tblMovies SET title=?,description=?,genre=?,duration_min=?,trailer_url=?,status=?,age_rating=?,poster_url=? WHERE id=?"
            : "UPDATE tblMovies SET title=?,description=?,genre=?,duration_min=?,trailer_url=?,status=?,age_rating=? WHERE id=?";
        $stmt = $db->prepare($sql);
        if ($posterFile) $stmt->bind_param('sssissssi', $title,$desc,$genre,$duration,$trailer,$status,$age_rating,$posterFile,$id);
        else             $stmt->bind_param('sssisssi',  $title,$desc,$genre,$duration,$trailer,$status,$age_rating,$id);
        $stmt->execute();
        $msg = 'Đã cập nhật phim.'; $editId = 0;
    } else {
        $stmt = $db->prepare("INSERT INTO tblMovies(title,description,genre,duration_min,trailer_url,status,age_rating,poster_url) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssissss', $title,$desc,$genre,$duration,$trailer,$status,$age_rating,$posterFile);
        $stmt->execute();
        $msg = 'Đã thêm phim mới.';
    }
}

$movies = $db->query("SELECT * FROM tblMovies ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$editing = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM tblMovies WHERE id=?");
    $s->bind_param('i', $editId); $s->execute();
    $editing = $s->get_result()->fetch_assoc();
}

// Age rating labels & colors
$ageLabels = ['P'=>'P – Mọi lứa tuổi','T13'=>'T13 – Từ 13 tuổi','T16'=>'T16 – Từ 16 tuổi','T18'=>'T18 – Từ 18 tuổi'];
$ageBadgeColor = ['P'=>'#27ae60','T13'=>'#2980b9','T16'=>'#e67e22','T18'=>'#e8192c'];

renderHead('Quản lý phim');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800">Quản lý phim</h1>
    <button class="btn btn-primary" onclick="toggleForm()">+ Thêm phim mới</button>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div id="movie-form" class="card mb-3" style="<?= ($editing || !empty($err)) ? '' : 'display:none' ?>">
    <div class="card-body">
      <h3 style="margin-bottom:16px"><?= $editing ? 'Sửa phim' : 'Thêm phim mới' ?></h3>
      <form method="post" enctype="multipart/form-data" action="">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editing['id'] ?? '' ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="form-group">
            <label>Tên phim *</label>
            <input class="form-control" name="title" required value="<?= htmlspecialchars($editing['title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Thể loại (chọn nhiều)</label>
            <?php
            $allGenres = ['Hành Động','Tình Cảm','Hài','Kinh Dị','Hoạt Hình','Khoa Học Viễn Tưởng','Phiêu Lưu','Tâm Lý','Bí Ẩn / Giật Gân','Lịch Sử','Gia Đình','Âm Nhạc','Tội Phạm'];
            $selectedGenres = array_map('trim', explode(',', $editing['genre'] ?? ''));
            ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;padding:10px;border:1px solid #ddd;border-radius:6px;background:#fff">
              <?php foreach ($allGenres as $g): ?>
              <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:13px;font-weight:400;color:#333">
                <input type="checkbox" name="genres[]" value="<?= htmlspecialchars($g) ?>"
                  <?= in_array($g, $selectedGenres) ? 'checked' : '' ?>
                  style="width:14px;height:14px;accent-color:#e8192c">
                <?= htmlspecialchars($g) ?>
              </label>
              <?php endforeach; ?>
            </div>
            <div style="font-size:11px;color:#aaa;margin-top:4px">Tích chọn các thể loại phù hợp</div>
          </div>
          <div class="form-group">
            <label>Thời lượng (phút)</label>
            <input class="form-control" type="number" name="duration_min" value="<?= $editing['duration_min'] ?? 90 ?>" min="1">
          </div>
          <div class="form-group">
            <label>Trạng thái</label>
            <select class="form-control" name="status">
              <?php foreach (['showing'=>'Đang chiếu','upcoming'=>'Sắp chiếu','ended'=>'Đã kết thúc'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($editing['status']??'upcoming')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Age Rating -->
          <div class="form-group">
            <label>Phân loại độ tuổi</label>
            <select class="form-control" name="age_rating">
              <?php foreach ($ageLabels as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($editing['age_rating']??'P')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Link trailer (YouTube)</label>
            <input class="form-control" name="trailer_url" value="<?= htmlspecialchars($editing['trailer_url'] ?? '') ?>" placeholder="https://youtube.com/...">
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label>Mô tả</label>
            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Poster (JPG/PNG/WebP)</label>
            <input class="form-control" type="file" name="poster" accept="image/*">
            <?php if (!empty($editing['poster_url'])): ?>
            <div class="mt-1"><img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($editing['poster_url']) ?>"
                 style="height:60px;border-radius:4px"></div>
            <?php endif; ?>
          </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px">
          <button class="btn btn-primary" type="submit"><?= $editing ? 'Cập nhật' : 'Thêm phim' ?></button>
          <button class="btn btn-outline" type="button" onclick="toggleForm()">Huỷ</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Poster</th><th>Tên phim</th><th>Thể loại</th><th>Độ tuổi</th><th>Thời lượng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
      <tbody>
        <?php foreach ($movies as $m):
          $ar = $m['age_rating'] ?? 'P';
          $arColor = $ageBadgeColor[$ar] ?? '#27ae60';
        ?>
        <tr>
          <td><?php if ($m['poster_url']): ?>
            <img src="<?= APP_URL ?>/uploads/posters/<?= htmlspecialchars($m['poster_url']) ?>"
                 style="height:48px;border-radius:4px;object-fit:cover">
          <?php else: ?><span style="font-size:28px">🎬</span><?php endif; ?></td>
          <td><div style="font-weight:600"><?= htmlspecialchars($m['title']) ?></div></td>
          <td class="text-muted"><?= htmlspecialchars($m['genre'] ?? '—') ?></td>
          <td><span style="background:<?= $arColor ?>;color:#fff;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:700"><?= $ar ?></span></td>
          <td class="text-muted"><?= $m['duration_min'] ?> phút</td>
          <td><span class="badge badge-<?= $m['status'] ?>">
            <?= ['showing'=>'Đang chiếu','upcoming'=>'Sắp chiếu','ended'=>'Kết thúc'][$m['status']] ?>
          </span></td>
          <td>
            <a href="?edit=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
            <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Xoá phim này?')">Xoá</a>
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
  const f = document.getElementById('movie-form');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
<?php if ($editing): ?>document.getElementById('movie-form').style.display='block';<?php endif; ?>
</script>
<?php renderFooter(); ?>
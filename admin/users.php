<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$db  = db();
$msg = '';

if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
    $id  = (int)$_GET['id'];
    $me  = currentUser();
    if ($id !== (int)$me['id']) {
        // Lấy trạng thái hiện tại
        $cur = $db->query("SELECT email_verified FROM tblUsers WHERE id=$id")->fetch_assoc();

        if ($cur['email_verified']) {
            // Đang active → khóa: set email_verified=0, verify_token=NULL
            // verify_token=NULL giúp phân biệt "bị khóa" vs "chưa xác thực"
            $db->query("UPDATE tblUsers SET email_verified=0, verify_token=NULL WHERE id=$id");
            $msg = 'Đã khóa tài khoản.';
        } else {
            // Đang khóa → mở khóa
            $db->query("UPDATE tblUsers SET email_verified=1 WHERE id=$id");
            $msg = 'Đã mở khóa tài khoản.';
        }
    }
}

$search = trim($_GET['q'] ?? '');
$where  = "role='customer'";
if ($search) {
    $s = addslashes($search);
    $where .= " AND (email LIKE '%{$s}%' OR full_name LIKE '%{$s}%')";
}

$users = $db->query("
    SELECT u.*,
      (SELECT COUNT(*) FROM tblBookings WHERE user_id=u.id AND payment_status='paid') as booking_count,
      (SELECT COALESCE(SUM(total_price),0) FROM tblBookings WHERE user_id=u.id AND payment_status='paid') as total_spent
    FROM tblUsers u WHERE $where ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

renderHead('Khách hàng');
?>
<?php renderNav(); ?>
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-content">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:22px;font-weight:800">👥 Khách hàng</h1>
    <span class="text-muted" style="font-size:14px"><?= count($users) ?> tài khoản</span>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <form method="get" style="display:flex;gap:12px;margin-bottom:20px">
    <input class="form-control" type="text" name="q" value="<?= htmlspecialchars($search) ?>"
           placeholder="Tìm tên, email..." style="max-width:320px">
    <button class="btn btn-primary" type="submit">Tìm</button>
    <?php if ($search): ?><a href="?" class="btn btn-outline">Xoá</a><?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Tên</th><th>Email</th><th>Trạng thái</th><th>Đặt vé</th><th>Chi tiêu</th><th>Điểm</th><th>Ngày đăng ký</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          // Phân biệt trạng thái
          if ($u['email_verified']) {
              $statusLabel = '✓ Đang hoạt động';
              $statusColor = 'background:#0f2d1a;color:#4caf50';
          } elseif ($u['verify_token']) {
              $statusLabel = '⏳ Chờ xác thực email';
              $statusColor = 'background:#2d220f;color:#ff9800';
          } else {
              $statusLabel = '🔒 Đã bị khóa';
              $statusColor = 'background:#2d0f0f;color:#ff4444';
          }
        ?>
        <tr>
          <td style="font-weight:500"><?= htmlspecialchars($u['full_name']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge" style="<?= $statusColor ?>"><?= $statusLabel ?></span></td>
          <td><?= $u['booking_count'] ?> đơn</td>
          <td style="color:#e50914;font-weight:600"><?= number_format($u['total_spent']) ?>đ</td>
          <td class="text-gold">⭐ <?= number_format($u['total_points']) ?></td>
          <td class="text-muted" style="font-size:12px"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="?action=toggle&id=<?= $u['id'] ?>"
               class="btn btn-sm <?= $u['email_verified'] ? 'btn-danger' : 'btn-success' ?>"
               onclick="return confirm('<?= $u['email_verified'] ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?' ?>')">
              <?= $u['email_verified'] ? '🔒 Khóa' : '🔓 Mở khóa' ?>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<?php renderFooter(); ?>
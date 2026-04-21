<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

startSession();
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = forgotPassword(trim($_POST['email'] ?? ''));
    if ($r['ok']) $msg = $r['msg']; else $err = $r['msg'];
}

renderHead('Quên mật khẩu');
?>
<?php renderNav(); ?>
<div class="auth-page">
<div class="auth-box">
  <h1>Quên mật khẩu?</h1>
  <p class="sub">Nhập email để nhận link đặt lại mật khẩu</p>

  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <?php if (!$msg): ?>
  <form method="post">
    <div class="form-group">
      <label>Email</label>
      <input class="form-control" type="email" name="email" placeholder="you@example.com" required autofocus>
    </div>
    <button class="btn btn-primary" style="width:100%" type="submit">Gửi link đặt lại</button>
  </form>
  <?php endif; ?>

  <p class="text-muted mt-3" style="font-size:14px;text-align:center">
    <a href="<?= APP_URL ?>/login.php">← Quay lại đăng nhập</a>
  </p>
</div>
</div>
<?php renderFooter(); ?>

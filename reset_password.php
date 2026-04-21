<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$token = trim($_GET['token'] ?? '');
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if (strlen($pass) < 6)    $err = 'Mật khẩu phải có ít nhất 6 ký tự.';
    elseif ($pass !== $pass2) $err = 'Mật khẩu xác nhận không khớp.';
    else {
        $r = resetPassword($token, $pass);
        if ($r['ok']) $msg = $r['msg']; else $err = $r['msg'];
    }
}

renderHead('Đặt lại mật khẩu');
?>
<?php renderNav(); ?>
<div class="auth-page">
<div class="auth-box">
  <h1>Đặt lại mật khẩu</h1>
  <p class="sub">Nhập mật khẩu mới cho tài khoản của bạn</p>

  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <a href="<?= APP_URL ?>/login.php" class="btn btn-primary" style="width:100%;margin-top:16px">Đăng nhập →</a>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div class="form-group">
      <label>Mật khẩu mới</label>
      <input class="form-control" type="password" name="password" placeholder="Ít nhất 6 ký tự" required>
    </div>
    <div class="form-group">
      <label>Xác nhận mật khẩu</label>
      <input class="form-control" type="password" name="password2" placeholder="Nhập lại mật khẩu" required>
    </div>
    <button class="btn btn-primary" style="width:100%" type="submit">Cập nhật mật khẩu</button>
  </form>
  <?php endif; ?>
</div>
</div>
<?php renderFooter(); ?>

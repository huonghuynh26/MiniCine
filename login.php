<?php
// tài khoản quản trị viên admin@minicine.vn / Admin@123
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

startSession();
if (currentUser()) { header('Location: ' . APP_URL); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($result['ok']) {
        $redirect = $_GET['redirect'] ?? ($result['role'] === 'admin' ? APP_URL . '/admin/index.php' : APP_URL . '/index.php');
        header('Location: ' . $redirect);
        exit;
    }
    $err = $result['msg'];
}

renderHead('Đăng nhập');
?>
<?php renderNav(); ?>
<div class="auth-page">
<div class="auth-box">
  <h1>Đăng nhập</h1>
  <p class="sub">Chào mừng trở lại với MiniCine 🎬</p>

  <?php if ($err): ?>
  <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label>Email</label>
      <input class="form-control" type="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="you@example.com" required autofocus>
    </div>
    <div class="form-group">
      <label>Mật khẩu</label>
      <input class="form-control" type="password" name="password" placeholder="••••••••" required>
    </div>
    <div style="text-align:right;margin:-8px 0 16px">
      <a href="<?= APP_URL ?>/forgot_password.php" style="font-size:13px;color:#888">Quên mật khẩu?</a>
    </div>
    <button class="btn btn-primary" style="width:100%" type="submit">Đăng nhập →</button>
  </form>

  <p class="text-muted mt-3" style="font-size:14px;text-align:center">
    Chưa có tài khoản? <a href="<?= APP_URL ?>/register.php">Đăng ký ngay</a>
  </p>
</div>
</div>
<?php renderFooter(); ?>

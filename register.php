<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

startSession();
if (currentUser()) { header('Location: ' . APP_URL); exit; }

$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $name  = trim($_POST['name'] ?? '');
    $pass2 = $_POST['password2'] ?? '';

    if (!$email || !$pass || !$name) {
        $err = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Email không hợp lệ.';
    } elseif (strlen($pass) < 6) {
        $err = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($pass !== $pass2) {
        $err = 'Mật khẩu xác nhận không khớp.';
    } else {
        $result = registerUser($email, $pass, $name);
        if ($result['ok']) $msg = $result['msg'];
        else $err = $result['msg'];
    }
}

renderHead('Đăng ký');
?>
<?php renderNav(); ?>
<div class="auth-page">
<div class="auth-box">
  <h1>Tạo tài khoản</h1>
  <p class="sub">Đăng ký để đặt vé xem phim tại MiniCine</p>

  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <?php if (!$msg): ?>
  <form method="post">
    <div class="form-group">
      <label>Họ và tên</label>
      <input class="form-control" type="text" name="name"
             value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Nguyễn Văn A" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input class="form-control" type="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label>Mật khẩu</label>
      <input class="form-control" type="password" name="password" placeholder="Ít nhất 6 ký tự" required>
    </div>
    <div class="form-group">
      <label>Xác nhận mật khẩu</label>
      <input class="form-control" type="password" name="password2" placeholder="Nhập lại mật khẩu" required>
    </div>
    <button class="btn btn-primary" style="width:100%;margin-top:8px" type="submit">
      Đăng ký →
    </button>
  </form>
  <?php endif; ?>

  <p class="text-muted mt-3" style="font-size:14px;text-align:center">
    Đã có tài khoản? <a href="<?= APP_URL ?>/login.php">Đăng nhập</a>
  </p>
</div>
</div>
<?php renderFooter(); ?>

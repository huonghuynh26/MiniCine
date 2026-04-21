<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$token = trim($_GET['token'] ?? '');
$ok    = $token && verifyEmail($token);

renderHead('Xác thực email');
?>
<?php renderNav(); ?>
<div class="auth-page">
<div class="auth-box" style="text-align:center">
  <?php if ($ok): ?>
    <div style="font-size:56px;margin-bottom:16px">✅</div>
    <h1 style="margin-bottom:8px">Email đã xác thực!</h1>
    <p class="sub">Tài khoản của bạn đã được kích hoạt. Bạn có thể đăng nhập ngay.</p>
    <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-lg mt-3">Đăng nhập →</a>
  <?php else: ?>
    <div style="font-size:56px;margin-bottom:16px">❌</div>
    <h1 style="margin-bottom:8px">Link không hợp lệ</h1>
    <p class="sub">Link xác thực đã hết hạn hoặc không đúng. Vui lòng đăng ký lại.</p>
    <a href="<?= APP_URL ?>/register.php" class="btn btn-outline mt-3">Đăng ký lại</a>
  <?php endif; ?>
</div>
</div>
<?php renderFooter(); ?>

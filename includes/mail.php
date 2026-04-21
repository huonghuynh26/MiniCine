<?php
require_once __DIR__ . '/../config/config.php';

// PHPMailer via Composer autoload hoặc manual include
// Đặt thư mục PHPMailer vào source/vendor/phpmailer/
$phpmailerPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
} else {
    // Fallback: manual include (nếu không dùng Composer)
    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    return $mail;
}

function sendVerifyEmail(string $to, string $name, string $link): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = '[MiniCine] Xác thực tài khoản của bạn';
        $mail->Body = emailTemplate('Xác thực tài khoản', "
            <p>Xin chào <strong>{$name}</strong>,</p>
            <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>MiniCine</strong>.</p>
            <p>Nhấn vào nút bên dưới để xác thực email và bắt đầu đặt vé:</p>
            <p style='text-align:center;margin:32px 0'>
              <a href='{$link}' style='background:#e50914;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px'>
                ✓ Xác thực tài khoản
              </a>
            </p>
            <p style='color:#999;font-size:13px'>Link có hiệu lực trong 24 giờ. Nếu bạn không đăng ký, hãy bỏ qua email này.</p>
        ");
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $e->getMessage());
        return false;
    }
}

function sendResetEmail(string $to, string $name, string $link): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = '[MiniCine] Đặt lại mật khẩu';
        $mail->Body = emailTemplate('Đặt lại mật khẩu', "
            <p>Xin chào <strong>{$name}</strong>,</p>
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
            <p style='text-align:center;margin:32px 0'>
              <a href='{$link}' style='background:#e50914;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px'>
                🔑 Đặt lại mật khẩu
              </a>
            </p>
            <p style='color:#999;font-size:13px'>Link có hiệu lực trong 1 giờ. Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
        ");
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $e->getMessage());
        return false;
    }
}

function sendBookingConfirmEmail(string $to, string $name, array $booking, string $qrDataUri): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = '[MiniCine] Xác nhận đặt vé #' . $booking['id'];

        $items = '';
        foreach ($booking['items'] as $item) {
            $flash = $item['flash_sale_applied']
                ? "<span style='color:#e50914;font-size:12px'> (-{$item['discount_pct']}% Flash Sale)</span>"
                : '';
            $items .= "<tr>
              <td style='padding:8px 0;border-bottom:1px solid #333'>{$item['seat_label']}</td>
              <td style='padding:8px 0;border-bottom:1px solid #333;text-align:right'>
                " . number_format($item['price']) . "đ{$flash}
              </td>
            </tr>";
        }

        $mail->Body = emailTemplate('Đặt vé thành công!', "
            <p>Xin chào <strong>{$name}</strong>, vé của bạn đã được xác nhận! 🎉</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
              <tr><td style='color:#999;padding:4px 0'>Phim</td><td style='text-align:right;font-weight:600'>{$booking['movie_title']}</td></tr>
              <tr><td style='color:#999;padding:4px 0'>Suất chiếu</td><td style='text-align:right'>{$booking['show_time']}</td></tr>
              <tr><td style='color:#999;padding:4px 0'>Phòng</td><td style='text-align:right'>{$booking['room_name']}</td></tr>
            </table>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
              <tr><th style='text-align:left;padding:8px 0;border-bottom:1px solid #333'>Ghế</th><th style='text-align:right;padding:8px 0;border-bottom:1px solid #333'>Giá</th></tr>
              {$items}
              <tr><td style='padding:12px 0;font-weight:700'>Tổng cộng</td><td style='text-align:right;font-weight:700;color:#e50914'>" . number_format($booking['total_price']) . "đ</td></tr>
            </table>
            <p style='text-align:center;margin:24px 0'>
              <img src='{$qrDataUri}' alt='QR Code' style='width:180px;height:180px;border:4px solid #333;border-radius:8px'/>
              <br><span style='color:#999;font-size:12px;margin-top:8px;display:block'>Xuất trình mã QR này tại cửa rạp</span>
            </p>
        ");
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $e->getMessage());
        return false;
    }
}

function emailTemplate(string $title, string $content): string {
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#141414;font-family:Arial,sans-serif;color:#e5e5e5'>
      <div style='max-width:560px;margin:0 auto;padding:40px 20px'>
        <div style='text-align:center;margin-bottom:32px'>
          <span style='font-size:28px;font-weight:900;color:#e50914;letter-spacing:-1px'>MINI</span><span style='font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px'>CINE</span>
        </div>
        <div style='background:#1f1f1f;border-radius:12px;padding:32px;border:1px solid #333'>
          <h2 style='margin:0 0 20px;font-size:20px;color:#fff'>{$title}</h2>
          {$content}
        </div>
        <p style='text-align:center;color:#666;font-size:12px;margin-top:24px'>
          © " . date('Y') . " MiniCine · Rạp chiếu phim chất lượng cao
        </p>
      </div>
    </body></html>";
}

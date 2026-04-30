<?php
require_once __DIR__ . '/../config/config.php';

// PHPMailer via Composer autoload hoặc manual include
$phpmailerPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
} else {
    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host        = 'smtp.gmail.com';       // smtp.gmail.com
    $mail->SMTPAuth    = true;
    $mail->Username    = 'huonghuynhuwu@gmail.com';
    $mail->Password    = 'euzmmzigjmjtdylj';   // App Password 16 ký tự
    $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port        = 587;       // 587
    $mail->CharSet     = 'UTF-8';
    $mail->Encoding    = 'base64';

    // Tắt verify SSL certificate (cần thiết trên XAMPP Windows)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    return $mail;
}

function mailLog(string $msg): void {
    $logFile = __DIR__ . '/../mail_error.log';
    $line    = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function sendVerifyEmail(string $to, string $name, string $link): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = '[MiniCine] Xác thực tài khoản của bạn';
        $mail->Body    = emailTemplate('Xác thực tài khoản', "
            <p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>MiniCine</strong>.</p>
            <p>Nhấn vào nút bên dưới để xác thực email:</p>
            <p style='text-align:center;margin:32px 0'>
              <a href='" . $link . "' style='background:#e50914;color:#fff;padding:14px 32px;
                border-radius:8px;text-decoration:none;font-weight:600;font-size:15px'>
                ✓ Xác thực tài khoản
              </a>
            </p>
            <p style='color:#999;font-size:13px'>Link có hiệu lực trong 24 giờ.</p>
        ");
        $mail->send();
        return true;
    } catch (Exception $e) {
        mailLog("sendVerifyEmail to {$to}: " . $e->getMessage());
        return false;
    }
}

function sendResetEmail(string $to, string $name, string $link): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = '[MiniCine] Đặt lại mật khẩu';
        $mail->Body    = emailTemplate('Đặt lại mật khẩu', "
            <p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Nhấn vào nút bên dưới để đặt lại mật khẩu:</p>
            <p style='text-align:center;margin:32px 0'>
              <a href='" . $link . "' style='background:#e50914;color:#fff;padding:14px 32px;
                border-radius:8px;text-decoration:none;font-weight:600;font-size:15px'>
                🔑 Đặt lại mật khẩu
              </a>
            </p>
            <p style='color:#999;font-size:13px'>Link có hiệu lực trong 1 giờ.</p>
        ");
        $mail->send();
        return true;
    } catch (Exception $e) {
        mailLog("sendResetEmail to {$to}: " . $e->getMessage());
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
              <td style='padding:8px 0;border-bottom:1px solid #333'>" . htmlspecialchars($item['seat_label']) . "</td>
              <td style='padding:8px 0;border-bottom:1px solid #333;text-align:right'>
                " . number_format($item['price']) . "đ{$flash}
              </td>
            </tr>";
        }

        $mail->Body = emailTemplate('Đặt vé thành công! 🎉', "
            <p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>, vé của bạn đã được xác nhận!</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
              <tr><td style='color:#999;padding:6px 0'>Phim</td>
                  <td style='text-align:right;font-weight:600'>" . htmlspecialchars($booking['movie_title']) . "</td></tr>
              <tr><td style='color:#999;padding:6px 0'>Suất chiếu</td>
                  <td style='text-align:right'>" . $booking['show_time'] . "</td></tr>
              <tr><td style='color:#999;padding:6px 0'>Phòng</td>
                  <td style='text-align:right'>" . htmlspecialchars($booking['room_name']) . "</td></tr>
            </table>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
              <tr>
                <th style='text-align:left;padding:8px 0;border-bottom:1px solid #444'>Ghế</th>
                <th style='text-align:right;padding:8px 0;border-bottom:1px solid #444'>Giá</th>
              </tr>
              {$items}
              <tr>
                <td style='padding:12px 0;font-weight:700;font-size:16px'>Tổng cộng</td>
                <td style='text-align:right;font-weight:700;font-size:16px;color:#e50914'>"
                  . number_format($booking['total_price']) . "đ</td>
              </tr>
            </table>
            <div style='text-align:center;margin:24px 0'>
              <img src='" . $qrDataUri . "' alt='QR Code'
                   style='width:180px;height:180px;border:4px solid #333;border-radius:8px'/>
              <p style='color:#999;font-size:12px;margin-top:8px'>Xuất trình mã QR này tại cửa rạp</p>
            </div>
        ");
        $mail->send();
        mailLog("OK sendBookingConfirmEmail to {$to} booking #{$booking['id']}");
        return true;
    } catch (Exception $e) {
        mailLog("FAIL sendBookingConfirmEmail to {$to}: " . $e->getMessage());
        return false;
    }
}

function emailTemplate(string $title, string $content): string {
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#141414;font-family:Arial,sans-serif;color:#e5e5e5'>
      <div style='max-width:560px;margin:0 auto;padding:40px 20px'>
        <div style='text-align:center;margin-bottom:32px'>
          <span style='font-size:28px;font-weight:900;color:#e50914;letter-spacing:-1px'>MINI</span>
          <span style='font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px'>CINE</span>
        </div>
        <div style='background:#1f1f1f;border-radius:12px;padding:32px;border:1px solid #333'>
          <h2 style='margin:0 0 20px;font-size:20px;color:#fff'>" . $title . "</h2>
          " . $content . "
        </div>
        <p style='text-align:center;color:#555;font-size:12px;margin-top:24px'>
          © " . date('Y') . " MiniCine · Hệ thống đặt vé tự động
        </p>
      </div>
    </body></html>";
}
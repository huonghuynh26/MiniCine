# MiniCine – Hướng dẫn cài đặt

## Yêu cầu
- XAMPP (PHP 8.0+, MySQL 5.7+)
- Composer (hoặc tải PHPMailer thủ công)

---

## Bước 1: Đặt thư mục
Giải nén toàn bộ thư mục `source` vào:
```
C:\xampp\htdocs\source\
```

---

## Bước 2: Cài PHPMailer

### Cách A – Dùng Composer (khuyến nghị)
```bash
cd C:\xampp\htdocs\source
composer require phpmailer/phpmailer
```

### Cách B – Thủ công (không cần Composer)
1. Tải PHPMailer tại: https://github.com/PHPMailer/PHPMailer/releases
2. Giải nén, lấy 3 file trong thư mục `src/`:
   - `PHPMailer.php`
   - `SMTP.php`
   - `Exception.php`
3. Đặt vào: `source/vendor/phpmailer/src/`

---

## Bước 3: Tạo database
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Import file `source/database.sql`
3. Database `minicine` sẽ được tạo tự động với dữ liệu mẫu

---

## Bước 4: Cấu hình Gmail SMTP

### Tạo App Password Gmail:
1. Vào https://myaccount.google.com/security
2. Bật **2-Step Verification**
3. Vào **App passwords** → chọn "Mail" + "Windows Computer"
4. Copy mật khẩu 16 ký tự được tạo ra

### Điền vào `source/config/config.php`:
```php
define('MAIL_USERNAME', 'your_gmail@gmail.com');
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx'); // App Password
define('MAIL_FROM',     'your_gmail@gmail.com');
```

---

## Bước 5: Cấu hình APP_URL
Trong `source/config/config.php`, đảm bảo:
```php
define('APP_URL', 'http://localhost/source');
```

---

## Bước 6: Phân quyền thư mục upload
Đảm bảo thư mục `source/uploads/posters/` có quyền ghi.

---

## Tài khoản mặc định
| Role  | Email              | Mật khẩu  |
|-------|--------------------|-----------|
| Admin | admin@minicine.vn  | Admin@123 |

---

## Cấu trúc thư mục
```
source/
├── config/
│   ├── config.php        ← Cấu hình chính
│   └── db.php            ← Kết nối DB
├── includes/
│   ├── auth.php          ← Xác thực
│   ├── booking.php       ← Logic đặt vé
│   ├── layout.php        ← Header/Footer
│   └── mail.php          ← PHPMailer wrapper
├── api/
│   ├── seats.php         ← GET sơ đồ ghế (polling)
│   ├── hold.php          ← POST giữ ghế
│   ├── confirm.php       ← POST xác nhận thanh toán
│   └── suggest.php       ← GET gợi ý ghế liền kề
├── admin/
│   ├── index.php         ← Dashboard
│   ├── movies.php        ← Quản lý phim
│   ├── shows.php         ← Quản lý suất chiếu
│   ├── prices.php        ← Quản lý giá vé
│   ├── flash.php         ← Flash Sale
│   ├── bookings.php      ← Đơn đặt vé
│   ├── users.php         ← Khách hàng
│   └── sidebar.php       ← Sidebar navigation
├── assets/
│   └── css/main.css      ← Giao diện toàn bộ
├── uploads/
│   └── posters/          ← Poster phim upload
├── index.php             ← Trang chủ
├── movie.php             ← Chi tiết phim + suất
├── booking.php           ← Đặt vé (seat map)
├── booking_success.php   ← Trang thành công + QR
├── my_bookings.php       ← Vé của tôi
├── login.php
├── register.php
├── logout.php
├── verify.php            ← Xác thực email
├── forgot_password.php
├── reset_password.php
└── database.sql          ← Schema + seed data
```

---

## Tính năng đã triển khai
- ✅ Đăng ký / Đăng nhập / Xác thực email thật (Gmail SMTP)
- ✅ Quên mật khẩu qua email
- ✅ Sơ đồ ghế realtime (polling 10 giây)
- ✅ Giữ ghế 5 phút với đếm ngược
- ✅ Optimistic Locking chống xung đột đồng thời
- ✅ Gợi ý ghế liền kề tự động (sliding window)
- ✅ Flash Sale tự động (trước 2h / sau 15 phút)
- ✅ Điểm thưởng Standard/VIP/Couple
- ✅ Email xác nhận booking + mã QR
- ✅ Quản lý phim / suất chiếu / giá vé
- ✅ Dashboard thống kê doanh thu với biểu đồ
- ✅ Responsive mobile + desktop

---

## Nâng cấp QR Code thật (tuỳ chọn)
Cài thư viện QR thật bằng Composer:
```bash
composer require endroid/qr-code
```
Rồi cập nhật hàm `generateTextQR()` trong `api/confirm.php`.

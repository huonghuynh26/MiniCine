<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mail.php';

// ─── Session ─────────────────────────────────────────────────────────────────
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function currentUser(): ?array {
    startSession();
    return $_SESSION['user'] ?? null;
}

// ─── Check tài khoản từ DB realtime ──────────────────────────────────────────
function checkUserActiveFromDB(int $userId): bool {
    $db   = db();
    $stmt = $db->prepare("SELECT email_verified FROM tblUsers WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row && (int)$row['email_verified'] === 1;
}

// ─── requireLogin: check session + DB realtime ────────────────────────────────
function requireLogin(): void {
    startSession();
    $user = $_SESSION['user'] ?? null;

    if (!$user) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }

    // Check DB realtime — phát hiện tài khoản bị khóa dù session vẫn còn
    if (!checkUserActiveFromDB($user['id'])) {
        session_destroy();
        header('Location: ' . APP_URL . '/login.php?locked=1');
        exit;
    }
}

function requireAdmin(): void {
    startSession();
    $u = $_SESSION['user'] ?? null;
    if (!$u || $u['role'] !== 'admin') {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function isAdmin(): bool {
    $u = currentUser();
    return $u && $u['role'] === 'admin';
}

// ─── Register ────────────────────────────────────────────────────────────────
function registerUser(string $email, string $password, string $name): array {
    $db = db();
    $email = strtolower(trim($email));

    $stmt = $db->prepare("SELECT id FROM tblUsers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['ok' => false, 'msg' => 'Email đã được đăng ký.'];
    }

    $hash  = password_hash($password, PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(32));

    $stmt = $db->prepare(
        "INSERT INTO tblUsers (email, password_hash, full_name, verify_token) VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssss', $email, $hash, $name, $token);
    $stmt->execute();

    $link = APP_URL . '/verify.php?token=' . $token;
    sendVerifyEmail($email, $name, $link);

    return ['ok' => true, 'msg' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực.'];
}

// ─── Login ───────────────────────────────────────────────────────────────────
function loginUser(string $email, string $password): array {
    $db = db();
    $email = strtolower(trim($email));

    $stmt = $db->prepare("SELECT * FROM tblUsers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) return ['ok' => false, 'msg' => 'Email hoặc mật khẩu không đúng.'];
    if (!password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'msg' => 'Email hoặc mật khẩu không đúng.'];
    }
    if (!$user['email_verified']) {
        // Phân biệt: chưa xác thực email vs bị admin khóa
        // Nếu verify_token còn tồn tại → chưa xác thực email
        // Nếu verify_token = NULL → đã bị admin khóa thủ công
        if ($user['verify_token']) {
            return ['ok' => false, 'msg' => 'Tài khoản chưa xác thực email. Vui lòng kiểm tra hộp thư.'];
        } else {
            return ['ok' => false, 'msg' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'];
        }
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'     => $user['id'],
        'email'  => $user['email'],
        'name'   => $user['full_name'],
        'role'   => $user['role'],
        'points' => $user['total_points'],
    ];
    return ['ok' => true, 'role' => $user['role']];
}

function logoutUser(): void {
    startSession();
    session_destroy();
}

// ─── Verify email ─────────────────────────────────────────────────────────────
function verifyEmail(string $token): bool {
    $db = db();
    $stmt = $db->prepare(
        "UPDATE tblUsers SET email_verified=1, verify_token=NULL WHERE verify_token=?"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

// ─── Forgot / Reset password ──────────────────────────────────────────────────
function forgotPassword(string $email): array {
    $db = db();
    $email = strtolower(trim($email));
    $stmt = $db->prepare("SELECT id, full_name FROM tblUsers WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) return ['ok' => false, 'msg' => 'Email không tồn tại trong hệ thống.'];

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $stmt = $db->prepare("UPDATE tblUsers SET reset_token=?, reset_expires=? WHERE id=?");
    $stmt->bind_param('ssi', $token, $expires, $user['id']);
    $stmt->execute();

    $link = APP_URL . '/reset_password.php?token=' . $token;
    sendResetEmail($email, $user['full_name'], $link);
    return ['ok' => true, 'msg' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn.'];
}

function resetPassword(string $token, string $newPassword): array {
    $db = db();
    $stmt = $db->prepare(
        "SELECT id FROM tblUsers WHERE reset_token=? AND reset_expires > NOW()"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) return ['ok' => false, 'msg' => 'Link đã hết hạn hoặc không hợp lệ.'];

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare(
        "UPDATE tblUsers SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?"
    );
    $stmt->bind_param('si', $hash, $user['id']);
    $stmt->execute();
    return ['ok' => true, 'msg' => 'Mật khẩu đã được đặt lại thành công.'];
}
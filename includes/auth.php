<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/mail.php';

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

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    $u = currentUser();
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

    // Check duplicate
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

    // Send verification email
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
        return ['ok' => false, 'msg' => 'Tài khoản chưa xác thực email.'];
    }

    startSession();
    $_SESSION['user'] = [
        'id'    => $user['id'],
        'email' => $user['email'],
        'name'  => $user['full_name'],
        'role'  => $user['role'],
        'points'=> $user['total_points'],
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

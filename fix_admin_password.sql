-- ============================================================
-- Fix: Cập nhật mật khẩu Admin về Admin@123
-- Chạy file này trong phpMyAdmin → Import
-- hoặc MySQL CLI: mysql -u root minicine < fix_admin_password.sql
-- ============================================================
USE minicine;

-- Nếu chưa có admin thì INSERT, nếu có rồi thì UPDATE hash
INSERT INTO tblUsers (email, password_hash, full_name, role, email_verified)
VALUES ('admin@minicine.vn', '$2y$12$/v0mL3E1DkF2PTkH4MaA9eFbRi8PjFRPCuy1jx/Rx1Hr.MIjIOLty', 'Quản trị viên', 'admin', 1)
ON DUPLICATE KEY UPDATE
    password_hash = '$2y$12$/v0mL3E1DkF2PTkH4MaA9eFbRi8PjFRPCuy1jx/Rx1Hr.MIjIOLty',
    email_verified = 1,
    role = 'admin';

-- Xác nhận
SELECT id, email, role, email_verified,
       LEFT(password_hash, 7) as hash_prefix
FROM tblUsers WHERE email = 'admin@minicine.vn';

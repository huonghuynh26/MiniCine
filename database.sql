-- ============================================================
-- MiniCine Database Schema
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `minicine`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `minicine`;

-- ─── Users ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblUsers` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email`           VARCHAR(191) NOT NULL UNIQUE,
  `password_hash`   VARCHAR(255) NOT NULL,
  `full_name`       VARCHAR(120) NOT NULL DEFAULT '',
  `role`            ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `email_verified`  TINYINT(1) NOT NULL DEFAULT 0,
  `verify_token`    VARCHAR(64)  DEFAULT NULL,
  `reset_token`     VARCHAR(64)  DEFAULT NULL,
  `reset_expires`   DATETIME     DEFAULT NULL,
  `total_points`    INT NOT NULL DEFAULT 0,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Movies ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblMovies` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`        VARCHAR(255) NOT NULL,
  `description`  TEXT,
  `genre`        VARCHAR(100),
  `duration_min` SMALLINT UNSIGNED NOT NULL DEFAULT 90,
  `poster_url`   VARCHAR(255) DEFAULT NULL,
  `trailer_url`  VARCHAR(255) DEFAULT NULL,
  `status`       ENUM('showing','upcoming','ended') NOT NULL DEFAULT 'upcoming',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Rooms ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblRooms` (
  `id`    TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `floor` TINYINT UNSIGNED NOT NULL,
  `name`  VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- ─── Seats ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblSeats` (
  `id`       SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `room_id`  TINYINT UNSIGNED NOT NULL,
  `row`      CHAR(1) NOT NULL,           -- A-G
  `number`   TINYINT UNSIGNED NOT NULL,  -- 1-8 (or 1-4 for Couple)
  `type`     ENUM('standard','vip','couple') NOT NULL DEFAULT 'standard',
  FOREIGN KEY (`room_id`) REFERENCES `tblRooms`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_seat` (`room_id`,`row`,`number`)
) ENGINE=InnoDB;

-- ─── Shows ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblShows` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `movie_id`   INT UNSIGNED NOT NULL,
  `room_id`    TINYINT UNSIGNED NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time`   DATETIME NOT NULL,
  FOREIGN KEY (`movie_id`) REFERENCES `tblMovies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`)  REFERENCES `tblRooms`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Seat Status (per show) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblSeatStatus` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `show_id`        INT UNSIGNED NOT NULL,
  `seat_id`        SMALLINT UNSIGNED NOT NULL,
  `status`         ENUM('available','held','booked') NOT NULL DEFAULT 'available',
  `version_number` INT UNSIGNED NOT NULL DEFAULT 0,
  `held_until`     DATETIME DEFAULT NULL,
  `held_by`        INT UNSIGNED DEFAULT NULL,   -- user id
  FOREIGN KEY (`show_id`) REFERENCES `tblShows`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`seat_id`) REFERENCES `tblSeats`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_show_seat` (`show_id`,`seat_id`)
) ENGINE=InnoDB;

-- ─── Bookings ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblBookings` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`        INT UNSIGNED NOT NULL,
  `show_id`        INT UNSIGNED NOT NULL,
  `total_price`    DECIMAL(12,0) NOT NULL DEFAULT 0,
  `payment_status` ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `qr_code`        VARCHAR(100) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `tblUsers`(`id`),
  FOREIGN KEY (`show_id`) REFERENCES `tblShows`(`id`)
) ENGINE=InnoDB;

-- ─── Booking Items ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblBookingItems` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id`          INT UNSIGNED NOT NULL,
  `seat_id`             SMALLINT UNSIGNED NOT NULL,
  `price`               DECIMAL(12,0) NOT NULL,
  `original_price`      DECIMAL(12,0) NOT NULL,
  `flash_sale_applied`  TINYINT(1) NOT NULL DEFAULT 0,
  `discount_pct`        TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`booking_id`) REFERENCES `tblBookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`seat_id`)    REFERENCES `tblSeats`(`id`)
) ENGINE=InnoDB;

-- ─── Flash Sales ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblFlashSales` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `show_id`      INT UNSIGNED NOT NULL UNIQUE,
  `discount_pct` TINYINT UNSIGNED NOT NULL DEFAULT 30,
  `trigger_type` ENUM('pre2h','post15m','manual') NOT NULL DEFAULT 'pre2h',
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`show_id`) REFERENCES `tblShows`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Seat Prices ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblPrices` (
  `id`         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `seat_type`  ENUM('standard','vip','couple') NOT NULL UNIQUE,
  `price`      DECIMAL(12,0) NOT NULL
) ENGINE=InnoDB;

-- ─── Points Log ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tblPointsLog` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `booking_id`   INT UNSIGNED DEFAULT NULL,
  `points_delta` INT NOT NULL,
  `reason`       VARCHAR(120) NOT NULL DEFAULT '',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)    REFERENCES `tblUsers`(`id`),
  FOREIGN KEY (`booking_id`) REFERENCES `tblBookings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Seed Data
-- ============================================================

-- Rooms (tầng 1,2,3)
INSERT INTO `tblRooms` (`floor`,`name`) VALUES
(1,'Phòng 1'), (2,'Phòng 2'), (3,'Phòng 3');

-- Prices
INSERT INTO `tblPrices` (`seat_type`,`price`) VALUES
('standard', 90000),
('vip',      130000),
('couple',   200000);

-- Admin account (password: Admin@123)
INSERT INTO `tblUsers` (`email`,`password_hash`,`full_name`,`role`,`email_verified`) VALUES
('admin@minicine.vn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uEutrizEM', 'Quản trị viên', 'admin', 1);

-- Seed seats for all 3 rooms
-- Procedure to insert seats
DROP PROCEDURE IF EXISTS seed_seats;
DELIMITER $$
CREATE PROCEDURE seed_seats()
BEGIN
  DECLARE r TINYINT DEFAULT 1;
  DECLARE row_letter CHAR(1);
  DECLARE col TINYINT;
  DECLARE seat_type ENUM('standard','vip','couple');

  WHILE r <= 3 DO
    -- Rows A-F: standard + VIP
    SET row_letter = 'A';
    WHILE row_letter <= 'F' DO
      SET col = 1;
      WHILE col <= 8 DO
        -- VIP: rows C-E, columns 3-6
        IF row_letter >= 'C' AND row_letter <= 'E' AND col >= 3 AND col <= 6 THEN
          SET seat_type = 'vip';
        ELSE
          SET seat_type = 'standard';
        END IF;
        INSERT IGNORE INTO `tblSeats` (`room_id`,`row`,`number`,`type`)
          VALUES (r, row_letter, col, seat_type);
        SET col = col + 1;
      END WHILE;
      SET row_letter = CHAR(ASCII(row_letter) + 1);
    END WHILE;

    -- Row G: 4 couple seats
    SET col = 1;
    WHILE col <= 4 DO
      INSERT IGNORE INTO `tblSeats` (`room_id`,`row`,`number`,`type`)
        VALUES (r, 'G', col, 'couple');
      SET col = col + 1;
    END WHILE;

    SET r = r + 1;
  END WHILE;
END$$
DELIMITER ;
CALL seed_seats();
DROP PROCEDURE IF EXISTS seed_seats;

-- Sample movies
INSERT INTO `tblMovies` (`title`,`description`,`genre`,`duration_min`,`status`) VALUES
('Avengers: Doomsday','Trận chiến cuối cùng của các siêu anh hùng','Hành động, Khoa học viễn tưởng',150,'showing'),
('Lilo & Stitch (2025)','Phiên bản người thật của bộ phim hoạt hình huyền thoại','Gia đình, Hài',108,'showing'),
('Mission Impossible 8','Ethan Hunt trở lại với nhiệm vụ không thể','Hành động, Gián điệp',140,'upcoming');




-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `bhutan_travel_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bhutan_travel_db`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 1. Hero Settings Table
CREATE TABLE IF NOT EXISTS `hero_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `eyebrow` VARCHAR(255) NOT NULL DEFAULT 'BHUTAN Believe',
    `title` TEXT NOT NULL,
    `media_type` ENUM('image', 'video', 'none') DEFAULT 'none',
    `media_path` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Brand Showcase Table
CREATE TABLE IF NOT EXISTS `brand_showcase` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `eyebrow` VARCHAR(255) DEFAULT 'An Anatomy of the Brand',
    `heading` VARCHAR(255) DEFAULT 'BHUTAN Believe',
    `manifesto` TEXT NOT NULL,
    `block1_title` VARCHAR(255), `block1_subline` VARCHAR(255), `block1_theme` TEXT, `block1_exp` TEXT,
    `block2_title` VARCHAR(255), `block2_subline` VARCHAR(255), `block2_theme` TEXT, `block2_exp` TEXT,
    `block3_title` VARCHAR(255), `block3_subline` VARCHAR(255), `block3_theme` TEXT, `block3_exp` TEXT,
    `block4_title` VARCHAR(255), `block4_subline` VARCHAR(255), `block4_theme` TEXT, `block4_exp` TEXT,
    `block5_title` VARCHAR(255), `block5_subline` VARCHAR(255), `block5_theme` TEXT, `block5_exp` TEXT,
    `block6_title` VARCHAR(255), `block6_subline` VARCHAR(255), `block6_theme` TEXT, `block6_exp` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. Destinations Table
CREATE TABLE IF NOT EXISTS `destinations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `badge` VARCHAR(255) NOT NULL,
    `region` VARCHAR(100) NOT NULL DEFAULT 'region-western',
    `activity` VARCHAR(255) NOT NULL,
    `media_path` VARCHAR(255) NOT NULL,
    `media_type` ENUM('image', 'video') DEFAULT 'image',
    `description` TEXT NOT NULL,
    `highlights` TEXT NOT NULL,
    `tags` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Events Table
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `season` VARCHAR(50) NOT NULL DEFAULT 'spring',
    `category` VARCHAR(100) NOT NULL DEFAULT 'cat-tshechu',
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `date` VARCHAR(100) NOT NULL,
    `tag` VARCHAR(100) NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `media` VARCHAR(255) DEFAULT '',
    `media_type` ENUM('image', 'video') DEFAULT 'image',
    `description` TEXT NOT NULL,
    `highlights` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Inquiries Table
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `nationality` VARCHAR(100) NOT NULL,
    `season` VARCHAR(50) DEFAULT 'Any',
    `duration` INT DEFAULT 1,
    `adults` INT DEFAULT 1,
    `children` INT DEFAULT 0,
    `infants` INT DEFAULT 0,
    `interests` VARCHAR(255) DEFAULT NULL,
    `estimated_total` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('new', 'contacted', 'confirmed', 'archived') DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Luxury Section Table
CREATE TABLE IF NOT EXISTS `luxury_section` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `eyebrow` VARCHAR(255) NOT NULL DEFAULT 'THE BHUTANESE WAY',
    `title` VARCHAR(255) NOT NULL DEFAULT 'A Different Definition of Luxury',
    `paragraph_1` TEXT NOT NULL,
    `paragraph_2` TEXT NOT NULL,
    `divider_quote` VARCHAR(255) NOT NULL DEFAULT 'Your journey becomes part of something greater.',
    `card_1_label` VARCHAR(255) NOT NULL DEFAULT 'BHUTAN, UNRUSHED',
    `card_1_image` VARCHAR(255) DEFAULT '',
    `card_2_label` VARCHAR(255) NOT NULL DEFAULT 'AUTHENTIC CUISINE',
    `card_2_image` VARCHAR(255) DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Trip Planner Base Rates Table
CREATE TABLE IF NOT EXISTS `plan_rates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rate_key` VARCHAR(50) NOT NULL UNIQUE,
    `rate_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Trip Essential Steps Table
CREATE TABLE IF NOT EXISTS `plan_steps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `step_order` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Promotional Banner Settings Table
CREATE TABLE IF NOT EXISTS `promo_banner_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `btn_text` VARCHAR(100) NOT NULL,
    `btn_url` VARCHAR(255) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. SDF Global Settings Table
CREATE TABLE IF NOT EXISTS `sdf_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `eyebrow` VARCHAR(255) NOT NULL,
    `intro` TEXT NOT NULL,
    `closing_title` VARCHAR(255) NOT NULL,
    `closing_desc` TEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 11. SDF Feature Cards Table
CREATE TABLE IF NOT EXISTS `sdf_features` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `desc` TEXT NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `departures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_capacity` INT NOT NULL DEFAULT 12,
  `min_passengers` INT NOT NULL DEFAULT 4,
  `base_price` DECIMAL(10,2) NOT NULL,
  `booked_seats` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `departures` 
ADD COLUMN `title` VARCHAR(255) NOT NULL AFTER `id`,
ADD COLUMN `description` TEXT NULL AFTER `title`;
ALTER TABLE `departures` 
ADD COLUMN `status` ENUM('open', 'guaranteed', 'sold_out') NOT NULL DEFAULT 'open' AFTER `booked_seats`;



CREATE TABLE IF NOT EXISTS `b2c_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_reference` VARCHAR(20) NOT NULL UNIQUE,
  `departure_id` INT NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `seats_booked` INT NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'cancelled') NOT NULL DEFAULT 'paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_departure` 
    FOREIGN KEY (`departure_id`) REFERENCES `departures` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE b2c_bookings 
ADD COLUMN passport_number VARCHAR(50) NULL AFTER customer_email,
ADD COLUMN nationality VARCHAR(100) NULL AFTER passport_number,
ADD COLUMN passport_expiry DATE NULL AFTER nationality;

-- Remove single-passenger columns if you added them previously
ALTER TABLE `b2c_bookings`
DROP COLUMN `passport_number`,
DROP COLUMN `nationality`,
DROP COLUMN `passport_expiry`;

DROP TABLE IF EXISTS `booking_passengers`;

CREATE TABLE `booking_passengers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `passport_number` VARCHAR(50) NOT NULL,
  `nationality` VARCHAR(100) NOT NULL,
  `passport_expiry` DATE NOT NULL,
  `passport_scan_data` LONGBLOB DEFAULT NULL COMMENT 'Raw binary passport image file',
  `passport_mime_type` VARCHAR(50) DEFAULT NULL COMMENT 'MIME type e.g., image/jpeg, image/png, application/pdf',
  `is_autofilled` TINYINT(1) DEFAULT 0 COMMENT '1 if details were extracted via OCR scan, 0 if manually entered',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_passengers_booking`
    FOREIGN KEY (`booking_id`) REFERENCES `b2c_bookings` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- wedding invitation guestbook schema (MySQL 8+)
-- database: wedding

CREATE DATABASE IF NOT EXISTS `wedding`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `wedding`;

CREATE TABLE IF NOT EXISTS `guestbook` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_hash` VARCHAR(64) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_guestbook_created_at` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rsvp` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `side` ENUM('groom','bride') NOT NULL,
  `is_attend` TINYINT(1) NOT NULL DEFAULT 0,
  `guests` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `is_meal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=no, 1=yes, 2=maybe',
  `is_bus` TINYINT(1) NOT NULL DEFAULT 0,
  `message` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_hash` VARCHAR(64) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rsvp_created_at` (`created_at`),
  KEY `idx_rsvp_is_attend` (`is_attend`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

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

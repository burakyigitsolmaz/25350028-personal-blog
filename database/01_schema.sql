-- ============================================================
--  Kişisel Blog — Veritabanı Şeması
--  Burak Yiğit Solmaz · 25350028
--  MariaDB 10.11 / MySQL 8 uyumlu
-- ============================================================
--  Kullanım (phpMyAdmin > SQL sekmesi) veya komut satırı:
--    mysql -u root -e "source C:/xampp/htdocs/kisisel-blog/database/01_schema.sql"
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `kisisel_blog_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kisisel_blog_db`;

-- ------------------------------------------------------------
-- Eski tabloları temizle (tekrar import edilebilmesi için)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `article_tags`;
DROP TABLE IF EXISTS `article_likes`;
DROP TABLE IF EXISTS `downloads`;
DROP TABLE IF EXISTS `activity_log`;
DROP TABLE IF EXISTS `visits`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `settings`;
DROP VIEW IF EXISTS `v_article_list`;
DROP PROCEDURE IF EXISTS `sp_article_search`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 1) admins — yönetim paneli kullanıcıları
-- ------------------------------------------------------------
CREATE TABLE `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2) categories — blog kategorileri (bir yazının tek kategorisi olur)
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(80)  NOT NULL,
  `slug`       VARCHAR(100) NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3) articles — blog yazıları
-- ------------------------------------------------------------
CREATE TABLE `articles` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED NULL,
  `title`        VARCHAR(190) NOT NULL,
  `slug`         VARCHAR(190) NOT NULL,
  `summary`      VARCHAR(400) NOT NULL DEFAULT '',
  `content`      MEDIUMTEXT   NOT NULL,
  `cover_image`  VARCHAR(190) NULL,
  `view_count`   INT UNSIGNED NOT NULL DEFAULT 0,
  `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_articles_slug` (`slug`),
  KEY `idx_articles_category` (`category_id`),
  KEY `idx_articles_published` (`is_published`, `created_at`),
  CONSTRAINT `fk_articles_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4) tags — etiketler
-- 5) article_tags — köprü tablosu (çoktan-çoğa ilişki)
-- ------------------------------------------------------------
CREATE TABLE `tags` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(50)  NOT NULL,
  `slug`       VARCHAR(60)  NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tags_name` (`name`),
  UNIQUE KEY `uq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `article_tags` (
  `article_id` INT UNSIGNED NOT NULL,
  `tag_id`     INT UNSIGNED NOT NULL,
  PRIMARY KEY (`article_id`, `tag_id`),
  KEY `idx_article_tags_tag` (`tag_id`),
  CONSTRAINT `fk_article_tags_article`
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_article_tags_tag`
    FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6) projects — GitHub repo kartları
-- ------------------------------------------------------------
CREATE TABLE `projects` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `tech_stack`  VARCHAR(190) NOT NULL DEFAULT '',
  `repo_url`    VARCHAR(300) NOT NULL DEFAULT '',
  `demo_url`    VARCHAR(300) NULL,
  `image`       VARCHAR(190) NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `is_visible`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_projects_order` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7) skills — yetenek çubukları
-- ------------------------------------------------------------
CREATE TABLE `skills` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(80) NOT NULL,
  `level`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `group_name` VARCHAR(50) NOT NULL DEFAULT 'Diller',
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_skills_group` (`group_name`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8) messages — iletişim formu mesajları
-- ------------------------------------------------------------
CREATE TABLE `messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(190) NOT NULL,
  `subject`    VARCHAR(190) NOT NULL,
  `message`    TEXT NOT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `ip_hash`    CHAR(64) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_read` (`is_read`, `created_at`),
  KEY `idx_messages_ip` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9) settings — anahtar/değer site ayarları
-- ------------------------------------------------------------
CREATE TABLE `settings` (
  `setting_key`   VARCHAR(60) NOT NULL,
  `setting_value` TEXT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10) article_likes — beğeniler (IP başına tek kayıt)
-- ------------------------------------------------------------
CREATE TABLE `article_likes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` INT UNSIGNED NOT NULL,
  `ip_hash`    CHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_like_once` (`article_id`, `ip_hash`),
  CONSTRAINT `fk_likes_article`
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11) visits — sayfa ziyaretleri (istatistik)
-- ------------------------------------------------------------
CREATE TABLE `visits` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page`       VARCHAR(190) NOT NULL,
  `ip_hash`    CHAR(64) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visits_created` (`created_at`),
  KEY `idx_visits_page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12) activity_log — yönetici işlem günlüğü
-- ------------------------------------------------------------
CREATE TABLE `activity_log` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id`   INT UNSIGNED NULL,
  `action`     VARCHAR(100) NOT NULL,
  `detail`     VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_created` (`created_at`),
  CONSTRAINT `fk_activity_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13) downloads — dosya indirme sayacı (CV)
-- ------------------------------------------------------------
CREATE TABLE `downloads` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name`  VARCHAR(190) NOT NULL,
  `ip_hash`    CHAR(64) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_downloads_file` (`file_name`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  VIEW — yayınlanmış yazılar + kategori bilgisi
-- ============================================================
CREATE OR REPLACE VIEW `v_article_list` AS
SELECT
    a.`id`,
    a.`category_id`,
    a.`title`,
    a.`slug`,
    a.`summary`,
    a.`content`,
    a.`cover_image`,
    a.`view_count`,
    a.`created_at`,
    a.`updated_at`,
    c.`name` AS `category_name`,
    c.`slug` AS `category_slug`
FROM `articles` a
LEFT JOIN `categories` c ON c.`id` = a.`category_id`
WHERE a.`is_published` = 1;

-- ============================================================
--  STORED PROCEDURE — yazı araması (başlık, özet, içerik)
-- ============================================================
DROP PROCEDURE IF EXISTS `sp_article_search`;

DELIMITER $$

CREATE PROCEDURE `sp_article_search`(IN `p_term` VARCHAR(100))
BEGIN
    SELECT a.`id`
    FROM `articles` a
    WHERE a.`is_published` = 1
      AND (
            a.`title`   LIKE CONCAT('%', p_term, '%')
         OR a.`summary` LIKE CONCAT('%', p_term, '%')
         OR a.`content` LIKE CONCAT('%', p_term, '%')
      )
    ORDER BY a.`created_at` DESC;
END$$

DELIMITER ;

-- ============================================================
--  Bitti. Şimdi 02_seed.sql dosyasını çalıştırın.
-- ============================================================
SELECT '01_schema.sql tamamlandi' AS `durum`;

-- ============================================================
-- E-WARUNG (Warung Tiga Saudara) - RBAC Users Schema
-- Author ID : 11240044
-- Created    : 2026-06-25
-- Description: Creates users table with role-based access
--              control (admin / user) and seeds two default
--              accounts for testing.
-- ============================================================

-- Ensure the database exists
CREATE DATABASE IF NOT EXISTS `e_warung`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `e_warung`;

-- -----------------------------------------------------------
-- Table: users (RBAC)
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100)   NOT NULL,
    `email`      VARCHAR(150)   NOT NULL UNIQUE,
    `password`   VARCHAR(255)   NOT NULL,
    `role`       ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Seed Data: Default Accounts
-- -----------------------------------------------------------
-- Passwords are hashed using PHP password_hash() with BCRYPT.
--   admin123 → $2y$10$8Rpa5N1mxVQ/d2RrFgqf3ekRTH3.lDeRyjAkCQKNxt9aCwCGiukVi
--   user123  → $2y$10$y1G9Lj5lAzTlbTdNbkOlZuWQ.dwvJBQEGXEi2rNXgaB77J8h.iUX6

INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
(
    'Administrator',
    'admin@ewarung.com',
    '$2y$10$8Rpa5N1mxVQ/d2RrFgqf3ekRTH3.lDeRyjAkCQKNxt9aCwCGiukVi',
    'admin'
),
(
    'Budi Santoso',
    'budi@ewarung.com',
    '$2y$10$y1G9Lj5lAzTlbTdNbkOlZuWQ.dwvJBQEGXEi2rNXgaB77J8h.iUX6',
    'user'
);

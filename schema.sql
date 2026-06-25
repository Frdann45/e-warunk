-- ============================================================
-- Warung Tiga Saudara - E-Commerce Database Schema
-- Author ID : 11240044
-- Created    : 2026-06-24
-- Description: Database schema and seed data for products,
--              orders, and order items.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `e_warung`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `e_warung`;

-- -----------------------------------------------------------
-- Table: products
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100)   NOT NULL,
    `category`    VARCHAR(50)    NOT NULL,
    `unit_desc`   VARCHAR(100)   NOT NULL,
    `price`       DECIMAL(10,2)  NOT NULL,
    `image_url`   VARCHAR(255)   NOT NULL,
    `badge_label` VARCHAR(50)    DEFAULT NULL,
    `created_at`  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: orders
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `order_code`       VARCHAR(50)    NOT NULL UNIQUE,
    `order_date`       DATETIME       NOT NULL,
    `total_price`      DECIMAL(10,2)  NOT NULL,
    `shipping_address` TEXT           NOT NULL,
    `payment_method`   VARCHAR(50)    NOT NULL,
    `status`           VARCHAR(50)    NOT NULL, -- 'Selesai', 'Diproses', 'Dibatalkan'
    `created_at`       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Table: order_items
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `order_id`     INT            NOT NULL,
    `product_name` VARCHAR(100)   NOT NULL,
    `quantity`     INT            NOT NULL,
    `price`        DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Seed Data: Products matching the UI designs
-- -----------------------------------------------------------
INSERT INTO `products` (`name`, `category`, `unit_desc`, `price`, `image_url`, `badge_label`) VALUES
-- Sembako
('Beras Premium',   'Sembako',        '5 kg / Karung',      65000.00, 'images/product-beras.jpg',   'PALING LARIS'),
('Minyak Goreng',   'Sembako',        '2 Liter / Pouch',    32000.00, 'images/product-minyak.jpg',  NULL),
('Beras Rojolele Premium 5kg', 'Sembako', '5 kg / Karung', 65000.00, 'images/rojolele.jpg', NULL),
('Minyak Goreng Bimoli 2L', 'Sembako', '2 Liter / Pouch',  38000.00, 'images/product-bimoli.svg',  NULL),
('Bawang Merah Brebes 500g', 'Sembako', '500 gram',         22000.00, 'images/product-bawangmerah.svg', NULL),
('Paket Sayur Sop Segar', 'Sembako',  '1 Paket Lengkap',    15000.00, 'images/product-sayursop.svg', NULL),
('Beras Premium Pandan Wangi (5kg)', 'Sembako', '5 kg / Karung', 75000.00, 'images/product-pandanwangi.svg', 'WANGI'),

-- Rempah-rempah
('Lada Putih Butir',  'Rempah-rempah',  '100g / Pack',      12000.00, 'images/product-lada.svg',       'Stok Banyak'),
('Ketumbar Biji',     'Rempah-rempah',  '100g / Pack',      5500.00,  'images/product-ketumbar.svg',   'Organik'),
('Cengkeh Kering',    'Rempah-rempah',  '50g / Pack',       15000.00, 'images/product-cengkeh.svg',    'Sisa Sedikit'),
('Kayu Manis Batang', 'Rempah-rempah',  '100g / Pack',      8000.00,  'images/product-kayumanis.svg',  'Terlaris'),

-- Camilan
('Nastar Klasik Premium', 'Camilan',    'Toples 500g',      45000.00, 'images/product-nastar.svg',     'STOK BANYAK'),
('Kacang Atom Garuda',    'Camilan',    'Bungkus 200g',     15500.00, 'images/product-kacang.svg',     'SISA 5'),
('Keripik Singkong Pedas','Camilan',    'Bungkus 150g',     12000.00, 'images/product-singkong.svg',   'STOK BANYAK'),
('Stik Keju Edam',        'Camilan',    'Bungkus 200g',     28500.00, 'images/product-keju.svg',       'STOK BANYAK'),
('Kerupuk Udang Renyah',  'Camilan',    'Bungkus 250g',     35000.00, 'images/product-kerupuk.svg',    'TERLARIS'),
('Keripik Pisang Manis',  'Camilan',    'Bungkus 200g',     18000.00, 'images/product-pisang.svg',     'PROMO');

-- -----------------------------------------------------------
-- Seed Data: Orders & Order Items matching the UI design
-- -----------------------------------------------------------
INSERT INTO `orders` (`id`, `order_code`, `order_date`, `total_price`, `shipping_address`, `payment_method`, `status`) VALUES
(1, 'ORD-2023-1042', '2023-10-24 14:30:00', 125000.00, 'Budi Santoso\n+62 812 3456 7890\nJl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240', 'Bank Transfer', 'Selesai'),
(2, 'ORD-2023-1041', '2023-10-24 11:15:00', 45000.00, 'Budi Santoso\n+62 812 3456 7890\nJl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240', 'E-Wallet', 'Diproses'),
(3, 'ORD-2023-1040', '2023-10-23 09:00:00', 110000.00, 'Budi Santoso\n+62 812 3456 7890\nJl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240', 'COD', 'Dibatalkan');

INSERT INTO `order_items` (`order_id`, `product_name`, `quantity`, `price`) VALUES
(1, 'Beras Ramos 5kg', 1, 75000.00),
(1, 'Minyak Goreng 2L', 1, 32000.00),
(1, 'Bawang Merah Brebes 500g', 1, 18000.00),
(2, 'Minyak Goreng 2L', 1, 32000.00),
(2, 'Camilan Kering', 1, 13000.00),
(3, 'Indomie Goreng 1 Dus', 1, 110000.00);

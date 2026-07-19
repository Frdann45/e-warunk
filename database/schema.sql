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
-- Table: addresses
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `addresses` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`        INT            NOT NULL,
    `recipient_name` VARCHAR(100)   NOT NULL,
    `phone`          VARCHAR(20)    NOT NULL,
    `address_line`   TEXT           NOT NULL,
    `city`           VARCHAR(100)   NOT NULL,
    `province`       VARCHAR(100)   NOT NULL,
    `postal_code`    VARCHAR(10)    NOT NULL,
    `is_primary`     TINYINT(1)     DEFAULT 0,
    `created_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
('Rempah Pilihan',    'Rempah-rempah',  'Aneka bumbu dapur',15000.00, 'images/product-rempah.jpg',     NULL),

-- Camilan
('Nastar Klasik Premium', 'Camilan',    'Toples 500g',      45000.00, 'images/product-nastar.svg',     'STOK BANYAK'),
('Kacang Atom Garuda',    'Camilan',    'Bungkus 200g',     15500.00, 'images/product-kacang.svg',     'SISA 5'),
('Keripik Singkong Pedas','Camilan',    'Bungkus 150g',     12000.00, 'images/product-singkong.svg',   'STOK BANYAK'),
('Stik Keju Edam',        'Camilan',    'Bungkus 200g',     28500.00, 'images/product-keju.svg',       'STOK BANYAK'),
('Kerupuk Udang Renyah',  'Camilan',    'Bungkus 250g',     35000.00, 'images/product-kerupuk.svg',    'TERLARIS'),
('Keripik Pisang Manis',  'Camilan',    'Bungkus 200g',     18000.00, 'images/product-pisang.svg',     'PROMO'),
('Camilan Kering',        'Camilan',    'Kacang, Keripik',  25000.00, 'images/product-camilan.jpg',    'PROMO'),

-- Perawatan & Kecantikan
('Facial Wash Gentle Clean',     'Perawatan & Kecantikan', 'Tube 100ml',       35000.00, 'images/facial-wash.jpg',    'TERLARIS'),
('Sunscreen SPF 50+ PA+++',      'Perawatan & Kecantikan', 'Tube 50ml',        55000.00, 'images/sunscreen.jpg',      'WAJIB PUNYA'),
('Vitamin C Serum 20%',          'Perawatan & Kecantikan', 'Botol 30ml',       89000.00, 'images/serum-wajah.jpg',    'BEST SELLER'),
('Body Lotion Moisturizing',     'Perawatan & Kecantikan', 'Botol 200ml',      42000.00, 'images/body-lotion.jpg',    NULL),
('Sampo Anti Rontok',            'Perawatan & Kecantikan', 'Botol 170ml',      38000.00, 'images/shampoo.jpg',        'STOK BANYAK'),
('Lipstick Matte Velvet',        'Perawatan & Kecantikan', '1 Pcs',            75000.00, 'images/lipstick.jpg',       'PROMO'),
('Eau de Parfum Elegance',       'Perawatan & Kecantikan', 'Botol 50ml',      125000.00, 'images/parfum.jpg',         'PREMIUM'),
('Pasta Gigi Whitening',         'Perawatan & Kecantikan', 'Tube 150g',        18000.00, 'images/pasta-gigi.jpg',     NULL),
('Deodoran Roll-On Fresh',       'Perawatan & Kecantikan', '50ml',             27000.00, 'images/deodoran.jpg',       NULL),
('Baby Lotion Gentle Care',      'Perawatan & Kecantikan', 'Botol 200ml',      32000.00, 'images/baby-lotion.jpg',    'LEMBUT'),

-- Kesehatan
('Paracetamol 500mg',            'Kesehatan', '1 Strip / 10 Tablet', 5000.00,  'images/paracetamol.jpg',    'FAST ACTION'),
('Obat Batuk Sirup',             'Kesehatan', 'Sachet 15ml',        3000.00,  'images/obat-batuk.jpg',     NULL),
('Minyak Kayu Putih 60ml',       'Kesehatan', 'Botol 60ml',         22000.00, 'images/minyak-kayu-putih.jpg','BEST SELLER'),
('Obat Maag Tablet',             'Kesehatan', '1 Strip / 10 Tablet', 7500.00,  'images/maag.jpg',           NULL),
('Vitamin C 1000mg',             'Kesehatan', 'Strip / 6 Tablet',   9000.00,  'images/vitaminc.jpg',       'DAYA TAHAN'),
('Plester Luka Steril',          'Kesehatan', 'Box / 10 Pcs',       6000.00,  'images/plester-luka.jpg',   'P3K'),
('Masker Medis 3-Ply',           'Kesehatan', 'Box / 50 Pcs',       35000.00, 'images/masker-medis.jpg',   'HIGIENIS'),
('Hand Sanitizer Spray 60ml',    'Kesehatan', 'Botol 60ml',         15000.00, 'images/sanitizer.jpg',      NULL),
('Pembalut Wanita Wing',         'Kesehatan', 'Pack / 10 Pcs',      18000.00, 'images/pembalut.jpg',       NULL),
('Madu Herbal Murni 250g',       'Kesehatan', 'Botol 250g',         55000.00, 'images/madu.jpg',           'ALAMI'),
('Minyak Telon Lang 60ml',       'Kesehatan', 'Botol 60ml',         23000.00, 'images/minyak-telon.jpg',   NULL),
('Tolak Angin Cair',             'Kesehatan', 'Box / 12 Sachet',    42000.00, 'images/tolak-angin.jpg',    'POPULER'),

-- Minuman
('Air Mineral Aqua 600ml',       'Minuman',   'Botol 600ml',        4000.00,  'images/aqua.jpg',           'SEGAR'),
('Teh Pucuk Harum 350ml',       'Minuman',   'Botol 350ml',        4500.00,  'images/teh-pucuk.jpg',      'POPULER'),
('Kopi Kapal Api Mantap',       'Minuman',   'Pack 10 Sachet',     14500.00, 'images/kopi-kapal-api.jpg', 'STOK BANYAK'),
('Susu UHT Ultra Milk 250ml',   'Minuman',   'Karton 250ml',       7000.00,  'images/susu-ultra.jpg',     'BEST SELLER'),
('Pocari Sweat 500ml',          'Minuman',   'Botol 500ml',        8500.00,  'images/pocari.jpg',         'ISOTONIK'),
('Kratingdaeng 150ml',          'Minuman',   'Botol Kaca 150ml',   6500.00,  'images/kratingdaeng.jpg',   'STAMINA'),
('Coca-Cola Kaleng 330ml',      'Minuman',   'Kaleng 330ml',       6500.00,  'images/cocacola.jpg',       'DINGIN'),
('Jus Buavita Jambu 250ml',     'Minuman',   'Karton 250ml',       8500.00,  'images/buavita.jpg',        'VITAMIN'),
('Yakult Probiotik (5 Pcs)',    'Minuman',   'Pack isi 5 Botol',   10500.00, 'images/yakult.jpg',         'TERLARIS'),
('Kopi Good Day Cappuccino',    'Minuman',   'Pack 5 Sachet',      12000.00, 'images/goodday.jpg',        'PROMO');

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

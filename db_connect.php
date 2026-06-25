<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Database Connection
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-24
 * Description : Establishes a secure PDO connection to the
 *               MySQL database for the e-warung application.
 * ============================================================
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'e_warung');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create and return a PDO database connection instance.
 *
 * @return PDO
 * @throws PDOException if the connection fails
 */
function getDBConnection(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error in production; display generic message
        error_log('Database Connection Error: ' . $e->getMessage());
        die('Koneksi database gagal. Silakan coba lagi nanti.');
    }
}

// Create a global connection instance
$pdo = getDBConnection();

/**
 * Get local product image path by name.
 *
 * @param  string $name
 * @return string
 */
function getProductImage(string $name): string
{
    $map = [
        // Sembako
        'Beras Premium' => 'images/product-beras.jpg',
        'Minyak Goreng' => 'images/product-minyak.jpg',
        'Beras Rojolele Premium 5kg' => 'images/rojolele.jpg',
        'Minyak Goreng Bimoli 2L' => 'images/bimoli.jpeg',
        'Bawang Merah Brebes 500g' => 'images/bawang.jpeg',

        // Rempah-rempah
        'Lada Putih Butir' => 'images/lada.jpeg',
        'Lada Putih Bubuk' => 'images/lada.jpeg',
        'Ketumbar Biji' => 'images/ketumbar.jpeg',
        'Ketumbar' => 'images/ketumbar.jpeg',
        'Cengkeh Kering' => 'images/cengkeh.jpeg',
        'Cengkeh' => 'images/cengkeh.jpeg',
        'Kayu Manis Batang' => 'images/kayumanis.jpeg',
        'Kayu Manis' => 'images/kayumanis.jpeg',

        // Camilan
        'Nastar Klasik Premium' => 'images/nastar.jpeg',
        'Kacang Atom Garuda' => 'images/katom.jpeg',
        'Keripik Singkong Pedas' => 'images/pikdas.jpeg',
        'Stik Keju Edam' => 'images/stik.jpeg',
        'Kerupuk Udang Renyah' => 'images/kerupuk.jpeg',
        'Keripik Pisang Manis' => 'images/kerpis.jpeg',

        // Promo
        'Minyak Goreng SunCo 2L' => 'images/minyaks.jpeg',
        'Telur Ayam Negeri 1kg' => 'images/telur.jpeg',
        'Gula Pasir Gulaku 1kg' => 'images/gula.jpeg',
        'Teh Celup Premium 25s' => 'images/celup.jpeg',
        'Tepung Terigu Segitiga Biru 1kg' => 'images/setbir.jpeg',
        'Paket Sembako Berkah' => 'images/paket.jpeg'
    ];

    return $map[$name] ?? 'images/logo.png';
}


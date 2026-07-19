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

// Base Path and URL configurations
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . '/');
}

if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $projectFolder = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname(__DIR__)));
    $projectFolder = trim($projectFolder, '/');
    define('BASE_URL', $protocol . $host . ($projectFolder ? '/' . $projectFolder : '') . '/');
}

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
 * Get product image path.
 *
 * Priority order:
 *   1. $dbUrl — value from products.image_url (uploaded file)
 *   2. Name map — hardcoded legacy fallback
 *   3. Default  — images/logo.png
 *
 * @param  string $name
 * @param  string $dbUrl  Value from products.image_url (default '')
 * @return string
 */
function getProductImage(string $name, string $dbUrl = ''): string
{
    // 1. Use DB URL if it is a real uploaded/custom path that exists on disk
    if ($dbUrl !== '' && $dbUrl !== 'assets/images/logo.png' && $dbUrl !== 'assets/images/logo.png') {
        $testUrl = $dbUrl;
        if (strpos($testUrl, 'assets/images/') === 0) {
            $testUrl = 'assets/' . $testUrl;
        }
        if (file_exists(BASE_PATH . $testUrl)) {
            return $testUrl;
        }
    }

    $map = [
        // Sembako
        'Beras Premium' => 'assets/images/product-beras.jpg',
        'Minyak Goreng' => 'assets/images/product-minyak.jpg',
        'Beras Rojolele Premium 5kg' => 'assets/images/rojolele.jpg',
        'Minyak Goreng Bimoli 2L' => 'assets/images/bimoli.jpeg',
        'Bawang Merah Brebes 500g' => 'assets/images/bawang.jpeg',

        // Rempah-rempah
        'Rempah Pilihan' => 'assets/images/product-rempah.jpg',
        'Lada Putih Butir' => 'assets/images/lada.jpeg',
        'Lada Putih Bubuk' => 'assets/images/lada.jpeg',
        'Ketumbar Biji' => 'assets/images/ketumbar.jpeg',
        'Ketumbar' => 'assets/images/ketumbar.jpeg',
        'Cengkeh Kering' => 'assets/images/cengkeh.jpeg',
        'Cengkeh' => 'assets/images/cengkeh.jpeg',
        'Kayu Manis Batang' => 'assets/images/kayumanis.jpeg',
        'Kayu Manis' => 'assets/images/kayumanis.jpeg',

        // Camilan
        'Camilan Kering' => 'assets/images/product-camilan.jpg',
        'Nastar Klasik Premium' => 'assets/images/nastar.jpeg',
        'Kacang Atom Garuda' => 'assets/images/katom.jpeg',
        'Keripik Singkong Pedas' => 'assets/images/pikdas.jpeg',
        'Stik Keju Edam' => 'assets/images/stik.jpeg',
        'Kerupuk Udang Renyah' => 'assets/images/kerupuk.jpeg',
        'Keripik Pisang Manis' => 'assets/images/kerpis.jpeg',

        // Promo
        'Minyak Goreng SunCo 2L' => 'assets/images/minyaks.jpeg',
        'Telur Ayam Negeri 1kg' => 'assets/images/telur.jpeg',
        'Gula Pasir Gulaku 1kg' => 'assets/images/gula.jpeg',
        'Teh Celup Premium 25s' => 'assets/images/celup.jpeg',
        'Tepung Terigu Segitiga Biru 1kg' => 'assets/images/setbir.jpeg',
        'Paket Sembako Berkah' => 'assets/images/paket.jpeg',

        // Perawatan & Kecantikan
        'Facial Wash Gentle Clean' => 'assets/images/facial-wash.jpg',
        'Sunscreen SPF 50+ PA+++' => 'assets/images/sunscreen.jpg',
        'Vitamin C Serum 20%' => 'assets/images/serum-wajah.jpg',
        'Body Lotion Moisturizing' => 'assets/images/body-lotion.jpg',
        'Sampo Anti Rontok' => 'assets/images/shampoo.jpg',
        'Lipstick Matte Velvet' => 'assets/images/lipstick.jpg',
        'Eau de Parfum Elegance' => 'assets/images/parfum.jpg',
        'Pasta Gigi Whitening' => 'assets/images/pasta-gigi.jpg',
        'Deodoran Roll-On Fresh' => 'assets/images/deodoran.jpg',
        'Baby Lotion Gentle Care' => 'assets/images/baby-lotion.jpg',

        // Kesehatan
        'Paracetamol 500mg' => 'assets/images/paracetamol.jpg',
        'Obat Batuk Sirup' => 'assets/images/obat-batuk.jpg',
        'Minyak Kayu Putih 60ml' => 'assets/images/minyak-kayu-putih.jpg',
        'Plester Luka Steril' => 'assets/images/plester-luka.jpg',
        'Masker Medis 3-Ply' => 'assets/images/masker-medis.jpg',
        'Madu Herbal Murni 250g' => 'assets/images/madu.jpg',
        'Obat Maag Tablet' => 'assets/images/maag.jpg',
        'Vitamin C 1000mg' => 'assets/images/vitaminc.jpg',
        'Hand Sanitizer Spray 60ml' => 'assets/images/sanitizer.jpg',
        'Pembalut Wanita Wing' => 'assets/images/pembalut.jpg',
        'Minyak Telon Lang 60ml' => 'assets/images/minyak-telon.jpg',
        'Tolak Angin Cair' => 'assets/images/tolak-angin.jpg',

        // Minuman
        'Air Mineral Aqua 600ml' => 'assets/images/aqua.jpg',
        'Teh Pucuk Harum 350ml' => 'assets/images/teh-pucuk.jpg',
        'Kopi Kapal Api Mantap' => 'assets/images/kopi-kapal-api.jpg',
        'Susu UHT Ultra Milk 250ml' => 'assets/images/susu-ultra.jpg',
        'Pocari Sweat 500ml' => 'assets/images/pocari.jpg',
        'Kratingdaeng 150ml' => 'assets/images/kratingdaeng.jpg',
        'Coca-Cola Kaleng 330ml' => 'assets/images/cocacola.jpg',
        'Jus Buavita Jambu 250ml' => 'assets/images/buavita.jpg',
        'Yakult Probiotik (5 Pcs)' => 'assets/images/yakult.jpg',
        'Kopi Good Day Cappuccino' => 'assets/images/goodday.jpg',
    ];

    // 3. Fallback to logo if name not in map
    return $map[$name] ?? 'assets/images/logo.png';
}


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
        'Rempah Pilihan' => 'images/product-rempah.jpg',
        'Lada Putih Butir' => 'images/lada.jpeg',
        'Lada Putih Bubuk' => 'images/lada.jpeg',
        'Ketumbar Biji' => 'images/ketumbar.jpeg',
        'Ketumbar' => 'images/ketumbar.jpeg',
        'Cengkeh Kering' => 'images/cengkeh.jpeg',
        'Cengkeh' => 'images/cengkeh.jpeg',
        'Kayu Manis Batang' => 'images/kayumanis.jpeg',
        'Kayu Manis' => 'images/kayumanis.jpeg',

        // Camilan
        'Camilan Kering' => 'images/product-camilan.jpg',
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
        'Paket Sembako Berkah' => 'images/paket.jpeg',

        // Perawatan & Kecantikan
        'Facial Wash Gentle Clean' => 'images/facial-wash.jpg',
        'Sunscreen SPF 50+ PA+++' => 'images/sunscreen.jpg',
        'Vitamin C Serum 20%' => 'images/serum-wajah.jpg',
        'Body Lotion Moisturizing' => 'images/body-lotion.jpg',
        'Sampo Anti Rontok' => 'images/shampoo.jpg',
        'Lipstick Matte Velvet' => 'images/lipstick.jpg',
        'Eau de Parfum Elegance' => 'images/parfum.jpg',
        'Pasta Gigi Whitening' => 'images/pasta-gigi.jpg',
        'Deodoran Roll-On Fresh' => 'images/deodoran.jpg',
        'Baby Lotion Gentle Care' => 'images/baby-lotion.jpg',

        // Kesehatan
        'Paracetamol 500mg' => 'images/paracetamol.jpg',
        'Obat Batuk Sirup' => 'images/obat-batuk.jpg',
        'Minyak Kayu Putih 60ml' => 'images/minyak-kayu-putih.jpg',
        'Plester Luka Steril' => 'images/plester-luka.jpg',
        'Masker Medis 3-Ply' => 'images/masker-medis.jpg',
        'Madu Herbal Murni 250g' => 'images/madu.jpg',
        'Obat Maag Tablet' => 'images/maag.jpg',
        'Vitamin C 1000mg' => 'images/vitaminc.jpg',
        'Hand Sanitizer Spray 60ml' => 'images/sanitizer.jpg',
        'Pembalut Wanita Wing' => 'images/pembalut.jpg',
        'Minyak Telon Lang 60ml' => 'images/minyak-telon.jpg',
        'Tolak Angin Cair' => 'images/tolak-angin.jpg',

        // Minuman
        'Air Mineral Aqua 600ml' => 'images/aqua.jpg',
        'Teh Pucuk Harum 350ml' => 'images/teh-pucuk.jpg',
        'Kopi Kapal Api Mantap' => 'images/kopi-kapal-api.jpg',
        'Susu UHT Ultra Milk 250ml' => 'images/susu-ultra.jpg',
        'Pocari Sweat 500ml' => 'images/pocari.jpg',
        'Kratingdaeng 150ml' => 'images/kratingdaeng.jpg',
        'Coca-Cola Kaleng 330ml' => 'images/cocacola.jpg',
        'Jus Buavita Jambu 250ml' => 'images/buavita.jpg',
        'Yakult Probiotik (5 Pcs)' => 'images/yakult.jpg',
        'Kopi Good Day Cappuccino' => 'images/goodday.jpg'
    ];

    return $map[$name] ?? 'images/logo.png';
}


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

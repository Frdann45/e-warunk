<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Admin Portal Entry Point
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Main entry point for the Administrator portal.
 *               Provides left sidebar navigation + admin header.
 *               Routes to admin-only pages:
 *                 - pesanan-masuk (default)
 *                 - proses-pengiriman
 *                 - semua-transaksi
 *                 - tambah-produk
 *                 - buat-promo
 *               Non-admin users are redirected to index.php.
 * ============================================================
 */

session_start();

// ── Authorization: Admin Only ───────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// ── Database Connection ─────────────────────────────────────
require_once __DIR__ . '/db_connect.php';

// ── Page Routing ────────────────────────────────────────────
$page = isset($_GET['page']) ? $_GET['page'] : 'pesanan-masuk';

// Whitelist admin pages
$adminPages = ['pesanan-masuk', 'proses-pengiriman', 'semua-transaksi', 'tambah-produk', 'buat-promo'];
if (!in_array($page, $adminPages)) {
    $page = 'pesanan-masuk';
}

// ── Handle Admin POST Actions early (before HTML output) ────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if ($page === 'pesanan-masuk') {
        require_once __DIR__ . '/pages/pesanan_masuk.php';
    } elseif ($page === 'proses-pengiriman') {
        require_once __DIR__ . '/pages/proses_pengiriman.php';
    } elseif ($page === 'semua-transaksi') {
        require_once __DIR__ . '/pages/semua_transaksi.php';
    }
}

// ── Session-based user info ─────────────────────────────────
$userName     = $_SESSION['name'];
$userRole     = $_SESSION['role'];
$userInitials = strtoupper(substr($_SESSION['name'], 0, 1)) . strtoupper(substr(explode(' ', $_SESSION['name'])[1] ?? $_SESSION['name'], 0, 1));

/**
 * Format price in Indonesian Rupiah format.
 */
function formatRupiah(float $price): string
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel Admin — E-WARUNG Warung Tiga Saudara">
    <title>Admin — E-WARUNG Warung Tiga Saudara</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="images/logo.png">
</head>
<body class="admin-body">
<div class="admin-layout" id="admin-layout">

    <!-- ── ADMIN HEADER ──────────────────────────────────────── -->
    <?php include __DIR__ . '/header_admin.php'; ?>

    <!-- ── BODY: SIDEBAR + MAIN CONTENT ──────────────────────── -->
    <div class="admin-body-inner">

        <!-- ── LEFT SIDEBAR ──────────────────────────────────── -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- ── MAIN CONTENT AREA ─────────────────────────────── -->
        <main class="admin-main" id="admin-main-content">
            <?php
            switch ($page) {
                case 'pesanan-masuk':
                    include __DIR__ . '/pages/pesanan_masuk.php';
                    break;
                case 'proses-pengiriman':
                    include __DIR__ . '/pages/proses_pengiriman.php';
                    break;
                case 'semua-transaksi':
                    include __DIR__ . '/pages/semua_transaksi.php';
                    break;
                case 'tambah-produk':
                    include __DIR__ . '/pages/tambah_produk.php';
                    break;
                case 'buat-promo':
                    include __DIR__ . '/pages/buat_promo.php';
                    break;
                default:
                    include __DIR__ . '/pages/pesanan_masuk.php';
                    break;
            }
            ?>
        </main>
    </div>

    <!-- ── FOOTER ────────────────────────────────────────────── -->
    <footer class="admin-footer" id="admin-footer">
        <span>&copy; 2026 Warung Tiga Saudara — Admin Panel. All rights reserved.</span>
    </footer>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Admin profile dropdown toggle
    var profileWrapper = document.getElementById('admin-profile-wrapper');
    var profileDropdown = document.getElementById('admin-profile-dropdown');
    
    if (profileWrapper && profileDropdown) {
        profileWrapper.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('admin-profile-dropdown--show');
        });
        
        document.addEventListener('click', function() {
            profileDropdown.classList.remove('admin-profile-dropdown--show');
        });
    }
});
</script>
</body>
</html>

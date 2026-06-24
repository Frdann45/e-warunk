<?php
session_start();

// Include database connection
require_once __DIR__ . '/db_connect.php';

// Initialize cart as associative array [product_id => quantity]
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart count (sum of quantities)
$cartCount = array_sum($_SESSION['cart']);

// Dynamic page routing
$page = isset($_GET['page']) ? $_GET['page'] : 'sembako';

// Check for flash message
$cartMessage = null;
if (isset($_SESSION['cart_message'])) {
    $cartMessage = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

/**
 * Format price in Indonesian Rupiah format.
 *
 * @param  float $price
 * @return string
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
    <meta name="description" content="Warung Tiga Saudara — Pusat belanja serba ada untuk kebutuhan sehari-hari yang segar, andal, dan berkualitas tinggi.">
    <title>Warung Tiga Saudara — Toko Kelontong Online</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="images/logo.png">
</head>
<body>
<div class="app-layout <?= $page === 'kontak' ? 'app-layout--no-sidebar' : '' ?>">

    <!-- ═══════════════════════════════════════════════════════
         TOP NAVIGATION BAR
         ═══════════════════════════════════════════════════════ -->
    <nav class="navbar" id="navbar">
        <!-- Brand -->
        <a href="index.php?page=sembako" class="navbar__brand">
            <span class="navbar__logo">
                <img src="images/logo.png" alt="Logo Warung Tiga Saudara">
            </span>
            <span class="navbar__title">Warung Tiga Saudara</span>
        </a>

        <!-- Navbar Search bar removed (moved to page-level catalogs) -->


        <!-- Navigation Links -->
        <div class="navbar__links">
            <a href="index.php?page=sembako" class="navbar__link <?= in_array($page, ['sembako', 'rempah', 'camilan']) ? 'navbar__link--active' : '' ?>" id="nav-beranda">Beranda</a>
            <a href="index.php?page=tentang" class="navbar__link <?= $page === 'tentang' ? 'navbar__link--active' : '' ?>" id="nav-tentang">Tentang Kami</a>
            <a href="index.php?page=promo" class="navbar__link <?= $page === 'promo' ? 'navbar__link--active' : '' ?>" id="nav-promo">Promo Bulanan</a>
            <a href="index.php?page=panduan" class="navbar__link <?= $page === 'panduan' ? 'navbar__link--active' : '' ?>" id="nav-panduan">Panduan Belanja</a>
            <a href="index.php?page=kontak" class="navbar__link <?= $page === 'kontak' ? 'navbar__link--active' : '' ?>" id="nav-kontak">Kontak</a>
        </div>

        <!-- Action Buttons -->
        <div class="navbar__actions">
            <button class="navbar__action-btn" id="btn-notifications" title="Notifikasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
            </button>
            <button class="navbar__action-btn" id="btn-settings" title="Pengaturan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
            </button>
            <div class="navbar__avatar" id="user-avatar" title="Profil">TS</div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
         BODY: SIDEBAR + MAIN CONTENT
         ═══════════════════════════════════════════════════════ -->
    <div class="app-body">

        <!-- ── LEFT SIDEBAR ─────────────────────────────────── -->
        <?php if ($page !== 'kontak'): ?>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__header">
                <div class="sidebar__title">Kategori &amp;<br>Transaksi</div>
                <div class="sidebar__subtitle">Dashboard Toko</div>
            </div>

            <!-- Kategori Produk -->
            <div class="sidebar__section">
                <div class="sidebar__section-label">Kategori Produk</div>
            </div>
            <nav class="sidebar__nav">
                <a href="index.php?page=sembako" class="sidebar__nav-item <?= $page === 'sembako' ? 'sidebar__nav-item--active' : '' ?>" id="cat-sembako">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                    </span>
                    Sembako
                </a>
                <a href="index.php?page=rempah" class="sidebar__nav-item <?= $page === 'rempah' ? 'sidebar__nav-item--active' : '' ?>" id="cat-rempah">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a10 10 0 00-6.88 17.23l.9-.67A8.5 8.5 0 0112 3.5 8.5 8.5 0 0118 18.56l.9.67A10 10 0 0012 2z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </span>
                    Rempah-rempah
                </a>
                <a href="index.php?page=camilan" class="sidebar__nav-item <?= $page === 'camilan' ? 'sidebar__nav-item--active' : '' ?>" id="cat-camilan">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8h1a4 4 0 010 8h-1"/>
                            <path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4"/>
                            <line x1="10" y1="1" x2="10" y2="4"/>
                            <line x1="14" y1="1" x2="14" y2="4"/>
                        </svg>
                    </span>
                    Camilan
                </a>
            </nav>

            <!-- Transaksi -->
            <div class="sidebar__section">
                <div class="sidebar__section-label">Transaksi</div>
            </div>
            <nav class="sidebar__nav">
                <a href="index.php?page=keranjang" class="sidebar__nav-item <?= $page === 'keranjang' ? 'sidebar__nav-item--active' : '' ?>" id="nav-keranjang">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                    </span>
                    Keranjang
                    <?php if ($cartCount > 0): ?>
                        <span class="sidebar__cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="index.php?page=pembayaran" class="sidebar__nav-item <?= $page === 'pembayaran' ? 'sidebar__nav-item--active' : '' ?>" id="nav-pembayaran">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </span>
                    Pembayaran
                </a>
                <a href="index.php?page=riwayat" class="sidebar__nav-item <?= $page === 'riwayat' ? 'sidebar__nav-item--active' : '' ?>" id="nav-riwayat">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    Riwayat
                </a>
            </nav>

            <div class="sidebar__divider"></div>

            <!-- Promo Button -->
            <button class="sidebar__promo-btn" id="btn-promo">
                Ada Promo Baru
            </button>

            <!-- Bottom Links -->
            <div class="sidebar__bottom">
                <nav class="sidebar__nav">
                    <a href="#" class="sidebar__nav-item" id="nav-bantuan">
                        <span class="sidebar__nav-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </span>
                        Bantuan
                    </a>
                    <a href="#" class="sidebar__nav-item" id="nav-keluar">
                        <span class="sidebar__nav-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </span>
                        Keluar
                    </a>
                </nav>
            </div>
        </aside>
        <?php endif; ?>

        <!-- ── MAIN CONTENT AREA ────────────────────────────── -->
        <main class="main-content" id="main-content">
            <?php
            switch ($page) {
                case 'rempah':
                    include __DIR__ . '/pages/rempah.php';
                    break;
                case 'camilan':
                    include __DIR__ . '/pages/camilan.php';
                    break;
                case 'keranjang':
                    include __DIR__ . '/pages/keranjang.php';
                    break;
                case 'pembayaran':
                    include __DIR__ . '/pages/pembayaran.php';
                    break;
                case 'riwayat':
                    include __DIR__ . '/pages/riwayat.php';
                    break;
                case 'tentang':
                    include __DIR__ . '/pages/tentang.php';
                    break;
                case 'promo':
                    include __DIR__ . '/pages/promo.php';
                    break;
                case 'panduan':
                    include __DIR__ . '/pages/panduan.php';
                    break;
                case 'kontak':
                    include __DIR__ . '/pages/kontak.php';
                    break;
                case 'sembako':
                default:
                    include __DIR__ . '/pages/sembako.php';
                    break;
            }
            ?>
        </main>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         FOOTER
         ═══════════════════════════════════════════════════════ -->
    <?php if (in_array($page, ['sembako', 'rempah', 'camilan', 'keranjang', 'pembayaran', 'riwayat'])): ?>
    <footer class="footer" id="footer">
        <span>&copy; 2026 Warung Tiga Saudara. All rights reserved.</span>
        <div class="footer__links">
            <a href="#" class="footer__link" id="link-hubungi">Hubungi Kami</a>
            <a href="#" class="footer__link" id="link-privasi">Kebijakan Privasi</a>
            <a href="#" class="footer__link" id="link-syarat">Syarat Layanan</a>
        </div>
    </footer>
    <?php endif; ?>

</div>

<!-- ═══════════════════════════════════════════════════════════
     TOAST NOTIFICATION (Cart feedback)
     ═══════════════════════════════════════════════════════════ -->
<?php if ($cartMessage): ?>
<div class="toast" id="cart-toast">
    <svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <?= htmlspecialchars($cartMessage) ?>
</div>
<script>
    // Auto-remove toast after animation completes
    setTimeout(function() {
        var toast = document.getElementById('cart-toast');
        if (toast) toast.remove();
    }, 3200);
</script>
<?php endif; ?>

</body>
</html>

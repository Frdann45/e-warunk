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

// Resolve page routing early
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Admin pages security check
$adminPages = ['pesanan-masuk', 'proses-pengiriman', 'semua-transaksi'];
if (in_array($page, $adminPages)) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

// ── Handle checkout POST early (before any HTML output) ─────
if (
    $page === 'pembayaran'
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['place_order'])
) {
    require_once __DIR__ . '/pages/pembayaran_process.php';
}

// ── Handle Admin POST Actions early (before any HTML output) ──
if (
    in_array($page, $adminPages)
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_status'])
) {
    if ($page === 'pesanan-masuk') {
        require_once __DIR__ . '/pages/pesanan_masuk.php';
    } elseif ($page === 'proses-pengiriman') {
        require_once __DIR__ . '/pages/proses_pengiriman.php';
    } elseif ($page === 'semua-transaksi') {
        require_once __DIR__ . '/pages/semua_transaksi.php';
    }
}

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

// ── Session-based user info for navbar ──────────────────────
$isLoggedIn   = isset($_SESSION['user_id']);
$userName     = $isLoggedIn ? $_SESSION['name']  : 'Tamu';
$userRole     = $isLoggedIn ? $_SESSION['role']  : '';
$userInitials = $isLoggedIn
    ? strtoupper(substr($_SESSION['name'], 0, 1)) . strtoupper(substr(explode(' ', $_SESSION['name'])[1] ?? $_SESSION['name'], 0, 1))
    : 'TS';
$userStatus   = $isLoggedIn
    ? ($userRole === 'admin' ? 'Administrator' : 'Pelanggan Setia')
    : 'Belum Masuk';
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
        <a href="index.php?page=beranda" class="navbar__brand">
            <span class="navbar__logo">
                <img src="images/logo.png" alt="Logo Warung Tiga Saudara">
            </span>
            <span class="navbar__title">Warung Tiga Saudara</span>
        </a>

        <!-- Navbar Search bar removed (moved to page-level catalogs) -->


        <!-- Navigation Links -->
        <div class="navbar__links">
            <a href="index.php?page=beranda" class="navbar__link <?= $page === 'beranda' ? 'navbar__link--active' : '' ?>" id="nav-beranda">Beranda</a>
            <a href="index.php?page=tentang" class="navbar__link <?= $page === 'tentang' ? 'navbar__link--active' : '' ?>" id="nav-tentang">Tentang Kami</a>
            <a href="index.php?page=promo" class="navbar__link <?= $page === 'promo' ? 'navbar__link--active' : '' ?>" id="nav-promo">Promo Bulanan</a>
            <a href="index.php?page=panduan" class="navbar__link <?= $page === 'panduan' ? 'navbar__link--active' : '' ?>" id="nav-panduan">Panduan Belanja</a>
            <a href="index.php?page=kontak" class="navbar__link <?= $page === 'kontak' ? 'navbar__link--active' : '' ?>" id="nav-kontak">Kontak</a>
        </div>

        <!-- Action Buttons -->
        <!-- Action Buttons -->
        <div class="navbar__actions" style="position: relative;">
            <button class="navbar__action-btn" id="btn-notifications" title="Notifikasi" style="position: relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                <span class="profile-dropdown__badge" id="navbar-notif-badge" style="position: absolute; top: 0; right: 0; transform: translate(25%, -25%);">3</span>
            </button>
            <button class="navbar__action-btn" id="btn-settings" title="Pengaturan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
            </button>
            <div class="navbar__avatar" id="user-avatar" title="Profil" style="cursor: pointer; user-select: none;"><?= htmlspecialchars($userInitials) ?></div>
            
            <!-- Profile Dropdown Menu -->
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-dropdown__header">
                    <div class="profile-dropdown__avatar" id="dropdown-avatar"><?= htmlspecialchars($userInitials) ?></div>
                    <div class="profile-dropdown__user-details">
                        <h4 class="profile-dropdown__name" id="dropdown-user-name"><?= htmlspecialchars($userName) ?></h4>
                        <p class="profile-dropdown__status" id="dropdown-user-status"><?= htmlspecialchars($userStatus) ?></p>
                    </div>
                </div>
                <div class="profile-dropdown__divider"></div>
                <ul class="profile-dropdown__menu">
                    <li>
                        <?php if ($isLoggedIn): ?>
                        <a href="logout.php" class="profile-dropdown__item" id="menu-login-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="profile-dropdown__icon">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                            </svg>
                            <span>Keluar / Logout</span>
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="profile-dropdown__item" id="menu-login-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="profile-dropdown__icon">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/>
                            </svg>
                            <span>Masuk / Login</span>
                        </a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <button class="profile-dropdown__item" id="menu-notif-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="profile-dropdown__icon">
                                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 01-3.46 0"/>
                            </svg>
                            <span>Notifikasi</span>
                            <span class="profile-dropdown__badge" id="dropdown-notif-badge">3</span>
                        </button>
                    </li>
                    <li>
                        <button class="profile-dropdown__item" id="menu-settings-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="profile-dropdown__icon">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                            </svg>
                            <span>Pengaturan</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
         BODY: SIDEBAR + MAIN CONTENT
         ═══════════════════════════════════════════════════════ -->
    <div class="app-body">

        <!-- ── LEFT SIDEBAR (RBAC Component) ──────────────────── -->
        <?php if ($page !== 'kontak'): ?>
            <?php include __DIR__ . '/sidebar.php'; ?>
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
                    include __DIR__ . '/pages/sembako.php';
                    break;
                case 'pesanan-masuk':
                    include __DIR__ . '/pages/pesanan_masuk.php';
                    break;
                case 'proses-pengiriman':
                    include __DIR__ . '/pages/proses_pengiriman.php';
                    break;
                case 'semua-transaksi':
                    include __DIR__ . '/pages/semua_transaksi.php';
                    break;
                case 'beranda':
                default:
                    include __DIR__ . '/pages/beranda.php';
                    break;
            }
            ?>
        </main>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         FOOTER
         ═══════════════════════════════════════════════════════ -->
    <?php if (in_array($page, ['beranda', 'sembako', 'rempah', 'camilan', 'keranjang', 'pembayaran', 'riwayat'])): ?>
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

<!-- ═══════════════════════════════════════════════════════════
     MODALS & DRAWERS (Login, Notifications, Settings)
     ═══════════════════════════════════════════════════════════ -->

<!-- Login now handled by login.php (RBAC server-side auth) -->

<!-- Notification Drawer -->
<div class="custom-modal" id="notifDrawer">
    <div class="custom-modal__backdrop" id="notifBackdrop"></div>
    <div class="custom-drawer" id="notifDrawerContent">
        <div class="custom-drawer__header">
            <div class="custom-drawer__title-group">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="custom-drawer__title-icon">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                <h3 class="custom-drawer__title">Notifikasi</h3>
            </div>
            <button class="custom-drawer__close" id="closeNotifBtn">&times;</button>
        </div>
        <div class="custom-drawer__body">
            <div class="notif-list">
                <!-- Notif Item 1 -->
                <div class="notif-item notif-item--unread">
                    <div class="notif-item__icon-wrapper notif-item__icon-wrapper--promo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <h4 class="notif-item__title">Diskon Gajian 20% Berakhir Besok!</h4>
                        <p class="notif-item__desc">Jangan lewatkan diskon gajian 20% untuk semua kategori sembako dan minyak goreng.</p>
                        <span class="notif-item__time">5 menit yang lalu</span>
                    </div>
                </div>
                <!-- Notif Item 2 -->
                <div class="notif-item notif-item--unread">
                    <div class="notif-item__icon-wrapper notif-item__icon-wrapper--order">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <h4 class="notif-item__title">Pesanan ORD-2023-1041 Sedang Dikemas</h4>
                        <p class="notif-item__desc">Pesanan Anda berupa Minyak Goreng dan Nastar sedang diproses oleh admin toko.</p>
                        <span class="notif-item__time">1 jam yang lalu</span>
                    </div>
                </div>
                <!-- Notif Item 3 -->
                <div class="notif-item notif-item--unread">
                    <div class="notif-item__icon-wrapper notif-item__icon-wrapper--stock">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <h4 class="notif-item__title">Beras Premium Pandan Wangi Ready!</h4>
                        <p class="notif-item__desc">Stok beras premium Pandan Wangi kembali terisi. Klik untuk pesan sebelum kehabisan.</p>
                        <span class="notif-item__time">3 jam yang lalu</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-drawer__footer">
            <button class="btn-clear-notif" id="clearNotifBtn">Tandai Semua Dibaca</button>
        </div>
    </div>
</div>

<!-- Settings Drawer -->
<div class="custom-modal" id="settingsDrawer">
    <div class="custom-modal__backdrop" id="settingsBackdrop"></div>
    <div class="custom-drawer" id="settingsDrawerContent">
        <div class="custom-drawer__header">
            <div class="custom-drawer__title-group">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="custom-drawer__title-icon">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 012.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                <h3 class="custom-drawer__title">Pengaturan</h3>
            </div>
            <button class="custom-drawer__close" id="closeSettingsBtn">&times;</button>
        </div>
        <div class="custom-drawer__body">
            <div class="settings-list">
                <!-- Theme Option -->
                <div class="settings-item">
                    <div class="settings-item__info">
                        <h4 class="settings-item__title">Mode Gelap (Dark Mode)</h4>
                        <p class="settings-item__desc">Ubah tema tampilan aplikasi menjadi gelap untuk kenyamanan mata.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
                </div>
                <!-- Notif Toggle -->
                <div class="settings-item">
                    <div class="settings-item__info">
                        <h4 class="settings-item__title">Terima Notifikasi</h4>
                        <p class="settings-item__desc">Izinkan browser menampilkan notifikasi pesanan dan promo terbaru.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="notifPushToggle" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <!-- Language Option -->
                <div class="settings-item settings-item--vertical">
                    <div class="settings-item__info">
                        <h4 class="settings-item__title">Bahasa Aplikasi</h4>
                        <p class="settings-item__desc">Pilih bahasa yang digunakan dalam sistem e-warung.</p>
                    </div>
                    <div class="select-wrapper-settings">
                        <select id="languageSelect" class="form-control-select">
                            <option value="id" selected>Bahasa Indonesia</option>
                            <option value="en">English (US)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-drawer__footer">
            <p class="settings-version">Warung Tiga Saudara v1.0.0</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const userAvatar = document.getElementById('user-avatar');
    const profileDropdown = document.getElementById('profileDropdown');
    
    const btnNotifications = document.getElementById('btn-notifications');
    const menuNotifBtn = document.getElementById('menu-notif-btn');
    const notifDrawer = document.getElementById('notifDrawer');
    const notifBackdrop = document.getElementById('notifBackdrop');
    const closeNotifBtn = document.getElementById('closeNotifBtn');
    const clearNotifBtn = document.getElementById('clearNotifBtn');
    
    const btnSettings = document.getElementById('btn-settings');
    const menuSettingsBtn = document.getElementById('menu-settings-btn');
    const settingsDrawer = document.getElementById('settingsDrawer');
    const settingsBackdrop = document.getElementById('settingsBackdrop');
    const closeSettingsBtn = document.getElementById('closeSettingsBtn');
    
    // Settings elements
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // --- Helper to show Toast ---
    function showCustomToast(message) {
        const existing = document.getElementById('custom-js-toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.id = 'custom-js-toast';
        toast.style.top = '80px';
        toast.innerHTML = `
            <svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast) toast.remove();
        }, 3200);
    }
    
    // --- Dark Mode Handler ---
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-theme');
        darkModeToggle.checked = true;
    }
    
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-theme');
            localStorage.setItem('darkMode', 'true');
            showCustomToast('Mode Gelap diaktifkan.');
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('darkMode', 'false');
            showCustomToast('Mode Terang diaktifkan.');
        }
    });

    // --- Toggle Profile Dropdown ---
    userAvatar.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('profile-dropdown--show');
    });
    
    document.addEventListener('click', function() {
        profileDropdown.classList.remove('profile-dropdown--show');
    });
    
    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // --- Modal Open/Close Helpers ---
    function openModal(modalEl) {
        profileDropdown.classList.remove('profile-dropdown--show');
        modalEl.classList.add('custom-modal--show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalEl) {
        modalEl.classList.remove('custom-modal--show');
        if (!document.querySelector('.custom-modal--show')) {
            document.body.style.overflow = '';
        }
    }

    // --- Notification Actions ---
    function openNotifDrawer() {
        openModal(notifDrawer);
    }
    btnNotifications.addEventListener('click', openNotifDrawer);
    menuNotifBtn.addEventListener('click', openNotifDrawer);
    
    closeNotifBtn.addEventListener('click', () => closeModal(notifDrawer));
    notifBackdrop.addEventListener('click', () => closeModal(notifDrawer));
    
    clearNotifBtn.addEventListener('click', function() {
        const unreadItems = document.querySelectorAll('.notif-item--unread');
        unreadItems.forEach(item => item.classList.remove('notif-item--unread'));
        
        document.getElementById('dropdown-notif-badge').style.display = 'none';
        document.getElementById('navbar-notif-badge').style.display = 'none';
        
        showCustomToast('Semua notifikasi ditandai dibaca.');
    });

    // --- Settings Actions ---
    function openSettingsDrawer() {
        openModal(settingsDrawer);
    }
    btnSettings.addEventListener('click', openSettingsDrawer);
    menuSettingsBtn.addEventListener('click', openSettingsDrawer);
    
    closeSettingsBtn.addEventListener('click', () => closeModal(settingsDrawer));
    settingsBackdrop.addEventListener('click', () => closeModal(settingsDrawer));
});
</script>
</body>
</html>

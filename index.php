<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - User Portal (Shopee-Style)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-24
 * Updated     : 2026-07-03
 * Description : Main entry point for the User (Customer) portal.
 *               Full-width layout with NO sidebar.
 *               Uses header_user.php (Shopee-style header).
 *               Admin users are redirected to admin.php.
 * ============================================================
 */

session_start();

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

// ── RBAC: Redirect admins to admin portal ───────────────────
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/admin.php');
    exit;
}

// Initialize cart as associative array [product_id => quantity]
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart count (sum of quantities)
$cartCount = array_sum($_SESSION['cart']);

// Resolve page routing early
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// ── User-only pages whitelist ───────────────────────────────
$userPages = [
    'beranda', 'sembako', 'rempah', 'camilan', 'perawatan', 'kesehatan', 'minuman', 'keranjang',
    'pembayaran', 'riwayat', 'tentang', 'promo', 'panduan', 'kontak', 'pencarian'
];
if (!in_array($page, $userPages)) {
    $page = 'beranda';
}

// ── Handle checkout POST early (before any HTML output) ─────
if (
    $page === 'pembayaran'
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['place_order'])
) {
    require_once __DIR__ . '/pages/pembayaran_process.php';
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
if (!function_exists('formatRupiah')) {
    function formatRupiah(float $price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
}

// ── Session-based user info for header ──────────────────────
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
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="assets/images/logo.png">
</head>
<body>
<div class="user-layout" id="user-layout">

    <!-- ═══════════════════════════════════════════════════════
         USER HEADER 
         ═══════════════════════════════════════════════════════ -->
    <?php include __DIR__ . '/includes/header_user.php'; ?>

    <!-- ═══════════════════════════════════════════════════════
         MAIN CONTENT AREA — Full Width 
         ═══════════════════════════════════════════════════════ -->
    <main class="user-main" id="user-main-content">
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
            case 'perawatan':
                include __DIR__ . '/pages/perawatan.php';
                break;
            case 'kesehatan':
                include __DIR__ . '/pages/kesehatan.php';
                break;
            case 'minuman':
                include __DIR__ . '/pages/minuman.php';
                break;
            case 'pencarian':
                include __DIR__ . '/pages/pencarian.php';
                break;
            case 'beranda':
            default:
                include __DIR__ . '/pages/beranda.php';
                break;
        }
        ?>
    </main>

    <!-- ═══════════════════════════════════════════════════════
         FOOTER — 4-Column Alfagift-Style
         ═══════════════════════════════════════════════════════ -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

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
    setTimeout(function() {
        var toast = document.getElementById('cart-toast');
        if (toast) toast.remove();
    }, 3200);
</script>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     MODALS & DRAWERS (Notifications, Settings)
     ═══════════════════════════════════════════════════════════ -->

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
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
                <h3 class="custom-drawer__title">Pengaturan</h3>
            </div>
            <button class="custom-drawer__close" id="closeSettingsBtn">&times;</button>
        </div>
        <div class="custom-drawer__body">
            <div class="settings-list">
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
            <p class="settings-version">Warung Tiga Saudara v2.0.0</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── DOM Elements ────────────────────────────────────────
    var notifDrawer      = document.getElementById('notifDrawer');
    var notifBackdrop    = document.getElementById('notifBackdrop');
    var closeNotifBtn    = document.getElementById('closeNotifBtn');
    var clearNotifBtn    = document.getElementById('clearNotifBtn');
    var settingsDrawer   = document.getElementById('settingsDrawer');
    var settingsBackdrop = document.getElementById('settingsBackdrop');
    var closeSettingsBtn = document.getElementById('closeSettingsBtn');
    var darkModeToggle   = document.getElementById('darkModeToggle');

    // Header action buttons
    var userNotifBtn     = document.getElementById('user-notif-btn');
    var udmNotifikasi    = document.getElementById('udm-notifikasi');
    var udmPengaturan    = document.getElementById('udm-pengaturan');

    // ── Toast Helper ────────────────────────────────────────
    function showCustomToast(message) {
        var existing = document.getElementById('custom-js-toast');
        if (existing) existing.remove();
        var toast = document.createElement('div');
        toast.className = 'toast';
        toast.id = 'custom-js-toast';
        toast.innerHTML = '<svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>' + message + '</span>';
        document.body.appendChild(toast);
        setTimeout(function() { if (toast) toast.remove(); }, 3200);
    }

    // ── Dark Mode ───────────────────────────────────────────
    var isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-theme');
        if (darkModeToggle) darkModeToggle.checked = true;
    }
    if (darkModeToggle) {
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
    }

    // ── Modal Open/Close Helpers ────────────────────────────
    function openModal(modalEl) {
        if (modalEl) {
            modalEl.classList.add('custom-modal--show');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeModal(modalEl) {
        if (modalEl) {
            modalEl.classList.remove('custom-modal--show');
            if (!document.querySelector('.custom-modal--show')) {
                document.body.style.overflow = '';
            }
        }
    }

    // ── Notification Drawer ─────────────────────────────────
    if (userNotifBtn)  userNotifBtn.addEventListener('click', function() { openModal(notifDrawer); });
    if (udmNotifikasi) udmNotifikasi.addEventListener('click', function() { openModal(notifDrawer); });
    if (closeNotifBtn) closeNotifBtn.addEventListener('click', function() { closeModal(notifDrawer); });
    if (notifBackdrop) notifBackdrop.addEventListener('click', function() { closeModal(notifDrawer); });

    if (clearNotifBtn) {
        clearNotifBtn.addEventListener('click', function() {
            var unreadItems = document.querySelectorAll('.notif-item--unread');
            unreadItems.forEach(function(item) { item.classList.remove('notif-item--unread'); });
            var notifBadge = document.getElementById('user-notif-badge');
            if (notifBadge) notifBadge.style.display = 'none';
            var dropdownBadge = document.querySelector('.user-dropdown__badge-inline');
            if (dropdownBadge) dropdownBadge.style.display = 'none';
            showCustomToast('Semua notifikasi ditandai dibaca.');
        });
    }

    // ── Settings Drawer ─────────────────────────────────────
    if (udmPengaturan)    udmPengaturan.addEventListener('click', function() { openModal(settingsDrawer); });
    if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', function() { closeModal(settingsDrawer); });
    if (settingsBackdrop) settingsBackdrop.addEventListener('click', function() { closeModal(settingsDrawer); });
});
</script>
</body>
</html>

<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Admin Dashboard
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Description : Main entry point for the Administrator.
 *               Provides sales overview, transaction stats,
 *               and access to order processing sections.
 * ============================================================
 */

session_start();
require_once __DIR__ . '/db_connect.php';

// Authorization Check: Admin role only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ── Database Queries for Stats ──────────────────────────────
try {
    // 1. Total Orders count (seeded base 1245 + db count)
    $stmtAll = $pdo->query("SELECT COUNT(*) FROM orders");
    $dbAllCount = (int) $stmtAll->fetchColumn();
    $totalOrdersStat = 1245 + $dbAllCount;

    // 2. Pending Orders (Status: 'Diproses')
    $stmtProc = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Diproses'");
    $pendingOrdersCount = (int) $stmtProc->fetchColumn();

    // 3. Shipped Orders (Status: 'Dikirim')
    $stmtSent = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Dikirim'");
    $shippedOrdersCount = (int) $stmtSent->fetchColumn();

    // 4. Completed Orders (Status: 'Selesai')
    $stmtDone = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Selesai'");
    $completedOrdersCount = (int) $stmtDone->fetchColumn();

    // 5. Total Revenue (seeded base 15250000 + db sum of non-cancelled orders)
    $stmtSpent = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'Dibatalkan'");
    $dbSpentSum = (float) $stmtSpent->fetchColumn();
    $totalRevenueStat = 15250000 + $dbSpentSum;

    // 6. Recent 5 Orders
    $stmtRecent = $pdo->query("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count,
               (SELECT product_name FROM order_items WHERE order_id = o.id ORDER BY id ASC LIMIT 1) AS first_item_name
        FROM orders o
        ORDER BY o.order_date DESC, o.id DESC
        LIMIT 5
    ");
    $recentOrders = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    error_log('Admin Dashboard database error: ' . $e->getMessage());
    $totalOrdersStat = 1245;
    $pendingOrdersCount = 0;
    $shippedOrdersCount = 0;
    $completedOrdersCount = 0;
    $totalRevenueStat = 15250000;
    $recentOrders = [];
}

/**
 * Format price in Indonesian Rupiah.
 */
function formatRupiah(float $price): string
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Session-based user info for navbar
$userName     = $_SESSION['name'];
$userRole     = $_SESSION['role'];
$userInitials = strtoupper(substr($userName, 0, 1)) . strtoupper(substr(explode(' ', $userName)[1] ?? $userName, 0, 1));
$userStatus   = 'Administrator';
$page         = 'beranda'; // Set to beranda so sidebar highlights Dashboard link
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="E-WARUNG Admin Dashboard — Warung Tiga Saudara">
    <title>Dashboard Toko — Administrator</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="images/logo.png">
</head>
<body>
<div class="app-layout">

    <!-- ═══════════════════════════════════════════════════════
         TOP NAVIGATION BAR (Admin customized)
         ═══════════════════════════════════════════════════════ -->
    <nav class="navbar" id="navbar">
        <!-- Brand -->
        <a href="sim_dashboard.php" class="navbar__brand">
            <span class="navbar__logo">
                <img src="images/logo.png" alt="Logo Warung Tiga Saudara">
            </span>
            <span class="navbar__title">Warung Tiga Saudara</span>
        </a>

        <!-- Admin View Label -->
        <div style="background: rgba(109, 58, 26, 0.1); color: var(--color-primary); font-weight: 700; font-size: 0.8rem; padding: 4px 12px; border-radius: 20px;">
            PANEL ADMINISTRATOR
        </div>

        <!-- Action Buttons -->
        <div class="navbar__actions" style="position: relative;">
            <button class="navbar__action-btn" id="btn-notifications" title="Notifikasi" style="position: relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                <span class="profile-dropdown__badge" id="navbar-notif-badge" style="position: absolute; top: 0; right: 0; transform: translate(25%, -25%);"><?= $pendingOrdersCount ?></span>
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
                        <a href="logout.php" class="profile-dropdown__item" id="menu-login-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="profile-dropdown__icon">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                            </svg>
                            <span>Keluar / Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
         BODY: SIDEBAR + MAIN CONTENT
         ═══════════════════════════════════════════════════════ -->
    <div class="app-body">

        <!-- Left Sidebar (RBAC Component) -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="main-content" id="main-content">
            
            <!-- Welcome Header -->
            <div class="history-header fade-in">
                <h1 class="history-header__title">Dashboard Toko</h1>
                <p class="history-header__desc">Selamat datang kembali, <strong><?= htmlspecialchars($userName) ?></strong>. Kelola transaksi dan pantau performa operasional toko Anda.</p>
            </div>

            <!-- Stats Widgets Grid -->
            <div class="stats-grid fade-in" style="grid-template-columns: repeat(4, 1fr); margin-top: 20px;">
                <!-- Stat 1: Total Revenue -->
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12" y2="18.01"/>
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <span class="stat-card__label">TOTAL PENJUALAN</span>
                        <h3 class="stat-card__value" style="font-size: 1.25rem;"><?= formatRupiah($totalRevenueStat) ?></h3>
                        <span class="stat-card__trend text-success">Masa Aktif Toko</span>
                    </div>
                </div>

                <!-- Stat 2: Pending Orders -->
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--yellow">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <span class="stat-card__label">PESANAN MASUK</span>
                        <h3 class="stat-card__value" style="font-size: 1.25rem;"><?= $pendingOrdersCount ?></h3>
                        <span class="stat-card__trend text-warning">Perlu Diproses</span>
                    </div>
                </div>

                <!-- Stat 3: Shipped Orders -->
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <span class="stat-card__label">PROSES PENGIRIMAN</span>
                        <h3 class="stat-card__value" style="font-size: 1.25rem;"><?= $shippedOrdersCount ?></h3>
                        <span class="stat-card__trend text-warning">Dalam Perjalanan</span>
                    </div>
                </div>

                <!-- Stat 4: Completed Orders -->
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div class="stat-card__content">
                        <span class="stat-card__label">PESANAN SELESAI</span>
                        <h3 class="stat-card__value" style="font-size: 1.25rem;"><?= $completedOrdersCount ?></h3>
                        <span class="stat-card__trend text-success">Berhasil Terkirim</span>
                    </div>
                </div>
            </div>

            <!-- Two-Column Section: Recent Orders & Quick Actions -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;" class="fade-in">
                
                <!-- Left: Recent Orders Table -->
                <div class="table-container" style="margin: 0; background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-text-primary);">Transaksi Terbaru</h3>
                        <a href="index.php?page=semua-transaksi" style="font-size: 0.8rem; font-weight: 600; color: var(--color-primary); text-decoration: none;">Lihat Semua &rarr;</a>
                    </div>
                    
                    <table class="history-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ORDER ID</th>
                                <th>MAIN ITEM</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <?php
                                    $mainItemText = htmlspecialchars($order['first_item_name']);
                                    $extraItemsCount = (int) $order['item_count'] - 1;
                                    if ($extraItemsCount > 0) {
                                        $mainItemText .= " (+{$extraItemsCount} item)";
                                    }
                                    ?>
                                    <tr>
                                        <td class="td-order-code">#<?= htmlspecialchars($order['order_code']) ?></td>
                                        <td><?= $mainItemText ?></td>
                                        <td class="td-price"><?= formatRupiah((float) $order['total_price']) ?></td>
                                        <td>
                                            <span class="status-badge status-badge--<?= strtolower($order['status']) ?>">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 24px; color: var(--color-text-light);">Belum ada data transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Right: Quick Actions -->
                <div style="background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-text-primary); margin-bottom: 4px;">Pintasan Cepat</h3>
                    
                    <a href="index.php?page=pesanan-masuk" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 12px 16px; background: var(--bg); border-radius: var(--radius-md); transition: background 0.2s;" onmouseover="this.style.background='#eee'" onmouseout="this.style.background='var(--bg)'">
                        <div style="background: rgba(243, 156, 18, 0.1); color: #F39C12; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary); margin: 0;">Pesanan Masuk</h4>
                            <p style="font-size: 0.7rem; color: var(--color-text-light); margin: 0;">Proses pesanan pelanggan baru</p>
                        </div>
                    </a>

                    <a href="index.php?page=proses-pengiriman" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 12px 16px; background: var(--bg); border-radius: var(--radius-md); transition: background 0.2s;" onmouseover="this.style.background='#eee'" onmouseout="this.style.background='var(--bg)'">
                        <div style="background: rgba(142, 68, 173, 0.1); color: #8E44AD; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary); margin: 0;">Proses Pengiriman</h4>
                            <p style="font-size: 0.7rem; color: var(--color-text-light); margin: 0;">Pantau barang dalam perjalanan</p>
                        </div>
                    </a>

                    <a href="index.php?page=semua-transaksi" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 12px 16px; background: var(--bg); border-radius: var(--radius-md); transition: background 0.2s;" onmouseover="this.style.background='#eee'" onmouseout="this.style.background='var(--bg)'">
                        <div style="background: rgba(41, 128, 185, 0.1); color: #2980B9; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary); margin: 0;">Semua Transaksi</h4>
                            <p style="font-size: 0.7rem; color: var(--color-text-light); margin: 0;">Kelola & edit riwayat transaksi</p>
                        </div>
                    </a>
                </div>

            </div>

    </div>
    <!-- Footer -->
    <footer class="footer" id="footer">
        <span>&copy; 2026 Warung Tiga Saudara. All rights reserved.</span>
        <div class="footer__links">
            <a href="#" class="footer__link" id="link-hubungi">Hubungi Kami</a>
            <a href="#" class="footer__link" id="link-privasi">Kebijakan Privasi</a>
            <a href="#" class="footer__link" id="link-syarat">Syarat Layanan</a>
        </div>
    </footer>
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
                        <p class="settings-item__desc">Ubah tema tampilan aplikasi menjadi gelap.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
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
    const userAvatar = document.getElementById('user-avatar');
    const profileDropdown = document.getElementById('profileDropdown');
    const btnSettings = document.getElementById('btn-settings');
    const settingsDrawer = document.getElementById('settingsDrawer');
    const settingsBackdrop = document.getElementById('settingsBackdrop');
    const closeSettingsBtn = document.getElementById('closeSettingsBtn');
    const darkModeToggle = document.getElementById('darkModeToggle');

    // Avatar Dropdown
    userAvatar.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('profile-dropdown--show');
    });
    document.addEventListener('click', function() {
        profileDropdown.classList.remove('profile-dropdown--show');
    });

    // Settings Modal
    btnSettings.addEventListener('click', () => {
        settingsDrawer.classList.add('custom-modal--show');
        document.body.style.overflow = 'hidden';
    });
    const closeSettings = () => {
        settingsDrawer.classList.remove('custom-modal--show');
        document.body.style.overflow = '';
    };
    closeSettingsBtn.addEventListener('click', closeSettings);
    settingsBackdrop.addEventListener('click', closeSettings);

    // Dark Mode
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-theme');
        darkModeToggle.checked = true;
    }
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-theme');
            localStorage.setItem('darkMode', 'true');
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('darkMode', 'false');
        }
    });
});
</script>
</body>
</html>

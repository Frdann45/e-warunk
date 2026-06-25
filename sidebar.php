<?php
/**
 * ============================================================
 * E-WARTA (Warung Tiga Saudara) - Reusable RBAC Sidebar
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Description : Dynamic sidebar component with Role-Based
 *               Access Control (RBAC). Renders admin or user
 *               navigation menus based on $_SESSION['role'].
 *
 * Prerequisite: session_start() must be called BEFORE including
 *               this file (typically in the main layout).
 *
 * Usage:
 *   <?php include __DIR__ . '/sidebar.php'; ?>
 * ============================================================
 */

// ── Resolve current role & active page ──────────────────────
$sidebarRole   = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$sidebarPage   = isset($_GET['page'])     ? $_GET['page']     : 'beranda';
$isAdmin       = ($sidebarRole === 'admin');

// ── Dashboard link differs by role ──────────────────────────
$dashboardLink = $isAdmin ? 'sim_dashboard.php' : 'index.php';

// ── Cart count for user badge ───────────────────────────────
$sidebarCartCount = 0;
if (!$isAdmin && isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $sidebarCartCount = array_sum($_SESSION['cart']);
}

// ── Admin: pending order count for badge ────────────────────
$adminPendingCount = 0;
if ($isAdmin && isset($pdo)) {
    try {
        $stmtPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Diproses'");
        $adminPendingCount = (int) $stmtPending->fetchColumn();
    } catch (PDOException $e) {
        error_log('Sidebar badge query error: ' . $e->getMessage());
    }
}
?>

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR COMPONENT (RBAC)
     ═══════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- ── Branding Header ───────────────────────────────────── -->
    <div class="sidebar__header">
        <div class="sidebar__title">Kategori &amp;<br>Transaksi</div>
        <div class="sidebar__subtitle">Dashboard Toko</div>
    </div>

    <!-- ── Dashboard Link ────────────────────────────────────── -->
    <div class="sidebar__section">
        <div class="sidebar__section-label">Dashboard</div>
    </div>
    <nav class="sidebar__nav">
        <a href="<?= htmlspecialchars($dashboardLink) ?>"
           class="sidebar__nav-item <?= ($sidebarPage === 'beranda' || basename($_SERVER['SCRIPT_NAME']) === 'sim_dashboard.php') ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-dashboard">
            <span class="sidebar__nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                </svg>
            </span>
            Dashboard
        </a>
    </nav>

    <?php if (!$isAdmin): ?>
    <!-- ════════════════════════════════════════════════════════
         USER MENU: Kategori Produk
         ════════════════════════════════════════════════════════ -->
    <div class="sidebar__section">
        <div class="sidebar__section-label">Kategori Produk</div>
    </div>
    <nav class="sidebar__nav">
        <a href="index.php?page=sembako" class="sidebar__nav-item <?= $sidebarPage === 'sembako' ? 'sidebar__nav-item--active' : '' ?>" id="cat-sembako">
            <span class="sidebar__nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </span>
            Sembako
        </a>
        <a href="index.php?page=rempah" class="sidebar__nav-item <?= $sidebarPage === 'rempah' ? 'sidebar__nav-item--active' : '' ?>" id="cat-rempah">
            <span class="sidebar__nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a10 10 0 00-6.88 17.23l.9-.67A8.5 8.5 0 0112 3.5 8.5 8.5 0 0118 18.56l.9.67A10 10 0 0012 2z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </span>
            Rempah-rempah
        </a>
        <a href="index.php?page=camilan" class="sidebar__nav-item <?= $sidebarPage === 'camilan' ? 'sidebar__nav-item--active' : '' ?>" id="cat-camilan">
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
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════
         TRANSAKSI SECTION (role-based)
         ════════════════════════════════════════════════════════ -->
    <div class="sidebar__section">
        <div class="sidebar__section-label">Transaksi</div>
    </div>
    <nav class="sidebar__nav">

    <?php if ($isAdmin): ?>
        <!-- ── ADMIN: Order Management ───────────────────────── -->
        <a href="index.php?page=pesanan-masuk"
           class="sidebar__nav-item <?= $sidebarPage === 'pesanan-masuk' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-pesanan-masuk">
            <span class="sidebar__nav-icon">
                <!-- Box / Package icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </span>
            Pesanan Masuk
            <?php if ($adminPendingCount > 0): ?>
                <span class="sidebar__cart-badge"><?= $adminPendingCount ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?page=proses-pengiriman"
           class="sidebar__nav-item <?= $sidebarPage === 'proses-pengiriman' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-proses-pengiriman">
            <span class="sidebar__nav-icon">
                <!-- Truck icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </span>
            Proses Pengiriman
        </a>

        <a href="index.php?page=semua-transaksi"
           class="sidebar__nav-item <?= $sidebarPage === 'semua-transaksi' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-semua-transaksi">
            <span class="sidebar__nav-icon">
                <!-- History / List icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
            Semua Transaksi
        </a>

    <?php else: ?>
        <!-- ── USER: Shopping Transactions ────────────────────── -->
        <a href="index.php?page=keranjang"
           class="sidebar__nav-item <?= $sidebarPage === 'keranjang' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-keranjang">
            <span class="sidebar__nav-icon">
                <!-- Cart icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
            </span>
            Keranjang
            <?php if ($sidebarCartCount > 0): ?>
                <span class="sidebar__cart-badge"><?= $sidebarCartCount ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?page=pembayaran"
           class="sidebar__nav-item <?= $sidebarPage === 'pembayaran' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-pembayaran">
            <span class="sidebar__nav-icon">
                <!-- Credit-card icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </span>
            Pembayaran
        </a>

        <a href="index.php?page=riwayat"
           class="sidebar__nav-item <?= $sidebarPage === 'riwayat' ? 'sidebar__nav-item--active' : '' ?>"
           id="nav-riwayat">
            <span class="sidebar__nav-icon">
                <!-- Clock icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
            Riwayat Belanja
        </a>
    <?php endif; ?>

    </nav>

    <div class="sidebar__divider"></div>

    <?php if (!$isAdmin): ?>
    <!-- ── Promo Button (user only) ──────────────────────────── -->
    <button class="sidebar__promo-btn" id="btn-promo" onclick="window.location.href='index.php?page=promo'">
        Ada Promo Baru
    </button>
    <?php endif; ?>

    <!-- ── Bottom Section ────────────────────────────────────── -->
    <div class="sidebar__bottom">
        <nav class="sidebar__nav">
            <?php
            /**
             * Author ID: 11240044
             * Description: Dynamic Bantuan (Help) menu logic based on RBAC role.
             */
            $store_phone = "6281234567890";
            $session_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

            if ($session_role === 'admin') {
                // Admin role: render system manual link
                echo '<li style="list-style: none; display: block; width: 100%;">';
                echo '<a href="help_admin.php" class="sidebar__nav-item" id="nav-bantuan">';
                echo '<i class="icon-help"></i> Bantuan &amp; Panduan Sistem';
                echo '</a>';
                echo '</li>';
            } else {
                // User role: construct WhatsApp contact URL with pre-formatted message
                $session_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Pelanggan';
                $wa_message = "Halo Admin e-warung, saya butuh bantuan. Nama saya: " . $session_name . ". Apakah ada admin yang aktif?";
                
                // urlencode() is used to safely convert space and special characters into a format suitable for URL transmission (e.g. %20 for spaces).
                $encoded_message = urlencode($wa_message);
                $wa_url = "https://wa.me/" . $store_phone . "?text=" . $encoded_message;

                echo '<li style="list-style: none; display: block; width: 100%;">';
                echo '<a href="' . htmlspecialchars($wa_url) . '" class="sidebar__nav-item" id="nav-bantuan" target="_blank">';
                echo '<i class="icon-help"></i> Bantuan';
                echo '</a>';
                echo '</li>';
            }
            ?>
            <a href="logout.php" class="sidebar__nav-item" id="nav-keluar">
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

<?php
/**
 * ============================================================
 * INTEGRATION GUIDE
 * ============================================================
 *
 * 1. PREREQUISITES:
 *    - session_start() must be called BEFORE including this file.
 *    - db_connect.php should be included so that $pdo is available
 *      (used for the admin pending-orders badge query).
 *    - The user must be logged in with $_SESSION['role'] set to
 *      either 'admin' or 'user'.
 *
 * 2. USAGE IN index.php (user storefront):
 *    Replace the existing inline <aside class="sidebar">…</aside>
 *    block with:
 *
 *        if ($page !== 'kontak') {
 *            include __DIR__ . '/sidebar.php';
 *        }
 *
 * 3. USAGE IN sim_dashboard.php (admin dashboard):
 *    After session_start() and require db_connect.php:
 *
 *        include __DIR__ . '/sidebar.php';
 *
 * 4. CSS:
 *    The sidebar styles already exist in style.css. No extra CSS
 *    file is needed — all classes (sidebar, sidebar__nav-item,
 *    sidebar__cart-badge, etc.) are pre-defined in the existing
 *    stylesheet.
 * ============================================================
 */
?>

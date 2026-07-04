<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Admin-Only Sidebar
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Updated     : 2026-07-03
 * Description : Left sidebar component for the Admin Portal.
 *               CRITICAL: No "Dashboard" link.
 *               
 *               Group 1 — KELOLA PRODUK:
 *                 • Tambah Produk
 *                 • Buat Promo
 *               Group 2 — TRANSAKSI:
 *                 • Pesanan Masuk (dynamic badge)
 *                 • Proses Pengiriman
 *                 • Semua Transaksi
 *               Group 3 — BANTUAN:
 *                 • Help (admin → help_admin.php)
 *                 • Help (user fallback → WhatsApp wa.me)
 *               Bottom — Keluar
 *
 * Prerequisite: session_start() and db_connect.php must be
 *               loaded BEFORE including this file.
 * ============================================================
 */

// ── Resolve current role & active page ──────────────────────
$sidebarRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$sidebarPage = isset($_GET['page'])     ? $_GET['page']     : 'pesanan-masuk';
$isAdmin     = ($sidebarRole === 'admin');

// ── Admin: pending order count for badge ────────────────────
// Dynamic count: SELECT COUNT(*) FROM orders WHERE status = 'Menunggu Diproses'
// Fallback to 'Diproses' for backward-compatibility with existing seed data
$adminPendingCount = 0;
if ($isAdmin && isset($pdo)) {
    try {
        $stmtPending = $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status IN ('Menunggu Diproses', 'Diproses')"
        );
        $adminPendingCount = (int) $stmtPending->fetchColumn();
    } catch (PDOException $e) {
        error_log('Sidebar badge query error: ' . $e->getMessage());
    }
}
?>

<!-- ═══════════════════════════════════════════════════════════
     ADMIN SIDEBAR COMPONENT
     ═══════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- ── Branding Header ───────────────────────────────────── -->
    <div class="sidebar__header">
        <div class="sidebar__title">Kelola<br>Toko</div>
        <div class="sidebar__subtitle">Panel Administrasi</div>
    </div>

    <!-- ════════════════════════════════════════════════════════
         GROUP 1: KELOLA PRODUK
         ════════════════════════════════════════════════════════ -->
    <div class="sidebar__section">
        <h3 class="menu-title">KELOLA PRODUK</h3>
    </div>
    <nav class="sidebar__nav">
        <ul class="sidebar__menu-list">
            <!-- Tambah Produk -->
            <li>
                <a href="admin.php?page=tambah-produk"
                   class="sidebar__nav-item <?= $sidebarPage === 'tambah-produk' ? 'sidebar__nav-item--active' : '' ?>"
                   id="nav-tambah-produk">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    </span>
                    Tambah Produk
                </a>
            </li>

            <!-- Buat Promo -->
            <li>
                <a href="admin.php?page=buat-promo"
                   class="sidebar__nav-item <?= $sidebarPage === 'buat-promo' ? 'sidebar__nav-item--active' : '' ?>"
                   id="nav-buat-promo">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                    </span>
                    Buat Promo
                </a>
            </li>
        </ul>
    </nav>

    <!-- ════════════════════════════════════════════════════════
         GROUP 2: TRANSAKSI
         ════════════════════════════════════════════════════════ -->
    <div class="sidebar__section">
        <h3 class="menu-title">TRANSAKSI</h3>
    </div>
    <nav class="sidebar__nav">
        <ul class="sidebar__menu-list">
            <!-- Pesanan Masuk (with dynamic badge) -->
            <li>
                <a href="admin.php?page=pesanan-masuk"
                   class="sidebar__nav-item <?= $sidebarPage === 'pesanan-masuk' ? 'sidebar__nav-item--active' : '' ?>"
                   id="nav-pesanan-masuk">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </span>
                    Pesanan Masuk
                    <?php if ($adminPendingCount > 0): ?>
                        <span class="sidebar__badge"><?= $adminPendingCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Proses Pengiriman -->
            <li>
                <a href="admin.php?page=proses-pengiriman"
                   class="sidebar__nav-item <?= $sidebarPage === 'proses-pengiriman' ? 'sidebar__nav-item--active' : '' ?>"
                   id="nav-proses-pengiriman">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </span>
                    Proses Pengiriman
                </a>
            </li>

            <!-- Semua Transaksi -->
            <li>
                <a href="admin.php?page=semua-transaksi"
                   class="sidebar__nav-item <?= $sidebarPage === 'semua-transaksi' ? 'sidebar__nav-item--active' : '' ?>"
                   id="nav-semua-transaksi">
                    <span class="sidebar__nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    Semua Transaksi
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar__divider"></div>

    <!-- ════════════════════════════════════════════════════════
         GROUP 3: HELP / BANTUAN
         ════════════════════════════════════════════════════════ -->
    <div class="sidebar__bottom">
        <nav class="sidebar__nav">
            <ul class="sidebar__menu-list">
                <?php
                /**
                 * Author ID: 11240044
                 * Dynamic Bantuan (Help) menu logic based on RBAC role.
                 */
                $store_phone  = "6281234567890";
                $session_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

                if ($session_role === 'admin') {
                    // Admin: link to system manual
                    echo '<li>';
                    echo '<a href="help_admin.php" class="sidebar__nav-item" id="nav-bantuan">';
                    echo '<span class="sidebar__nav-icon"><i class="icon-help" aria-hidden="true">';
                    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                    echo '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
                    echo '</svg>';
                    echo '</i></span>';
                    echo 'Bantuan &amp; Panduan';
                    echo '</a>';
                    echo '</li>';
                } else {
                    // User fallback: WhatsApp contact link with pre-filled message
                    $session_name  = isset($_SESSION['name']) ? $_SESSION['name'] : 'Pelanggan';
                    $wa_message    = "Halo Admin e-warung, saya butuh bantuan. Nama saya: " . $session_name . ". Apakah ada admin yang aktif?";
                    $encoded_msg   = urlencode($wa_message);
                    $wa_url        = "https://wa.me/" . $store_phone . "?text=" . $encoded_msg;

                    echo '<li>';
                    echo '<a href="' . htmlspecialchars($wa_url) . '" class="sidebar__nav-item" id="nav-bantuan" target="_blank">';
                    echo '<span class="sidebar__nav-icon"><i class="icon-help" aria-hidden="true">';
                    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                    echo '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
                    echo '</svg>';
                    echo '</i></span>';
                    echo 'Bantuan';
                    echo '</a>';
                    echo '</li>';
                }
                ?>

                <!-- Keluar / Logout -->
                <li>
                    <a href="logout.php" class="sidebar__nav-item" id="nav-keluar">
                        <span class="sidebar__nav-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </span>
                        Keluar
                    </a>
                </li>
            </ul>
        </nav>
    </div>

</aside>

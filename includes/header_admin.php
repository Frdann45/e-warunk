<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Admin Header
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Clean professional top navbar for the Admin
 *               Portal (back-office).
 *               - Left: Logo + brand + "Admin" badge
 *               - Center: Search bar for Order IDs
 *               - Right: "Kunjungi Toko" button (target=_blank),
 *                 Profile avatar
 *
 * Prerequisite: session_start() must be called BEFORE including
 *               this file. $_SESSION['name'] should be set.
 * ============================================================
 */

$adminHeaderName     = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Admin';
$adminHeaderInitials = isset($_SESSION['name'])
    ? strtoupper(substr($_SESSION['name'], 0, 1)) . strtoupper(substr(explode(' ', $_SESSION['name'])[1] ?? $_SESSION['name'], 0, 1))
    : 'AD';
?>

<!-- ═══════════════════════════════════════════════════════════
     ADMIN HEADER — Professional Top Navbar
     ═══════════════════════════════════════════════════════════ -->
<header class="admin-header" id="admin-header">
    <div class="admin-header__inner">

        <!-- ── Brand / Logo ──────────────────────────────────── -->
        <a href="<?= BASE_URL ?>admin/admin.php" class="admin-header__brand" id="admin-brand-link">
            <span class="admin-header__logo">
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo Warung Tiga Saudara" onerror="this.style.display='none';">
            </span>
            <span class="admin-header__brand-text">Warung Tiga Saudara</span>
            <span class="admin-header__role-badge">Admin</span>
        </a>

        <!-- ── Order ID Search ───────────────────────────────── -->
        <div class="admin-header__search-wrapper">
            <form action="<?= BASE_URL ?>admin/admin.php" method="GET" class="admin-header__search-form" id="admin-search-form">
                <input type="hidden" name="page" value="semua-transaksi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-header__search-icon" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="admin-header__search-input"
                    id="admin-search-input"
                    placeholder="Cari Order ID..."
                    autocomplete="off"
                >
            </form>
        </div>

        <!-- ── Right Actions ─────────────────────────────────── -->
        <div class="admin-header__actions">

            <!-- Profile Avatar -->
            <div class="admin-header__profile" id="admin-profile-wrapper">
                <div class="admin-header__avatar" title="<?= $adminHeaderName ?>">
                    <?= $adminHeaderInitials ?>
                </div>
                <!-- Admin profile dropdown -->
                <div class="admin-profile-dropdown" id="admin-profile-dropdown">
                    <div class="admin-profile-dropdown__header">
                        <div class="admin-profile-dropdown__avatar"><?= $adminHeaderInitials ?></div>
                        <div class="admin-profile-dropdown__info">
                            <h4><?= $adminHeaderName ?></h4>
                            <p>Administrator</p>
                        </div>
                    </div>
                    <div class="admin-profile-dropdown__divider"></div>
                    <ul class="admin-profile-dropdown__menu">
                        <li>
                            <a href="<?= BASE_URL ?>logout.php" class="admin-profile-dropdown__item" id="admin-logout-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Keluar</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</header>

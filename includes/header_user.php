<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - User Header (Shopee-Style)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Shopee-inspired top header for the User Portal.
 *               - Left: Logo + brand name
 *               - Center: Wide global search bar
 *               - Right: Cart (orange badge), Bell, Profile
 *               - Profile CSS hover dropdown with: User Name,
 *                 Pesanan Saya, Notifikasi, Pengaturan, Keluar
 *               - CRITICAL: No "Pembayaran" or "Dashboard" links
 *
 * Prerequisite: session_start() and db_connect.php must be
 *               loaded BEFORE including this file. Variables
 *               $cartCount, $userName, $userInitials must exist.
 * ============================================================
 */

// ── Resolve user info for header ────────────────────────────
$headerLoggedIn = isset($_SESSION['user_id']);
$headerUserName = $headerLoggedIn ? htmlspecialchars($_SESSION['name']) : 'Tamu';
$headerAvatar   = ($headerLoggedIn && !empty($_SESSION['avatar_url'])) ? $_SESSION['avatar_url'] : null;
$headerInitials = $headerLoggedIn
    ? strtoupper(substr($_SESSION['name'], 0, 1)) . strtoupper(substr(explode(' ', $_SESSION['name'])[1] ?? $_SESSION['name'], 0, 1))
    : 'TS';
$headerCartCount = isset($cartCount) ? (int) $cartCount : 0;
?>

<!-- ═══════════════════════════════════════════════════════════
     USER HEADER — Shopee-Style Top Navigation
     ═══════════════════════════════════════════════════════════ -->
<header class="user-header" id="user-header">
    <div class="user-header__inner">

        <!-- ── Brand / Logo ──────────────────────────────────── -->
        <a href="<?= BASE_URL ?>index.php" class="user-header__brand" id="user-brand-link">
            <span class="user-header__logo">
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo Warung Tiga Saudara" onerror="this.style.display='none';">
            </span>
            <span class="user-header__brand-text">e-warung</span>
        </a>


        <!-- ── Global Search Bar ─────────────────────────────── -->
        <div class="user-header__search-wrapper">
            <form action="<?= BASE_URL ?>index.php" method="GET" class="user-header__search-form" id="global-search-form">
                <input type="hidden" name="page" value="pencarian">
                <input
                    type="text"
                    name="search"
                    class="user-header__search-input"
                    id="global-search-input"
                    placeholder="Cari..."
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                    autocomplete="off"
                >
                <button type="submit" class="user-header__search-btn" id="global-search-btn" title="Cari">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- ── Action Icons ──────────────────────────────────── -->
        <div class="user-header__actions">

            <!-- Cart Icon -->
            <a href="index.php?page=keranjang" class="user-header__icon-btn" id="user-cart-btn" title="Keranjang">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
                <?php if ($headerCartCount > 0): ?>
                    <span class="user-header__badge" id="user-cart-badge"><?= $headerCartCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Bell / Notification Icon -->
            <button type="button" class="user-header__icon-btn" id="user-notif-btn" title="Notifikasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
                <span class="user-header__badge" id="user-notif-badge">3</span>
            </button>

            <!-- Profile Direct Link (Dropdown Removed) -->
            <a href="<?= BASE_URL ?>akun.php" class="user-header__profile-link" id="user-profile-btn" title="Akun Saya">
                <div class="user-header__avatar" id="user-header-avatar">
                    <?php if (!empty($headerAvatar) && file_exists(BASE_PATH . $headerAvatar)): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($headerAvatar) ?>" alt="Foto Profil" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    <?php else: ?>
                        <?= $headerInitials ?>
                    <?php endif; ?>
                </div>
            </a>

        </div>
    </div>
</header>

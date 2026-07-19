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
$headerLoggedIn  = isset($_SESSION['user_id']);
$headerUserName  = $headerLoggedIn ? htmlspecialchars($_SESSION['name']) : 'Tamu';
$headerInitials  = $headerLoggedIn
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

            <!-- Profile Avatar + Hover Dropdown -->
            <div class="user-header__profile" id="user-profile-wrapper">
                <div class="user-header__avatar" id="user-header-avatar" title="Profil">
                    <?= $headerInitials ?>
                </div>

                <!-- CSS Hover Dropdown -->
                <div class="user-dropdown" id="user-profile-dropdown">
                    <div class="user-dropdown__header">
                        <div class="user-dropdown__avatar"><?= $headerInitials ?></div>
                        <div class="user-dropdown__info">
                            <h4 class="user-dropdown__name"><?= $headerUserName ?></h4>
                            <p class="user-dropdown__role">Pelanggan Setia</p>
                        </div>
                    </div>
                    <div class="user-dropdown__divider"></div>
                    <ul class="user-dropdown__menu">
                        <!-- Mobile-only Nav Links -->
                        <li class="user-dropdown__mobile-only">
                            <a href="index.php?page=beranda" class="user-dropdown__item" id="udm-m-beranda">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li class="user-dropdown__mobile-only">
                            <a href="index.php?page=panduan" class="user-dropdown__item" id="udm-m-panduan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                                </svg>
                                <span>Panduan Belanja</span>
                            </a>
                        </li>
                        <li class="user-dropdown__mobile-only">
                            <a href="index.php?page=promo" class="user-dropdown__item" id="udm-m-promo">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                                </svg>
                                <span>Promo Bulanan</span>
                            </a>
                        </li>
                        <li class="user-dropdown__mobile-only">
                            <a href="index.php?page=tentang" class="user-dropdown__item" id="udm-m-tentang">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <span>Tentang Kami</span>
                            </a>
                        </li>
                        <li class="user-dropdown__mobile-only">
                            <a href="index.php?page=kontak" class="user-dropdown__item" id="udm-m-kontak">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                                <span>Kontak</span>
                            </a>
                        </li>
                        <li class="user-dropdown__mobile-only">
                            <div class="user-dropdown__divider"></div>
                        </li>
                        <!-- End Mobile-only Links -->
                        
                        <li>
                            <a href="index.php?page=riwayat" class="user-dropdown__item" id="udm-pesanan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                <span>Pesanan Saya</span>
                            </a>
                        </li>
                        <li>
                            <button type="button" class="user-dropdown__item" id="udm-notifikasi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                                </svg>
                                <span>Notifikasi</span>
                                <span class="user-dropdown__badge-inline">3</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="user-dropdown__item" id="udm-pengaturan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                                </svg>
                                <span>Pengaturan</span>
                            </button>
                        </li>
                        <li>
                            <div class="user-dropdown__divider"></div>
                        </li>
                        <li>
                            <?php if ($headerLoggedIn): ?>
                            <a href="<?= BASE_URL ?>logout.php" class="user-dropdown__item user-dropdown__item--logout" id="udm-keluar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Keluar</span>
                            </a>
                            <?php else: ?>
                            <a href="<?= BASE_URL ?>login.php" class="user-dropdown__item" id="udm-masuk">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="user-dropdown__icon">
                                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                                    <polyline points="10 17 15 12 10 7"/>
                                    <line x1="15" y1="12" x2="3" y2="12"/>
                                </svg>
                                <span>Masuk / Login</span>
                            </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</header>

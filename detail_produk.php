<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Product Detail Page
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-07
 * Description : Displays a full product detail view 
 *               - Two-column CSS Grid layout: image gallery left,
 *                 product info right.
 *               - Responsive: stacks to single column on mobile
 *               - Add-to-cart form hooks into cart_action.php
 *               - Secure PDO prepared statement for product fetch.
 * ============================================================
 */

session_start();

// ── Database & Helpers ───────────────────────────────────────
require_once __DIR__ . '/db_connect.php';

// ── Guard: require a valid product ID in the URL ─────────────
if (!isset($_GET['id']) || !ctype_digit((string) $_GET['id'])) {
    header('Location: index.php');
    exit;
}

$productId = (int) $_GET['id'];

// ── Fetch product via PDO prepared statement ─────────────────
$stmt = $pdo->prepare(
    'SELECT id, name, category, unit_desc, price, image_url, badge_label
       FROM products
      WHERE id = :id
      LIMIT 1'
);
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

// ── Resolve cart count for header badge ──────────────────────
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount = array_sum($_SESSION['cart']);

// ── Active page token for nav highlight ──────────────────────
$page = 'beranda';

// ── Rupiah formatter (safe duplicate-check) ──────────────────
if (!function_exists('formatRupiah')) {
    function formatRupiah(float $price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
}

// ── Cart flash message passthrough ──────────────────────────
$cartMessage = null;
if (isset($_SESSION['cart_message'])) {
    $cartMessage = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($product): ?>
    <title><?= htmlspecialchars($product['name']) ?> — Warung Tiga Saudara</title>
    <meta name="description" content="Beli <?= htmlspecialchars($product['name']) ?> dengan harga terbaik di Warung Tiga Saudara. Pengiriman instan ke seluruh wilayah.">
    <?php else: ?>
    <title>Produk Tidak Ditemukan — Warung Tiga Saudara</title>
    <meta name="description" content="Produk yang Anda cari tidak ditemukan di Warung Tiga Saudara.">
    <?php endif; ?>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- ═══════════════════════════════════════════════════════
         DETAIL PRODUK — Embedded Styles
         ═══════════════════════════════════════════════════════ -->
    <style>
        /* ── Design Tokens ─────────────────────────────────── */
        :root {
            --dp-accent:       #ee4d2d;
            --dp-accent-light: #fff1ee;
            --dp-orange:       #f57c00;
            --dp-orange-hover: #e65100;
            --dp-blue:         #1a6fb8;
            --dp-text-dark:    #1a1a2e;
            --dp-text-mid:     #4a4a6a;
            --dp-text-light:   #8a8aaa;
            --dp-border:       #e8e8f0;
            --dp-bg:           #f5f5fa;
            --dp-white:        #ffffff;
            --dp-radius-lg:    16px;
            --dp-radius-md:    10px;
            --dp-radius-sm:    6px;
            --dp-shadow-card:  0 4px 24px rgba(26,26,46,.08);
            --dp-transition:   all .22s cubic-bezier(.4,0,.2,1);
        }

        /* ── Page Wrapper ──────────────────────────────────── */
        .dp-page {
            background: var(--dp-bg);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        /* ── Breadcrumb ────────────────────────────────────── */
        .dp-breadcrumb {
            max-width: 1180px;
            margin: 0 auto;
            padding: 18px 24px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            color: var(--dp-text-light);
            flex-wrap: wrap;
        }
        .dp-breadcrumb a {
            color: var(--dp-text-light);
            text-decoration: none;
            transition: color .18s;
        }
        .dp-breadcrumb a:hover { color: var(--dp-accent); }
        .dp-breadcrumb__sep svg {
            width: 14px; height: 14px;
            stroke: var(--dp-text-light);
            display: block;
        }
        .dp-breadcrumb__current {
            color: var(--dp-text-dark);
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 260px;
        }

        /* ── Main Two-Column Grid ──────────────────────────── */
        .product-detail-container {
            max-width: 1180px;
            margin: 16px auto 56px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 460px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* ═══════════════════════════════════════════════════
           LEFT COLUMN — Image Gallery
           ═══════════════════════════════════════════════════ */
        .dp-gallery {
            background: var(--dp-white);
            border-radius: var(--dp-radius-lg);
            box-shadow: var(--dp-shadow-card);
            padding: 28px;
            position: sticky;
            top: 88px;
        }

        /* Main image frame */
        .dp-main-image-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: var(--dp-radius-md);
            overflow: hidden;
            background: linear-gradient(145deg, #f8f8fc, #f0f0f8);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dp-main-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform .4s cubic-bezier(.4,0,.2,1);
        }
        .dp-main-image-wrap:hover img { transform: scale(1.06); }

        /* Badge overlay on image */
        .dp-img-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background: var(--dp-accent);
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            z-index: 2;
            box-shadow: 0 2px 10px rgba(238,77,45,.4);
        }

        /* Thumbnail strip */
        .dp-thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            justify-content: center;
        }
        .dp-thumb {
            width: 72px;
            height: 72px;
            border-radius: var(--dp-radius-sm);
            overflow: hidden;
            border: 2px solid var(--dp-border);
            cursor: pointer;
            transition: var(--dp-transition);
            background: #f8f8fc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .dp-thumb:hover,
        .dp-thumb.dp-thumb--active {
            border-color: var(--dp-accent);
            box-shadow: 0 0 0 3px rgba(238,77,45,.18);
        }
        .dp-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .dp-thumb-placeholder {
            background: linear-gradient(135deg, #f0f0f8, #e6e6f2);
        }
        .dp-thumb-placeholder svg {
            width: 26px; height: 26px;
            stroke: #c8c8e0;
        }

        /* ═══════════════════════════════════════════════════
           RIGHT COLUMN — Product Info
           ═══════════════════════════════════════════════════ */
        .dp-info {
            background: var(--dp-white);
            border-radius: var(--dp-radius-lg);
            box-shadow: var(--dp-shadow-card);
            padding: 32px 36px;
            display: flex;
            flex-direction: column;
        }

        /* Title */
        .product-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--dp-text-dark);
            line-height: 1.3;
            margin: 0 0 18px;
            letter-spacing: -.02em;
        }

        /* Stats row */
        .dp-stats-row {
            display: flex;
            flex-direction: column;
            padding: 14px 18px;
            background: #fafafa;
            border: 1px solid var(--dp-border);
            border-radius: var(--dp-radius-md);
            margin-bottom: 22px;
        }
        .dp-stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .875rem;
            color: var(--dp-text-mid);
            padding: 8px 0;
        }
        .dp-stat-item:not(:last-child) {
            border-bottom: 1px solid var(--dp-border);
        }
        .dp-stat-item svg {
            width: 20px; height: 20px;
            flex-shrink: 0;
            stroke: var(--dp-text-light);
        }
        .dp-stat-label { color: var(--dp-text-light); }
        .badge-category {
            color: var(--dp-accent);
            font-weight: 600;
            text-decoration: none;
            transition: opacity .18s;
        }
        .badge-category:hover { opacity: .75; }
        .dp-instan-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: var(--dp-text-dark);
        }

        /* Price */
        .product-price {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dp-accent);
            letter-spacing: -.03em;
            line-height: 1;
            margin-bottom: 6px;
        }
        .dp-unit-desc {
            font-size: .82rem;
            color: var(--dp-text-light);
            margin: 0 0 22px;
        }

        /* Divider */
        .dp-divider {
            border: none;
            border-top: 1.5px solid var(--dp-border);
            margin: 0 0 20px;
        }

        /* Description */
        .desc-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dp-text-dark);
            margin: 0 0 10px;
        }
        .dp-desc-body {
            font-size: .9rem;
            color: var(--dp-text-mid);
            line-height: 1.8;
            margin: 0 0 8px;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .dp-read-more {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--dp-blue);
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 26px;
            cursor: pointer;
            transition: opacity .18s;
            background: none;
            border: none;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        .dp-read-more:hover { opacity: .7; }
        .dp-read-more svg {
            width: 14px; height: 14px;
            stroke: var(--dp-blue);
        }

        /* ── Add to Cart Form ─────────────────────────────── */
        .dp-cart-form {
            background: var(--dp-accent-light);
            border: 1.5px solid rgba(238,77,45,.15);
            border-radius: var(--dp-radius-md);
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: auto;
        }
        .dp-qty-row {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .dp-qty-label {
            font-size: .875rem;
            font-weight: 600;
            color: var(--dp-text-dark);
            min-width: 58px;
        }
        .dp-qty-control {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--dp-border);
            border-radius: 8px;
            overflow: hidden;
            background: var(--dp-white);
        }
        .dp-qty-btn {
            width: 38px;
            height: 38px;
            background: #f5f5fa;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dp-text-dark);
            transition: background .18s, color .18s;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            line-height: 1;
        }
        .dp-qty-btn:hover {
            background: var(--dp-accent);
            color: #fff;
        }
        .dp-qty-input {
            width: 56px;
            height: 38px;
            border: none;
            border-left: 1.5px solid var(--dp-border);
            border-right: 1.5px solid var(--dp-border);
            text-align: center;
            font-size: .95rem;
            font-weight: 700;
            color: var(--dp-text-dark);
            font-family: 'Inter', sans-serif;
            outline: none;
            -moz-appearance: textfield;
        }
        .dp-qty-input::-webkit-inner-spin-button,
        .dp-qty-input::-webkit-outer-spin-button { -webkit-appearance: none; }

        /* CTA button */
        .dp-btn-cart {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 15px 24px;
            background: linear-gradient(135deg, var(--dp-orange) 0%, #ff6f00 100%);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: .03em;
            transition: var(--dp-transition);
            box-shadow: 0 4px 18px rgba(245,124,0,.35);
        }
        .dp-btn-cart:hover {
            background: linear-gradient(135deg, var(--dp-orange-hover) 0%, #bf360c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,124,0,.5);
        }
        .dp-btn-cart:active { transform: translateY(0); }
        .dp-btn-cart:disabled {
            background: linear-gradient(135deg, #4ade80, #16a34a);
            cursor: default;
            transform: none;
        }
        .dp-btn-cart svg {
            width: 20px; height: 20px;
            flex-shrink: 0;
        }

        /* Secondary actions */
        .dp-secondary-actions {
            display: flex;
            gap: 10px;
        }
        .dp-btn-secondary {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px;
            background: var(--dp-white);
            border: 1.5px solid var(--dp-border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: .83rem;
            font-weight: 600;
            color: var(--dp-text-mid);
            cursor: pointer;
            transition: var(--dp-transition);
        }
        .dp-btn-secondary:hover {
            border-color: var(--dp-accent);
            color: var(--dp-accent);
            background: var(--dp-accent-light);
        }
        .dp-btn-secondary svg {
            width: 16px; height: 16px;
        }

        /* ── Not Found State ──────────────────────────────── */
        .dp-not-found {
            max-width: 480px;
            margin: 80px auto;
            text-align: center;
            background: var(--dp-white);
            border-radius: var(--dp-radius-lg);
            box-shadow: var(--dp-shadow-card);
            padding: 56px 40px;
        }
        .dp-not-found__icon {
            width: 80px;
            height: 80px;
            background: var(--dp-accent-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .dp-not-found__icon svg {
            width: 40px; height: 40px;
            stroke: var(--dp-accent);
        }
        .dp-not-found h2 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--dp-text-dark);
            margin: 0 0 10px;
        }
        .dp-not-found p {
            font-size: .9rem;
            color: var(--dp-text-light);
            line-height: 1.65;
            margin: 0 0 28px;
        }
        .dp-not-found a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: var(--dp-accent);
            color: #fff;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            transition: var(--dp-transition);
        }
        .dp-not-found a:hover {
            background: #c73a1e;
            transform: translateY(-1px);
        }

        /* ── Toast fallback ───────────────────────────────── */
        .dp-toast {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background: #1a1a2e;
            color: #fff;
            padding: 14px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .9rem;
            font-weight: 500;
            z-index: 9999;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
            animation: dp-toast-in .35s cubic-bezier(.4,0,.2,1) both;
        }
        .dp-toast svg { width: 18px; height: 18px; }
        @keyframes dp-toast-in {
            from { transform: translateX(-50%) translateY(60px); opacity: 0; }
            to   { transform: translateX(-50%) translateY(0);   opacity: 1; }
        }

        /* ═══════════════════════════════════════════════════
           RESPONSIVE — Mobile <= 768px
           ═══════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .product-detail-container {
                grid-template-columns: 1fr;   /* stack: image on top, info below */
                padding: 0 12px;
                margin: 12px auto 40px;
                gap: 16px;
            }
            .dp-gallery {
                padding: 16px;
                position: static;  /* no sticky on mobile */
            }
            .dp-info {
                padding: 20px 16px;
            }
            .product-title  { font-size: 1.2rem; }
            .product-price  { font-size: 1.65rem; }
            .dp-thumbnails  { gap: 8px; }
            .dp-thumb       { width: 60px; height: 60px; }
            .dp-breadcrumb  { padding: 12px 12px 0; font-size: .78rem; }
            .dp-cart-form   { padding: 16px; }
        }

        @media (max-width: 480px) {
            .dp-secondary-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="user-layout dp-page" id="user-layout">

    <?php include __DIR__ . '/header_user.php'; ?>

    <main class="user-main" id="dp-main-content">

        <?php if (!$product): ?>
        <!-- ══════════════════════════════════════════════════
             PRODUCT NOT FOUND
             ══════════════════════════════════════════════════ -->
        <div class="dp-not-found" id="dp-not-found-state" role="alert">
            <div class="dp-not-found__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    <line x1="11" y1="8"  x2="11" y2="14"/>
                    <line x1="11" y1="16" x2="11.01" y2="16"/>
                </svg>
            </div>
            <h2>Produk Tidak Ditemukan</h2>
            <p>Produk yang Anda cari tidak tersedia atau sudah tidak dijual.<br>Silakan kembali dan temukan produk lainnya.</p>
            <a href="index.php" id="dp-back-home-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <?php else:
            /* ── Resolve display values ──────────────────────── */
            $imageSrc       = getProductImage($product['name'], $product['image_url']);
            $hasImage       = file_exists(__DIR__ . '/' . $imageSrc);
            $displayImg     = $hasImage ? htmlspecialchars($imageSrc) : 'images/logo.png';
            $productName    = htmlspecialchars($product['name']);
            $category       = htmlspecialchars($product['category']);
            $unitDesc       = htmlspecialchars($product['unit_desc']);
            $badgeLabel     = $product['badge_label'] ? htmlspecialchars($product['badge_label']) : null;
            $formattedPrice = formatRupiah((float) $product['price']);

            /* ── Map category to page slug ────────────────────── */
            $categoryPage = match (strtolower(trim($product['category']))) {
                'sembako'                => 'sembako',
                'rempah-rempah'          => 'rempah',
                'camilan'                => 'camilan',
                'perawatan & kecantikan' => 'perawatan',
                'kesehatan'              => 'kesehatan',
                'minuman'                => 'minuman',
                default                  => 'beranda',
            };
        ?>

        <!-- ══════════════════════════════════════════════════
             BREADCRUMB NAVIGATION
             ══════════════════════════════════════════════════ -->
        <nav class="dp-breadcrumb" id="dp-breadcrumb" aria-label="Navigasi halaman">
            <a href="index.php" id="bc-home">Beranda</a>
            <span class="dp-breadcrumb__sep" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </span>
            <a href="index.php?page=<?= $categoryPage ?>" id="bc-category"><?= $category ?></a>
            <span class="dp-breadcrumb__sep" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </span>
            <span class="dp-breadcrumb__current" title="<?= $productName ?>"><?= $productName ?></span>
        </nav>

        <!-- ══════════════════════════════════════════════════
             MAIN TWO-COLUMN LAYOUT
             ══════════════════════════════════════════════════ -->
        <div class="product-detail-container" id="product-detail-container">

            <!-- ╔══════════════════════════════╗
                 ║  LEFT COLUMN — Image Gallery ║
                 ╚══════════════════════════════╝ -->
            <section class="dp-gallery" id="dp-gallery" aria-label="Galeri gambar produk">

                <!-- Main Image -->
                <div class="dp-main-image-wrap" id="dp-main-img-wrap">
                    <?php if ($badgeLabel): ?>
                    <span class="dp-img-badge" id="dp-img-badge" aria-label="Label: <?= $badgeLabel ?>">
                        <?= $badgeLabel ?>
                    </span>
                    <?php endif; ?>
                    <img
                        src="<?= $displayImg ?>"
                        alt="Foto produk: <?= $productName ?>"
                        id="dp-main-image"
                        loading="eager"
                        onerror="this.src='images/logo.png'"
                    >
                </div>

                <!-- Thumbnail Strip (1 real + 3 decorative placeholders) -->
                <div class="dp-thumbnails" id="dp-thumbnails" role="list" aria-label="Tampilan lain produk">

                    <!-- Thumb 1 — active (mirrors main image) -->
                    <div class="dp-thumb dp-thumb--active" id="dp-thumb-1" role="listitem"
                         tabindex="0" onclick="dpSetMain('<?= $displayImg ?>', this)"
                         aria-label="Gambar produk 1" aria-current="true">
                        <img src="<?= $displayImg ?>"
                             alt="<?= $productName ?>"
                             onerror="this.parentElement.classList.add('dp-thumb-placeholder'); this.remove()">
                    </div>

                    <!-- Thumb 2 — placeholder -->
                    <div class="dp-thumb dp-thumb-placeholder" id="dp-thumb-2" role="listitem"
                         tabindex="0" onclick="dpSetMain(null, this)" aria-label="Gambar produk 2">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>

                    <!-- Thumb 3 — placeholder -->
                    <div class="dp-thumb dp-thumb-placeholder" id="dp-thumb-3" role="listitem"
                         tabindex="0" onclick="dpSetMain(null, this)" aria-label="Gambar produk 3">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>

                    <!-- Thumb 4 — placeholder -->
                    <div class="dp-thumb dp-thumb-placeholder" id="dp-thumb-4" role="listitem"
                         tabindex="0" onclick="dpSetMain(null, this)" aria-label="Gambar produk 4">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>

                </div><!-- /dp-thumbnails -->
            </section><!-- /dp-gallery -->

            <!-- ╔══════════════════════════════╗
                 ║  RIGHT COLUMN — Product Info ║
                 ╚══════════════════════════════╝ -->
            <section class="dp-info" id="dp-info" aria-label="Informasi produk">

                <!-- H1 — Product Name -->
                <h1 class="product-title" id="dp-product-title"><?= $productName ?></h1>

                <!-- Stats / Badges Row -->
                <div class="dp-stats-row" id="dp-stats-row" role="list"
                     aria-label="Informasi kategori dan pengiriman">

                    <!-- Category / Brand -->
                    <div class="dp-stat-item" id="dp-stat-brand" role="listitem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                            <line x1="12" y1="12" x2="12" y2="16"/>
                            <line x1="10" y1="14" x2="14" y2="14"/>
                        </svg>
                        <span class="dp-stat-label">Kategori :</span>
                        <a href="index.php?page=<?= $categoryPage ?>"
                           class="badge-category"
                           id="dp-badge-category"><?= $category ?> ›</a>
                        <?php if ($badgeLabel): ?>
                        <span style="margin-left:8px; background:var(--dp-accent); color:#fff;
                                     font-size:.68rem; font-weight:700; padding:3px 9px;
                                     border-radius:12px; letter-spacing:.05em; white-space:nowrap;">
                            <?= $badgeLabel ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Instant delivery badge -->
                    <div class="dp-stat-item" id="dp-stat-delivery" role="listitem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        <span class="dp-instan-badge" id="dp-instan-badge">Pengiriman Instan</span>
                    </div>
                </div>

                <!-- Price -->
                <p class="product-price" id="dp-product-price"
                   aria-label="Harga <?= $formattedPrice ?>">
                    <?= $formattedPrice ?>
                </p>
                <p class="dp-unit-desc" id="dp-unit-desc"><?= $unitDesc ?></p>

                <hr class="dp-divider" aria-hidden="true">

                <!-- Description -->
                <h3 class="desc-title" id="dp-desc-title">Deskripsi</h3>
                <div class="dp-desc-body" id="dp-desc-body">
                    <?php
                    /*
                     * NOTE: The current `products` table does not have a `deskripsi`
                     * column. When that column is added, replace the block below with:
                     *   echo nl2br(htmlspecialchars($product['deskripsi']));
                     *
                     * For now we render a rich auto-generated description from the
                     * available fields so the UI is never empty.
                     */
                    echo '<ul style="margin:0 0 0 2px; padding-left:18px; line-height:1.95;">'
                       . '<li>' . htmlspecialchars($product['name'])      . '</li>'
                       . '<li>Kategori: '  . htmlspecialchars($product['category'])  . '</li>'
                       . '<li>Satuan: '    . htmlspecialchars($product['unit_desc']) . '</li>'
                       . '<li>Produk segar dan berkualitas langsung dari mitra terpercaya Warung Tiga Saudara.</li>'
                       . '<li>Stok selalu tersedia — pesan sekarang, terima hari ini.</li>'
                       . '</ul>';
                    ?>
                </div>
                <button type="button" class="dp-read-more" id="dp-read-more-btn"
                        onclick="dpToggleDesc(this)" aria-expanded="false">
                    Lihat Selengkapnya
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <!-- ══ Add-to-Cart Form ══════════════════════ -->
                <form action="cart_action.php" method="POST"
                      id="dp-cart-form" class="dp-cart-form"
                      aria-label="Tambah ke keranjang belanja">

                    <!-- Hidden fields required by cart_action.php -->
                    <input type="hidden" name="action"        value="add">
                    <input type="hidden" name="product_id"    value="<?= (int) $product['id'] ?>"
                           id="dp-hidden-product-id">
                    <!--
                        redirect_page is parsed by cart_action.php as:
                        index.php?{redirect_page}
                        We leave it intentionally pointing to beranda so the
                        session flash message appears on the home page.
                        The JS intercept below handles the stay-on-page flow.
                    -->
                    <input type="hidden" name="redirect_page" value="beranda">

                    <!-- Quantity selector -->
                    <div class="dp-qty-row" id="dp-qty-row">
                        <label class="dp-qty-label" for="dp-qty-input" id="dp-qty-label">Jumlah</label>
                        <div class="dp-qty-control" id="dp-qty-control">
                            <button type="button" class="dp-qty-btn" id="dp-qty-dec"
                                    onclick="dpQtyStep(-1)" aria-label="Kurangi jumlah">
                                &minus;
                            </button>
                            <input type="number"
                                   name="qty"
                                   id="dp-qty-input"
                                   class="dp-qty-input"
                                   value="1"
                                   min="1"
                                   max="99"
                                   aria-label="Jumlah produk yang akan dibeli">
                            <button type="button" class="dp-qty-btn" id="dp-qty-inc"
                                    onclick="dpQtyStep(1)" aria-label="Tambah jumlah">
                                &#43;
                            </button>
                        </div>
                    </div><!-- /dp-qty-row -->

                    <!-- Primary CTA -->
                    <button type="submit" class="dp-btn-cart" id="dp-btn-add-cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                        Masukkan ke Keranjang
                    </button>

                    <!-- Secondary actions: Wishlist + Share -->
                    <div class="dp-secondary-actions" id="dp-secondary-actions">
                        <button type="button" class="dp-btn-secondary" id="dp-btn-wishlist"
                                onclick="dpWishlist(this)" aria-label="Simpan ke wishlist">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06
                                         a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78
                                         1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                            Simpan
                        </button>
                        <button type="button" class="dp-btn-secondary" id="dp-btn-share"
                                onclick="dpShare('<?= addslashes($productName) ?>')"
                                aria-label="Bagikan produk ini">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="18" cy="5"  r="3"/>
                                <circle cx="6"  cy="12" r="3"/>
                                <circle cx="18" cy="19" r="3"/>
                                <line x1="8.59"  y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51"  x2="8.59"  y2="10.49"/>
                            </svg>
                            Bagikan
                        </button>
                    </div>

                </form><!-- /dp-cart-form -->

            </section><!-- /dp-info -->

        </div><!-- /product-detail-container -->

        <?php endif; // end product check ?>

    </main><!-- /user-main -->

    <!-- ══════════════════════════════════════════════════════
         FOOTER
         ══════════════════════════════════════════════════════ -->
    <footer class="user-footer" id="user-footer">
        <div class="user-footer__inner">
            <span>&copy; 2026 Warung Tiga Saudara. All rights reserved.</span>
            <div class="user-footer__links">
                <a href="index.php?page=kontak" class="user-footer__link" id="footer-kontak">Hubungi Kami</a>
                <a href="#" class="user-footer__link" id="footer-privasi">Kebijakan Privasi</a>
                <a href="#" class="user-footer__link" id="footer-syarat">Syarat Layanan</a>
            </div>
        </div>
    </footer>

</div><!-- /user-layout -->

<!-- ══════════════════════════════════════════════════════════
     TOAST — Cart / action feedback
     ══════════════════════════════════════════════════════════ -->
<?php if ($cartMessage): ?>
<div class="dp-toast" id="dp-cart-toast" role="status" aria-live="polite">
    <svg viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <?= htmlspecialchars($cartMessage) ?>
</div>
<script>
    (function () {
        var toast = document.getElementById('dp-cart-toast');
        if (!toast) return;
        setTimeout(function () {
            toast.style.transition = 'opacity .4s ease, transform .4s ease';
            toast.style.opacity    = '0';
            toast.style.transform  = 'translateX(-50%) translateY(50px)';
            setTimeout(function () { if (toast) toast.remove(); }, 420);
        }, 3500);
    }());
</script>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     PAGE SCRIPTS
     ══════════════════════════════════════════════════════════ -->
<script>
/* ─── Thumbnail switcher ────────────────────────────────────
   Click a thumbnail to swap the main image with a smooth fade.
 ─────────────────────────────────────────────────────────── */
function dpSetMain(src, thumbEl) {
    var mainImg = document.getElementById('dp-main-image');
    var thumbs  = document.querySelectorAll('.dp-thumb');

    thumbs.forEach(function (t) {
        t.classList.remove('dp-thumb--active');
        t.removeAttribute('aria-current');
    });
    thumbEl.classList.add('dp-thumb--active');
    thumbEl.setAttribute('aria-current', 'true');

    if (src && mainImg) {
        mainImg.style.transition = 'opacity .2s, transform .2s';
        mainImg.style.opacity    = '0';
        mainImg.style.transform  = 'scale(.96)';
        setTimeout(function () {
            mainImg.src             = src;
            mainImg.style.opacity   = '1';
            mainImg.style.transform = 'scale(1)';
        }, 160);
    }
}

/* ─── Quantity stepper ──────────────────────────────────────
   +/- buttons adjust the numeric input; enforces min 1 max 99.
 ─────────────────────────────────────────────────────────── */
function dpQtyStep(delta) {
    var inp = document.getElementById('dp-qty-input');
    if (!inp) return;
    var val = parseInt(inp.value, 10);
    if (isNaN(val)) val = 1;
    val = Math.max(1, Math.min(99, val + delta));
    inp.value = val;
}

(function () {
    var inp = document.getElementById('dp-qty-input');
    if (!inp) return;
    inp.addEventListener('change', function () {
        var v = parseInt(this.value, 10);
        this.value = isNaN(v) || v < 1 ? 1 : v > 99 ? 99 : v;
    });
}());

/* ─── Wishlist toggle (UI-only) ──────────────────────────── */
function dpWishlist(btn) {
    if (!btn) return;
    var svg   = btn.querySelector('svg');
    var saved = btn.dataset.saved === '1';

    if (saved) {
        svg.setAttribute('fill',   'none');
        svg.setAttribute('stroke', 'currentColor');
        btn.dataset.saved = '0';
    } else {
        svg.setAttribute('fill',   '#ee4d2d');
        svg.setAttribute('stroke', '#ee4d2d');
        btn.dataset.saved = '1';
        dpShowToast('Produk disimpan ke wishlist \u2764\ufe0f');
    }
}

/* ─── Share via Web Share API / clipboard fallback ───────── */
function dpShare(name) {
    var url = window.location.href;
    if (navigator.share) {
        navigator.share({ title: name, url: url }).catch(function () {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
            dpShowToast('Link produk disalin ke clipboard!');
        }).catch(function () {});
    }
}

/* ─── Dynamic toast helper ───────────────────────────────── */
function dpShowToast(msg) {
    var old = document.getElementById('dp-dynamic-toast');
    if (old) old.remove();

    var t   = document.createElement('div');
    t.id    = 'dp-dynamic-toast';
    t.className = 'dp-toast';
    t.setAttribute('role', 'status');
    t.setAttribute('aria-live', 'polite');
    t.innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" '
        + 'stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px" aria-hidden="true">'
        + '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>'
        + '<polyline points="22 4 12 14.01 9 11.01"/></svg>' + msg;
    document.body.appendChild(t);

    setTimeout(function () {
        t.style.transition = 'opacity .4s ease, transform .4s ease';
        t.style.opacity    = '0';
        t.style.transform  = 'translateX(-50%) translateY(50px)';
        setTimeout(function () { if (t) t.remove(); }, 420);
    }, 3200);
}

/* ─── Description expand / collapse ─────────────────────── */
function dpToggleDesc(btn) {
    var body     = document.getElementById('dp-desc-body');
    var expanded = btn.getAttribute('aria-expanded') === 'true';

    if (expanded) {
        body.style.webkitLineClamp = '5';
        body.style.overflow        = 'hidden';
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML =
            'Lihat Selengkapnya '
            + '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" '
            + 'style="width:14px;height:14px;stroke:var(--dp-blue)" aria-hidden="true">'
            + '<polyline points="6 9 12 15 18 9"/></svg>';
    } else {
        body.style.webkitLineClamp = 'unset';
        body.style.overflow        = 'visible';
        btn.setAttribute('aria-expanded', 'true');
        btn.innerHTML =
            'Sembunyikan '
            + '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" '
            + 'style="width:14px;height:14px;stroke:var(--dp-blue)" aria-hidden="true">'
            + '<polyline points="18 15 12 9 6 15"/></svg>';
    }
}

/* ─── Cart form submit via fetch (stay on page) ──────────── */
(function () {
    var form = document.getElementById('dp-cart-form');
    var btn  = document.getElementById('dp-btn-add-cart');
    if (!form || !btn) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // stop normal navigation

        /* Animate button to "loading" state */
        btn.disabled         = true;
        var originalHTML     = btn.innerHTML;
        btn.style.background = 'linear-gradient(135deg, #f5a623 0%, #f57c00 100%)';
        btn.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" '
            + 'stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px" aria-hidden="true">'
            + '<circle cx="12" cy="12" r="10" stroke-dasharray="40" stroke-dashoffset="0">'
            + '<animateTransform attributeName="transform" type="rotate" dur=".8s" repeatCount="indefinite" from="0 12 12" to="360 12 12"/>'
            + '</circle></svg>'
            + 'Menambahkan\u2026';

        var data = new FormData(form);

        fetch('cart_action.php', {
            method:      'POST',
            body:        data,
            redirect:    'manual'   /* do NOT follow the 302 redirect */
        })
        .then(function () {
            /* Success: show toast and reset button to "Added" state */
            btn.style.background =
                'linear-gradient(135deg, #4ade80 0%, #16a34a 100%)';
            btn.innerHTML =
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" '
                + 'stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px" aria-hidden="true">'
                + '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>'
                + '<polyline points="22 4 12 14.01 9 11.01"/></svg>'
                + 'Ditambahkan ke Keranjang!';

            dpShowToast('Produk berhasil ditambahkan ke keranjang \uD83D\uDED2');

            /* Reset button after 2.5 s */
            setTimeout(function () {
                btn.disabled         = false;
                btn.style.background = '';
                btn.innerHTML        = originalHTML;
            }, 2500);
        })
        .catch(function () {
            /* Network error — fall back to normal form submit */
            btn.disabled = false;
            btn.style.background = '';
            btn.innerHTML        = originalHTML;
            form.submit();
        });
    });
}());
</script>

</body>
</html>

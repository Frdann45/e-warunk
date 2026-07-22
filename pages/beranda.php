<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Beranda (Homepage) View
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-24
 * Updated     : 2026-07-03
 * Description : User portal homepage with:
 *               - Full-width hero banner
 *               - KATEGORI section (Shopee-style horizontal grid)
 *               - Featured products grid (full-width)
 * ============================================================
 */
require_once dirname(__DIR__) . '/config/db_connect.php';

try {
    // Fetch the featured products including new categories
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name IN ('Beras Premium', 'Rempah Pilihan', 'Camilan Kering', 'Minyak Goreng', 'Vitamin C Serum 20%', 'Minyak Kayu Putih 60ml', 'Susu UHT Ultra Milk 250ml') ORDER BY FIELD(name, 'Beras Premium', 'Rempah Pilihan', 'Camilan Kering', 'Minyak Goreng', 'Vitamin C Serum 20%', 'Minyak Kayu Putih 60ml', 'Susu UHT Ultra Milk 250ml')");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredProducts = [];
    error_log('Featured products fetch error: ' . $e->getMessage());
}
?>

<!-- ═══════════════════════════════════════════════════════════
     HERO BANNER — Full Width
     ═══════════════════════════════════════════════════════════ -->
<section class="user-hero scroll-reveal" id="hero-banner">
    <div class="user-hero__slider">
        <img src="<?= BASE_URL ?>assets/images/warung.webp" alt="Warung Tiga Saudara Storefront" class="user-hero__image">
        <div class="user-hero__overlay"></div>
        <div class="user-hero__content">
            <h1 class="user-hero__title">Selamat Datang di Warung Tiga Saudara</h1>
            <p class="user-hero__desc">
                Pusat belanja serba ada untuk kebutuhan sehari-hari yang segar, andal, dan 
                berkualitas tinggi.
            </p>
            <a href="index.php?page=sembako" class="user-hero__cta">Mulai Belanja</a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     KATEGORI — Shopee-Style Horizontal Grid
     ═══════════════════════════════════════════════════════════ -->
<section class="category-section scroll-reveal" id="section-kategori">
    <div class="category-section__header">
        <h2 class="category-section__title">KATEGORI</h2>
    </div>
    <div class="category-grid">

        <!-- Sembako -->
        <a href="index.php?page=sembako" class="category-tile" id="cat-tile-sembako">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--sembako">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <span class="category-tile__label">Sembako</span>
        </a>

        <!-- Rempah-rempah -->
        <a href="index.php?page=rempah" class="category-tile" id="cat-tile-rempah">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--rempah">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a10 10 0 00-6.88 17.23l.9-.67A8.5 8.5 0 0112 3.5 8.5 8.5 0 0118 18.56l.9.67A10 10 0 0012 2z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <span class="category-tile__label">Rempah-rempah</span>
        </a>

        <!-- Camilan -->
        <a href="index.php?page=camilan" class="category-tile" id="cat-tile-camilan">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--camilan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="8" cy="9" r="1" fill="currentColor"/>
                    <circle cx="15" cy="10" r="1.5" fill="currentColor"/>
                    <circle cx="11" cy="14" r="1" fill="currentColor"/>
                    <circle cx="8" cy="15" r="1.5" fill="currentColor"/>
                    <circle cx="16" cy="15" r="1" fill="currentColor"/>
                </svg>
            </div>
            <span class="category-tile__label">Camilan</span>
        </a>

        <!-- Minuman -->
        <a href="index.php?page=minuman" class="category-tile" id="cat-tile-minuman">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--minuman">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 010 8h-1"/>
                    <path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>
                    <line x1="6" y1="1" x2="6" y2="4"/>
                    <line x1="10" y1="1" x2="10" y2="4"/>
                    <line x1="14" y1="1" x2="14" y2="4"/>
                </svg>
            </div>
            <span class="category-tile__label">Minuman</span>
        </a>

        <!-- Perawatan & Kecantikan -->
        <a href="index.php?page=perawatan" class="category-tile" id="cat-tile-perawatan">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--perawatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l1.09 3.26L16 6l-2.18 1.74L14.73 11 12 9.27 9.27 11l.91-3.26L8 6l2.91-.74z"/>
                    <path d="M5 17h14"/>
                    <path d="M7 21h10"/>
                    <path d="M9 13v4"/>
                    <path d="M15 13v4"/>
                </svg>
            </div>
            <span class="category-tile__label">Perawatan & Kecantikan</span>
        </a>

        <!-- Kesehatan -->
        <a href="index.php?page=kesehatan" class="category-tile" id="cat-tile-kesehatan">
            <div class="category-tile__icon-wrapper category-tile__icon-wrapper--kesehatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
            </div>
            <span class="category-tile__label">Kesehatan</span>
        </a>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     TOKO KAMI GALLERY
     ═══════════════════════════════════════════════════════════ -->
<section class="gallery user-gallery scroll-reveal" id="section-gallery">
    <div class="section-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="section-header__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            <h2 class="section-header__title">Toko Kami</h2>
        </div>
    </div>
    <div class="gallery__grid">
        <div class="gallery__item scroll-reveal">
            <img src="<?= BASE_URL ?>assets/images/1.webp" alt="Interior toko Warung Tiga Saudara">
        </div>
        <div class="gallery__item scroll-reveal">
            <img src="<?= BASE_URL ?>assets/images/2.webp" alt="Display produk segar">
        </div>
        <div class="gallery__item scroll-reveal">
            <img src="<?= BASE_URL ?>assets/images/3.webp" alt="Rak bumbu dan saus">
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     PRODUK UNGGULAN — Full Width Grid
     ═══════════════════════════════════════════════════════════ -->
<section class="products user-products scroll-reveal" id="section-featured-products">
    <div class="section-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="section-header__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </span>
            <h2 class="section-header__title">Produk Unggulan</h2>
        </div>
    </div>
    
    <div class="products__grid user-products__grid">
        <?php if (!empty($featuredProducts)): ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card scroll-reveal" id="product-<?= htmlspecialchars($product['id']) ?>">
                    <!-- Clickable image → detail page -->
                    <a href="detail_produk.php?id=<?= (int) $product['id'] ?>" class="product-card__link" style="text-decoration:none;color:inherit;display:block;">
                        <div class="product-card__image-wrapper">
                            <img 
                                src="<?= htmlspecialchars(getProductImage($product['name'], $product['image_url'] ?? '')) ?>" 
                                alt="<?= htmlspecialchars($product['name']) ?>" 
                                class="product-card__image"
                                loading="lazy"
                            >
                            <?php if (!empty($product['badge_label'])): ?>
                                <span class="product-card__badge <?= $product['badge_label'] === 'PALING LARIS' 
                                    ? 'product-card__badge--laris' 
                                    : 'product-card__badge--promo' ?>">
                                    <?= htmlspecialchars($product['badge_label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card__info">
                            <h3 class="product-card__name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-card__unit"><?= htmlspecialchars($product['unit_desc']) ?></p>
                        </div>
                    </a>
                    <div class="product-card__footer" style="padding:0 12px 12px;">
                        <span class="product-card__price"><?= formatRupiah((float) $product['price']) ?></span>
                        <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect_page" value="beranda">
                            <button type="submit" class="product-card__cart-btn" title="Tambah ke Keranjang" id="add-cart-<?= (int) $product['id'] ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/>
                                    <circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--color-text-light);">
                <p>Tidak ada produk unggulan.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

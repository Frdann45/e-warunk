<?php
/**
 * =======
 * Warung Tiga Saudara - Sembako view
 * Author ID: 11240044
 * =======
 */
require_once __DIR__ . '/../db_connect.php';

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($searchQuery !== '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Sembako' AND name LIKE ? ORDER BY id ASC");
        $stmt->execute(['%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Sembako' ORDER BY id ASC");
        $stmt->execute();
    }
    $sembakoProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $sembakoProducts = [];
    error_log('Sembako fetch error: ' . $e->getMessage());
}
?>

<!-- Hero Banner -->
<section class="hero fade-in" id="hero-banner">
    <img src="images/warung.webp" alt="Warung Tiga Saudara Storefront" class="hero__image">
    <div class="hero__overlay"></div>
    <div class="hero__content">
        <h1 class="hero__title">Selamat Datang di Warung Tiga Saudara</h1>
        <p class="hero__desc">
            Pusat belanja serba ada Anda untuk kebutuhan sehari-hari yang segar, andal, dan 
            berkualitas tinggi. Kami menghadirkan kehangatan toko lingkungan lokal ke dalam 
            pengalaman digital yang lancar.
        </p>
    </div>
</section>

<!-- Toko Kami Gallery -->
<section class="gallery" id="section-gallery">
    <div class="section-header">
        <span class="section-header__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </span>
        <h2 class="section-header__title">Toko Kami</h2>
    </div>
    <div class="gallery__grid">
        <div class="gallery__item fade-in">
            <img src="images/1.webp" alt="Interior toko Warung Tiga Saudara">
        </div>
        <div class="gallery__item fade-in">
            <img src="images/2.webp" alt="Display produk segar">
        </div>
        <div class="gallery__item fade-in">
            <img src="images/3.webp" alt="Rak bumbu dan saus">
        </div>
    </div>
</section>

<!-- Produk Unggulan -->
<section class="products" id="section-products">
    <div class="section-header" style="justify-content: space-between; width: 100%; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="section-header__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </span>
            <h2 class="section-header__title">Produk Unggulan</h2>
        </div>
        <div class="catalog-header__actions">
            <!-- Search bar input -->
            <div class="search-box-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="search-box-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <form action="index.php" method="GET" style="margin:0; display:flex;">
                    <input type="hidden" name="page" value="sembako">
                    <input type="text" name="search" class="input-search" placeholder="Cari produk..." value="<?= htmlspecialchars($searchQuery) ?>">
                </form>
            </div>
            
            <button class="btn-filter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                    <line x1="4" y1="21" x2="4" y2="14"/>
                    <line x1="4" y1="10" x2="4" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12" y2="3"/>
                    <line x1="20" y1="21" x2="20" y2="16"/>
                    <line x1="20" y1="12" x2="20" y2="3"/>
                    <line x1="1" y1="14" x2="7" y2="14"/>
                    <line x1="9" y1="8" x2="15" y2="8"/>
                    <line x1="17" y1="16" x2="23" y2="16"/>
                </svg>
                Filter
            </button>
            <button class="btn-sort">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                    <line x1="11" y1="5" x2="19" y2="5"/>
                    <line x1="11" y1="9" x2="19" y2="9"/>
                    <line x1="11" y1="13" x2="19" y2="13"/>
                    <line x1="11" y1="17" x2="19" y2="17"/>
                    <polyline points="7 15 4 18 1 15"/>
                    <line x1="4" y1="6" x2="4" y2="18"/>
                </svg>
                Urutkan
            </button>
        </div>
    </div>
    <div class="products__grid">
        <?php if (!empty($sembakoProducts)): ?>
            <?php foreach ($sembakoProducts as $product): ?>
                <div class="product-card fade-in" id="product-<?= htmlspecialchars($product['id']) ?>">
                    <!-- Product Image -->
                    <div class="product-card__image-wrapper">
                        <img 
                            src="<?= htmlspecialchars($product['image_url']) ?>" 
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

                    <!-- Product Info -->
                    <div class="product-card__info">
                        <h3 class="product-card__name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-card__unit"><?= htmlspecialchars($product['unit_desc']) ?></p>
                        <div class="product-card__footer">
                            <span class="product-card__price"><?= formatRupiah((float) $product['price']) ?></span>
                            <form action="cart_action.php" method="POST" style="margin:0;">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="redirect_page" value="sembako">
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
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--color-text-light);">
                <p>Tidak ada produk kategori Sembako.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

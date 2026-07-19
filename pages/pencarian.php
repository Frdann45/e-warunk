<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Hasil Pencarian Global
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Global search result page displaying matching
 *               products across all categories.
 * ============================================================
 */
require_once dirname(__DIR__) . '/config/db_connect.php';

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($searchQuery !== '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id ASC");
        $stmt->execute(['%' . $searchQuery . '%']);
        $searchResults = $stmt->fetchAll();
    } else {
        $searchResults = [];
    }
} catch (PDOException $e) {
    $searchResults = [];
    error_log('Search query error: ' . $e->getMessage());
}
?>

<!-- ═══════════════════════════════════════════════════════════
     SEARCH RESULTS CATALOG
     ═══════════════════════════════════════════════════════════ -->
<div class="catalog-header fade-in">
    <div>
        <h1 class="catalog-header__title">Hasil Pencarian</h1>
        <p class="catalog-header__desc">
            <?php if ($searchQuery !== ''): ?>
                Menampilkan hasil pencarian untuk: "<strong><?= htmlspecialchars($searchQuery) ?></strong>" (<?= count($searchResults) ?> produk ditemukan)
            <?php else: ?>
                Silakan masukkan kata kunci pencarian pada kolom di atas.
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="products__grid">
    <?php if (!empty($searchResults)): ?>
        <?php foreach ($searchResults as $product): ?>
            <div class="product-card fade-in" id="product-<?= htmlspecialchars($product['id']) ?>">
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

                    <!-- Product Info -->
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
                        <input type="hidden" name="redirect_page" value="pencarian&search=<?= urlencode($searchQuery) ?>">
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
        <div style="grid-column: 1 / -1; text-align: center; padding: 64px 20px; color: var(--color-text-light);">
            <p>Tidak ada produk yang cocok dengan kata kunci pencarian Anda.</p>
        </div>
    <?php endif; ?>
</div>

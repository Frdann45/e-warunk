<?php
/**
 * =======
 * Warung Tiga Saudara - Camilan view
 * Author ID: 11240044
 * =======
 */
require_once __DIR__ . '/../db_connect.php';

// Fetch snacks
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($searchQuery !== '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Camilan' AND name LIKE ? ORDER BY id ASC");
        $stmt->execute(['%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Camilan' ORDER BY id ASC");
        $stmt->execute();
    }
    $snacks = $stmt->fetchAll();
} catch (PDOException $e) {
    $snacks = [];
    error_log('Snacks fetch error: ' . $e->getMessage());
}

// Separate featured snacks and regular snacks
$featuredSnacks = [];
$regularSnacks = [];
foreach ($snacks as $snack) {
    if ($snack['name'] === 'Kerupuk Udang Renyah' || $snack['name'] === 'Keripik Pisang Manis') {
        $featuredSnacks[$snack['name']] = $snack;
    } else {
        $regularSnacks[] = $snack;
    }
}
?>

<div class="catalog-header fade-in">
    <div>
        <h1 class="catalog-header__title">Katalog Camilan</h1>
        <p class="catalog-header__desc">Temukan berbagai pilihan camilan lezat untuk menemani hari Anda.</p>
    </div>
    <div class="catalog-header__actions">
        <!-- Search bar input -->
        <div class="search-box-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="search-box-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <form action="index.php" method="GET" style="margin:0; display:flex;">
                <input type="hidden" name="page" value="camilan">
                <input type="text" name="search" class="input-search" placeholder="Cari camilan..." value="<?= htmlspecialchars($searchQuery) ?>">
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
        <div class="select-wrapper">
            <select class="btn-sort" style="appearance: none; padding-right: 28px;">
                <option>Terpopuler</option>
                <option>Harga Terendah</option>
                <option>Harga Tertinggi</option>
            </select>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-arrow">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>
</div>

<!-- Featured Snacks Grid (Hero Items) -->
<div class="featured-snacks fade-in">
    <!-- Card 1: Kerupuk Udang -->
    <?php if (isset($featuredSnacks['Kerupuk Udang Renyah'])): 
        $ks = $featuredSnacks['Kerupuk Udang Renyah']; ?>
        <div class="featured-card featured-card--udang" style="background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.8)), url('<?= htmlspecialchars(getProductImage($ks['name'])) ?>');">
            <span class="featured-card__badge">TERLARIS</span>
            <div class="featured-card__content">
                <h2 class="featured-card__title"><?= htmlspecialchars($ks['name']) ?></h2>
                <p class="featured-card__desc">Gurih, renyah, dan cocok untuk pendamping makan.</p>
                <div class="featured-card__footer">
                    <span class="featured-card__price"><?= formatRupiah((float) $ks['price']) ?></span>
                    <form action="cart_action.php" method="POST" style="margin:0;">
                        <input type="hidden" name="product_id" value="<?= (int) $ks['id'] ?>">
                        <input type="hidden" name="redirect_page" value="camilan">
                        <button type="submit" class="featured-card__btn">Tambah</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 2: Keripik Pisang -->
    <?php if (isset($featuredSnacks['Keripik Pisang Manis'])): 
        $kp = $featuredSnacks['Keripik Pisang Manis']; ?>
        <div class="featured-card featured-card--pisang" style="background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.8)), url('<?= htmlspecialchars(getProductImage($kp['name'])) ?>');">
            <div class="featured-card__content">
                <h2 class="featured-card__title"><?= htmlspecialchars($kp['name']) ?></h2>
                <p class="featured-card__desc">Irisan tipis, manis pas.</p>
                <div class="featured-card__footer">
                    <span class="featured-card__price"><?= formatRupiah((float) $kp['price']) ?></span>
                    <form action="cart_action.php" method="POST" style="margin:0;">
                        <input type="hidden" name="product_id" value="<?= (int) $kp['id'] ?>">
                        <input type="hidden" name="redirect_page" value="camilan">
                        <button type="submit" class="featured-card__btn">Tambah</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<h2 class="section-subtitle fade-in">Semua Camilan</h2>

<div class="products__grid">
    <?php if (!empty($regularSnacks)): ?>
        <?php foreach ($regularSnacks as $product): ?>
            <?php
            $desc = '';
            switch ($product['name']) {
                case 'Nastar Klasik Premium':
                    $desc = 'Kue kering isi selai nanas asli.';
                    break;
                case 'Kacang Atom Garuda':
                    $desc = 'Renyah, gurih, bumbu meresap.';
                    break;
                case 'Keripik Singkong Pedas':
                    $desc = 'Ekstra pedas, irisan tipis.';
                    break;
                case 'Stik Keju Edam':
                    $desc = 'Rasa keju asli, renyah tanpa pengawet.';
                    break;
            }
            ?>
            <div class="product-card fade-in" id="product-<?= htmlspecialchars($product['id']) ?>">
                <div class="product-card__image-wrapper">
                    <img 
                        src="<?= htmlspecialchars(getProductImage($product['name'])) ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>" 
                        class="product-card__image"
                        loading="lazy"
                    >
                    <?php if (!empty($product['badge_label'])): ?>
                        <span class="product-card__badge 
                            <?= ($product['badge_label'] === 'STOK BANYAK') 
                                ? 'product-card__badge--laris' 
                                : 'product-card__badge--promo' ?>">
                            <?= htmlspecialchars($product['badge_label']) ?>
                        </span>
                    <?php endif; ?>
                    <button class="btn-wishlist" title="Tambah ke Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                    </button>
                </div>

                <div class="product-card__info">
                    <h3 class="product-card__name"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="product-card__desc"><?= htmlspecialchars($desc) ?></p>
                    <div class="product-card__footer">
                        <div>
                            <span class="product-card__price"><?= formatRupiah((float) $product['price']) ?></span>
                        </div>
                        <form action="cart_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect_page" value="camilan">
                            <button type="submit" class="product-card__cart-btn product-card__cart-btn--round" title="Tambah ke Keranjang">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--color-text-light);">
            <p>Tidak ada produk kategori Camilan.</p>
        </div>
    <?php endif; ?>
</div>

<div class="load-more-container fade-in">
    <button class="btn-load-more">Muat Lebih Banyak</button>
</div>

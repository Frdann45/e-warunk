<?php
/**
 * =======
 * Warung Tiga Saudara - Promo Bulanan view
 * Author ID: 11240044
 * =======
 */
require_once dirname(__DIR__) . '/config/db_connect.php';

// Fetch promo items from DB
try {
    $promoProductNames = [
        'Beras Rojolele Premium 5kg',
        'Minyak Goreng SunCo 2L',
        'Telur Ayam Negeri 1kg',
        'Gula Pasir Gulaku 1kg',
        'Teh Celup Premium 25s',
        'Tepung Terigu Segitiga Biru 1kg',
        'Paket Sembako Berkah'
    ];
    
    $placeholders = implode(',', array_fill(0, count($promoProductNames), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name IN ($placeholders)");
    $stmt->execute($promoProductNames);
    $dbPromoProducts = $stmt->fetchAll();
    
    // Index by name for easy lookup
    $promoItems = [];
    foreach ($dbPromoProducts as $p) {
        $promoItems[$p['name']] = $p;
    }
} catch (PDOException $e) {
    $promoItems = [];
    error_log('Promo products fetch error: ' . $e->getMessage());
}

// Function to safely get promo product data or mock if database query fails
function getPromoProduct($name, $promoItems) {
    if (isset($promoItems[$name])) {
        return $promoItems[$name];
    }
    // Fallback Mock
    return [
        'id' => 0,
        'name' => $name,
        'price' => 0.00,
        'image_url' => 'images/logo.png',
        'badge_label' => ''
    ];
}

// Original prices mapping
$originalPrices = [
    'Beras Rojolele Premium 5kg' => 75000.00,
    'Minyak Goreng SunCo 2L' => 42000.00,
    'Telur Ayam Negeri 1kg' => 28000.00,
    'Gula Pasir Gulaku 1kg' => 16500.00,
    'Teh Celup Premium 25s' => 10000.00,
    'Tepung Terigu Segitiga Biru 1kg' => 14000.00,
    'Paket Sembako Berkah' => 168000.00
];
?>
<div class="promo-container fade-in">
    
    <!-- Hero Banner Card -->
    <div class="promo-hero" style="background-image: linear-gradient(135deg, rgba(11, 45, 114, 0.95), rgba(9, 146, 194, 0.8)), url('<?= BASE_URL ?>assets/images/warung.webp');">
        <div class="promo-hero__content">
            <span class="promo-hero__badge">PROMO BULAN INI</span>
            <h1 class="promo-hero__title">Gelar Diskon Sembako<br>Meriah</h1>
            <div class="promo-hero__discount">30% <span class="promo-hero__discount-label">POTONGAN HARGA</span></div>
            <p class="promo-hero__desc">Stok kebutuhan dapur Anda dengan harga terbaik hanya bulan ini.</p>
            <a href="#katalog-promo" class="promo-hero__btn">Belanja Sekarang</a>
        </div>
    </div>

    <!-- Promo Catalog Header -->
    <div class="promo-section-header" id="katalog-promo">
        <div>
            <h2 class="promo-section-header__title">Katalog Promo Sembako</h2>
            <p class="promo-section-header__desc">Harga spesial untuk pelanggan setia Warung Tiga Saudara</p>
        </div>
        <div class="promo-section-header__actions">
            <button class="btn-filter-promo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                    <line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/>
                    <line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/>
                    <line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>
                </svg>
            </button>
            <div class="promo-countdown-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Berakhir dalam: 12 Hari
            </div>
        </div>
    </div>

    <!-- Catalog Grid (4 Items) -->
    <div class="promo-grid">
        <!-- Item 1: Beras Rojolele -->
        <?php $p1 = getPromoProduct('Beras Rojolele Premium 5kg', $promoItems); ?>
        <div class="promo-card">
            <div class="promo-card__img-wrapper">
                <img src="<?= htmlspecialchars(getProductImage($p1['name'], $p1['image_url'] ?? '')) ?>" alt="Beras Rojolele" class="promo-card__img">
                <span class="promo-card__discount-badge">HEMAT 15%</span>
            </div>
            <div class="promo-card__info">
                <span class="promo-card__category">SEMBAKO UTAMA</span>
                <h3 class="promo-card__title"><?= htmlspecialchars($p1['name']) ?></h3>
                <div class="promo-card__pricing">
                    <span class="promo-card__price-original"><?= formatRupiah($originalPrices['Beras Rojolele Premium 5kg']) ?></span>
                    <span class="promo-card__price-promo"><?= formatRupiah((float)$p1['price']) ?></span>
                </div>
                <!-- Stock Bar -->
                <div class="stock-status">
                    <div class="stock-status__text">
                        <span>Sisa Stok: <strong>12 Karung</strong></span>
                        <span class="text-danger font-bold">Terbatas!</span>
                    </div>
                    <div class="stock-progress-bar">
                        <div class="stock-progress-bar__fill bg-danger" style="width: 30%;"></div>
                    </div>
                </div>
                <!-- Add Form -->
                <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" class="promo-card__form">
                    <input type="hidden" name="product_id" value="<?= (int)$p1['id'] ?>">
                    <input type="hidden" name="redirect_page" value="promo">
                    <button type="submit" class="promo-card__btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>

        <!-- Item 2: Minyak SunCo -->
        <?php $p2 = getPromoProduct('Minyak Goreng SunCo 2L', $promoItems); ?>
        <div class="promo-card">
            <div class="promo-card__img-wrapper">
                <img src="<?= htmlspecialchars(getProductImage($p2['name'], $p2['image_url'] ?? '')) ?>" alt="Minyak SunCo" class="promo-card__img">
                <span class="promo-card__discount-badge">HEMAT 20%</span>
            </div>
            <div class="promo-card__info">
                <span class="promo-card__category">MINYAK GORENG</span>
                <h3 class="promo-card__title"><?= htmlspecialchars($p2['name']) ?></h3>
                <div class="promo-card__pricing">
                    <span class="promo-card__price-original"><?= formatRupiah($originalPrices['Minyak Goreng SunCo 2L']) ?></span>
                    <span class="promo-card__price-promo"><?= formatRupiah((float)$p2['price']) ?></span>
                </div>
                <!-- Stock Bar -->
                <div class="stock-status">
                    <div class="stock-status__text">
                        <span>Sisa Stok: <strong>45 Botol</strong></span>
                        <span class="text-warning font-bold">Stok Aman</span>
                    </div>
                    <div class="stock-progress-bar">
                        <div class="stock-progress-bar__fill bg-warning" style="width: 65%;"></div>
                    </div>
                </div>
                <!-- Add Form -->
                <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" class="promo-card__form">
                    <input type="hidden" name="product_id" value="<?= (int)$p2['id'] ?>">
                    <input type="hidden" name="redirect_page" value="promo">
                    <button type="submit" class="promo-card__btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>

        <!-- Item 3: Telur Ayam -->
        <?php $p3 = getPromoProduct('Telur Ayam Negeri 1kg', $promoItems); ?>
        <div class="promo-card">
            <div class="promo-card__img-wrapper">
                <img src="<?= htmlspecialchars(getProductImage($p3['name'], $p3['image_url'] ?? '')) ?>" alt="Telur Ayam" class="promo-card__img">
                <span class="promo-card__discount-badge">HEMAT 30%</span>
            </div>
            <div class="promo-card__info">
                <span class="promo-card__category">TELUR &amp; SUSU</span>
                <h3 class="promo-card__title"><?= htmlspecialchars($p3['name']) ?></h3>
                <div class="promo-card__pricing">
                    <span class="promo-card__price-original"><?= formatRupiah($originalPrices['Telur Ayam Negeri 1kg']) ?></span>
                    <span class="promo-card__price-promo"><?= formatRupiah((float)$p3['price']) ?></span>
                </div>
                <!-- Stock Bar -->
                <div class="stock-status">
                    <div class="stock-status__text">
                        <span>Sisa Stok: <strong>8 kg</strong></span>
                        <span class="text-danger font-bold">Sangat Terbatas!</span>
                    </div>
                    <div class="stock-progress-bar">
                        <div class="stock-progress-bar__fill bg-danger" style="width: 15%;"></div>
                    </div>
                </div>
                <!-- Add Form -->
                <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" class="promo-card__form">
                    <input type="hidden" name="product_id" value="<?= (int)$p3['id'] ?>">
                    <input type="hidden" name="redirect_page" value="promo">
                    <button type="submit" class="promo-card__btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>

        <!-- Item 4: Gulaku -->
        <?php $p4 = getPromoProduct('Gula Pasir Gulaku 1kg', $promoItems); ?>
        <div class="promo-card">
            <div class="promo-card__img-wrapper">
                <img src="<?= htmlspecialchars(getProductImage($p4['name'], $p4['image_url'] ?? '')) ?>" alt="Gulaku" class="promo-card__img">
                <span class="promo-card__discount-badge">HEMAT 10%</span>
            </div>
            <div class="promo-card__info">
                <span class="promo-card__category">KEBUTUHAN DAPUR</span>
                <h3 class="promo-card__title"><?= htmlspecialchars($p4['name']) ?></h3>
                <div class="promo-card__pricing">
                    <span class="promo-card__price-original"><?= formatRupiah($originalPrices['Gula Pasir Gulaku 1kg']) ?></span>
                    <span class="promo-card__price-promo"><?= formatRupiah((float)$p4['price']) ?></span>
                </div>
                <!-- Stock Bar -->
                <div class="stock-status">
                    <div class="stock-status__text">
                        <span>Sisa Stok: <strong>120 pack</strong></span>
                        <span class="text-success font-bold">Stok Melimpah</span>
                    </div>
                    <div class="stock-progress-bar">
                        <div class="stock-progress-bar__fill bg-success" style="width: 90%;"></div>
                    </div>
                </div>
                <!-- Add Form -->
                <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" class="promo-card__form">
                    <input type="hidden" name="product_id" value="<?= (int)$p4['id'] ?>">
                    <input type="hidden" name="redirect_page" value="promo">
                    <button type="submit" class="promo-card__btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Promo Cards Row -->
    <div class="promo-bottom-grid">
        <!-- Paket Hemat Card -->
        <?php $pkg = getPromoProduct('Paket Sembako Berkah', $promoItems); ?>
        <div class="promo-bundle-card">
            <span class="promo-bundle-card__badge">PAKET HEMAT KELUARGA</span>
            <div class="promo-bundle-card__content">
                <div class="promo-bundle-card__details">
                    <h3 class="promo-bundle-card__title"><?= htmlspecialchars($pkg['name']) ?></h3>
                    <p class="promo-bundle-card__desc">Isi Paket: Beras 5kg, Minyak 2L, Gula 1kg, Teh Celup, Kecap Manis, Saus Sambal, Kopi, Santan Instan, Energen, Tepung 1kg, Mie Instan (5pcs) &amp; Sabun Cuci Piring. Solusi praktis kebutuhan mingguan keluarga Anda.</p>
                    <div class="promo-bundle-card__pricing">
                        <span class="promo-bundle-card__price-promo"><?= formatRupiah((float)$pkg['price']) ?></span>
                        <span class="promo-bundle-card__price-original"><?= formatRupiah($originalPrices['Paket Sembako Berkah']) ?></span>
                    </div>
                    <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" style="margin-top:15px;">
                        <input type="hidden" name="product_id" value="<?= (int)$pkg['id'] ?>">
                        <input type="hidden" name="redirect_page" value="promo">
                        <button type="submit" class="promo-bundle-card__btn">Ambil Paket Sekarang</button>
                    </form>
                </div>
                <div class="promo-bundle-card__image-wrapper">
                    <img src="<?= htmlspecialchars(getProductImage($pkg['name'], $pkg['image_url'] ?? '')) ?>" alt="Paket Sembako Berkah" class="promo-bundle-card__image">
                </div>
            </div>
        </div>

        <!-- Rempah Nusantara promo list card -->
        <div class="promo-list-card">
            <h3 class="promo-list-card__title">Promo Rempah Nusantara</h3>
            <p class="promo-list-card__desc">Lengkapi bumbu dapur Anda dengan rempah pilihan terbaik dari petani lokal.</p>
            <ul class="promo-list-card__items">
                <li><span class="bullet-red"></span> Lada Putih (100g) - Rp 12rb</li>
                <li><span class="bullet-red"></span> Ketumbar Bubuk - Rp 5rb</li>
                <li><span class="bullet-red"></span> Kayu Manis - Rp 8rb</li>
            </ul>
            <div class="promo-list-card__footer">
                <img src="<?= BASE_URL ?>assets/images/rempah-promo.svg" alt="Rempah Nusantara" class="promo-list-card__img">
                <a href="index.php?page=rempah" class="promo-list-card__btn">Lihat Koleksi Rempah</a>
            </div>
        </div>

        <!-- Right Column Stack of Two Cards -->
        <div class="promo-stack-column">
            <!-- Card 1: Teh Celup -->
            <?php $pTeh = getPromoProduct('Teh Celup Premium 25s', $promoItems); ?>
            <div class="promo-stack-card">
                <div class="promo-stack-card__image-col">
                    <img src="<?= htmlspecialchars(getProductImage($pTeh['name'], $pTeh['image_url'] ?? '')) ?>" alt="Teh Celup" class="promo-stack-card__img">
                    <span class="promo-stack-card__badge">HEMAT 5%</span>
                </div>
                <div class="promo-stack-card__info-col">
                    <h4 class="promo-stack-card__title"><?= htmlspecialchars($pTeh['name']) ?></h4>
                    <div class="promo-stack-card__pricing">
                        <span class="promo-stack-card__price-promo"><?= formatRupiah((float)$pTeh['price']) ?></span>
                        <span class="promo-stack-card__price-original"><?= formatRupiah($originalPrices['Teh Celup Premium 25s']) ?></span>
                    </div>
                    <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="product_id" value="<?= (int)$pTeh['id'] ?>">
                        <input type="hidden" name="redirect_page" value="promo">
                        <button type="submit" class="promo-stack-card__add-btn" title="Tambah ke Keranjang">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card 2: Tepung Terigu -->
            <?php $pTepung = getPromoProduct('Tepung Terigu Segitiga Biru 1kg', $promoItems); ?>
            <div class="promo-stack-card">
                <div class="promo-stack-card__image-col">
                    <img src="<?= htmlspecialchars(getProductImage($pTepung['name'], $pTepung['image_url'] ?? '')) ?>" alt="Tepung Terigu" class="promo-stack-card__img">
                    <span class="promo-stack-card__badge">HEMAT 12%</span>
                </div>
                <div class="promo-stack-card__info-col">
                    <h4 class="promo-stack-card__title"><?= htmlspecialchars($pTepung['name']) ?></h4>
                    <div class="promo-stack-card__pricing">
                        <span class="promo-stack-card__price-promo"><?= formatRupiah((float)$pTepung['price']) ?></span>
                        <span class="promo-stack-card__price-original"><?= formatRupiah($originalPrices['Tepung Terigu Segitiga Biru 1kg']) ?></span>
                    </div>
                    <form action="<?= BASE_URL ?>process/cart_action.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="product_id" value="<?= (int)$pTepung['id'] ?>">
                        <input type="hidden" name="redirect_page" value="promo">
                        <button type="submit" class="promo-stack-card__add-btn" title="Tambah ke Keranjang">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat Button -->
    <a href="index.php?page=kontak" class="floating-chat-btn" title="Hubungi Kami">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </a>

</div>

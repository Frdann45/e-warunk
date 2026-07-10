<?php
/**
 * =======
 * Warung Tiga Saudara - Keranjang view
 * Author ID: 11240044
 * =======
 */
require_once __DIR__ . '/../db_connect.php';

// Helper to get cart products with quantities
$cartItems = [];
$totalOriginal = 0;
$totalCount = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    
    // Fetch products in cart
    if (!empty($productIds)) {
        try {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($productIds);
            $dbProducts = $stmt->fetchAll();
            
            foreach ($dbProducts as $product) {
                $id = $product['id'];
                $qty = $_SESSION['cart'][$id];
                $subtotal = $product['price'] * $qty;
                
                $cartItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $subtotal
                ];
                
                $totalOriginal += $subtotal;
                $totalCount += $qty;
            }
        } catch (PDOException $e) {
            error_log('Cart products fetch error: ' . $e->getMessage());
        }
    }
}

// Calculations based on screenshot
$promoDiscount = ($totalOriginal >= 190000) ? 10000 : 0; // matching Rp 190.000 -> Rp 10.000 discount
$serviceFee = empty($_SESSION['cart']) ? 0 : 2000;
$totalBill = $totalOriginal - $promoDiscount + $serviceFee;
?>

<div class="cart-header fade-in">
    <h1 class="cart-header__title">Keranjang Belanja</h1>
    <span class="cart-header__badge"><?= count($cartItems) ?> Item terpilih</span>
</div>

<?php if (empty($cartItems)): ?>
    <div class="cart-empty fade-in">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="cart-empty__icon">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
        </svg>
        <h2 class="cart-empty__title">Keranjang Belanja Anda Kosong</h2>
        <p class="cart-empty__desc">Kembali ke katalog dan tambahkan beberapa produk segar pilihan Anda.</p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <a href="index.php?page=sembako" class="btn-shop-now">Belanja Sekarang</a>
            <form action="cart_action.php" method="POST">
                <input type="hidden" name="action" value="seed_demo">
                <button type="submit" class="btn-seed-demo">Gunakan Data Demo</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="cart-layout fade-in">
        <!-- Cart Items List -->
        <div class="cart-items">
            <?php foreach ($cartItems as $item): 
                $p = $item['product']; 
                $qty = $item['qty']; 
                $sub = $item['subtotal']; ?>
                <div class="cart-card">
                    <img src="<?= htmlspecialchars(getProductImage($p['name'], $p['image_url'] ?? '')) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="cart-card__image">
                    
                    <div class="cart-card__details">
                        <span class="cart-card__category"><?= htmlspecialchars($p['category']) ?></span>
                        <h3 class="cart-card__name"><?= htmlspecialchars($p['name']) ?></h3>
                        <p class="cart-card__price"><?= formatRupiah((float) $p['price']) ?></p>
                    </div>

                    <div class="cart-card__actions">
                        <!-- Quantity selector form -->
                        <div class="qty-selector">
                            <form action="cart_action.php" method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="qty" value="<?= $qty - 1 ?>">
                                <input type="hidden" name="redirect_page" value="keranjang">
                                <button type="submit" class="btn-qty" <?= $qty <= 1 ? 'disabled' : '' ?>>-</button>
                            </form>
                            <span class="qty-value"><?= $qty ?></span>
                            <form action="cart_action.php" method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="qty" value="<?= $qty + 1 ?>">
                                <input type="hidden" name="redirect_page" value="keranjang">
                                <button type="submit" class="btn-qty">+</button>
                            </form>
                        </div>

                        <!-- Remove form -->
                        <form action="cart_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="hidden" name="redirect_page" value="keranjang">
                            <button type="submit" class="btn-delete" title="Hapus Item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary Sidebar -->
        <div class="cart-summary">
            <div class="summary-card">
                <h3 class="summary-card__title">Ringkasan Belanja</h3>
                
                <div class="summary-card__row">
                    <span class="summary-card__label">Total Harga (<?= $totalCount ?> barang)</span>
                    <span class="summary-card__val"><?= formatRupiah((float) $totalOriginal) ?></span>
                </div>
                
                <div class="summary-card__row">
                    <span class="summary-card__label">Diskon Promo</span>
                    <span class="summary-card__val text-discount">-<?= formatRupiah((float) $promoDiscount) ?></span>
                </div>
                
                <div class="summary-card__row">
                    <span class="summary-card__label">Biaya Layanan</span>
                    <span class="summary-card__val"><?= formatRupiah((float) $serviceFee) ?></span>
                </div>
                
                <div class="summary-card__divider"></div>
                
                <div class="summary-card__total-row">
                    <span class="summary-card__total-label">Total Tagihan</span>
                    <span class="summary-card__total-val"><?= formatRupiah((float) $totalBill) ?></span>
                </div>
                
                <a href="index.php?page=pembayaran" class="btn-checkout">LANJUT KE PEMBAYARAN</a>
            </div>
        </div>
    </div>
<?php endif; ?>

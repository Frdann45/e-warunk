<?php
/**
 * =======
 * Warung Tiga Saudara - Pembayaran (Checkout) view
 * Author ID: 11240044
 * =======
 */
require_once __DIR__ . '/../db_connect.php';

// If cart is empty, show empty state or redirect
$cartItems = [];
$totalOriginal = 0;
$totalCount = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
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
            error_log('Checkout items fetch error: ' . $e->getMessage());
        }
    }
}

// Calculations based on screenshot
$shippingFee = empty($_SESSION['cart']) ? 0 : 15000;
$shippingDiscount = empty($_SESSION['cart']) ? 0 : 5000;
$totalBill = $totalOriginal + $shippingFee - $shippingDiscount;

// Address details (from screenshot)
$addressName = 'Budi Santoso';
$addressPhone = '+62 812 3456 7890';
$addressText = "Jl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240";

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($cartItems)) {
        $_SESSION['cart_message'] = 'Keranjang kosong, gagal membuat pesanan.';
        header('Location: index.php?page=sembako');
        exit;
    }
    
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Bank Transfer';
    $orderCode = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    $fullAddress = "{$addressName}\n{$addressPhone}\n{$addressText}";
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insert order
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (order_code, order_date, total_price, shipping_address, payment_method, status) 
            VALUES (?, NOW(), ?, ?, ?, 'Diproses')
        ");
        $stmtOrder->execute([$orderCode, $totalBill, $fullAddress, $paymentMethod]);
        $orderId = $pdo->lastInsertId();
        
        // 2. Insert order items
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_name, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $stmtItem->execute([
                $orderId, 
                $item['product']['name'], 
                $item['qty'], 
                $item['product']['price']
            ]);
        }
        
        $pdo->commit();
        
        // 3. Clear cart and set success message
        $_SESSION['cart'] = [];
        $_SESSION['cart_message'] = 'Pembayaran Berhasil! Pesanan Anda sedang diproses.';
        header('Location: index.php?page=riwayat');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Order submission error: ' . $e->getMessage());
        $_SESSION['cart_message'] = 'Terjadi kesalahan sistem, silakan coba lagi.';
    }
}
?>

<div class="checkout-header fade-in">
    <h1 class="checkout-header__title">Checkout</h1>
</div>

<?php if (empty($cartItems)): ?>
    <div class="cart-empty fade-in">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="cart-empty__icon">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        <h2 class="cart-empty__title">Tidak Ada Pesanan untuk di-Checkout</h2>
        <p class="cart-empty__desc">Silakan masukkan beberapa produk ke keranjang belanja Anda terlebih dahulu.</p>
        <a href="index.php?page=sembako" class="btn-shop-now" style="margin-top: 16px;">Belanja Sekarang</a>
    </div>
<?php else: ?>
    <form action="" method="POST" id="checkout-form">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="payment_method" id="input-payment-method" value="Bank Transfer">
        
        <div class="checkout-layout fade-in">
            <!-- Checkout Content -->
            <div class="checkout-content">
                <!-- Address Section -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="checkout-section__title">Alamat Pengiriman</span>
                        </div>
                        <button type="button" class="btn-change-address">Ubah Alamat</button>
                    </div>
                    <div class="checkout-section__body">
                        <div class="address-details">
                            <h4 class="address-details__name"><?= htmlspecialchars($addressName) ?></h4>
                            <p class="address-details__phone"><?= htmlspecialchars($addressPhone) ?></p>
                            <p class="address-details__text"><?= nl2br(htmlspecialchars($addressText)) ?></p>
                            <span class="address-details__badge">Utama</span>
                        </div>
                    </div>
                </div>

                <!-- Order Details Section -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            <span class="checkout-section__title">Rincian Pesanan</span>
                        </div>
                    </div>
                    <div class="checkout-section__body">
                        <div class="checkout-items-list">
                            <?php foreach ($cartItems as $item): 
                                $p = $item['product']; 
                                $qty = $item['qty']; 
                                $sub = $item['subtotal']; ?>
                                <div class="checkout-item-row">
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="checkout-item-row__image">
                                    <div class="checkout-item-row__info">
                                        <h4 class="checkout-item-row__name"><?= htmlspecialchars($p['name']) ?></h4>
                                        <p class="checkout-item-row__qty"><?= $qty ?> x <?= formatRupiah((float) $p['price']) ?></p>
                                    </div>
                                    <span class="checkout-item-row__price"><?= formatRupiah((float) $sub) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            <span class="checkout-section__title">Metode Pembayaran</span>
                        </div>
                    </div>
                    <div class="checkout-section__body">
                        <div class="payment-methods-grid">
                            <!-- Bank Transfer -->
                            <div class="payment-method-box payment-method-box--active" data-method="Bank Transfer" id="pay-bank">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <span class="payment-method-box__name">Bank Transfer</span>
                                <span class="payment-method-box__check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </div>

                            <!-- E-Wallet -->
                            <div class="payment-method-box" data-method="E-Wallet" id="pay-wallet">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <span class="payment-method-box__name">E-Wallet</span>
                                <span class="payment-method-box__check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </div>

                            <!-- COD -->
                            <div class="payment-method-box" data-method="COD" id="pay-cod">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/>
                                    <circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                <span class="payment-method-box__name">COD</span>
                                <span class="payment-method-box__check">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Summary Sidebar -->
            <div class="checkout-summary">
                <div class="summary-card">
                    <h3 class="summary-card__title">Ringkasan Belanja</h3>
                    
                    <div class="summary-card__row">
                        <span class="summary-card__label">Total Harga (<?= $totalCount ?> barang)</span>
                        <span class="summary-card__val"><?= formatRupiah((float) $totalOriginal) ?></span>
                    </div>
                    
                    <div class="summary-card__row">
                        <span class="summary-card__label">Ongkos Kirim</span>
                        <span class="summary-card__val"><?= formatRupiah((float) $shippingFee) ?></span>
                    </div>
                    
                    <div class="summary-card__row">
                        <span class="summary-card__label">Diskon Ongkir</span>
                        <span class="summary-card__val text-discount">-<?= formatRupiah((float) $shippingDiscount) ?></span>
                    </div>
                    
                    <div class="summary-card__divider"></div>
                    
                    <div class="summary-card__total-row">
                        <span class="summary-card__total-label">Total Tagihan</span>
                        <span class="summary-card__total-val"><?= formatRupiah((float) $totalBill) ?></span>
                    </div>
                    
                    <button type="submit" class="btn-pay-now">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-pay-now__icon">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Bayar Sekarang
                    </button>
                    
                    <div class="secure-checkout-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="secure-checkout-badge__icon">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Pembayaran Aman
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Handle payment method switching
        var paymentBoxes = document.querySelectorAll('.payment-method-box');
        var inputPaymentMethod = document.getElementById('input-payment-method');
        
        paymentBoxes.forEach(function(box) {
            box.addEventListener('click', function() {
                // Remove active class from all
                paymentBoxes.forEach(function(b) {
                    b.classList.remove('payment-method-box--active');
                });
                
                // Add active class to clicked box
                box.classList.add('payment-method-box--active');
                
                // Update hidden input value
                inputPaymentMethod.value = box.getAttribute('data-method');
            });
        });
    </script>
<?php endif; ?>

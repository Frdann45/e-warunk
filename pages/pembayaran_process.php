<?php
/**
 * =======
 * Warung Tiga Saudara - Pembayaran POST Handler
 * Author ID: 11240044
 * =======
 * 
 * This file processes the checkout form submission BEFORE any
 * HTML output, so that header() redirects work correctly.
 * It is included by index.php when:
 *   - page=pembayaran
 *   - method=POST
 *   - place_order is set
 */

// Fetch cart items from database
$cartItems = [];
$totalOriginal = 0;

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
            }
        } catch (PDOException $e) {
            error_log('Checkout items fetch error: ' . $e->getMessage());
        }
    }
}

// Calculate totals
$shippingFee = empty($_SESSION['cart']) ? 0 : 15000;
$shippingDiscount = empty($_SESSION['cart']) ? 0 : 5000;
$totalBill = $totalOriginal + $shippingFee - $shippingDiscount;

// Address details
$addressName = 'Budi Santoso';
$addressPhone = '+62 812 3456 7890';
$addressText = "Jl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240";

// ── Process the order ───────────────────────────────────────
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
    
    // 3. Clear cart and redirect
    $_SESSION['cart'] = [];
    $_SESSION['cart_message'] = 'Pembayaran Berhasil! Pesanan Anda sedang diproses.';
    header('Location: index.php?page=riwayat');
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Order submission error: ' . $e->getMessage());
    $_SESSION['cart_message'] = 'Terjadi kesalahan sistem, silakan coba lagi.';
    // Fall through — index.php will render the pembayaran page with the error toast
}

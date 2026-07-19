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

// Address details (resolved dynamically from database if possible)
$addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;
$addressName = 'Budi Santoso';
$addressPhone = '+62 812 3456 7890';
$addressText = "Jl. Kebon Kacang Raya No. 15, RT.01/RW.02\nTanah Abang, Jakarta Pusat\nDKI Jakarta 10240";

if ($addressId > 0) {
    try {
        $stmtAddr = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $stmtAddr->execute([$addressId, $_SESSION['user_id']]);
        $addr = $stmtAddr->fetch();
        if ($addr) {
            $addressName = $addr['recipient_name'];
            $addressPhone = $addr['phone'];
            $addressText = $addr['address_line'] . "\n" . $addr['city'] . ', ' . $addr['province'] . ' ' . $addr['postal_code'];
        }
    } catch (PDOException $e) {
        error_log('Address fetch error during checkout process: ' . $e->getMessage());
    }
}

// ── Process the order ───────────────────────────────────────
if (empty($cartItems)) {
    $_SESSION['cart_message'] = 'Keranjang kosong, gagal membuat pesanan.';
    header('Location: ' . BASE_URL . 'index.php?page=sembako');
    exit;
}

$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Virtual Account';
$orderCode     = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
$fullAddress   = "{$addressName}\n{$addressPhone}\n{$addressText}";
$isCod          = ($paymentMethod === 'COD');
$initialStatus = $isCod ? 'Diproses' : 'Belum Bayar';

try {
    $pdo->beginTransaction();
    
    // 1. Insert order
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (order_code, order_date, total_price, shipping_address, payment_method, status) 
        VALUES (?, NOW(), ?, ?, ?, ?)
    ");
    $stmtOrder->execute([$orderCode, $totalBill, $fullAddress, $paymentMethod, $initialStatus]);
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
    
    // If it's COD, we are done. Commit and redirect.
    if ($isCod) {
        $pdo->commit();
        $_SESSION['cart'] = [];
        $_SESSION['cart_message'] = 'Pesanan Anda berhasil dibuat! Pembayaran akan dilakukan saat kurir tiba.';
        header('Location: ' . BASE_URL . 'index.php?page=riwayat');
        exit;
    }
    
    // ── Xendit Integration for Digital Payments ────────────────
    require_once dirname(__DIR__) . '/config/config_xendit.php';
    
    // Determine allowed payment channels
    $xenditMethods = [];
    if ($paymentMethod === 'Virtual Account') {
        $xenditMethods = ['VIRTUAL_ACCOUNT'];
    } elseif ($paymentMethod === 'E-Wallet') {
        $xenditMethods = ['EWALLET'];
    } elseif ($paymentMethod === 'QRIS') {
        $xenditMethods = ['QRIS'];
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $successRedirectUrl = $protocol . '://' . $host . '/e-warung/index.php?page=riwayat';
    
    $payload = [
        'external_id'          => $orderCode,
        'amount'               => $totalBill,
        'payer_email'          => $_SESSION['email'] ?? 'customer@ewarung.com',
        'description'          => 'Pembayaran Pesanan e-warung #' . $orderCode,
        'customer'             => [
            'given_names' => $_SESSION['name'] ?? 'Pelanggan',
            'email'       => $_SESSION['email'] ?? 'customer@ewarung.com'
        ],
        'success_redirect_url' => $successRedirectUrl
    ];
    
    if (!empty($xenditMethods)) {
        $payload['payment_methods'] = $xenditMethods;
    }
    
    $jsonPayload = json_encode($payload);
    
    // Execute cURL request to Xendit Invoices API
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.xendit.co/v2/invoices',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(XENDIT_SECRET_KEY . ':')
        ],
    ]);
    
    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        error_log('Xendit cURL error during checkout: ' . $err);
        throw new Exception('Gagal terhubung dengan server gateway pembayaran.');
    }
    
    $responseDecoded = json_decode($response, true);
    if (isset($responseDecoded['invoice_url'])) {
        $invoiceUrl = $responseDecoded['invoice_url'];
        
        // Save the payment link in the orders table
        $stmtUpdate = $pdo->prepare("UPDATE orders SET payment_url = ? WHERE id = ?");
        $stmtUpdate->execute([$invoiceUrl, $orderId]);
        
        $pdo->commit();
        
        // Clear cart and redirect to the invoice
        $_SESSION['cart'] = [];
        header('Location: ' . $invoiceUrl);
        exit;
    } else {
        $apiError = isset($responseDecoded['message']) ? $responseDecoded['message'] : 'Kesalahan internal Xendit.';
        error_log('Xendit Invoice creation failure: ' . $response);
        throw new Exception($apiError);
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Order submission error: ' . $e->getMessage());
    $_SESSION['cart_message'] = 'Gagal memproses transaksi: ' . $e->getMessage();
    header('Location: ' . BASE_URL . 'index.php?page=pembayaran');
    exit;
}

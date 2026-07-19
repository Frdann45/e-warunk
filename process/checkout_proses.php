<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Xendit Checkout Process
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Handles Xendit Invoice Creation via cURL.
 *               Redirects user to the generated payment invoice page.
 * ============================================================
 */

session_start();
require_once dirname(__DIR__) . '/config/config_xendit.php';

// Auth Guard: ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php?page=beranda');
    exit;
}

// Retrieve POST parameters
$orderId       = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
$totalAmount   = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0.0;
$customerEmail = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
$customerName  = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';

// Validation checks
if (empty($orderId) || $totalAmount <= 0 || empty($customerEmail) || empty($customerName)) {
    $_SESSION['cart_message'] = 'Informasi pembayaran tidak lengkap. Gagal memproses transaksi.';
    header('Location: ' . BASE_URL . 'index.php?page=keranjang');
    exit;
}

// Determine success redirect URL pointing to the user's order history
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$successRedirectUrl = BASE_URL . 'index.php?page=riwayat';

// ── Construct Xendit Payload ────────────────────────────────
$payload = [
    'external_id'          => $orderId,
    'amount'               => $totalAmount,
    'payer_email'          => $customerEmail,
    'description'          => 'Pembayaran Pesanan e-warung #' . $orderId,
    'customer'             => [
        'given_names' => $customerName,
        'email'       => $customerEmail
    ],
    'success_redirect_url' => $successRedirectUrl
];

$jsonPayload = json_encode($payload);

// ── cURL Setup to Call Xendit Invoice API ───────────────────
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
    error_log('Xendit Invoice Creation cURL Error: ' . $err);
    $_SESSION['cart_message'] = 'Gagal terhubung dengan server pembayaran. Coba lagi nanti.';
    header('Location: ' . BASE_URL . 'index.php?page=pembayaran');
    exit;
}

$responseDecoded = json_decode($response, true);

if (isset($responseDecoded['invoice_url'])) {
    $invoiceUrl = $responseDecoded['invoice_url'];
    // Redirect user to the payment link
    header('Location: ' . $invoiceUrl);
    exit;
} else {
    // Log API response mismatch
    error_log('Xendit Invoice API Error response: ' . $response);
    $_SESSION['cart_message'] = 'Gagal membuat Invoice Pembayaran. Detail: ' . ($responseDecoded['message'] ?? 'Unknown Error');
    header('Location: ' . BASE_URL . 'index.php?page=pembayaran');
    exit;
}

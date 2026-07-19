<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Cart Action Handler
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-24
 * Description : Handles shopping cart actions (add, update, remove, seed_demo).
 *               Stores product counts in $_SESSION['cart'] [id => qty].
 * ============================================================
 */

session_start();
require_once dirname(__DIR__) . '/config/db_connect.php';

// ── Auth Guard ────────────────────────────────────────────
// Semua aksi keranjang membutuhkan login.
// Jika belum login, simpan pesan & arahkan ke halaman login.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required_message'] = 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.';
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}
// ─────────────────────────────────────────────────────────

// Initialize cart as associative array [product_id => quantity]
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : 'add';
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
$redirectPage = isset($_POST['redirect_page']) ? $_POST['redirect_page'] : '';

// 1. Add item to cart
if ($action === 'add' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += 1;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }
    $_SESSION['cart_message'] = 'Produk berhasil ditambahkan ke keranjang!';
}

// 2. Update item quantity
elseif ($action === 'update' && $productId > 0) {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['cart_message'] = 'Produk dihapus dari keranjang.';
    } else {
        $_SESSION['cart'][$productId] = $qty;
        $_SESSION['cart_message'] = 'Jumlah produk berhasil diperbarui.';
    }
}

// 3. Remove item from cart
elseif ($action === 'remove' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['cart_message'] = 'Produk berhasil dihapus dari keranjang.';
    }
}

// 4. Clear cart
elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    $_SESSION['cart_message'] = 'Keranjang telah dikosongkan.';
}

// 5. Seed demo cart items matching the cart design screenshot
elseif ($action === 'seed_demo') {
    $_SESSION['cart'] = [
        3 => 2, // Beras Rojolele Premium 5kg (qty 2)
        4 => 1, // Minyak Goreng Bimoli 2L (qty 1)
        5 => 1  // Bawang Merah Brebes 500g (qty 1)
    ];
    $_SESSION['cart_message'] = 'Berhasil memuat data demo keranjang!';
    $redirectPage = 'keranjang';
}

// Build redirection URL
$redirectUrl = 'index.php';
if ($redirectPage !== '') {
    $parts = explode('&', $redirectPage);
    $params = [];
    foreach ($parts as $idx => $part) {
        $keyValue = explode('=', $part, 2);
        if (count($keyValue) === 2) {
            $params[] = urlencode($keyValue[0]) . '=' . urlencode($keyValue[1]);
        } else {
            if ($idx === 0) {
                $params[] = 'page=' . urlencode($keyValue[0]);
            } else {
                $params[] = urlencode($keyValue[0]);
            }
        }
    }
    $redirectUrl .= '?' . implode('&', $params);
}

header('Location: ' . BASE_URL . $redirectUrl);
exit;

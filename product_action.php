<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Product & Promo Action Handler
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-29
 * Description : Standalone POST handler for product CRUD
 *               (add, update, delete) and badge/promo management
 *               (set_badge, remove_badge, clear_all_badges).
 *               Uses PRG pattern to prevent double-submit.
 * ============================================================
 */

session_start();
require_once __DIR__ . '/db_connect.php';

// ── Auth guard ────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ── Only handle POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action      = trim($_POST['action'] ?? '');
$flashMsg    = '';
$flashType   = 'success';

// ══════════════════════════════════════════════════════════════
// PRODUCT CRUD ACTIONS
// ══════════════════════════════════════════════════════════════

// ── 1. Add product ────────────────────────────────────────────
if ($action === 'add') {
    $name      = trim($_POST['name'] ?? '');
    $category  = trim($_POST['category'] ?? '');
    $unit_desc = trim($_POST['unit_desc'] ?? '');
    $price     = (float) ($_POST['price'] ?? 0);
    $badge     = trim($_POST['badge_label'] ?? '') ?: null;
    $image_url = trim($_POST['image_url'] ?? '') ?: 'images/logo.png';

    if ($name && $category && $unit_desc && $price > 0) {
        try {
            $pdo->prepare("
                INSERT INTO products (name, category, unit_desc, price, image_url, badge_label)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([$name, $category, $unit_desc, $price, $image_url, $badge]);
            $flashMsg = "Produk \"$name\" berhasil ditambahkan.";
        } catch (PDOException $e) {
            error_log('Add product error: ' . $e->getMessage());
            $flashMsg  = 'Gagal menambahkan produk. Pastikan nama produk belum terdaftar.';
            $flashType = 'error';
        }
    } else {
        $flashMsg  = 'Mohon lengkapi semua field yang wajib diisi.';
        $flashType = 'error';
    }

    $_SESSION['prod_flash']      = $flashMsg;
    $_SESSION['prod_flash_type'] = $flashType;
    header('Location: index.php?page=tambah-produk');
    exit;
}

// ── 2. Update product ─────────────────────────────────────────
elseif ($action === 'update') {
    $id        = (int) ($_POST['product_id'] ?? 0);
    $name      = trim($_POST['name'] ?? '');
    $category  = trim($_POST['category'] ?? '');
    $unit_desc = trim($_POST['unit_desc'] ?? '');
    $price     = (float) ($_POST['price'] ?? 0);
    $badge     = trim($_POST['badge_label'] ?? '') ?: null;
    $image_url = trim($_POST['image_url'] ?? '') ?: 'images/logo.png';

    if ($id > 0 && $name && $category && $unit_desc && $price > 0) {
        try {
            $pdo->prepare("
                UPDATE products
                SET name=?, category=?, unit_desc=?, price=?, image_url=?, badge_label=?
                WHERE id=?
            ")->execute([$name, $category, $unit_desc, $price, $image_url, $badge, $id]);
            $flashMsg = "Produk \"$name\" berhasil diperbarui.";
        } catch (PDOException $e) {
            error_log('Update product error: ' . $e->getMessage());
            $flashMsg  = 'Gagal memperbarui produk.';
            $flashType = 'error';
        }
    } else {
        $flashMsg  = 'Data tidak valid. Periksa kembali semua field.';
        $flashType = 'error';
    }

    $_SESSION['prod_flash']      = $flashMsg;
    $_SESSION['prod_flash_type'] = $flashType;
    header('Location: index.php?page=tambah-produk');
    exit;
}

// ── 3. Delete product ─────────────────────────────────────────
elseif ($action === 'delete') {
    $id = (int) ($_POST['product_id'] ?? 0);
    if ($id > 0) {
        try {
            $stmtName = $pdo->prepare("SELECT name FROM products WHERE id=?");
            $stmtName->execute([$id]);
            $deletedName = $stmtName->fetchColumn();

            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            $flashMsg = "Produk \"$deletedName\" berhasil dihapus.";
        } catch (PDOException $e) {
            error_log('Delete product error: ' . $e->getMessage());
            $flashMsg  = 'Gagal menghapus produk.';
            $flashType = 'error';
        }
    }

    $_SESSION['prod_flash']      = $flashMsg;
    $_SESSION['prod_flash_type'] = $flashType;
    header('Location: index.php?page=tambah-produk');
    exit;
}

// ══════════════════════════════════════════════════════════════
// BADGE / PROMO ACTIONS
// ══════════════════════════════════════════════════════════════

// ── 4. Set badge on a product ─────────────────────────────────
elseif ($action === 'set_badge') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $badge     = trim($_POST['badge_label'] ?? '') ?: null;

    if ($productId > 0) {
        try {
            $pdo->prepare("UPDATE products SET badge_label=? WHERE id=?")
                ->execute([$badge, $productId]);

            $stmtName = $pdo->prepare("SELECT name FROM products WHERE id=?");
            $stmtName->execute([$productId]);
            $pName = $stmtName->fetchColumn();

            $flashMsg = $badge
                ? "Badge \"$badge\" berhasil dipasang pada produk \"$pName\"."
                : "Badge pada produk \"$pName\" berhasil dihapus.";
        } catch (PDOException $e) {
            error_log('Set badge error: ' . $e->getMessage());
            $flashMsg  = 'Gagal mengubah badge produk.';
            $flashType = 'error';
        }
    }

    $_SESSION['promo_flash']      = $flashMsg;
    $_SESSION['promo_flash_type'] = $flashType;
    header('Location: index.php?page=buat-promo');
    exit;
}

// ── 5. Remove badge from one product ─────────────────────────
elseif ($action === 'remove_badge') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        try {
            $pdo->prepare("UPDATE products SET badge_label=NULL WHERE id=?")
                ->execute([$productId]);
            $stmtName = $pdo->prepare("SELECT name FROM products WHERE id=?");
            $stmtName->execute([$productId]);
            $pName    = $stmtName->fetchColumn();
            $flashMsg = "Badge pada produk \"$pName\" berhasil dihapus.";
        } catch (PDOException $e) {
            error_log('Remove badge error: ' . $e->getMessage());
            $flashMsg  = 'Gagal menghapus badge.';
            $flashType = 'error';
        }
    }

    $_SESSION['promo_flash']      = $flashMsg;
    $_SESSION['promo_flash_type'] = $flashType;
    header('Location: index.php?page=buat-promo');
    exit;
}

// ── 6. Bulk clear all badges ──────────────────────────────────
elseif ($action === 'clear_all_badges') {
    try {
        $pdo->exec("UPDATE products SET badge_label=NULL");
        $flashMsg = 'Semua badge promosi berhasil dihapus.';
    } catch (PDOException $e) {
        error_log('Clear all badges error: ' . $e->getMessage());
        $flashMsg  = 'Gagal menghapus semua badge.';
        $flashType = 'error';
    }

    $_SESSION['promo_flash']      = $flashMsg;
    $_SESSION['promo_flash_type'] = $flashType;
    header('Location: index.php?page=buat-promo');
    exit;
}

// ── Unknown action: redirect safely ──────────────────────────
header('Location: index.php');
exit;

<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Address Action Handler
 * ============================================================
 * Author ID   : 11240044
 * Description : Handles CRUD for user shipping addresses.
 *               Actions: add, update, delete, set_primary
 * ============================================================
 */

session_start();
require_once dirname(__DIR__) . '/config/db_connect.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = (int) $_SESSION['user_id'];
$action   = isset($_POST['action']) ? $_POST['action'] : '';
$redirect = 'index.php?page=pembayaran';

// ── Helper: sanitize string ──────────────────────────────
function sanitize(string $val): string {
    return trim(htmlspecialchars_decode(strip_tags(trim($val))));
}

// ── 1. Add new address ───────────────────────────────────
if ($action === 'add') {
    $name       = sanitize($_POST['recipient_name'] ?? '');
    $phone      = sanitize($_POST['phone'] ?? '');
    $addrLine   = sanitize($_POST['address_line'] ?? '');
    $city       = sanitize($_POST['city'] ?? '');
    $province   = sanitize($_POST['province'] ?? '');
    $postal     = sanitize($_POST['postal_code'] ?? '');
    $isPrimary  = isset($_POST['is_primary']) ? 1 : 0;

    if ($name && $phone && $addrLine && $city && $province && $postal) {
        try {
            // If set as primary, unset others first
            if ($isPrimary) {
                $pdo->prepare("UPDATE addresses SET is_primary=0 WHERE user_id=?")->execute([$userId]);
            }
            // If no address exists yet, force primary
            $count = $pdo->prepare("SELECT COUNT(*) FROM addresses WHERE user_id=?");
            $count->execute([$userId]);
            if ($count->fetchColumn() == 0) $isPrimary = 1;

            $stmt = $pdo->prepare("
                INSERT INTO addresses (user_id, recipient_name, phone, address_line, city, province, postal_code, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $name, $phone, $addrLine, $city, $province, $postal, $isPrimary]);
            $_SESSION['address_message'] = 'Alamat baru berhasil ditambahkan.';
        } catch (PDOException $e) {
            error_log('Address add error: ' . $e->getMessage());
            $_SESSION['address_message'] = 'Gagal menambahkan alamat.';
        }
    } else {
        $_SESSION['address_message'] = 'Mohon lengkapi semua field alamat.';
    }
}

// ── 2. Update existing address ───────────────────────────
elseif ($action === 'update') {
    $addrId    = (int) ($_POST['address_id'] ?? 0);
    $name      = sanitize($_POST['recipient_name'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $addrLine  = sanitize($_POST['address_line'] ?? '');
    $city      = sanitize($_POST['city'] ?? '');
    $province  = sanitize($_POST['province'] ?? '');
    $postal    = sanitize($_POST['postal_code'] ?? '');
    $isPrimary = isset($_POST['is_primary']) ? 1 : 0;

    if ($addrId > 0 && $name && $phone && $addrLine && $city && $province && $postal) {
        try {
            // Verify ownership
            $own = $pdo->prepare("SELECT id FROM addresses WHERE id=? AND user_id=?");
            $own->execute([$addrId, $userId]);
            if ($own->fetch()) {
                if ($isPrimary) {
                    $pdo->prepare("UPDATE addresses SET is_primary=0 WHERE user_id=?")->execute([$userId]);
                }
                $stmt = $pdo->prepare("
                    UPDATE addresses
                    SET recipient_name=?, phone=?, address_line=?, city=?, province=?, postal_code=?, is_primary=?
                    WHERE id=? AND user_id=?
                ");
                $stmt->execute([$name, $phone, $addrLine, $city, $province, $postal, $isPrimary, $addrId, $userId]);
                $_SESSION['address_message'] = 'Alamat berhasil diperbarui.';
            }
        } catch (PDOException $e) {
            error_log('Address update error: ' . $e->getMessage());
            $_SESSION['address_message'] = 'Gagal memperbarui alamat.';
        }
    } else {
        $_SESSION['address_message'] = 'Mohon lengkapi semua field alamat.';
    }
}

// ── 3. Delete address ────────────────────────────────────
elseif ($action === 'delete') {
    $addrId = (int) ($_POST['address_id'] ?? 0);
    if ($addrId > 0) {
        try {
            $own = $pdo->prepare("SELECT id, is_primary FROM addresses WHERE id=? AND user_id=?");
            $own->execute([$addrId, $userId]);
            $row = $own->fetch();
            if ($row) {
                $pdo->prepare("DELETE FROM addresses WHERE id=? AND user_id=?")->execute([$addrId, $userId]);
                // If deleted address was primary, set the next one as primary
                if ($row['is_primary']) {
                    $next = $pdo->prepare("SELECT id FROM addresses WHERE user_id=? ORDER BY id ASC LIMIT 1");
                    $next->execute([$userId]);
                    $nextRow = $next->fetch();
                    if ($nextRow) {
                        $pdo->prepare("UPDATE addresses SET is_primary=1 WHERE id=?")->execute([$nextRow['id']]);
                    }
                }
                $_SESSION['address_message'] = 'Alamat berhasil dihapus.';
            }
        } catch (PDOException $e) {
            error_log('Address delete error: ' . $e->getMessage());
            $_SESSION['address_message'] = 'Gagal menghapus alamat.';
        }
    }
}

// ── 4. Set primary address ────────────────────────────────
elseif ($action === 'set_primary') {
    $addrId = (int) ($_POST['address_id'] ?? 0);
    if ($addrId > 0) {
        try {
            $own = $pdo->prepare("SELECT id FROM addresses WHERE id=? AND user_id=?");
            $own->execute([$addrId, $userId]);
            if ($own->fetch()) {
                $pdo->prepare("UPDATE addresses SET is_primary=0 WHERE user_id=?")->execute([$userId]);
                $pdo->prepare("UPDATE addresses SET is_primary=1 WHERE id=? AND user_id=?")->execute([$addrId, $userId]);
                $_SESSION['address_message'] = 'Alamat utama berhasil diubah.';
            }
        } catch (PDOException $e) {
            error_log('Address set_primary error: ' . $e->getMessage());
        }
    }
}

header('Location: ' . $redirect);
exit;

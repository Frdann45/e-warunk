<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - User Account Dashboard
 * ============================================================
 * File: akun.php
 * Author ID   : 11240044
 * Description : Dedicated User Dashboard with 2-Column Responsive Layout.
 *               Includes Profile Data Editing, Dedicated Photo Cropper Form,
 *               and Embedded "Pesanan Saya" (Order History) with Permanent Sidebar.
 * ============================================================
 */

session_start();

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

// Obtain database connection
try {
    $pdo = getDBConnection();

    // Auto-migrate avatar_url column to users table if missing
    if ($pdo) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_url'")->fetch();
        if (!$checkCol) {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL");
        }
    }
} catch (PDOException $e) {
    // Graceful fallback
}

// Check login status
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int) $_SESSION['user_id'] : null;

// Initialize Flash & Feedback Messages
$profileSuccess  = '';
$profileError    = '';
$passwordSuccess = '';
$passwordError   = '';
$orderSuccess    = '';
$orderError      = '';

// Active Tab: 'profile', 'password', or 'orders'
$activeTab = isset($_GET['tab']) ? trim($_GET['tab']) : 'profile';
if (!in_array($activeTab, ['profile', 'password', 'orders'])) {
    $activeTab = 'profile';
}

// ── Fetch Initial User & Address Data ──────────────────────────────
$userData = null;
$userAddr = null;

if ($isLoggedIn && $pdo) {
    try {
        $stmtU = $pdo->prepare('SELECT id, name, email, avatar_url, created_at FROM users WHERE id = ? LIMIT 1');
        $stmtU->execute([$userId]);
        $userData = $stmtU->fetch();

        $stmtA = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_primary DESC, id DESC LIMIT 1');
        $stmtA->execute([$userId]);
        $userAddr = $stmtA->fetch();
    } catch (PDOException $e) {
        error_log('Initial user fetch error: ' . $e->getMessage());
    }
}

$currentName  = $userData['name'] ?? ($_SESSION['name'] ?? 'Pelanggan');
$rawPhone     = $userAddr['phone'] ?? '';
$rawAddress   = $userAddr['address_line'] ?? '';

// ── Handle Logged In POST Actions (Profile, Password & Orders) ────
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // 1. UPDATE PROFILE & AVATAR
    if ($_POST['action'] === 'update_profile') {
        $activeTab = 'profile';

        if ($pdo) {
            try {
                $avatarSaved = false;

                // A. Cropped Canvas Base64 Photo Upload
                if (!empty($_POST['avatar_cropped_data'])) {
                    $croppedData = $_POST['avatar_cropped_data'];
                    if (strpos($croppedData, ',') !== false) {
                        $parts = explode(',', $croppedData);
                        $imageData = base64_decode($parts[1]);
                        if ($imageData !== false && strlen($imageData) > 0) {
                            $uploadDir = __DIR__ . '/assets/images/avatars/';
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }

                            $newFileName = 'avatar_' . $userId . '_' . time() . '.jpg';
                            $targetPath  = $uploadDir . $newFileName;

                            if (file_put_contents($targetPath, $imageData)) {
                                $avatarPath = 'assets/images/avatars/' . $newFileName;

                                // Delete old avatar file if exists
                                $stmtOld = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ?');
                                $stmtOld->execute([$userId]);
                                $oldRow = $stmtOld->fetch();
                                if (!empty($oldRow['avatar_url']) && file_exists(__DIR__ . '/' . $oldRow['avatar_url'])) {
                                    @unlink(__DIR__ . '/' . $oldRow['avatar_url']);
                                }

                                $stmtAv = $pdo->prepare('UPDATE users SET avatar_url = :avatar WHERE id = :id');
                                $stmtAv->execute([':avatar' => $avatarPath, ':id' => $userId]);
                                $_SESSION['avatar_url'] = $avatarPath;
                                $avatarSaved = true;
                                $profileSuccess = 'Foto profil berhasil diperbarui!';
                            }
                        }
                    }
                }
                
                // B. Direct File Upload Fallback
                if (!$avatarSaved && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp  = $_FILES['avatar']['tmp_name'];
                    $fileName = $_FILES['avatar']['name'];
                    $fileSize = $_FILES['avatar']['size'];
                    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($fileExt, $allowedExts)) {
                        $profileError = 'Format foto tidak didukung. Gunakan format JPG, PNG, atau WEBP.';
                    } elseif ($fileSize > 5 * 1024 * 1024) {
                        $profileError = 'Ukuran foto maksimal 5MB.';
                    } else {
                        $uploadDir = __DIR__ . '/assets/images/avatars/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
                        $targetPath  = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmp, $targetPath)) {
                            $avatarPath = 'assets/images/avatars/' . $newFileName;

                            $stmtOld = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ?');
                            $stmtOld->execute([$userId]);
                            $oldRow = $stmtOld->fetch();
                            if (!empty($oldRow['avatar_url']) && file_exists(__DIR__ . '/' . $oldRow['avatar_url'])) {
                                @unlink(__DIR__ . '/' . $oldRow['avatar_url']);
                            }

                            $stmtAv = $pdo->prepare('UPDATE users SET avatar_url = :avatar WHERE id = :id');
                            $stmtAv->execute([':avatar' => $avatarPath, ':id' => $userId]);
                            $_SESSION['avatar_url'] = $avatarPath;
                            $avatarSaved = true;
                            $profileSuccess = 'Foto profil berhasil diperbarui!';
                        }
                    }
                }

                // C. Profile Text Data Update (Name, Phone, Address)
                if (isset($_POST['name'])) {
                    $rawName    = trim($_POST['name']);
                    $newName    = !empty($rawName) ? $rawName : $currentName;
                    $newPhone   = isset($_POST['phone']) ? trim($_POST['phone']) : $rawPhone;
                    $newAddress = isset($_POST['address']) ? trim($_POST['address']) : $rawAddress;

                    if (empty($newName)) {
                        $profileError = 'Nama lengkap tidak boleh kosong.';
                    } else {
                        // Update users table name
                        $stmt = $pdo->prepare('UPDATE users SET name = :name WHERE id = :id');
                        $stmt->execute([':name' => $newName, ':id' => $userId]);
                        $_SESSION['name'] = $newName;

                        // Update or insert primary address
                        $stmtAddr = $pdo->prepare('SELECT id FROM addresses WHERE user_id = ? ORDER BY is_primary DESC, id DESC LIMIT 1');
                        $stmtAddr->execute([$userId]);
                        $existingAddr = $stmtAddr->fetch();

                        if ($existingAddr) {
                            $stmtUpdateAddr = $pdo->prepare('UPDATE addresses SET recipient_name = :rname, phone = :phone, address_line = :address WHERE id = :id');
                            $stmtUpdateAddr->execute([
                                ':rname'   => $newName,
                                ':phone'   => $newPhone,
                                ':address' => $newAddress,
                                ':id'      => $existingAddr['id']
                            ]);
                        } else {
                            $stmtInsAddr = $pdo->prepare('INSERT INTO addresses (user_id, recipient_name, phone, address_line, city, province, postal_code, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
                            $stmtInsAddr->execute([$userId, $newName, $newPhone, $newAddress, 'Jakarta Pusat', 'DKI Jakarta', '10240']);
                        }

                        if (!$avatarSaved) {
                            $profileSuccess = 'Data profil berhasil diperbarui.';
                        }
                    }
                }

            } catch (PDOException $e) {
                error_log('Update profile error: ' . $e->getMessage());
                $profileError = 'Terjadi kesalahan sistem saat memperbarui profil.';
            }
        }
    }

    // 2. REMOVE AVATAR
    elseif ($_POST['action'] === 'remove_avatar') {
        $activeTab = 'profile';
        if ($pdo) {
            try {
                $stmtOld = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ?');
                $stmtOld->execute([$userId]);
                $oldRow = $stmtOld->fetch();
                if (!empty($oldRow['avatar_url']) && file_exists(__DIR__ . '/' . $oldRow['avatar_url'])) {
                    @unlink(__DIR__ . '/' . $oldRow['avatar_url']);
                }

                $stmtDel = $pdo->prepare('UPDATE users SET avatar_url = NULL WHERE id = ?');
                $stmtDel->execute([$userId]);
                $_SESSION['avatar_url'] = null;

                $profileSuccess = 'Foto profil berhasil dihapus.';
            } catch (PDOException $e) {
                error_log('Remove avatar error: ' . $e->getMessage());
                $profileError = 'Terjadi kesalahan sistem saat menghapus foto.';
            }
        }
    }

    // 3. UPDATE PASSWORD
    elseif ($_POST['action'] === 'update_password') {
        $activeTab       = 'password';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $passwordError = 'Mohon lengkapi seluruh field password.';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = 'Konfirmasi password baru tidak cocok.';
        } elseif (strlen($newPassword) < 6) {
            $passwordError = 'Password baru minimal 6 karakter.';
        } elseif ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $userPassData = $stmt->fetch();

                if ($userPassData && password_verify($currentPassword, $userPassData['password'])) {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmtUp  = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmtUp->execute([$newHash, $userId]);
                    $passwordSuccess = 'Password Anda berhasil diperbarui!';
                } else {
                    $passwordError = 'Password saat ini yang Anda masukkan salah.';
                }
            } catch (PDOException $e) {
                error_log('Update password error: ' . $e->getMessage());
                $passwordError = 'Terjadi kesalahan sistem.';
            }
        }
    }

    // 4. CANCEL ORDER
    elseif ($_POST['action'] === 'cancel_order') {
        $activeTab = 'orders';
        $orderCode = isset($_POST['order_code']) ? trim($_POST['order_code']) : '';
        if (!empty($orderCode) && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_code = ?");
                $stmt->execute([$orderCode]);
                $ord = $stmt->fetch();
                if ($ord && ($ord['status'] === 'Belum Bayar' || $ord['status'] === 'Diproses')) {
                    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'Dibatalkan' WHERE order_code = ?");
                    $stmtUpdate->execute([$orderCode]);
                    $orderSuccess = 'Pesanan #' . htmlspecialchars($orderCode) . ' berhasil dibatalkan.';
                } else {
                    $orderError = 'Pesanan tidak dapat dibatalkan karena telah diproses pengiriman.';
                }
            } catch (PDOException $e) {
                $orderError = 'Terjadi kesalahan sistem saat membatalkan pesanan.';
            }
        }
    }

    // 5. COMPLETE ORDER
    elseif ($_POST['action'] === 'complete_order') {
        $activeTab = 'orders';
        $orderCode = isset($_POST['order_code']) ? trim($_POST['order_code']) : '';
        if (!empty($orderCode) && $pdo) {
            try {
                $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'Selesai' WHERE order_code = ?");
                $stmtUpdate->execute([$orderCode]);
                $orderSuccess = 'Pesanan #' . htmlspecialchars($orderCode) . ' telah selesai. Terima kasih!';
            } catch (PDOException $e) {
                $orderError = 'Terjadi kesalahan sistem saat menyelesaikan pesanan.';
            }
        }
    }
}

// ── Re-fetch User & Address Data after POST ────────────────────────
$orderCounts = [
    'Semua'               => 0,
    'Menunggu Pembayaran' => 0,
    'Diproses'            => 0,
    'Dikirim'             => 0,
    'Selesai'             => 0,
    'Dibatalkan'          => 0,
];

// Order History filtering parameters
$selectedStatus = isset($_GET['status']) ? trim($_GET['status']) : 'Semua';
$orderSearch    = isset($_GET['search']) ? trim($_GET['search']) : '';

$statusTabsMap = [
    'Semua'               => null,
    'Belum Bayar'         => 'Belum Bayar',
    'Menunggu Pembayaran' => 'Belum Bayar',
    'Sedang Dikemas'      => 'Diproses',
    'Diproses'            => 'Diproses',
    'Dikirim'             => 'Dikirim',
    'Selesai'             => 'Selesai',
    'Dibatalkan'          => 'Dibatalkan',
];

$ordersList = [];
$orderItemsMap = [];

if ($isLoggedIn && $pdo) {
    try {
        // Fetch user row
        $stmtU = $pdo->prepare('SELECT id, name, email, avatar_url, created_at FROM users WHERE id = ? LIMIT 1');
        $stmtU->execute([$userId]);
        $userData = $stmtU->fetch();

        if ($userData && isset($userData['avatar_url'])) {
            $_SESSION['avatar_url'] = $userData['avatar_url'];
        }

        // Fetch primary address
        $stmtA = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_primary DESC, id DESC LIMIT 1');
        $stmtA->execute([$userId]);
        $userAddr = $stmtA->fetch();

        // Fetch Order Status Counts
        $stmtOrdersCount = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $statusRows = $stmtOrdersCount->fetchAll();
        $totalOrders = 0;

        foreach ($statusRows as $row) {
            $st  = $row['status'];
            $cnt = (int) $row['count'];
            $totalOrders += $cnt;

            if ($st === 'Belum Bayar') {
                $orderCounts['Menunggu Pembayaran'] += $cnt;
            } elseif ($st === 'Diproses' || $st === 'Sedang Dikemas') {
                $orderCounts['Diproses'] += $cnt;
            } elseif ($st === 'Dikirim') {
                $orderCounts['Dikirim'] += $cnt;
            } elseif ($st === 'Selesai') {
                $orderCounts['Selesai'] += $cnt;
            } elseif ($st === 'Dibatalkan') {
                $orderCounts['Dibatalkan'] += $cnt;
            }
        }
        $orderCounts['Semua'] = $totalOrders;

        // Fetch Orders list for "Pesanan Saya" tab
        $sqlO = "
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count,
                   (SELECT product_name FROM order_items WHERE order_id = o.id ORDER BY id ASC LIMIT 1) AS first_item_name
            FROM orders o
            WHERE 1=1
        ";
        $paramsO = [];
        $dbStatus = isset($statusTabsMap[$selectedStatus]) ? $statusTabsMap[$selectedStatus] : null;

        if ($dbStatus !== null) {
            $sqlO .= " AND o.status = ?";
            $paramsO[] = $dbStatus;
        }

        if ($orderSearch !== '') {
            $sqlO .= " AND (o.order_code LIKE ? OR EXISTS (
                SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.product_name LIKE ?
            ))";
            $paramsO[] = '%' . $orderSearch . '%';
            $paramsO[] = '%' . $orderSearch . '%';
        }

        $sqlO .= " ORDER BY o.order_date DESC, o.id DESC";

        $stmtFetchO = $pdo->prepare($sqlO);
        $stmtFetchO->execute($paramsO);
        $ordersList = $stmtFetchO->fetchAll();

        if (!empty($ordersList)) {
            $orderIds = array_column($ordersList, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY id ASC");
            $stmtItems->execute($orderIds);
            $allItems = $stmtItems->fetchAll();
            foreach ($allItems as $item) {
                $orderItemsMap[$item['order_id']][] = $item;
            }
        }

    } catch (PDOException $e) {
        error_log('Akun.php fetch error: ' . $e->getMessage());
    }
}

// Resolve displayed names, avatar & initials
$displayName  = $userData['name'] ?? ($_SESSION['name'] ?? 'Pelanggan');
$displayEmail = $userData['email'] ?? ($_SESSION['email'] ?? 'user@ewarung.com');
$displayPhone = !empty($userAddr['phone']) ? $userAddr['phone'] : 'Belum diatur';
$rawAddress   = $userAddr['address_line'] ?? '';
$displayAddr  = !empty($rawAddress) ? ($rawAddress . ($userAddr['city'] ? ', ' . $userAddr['city'] : '') . ($userAddr['province'] ? ', ' . $userAddr['province'] : '')) : 'Belum ada alamat utama diset';
$userAvatar   = $userData['avatar_url'] ?? ($_SESSION['avatar_url'] ?? null);

$nameParts    = explode(' ', trim($displayName));
$userInitials = strtoupper(substr($nameParts[0] ?? 'P', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : 'S'));

// Cart count for header
$cartCount = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Order Status Display Helpers
function getStatusColor(string $status): string {
    $map = [
        'Selesai'    => '#16A34A', // Green
        'Diproses'   => '#D97706', // Amber/Orange
        'Dikirim'    => '#0052CC', // Blue
        'Dibatalkan' => '#DC2626', // Red
        'Belum Bayar'=> '#EA580C', // Deep Orange
    ];
    return $map[$status] ?? '#D97706';
}

function getStatusLabel(string $status): string {
    $map = [
        'Diproses'   => 'SEDANG DIKEMAS',
        'Dikirim'    => 'DIKIRIM',
        'Selesai'    => 'SELESAI',
        'Dibatalkan' => 'DIBATALKAN',
        'Belum Bayar' => 'BELUM BAYAR',
    ];
    return $map[$status] ?? strtoupper($status);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Warung Tiga Saudara</title>
    
    <!-- Google Fonts & Main Stylesheet -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ═══════════════════════════════════════════════════════════
           AKUN.PHP STYLES — Professional Blue Theme (#0052CC)
           ═══════════════════════════════════════════════════════════ */
        :root {
            --theme-blue: #0052CC;
            --theme-blue-hover: #0040A8;
            --theme-blue-light: #EBF3FF;
            --theme-bg: #F8FAFC;
            --card-radius: 16px;
            --card-shadow: 0 4px 20px -2px rgba(0, 82, 204, 0.06), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
        }

        body {
            background-color: var(--theme-bg);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #0C1E43;
        }

        .account-page-wrapper {
            max-width: 1200px;
            margin: 32px auto 60px;
            padding: 0 20px;
        }

        /* ── GUEST CARD ─────────────────────────────────────────── */
        .guest-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .guest-card {
            background: #FFFFFF;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px -4px rgba(0, 82, 204, 0.1), 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid #E2E8F0;
            max-width: 480px;
            width: 100%;
            padding: 48px 36px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .guest-card:hover {
            transform: translateY(-2px);
        }

        .guest-icon-box {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: var(--theme-blue-light);
            color: var(--theme-blue);
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(0, 82, 204, 0.15);
        }

        .guest-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0C1E43;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        .guest-description {
            font-size: 0.95rem;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .guest-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-primary-blue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--theme-blue);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 12px 22px;
            border-radius: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.25);
        }

        .btn-primary-blue:hover {
            background: var(--theme-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 82, 204, 0.35);
        }

        .btn-secondary-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: transparent;
            color: var(--theme-blue);
            border: 1.5px solid var(--theme-blue);
            font-weight: 700;
            font-size: 0.95rem;
            padding: 11px 22px;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary-outline:hover {
            background: var(--theme-blue-light);
        }

        .btn-danger-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            color: #EF4444;
            border: none;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-danger-link:hover {
            background: #FEF2F2;
        }

        /* ── LOGGED-IN 2-COLUMN LAYOUT ───────────────────────────── */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 310px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* Left Column: Sidebar */
        .dashboard-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .user-profile-header-card {
            background: #FFFFFF;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid #E2E8F0;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052CC 0%, #003399 100%);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0, 82, 204, 0.3);
            overflow: hidden;
            border: 2px solid var(--theme-blue);
        }

        .user-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-profile-info {
            overflow: hidden;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0C1E43;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-pelanggan-setia {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: var(--theme-blue-light);
            color: var(--theme-blue);
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        /* Sidebar Navigation Menu Card */
        .sidebar-menu-card {
            background: #FFFFFF;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid #E2E8F0;
            padding: 20px 16px;
        }

        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: #94A3B8;
            margin: 16px 12px 8px;
        }

        .nav-section-title:first-child {
            margin-top: 4px;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 10px;
            color: #334155;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .nav-link-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--theme-blue-light);
            color: var(--theme-blue);
        }

        .counter-badge {
            background: #F1F5F9;
            color: #64748B;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .nav-link.active .counter-badge,
        .nav-link:hover .counter-badge {
            background: rgba(0, 82, 204, 0.15);
            color: var(--theme-blue);
        }

        .nav-link-logout {
            color: #EF4444 !important;
        }

        .nav-link-logout:hover {
            background: #FEF2F2 !important;
            color: #DC2626 !important;
        }

        .menu-divider {
            height: 1px;
            background: #F1F5F9;
            margin: 12px 0;
        }

        /* Right Column: Main Content Panel */
        .content-card {
            background: #FFFFFF;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid #E2E8F0;
            padding: 32px;
        }

        .content-card-header {
            margin-bottom: 24px;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-card-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0C1E43;
            letter-spacing: -0.01em;
        }

        .content-card-subtitle {
            font-size: 0.88rem;
            color: #64748B;
            margin-top: 4px;
        }

        /* ── PESANAN SAYA STYLES ───────────────────────────── */
        .order-history-header-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid #F1F5F9;
            overflow-x: auto;
            margin-bottom: 20px;
            padding-bottom: 4px;
        }

        .order-history-tab-link {
            padding: 10px 16px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #64748B;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .order-history-tab-link:hover,
        .order-history-tab-link.active {
            color: var(--theme-blue);
            border-bottom-color: var(--theme-blue);
        }

        .order-search-box {
            position: relative;
            margin-bottom: 24px;
        }

        .order-search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border-radius: 12px;
            border: 1px solid #CBD5E1;
            font-size: 0.9rem;
            outline: none;
        }

        .order-search-input:focus {
            border-color: var(--theme-blue);
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1);
        }

        .order-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94A3B8;
        }

        .order-item-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .order-item-card:hover {
            box-shadow: 0 6px 18px rgba(0, 82, 204, 0.08);
            border-color: #CBD5E1;
        }

        .order-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 1px solid #F1F5F9;
            margin-bottom: 16px;
        }

        .order-store-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            color: #0C1E43;
        }

        .order-code-badge {
            color: #64748B;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .order-status-badge {
            font-size: 0.8rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .order-product-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 8px 0;
        }

        .order-product-thumb {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #E2E8F0;
            flex-shrink: 0;
            background: #F8FAFC;
        }

        .order-product-details {
            flex: 1;
        }

        .order-product-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0C1E43;
            margin-bottom: 4px;
        }

        .order-product-qty {
            font-size: 0.82rem;
            color: #64748B;
            font-weight: 600;
        }

        .order-product-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0C1E43;
        }

        .order-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            margin-top: 14px;
            border-top: 1px dashed #F1F5F9;
            flex-wrap: wrap;
            gap: 12px;
        }

        .order-total-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--theme-blue);
        }

        .order-action-btns {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Profile Avatar Upload Header Box */
        .profile-avatar-upload-box {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 20px;
            background: #F8FAFC;
            border-radius: 14px;
            border: 1px solid #E2E8F0;
            margin-bottom: 28px;
        }

        .avatar-large-wrapper {
            position: relative;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052CC 0%, #003399 100%);
            color: #FFFFFF;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0, 82, 204, 0.25);
            border: 3px solid #FFFFFF;
            overflow: hidden;
        }

        .avatar-large-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload-controls {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .avatar-upload-hint {
            font-size: 0.8rem;
            color: #64748B;
        }

        /* Profile Data Fields List */
        .profile-data-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-field-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #F1F5F9;
        }

        .profile-field-row:last-child {
            border-bottom: none;
        }

        .field-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748B;
        }

        .field-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0C1E43;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: #16A34A;
            background: #DCFCE7;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
        }

        /* Forms Styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #0C1E43;
            background: #FFFFFF;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--theme-blue);
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.12);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        /* Alerts */
        .alert-banner {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #EFF6FF;
            border-left: 4px solid var(--theme-blue);
            color: #1E40AF;
        }

        .alert-danger {
            background: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #991B1B;
        }

        /* Tab Switcher Logic */
        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* ── CIRCULAR PHOTO CROPPER MODAL ───────────────────────── */
        .crop-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(12, 30, 67, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .crop-modal-card {
            background: #FFFFFF;
            border-radius: 20px;
            max-width: 480px;
            width: 100%;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: modalPop 0.25s ease-out;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .crop-modal-header {
            margin-bottom: 20px;
        }

        .crop-modal-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0C1E43;
        }

        .crop-modal-subtitle {
            font-size: 0.85rem;
            color: #64748B;
            margin-top: 4px;
        }

        .crop-canvas-wrapper {
            position: relative;
            width: 260px;
            height: 260px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--theme-blue);
            box-shadow: 0 8px 24px rgba(0, 82, 204, 0.25);
            cursor: grab;
            background: #F1F5F9;
        }

        .crop-canvas-wrapper:active {
            cursor: grabbing;
        }

        .crop-canvas-wrapper canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .zoom-control-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
            padding: 0 16px;
        }

        .zoom-control-bar input[type=range] {
            flex: 1;
            accent-color: var(--theme-blue);
            cursor: pointer;
        }

        .zoom-icon-btn {
            background: #F1F5F9;
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #334155;
            cursor: pointer;
        }

        .zoom-icon-btn:hover {
            background: var(--theme-blue-light);
            color: var(--theme-blue);
        }

        .crop-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        /* Responsive Layout Adjustments */
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .content-card {
                padding: 24px 20px;
            }

            .profile-field-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .profile-avatar-upload-box {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- ── USER HEADER INCLUSION ──────────────────────────────── -->
    <?php include __DIR__ . '/includes/header_user.php'; ?>

    <main class="account-page-wrapper">

        <?php if (!$isLoggedIn): ?>
        <!-- ═══════════════════════════════════════════════════════
             STATE A: GUEST / NOT LOGGED IN
             ═══════════════════════════════════════════════════════ -->
        <div class="guest-container">
            <div class="guest-card">
                <div class="guest-icon-box">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h1 class="guest-title">Belum Masuk ke Akun</h1>
                <p class="guest-description">
                    Silakan login untuk melihat riwayat pesanan, memantau pengiriman, dan mengelola informasi data diri Anda.
                </p>
                <div class="guest-actions">
                    <a href="login.php" class="btn-primary-blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Masuk / Login
                    </a>
                    <a href="login.php?tab=register" class="btn-secondary-outline">
                        Daftar Akun Baru
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ═══════════════════════════════════════════════════════
             STATE B: LOGGED IN USER DASHBOARD (2-COLUMN)
             ═══════════════════════════════════════════════════════ -->
        <div class="dashboard-grid">

            <!-- ── LEFT COLUMN: SIDEBAR MENU (PERMANENTLY VISIBLE) ─────────────── -->
            <aside class="dashboard-sidebar">
                
                <!-- Profile Header Card -->
                <div class="user-profile-header-card">
                    <div class="user-avatar-circle">
                        <?php if (!empty($userAvatar) && file_exists(__DIR__ . '/' . $userAvatar)): ?>
                            <img src="<?= htmlspecialchars($userAvatar) ?>?v=<?= time() ?>" alt="Foto Profil">
                        <?php else: ?>
                            <?= $userInitials ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-profile-info">
                        <h2 class="user-name"><?= htmlspecialchars($displayName) ?></h2>
                        <div class="badge-pelanggan-setia">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Pelanggan Setia
                        </div>
                    </div>
                </div>

                <!-- Navigation Section Card -->
                <nav class="sidebar-menu-card">

                    <!-- SECTION 1: PESANAN SAYA -->
                    <div class="nav-section-title">Pesanan Saya</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="akun.php?tab=orders" class="nav-link <?= ($activeTab === 'orders' && ($selectedStatus === 'Semua' || empty($selectedStatus))) ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    Semua
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Semua'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=orders&status=Belum+Bayar" class="nav-link <?= ($activeTab === 'orders' && $selectedStatus === 'Belum Bayar') ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Menunggu Pembayaran
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Menunggu Pembayaran'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=orders&status=Sedang+Dikemas" class="nav-link <?= ($activeTab === 'orders' && ($selectedStatus === 'Sedang Dikemas' || $selectedStatus === 'Diproses')) ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                    </svg>
                                    Diproses
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Diproses'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=orders&status=Dikirim" class="nav-link <?= ($activeTab === 'orders' && $selectedStatus === 'Dikirim') ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                        <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                                    </svg>
                                    Dikirim
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Dikirim'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=orders&status=Selesai" class="nav-link <?= ($activeTab === 'orders' && $selectedStatus === 'Selesai') ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                    Selesai
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Selesai'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=orders&status=Dibatalkan" class="nav-link <?= ($activeTab === 'orders' && $selectedStatus === 'Dibatalkan') ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    Dibatalkan
                                </span>
                                <span class="counter-badge"><?= $orderCounts['Dibatalkan'] ?></span>
                            </a>
                        </li>
                    </ul>

                    <div class="menu-divider"></div>

                    <!-- SECTION 2: PENGATURAN AKUN -->
                    <div class="nav-section-title">Pengaturan Akun</div>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="akun.php?tab=profile" class="nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Profil & Alamat
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="akun.php?tab=password" class="nav-link <?= $activeTab === 'password' ? 'active' : '' ?>">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                    </svg>
                                    Ubah Password
                                </span>
                            </a>
                        </li>
                    </ul>

                    <div class="menu-divider"></div>

                    <!-- SECTION 3: KELUAR -->
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link nav-link-logout">
                                <span class="nav-link-content">
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    Keluar
                                </span>
                            </a>
                        </li>
                    </ul>

                </nav>
            </aside>

            <!-- ── RIGHT COLUMN: MAIN CONTENT PANEL (70%) ─────── -->
            <section class="dashboard-main-content">

                <!-- ╔══════════════════════════════════════════╗ -->
                <!-- ║ TAB 1: DATA DIRI & KONTAK                ║ -->
                <!-- ╚══════════════════════════════════════════╝ -->
                <div id="tab-profile" class="tab-panel <?= $activeTab === 'profile' ? 'active' : '' ?>">
                    <div class="content-card">
                        
                        <div class="content-card-header">
                            <div>
                                <h1 class="content-card-title">Data Diri & Kontak</h1>
                                <p class="content-card-subtitle">Kelola informasi profil Anda untuk keamanan dan kemudahan transaksi.</p>
                            </div>
                            <button type="button" class="btn-primary-blue" id="btn-toggle-edit" onclick="toggleEditProfile()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit Profil
                            </button>
                        </div>

                        <?php if (!empty($profileSuccess)): ?>
                            <div class="alert-banner alert-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= htmlspecialchars($profileSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($profileError)): ?>
                            <div class="alert-banner alert-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <?= htmlspecialchars($profileError) ?>
                            </div>
                        <?php endif; ?>

                        <!-- 1. DEDICATED AVATAR UPLOAD FORM -->
                        <form id="avatar-upload-form" action="akun.php?tab=profile" method="POST" enctype="multipart/form-data" style="display: none;">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="avatar_cropped_data" id="avatar_cropped_data" value="">
                            <input type="file" name="avatar" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" onchange="handleFileSelected(this)">
                        </form>

                        <!-- PROFILE AVATAR DISPLAY & UPLOAD HEADER BOX -->
                        <div class="profile-avatar-upload-box">
                            <div class="avatar-large-wrapper" id="avatar-preview-container">
                                <?php if (!empty($userAvatar) && file_exists(__DIR__ . '/' . $userAvatar)): ?>
                                    <img src="<?= htmlspecialchars($userAvatar) ?>?v=<?= time() ?>" id="avatar-preview-img" alt="Foto Profil">
                                <?php else: ?>
                                    <span id="avatar-initials-text"><?= $userInitials ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="avatar-upload-controls">
                                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                    
                                    <!-- NATIVE HTML FILE LABEL (Guaranteed to trigger file picker) -->
                                    <label for="avatar-file-input" class="btn-secondary-outline" style="padding: 8px 16px; font-size: 0.88rem; cursor: pointer; margin: 0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                        Pilih & Atur Foto Profil
                                    </label>

                                    <?php if (!empty($userAvatar)): ?>
                                        <form action="akun.php?tab=profile" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus foto profil?');">
                                            <input type="hidden" name="action" value="remove_avatar">
                                            <button type="submit" class="btn-danger-link">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                Hapus Foto
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <span class="avatar-upload-hint">Tekan tombol di atas untuk memilih foto, memotong, dan menyesuaikan ukuran bulatan.</span>
                            </div>
                        </div>

                        <!-- DISPLAY MODE (Shown by default) -->
                        <div id="profile-display-mode" class="profile-data-list">
                            <div class="profile-field-row">
                                <span class="field-label">Nama Lengkap</span>
                                <span class="field-value"><?= htmlspecialchars($displayName) ?></span>
                            </div>
                            <div class="profile-field-row">
                                <span class="field-label">Nomor WhatsApp / HP</span>
                                <span class="field-value"><?= htmlspecialchars($displayPhone) ?></span>
                            </div>
                            <div class="profile-field-row">
                                <span class="field-label">Alamat E-mail</span>
                                <span class="field-value">
                                    <?= htmlspecialchars($displayEmail) ?>
                                    <span class="verified-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        Sudah diverifikasi
                                    </span>
                                </span>
                            </div>
                            <div class="profile-field-row">
                                <span class="field-label">Alamat Utama</span>
                                <span class="field-value" style="font-weight: 500; line-height: 1.5;"><?= nl2br(htmlspecialchars($displayAddr)) ?></span>
                            </div>
                        </div>

                        <!-- 2. DEDICATED PROFILE TEXT DATA EDIT FORM (Hidden by default, toggled via Edit Profil button) -->
                        <form id="profile-edit-form" action="akun.php?tab=profile" method="POST" style="display: none; margin-top: 10px;">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" id="input_profile_name" class="form-control" value="<?= htmlspecialchars($displayName) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nomor WhatsApp / HP</label>
                                <input type="text" name="phone" id="input_profile_phone" class="form-control" value="<?= htmlspecialchars($rawPhone) ?>" placeholder="Masukkan nomor HP/WhatsApp">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Alamat Lengkap Utama</label>
                                <textarea name="address" id="input_profile_address" class="form-control" rows="3" placeholder="Masukkan alamat lengkap pengiriman"><?= htmlspecialchars($rawAddress) ?></textarea>
                            </div>

                            <div style="display: flex; gap: 12px; margin-top: 24px;">
                                <button type="submit" class="btn-primary-blue">Simpan Perubahan</button>
                                <button type="button" class="btn-secondary-outline" onclick="toggleEditProfile()">Batal</button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- ╔══════════════════════════════════════════╗ -->
                <!-- ║ TAB 2: UBAH PASSWORD                     ║ -->
                <!-- ╚══════════════════════════════════════════╝ -->
                <div id="tab-password" class="tab-panel <?= $activeTab === 'password' ? 'active' : '' ?>">
                    <div class="content-card">
                        
                        <div class="content-card-header">
                            <div>
                                <h1 class="content-card-title">Ubah Password</h1>
                                <p class="content-card-subtitle">Demi keamanan akun Anda, mohon tidak memberikan kata sandi kepada siapapun.</p>
                            </div>
                        </div>

                        <?php if (!empty($passwordSuccess)): ?>
                            <div class="alert-banner alert-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= htmlspecialchars($passwordSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($passwordError)): ?>
                            <div class="alert-banner alert-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <?= htmlspecialchars($passwordError) ?>
                            </div>
                        <?php endif; ?>

                        <form action="akun.php?tab=password" method="POST" style="max-width: 520px;">
                            <input type="hidden" name="action" value="update_password">

                            <div class="form-group">
                                <label class="form-label">Kata Sandi Saat Ini</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Masukkan password saat ini" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kata Sandi Baru</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi kata sandi baru" required minlength="6">
                            </div>

                            <div style="margin-top: 28px;">
                                <button type="submit" class="btn-primary-blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                    </svg>
                                    Simpan Password Baru
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- ╔══════════════════════════════════════════╗ -->
                <!-- ║ TAB 3: PESANAN SAYA (ORDER HISTORY)      ║ -->
                <!-- ╚══════════════════════════════════════════╝ -->
                <div id="tab-orders" class="tab-panel <?= $activeTab === 'orders' ? 'active' : '' ?>">
                    <div class="content-card">
                        
                        <div class="content-card-header">
                            <div>
                                <h1 class="content-card-title">Pesanan Saya</h1>
                                <p class="content-card-subtitle">Pantau status transaksi, pengiriman, dan riwayat pesanan Anda.</p>
                            </div>
                        </div>

                        <?php if (!empty($orderSuccess)): ?>
                            <div class="alert-banner alert-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= htmlspecialchars($orderSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($orderError)): ?>
                            <div class="alert-banner alert-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <?= htmlspecialchars($orderError) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Status Filter Tabs Bar -->
                        <div class="order-history-header-tabs">
                            <?php
                            $filterTabOptions = [
                                'Semua'          => 'Semua',
                                'Belum Bayar'    => 'Menunggu Pembayaran',
                                'Sedang Dikemas' => 'Diproses',
                                'Dikirim'        => 'Dikirim',
                                'Selesai'        => 'Selesai',
                                'Dibatalkan'     => 'Dibatalkan',
                            ];
                            foreach ($filterTabOptions as $keyStatus => $labelTab):
                                $isActiveFilter = ($selectedStatus === $keyStatus || ($keyStatus === 'Semua' && empty($selectedStatus)));
                            ?>
                                <a href="akun.php?tab=orders&status=<?= urlencode($keyStatus) ?><?= !empty($orderSearch) ? '&search=' . urlencode($orderSearch) : '' ?>"
                                   class="order-history-tab-link <?= $isActiveFilter ? 'active' : '' ?>">
                                    <?= htmlspecialchars($labelTab) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Search Box -->
                        <div class="order-search-box">
                            <form action="akun.php" method="GET">
                                <input type="hidden" name="tab" value="orders">
                                <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus) ?>">
                                <svg class="order-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                <input type="text" name="search" class="order-search-input" placeholder="Cari berdasarkan No. Pesanan atau Nama Produk..." value="<?= htmlspecialchars($orderSearch) ?>">
                            </form>
                        </div>

                        <!-- Order Cards List -->
                        <?php if (!empty($ordersList)): ?>
                            <?php foreach ($ordersList as $ord): ?>
                                <?php
                                $items = isset($orderItemsMap[$ord['id']]) ? $orderItemsMap[$ord['id']] : [];
                                $statusColor = getStatusColor($ord['status']);
                                $statusTxt   = getStatusLabel($ord['status']);
                                $dateObj     = new DateTime($ord['order_date']);
                                $formattedDt = $dateObj->format('d M Y, H:i');
                                ?>
                                <div class="order-item-card">
                                    <!-- Top Bar: Store Name & Status Badge -->
                                    <div class="order-card-top">
                                        <div class="order-store-info">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <polyline points="9 22 9 12 15 12 15 22"/>
                                            </svg>
                                            <span>Warung Tiga Saudara</span>
                                            <span class="order-code-badge">#<?= htmlspecialchars($ord['order_code']) ?></span>
                                        </div>
                                        <div class="order-status-badge" style="color: <?= $statusColor ?>; background: <?= $statusColor ?>15;">
                                            <?= $statusTxt ?>
                                        </div>
                                    </div>

                                    <!-- Product List -->
                                    <div class="order-card-middle">
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $it): ?>
                                                <div class="order-product-row">
                                                    <img src="<?= htmlspecialchars(getProductImage($it['product_name'])) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>" class="order-product-thumb">
                                                    <div class="order-product-details">
                                                        <h4 class="order-product-title"><?= htmlspecialchars($it['product_name']) ?></h4>
                                                        <span class="order-product-qty">Jumlah: <?= (int)$it['quantity'] ?> item</span>
                                                    </div>
                                                    <div class="order-product-price">
                                                        <?= formatRupiah((float)$it['price']) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="order-product-row">
                                                <div class="order-product-details">
                                                    <h4 class="order-product-title"><?= htmlspecialchars($ord['first_item_name'] ?? 'Pesanan Produk') ?></h4>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Bottom Bar: Date, Total & Actions -->
                                    <div class="order-card-bottom">
                                        <div>
                                            <div style="font-size: 0.8rem; color: #64748B;"><?= $formattedDt ?></div>
                                            <div class="order-total-price"><?= formatRupiah((float)$ord['total_price']) ?></div>
                                        </div>

                                        <div class="order-action-btns">
                                            <?php if ($ord['status'] === 'Belum Bayar'): ?>
                                                <?php if (!empty($ord['payment_url'])): ?>
                                                    <a href="<?= htmlspecialchars($ord['payment_url']) ?>" class="btn-primary-blue" style="padding: 8px 16px; font-size: 0.85rem;" target="_blank">Bayar Sekarang</a>
                                                <?php endif; ?>
                                                <form action="akun.php?tab=orders" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                                    <input type="hidden" name="action" value="cancel_order">
                                                    <input type="hidden" name="order_code" value="<?= htmlspecialchars($ord['order_code']) ?>">
                                                    <button type="submit" class="btn-danger-link">Batalkan Pesanan</button>
                                                </form>
                                            <?php elseif ($ord['status'] === 'Diproses'): ?>
                                                <form action="akun.php?tab=orders" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                                    <input type="hidden" name="action" value="cancel_order">
                                                    <input type="hidden" name="order_code" value="<?= htmlspecialchars($ord['order_code']) ?>">
                                                    <button type="submit" class="btn-danger-link">Batalkan Pesanan</button>
                                                </form>
                                            <?php elseif ($ord['status'] === 'Dikirim'): ?>
                                                <form action="akun.php?tab=orders" method="POST" style="display: inline;" onsubmit="return confirm('Konfirmasi bahwa Anda telah menerima pesanan ini?');">
                                                    <input type="hidden" name="action" value="complete_order">
                                                    <input type="hidden" name="order_code" value="<?= htmlspecialchars($ord['order_code']) ?>">
                                                    <button type="submit" class="btn-primary-blue" style="padding: 8px 16px; font-size: 0.85rem;">Pesanan Selesai</button>
                                                </form>
                                            <?php elseif ($ord['status'] === 'Selesai'): ?>
                                                <a href="index.php?page=sembako" class="btn-primary-blue" style="padding: 8px 16px; font-size: 0.85rem;">Beli Lagi</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty Orders State -->
                            <div style="text-align: center; padding: 48px 20px; background: #F8FAFC; border-radius: 14px; border: 1px dashed #CBD5E1;">
                                <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 12px;">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0C1E43; margin-bottom: 6px;">Belum Ada Pesanan</h3>
                                <p style="font-size: 0.88rem; color: #64748B; margin-bottom: 20px;">Tidak ada transaksi yang cocok dengan kriteria filter Anda saat ini.</p>
                                <a href="index.php?page=sembako" class="btn-primary-blue">Mulai Belanja Now</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </section>

        </div>
        <?php endif; ?>

    </main>

    <!-- ── INTERACTIVE PHOTO CROPPER MODAL ───────────────────── -->
    <div class="crop-modal-overlay" id="crop-modal-overlay">
        <div class="crop-modal-card">
            <div class="crop-modal-header">
                <h3 class="crop-modal-title">Sesuaikan Foto Profil</h3>
                <p class="crop-modal-subtitle">Geser gambar untuk mengatur posisi dan gunakan slider untuk memperbesar / memperkecil.</p>
            </div>

            <div class="crop-canvas-wrapper" id="crop-canvas-wrapper">
                <canvas id="crop-canvas"></canvas>
            </div>

            <div class="zoom-control-bar">
                <button type="button" class="zoom-icon-btn" onclick="adjustZoom(-0.15)">−</button>
                <input type="range" id="zoom-range" min="0.5" max="3" step="0.02" value="1" oninput="onZoomSliderChange(this.value)">
                <button type="button" class="zoom-icon-btn" onclick="adjustZoom(0.15)">+</button>
            </div>

            <div class="crop-modal-actions">
                <button type="button" class="btn-primary-blue" onclick="confirmCropAndSave()">Gunakan Foto Ini</button>
                <button type="button" class="btn-secondary-outline" onclick="closeCropModal()">Batal</button>
            </div>
        </div>
    </div>

    <!-- ── MAIN FOOTER INCLUSION ──────────────────────────────── -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- ── TAB & INTERACTIVE CROPPER JAVASCRIPT ─────────────── -->
    <script>
        function toggleEditProfile() {
            const displayMode = document.getElementById('profile-display-mode');
            const editForm = document.getElementById('profile-edit-form');
            const toggleBtn = document.getElementById('btn-toggle-edit');

            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                displayMode.style.display = 'none';
                toggleBtn.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                displayMode.style.display = 'flex';
                toggleBtn.style.display = 'inline-flex';
            }
        }

        // ═══════════════════════════════════════════════════════════
        // INTERACTIVE CIRCULAR PHOTO CROPPER & RESIZER ENGINE
        // ═══════════════════════════════════════════════════════════
        let cropImage = new Image();
        let cropScale = 1;
        let minScale = 0.5;
        let maxScale = 3;
        let cropPosX = 0;
        let cropPosY = 0;
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        const canvasSize = 260;

        function handleFileSelected(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropImage = new Image();
                    cropImage.onload = function() {
                        const scaleW = canvasSize / cropImage.width;
                        const scaleH = canvasSize / cropImage.height;
                        cropScale = Math.max(scaleW, scaleH);
                        minScale = cropScale * 0.7;
                        maxScale = cropScale * 3.5;

                        cropPosX = (canvasSize - cropImage.width * cropScale) / 2;
                        cropPosY = (canvasSize - cropImage.height * cropScale) / 2;

                        const zoomInput = document.getElementById('zoom-range');
                        zoomInput.min = minScale;
                        zoomInput.max = maxScale;
                        zoomInput.value = cropScale;
                        zoomInput.step = (maxScale - minScale) / 100;

                        drawCropCanvas();
                        openCropModal();
                    };
                    cropImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        function drawCropCanvas() {
            const canvas = document.getElementById('crop-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            canvas.width = canvasSize;
            canvas.height = canvasSize;

            ctx.clearRect(0, 0, canvasSize, canvasSize);

            ctx.save();
            ctx.beginPath();
            ctx.arc(canvasSize / 2, canvasSize / 2, canvasSize / 2, 0, Math.PI * 2);
            ctx.closePath();
            ctx.clip();

            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvasSize, canvasSize);

            ctx.drawImage(cropImage, cropPosX, cropPosY, cropImage.width * cropScale, cropImage.height * cropScale);
            ctx.restore();
        }

        // Mouse Drag Controls
        const wrapper = document.getElementById('crop-canvas-wrapper');
        if (wrapper) {
            wrapper.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX - cropPosX;
                startY = e.clientY - cropPosY;
            });

            window.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    cropPosX = e.clientX - startX;
                    cropPosY = e.clientY - startY;
                    drawCropCanvas();
                }
            });

            window.addEventListener('mouseup', function() {
                isDragging = false;
            });

            // Touch Drag Controls for Mobile
            wrapper.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1) {
                    isDragging = true;
                    startX = e.touches[0].clientX - cropPosX;
                    startY = e.touches[0].clientY - cropPosY;
                }
            });

            window.addEventListener('touchmove', function(e) {
                if (isDragging && e.touches.length === 1) {
                    cropPosX = e.touches[0].clientX - startX;
                    cropPosY = e.touches[0].clientY - startY;
                    drawCropCanvas();
                }
            });

            window.addEventListener('touchend', function() {
                isDragging = false;
            });
        }

        function onZoomSliderChange(val) {
            const newScale = parseFloat(val);
            const center = canvasSize / 2;

            const imageX = (center - cropPosX) / cropScale;
            const imageY = (center - cropPosY) / cropScale;

            cropScale = newScale;
            cropPosX = center - imageX * cropScale;
            cropPosY = center - imageY * cropScale;

            drawCropCanvas();
        }

        function adjustZoom(delta) {
            const zoomInput = document.getElementById('zoom-range');
            let currentVal = parseFloat(zoomInput.value);
            let newVal = Math.min(Math.max(currentVal + delta, minScale), maxScale);
            zoomInput.value = newVal;
            onZoomSliderChange(newVal);
        }

        function openCropModal() {
            document.getElementById('crop-modal-overlay').style.display = 'flex';
        }

        function closeCropModal() {
            document.getElementById('crop-modal-overlay').style.display = 'none';
        }

        function confirmCropAndSave() {
            const canvas = document.getElementById('crop-canvas');
            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

            document.getElementById('avatar_cropped_data').value = dataUrl;

            const container = document.getElementById('avatar-preview-container');
            if (container) {
                container.innerHTML = '<img src="' + dataUrl + '" alt="Preview Foto" id="avatar-preview-img">';
            }

            closeCropModal();

            // Submit dedicated avatar upload form
            const avatarForm = document.getElementById('avatar-upload-form');
            if (avatarForm) {
                avatarForm.submit();
            }
        }
    </script>
</body>
</html>
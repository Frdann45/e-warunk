<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Transaction Data Export
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Handles exporting order data to Excel (.xls) and SQL formats.
 * ============================================================
 */

session_start();
require_once dirname(__DIR__) . '/config/db_connect.php';

// Auth Guard: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('Akses ditolak. Hanya Administrator yang dapat mengakses fitur ini.');
}

$format = isset($_GET['format']) ? $_GET['format'] : '';

if ($format === 'excel') {
    // ── Excel Export (.xls) ──
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_transaksi_' . date('Ymd_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Fetch all orders
    try {
        $stmt = $pdo->query("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count,
                   (SELECT product_name FROM order_items WHERE order_id = o.id ORDER BY id ASC LIMIT 1) AS first_item_name
            FROM orders o
            ORDER BY o.order_date DESC
        ");
        $orders = $stmt->fetchAll();

        // Fetch users map for email lookup
        $userStmt = $pdo->query("SELECT name, email FROM users");
        $usersList = $userStmt->fetchAll();
        $userEmails = [];
        foreach ($usersList as $u) {
            $userEmails[strtolower(trim($u['name']))] = $u['email'];
        }
    } catch (PDOException $e) {
        error_log('Excel export database error: ' . $e->getMessage());
        die('Gagal mengambil data transaksi untuk ekspor.');
    }
    
    // Output HTML Table structured for Excel
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<h2>Laporan Transaksi e-warung (Warung Tiga Saudara)</h2>';
    echo '<p>Tanggal Cetak: ' . date('d-m-Y H:i:s') . '</p>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr style="background-color:#0B2D72; color:#FFFFFF; font-weight:bold; font-family:sans-serif;">';
    echo '<th>ORDER ID</th>';
    echo '<th>TANGGAL</th>';
    echo '<th>NAMA PELANGGAN</th>';
    echo '<th>EMAIL</th>';
    echo '<th>BARANG UTAMA</th>';
    echo '<th>TOTAL HARGA (Rp)</th>';
    echo '<th>STATUS AKTIF</th>';
    echo '</tr>';
    
    foreach ($orders as $order) {
        $mainItemText = htmlspecialchars($order['first_item_name'] ?? '');
        $extraItemsCount = (int) ($order['item_count'] ?? 0) - 1;
        if ($extraItemsCount > 0) {
            $mainItemText .= " (+{$extraItemsCount} item lainnya)";
        }
        
        $dateTime = new DateTime($order['order_date'] ?? 'now');
        $formattedDate = $dateTime->format('d-m-Y H:i');
        
        $addressLines = explode("\n", $order['shipping_address'] ?? '');
        $customerName = isset($addressLines[0]) ? trim($addressLines[0]) : '';
        $customerEmail = isset($userEmails[strtolower($customerName)]) ? $userEmails[strtolower($customerName)] : '-';
        if ($customerName === '') {
            $customerName = 'Pelanggan';
        }
        
        echo '<tr style="font-family:sans-serif; font-size:10pt;">';
        echo '<td style="mso-number-format:\'\@\';">#' . htmlspecialchars($order['order_code'] ?? '') . '</td>';
        echo '<td>' . $formattedDate . '</td>';
        echo '<td>' . htmlspecialchars($customerName) . '</td>';
        echo '<td>' . htmlspecialchars($customerEmail) . '</td>';
        echo '<td>' . $mainItemText . '</td>';
        echo '<td align="right">' . (float) ($order['total_price'] ?? 0) . '</td>';
        echo '<td>' . htmlspecialchars($order['status'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
}

elseif ($format === 'sql') {
    // ── SQL Export (.sql) ──
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="dump_transaksi_' . date('Ymd_His') . '.sql"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    try {
        $ordersStmt = $pdo->query("SELECT * FROM orders ORDER BY id ASC");
        $orders = $ordersStmt->fetchAll();
        
        $itemsStmt = $pdo->query("SELECT * FROM order_items ORDER BY id ASC");
        $items = $itemsStmt->fetchAll();
    } catch (PDOException $e) {
        error_log('SQL export database error: ' . $e->getMessage());
        die('-- Gagal mengekspor skrip SQL.');
    }
    
    echo "-- ============================================================\n";
    echo "-- E-WARUNG (Warung Tiga Saudara) SQL Data Dump\n";
    echo "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
    echo "-- ============================================================\n\n";
    
    echo "--\n";
    echo "-- Dumping data untuk tabel `orders`\n";
    echo "--\n\n";
    
    foreach ($orders as $o) {
        $fields = [];
        $values = [];
        foreach ($o as $key => $val) {
            $fields[] = "`$key`";
            if ($val === null) {
                $values[] = "NULL";
            } else {
                $values[] = $pdo->quote($val);
            }
        }
        echo "INSERT INTO `orders` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ");\n";
    }
    
    echo "\n\n--\n";
    echo "-- Dumping data untuk tabel `order_items`\n";
    echo "--\n\n";
    
    foreach ($items as $i) {
        $fields = [];
        $values = [];
        foreach ($i as $key => $val) {
            $fields[] = "`$key`";
            if ($val === null) {
                $values[] = "NULL";
            } else {
                $values[] = $pdo->quote($val);
            }
        }
        echo "INSERT INTO `order_items` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ");\n";
    }
    exit;
}

else {
    http_response_code(400);
    die('Format ekspor tidak didukung.');
}

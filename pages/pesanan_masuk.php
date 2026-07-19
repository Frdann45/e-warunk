<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Pesanan Masuk (Admin View)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Description : View for managing new incoming orders.
 *               Allows admin to mark orders as shipped (Dikirim)
 *               or cancelled (Dibatalkan).
 * ============================================================
 */

require_once dirname(__DIR__) . '/config/db_connect.php';

// ── Handle Action POST early ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $newStatus = isset($_POST['status']) ? $_POST['status'] : '';

    if ($orderId > 0 && in_array($newStatus, ['Dikirim', 'Dibatalkan'])) {
        try {
            // Retrieve order code first for the flash message
            $stmtCode = $pdo->prepare("SELECT order_code FROM orders WHERE id = ?");
            $stmtCode->execute([$orderId]);
            $orderCode = $stmtCode->fetchColumn();

            if ($orderCode) {
                $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmtUpdate->execute([$newStatus, $orderId]);
                $_SESSION['cart_message'] = "Pesanan #{$orderCode} berhasil dipindahkan ke " . ($newStatus === 'Dikirim' ? 'Proses Pengiriman' : 'Dibatalkan') . ".";
            }
        } catch (PDOException $e) {
            error_log('Error updating order status: ' . $e->getMessage());
            $_SESSION['cart_message'] = "Gagal memperbarui status pesanan.";
        }
    }
    header('Location: ' . BASE_URL . 'admin/admin.php?page=pesanan-masuk');
    exit;
}

// ── Fetch orders with status = 'Diproses' ─────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
        FROM orders o
        WHERE o.status = 'Diproses'
        ORDER BY o.order_date DESC, o.id DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll();

    // Fetch details/items for each order
    foreach ($orders as &$order) {
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order['id']]);
        $order['items'] = $stmtItems->fetchAll();
    }
    unset($order); // break reference

} catch (PDOException $e) {
    error_log('Error fetching incoming orders: ' . $e->getMessage());
    $orders = [];
}
?>

<div class="history-header fade-in">
    <h1 class="history-header__title">Pesanan Masuk</h1>
    <p class="history-header__desc">Berikut adalah pesanan baru dari pelanggan yang perlu diproses dan dikirim.</p>
</div>

<div class="table-container fade-in" style="margin-top: 20px;">
    <table class="history-table" style="width: 100%;">
        <thead>
            <tr>
                <th>ORDER ID &amp; TANGGAL</th>
                <th>PELANGGAN &amp; ALAMAT</th>
                <th>ITEM PESANAN</th>
                <th>METODE &amp; TOTAL</th>
                <th>STATUS</th>
                <th style="text-align: right; padding-right: 20px;">TINDAKAN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                    $dateTime = new DateTime($order['order_date']);
                    $formattedDate = $dateTime->format('d M Y, H:i');
                    
                    // Parse address details
                    $addressLines = explode("\n", $order['shipping_address']);
                    $customerName = htmlspecialchars($addressLines[0] ?? 'Pelanggan');
                    $customerPhone = htmlspecialchars($addressLines[1] ?? '-');
                    $fullAddress = htmlspecialchars(implode(', ', array_slice($addressLines, 2)));
                    ?>
                    <tr>
                        <td style="vertical-align: top; padding-top: 16px;">
                            <span class="td-order-code" style="display: block; font-weight: 700;">#<?= htmlspecialchars($order['order_code']) ?></span>
                            <span style="font-size: 0.75rem; color: var(--color-text-light);"><?= $formattedDate ?></span>
                        </td>
                        <td style="vertical-align: top; padding-top: 16px; max-width: 280px; white-space: normal;">
                            <strong style="color: var(--color-text-primary);"><?= $customerName ?></strong>
                            <div style="font-size: 0.75rem; color: var(--color-text-light); margin-top: 4px;"><?= $customerPhone ?></div>
                            <div style="font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 4px; line-height: 1.4;"><?= $fullAddress ?></div>
                        </td>
                        <td style="vertical-align: top; padding-top: 16px;">
                            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem; line-height: 1.5; color: var(--color-text-primary);">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li>
                                        <strong><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['product_name']) ?> 
                                        <span style="color: var(--color-text-light);">(<?= formatRupiah((float)$item['price']) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td style="vertical-align: top; padding-top: 16px;">
                            <span style="display: block; font-size: 0.75rem; color: var(--color-text-light);"><?= htmlspecialchars($order['payment_method']) ?></span>
                            <span class="td-price" style="display: block; margin-top: 4px; font-weight: 700; color: var(--color-primary);"><?= formatRupiah((float)$order['total_price']) ?></span>
                        </td>
                        <td style="vertical-align: top; padding-top: 16px;">
                            <span class="status-badge status-badge--diproses">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="status-badge__icon">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td style="vertical-align: top; padding-top: 12px; text-align: right; padding-right: 20px; white-space: nowrap;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <form action="" method="POST" style="margin: 0;">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="Dikirim">
                                    <button type="submit" class="btn-admin btn-admin--primary" title="Kirim barang pesanan">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                                            <rect x="1" y="3" width="15" height="13"/>
                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                            <circle cx="5.5" cy="18.5" r="2.5"/>
                                            <circle cx="18.5" cy="18.5" r="2.5"/>
                                        </svg>
                                        Proses Kirim
                                    </button>
                                </form>
                                <form action="" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan #<?= htmlspecialchars($order['order_code']) ?>?');">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="Dibatalkan">
                                    <button type="submit" class="btn-admin btn-admin--danger" title="Batalkan pesanan ini">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px; color: var(--color-text-light);">
                        Tidak ada pesanan masuk yang sedang menunggu proses.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

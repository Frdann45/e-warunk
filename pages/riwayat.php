<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Riwayat Pesanan (Shopee-Style)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-24
 * Updated     : 2026-07-03
 * Description : Shopee-style purchase history page with:
 *               - Sticky horizontal status tabs (orange active)
 *               - Order search bar
 *               - Shopee-style Order Cards (not table)
 * ============================================================
 */
require_once __DIR__ . '/../db_connect.php';

// Handle order actions (Cancel or Complete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action    = $_POST['action'];
    $orderCode = isset($_POST['order_code']) ? trim($_POST['order_code']) : '';
    
    if ($action === 'cancel' && !empty($orderCode)) {
        try {
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_code = ?");
            $stmt->execute([$orderCode]);
            $order = $stmt->fetch();
            
            if ($order) {
                $status = $order['status'];
                if ($status === 'Belum Bayar' || $status === 'Diproses') {
                    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'Dibatalkan' WHERE order_code = ?");
                    $stmtUpdate->execute([$orderCode]);
                    $_SESSION['cart_message'] = 'Pesanan #' . htmlspecialchars($orderCode) . ' berhasil dibatalkan.';
                } else {
                    $_SESSION['cart_message'] = 'Pesanan tidak dapat dibatalkan karena sudah melewati proses pengemasan.';
                }
            } else {
                $_SESSION['cart_message'] = 'Pesanan tidak ditemukan.';
            }
        } catch (PDOException $e) {
            error_log('Order cancel error: ' . $e->getMessage());
            $_SESSION['cart_message'] = 'Terjadi kesalahan sistem, silakan coba lagi.';
        }
        echo "<script>window.location.href = 'index.php?page=riwayat';</script>";
        exit;
    }
    
    if ($action === 'complete' && !empty($orderCode)) {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'Selesai' WHERE order_code = ?");
            $stmtUpdate->execute([$orderCode]);
            $_SESSION['cart_message'] = 'Pesanan #' . htmlspecialchars($orderCode) . ' telah selesai. Terima kasih!';
        } catch (PDOException $e) {
            error_log('Order complete error: ' . $e->getMessage());
            $_SESSION['cart_message'] = 'Terjadi kesalahan sistem, silakan coba lagi.';
        }
        echo "<script>window.location.href = 'index.php?page=riwayat';</script>";
        exit;
    }
}

// Tab / filter variables
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Semua';
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

// Map display tabs to DB status values
$statusTabs = [
    'Semua'          => null,
    'Belum Bayar'    => 'Belum Bayar',
    'Sedang Dikemas' => 'Diproses',
    'Dikirim'        => 'Dikirim',
    'Selesai'        => 'Selesai',
    'Dibatalkan'     => 'Dibatalkan',
];

try {
    // Build dynamic query for orders list
    $sql = "
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count,
               (SELECT product_name FROM order_items WHERE order_id = o.id ORDER BY id ASC LIMIT 1) AS first_item_name
        FROM orders o
        WHERE 1=1
    ";
    
    $params = [];
    $dbStatus = isset($statusTabs[$selectedStatus]) ? $statusTabs[$selectedStatus] : null;
    
    if ($dbStatus !== null) {
        $sql .= " AND o.status = ?";
        $params[] = $dbStatus;
    }
    
    if ($searchQuery !== '') {
        $sql .= " AND (o.order_code LIKE ? OR EXISTS (
            SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.product_name LIKE ?
        ))";
        $params[] = '%' . $searchQuery . '%';
        $params[] = '%' . $searchQuery . '%';
    }
    
    $sql .= " ORDER BY o.order_date DESC, o.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Fetch order items for each order
    $orderItems = [];
    if (!empty($orders)) {
        $orderIds = array_column($orders, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY id ASC");
        $stmtItems->execute($orderIds);
        $allItems = $stmtItems->fetchAll();
        foreach ($allItems as $item) {
            $orderItems[$item['order_id']][] = $item;
        }
    }

} catch (PDOException $e) {
    $orders = [];
    $orderItems = [];
    error_log('Orders fetch error: ' . $e->getMessage());
}

// Status display colors
function getStatusColor(string $status): string {
    $map = [
        'Selesai'    => '#27ae60',
        'Diproses'   => '#f39c12',
        'Dikirim'    => '#2980b9',
        'Dibatalkan'  => '#e74c3c',
        'Belum Bayar' => '#e67e22',
    ];
    return $map[$status] ?? '#f39c12';
}

function getStatusLabel(string $status): string {
    $map = [
        'Diproses'   => 'SEDANG DIKEMAS',
        'Dikirim'    => 'DIKIRIM',
        'Selesai'    => 'SELESAI',
        'Dibatalkan'  => 'DIBATALKAN',
        'Belum Bayar' => 'BELUM BAYAR',
    ];
    return $map[$status] ?? strtoupper($status);
}
?>

<!-- ═══════════════════════════════════════════════════════════
     RIWAYAT PESANAN — Shopee-Style Purchase History
     ═══════════════════════════════════════════════════════════ -->
<div class="order-history" id="order-history-page">

    <!-- ── Sticky Horizontal Tabs ────────────────────────────── -->
    <div class="order-tabs" id="order-tabs">
        <?php foreach (array_keys($statusTabs) as $tabLabel): ?>
            <a href="index.php?page=riwayat&status=<?= urlencode($tabLabel) ?><?= $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : '' ?>"
               class="order-tab <?= $selectedStatus === $tabLabel ? 'order-tab--active' : '' ?>"
               id="tab-<?= strtolower(str_replace(' ', '-', $tabLabel)) ?>">
                <?= htmlspecialchars($tabLabel) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ── Order Search Bar ──────────────────────────────────── -->
    <div class="order-search" id="order-search-wrapper">
        <form action="index.php" method="GET" class="order-search__form">
            <input type="hidden" name="page" value="riwayat">
            <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="order-search__icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                type="text"
                name="search"
                class="order-search__input"
                placeholder="Kamu bisa cari berdasarkan Nama Penjual, No. Pesanan atau Nama Produk"
                value="<?= htmlspecialchars($searchQuery) ?>"
            >
        </form>
    </div>

    <!-- ── Order Cards List ──────────────────────────────────── -->
    <div class="order-cards" id="order-cards-list">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <?php
                $items = isset($orderItems[$order['id']]) ? $orderItems[$order['id']] : [];
                $statusColor = getStatusColor($order['status']);
                $statusLabel = getStatusLabel($order['status']);
                $dateTime = new DateTime($order['order_date']);
                $formattedDate = $dateTime->format('d M Y, H:i');
                ?>
                <div class="order-card order-card--<?= strtolower(str_replace(' ', '-', $order['status'])) ?> fade-in" id="order-<?= htmlspecialchars($order['order_code']) ?>">

                    <!-- Card Header: Store Name + Status -->
                    <div class="order-card__header">
                        <div class="order-card__store">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="order-card__store-icon">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <span class="order-card__store-name">Warung Tiga Saudara</span>
                            <span class="order-card__order-code">#<?= htmlspecialchars($order['order_code']) ?></span>
                        </div>
                        <div class="order-card__status" style="color: <?= $statusColor ?>;">
                            <?= $statusLabel ?>
                        </div>
                    </div>

                    <!-- Card Body: Product Items -->
                    <div class="order-card__body">
                        <?php foreach ($items as $idx => $item): ?>
                            <div class="order-card__product <?= $idx > 0 ? 'order-card__product--extra' : '' ?>">
                                <div class="order-card__product-img">
                                    <img src="<?= htmlspecialchars(getProductImage($item['product_name'])) ?>" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>"
                                         loading="lazy">
                                </div>
                                <div class="order-card__product-info">
                                    <h4 class="order-card__product-name"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <span class="order-card__product-qty">x<?= (int) $item['quantity'] ?></span>
                                </div>
                                <div class="order-card__product-price">
                                    <?= formatRupiah((float) $item['price']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <div class="order-card__product">
                                <div class="order-card__product-info">
                                    <h4 class="order-card__product-name"><?= htmlspecialchars($order['first_item_name'] ?? 'Produk') ?></h4>
                                    <span class="order-card__product-qty">x1</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Footer: Total + Action Buttons -->
                    <div class="order-card__footer">
                        <div class="order-card__footer-left">
                            <span class="order-card__date"><?= $formattedDate ?></span>
                        </div>
                        <div class="order-card__footer-right">
                            <span class="order-card__total-label">Total Pesanan:</span>
                            <span class="order-card__total-price"><?= formatRupiah((float) $order['total_price']) ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons Row -->
                    <div class="order-card__actions">
                        <?php if ($order['status'] === 'Belum Bayar'): ?>
                            <?php if (!empty($order['payment_url'])): ?>
                                <a href="<?= htmlspecialchars($order['payment_url']) ?>" class="order-card__btn order-card__btn--primary" title="Lanjutkan pembayaran aman" style="text-align: center; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">Bayar Sekarang</a>
                            <?php else: ?>
                                <span class="order-card__actions-note" style="color: var(--color-accent-red); margin-right: auto;">Link pembayaran tidak tersedia.</span>
                            <?php endif; ?>
                            <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="order_code" value="<?= htmlspecialchars($order['order_code']) ?>">
                                <button type="submit" class="order-card__btn order-card__btn--danger" title="Batalkan pesanan ini">Batalkan Pesanan</button>
                            </form>
                        <?php elseif ($order['status'] === 'Diproses'): ?>
                            <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="order_code" value="<?= htmlspecialchars($order['order_code']) ?>">
                                <button type="submit" class="order-card__btn order-card__btn--danger" title="Batalkan pesanan ini">Batalkan Pesanan</button>
                            </form>
                            <span class="order-card__actions-note" style="margin-left: auto;">Pesanan sedang dikemas oleh admin toko.</span>
                        <?php elseif ($order['status'] === 'Dikirim'): ?>
                            <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin pesanan sudah sampai dan ingin menyelesaikannya?');">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="order_code" value="<?= htmlspecialchars($order['order_code']) ?>">
                                <button type="submit" class="order-card__btn order-card__btn--primary" title="Konfirmasi pesanan selesai">Pesanan Selesai</button>
                            </form>
                            <button type="button" class="order-card__btn" title="Ajukan pengembalian">Ajukan Pengembalian</button>
                        <?php elseif ($order['status'] === 'Selesai'): ?>
                            <button type="button" class="order-card__btn order-card__btn--primary" title="Beli lagi">Beli Lagi</button>
                        <?php endif; ?>
                        <button type="button" class="order-card__btn" title="Hubungi penjual">Hubungi Penjual</button>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="order-empty fade-in">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="order-empty__icon">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <h3 class="order-empty__title">Belum Ada Pesanan</h3>
                <p class="order-empty__desc">Tidak ada transaksi yang cocok dengan kriteria filter Anda.</p>
                <a href="index.php?page=sembako" class="order-empty__cta">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>

</div>

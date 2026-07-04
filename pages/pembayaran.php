<?php
/**
 * =======
 * Warung Tiga Saudara - Pembayaran (Checkout) view
 * Author ID: 11240044
 * =======
 */
require_once __DIR__ . '/../db_connect.php';

// ── Auth guard ────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required_message'] = 'Silakan login terlebih dahulu untuk melanjutkan ke pembayaran.';
    header('Location: login.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];

// ── Fetch user addresses ──────────────────────────────────
$addresses = [];
try {
    $stmtAddr = $pdo->prepare("SELECT * FROM addresses WHERE user_id=? ORDER BY is_primary DESC, id ASC");
    $stmtAddr->execute([$userId]);
    $addresses = $stmtAddr->fetchAll();
} catch (PDOException $e) {
    error_log('Address fetch error: ' . $e->getMessage());
}

// Primary address (first entry after ORDER BY is_primary DESC)
$primaryAddress = !empty($addresses) ? $addresses[0] : null;

// Flash message from address_action.php
$addressMessage = '';
if (isset($_SESSION['address_message'])) {
    $addressMessage = $_SESSION['address_message'];
    unset($_SESSION['address_message']);
}

// ── Cart items ────────────────────────────────────────────
$cartItems = [];
$totalOriginal = 0;
$totalCount = 0;

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
                    'qty'     => $qty,
                    'subtotal'=> $subtotal
                ];
                
                $totalOriginal += $subtotal;
                $totalCount    += $qty;
            }
        } catch (PDOException $e) {
            error_log('Checkout items fetch error: ' . $e->getMessage());
        }
    }
}

// Calculations
$shippingFee      = empty($_SESSION['cart']) ? 0 : 15000;
$shippingDiscount = empty($_SESSION['cart']) ? 0 : 5000;
$totalBill        = $totalOriginal + $shippingFee - $shippingDiscount;
?>

<!-- Address flash message toast -->
<?php if ($addressMessage): ?>
<div class="toast" id="addr-toast" style="top:80px;">
    <svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <?= htmlspecialchars($addressMessage) ?>
</div>
<script>setTimeout(function(){var t=document.getElementById('addr-toast');if(t)t.remove();},3200);</script>
<?php endif; ?>

<div class="checkout-header fade-in">
    <h1 class="checkout-header__title">Checkout</h1>
</div>

<?php if (empty($cartItems)): ?>
    <div class="cart-empty fade-in">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="cart-empty__icon">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        <h2 class="cart-empty__title">Tidak Ada Pesanan untuk di-Checkout</h2>
        <p class="cart-empty__desc">Silakan masukkan beberapa produk ke keranjang belanja Anda terlebih dahulu.</p>
        <a href="index.php?page=sembako" class="btn-shop-now" style="margin-top: 16px;">Belanja Sekarang</a>
    </div>
<?php else: ?>
    <form action="" method="POST" id="checkout-form">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="payment_method" id="input-payment-method" value="Bank Transfer">
        <?php if ($primaryAddress): ?>
        <input type="hidden" name="address_id" value="<?= (int) $primaryAddress['id'] ?>">
        <?php endif; ?>
        
        <div class="checkout-layout fade-in">
            <!-- Checkout Content -->
            <div class="checkout-content">

                <!-- ── Address Section ── -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="checkout-section__title">Alamat Pengiriman</span>
                        </div>
                        <button type="button" class="btn-change-address" id="btn-open-address-modal">Ubah Alamat</button>
                    </div>
                    <div class="checkout-section__body">
                        <?php if ($primaryAddress): ?>
                            <div class="address-details" id="address-display">
                                <h4 class="address-details__name"><?= htmlspecialchars($primaryAddress['recipient_name']) ?></h4>
                                <p class="address-details__phone"><?= htmlspecialchars($primaryAddress['phone']) ?></p>
                                <p class="address-details__text">
                                    <?= nl2br(htmlspecialchars(
                                        $primaryAddress['address_line'] . "\n" .
                                        $primaryAddress['city'] . ', ' . $primaryAddress['province'] . ' ' . $primaryAddress['postal_code']
                                    )) ?>
                                </p>
                                <span class="address-details__badge">Utama</span>
                            </div>
                        <?php else: ?>
                            <div class="address-empty-state" id="address-display">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;color:var(--color-text-light);margin-bottom:10px;">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <p style="color:var(--color-text-light);font-size:0.9rem;margin-bottom:10px;">Belum ada alamat pengiriman.</p>
                                <button type="button" class="btn-change-address" onclick="openAddressModal()">+ Tambah Alamat</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Order Details Section ── -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            <span class="checkout-section__title">Rincian Pesanan</span>
                        </div>
                    </div>
                    <div class="checkout-section__body">
                        <div class="checkout-items-list">
                            <?php foreach ($cartItems as $item):
                                $p   = $item['product'];
                                $qty = $item['qty'];
                                $sub = $item['subtotal']; ?>
                                <div class="checkout-item-row">
                                    <img src="<?= htmlspecialchars(getProductImage($p['name'])) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="checkout-item-row__image">
                                    <div class="checkout-item-row__info">
                                        <h4 class="checkout-item-row__name"><?= htmlspecialchars($p['name']) ?></h4>
                                        <p class="checkout-item-row__qty"><?= $qty ?> x <?= formatRupiah((float) $p['price']) ?></p>
                                    </div>
                                    <span class="checkout-item-row__price"><?= formatRupiah((float) $sub) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Payment Method Section ── -->
                <div class="checkout-section">
                    <div class="checkout-section__header-bar">
                        <div class="checkout-section__title-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="checkout-section__icon">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            <span class="checkout-section__title">Metode Pembayaran</span>
                        </div>
                    </div>
                    <div class="checkout-section__body">
                        <div class="payment-methods-grid">
                            <div class="payment-method-box payment-method-box--active" data-method="Bank Transfer" id="pay-bank">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <span class="payment-method-box__name">Bank Transfer</span>
                                <span class="payment-method-box__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                            <div class="payment-method-box" data-method="E-Wallet" id="pay-wallet">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <span class="payment-method-box__name">E-Wallet</span>
                                <span class="payment-method-box__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                            <div class="payment-method-box" data-method="COD" id="pay-cod">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="payment-method-box__icon">
                                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                <span class="payment-method-box__name">COD</span>
                                <span class="payment-method-box__check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Summary Sidebar -->
            <div class="checkout-summary">
                <div class="summary-card">
                    <h3 class="summary-card__title">Ringkasan Belanja</h3>
                    <div class="summary-card__row">
                        <span class="summary-card__label">Total Harga (<?= $totalCount ?> barang)</span>
                        <span class="summary-card__val"><?= formatRupiah((float) $totalOriginal) ?></span>
                    </div>
                    <div class="summary-card__row">
                        <span class="summary-card__label">Ongkos Kirim</span>
                        <span class="summary-card__val"><?= formatRupiah((float) $shippingFee) ?></span>
                    </div>
                    <div class="summary-card__row">
                        <span class="summary-card__label">Diskon Ongkir</span>
                        <span class="summary-card__val text-discount">-<?= formatRupiah((float) $shippingDiscount) ?></span>
                    </div>
                    <div class="summary-card__divider"></div>
                    <div class="summary-card__total-row">
                        <span class="summary-card__total-label">Total Tagihan</span>
                        <span class="summary-card__total-val"><?= formatRupiah((float) $totalBill) ?></span>
                    </div>
                    <button type="submit" class="btn-pay-now" <?= empty($primaryAddress) ? 'disabled title="Tambahkan alamat pengiriman terlebih dahulu"' : '' ?>>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-pay-now__icon">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Bayar Sekarang
                    </button>
                    <div class="secure-checkout-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="secure-checkout-badge__icon">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Pembayaran Aman
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════
     MODAL: Ubah / Kelola Alamat Pengiriman
     ═══════════════════════════════════════════════════════ -->
<div class="addr-modal-overlay" id="addressModal">
    <div class="addr-modal" role="dialog" aria-modal="true" aria-labelledby="addr-modal-title">

        <!-- Modal Header -->
        <div class="addr-modal__header">
            <h2 class="addr-modal__title" id="addr-modal-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                Kelola Alamat Pengiriman
            </h2>
            <button class="addr-modal__close" id="btn-close-address-modal" aria-label="Tutup">&times;</button>
        </div>

        <!-- Modal Body -->
        <div class="addr-modal__body">

            <!-- Existing addresses list -->
            <?php if (!empty($addresses)): ?>
            <div class="addr-list" id="addr-list">
                <?php foreach ($addresses as $addr): ?>
                <div class="addr-item <?= $addr['is_primary'] ? 'addr-item--primary' : '' ?>" id="addr-item-<?= $addr['id'] ?>">
                    <div class="addr-item__info">
                        <div class="addr-item__name-row">
                            <strong class="addr-item__name"><?= htmlspecialchars($addr['recipient_name']) ?></strong>
                            <?php if ($addr['is_primary']): ?>
                            <span class="addr-item__badge">Utama</span>
                            <?php endif; ?>
                        </div>
                        <p class="addr-item__phone"><?= htmlspecialchars($addr['phone']) ?></p>
                        <p class="addr-item__addr">
                            <?= htmlspecialchars($addr['address_line']) ?>,
                            <?= htmlspecialchars($addr['city']) ?>,
                            <?= htmlspecialchars($addr['province']) ?>
                            <?= htmlspecialchars($addr['postal_code']) ?>
                        </p>
                    </div>
                    <div class="addr-item__actions">
                        <?php if (!$addr['is_primary']): ?>
                        <form action="address_action.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="set_primary">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" class="addr-btn addr-btn--primary-set">Jadikan Utama</button>
                        </form>
                        <?php endif; ?>
                        <button type="button" class="addr-btn addr-btn--edit"
                            data-id="<?= $addr['id'] ?>"
                            data-name="<?= htmlspecialchars($addr['recipient_name']) ?>"
                            data-phone="<?= htmlspecialchars($addr['phone']) ?>"
                            data-line="<?= htmlspecialchars($addr['address_line']) ?>"
                            data-city="<?= htmlspecialchars($addr['city']) ?>"
                            data-province="<?= htmlspecialchars($addr['province']) ?>"
                            data-postal="<?= htmlspecialchars($addr['postal_code']) ?>"
                            data-primary="<?= $addr['is_primary'] ?>">
                            Edit
                        </button>
                        <?php if (count($addresses) > 1): ?>
                        <form action="address_action.php" method="POST" style="display:inline;"
                            onsubmit="return confirm('Hapus alamat ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" class="addr-btn addr-btn--delete">Hapus</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Divider -->
            <div class="addr-form-toggle">
                <button type="button" class="addr-btn-add-toggle" id="btn-toggle-addr-form">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Alamat Baru
                </button>
            </div>

            <!-- Add / Edit form (hidden by default) -->
            <form action="address_action.php" method="POST" class="addr-form" id="addr-form" style="display:none;">
                <input type="hidden" name="action" value="add" id="addr-form-action">
                <input type="hidden" name="address_id" value="" id="addr-form-id">

                <h3 class="addr-form__title" id="addr-form-title">Tambah Alamat Baru</h3>

                <div class="addr-form__grid">
                    <div class="addr-form__field">
                        <label class="addr-form__label" for="f-name">Nama Penerima</label>
                        <input type="text" class="addr-form__input" id="f-name" name="recipient_name" placeholder="Nama lengkap penerima" required>
                    </div>
                    <div class="addr-form__field">
                        <label class="addr-form__label" for="f-phone">Nomor Telepon</label>
                        <input type="text" class="addr-form__input" id="f-phone" name="phone" placeholder="+62 8xx xxxx xxxx" required>
                    </div>
                </div>

                <div class="addr-form__field">
                    <label class="addr-form__label" for="f-line">Alamat Lengkap</label>
                    <textarea class="addr-form__input addr-form__textarea" id="f-line" name="address_line" rows="3"
                        placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan" required></textarea>
                </div>

                <div class="addr-form__grid">
                    <div class="addr-form__field">
                        <label class="addr-form__label" for="f-city">Kota / Kabupaten</label>
                        <input type="text" class="addr-form__input" id="f-city" name="city" placeholder="Jakarta Pusat" required>
                    </div>
                    <div class="addr-form__field">
                        <label class="addr-form__label" for="f-province">Provinsi</label>
                        <input type="text" class="addr-form__input" id="f-province" name="province" placeholder="DKI Jakarta" required>
                    </div>
                    <div class="addr-form__field">
                        <label class="addr-form__label" for="f-postal">Kode Pos</label>
                        <input type="text" class="addr-form__input" id="f-postal" name="postal_code" placeholder="10240" maxlength="10" required>
                    </div>
                </div>

                <label class="addr-form__checkbox-label">
                    <input type="checkbox" name="is_primary" id="f-primary">
                    <span>Jadikan alamat utama</span>
                </label>

                <div class="addr-form__actions">
                    <button type="button" class="addr-btn addr-btn--cancel" id="btn-cancel-addr-form">Batal</button>
                    <button type="submit" class="addr-btn addr-btn--save">Simpan Alamat</button>
                </div>
            </form>
        </div><!-- /.addr-modal__body -->
    </div>
</div>


<style>
/* ═══════════════════════════════════════════════════════
   ADDRESS MODAL STYLES
   ═══════════════════════════════════════════════════════ */
.addr-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(3px);
    animation: overlayFadeIn 0.2s ease;
}
.addr-modal-overlay--show {
    display: flex;
}
@keyframes overlayFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
.addr-modal {
    background: #fff;
    border-radius: 18px;
    width: 100%;
    max-width: 620px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    animation: modalSlideUp 0.3s cubic-bezier(0.4,0,0.2,1);
    overflow: hidden;
}
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(24px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.addr-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f0ece8;
    flex-shrink: 0;
}
.addr-modal__title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #4A2710;
    margin: 0;
}
.addr-modal__close {
    background: none;
    border: none;
    font-size: 1.6rem;
    color: #999;
    cursor: pointer;
    line-height: 1;
    padding: 4px 8px;
    border-radius: 8px;
    transition: background 0.2s, color 0.2s;
}
.addr-modal__close:hover { background: #f5f3f0; color: #4A2710; }
.addr-modal__body {
    overflow-y: auto;
    padding: 20px 24px 24px;
    flex: 1;
}

/* Address list items */
.addr-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
.addr-item {
    border: 1.5px solid #e8e4e0;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    justify-content: space-between;
    transition: border-color 0.2s, background 0.2s;
}
.addr-item--primary {
    border-color: #6D3A1A;
    background: rgba(109,58,26,0.03);
}
.addr-item__info { flex: 1; min-width: 0; }
.addr-item__name-row { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
.addr-item__name { font-size: 0.92rem; font-weight: 700; color: #2d2d2d; }
.addr-item__badge {
    font-size: 0.68rem; font-weight: 700; letter-spacing: 0.04em;
    background: #6D3A1A; color: #fff;
    padding: 2px 8px; border-radius: 20px;
}
.addr-item__phone { font-size: 0.82rem; color: #6b6b6b; margin-bottom: 4px; }
.addr-item__addr { font-size: 0.82rem; color: #6b6b6b; line-height: 1.5; }
.addr-item__actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }

/* Buttons */
.addr-btn {
    font-size: 0.75rem; font-weight: 600; font-family: inherit;
    padding: 5px 12px; border-radius: 8px; border: 1.5px solid transparent;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.addr-btn--primary-set { border-color: #6D3A1A; color: #6D3A1A; background: transparent; }
.addr-btn--primary-set:hover { background: #6D3A1A; color: #fff; }
.addr-btn--edit { border-color: #e8e4e0; color: #4a4a4a; background: #f8f6f4; }
.addr-btn--edit:hover { border-color: #6D3A1A; color: #6D3A1A; }
.addr-btn--delete { border-color: #fde8e8; color: #b8382c; background: #fff8f8; }
.addr-btn--delete:hover { background: #b8382c; color: #fff; border-color: #b8382c; }
.addr-btn--cancel { border-color: #e8e4e0; color: #6b6b6b; background: #f8f6f4; padding: 9px 20px; font-size: 0.85rem; }
.addr-btn--cancel:hover { background: #e8e4e0; }
.addr-btn--save {
    border-color: transparent; background: linear-gradient(135deg, #6D3A1A, #4A2710);
    color: #fff; padding: 9px 20px; font-size: 0.85rem;
    box-shadow: 0 3px 10px rgba(109,58,26,0.25);
}
.addr-btn--save:hover { background: linear-gradient(135deg, #5A2F15, #4A2710); transform: translateY(-1px); }

/* Toggle button */
.addr-form-toggle { margin-bottom: 4px; }
.addr-btn-add-toggle {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 0.85rem; font-weight: 600; color: #6D3A1A;
    background: none; border: 1.5px dashed rgba(109,58,26,0.35);
    border-radius: 10px; padding: 9px 16px; cursor: pointer;
    transition: all 0.2s; width: 100%; justify-content: center;
}
.addr-btn-add-toggle:hover { background: rgba(109,58,26,0.04); border-color: #6D3A1A; }

/* Form */
.addr-form { margin-top: 16px; }
.addr-form__title { font-size: 0.95rem; font-weight: 700; color: #2d2d2d; margin-bottom: 16px; }
.addr-form__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.addr-form__field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 12px; }
.addr-form__label { font-size: 0.72rem; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 0.05em; }
.addr-form__input {
    padding: 10px 12px; border: 1.5px solid #e8e4e0; border-radius: 10px;
    font-size: 0.88rem; font-family: inherit; color: #2d2d2d;
    background: #f8f6f4; outline: none; transition: all 0.2s;
}
.addr-form__input:focus { border-color: #8B5E3C; background: #fff; box-shadow: 0 0 0 3px rgba(109,58,26,0.08); }
.addr-form__textarea { resize: vertical; min-height: 80px; }
.addr-form__checkbox-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.85rem; color: #4a4a4a; cursor: pointer; margin-bottom: 16px;
}
.addr-form__checkbox-label input { accent-color: #6D3A1A; width: 15px; height: 15px; cursor: pointer; }
.addr-form__actions { display: flex; gap: 10px; justify-content: flex-end; }

@media (max-width: 540px) {
    .addr-form__grid { grid-template-columns: 1fr; }
    .addr-item { flex-direction: column; }
    .addr-item__actions { flex-direction: row; flex-wrap: wrap; }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const overlay     = document.getElementById('addressModal');
    const btnOpen     = document.getElementById('btn-open-address-modal');
    const btnClose    = document.getElementById('btn-close-address-modal');
    const addrForm    = document.getElementById('addr-form');
    const formAction  = document.getElementById('addr-form-action');
    const formId      = document.getElementById('addr-form-id');
    const formTitle   = document.getElementById('addr-form-title');
    const btnToggle   = document.getElementById('btn-toggle-addr-form');
    const btnCancel   = document.getElementById('btn-cancel-addr-form');

    // ── Open/Close modal ───────────────────────────────
    function openAddressModal() {
        overlay.classList.add('addr-modal-overlay--show');
        document.body.style.overflow = 'hidden';
    }
    function closeAddressModal() {
        overlay.classList.remove('addr-modal-overlay--show');
        document.body.style.overflow = '';
        resetForm();
    }
    if (btnOpen) btnOpen.addEventListener('click', openAddressModal);
    if (btnClose) btnClose.addEventListener('click', closeAddressModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeAddressModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAddressModal();
    });
    // expose for PHP inline onclick
    window.openAddressModal = openAddressModal;

    // ── Show / hide add form ───────────────────────────
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            resetForm();
            addrForm.style.display = addrForm.style.display === 'none' ? 'block' : 'none';
            if (addrForm.style.display === 'block') {
                addrForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            addrForm.style.display = 'none';
            resetForm();
        });
    }

    // ── Reset form to "add" state ──────────────────────
    function resetForm() {
        formAction.value = 'add';
        formId.value     = '';
        formTitle.textContent = 'Tambah Alamat Baru';
        addrForm.querySelector('#f-name').value     = '';
        addrForm.querySelector('#f-phone').value    = '';
        addrForm.querySelector('#f-line').value     = '';
        addrForm.querySelector('#f-city').value     = '';
        addrForm.querySelector('#f-province').value = '';
        addrForm.querySelector('#f-postal').value   = '';
        addrForm.querySelector('#f-primary').checked = false;
    }

    // ── Edit button: populate form ─────────────────────
    document.querySelectorAll('.addr-btn--edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            formAction.value = 'update';
            formId.value     = btn.dataset.id;
            formTitle.textContent = 'Edit Alamat';
            addrForm.querySelector('#f-name').value     = btn.dataset.name;
            addrForm.querySelector('#f-phone').value    = btn.dataset.phone;
            addrForm.querySelector('#f-line').value     = btn.dataset.line;
            addrForm.querySelector('#f-city').value     = btn.dataset.city;
            addrForm.querySelector('#f-province').value = btn.dataset.province;
            addrForm.querySelector('#f-postal').value   = btn.dataset.postal;
            addrForm.querySelector('#f-primary').checked = btn.dataset.primary === '1';
            addrForm.style.display = 'block';
            addrForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    // ── Payment method switching ───────────────────────
    var paymentBoxes = document.querySelectorAll('.payment-method-box');
    var inputPaymentMethod = document.getElementById('input-payment-method');
    paymentBoxes.forEach(function(box) {
        box.addEventListener('click', function() {
            paymentBoxes.forEach(function(b) { b.classList.remove('payment-method-box--active'); });
            box.classList.add('payment-method-box--active');
            inputPaymentMethod.value = box.getAttribute('data-method');
        });
    });
});
</script>

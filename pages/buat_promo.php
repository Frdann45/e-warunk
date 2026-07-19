<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Buat Promo (Admin View)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-29
 * Description : Admin page to create, update, and remove
 *               promotional badge labels on products.
 *               Also manages the featured promo cards shown
 *               on the Promo Bulanan page.
 * ============================================================
 */

require_once dirname(__DIR__) . '/config/db_connect.php';

// ── Auth guard ────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// ── Read flash message (set by product_action.php) ───────────
$flashMessage = '';
$flashType    = 'success';
if (isset($_SESSION['promo_flash'])) {
    $flashMessage = $_SESSION['promo_flash'];
    $flashType    = $_SESSION['promo_flash_type'] ?? 'success';
    unset($_SESSION['promo_flash'], $_SESSION['promo_flash_type']);
}

// ── Preset badge options ──────────────────────────────────────
$badgeOptions = [
    'PALING LARIS', 'PROMO', 'STOK BANYAK', 'SISA SEDIKIT',
    'BARU', 'TERLARIS', 'ORGANIK', 'WANGI', 'DISKON',
];


// ── Fetch all products (group by category) ────────────────────
try {
    $stmtProds = $pdo->query("SELECT * FROM products ORDER BY category, name ASC");
    $allProducts = $stmtProds->fetchAll();
} catch (PDOException $e) {
    error_log('Promo page products fetch error: ' . $e->getMessage());
    $allProducts = [];
}

// Group by category
$grouped = [];
foreach ($allProducts as $p) {
    $grouped[$p['category']][] = $p;
}

// Count promo stats
$totalWithBadge    = count(array_filter($allProducts, fn($p) => !empty($p['badge_label'])));
$totalWithoutBadge = count($allProducts) - $totalWithBadge;
?>

<!-- ══════════════════════════════════════════════════════════
     PAGE HEADER
     ══════════════════════════════════════════════════════════ -->
<div class="history-header fade-in">
    <h1 class="history-header__title">Kelola Promo &amp; Badge</h1>
    <p class="history-header__desc">Atur badge label promosi yang tampil pada kartu produk di seluruh halaman.</p>
</div>

<!-- Flash message -->
<?php if ($flashMessage): ?>
<div class="prod-flash prod-flash--<?= $flashType ?>" id="promo-flash-msg">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="prod-flash__icon">
        <?php if ($flashType === 'success'): ?>
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        <?php else: ?>
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        <?php endif; ?>
    </svg>
    <?= htmlspecialchars($flashMessage) ?>
</div>
<script>setTimeout(function(){var el=document.getElementById('promo-flash-msg');if(el)el.remove();},4000);</script>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════
     STAT CARDS
     ══════════════════════════════════════════════════════════ -->
<div class="promo-stats fade-in">
    <div class="promo-stat-card">
        <div class="promo-stat-card__icon promo-stat-card__icon--total">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
            </svg>
        </div>
        <div>
            <div class="promo-stat-card__val"><?= $totalWithBadge ?></div>
            <div class="promo-stat-card__label">Produk Berpromo</div>
        </div>
    </div>
    <div class="promo-stat-card">
        <div class="promo-stat-card__icon promo-stat-card__icon--none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>
        <div>
            <div class="promo-stat-card__val"><?= $totalWithoutBadge ?></div>
            <div class="promo-stat-card__label">Tanpa Badge</div>
        </div>
    </div>
    <div class="promo-stat-card">
        <div class="promo-stat-card__icon promo-stat-card__icon--all">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>
        <div>
            <div class="promo-stat-card__val"><?= count($allProducts) ?></div>
            <div class="promo-stat-card__label">Total Produk</div>
        </div>
    </div>
    <!-- Bulk clear -->
    <form action="<?= BASE_URL ?>process/product_action.php" method="POST"
          onsubmit="return confirm('Hapus SEMUA badge dari seluruh produk?')">

        <input type="hidden" name="action" value="clear_all_badges">
        <button type="submit" class="prod-btn prod-btn--danger" id="btn-clear-all-badges">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
            </svg>
            Hapus Semua Badge
        </button>
    </form>
</div>


<!-- ══════════════════════════════════════════════════════════
     BADGE LEGEND
     ══════════════════════════════════════════════════════════ -->
<div class="promo-legend fade-in">
    <span class="promo-legend__title">Pilihan Badge:</span>
    <?php foreach ($badgeOptions as $opt): ?>
    <span class="promo-legend__chip"><?= $opt ?></span>
    <?php endforeach; ?>
</div>


<!-- ══════════════════════════════════════════════════════════
     PRODUCT TABLES BY CATEGORY
     ══════════════════════════════════════════════════════════ -->
<?php foreach ($grouped as $category => $products): ?>
<div class="promo-section fade-in">
    <div class="promo-section__header">
        <h2 class="promo-section__title">
            <?php
            $catIcons = [
                'Sembako'      => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
                'Rempah-rempah'=> '<path d="M12 2a10 10 0 00-6.88 17.23l.9-.67A8.5 8.5 0 0112 3.5 8.5 8.5 0 0118 18.56l.9.67A10 10 0 0012 2z"/><circle cx="12" cy="12" r="3"/>',
                'Camilan'      => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
            ];
            ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;">
                <?= $catIcons[$category] ?? '<rect x="2" y="2" width="20" height="20" rx="2"/>' ?>
            </svg>
            <?= htmlspecialchars($category) ?>
            <span class="promo-section__count"><?= count($products) ?> produk</span>
        </h2>
    </div>

    <div class="table-container">
        <table class="history-table prod-table" style="width:100%;">
            <thead>
                <tr>
                    <th>PRODUK</th>
                    <th style="text-align:right;">HARGA</th>
                    <th style="text-align:center;">BADGE SAAT INI</th>
                    <th style="text-align:center; min-width:280px; padding-right:20px;">UBAH BADGE</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr class="prod-table__row">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="<?= htmlspecialchars(getProductImage($p['name'], $p['image_url'] ?? '')) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="width:36px;height:36px;object-fit:cover;border-radius:8px;border:1px solid #f0ece8;">
                        <div>
                            <span style="font-weight:600;color:var(--color-text-primary);display:block;">
                                <?= htmlspecialchars($p['name']) ?>
                            </span>
                            <span style="font-size:0.75rem;color:var(--color-text-light);">
                                <?= htmlspecialchars($p['unit_desc']) ?>
                            </span>
                        </div>
                    </div>
                </td>
                <td style="text-align:right;font-weight:700;color:var(--color-primary);">
                    <?= formatRupiah((float) $p['price']) ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($p['badge_label']): ?>
                    <span class="product-card__badge product-card__badge--<?= $p['badge_label'] === 'PALING LARIS' ? 'laris' : 'promo' ?>"
                          style="position:static;display:inline-block;font-size:0.65rem;padding:3px 9px;">
                        <?= htmlspecialchars($p['badge_label']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#ccc;font-size:0.8rem;">Tidak ada</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;padding-right:20px;">
                    <form action="<?= BASE_URL ?>process/product_action.php" method="POST"
                          class="promo-inline-form" id="promo-form-<?= $p['id'] ?>">
                        <input type="hidden" name="action" value="set_badge">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <div class="promo-inline-form__controls">
                            <select name="badge_label" class="prod-toolbar__filter promo-select"
                                    id="badge-select-<?= $p['id'] ?>">
                                <option value="">-- Hapus Badge --</option>
                                <?php foreach ($badgeOptions as $opt): ?>
                                <option value="<?= $opt ?>"
                                    <?= ($p['badge_label'] ?? '') === $opt ? 'selected' : '' ?>>
                                    <?= $opt ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="prod-btn prod-btn--primary promo-apply-btn"
                                    id="btn-badge-<?= $p['id'] ?>" title="Terapkan badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Terapkan
                            </button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($allProducts)): ?>
<div style="text-align:center;padding:60px 20px;color:var(--color-text-light);">
    <p>Belum ada produk. Silakan tambahkan produk terlebih dahulu melalui menu <a href="admin.php?page=tambah-produk">Tambah Produk</a>.</p>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════
     PAGE-SCOPED STYLES
     ══════════════════════════════════════════════════════════ -->
<style>
/* Flash (reuse from tambah_produk) */
.prod-flash {
    display:flex;align-items:center;gap:10px;padding:12px 18px;
    border-radius:12px;font-size:0.87rem;font-weight:600;
    margin-bottom:20px;animation:flashSlide 0.35s ease;
}
@keyframes flashSlide { from{opacity:0;transform:translateY(-6px);}to{opacity:1;transform:translateY(0);} }
.prod-flash__icon{width:18px;height:18px;flex-shrink:0;}
.prod-flash--success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.3);color:#15803d;}
.prod-flash--error  {background:rgba(224,41,41,.06);border:1px solid rgba(224,41,41,.25);color:var(--accent-red);}

/* Stat cards */
.promo-stats {
    display:flex;align-items:center;gap:16px;flex-wrap:wrap;
    margin-bottom:20px;
}
.promo-stat-card {
    display:flex;align-items:center;gap:14px;
    background:var(--bg-card);border-radius:14px;padding:16px 20px;
    border:1px solid var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,0.05);
    min-width:160px;
}
.promo-stat-card__icon {
    width:44px;height:44px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.promo-stat-card__icon svg{width:20px;height:20px;}
.promo-stat-card__icon--total{background:rgba(11, 45, 114,.08);color:var(--primary-blue);}
.promo-stat-card__icon--none {background:rgba(156,163,175,.1);color:#6b7280;}
.promo-stat-card__icon--all  {background:rgba(59,130,246,.1); color:#2563eb;}
.promo-stat-card__val   {font-size:1.6rem;font-weight:800;color:var(--text-dark);line-height:1.1;}
.promo-stat-card__label {font-size:0.75rem;color:var(--text-gray);font-weight:500;}

/* Legend */
.promo-legend {
    display:flex;align-items:center;gap:8px;flex-wrap:wrap;
    padding:12px 16px;background:var(--bg-main);border-radius:12px;
    border:1px solid var(--border-color);margin-bottom:20px;
}
.promo-legend__title{font-size:0.78rem;font-weight:700;color:var(--text-gray);text-transform:uppercase;letter-spacing:.04em;}
.promo-legend__chip {
    display:inline-block;padding:3px 9px;border-radius:20px;
    font-size:0.7rem;font-weight:700;
    background:rgba(11, 45, 114,.08);color:var(--primary-blue);
    border:1px solid rgba(11, 45, 114,.15);
}

/* Section */
.promo-section{margin-bottom:28px;}
.promo-section__header{margin-bottom:10px;}
.promo-section__title {
    display:flex;align-items:center;gap:10px;
    font-size:1rem;font-weight:700;color:var(--text-dark);margin:0;
}
.promo-section__count {
    font-size:0.72rem;font-weight:500;color:var(--text-gray);
    background:var(--bg-main);padding:2px 8px;border-radius:20px;
}

/* Inline form */
.promo-inline-form__controls{display:flex;gap:8px;justify-content:center;align-items:center;}
.promo-select{padding:7px 10px;font-size:0.82rem;min-width:150px;}
.promo-apply-btn{padding:7px 14px;font-size:0.8rem;}

/* Reuse button styles */
.prod-btn {
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:9px;font-size:0.8rem;
    font-weight:600;font-family:inherit;cursor:pointer;
    border:1.5px solid transparent;text-decoration:none;
    transition:all 0.2s;white-space:nowrap;
}
.prod-btn--primary{background:linear-gradient(135deg,var(--primary-blue),var(--primary-hover));color:#fff;box-shadow:0 3px 10px rgba(11, 45, 114,.25);}
.prod-btn--primary:hover{transform:translateY(-1px);box-shadow:0 5px 14px rgba(11, 45, 114,.35);}
.prod-btn--danger{background:#fff8f8;color:var(--accent-red);border-color:#fde8e8;}
.prod-btn--danger:hover{background:var(--accent-red);color:#fff;border-color:var(--accent-red);}

.prod-toolbar__filter{
    padding:9px 12px;border:1.5px solid var(--border-color);border-radius:10px;
    font-size:0.87rem;font-family:inherit;background:var(--bg-main);
    outline:none;color:var(--text-dark);cursor:pointer;
}

.prod-table__row:hover{background:var(--bg-main);}
</style>

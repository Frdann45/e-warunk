<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Tambah Produk (Admin View)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-29
 * Description : Admin page to add, edit, and delete products
 *               from the products table.
 * ============================================================
 */

require_once __DIR__ . '/../db_connect.php';

// ── Auth guard ────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ── Categories list ───────────────────────────────────────────
$categories = ['Sembako', 'Rempah-rempah', 'Camilan'];

// ── Read flash message (set by product_action.php) ───────────
$flashMessage = '';
$flashType    = 'success';
if (isset($_SESSION['prod_flash'])) {
    $flashMessage = $_SESSION['prod_flash'];
    $flashType    = $_SESSION['prod_flash_type'] ?? 'success';
    unset($_SESSION['prod_flash'], $_SESSION['prod_flash_type']);
}

// ── Fetch product for edit (GET ?edit=id) ─────────────────────
$editProduct  = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    try {
        $stmtEdit = $pdo->prepare("SELECT * FROM products WHERE id=?");
        $stmtEdit->execute([$editId]);
        $editProduct = $stmtEdit->fetch();
    } catch (PDOException $e) {
        error_log('Fetch edit product error: ' . $e->getMessage());
    }
}

// ── Fetch all products with search/filter ─────────────────────
$search      = trim($_GET['search'] ?? '');
$filterCat   = trim($_GET['category'] ?? '');

try {
    $whereClauses = [];
    $params       = [];

    if ($search !== '') {
        $whereClauses[] = 'name LIKE ?';
        $params[]       = "%$search%";
    }
    if ($filterCat !== '') {
        $whereClauses[] = 'category = ?';
        $params[]       = $filterCat;
    }

    $where = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
    $stmtAll = $pdo->prepare("SELECT * FROM products $where ORDER BY category, name ASC");
    $stmtAll->execute($params);
    $products = $stmtAll->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch products error: ' . $e->getMessage());
    $products = [];
}

$totalProducts = count($products);
?>

<!-- ══════════════════════════════════════════════════════════
     PAGE HEADER
     ══════════════════════════════════════════════════════════ -->
<div class="history-header fade-in">
    <h1 class="history-header__title">Kelola Produk</h1>
    <p class="history-header__desc">Tambah, edit, atau hapus produk yang tersedia di toko.</p>
</div>

<!-- Flash message -->
<?php if ($flashMessage): ?>
<div class="prod-flash prod-flash--<?= $flashType ?>" id="prod-flash-msg">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="prod-flash__icon">
        <?php if ($flashType === 'success'): ?>
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        <?php else: ?>
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        <?php endif; ?>
    </svg>
    <?= htmlspecialchars($flashMessage) ?>
</div>
<script>setTimeout(function(){var el=document.getElementById('prod-flash-msg');if(el)el.remove();},4000);</script>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════
     FORM: Tambah / Edit Produk
     ══════════════════════════════════════════════════════════ -->
<div class="prod-form-card fade-in">
    <div class="prod-form-card__header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="prod-form-card__icon">
            <?php if ($editProduct): ?>
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            <?php else: ?>
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            <?php endif; ?>
        </svg>
        <h2 class="prod-form-card__title">
            <?= $editProduct ? 'Edit Produk: ' . htmlspecialchars($editProduct['name']) : 'Tambah Produk Baru' ?>
        </h2>
        <?php if ($editProduct): ?>
        <a href="index.php?page=tambah-produk" class="prod-btn prod-btn--cancel" style="margin-left:auto;">
            ✕ Batal Edit
        </a>
        <?php endif; ?>
    </div>

    <form action="product_action.php" method="POST" class="prod-form" id="prod-form">
        <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'add' ?>">
        <?php if ($editProduct): ?>
        <input type="hidden" name="product_id" value="<?= (int) $editProduct['id'] ?>">
        <?php endif; ?>

        <div class="prod-form__grid">
            <!-- Nama Produk -->
            <div class="prod-form__field prod-form__field--wide">
                <label class="prod-form__label" for="pf-name">Nama Produk <span class="prod-form__req">*</span></label>
                <input type="text" id="pf-name" name="name" class="prod-form__input"
                    placeholder="Contoh: Beras Rojolele Premium 5kg"
                    value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            </div>

            <!-- Kategori -->
            <div class="prod-form__field">
                <label class="prod-form__label" for="pf-category">Kategori <span class="prod-form__req">*</span></label>
                <select id="pf-category" name="category" class="prod-form__input prod-form__select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($editProduct['category'] ?? '') === $cat ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Deskripsi Satuan -->
            <div class="prod-form__field">
                <label class="prod-form__label" for="pf-unit">Satuan / Deskripsi <span class="prod-form__req">*</span></label>
                <input type="text" id="pf-unit" name="unit_desc" class="prod-form__input"
                    placeholder="Contoh: 5 kg / Karung"
                    value="<?= htmlspecialchars($editProduct['unit_desc'] ?? '') ?>" required>
            </div>

            <!-- Harga -->
            <div class="prod-form__field">
                <label class="prod-form__label" for="pf-price">Harga (Rp) <span class="prod-form__req">*</span></label>
                <input type="number" id="pf-price" name="price" class="prod-form__input"
                    placeholder="Contoh: 65000" min="1" step="500"
                    value="<?= $editProduct['price'] ?? '' ?>" required>
            </div>

            <!-- Badge Label -->
            <div class="prod-form__field">
                <label class="prod-form__label" for="pf-badge">Badge Label <span style="color:#999;font-weight:400;">(opsional)</span></label>
                <input type="text" id="pf-badge" name="badge_label" class="prod-form__input"
                    placeholder="Contoh: PALING LARIS, PROMO"
                    value="<?= htmlspecialchars($editProduct['badge_label'] ?? '') ?>">
            </div>

            <!-- URL Gambar -->
            <div class="prod-form__field">
                <label class="prod-form__label" for="pf-img">Path Gambar</label>
                <input type="text" id="pf-img" name="image_url" class="prod-form__input"
                    placeholder="Contoh: images/product-beras.jpg"
                    value="<?= htmlspecialchars($editProduct['image_url'] ?? 'images/logo.png') ?>">
            </div>
        </div>

        <div class="prod-form__actions">
            <button type="submit" class="prod-btn prod-btn--primary" id="btn-submit-prod">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?= $editProduct ? 'Simpan Perubahan' : 'Tambah Produk' ?>
            </button>
            <?php if (!$editProduct): ?>
            <button type="reset" class="prod-btn prod-btn--ghost">Reset</button>
            <?php endif; ?>
        </div>
    </form>
</div>


<!-- ══════════════════════════════════════════════════════════
     SEARCH & FILTER BAR
     ══════════════════════════════════════════════════════════ -->
<div class="prod-toolbar fade-in">
    <form method="GET" action="index.php" class="prod-toolbar__form" id="prod-search-form">
        <input type="hidden" name="page" value="tambah-produk">
        <div class="prod-toolbar__search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="prod-toolbar__search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" class="prod-toolbar__search" id="prod-search-input"
                placeholder="Cari nama produk…"
                value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="category" class="prod-toolbar__filter" id="prod-filter-cat" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= $filterCat === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="prod-btn prod-btn--primary" id="btn-search-prod">Cari</button>
        <?php if ($search || $filterCat): ?>
        <a href="index.php?page=tambah-produk" class="prod-btn prod-btn--ghost" id="btn-reset-search">Reset</a>
        <?php endif; ?>
    </form>
    <span class="prod-toolbar__count"><?= $totalProducts ?> produk ditemukan</span>
</div>


<!-- ══════════════════════════════════════════════════════════
     TABLE: Daftar Produk
     ══════════════════════════════════════════════════════════ -->
<div class="table-container fade-in" style="margin-top: 16px;">
    <table class="history-table prod-table" style="width:100%;">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>NAMA PRODUK</th>
                <th>KATEGORI</th>
                <th>SATUAN</th>
                <th style="text-align:right;">HARGA</th>
                <th style="text-align:center;">BADGE</th>
                <th style="text-align:center; padding-right:20px;">TINDAKAN</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $i => $p): ?>
            <tr class="prod-table__row" id="prod-row-<?= $p['id'] ?>">
                <td style="color:var(--color-text-light);font-size:0.78rem;"><?= $i + 1 ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="<?= htmlspecialchars(getProductImage($p['name'])) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="width:36px;height:36px;object-fit:cover;border-radius:8px;border:1px solid #f0ece8;">
                        <span style="font-weight:600;color:var(--color-text-primary);">
                            <?= htmlspecialchars($p['name']) ?>
                        </span>
                    </div>
                </td>
                <td>
                    <span class="prod-cat-badge prod-cat-badge--<?= strtolower(str_replace([' ', '-'], '', $p['category'])) ?>">
                        <?= htmlspecialchars($p['category']) ?>
                    </span>
                </td>
                <td style="font-size:0.82rem;color:var(--color-text-secondary);">
                    <?= htmlspecialchars($p['unit_desc']) ?>
                </td>
                <td style="text-align:right;font-weight:700;color:var(--color-primary);">
                    <?= formatRupiah((float) $p['price']) ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($p['badge_label']): ?>
                    <span class="product-card__badge product-card__badge--<?= $p['badge_label'] === 'PALING LARIS' ? 'laris' : 'promo' ?>"
                          style="position:static;display:inline-block;font-size:0.62rem;padding:2px 7px;">
                        <?= htmlspecialchars($p['badge_label']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#ccc;font-size:0.8rem;">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;padding-right:20px;">
                    <div style="display:flex;gap:8px;justify-content:center;">
                        <!-- Edit button -->
                        <a href="index.php?page=tambah-produk&edit=<?= $p['id'] ?>#prod-form"
                           class="prod-btn prod-btn--edit" id="btn-edit-<?= $p['id'] ?>"
                           title="Edit produk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Edit
                        </a>
                        <!-- Delete form -->
                        <form action="product_action.php" method="POST" style="margin:0;"
                              onsubmit="return confirm('Hapus produk \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'?\nTindakan ini tidak dapat dibatalkan.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="prod-btn prod-btn--danger" id="btn-del-<?= $p['id'] ?>"
                                    title="Hapus produk">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:48px;color:var(--color-text-light);">
                    <?= $search || $filterCat ? 'Tidak ada produk yang cocok dengan pencarian.' : 'Belum ada produk. Tambahkan produk pertama Anda.' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<!-- ══════════════════════════════════════════════════════════
     PAGE-SCOPED STYLES
     ══════════════════════════════════════════════════════════ -->
<style>
/* Flash messages */
.prod-flash {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: 12px;
    font-size: 0.87rem; font-weight: 600;
    margin-bottom: 20px; animation: flashSlide 0.35s ease;
}
@keyframes flashSlide { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.prod-flash__icon { width: 18px; height: 18px; flex-shrink: 0; }
.prod-flash--success { background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.3); color: #15803d; }
.prod-flash--error   { background: rgba(184,56,44,0.06); border: 1px solid rgba(184,56,44,0.25); color: #b8382c; }

/* Form card */
.prod-form-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #f0ece8;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin-bottom: 24px; overflow: hidden;
}
.prod-form-card__header {
    display: flex; align-items: center; gap: 12px;
    padding: 18px 24px; background: #faf8f6;
    border-bottom: 1px solid #f0ece8;
}
.prod-form-card__icon { width: 20px; height: 20px; color: #6D3A1A; flex-shrink: 0; }
.prod-form-card__title { font-size: 1rem; font-weight: 700; color: #2d2d2d; margin: 0; }

/* Form */
.prod-form { padding: 24px; }
.prod-form__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.prod-form__field--wide { grid-column: 1 / -1; }
.prod-form__field { display: flex; flex-direction: column; gap: 6px; }
.prod-form__label {
    font-size: 0.7rem; font-weight: 700; color: #999;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.prod-form__req { color: #b8382c; }
.prod-form__input, .prod-form__select {
    padding: 10px 12px; border: 1.5px solid #e8e4e0;
    border-radius: 10px; font-size: 0.88rem; font-family: inherit;
    color: #2d2d2d; background: #f8f6f4; outline: none;
    transition: all 0.2s;
}
.prod-form__input:focus, .prod-form__select:focus {
    border-color: #8B5E3C; background: #fff;
    box-shadow: 0 0 0 3px rgba(109,58,26,0.08);
}
.prod-form__actions { display: flex; gap: 10px; }

/* Toolbar */
.prod-toolbar {
    display: flex; align-items: center; gap: 12px;
    flex-wrap: wrap; margin-bottom: 4px;
}
.prod-toolbar__form { display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap; }
.prod-toolbar__search-wrap { position: relative; flex: 1; min-width: 200px; }
.prod-toolbar__search-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px; color: #aaa; pointer-events: none;
}
.prod-toolbar__search {
    width: 100%; padding: 9px 12px 9px 36px;
    border: 1.5px solid #e8e4e0; border-radius: 10px;
    font-size: 0.87rem; font-family: inherit; background: #f8f6f4;
    outline: none; transition: all 0.2s;
}
.prod-toolbar__search:focus { border-color: #8B5E3C; background:#fff; box-shadow:0 0 0 3px rgba(109,58,26,0.08); }
.prod-toolbar__filter {
    padding: 9px 12px; border: 1.5px solid #e8e4e0; border-radius: 10px;
    font-size: 0.87rem; font-family: inherit; background: #f8f6f4;
    outline: none; color: #2d2d2d; cursor: pointer;
}
.prod-toolbar__count { font-size: 0.78rem; color: #999; white-space: nowrap; }

/* Buttons */
.prod-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px; font-size: 0.8rem;
    font-weight: 600; font-family: inherit; cursor: pointer;
    border: 1.5px solid transparent; text-decoration: none;
    transition: all 0.2s; white-space: nowrap;
}
.prod-btn--primary {
    background: linear-gradient(135deg, #6D3A1A, #4A2710);
    color: #fff; box-shadow: 0 3px 10px rgba(109,58,26,0.25);
}
.prod-btn--primary:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(109,58,26,0.35); }
.prod-btn--ghost  { background: #f0ece8; color: #4a4a4a; border-color: #e8e4e0; }
.prod-btn--ghost:hover  { background: #e8e4e0; }
.prod-btn--cancel { background: #fff5f5; color: #b8382c; border-color: #fde8e8; }
.prod-btn--cancel:hover { background: #fde8e8; }
.prod-btn--edit   { background: #f8f6f4; color: #4a4a4a; border-color: #e8e4e0; }
.prod-btn--edit:hover   { border-color:#6D3A1A; color:#6D3A1A; }
.prod-btn--danger { background: #fff8f8; color: #b8382c; border-color: #fde8e8; }
.prod-btn--danger:hover { background: #b8382c; color: #fff; border-color:#b8382c; }

/* Category badge */
.prod-cat-badge {
    display: inline-block; padding: 3px 9px; border-radius: 20px;
    font-size: 0.7rem; font-weight: 700;
}
.prod-cat-badge--sembako     { background: rgba(34,197,94,0.1);  color: #15803d; }
.prod-cat-badge--rempahrempa { background: rgba(234,88,12,0.1);  color: #c2410c; }
.prod-cat-badge--camilan     { background: rgba(139,92,246,0.1); color: #7c3aed; }

/* Table row hover */
.prod-table__row:hover { background: #faf8f6; }

@media (max-width: 768px) {
    .prod-form__grid { grid-template-columns: 1fr 1fr; }
    .prod-form__field--wide { grid-column: 1 / -1; }
}
@media (max-width: 480px) {
    .prod-form__grid { grid-template-columns: 1fr; }
}
</style>

<script>
// Auto-scroll to form when editing
<?php if ($editProduct): ?>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('prod-form');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
<?php endif; ?>
</script>

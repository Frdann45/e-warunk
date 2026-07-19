import os
import re

base_dir = r"c:\laragon\www\e-warung"

def replace_in_file(file_path, replacements):
    if not os.path.exists(file_path):
        print(f"Skipping {file_path} (does not exist)")
        return
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    for pattern, replacement in replacements:
        if isinstance(pattern, re.Pattern):
            content = pattern.sub(replacement, content)
        else:
            content = content.replace(pattern, replacement)
            
    if content != original:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {file_path}")
    else:
        print(f"No changes for {file_path}")

# 1. Update root files
replace_in_file(os.path.join(base_dir, "index.php"), [
    ("__DIR__ . '/db_connect.php'", "__DIR__ . '/config/db_connect.php'"),
    ("__DIR__ . '/header_user.php'", "__DIR__ . '/includes/header_user.php'"),
    ("__DIR__ . '/footer.php'", "__DIR__ . '/includes/footer.php'"),
    ("href=\"style.css?v=", "href=\"assets/css/style.css?v="),
    ("href=\"images/logo.png\"", "href=\"assets/images/logo.png\""),
    ("header('Location: admin.php')", "header('Location: admin/admin.php')"),
])

replace_in_file(os.path.join(base_dir, "login.php"), [
    ("__DIR__ . '/db_connect.php'", "__DIR__ . '/config/db_connect.php'"),
    ("src=\"images/logo.png\"", "src=\"assets/images/logo.png\""),
    ("header('Location: admin.php')", "header('Location: admin/admin.php')"),
])

replace_in_file(os.path.join(base_dir, "logout.php"), [
    ("header(\"Location: login.php\")", "header(\"Location: \" . BASE_URL . \"login.php\")"), 
])

replace_in_file(os.path.join(base_dir, "detail_produk.php"), [
    ("__DIR__ . '/db_connect.php'", "__DIR__ . '/config/db_connect.php'"),
    ("__DIR__ . '/header_user.php'", "__DIR__ . '/includes/header_user.php'"),
    ("__DIR__ . '/footer.php'", "__DIR__ . '/includes/footer.php'"),
    ("href=\"style.css?v=", "href=\"assets/css/style.css?v="),
    ("href=\"images/logo.png\"", "href=\"assets/images/logo.png\""),
    ("action=\"cart_action.php\"", "action=\"<?= BASE_URL ?>process/cart_action.php\""),
    ("fetch('cart_action.php'", "fetch('process/cart_action.php'"),
])

replace_in_file(os.path.join(base_dir, "checkout_view.php"), [
    ("__DIR__ . '/db_connect.php'", "__DIR__ . '/config/db_connect.php'"),
    ("action=\"checkout_proses.php\"", "action=\"process/checkout_proses.php\""),
    ("href=\"style.css?v=", "href=\"assets/css/style.css?v="),
    ("href=\"images/logo.png\"", "href=\"assets/images/logo.png\""),
    ("src=\"images/", "src=\"assets/images/"),
])

replace_in_file(os.path.join(base_dir, "xendit_webhook.php"), [
    ("__DIR__ . '/config_xendit.php'", "__DIR__ . '/config/config_xendit.php'"),
])

# 2. Update admin files
replace_in_file(os.path.join(base_dir, "admin", "admin.php"), [
    ("__DIR__ . '/db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
    ("__DIR__ . '/header_admin.php'", "dirname(__DIR__) . '/includes/header_admin.php'"),
    ("__DIR__ . '/sidebar.php'", "dirname(__DIR__) . '/includes/sidebar.php'"),
    ("__DIR__ . '/pages/", "dirname(__DIR__) . '/pages/"),
    ("href=\"style.css?v=", "href=\"<?= BASE_URL ?>assets/css/style.css?v="),
    ("href=\"images/logo.png\"", "href=\"<?= BASE_URL ?>assets/images/logo.png\""),
    ("header('Location: index.php')", "header('Location: ' . BASE_URL . 'index.php')"),
])

replace_in_file(os.path.join(base_dir, "admin", "sim_dashboard.php"), [
    ("__DIR__ . '/db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
    ("__DIR__ . '/sidebar.php'", "dirname(__DIR__) . '/includes/sidebar.php'"),
    ("href=\"style.css?v=", "href=\"<?= BASE_URL ?>assets/css/style.css?v="),
    ("href=\"images/logo.png\"", "href=\"<?= BASE_URL ?>assets/images/logo.png\""),
    ("src=\"images/logo.png\"", "src=\"<?= BASE_URL ?>assets/images/logo.png\""),
    ("href=\"sim_dashboard.php\"", "href=\"<?= BASE_URL ?>admin/sim_dashboard.php\""),
    ("header('Location: login.php')", "header('Location: ' . BASE_URL . 'login.php')"),
    ("src=\"images/", "src=\"<?= BASE_URL ?>assets/images/"),
])

# 3. Update includes files
replace_in_file(os.path.join(base_dir, "includes", "header_user.php"), [
    ("href=\"index.php\"", "href=\"<?= BASE_URL ?>index.php\""),
    ("action=\"index.php\"", "action=\"<?= BASE_URL ?>index.php\""),
    ("href=\"login.php\"", "href=\"<?= BASE_URL ?>login.php\""),
    ("href=\"logout.php\"", "href=\"<?= BASE_URL ?>logout.php\""),
    ("href=\"admin.php\"", "href=\"<?= BASE_URL ?>admin/admin.php\""),
    ("src=\"images/logo.png\"", "src=\"<?= BASE_URL ?>assets/images/logo.png\""),
    ("src=\"images/", "src=\"<?= BASE_URL ?>assets/images/"),
])

replace_in_file(os.path.join(base_dir, "includes", "header_admin.php"), [
    ("href=\"admin.php\"", "href=\"<?= BASE_URL ?>admin/admin.php\""),
    ("action=\"admin.php\"", "action=\"<?= BASE_URL ?>admin/admin.php\""),
    ("href=\"index.php\"", "href=\"<?= BASE_URL ?>index.php\""),
    ("href=\"logout.php\"", "href=\"<?= BASE_URL ?>logout.php\""),
    ("src=\"images/logo.png\"", "src=\"<?= BASE_URL ?>assets/images/logo.png\""),
])

replace_in_file(os.path.join(base_dir, "includes", "sidebar.php"), [
    ("href=\"admin.php?page=", "href=\"<?= BASE_URL ?>admin/admin.php?page="),
    ("href=\"help_admin.php\"", "href=\"<?= BASE_URL ?>help_admin.php\""),
    ("href=\"logout.php\"", "href=\"<?= BASE_URL ?>logout.php\""),
])

replace_in_file(os.path.join(base_dir, "includes", "footer.php"), [
    ("href=\"index.php?page=", "href=\"<?= BASE_URL ?>index.php?page="),
])

# 4. Update process files
replace_in_file(os.path.join(base_dir, "process", "address_action.php"), [
    ("__DIR__ . '/db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
    ("header('Location: index.php?page=pembayaran')", "header('Location: ' . BASE_URL . 'index.php?page=pembayaran')"),
])

replace_in_file(os.path.join(base_dir, "process", "cart_action.php"), [
    ("__DIR__ . '/db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
    ("header('Location: index.php", "header('Location: ' . BASE_URL . 'index.php"),
    ("header('Location: detail_produk.php", "header('Location: ' . BASE_URL . 'detail_produk.php"),
])

replace_in_file(os.path.join(base_dir, "process", "checkout_proses.php"), [
    ("__DIR__ . '/config_xendit.php'", "dirname(__DIR__) . '/config/config_xendit.php'"),
    ("header('Location: login.php')", "header('Location: ' . BASE_URL . 'login.php')"),
    ("header('Location: index.php?page=beranda')", "header('Location: ' . BASE_URL . 'index.php?page=beranda')"),
    ("header('Location: index.php?page=keranjang')", "header('Location: ' . BASE_URL . 'index.php?page=keranjang')"),
    ("header('Location: index.php?page=pembayaran')", "header('Location: ' . BASE_URL . 'index.php?page=pembayaran')"),
])

# successRedirectUrl = BASE_URL . 'index.php?page=riwayat'
replace_in_file(os.path.join(base_dir, "process", "checkout_proses.php"), [
    (re.compile(r"\$successRedirectUrl\s*=.*?;"), "$successRedirectUrl = BASE_URL . 'index.php?page=riwayat';"),
])

replace_in_file(os.path.join(base_dir, "process", "product_action.php"), [
    ("__DIR__ . '/db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
    ("header('Location: admin.php')", "header('Location: ' . BASE_URL . 'admin/admin.php')"),
    ("header('Location: admin.php?page=tambah-produk')", "header('Location: ' . BASE_URL . 'admin/admin.php?page=tambah-produk')"),
    ("header('Location: admin.php?page=buat-promo')", "header('Location: ' . BASE_URL . 'admin/admin.php?page=buat-promo')"),
    ("header('Location: admin.php?page=tambah-produk&edit=' . $id)", "header('Location: ' . BASE_URL . 'admin/admin.php?page=tambah-produk&edit=' . $id)"),
])

# 5. Update all pages in pages/
pages_dir = os.path.join(base_dir, "pages")
for filename in os.listdir(pages_dir):
    if filename.endswith(".php"):
        file_path = os.path.join(pages_dir, filename)
        replace_in_file(file_path, [
            ("__DIR__ . '/../db_connect.php'", "dirname(__DIR__) . '/config/db_connect.php'"),
            ("__DIR__ . '/../config_xendit.php'", "dirname(__DIR__) . '/config/config_xendit.php'"),
            ("action=\"cart_action.php\"", "action=\"<?= BASE_URL ?>process/cart_action.php\""),
            ("action=\"product_action.php\"", "action=\"<?= BASE_URL ?>process/product_action.php\""),
            ("action=\"address_action.php\"", "action=\"<?= BASE_URL ?>process/address_action.php\""),
            ("action=\"checkout_proses.php\"", "action=\"<?= BASE_URL ?>process/checkout_proses.php\""),
            ("action=\"admin.php\"", "action=\"<?= BASE_URL ?>admin/admin.php\""),
            ("href=\"admin.php?page=", "href=\"<?= BASE_URL ?>admin/admin.php?page="),
            ("src=\"images/", "src=\"<?= BASE_URL ?>assets/images/"),
            ("src='images/", "src='<?= BASE_URL ?>assets/images/"),
            ("url('images/", "url('<?= BASE_URL ?>assets/images/"),
        ])

print("Finished replacing paths.")

import os

pages_dir = r"c:\laragon\www\e-warung\pages"

for filename in os.listdir(pages_dir):
    if filename.endswith(".php"):
        file_path = os.path.join(pages_dir, filename)
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        content = content.replace("header('Location: login.php')", "header('Location: ' . BASE_URL . 'login.php')")
        content = content.replace("header('Location: index.php", "header('Location: ' . BASE_URL . 'index.php")
        
        if content != original:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated redirects in {filename}")

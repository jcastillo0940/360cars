import os

# Replacements for corrupted characters
REPLACEMENTS = {
    'Â·': '·',
    'â€™': "'",
    'â€œ': '"',
    'â€?': '"',
    'â€“': '–',
    'â€”': '—',
    'â€¦': '...',
    'Â¡': '¡',
    'Â¿': '¿',
    'â€¢': '•',
    'â¡': '¡',
}

TARGET_DIRECTORIES = ['app', 'resources', 'config', 'routes', 'database']
EXTENSIONS = {'.php', '.jsx', '.pcss', '.css', '.js', '.blade.php'}

def strip_bom(content):
    if content.startswith('\ufeff'):
        return content[1:]
    return content

def fix_file(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
    except UnicodeDecodeError:
        try:
            with open(file_path, 'r', encoding='latin-1') as f:
                content = f.read()
        except:
            return False

    original_content = content
    content = strip_bom(content)
    
    for corrupted, fixed in REPLACEMENTS.items():
        content = content.replace(corrupted, fixed)
    
    if content != original_content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

def main():
    fixed_count = 0
    for target in TARGET_DIRECTORIES:
        full_path = os.path.join(os.getcwd(), target)
        if not os.path.exists(full_path):
            continue
            
        for root, dirs, files in os.walk(full_path):
            for file in files:
                if any(file.endswith(ext) for ext in EXTENSIONS):
                    file_path = os.path.join(root, file)
                    if fix_file(file_path):
                        print(f"Fixed: {file_path}")
                        fixed_count += 1
    
    print(f"\nTotal files fixed: {fixed_count}")

if __name__ == "__main__":
    main()

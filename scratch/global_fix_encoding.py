import os

replacements = {
    'Ã¡': 'á',
    'Ã©': 'é',
    'Ã­': 'í',
    'Ã³': 'ó',
    'Ãº': 'ú',
    'Ã±': 'ñ',
    'Ã‘': 'Ñ',
    'Ã\x8d': 'Í',
    'Ã“': 'Ó',
    'Ãš': 'Ú',
    'Ã\x81': 'Á',
    'Â¿': '¿',
    'Â¡': '¡',
    'â€¢': '•',
    'â‚¡': '₡',
}

def fix_file(path):
    try:
        with open(path, 'rb') as f:
            raw_data = f.read()
        
        # Check for BOM
        if raw_data.startswith(b'\xef\xbb\xbf'):
            print(f"BOM detected in {path}, removing...")
            raw_data = raw_data[3:]
            
        content = raw_data.decode('utf-8', errors='ignore')
        
        original = content
        for search, replace in replacements.items():
            content = content.replace(search, replace)
            
        if content != original or len(raw_data) != len(original.encode('utf-8')):
            with open(path, 'w', encoding='utf-8', newline='') as f:
                f.write(content)
            return True
    except Exception as e:
        print(f"Error processing {path}: {e}")
    return False

root_dir = r'e:\apps\360Cars'
target_dirs = ['app', 'database', 'config', 'resources', 'routes']
fixed_count = 0

for t_dir in target_dirs:
    dir_path = os.path.join(root_dir, t_dir)
    if not os.path.exists(dir_path): continue
    
    for root, dirs, files in os.walk(dir_path):
        for file in files:
            if file.endswith(('.php', '.jsx', '.js', '.css')):
                if fix_file(os.path.join(root, file)):
                    fixed_count += 1
                    print(f"Fixed: {os.path.join(root, file)}")

print(f"Total files fixed: {fixed_count}")

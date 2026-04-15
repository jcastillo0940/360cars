import os

def fix_mojibake(directory):
    replacements = {
        'Ã¡': 'á',
        'Ã©': 'é',
        'Ã\xad': 'í',
        'Ã³': 'ó',
        'Ãº': 'ú',
        'Ã±': 'ñ',
        'Ã\x81': 'Á',
        'Ã\x89': 'É',
        'Ã\x8d': 'Í',
        'Ã\x93': 'Ó',
        'Ã\x9a': 'Ú',
        'Ã\x91': 'Ñ',
        'Â¿': '¿',
        'Â¡': '¡'
    }
    
    for root, dirs, files in os.walk(directory):
        if any(d in root for d in ['node_modules', 'vendor', '.git']):
            continue
            
        for file in files:
            if file.endswith(('.php', '.js', '.jsx', '.css', '.blade.php')):
                path = os.path.join(root, file)
                try:
                    with open(path, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    original_content = content
                    for bad, good in replacements.items():
                        content = content.replace(bad, good)
                    
                    if content != original_content:
                        with open(path, 'w', encoding='utf-8') as f:
                            f.write(content)
                        print(f"Fixed mojibake in: {path}")
                except Exception as e:
                    pass

fix_mojibake('.')

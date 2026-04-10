import os
import re

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
    'â‚¡': '₡',
    'Ã ': 'à', # rarer
    'Ã¨': 'è', # rarer
    'Ã²': 'ò', # rarer
    'Ã¹': 'ù', # rarer
    'Ã ': 'À', # rarer
    'Ãˆ': 'È', # rarer
    'Ã’': 'Ò', # rarer
    'Ã™': 'Ù', # rarer
}

# Special case for strings reported by user
manual_fixes = [
    ('VEHÃCULO', 'VEHÍCULO'), # Likely missing the second byte of the multi-byte char in display but present in file
    ('AÃ‘O', 'AÑO'),
    ('UBICACIÃ“N', 'UBICACIÓN'),
    ('lÃmite', 'límite'),
    ('paÃ­s', 'país'),
    ('BÃºsqueda', 'Búsqueda'),
    ('refinad', 'refinada'), # not encoding but typo?
]

def fix_file(path):
    try:
        with open(path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original = content
        for search, replace in replacements.items():
            content = content.replace(search, replace)
        
        for search, replace in manual_fixes:
            content = content.replace(search, replace)
            
        if content != original:
            with open(path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
    except Exception as e:
        print(f"Error processing {path}: {e}")
    return False

root_dir = r'e:\apps\360Cars\resources'
fixed_count = 0
for root, dirs, files in os.walk(root_dir):
    for file in files:
        if file.endswith(('.php', '.jsx', '.js', '.css', '.html')):
            if fix_file(os.path.join(root, file)):
                fixed_count += 1
                print(f"Fixed: {os.path.join(root, file)}")

print(f"Total files fixed: {fixed_count}")

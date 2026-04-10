import os

replacements = {
    'est? vacio': 'está vacío',
    'est? búsqueda': 'esta búsqueda',
    'est? evaluación': 'esta evaluación',
    'est?do': 'estado',
    'Rest?blecer': 'Restablecer',
    'dest?ca': 'destaca',
    'Dest?car': 'Destacar',
    'est? ': 'está ', # more generic
    'regreso de est?': 'regreso de esta',
}

def fix_file(path):
    try:
        with open(path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original = content
        for search, replace in replacements.items():
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
        if file.endswith(('.php', '.jsx', '.js')):
            if fix_file(os.path.join(root, file)):
                fixed_count += 1
                print(f"Fixed: {os.path.join(root, file)}")

print(f"Total files fixed: {fixed_count}")

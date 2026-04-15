import os

def cleanup_file(path):
    replacements = {
        'Ã¡': 'á', 'Ã©': 'é', 'Ã\xad': 'í', 'Ã³': 'ó', 'Ãº': 'ú', 'Ã±': 'ñ',
        'Ã\x81': 'Á', 'Ã\x89': 'É', 'Ã\x8d': 'Í', 'Ã\x93': 'Ó', 'Ã\x9a': 'Ú', 'Ã\x91': 'Ñ',
        'Ãš': 'Ú', 'Ã±': 'ñ', 'Ã‘': 'Ñ',
        'Â¿': '¿', 'Â¡': '¡',
        'Ã¢Å“â€œ': '✓', 'Ã¢Å¡Â': '⚠',
        'configuraciÃ³n': 'configuración',
        'actualizaciÃ³n': 'actualización',
        'Ã³n': 'ón', 'Ã¡n': 'án', 'Ã©n': 'én', 'Ã\xadn': 'ín'
    }
    
    try:
        with open(path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
        
        new_lines = []
        for line in lines:
            # Fix mojibake in the line
            for bad, good in replacements.items():
                line = line.replace(bad, good)
            
            # If line is totally empty and previous was also empty, skip it (to fix the gaps)
            # but we have to be careful not to remove intentional empty lines.
            # In AdminPortalController it seemed like every other line was empty.
            new_lines.append(line)
        
        # Heuristic to fix the "every other line is empty" issue
        # If more than 40% of lines are empty and they alternate, it's likely a bug
        empty_count = sum(1 for l in new_lines if not l.strip())
        if empty_count > len(new_lines) * 0.4:
            print(f"Heuristic triggered for {path}: fixing gaps")
            collapsed_lines = []
            for i in range(len(new_lines)):
                if not new_lines[i].strip():
                    # Check if surrounded by code
                    if i > 0 and i < len(new_lines) - 1:
                        if new_lines[i-1].strip() and new_lines[i+1].strip():
                            # This empty line might be one of the "gaps"
                            # If we see this pattern consistently, we skip it
                            continue
                collapsed_lines.append(new_lines[i])
            new_lines = collapsed_lines

        content = "".join(new_lines)
        with open(path, 'w', encoding='utf-8', newline='\n') as f:
            f.write(content)
        print(f"Cleaned up {path}")
            
    except Exception as e:
        print(f"Error cleaning {path}: {e}")

cleanup_file(r'e:\apps\360Cars\app\Http\Controllers\Web\AdminPortalController.php')
cleanup_file(r'e:\apps\360Cars\resources\views\portal\admin\settings.blade.php')
cleanup_file(r'e:\apps\360Cars\resources\views\portal\admin\users.blade.php')
# Add features too just in case
cleanup_file(r'e:\apps\360Cars\resources\views\portal\admin\features.blade.php')

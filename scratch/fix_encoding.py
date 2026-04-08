import os

file_path = r'e:\apps\360Cars\resources\views\seller\onboarding.blade.php'

with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Fix typical garbage patterns
fixes = {
    'Ã¡': 'á',
    'Ã©': 'é',
    'Ã­': 'í',
    'Ã³': 'ó',
    'Ãº': 'ú',
    'Ã±': 'ñ',
    'Ã ': 'Á',
    'Ã‰': 'É',
    'Ã ': 'Í',
    'Ã“': 'Ó',
    'Ãš': 'Ú',
    'Ã‘': 'Ñ',
    'Ã¼': 'ü',
    'Identidad del vehÃ­culo': 'Identidad del vehículo',
    'publicaciÃ³n': 'publicación',
    'descripciÃ³n': 'descripción',
    'fricciÃ³n': 'fricción',
    'rÃ¡pido': 'rápido',
    'AsÃ­': 'Así',
    'informaciÃ³n': 'información',
    'fotografÃ­as': 'fotografías',
    'ubicaciÃ³n': 'ubicación',
    'ubicaciÃ³nes': 'ubicaciones',
    'cantÃ³n': 'cantón',
    'despuÃ©s': 'después',
    'versiÃ³n': 'versión',
    'identidad del veh?culo': 'identidad del vehículo',
    'Estimaci?n': 'Estimación',
    'sesi?n': 'sesión',
    'est?': 'está',
    'contin?a': 'continúa',
    'caracter?sticas': 'características',
    'administraci?n': 'administración',
    'contrase?a': 'contraseña',
    't?rminos': 'términos',
    'b?sico': 'básico',
    'podr?s': 'podrás',
    'veh?culo': 'vehículo',
    'Identidad del veh?culo': 'Identidad del vehículo',
    'Publicaci?n': 'Publicación',
    'publicaci?n': 'publicación',
    'Dise?ado': 'Diseñado',
    'fotograf?as': 'fotografías',
    'm?s': 'más',
    'vaci?': 'vacía',
    'Pa?s': 'País',
    'bot?n': 'botón'
}

for src, dst in fixes.items():
    content = content.replace(src, dst)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fix applied successfully.")

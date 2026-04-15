import chardet

file_path = r'e:\apps\360Cars\resources\views\portal\admin\users.blade.php'
with open(file_path, 'rb') as f:
    raw_data = f.read()
    result = chardet.detect(raw_data)
    print(f"File: {file_path}")
    print(f"Encoding: {result['encoding']}")
    print(f"Confidence: {result['confidence']}")

# Also check AdminPortalController.php
file_path_2 = r'e:\apps\360Cars\app\Http\Controllers\Web\AdminPortalController.php'
with open(file_path_2, 'rb') as f:
    raw_data = f.read()
    result = chardet.detect(raw_data)
    print(f"File: {file_path_2}")
    print(f"Encoding: {result['encoding']}")
    print(f"Confidence: {result['confidence']}")

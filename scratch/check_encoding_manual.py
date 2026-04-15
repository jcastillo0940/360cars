def check_file(path):
    print(f"Checking {path}")
    try:
        with open(path, 'rb') as f:
            data = f.read()
        
        # Try UTF-8
        try:
            data.decode('utf-8')
            print("  Decodes as UTF-8: YES")
        except UnicodeDecodeError as e:
            print(f"  Decodes as UTF-8: NO ({e})")
            
        # Try Windows-1252 (Latin-1)
        try:
            data.decode('windows-1252')
            print("  Decodes as Windows-1252: YES")
        except UnicodeDecodeError as e:
            print(f"  Decodes as Windows-1252: NO ({e})")
            
        # Look for suspicious bytes
        # High bytes often indicate non-ASCII
        high_bytes = [b for b in data if b > 127]
        if high_bytes:
            print(f"  High bytes found: {len(high_bytes)}")
            # Show a few around where they occur
            pos = data.find(max(high_bytes))
            context = data[max(0, pos-20):pos+20]
            print(f"  Example at position {pos}: {context}")
            
    except Exception as e:
        print(f"  Error reading file: {e}")

check_file(r'e:\apps\360Cars\resources\views\portal\admin\users.blade.php')
check_file(r'e:\apps\360Cars\app\Http\Controllers\Web\AdminPortalController.php')

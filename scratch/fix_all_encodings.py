import os

def check_encoding(directory):
    for root, dirs, files in os.walk(directory):
        if 'node_modules' in dirs:
            dirs.remove('node_modules')
        if 'vendor' in dirs:
            dirs.remove('vendor')
        if '.git' in dirs:
            dirs.remove('.git')
        
        for file in files:
            if file.endswith(('.php', '.js', '.jsx', '.css', '.blade.php')):
                path = os.path.join(root, file)
                try:
                    with open(path, 'rb') as f:
                        data = f.read()
                    data.decode('utf-8')
                except UnicodeDecodeError:
                    print(f"Non-UTF-8 file found: {path}")
                    try:
                        # Try to decode as windows-1252 and re-encode as utf-8
                        content = data.decode('windows-1252')
                        with open(path, 'w', encoding='utf-8') as f:
                            f.write(content)
                        print(f"  Converted {path} to UTF-8")
                    except Exception as e:
                        print(f"  Failed to convert {path}: {e}")

check_encoding('.')

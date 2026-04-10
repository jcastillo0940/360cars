import os
import requests
import time

logos = {
    'byd': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/BYD_logo.svg/1200px-BYD_logo.svg.png',
    'changan': 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Changan_Auto_logo.svg/1200px-Changan_Auto_logo.svg.png',
    'chery': 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/Chery_logo.svg/1200px-Chery_logo.svg.png',
    'geely': 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Geely_logo.svg/1200px-Geely_logo.svg.png',
    'great-wall': 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Great_Wall_Motor_logo.svg/1200px-Great_Wall_Motor_logo.svg.png',
    'haval': 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Haval_logo.svg/1200px-Haval_logo.svg.png',
    'jac': 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/67/JAC_Motors_logo.svg/1200px-JAC_Motors_logo.svg.png',
    'alfa-romeo': 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/13/Alfa_Romeo_Logo_2015.svg/1200px-Alfa_Romeo_Logo_2015.svg.png',
    'ssangyong': 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SsangYong_Logo.svg/1200px-SsangYong_Logo.svg.png',
    'lexus': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Lexus_logo.svg/1200px-Lexus_logo.svg.png',
    'land-rover': 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/be/Land_Rover_logo_2023.svg/1200px-Land_Rover_logo_2023.svg.png',
    'dodge': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/70/Dodge_logo.svg/1200px-Dodge_logo.svg.png',
}

base_path = r'e:\apps\360Cars\public\logos'
os.makedirs(base_path, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
}

for slug, url in logos.items():
    try:
        print(f"Downloading {slug}...")
        response = requests.get(url, headers=headers, timeout=15)
        if response.status_code == 200:
            with open(os.path.join(base_path, f"{slug}.png"), 'wb') as f:
                f.write(response.content)
            print(f"Saved {slug}.png")
        else:
            print(f"Failed to download {slug}: Status {response.status_code}")
        time.sleep(1)
    except Exception as e:
        print(f"Error downloading {slug}: {e}")
